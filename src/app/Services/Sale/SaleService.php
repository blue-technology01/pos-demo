<?php

namespace App\Services\Sale;

use Carbon\Carbon;
use App\Models\Sale;
use App\Models\Product;
use App\Models\ProductUom;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use App\Services\Sale\InventoryService;
use App\Services\Cash\CashRegisterService;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Services\Inventory\StockMovementService;

class SaleService
{
    public function __construct(

        protected CashRegisterService $cashRegisterService,
        protected InventoryService $inventoryService,
        protected StockGuardService $stockGuardService,
        protected StockMovementService $stockMovementService

    ) {}

    private static ?bool $hasCashSalesColumn       = null;
    private static ?bool $hasExpectedBalanceColumn = null;

    // Caching
    private function hasCashSalesColumn(): bool
    {
        if (self::$hasCashSalesColumn === null) {
            self::$hasCashSalesColumn = Schema::hasColumn('cash_registers', 'cash_sales');
        }
        return self::$hasCashSalesColumn;  // true or false
    }

    private function hasExpectedBalanceColumn(): bool
    {
        if (self::$hasExpectedBalanceColumn === null) {
            self::$hasExpectedBalanceColumn = Schema::hasColumn('cash_registers', 'expected_balance');
        }
        return self::$hasExpectedBalanceColumn;
    }

    private function getProductUom(string $productCode, ?string $uomCode): ?object
    {
        if (!$uomCode) {
            return null;
        }

        return ProductUom::where('product_code', $productCode)
            ->where('uom_code', $uomCode)
            ->first();
    }

    // resolve cost price for a sale item fall back to the product uom cost price when no product uom
    private function resolveCostPrice(?object $productUom, Product $product): float
    {
        return (float) ($productUom?->cost_price ?? $product->cost_price ?? 0);
    }

    // convert a quantty expressed in sale item's uom, ex:1 pack =6
    private function resolveBaseQuantity(?object $productUom, float $qty): int
    {
        if ($productUom && $productUom->quantity_per_unit > 0) {
            return (int) round($qty * $productUom->quantity_per_unit);  // 1 * 33
        }
        return (int) round($qty);
    }

    private function processSaleItem(Sale $sale, array $item, ?object $productUom): void {

        // fetch product
        $product = Product::where('code', $item['product_code'])
            ->lockForUpdate()
            ->firstOrFail();

        // deduct stock
        $this->inventoryService->deductStockWithCheck(
            $item['product_code'],
            $item['uom_code'] ?? null,
            $item['quantity']
        );

        // Record Movement
        $this->stockMovementService->recordSale(
            $product->code,
            $this->resolveBaseQuantity($productUom, $item['quantity']),
            (int) Auth::id()
        );

        // Create Item
        $sale->items()->create([
            'product_id'   => $product->id,
            'product_code' => $product->code,
            'product_name' => $product->name,
            'quantity'     => $item['quantity'],
            'uom_code'     => $item['uom_code'] ?? null,
            'cost_price'   => $this->resolveCostPrice($productUom, $product),
            'unit_price'   => $item['unit_price'],
            'amount'       => bcmul((string)$item['quantity'], (string)$item['unit_price'], 2),
        ]);
    }

    // get all sale data for history
    public function getAllSales(Request $request): LengthAwarePaginator
    {
        return Sale::query()
            ->with(['items', 'user'])
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($q) use ($request) {
                    $q->where('invoice_no', 'LIKE', "%{$request->search}%")
                      ->orWhereHas('user', fn($u) =>
                          $u->where('name', 'LIKE', "%{$request->search}%")
                      );
                });
            })

            ->when($request->status, fn($q) =>
                $q->where('status', $request->status)
            )

            ->when($request->start_date || $request->end_date, function ($q) use ($request) {

                $start = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : null;
                $end   = $request->end_date   ? Carbon::parse($request->end_date)->endOfDay()     : null;

                if ($start && $end) {
                    return $q->whereBetween('sale_date', [$start, $end]);
                }

                return $q->when($start, fn($q) => $q->where('sale_date', '>=', $start))
                        ->when($end,   fn($q) => $q->where('sale_date', '<=', $end));
            })
            ->orderByDesc('id')
            ->paginate($request->per_page ?? 15)
            ->withQueryString();
    }

    // create and confirm
    public function confirmSale(array $data): Sale
    {
        return $this->createSale($data);
    }

    // create sale
    public function createSale(array $data): Sale
    {
        // validate stock before opening the transaction
        $validatedItems = [];

        foreach ($data['items'] as $item) {
            // only when the row exists.
            $productUom = $this->getProductUom(
                $item['product_code'],
                $item['uom_code'] ?? null
            );

            // run stock guard when we have a product uom row and stock dedunted directly by inventory_service
            if ($productUom) {
                $check = $this->stockGuardService->checkAndBlock(
                    $productUom->id,
                    $item['quantity']
                );

                if (!$check['allowed']) {
                    throw new \Exception("{$check['product_name']} blocked: {$check['reason']}");
                }
            }

            $validatedItems[] = [
                'item'       => $item,
                'productUom' => $productUom, // may be null
            ];
        }

        return DB::transaction(function () use ($data, $validatedItems) {

            $invoiceNo = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            $sale = Sale::create([
                'invoice_no'      => $invoiceNo,
                'register_id'     => $data['register_id'],
                'user_id'         => Auth::id(),
                'customer_id'     => $data['customer_id']     ?? null,
                'sub_total'       => $data['sub_total'],
                'discount_amount' => $data['discount_amount'] ?? 0,
                'tax_amount'      => $data['tax_amount']      ?? 0,
                'total_amount'    => $data['total_amount'],
                'paid_amount'     => $data['paid_amount'],
                'change_amount'   => $data['change_amount']   ?? 0,
                'payment_method'  => $data['payment_method']  ?? 'cash',
                'sale_date'       => now(),
                'status'          => 'completed',
            ]);

            foreach ($validatedItems as $validated) {
                $item       = $validated['item'];
                $productUom = $validated['productUom'];

                $product = Product::where('code', $item['product_code'])
                    ->lockForUpdate() // protect user can be dedunted stock one time
                    ->firstOrFail();

                $this->inventoryService->deductStockWithCheck(
                    $item['product_code'],
                    $item['uom_code'] ?? null,
                    $item['quantity']
                );

                // Record the movement so it shows up on the stock movement page.
                // Convert to base units first — 1 pack must log as -6, not -1.
                $this->stockMovementService->recordSale(
                    $product->code,
                    $this->resolveBaseQuantity($productUom, $item['quantity']),
                    (int) Auth::id()
                );

                $sale->items()->create([
                    'product_id'   => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'quantity'     => $item['quantity'],
                    'uom_code'     => $item['uom_code'] ?? null,
                    'cost_price'   => $this->resolveCostPrice($productUom, $product),
                    'unit_price'   => $item['unit_price'],
                    // 'amount'       => $item['quantity'] * $item['unit_price'],
                    'amount' => bcmul((string)$item['quantity'], (string)$item['unit_price'], 2),
                ]);
            }

            if (!empty($data['register_id'])) {
                $this->addCashTransaction(
                    registerId:    $data['register_id'],
                    amount:        (float) $data['total_amount'],
                    paymentMethod: $data['payment_method'] ?? 'cash'
                );
            }

            Log::info('Sale created', [
                'invoice_no'   => $sale->invoice_no,
                'total_amount' => $sale->total_amount,
                'items_count'  => count($data['items']),
            ]);

            return $sale;
        });
    }

    // update function
    public function updateSale(int $id, array $data): Sale
    {
        $sale = Sale::with('items')->findOrFail($id);

        if ($sale->status === 'voided') {
            throw new \Exception('Voided receipts cannot be edited.');
        }

        return DB::transaction(function () use ($sale, $data) {
            // restor old stock
            foreach ($sale->items as $oldItem) {

                $this->inventoryService->restoreStock($oldItem->product_code, $oldItem->uom_code, $oldItem->quantity);
                $oldProductUom = $this->getProductUom($oldItem->product_code, $oldItem->uom_code);

                $this->stockMovementService->record([
                    'created_by'    => Auth::id(),
                    'product_code'  => $oldItem->product_code,
                    'quantity'      => $this->resolveBaseQuantity($oldProductUom, $oldItem->quantity),
                    'movement_type' => 'return',
                ]);
            }

            // update cash register
            if ($sale->status === 'completed') {
                $this->reverseCashTransaction($sale);
            }

            // remove old item
            $sale->items()->delete();

            // start new item that use process_sale
            foreach ($data['items'] as $item) {

                $productUom = $this->getProductUom($item['product_code'], $item['uom_code'] ?? null);

                // validate stock
                if ($productUom) {
                    $check = $this->stockGuardService->checkAndBlock($productUom->id, $item['quantity']);
                    if (!$check['allowed']) {
                        throw new \Exception("{$check['product_name']} blocked: {$check['reason']}");
                    }
                }

                // call helper method
                $this->processSaleItem($sale, $item, $productUom);
            }

            // update header
            $sale->update([
                'sale_date'       => $data['sale_date']      ?? now(),
                'sub_total'       => $data['sub_total'],
                'discount_amount' => $data['discount_amount'],
                'tax_amount'      => $data['tax_amount'],
                'total_amount'    => $data['total_amount'],
                'paid_amount'     => $data['paid_amount'],
                'change_amount'   => $data['change_amount'],
                'payment_method'  => $data['payment_method'] ?? 'cash',
                'status'          => $data['status'] ?? 'completed',
                'note'            => $data['note']           ?? null,
            ]);

            // note new cash
            if ($sale->status === 'completed') {
                $this->addCashTransaction($sale->register_id, (float)$sale->total_amount, $sale->payment_method);
            }

            return $sale->refresh()->load('items');
        });
    }

    // function for cancel sale
    public function cancelSale(int $id): Sale
    {
        $sale = Sale::with('items')->findOrFail($id);

        if ($sale->status === 'voided') {
            throw new \Exception('This receipt has already been voided.');
        }

        if (!in_array($sale->status, ['completed', 'pending'])) {
            throw new \Exception('Only completed or pending receipts can be voided.');
        }

        return DB::transaction(function () use ($sale) {

            foreach ($sale->items as $item) {
                $this->inventoryService->restoreStock(
                    $item->product_code,
                    $item->uom_code,
                    $item->quantity
                );

                $itemProductUom = $this->getProductUom($item->product_code, $item->uom_code);

                $this->stockMovementService->record([
                    'created_by'    => Auth::id(),
                    'product_code'  => $item->product_code,
                    'quantity'      => $this->resolveBaseQuantity($itemProductUom, $item->quantity),
                    'movement_type' => 'return',
                ]);
            }

            if ($sale->status === 'completed') {
                $this->reverseCashTransaction($sale);
            }

            $sale->update(['status' => 'voided']);

            Log::info('Sale voided', ['invoice_no' => $sale->invoice_no]);

            return $sale->refresh();
        });
    }

    // add cash transaction
    private function addCashTransaction(int $registerId, float $amount, string $paymentMethod): void
    {
        $register = CashRegister::where('id', $registerId)
            ->lockForUpdate()
            ->first();

        if (!$register || $register->status !== 'open') {
            return;
        }

        $isCash = ($paymentMethod === 'cash');

        $register->increment('total_sales', $amount);
        $register->increment('total_transactions', 1);

        if ($this->hasCashSalesColumn()) {
            if ($isCash) {
                $register->increment('cash_sales', $amount);
            } else {
                $register->increment('non_cash_sales', $amount);
            }
        }

        if ($this->hasExpectedBalanceColumn() && $isCash) {
            $register->increment('expected_balance', $amount);
        }
    }

    // function for reverse cash transaction
    private function reverseCashTransaction(Sale $sale): void
    {
        $register = CashRegister::where('id', $sale->register_id)
            ->lockForUpdate()
            ->first();

        if (!$register || $register->status !== 'open') {
            return;
        }

        $isCash = ($sale->payment_method === 'cash');
        $amount = (float) $sale->total_amount;

        $updates = [
            'total_sales'        => max(0, $register->total_sales - $amount),
            'total_transactions' => max(0, $register->total_transactions - 1),
        ];

        if ($this->hasCashSalesColumn()) {
            if ($isCash) {
                $updates['cash_sales'] = max(0, $register->cash_sales - $amount);
            } else {
                $updates['non_cash_sales'] = max(0, $register->non_cash_sales - $amount);
            }
        }

        if ($this->hasExpectedBalanceColumn() && $isCash) {
            $updates['expected_balance'] = max(0, $register->expected_balance - $amount);
        }

        $register->update($updates);
    }
}
