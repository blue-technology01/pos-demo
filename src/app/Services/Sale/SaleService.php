<?php

namespace App\Services\Sale;

use App\Models\CashRegister;
use App\Models\Sale;
use App\Models\Product;
use App\Services\Cash\CashRegisterService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class SaleService
{
    protected $cashRegisterService;

    public function __construct(CashRegisterService $cashRegisterService)
    {
        $this->cashRegisterService = $cashRegisterService;
    }

    public function getAllSales(Request $request): LengthAwarePaginator
    {
        return Sale::query()
            ->with(['items'])
            ->when($request->invoice_no, fn($q) => $q->where('invoice_no', 'LIKE', "%{$request->invoice_no}%"))
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->date && !$request->date_from && !$request->date_to, fn($q) => $q->whereDate('sale_date', $request->date))
            ->when($request->date_from && $request->date_to, fn($q) => $q->whereBetween('sale_date', [$request->date_from, $request->date_to]))
            ->orderByDesc('id')
            ->paginate($request->per_page ?? 15);
    }

    public function confirmSale(array $data): Sale
    {
        return $this->createSale($data);
    }

    // open new sales
    public function createSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {

            // create receipt auto by day,month, year
            $invoiceNo = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            // insert data to table sale when have sale
            $sale = Sale::create([
                'invoice_no'        => $invoiceNo,
                'register_id'       => $data['register_id'],
                'user_id'           => Auth::id(),
                'customer_id'       => $data['customer_id'] ?? null,
                'sub_total'         => $data['sub_total'],
                'discount_amount'   => $data['discount_amount'] ?? 0,
                'tax_amount'        => $data['tax_amount'] ?? 0,
                'total_amount'      => $data['total_amount'],
                'paid_amount'       => $data['paid_amount'],
                'change_amount'     => $data['change_amount'] ?? 0,
                'payment_method'    => $data['payment_method'] ?? 'cash',
                'sale_date'         => now(),
                'status'            => 'completed',
            ]);

            // loop items inset to table sale_itmes and romove stock
            foreach ($data['items'] as $item) {

                // filter product by code
                $product = Product::where('code', $item['product_code'])->lockForUpdate()->first();

                if (!$product) {
                    throw new \Exception("Can't finding  「{$item['product_code']}」 product code!");
                }
                // get current matrix from product_uom for know about qty per unit
                $productUom = DB::table('product_uoms')
                    ->where('product_code', $item['product_code'])
                    ->where('uom_code', $item['uom_code']) // check uom code (e.g., UNIT-006)
                    ->first();

                // if not found on matrix it will
                $qtyPerUnit = $productUom ? $productUom->quantity_per_unit : 1;
                // calculate current product that cut from stock, example buy 1 =24 bottle
                $totalQtyToDecrement = $item['quantity'] * $qtyPerUnit;
                // check stock before cut stock
                if ($product->stock < $totalQtyToDecrement) {
                    $pName = $item['product_name'] ?? $product->name;
                    throw new \Exception(" Product name 「{$pName}」 don't have product not enought!");
                }
                // insert to sale_items
                $sale->items()->create([
                    'product_id'   => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'quantity'     => $item['quantity'], // number that customer sale
                    'uom_code'     => $item['uom_code'],  // store current code
                    'cost_price'   => $productUom ? $productUom->cost_price : $product->cost_price,
                    'unit_price'   => $item['unit_price'],
                    'amount'       => $item['quantity'] * $item['unit_price'],
                ]);
                // cut stock dynamic
                $product->decrement('stock', $totalQtyToDecrement);
            }
            // update money
            if (!empty($data['register_id'])) {
                $isCash = ($data['payment_method'] === 'cash');
                $amount = floatval($data['total_amount']);

                $register = DB::table('cash_registers')->where('id', $data['register_id'])->first();

                if ($register && $register->status === 'open') {
                    $updateData = [
                        'total_sales'        => DB::raw("total_sales + {$amount}"),
                        'total_transactions' => DB::raw("total_transactions + 1"),
                    ];

                    if (Schema::hasColumn('cash_registers', 'cash_sales')) {
                        $updateData['cash_sales']     = DB::raw($isCash ? "cash_sales + {$amount}" : "cash_sales");
                        $updateData['non_cash_sales'] = DB::raw(!$isCash ? "non_cash_sales + {$amount}" : "non_cash_sales");
                    }

                    if (Schema::hasColumn('cash_registers', 'expected_balance')) {
                        $cashAdd = $isCash ? $amount : 0;
                        $updateData['expected_balance'] = DB::raw("expected_balance + {$cashAdd}");
                    }

                    DB::table('cash_registers')
                        ->where('id', $data['register_id'])
                        ->update($updateData);
                }
            }

            return $sale;
        });
    }

    // update reciept
    public function updateSale(int $id, array $data): Sale
    {
        $sale = Sale::with('items')->findOrFail($id);

        if ($sale->status === 'voided') {
            throw new \Exception(' Reciept that cancel can be not edit');
        }

        return DB::transaction(function () use ($sale, $data) {
            // Add to old stock
            foreach ($sale->items as $oldItem) {
                $product = Product::where('code', $oldItem->product_code)->lockForUpdate()->first();
                if ($product) {
                    $product->increment('stock', $oldItem->quantity);
                }
            }
            // Update money on shift ( Take out the old money first. )
            if ($sale->status === 'completed') {
                $this->reverseCashTransaction($sale);
            }

            // remove old items
            $sale->items()->delete();

            // run loop insert new items and check new stock that refun
            foreach ($data['items'] as $item) {
                // finding product
                $product = Product::where('code', $item['product_code'])->lockForUpdate()->first();

                // download matrix that selling for know about price base qty
                $productUom = DB::table('product_uoms')
                    ->where('product_code', $item['product_code'])
                    ->where('uom_code', $item['uom_code'])
                    ->first();

                // if can't finding on matrix it will auto cut ( default = 1 )
                $qtyPerUnit = $productUom ? $productUom->quantity_per_unit : 1;

                // culculate real product that cut from stock
                $totalQtyToDecrement = $item['quantity'] * $qtyPerUnit;

                // check stock before cut
                if (!$product || $product->stock < $totalQtyToDecrement) {
                    throw new \Exception("Product not enough!");
                }

                // insert into sale_items
                $sale->items()->create([
                    'product_id'   => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'quantity'     => $item['quantity'], // number of item that use buy
                    'uom_code'     => $item['uom_code'],
                    'cost_price'   => $productUom ? $productUom->cost_price : $product->cost_price,
                    'unit_price'   => $item['unit_price'],
                    'amount'       => $item['quantity'] * $item['unit_price'],
                ]);
                $product->decrement('stock', $totalQtyToDecrement);
            }

            // edit information on sale
            $sale->update([
                'sale_date'       => $data['sale_date'] ?? now(),
                'sub_total'       => $data['sub_total'],
                'discount_amount' => $data['discount_amount'],
                'tax_amount'      => $data['tax_amount'],
                'total_amount'    => $data['total_amount'],
                'paid_amount'     => $data['paid_amount'],
                'change_amount'   => $data['change_amount'],
                'payment_method'  => $data['payment_method'] ?? 'cash',
                'status'          => $data['status'] ?? 'completed',
                'note'            => $data['note'] ?? null,
            ]);
            // calculate new money to list
            if ($sale->status === 'completed') {
                $register = CashRegister::where('id', $sale->register_id)->lockForUpdate()->first();
                if ($register && $register->status === 'open') {
                    $register->increment('total_sales', $sale->total_amount);
                    $register->increment('total_transactions', 1);
                }
            }

            return $sale->refresh()->load('items');
        });
    }

    private function reverseCashTransaction(Sale $sale): void
    {
        $register = CashRegister::where('id', $sale->register_id)
            ->lockForUpdate()
            ->first();

        if ($register && $register->status === 'open') {
            $register->update([
                'total_sales'        => max(0, $register->total_sales - $sale->total_amount),
                'total_transactions' => max(0, $register->total_transactions - 1),
            ]);
        }
    }

    public function cancelSale(int $id): Sale
    {
        $sale = Sale::with('items')->findOrFail($id);

        if ($sale->status === 'voided') {
            throw new \Exception('Reciept is ready cancel!');
        }

        if (!in_array($sale->status, ['completed', 'pending'])) {
            throw new \Exception("Can be not cancel or remove only completed or pending");
        }

        return DB::transaction(function () use ($sale) {
            foreach ($sale->items as $item) {
                $product = Product::where('code', $item->product_code)
                    ->lockForUpdate()
                    ->first();

                if ($product) {
                    $product->increment('stock', $item->quantity);
                }
            }

            if ($sale->status === 'completed') {
                $this->reverseCashTransaction($sale);
            }

            $sale->update(['status' => 'voided']);

            return $sale->refresh();
        });
    }
}
