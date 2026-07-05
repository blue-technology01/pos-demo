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

class SaleService
{
    protected $cashRegisterService;
    protected $inventoryService;
    protected $stockGuardService;

    private static ?bool $hasCashSalesColumn       = null;
    private static ?bool $hasExpectedBalanceColumn = null;

    public function __construct(
        CashRegisterService $cashRegisterService,
        InventoryService    $inventoryService,
        StockGuardService   $stockGuardService
    ) {
        $this->cashRegisterService = $cashRegisterService;
        $this->inventoryService    = $inventoryService;
        $this->stockGuardService   = $stockGuardService;
    }
    private function hasCashSalesColumn(): bool
    {   
        // Check if the cash_sales column exists in the cash_registers table
        if (self::$hasCashSalesColumn === null) {
            self::$hasCashSalesColumn = Schema::hasColumn('cash_registers', 'cash_sales');
        }
        return self::$hasCashSalesColumn;
    }

    private function hasExpectedBalanceColumn(): bool
    {
        if (self::$hasExpectedBalanceColumn === null) {
            self::$hasExpectedBalanceColumn = Schema::hasColumn('cash_registers', 'expected_balance');
        }
        return self::$hasExpectedBalanceColumn;
    }
    /**
     * Return the product_uoms row for a given product + UOM combination, or null.
     * A missing row is NOT an error — some products use a simple base UOM (e.g. UNIT)
     * that is stored in the uoms table but has no product_uoms entry.
     */
    private function getProductUom(string $productCode, ?string $uomCode): ?object
    {
        if (!$uomCode) {
            return null;
        }

        return ProductUom::where('product_code', $productCode)
            ->where('uom_code', $uomCode)
            ->first();
    }

    /**
     * Resolve cost price for a sale item.
     * Falls back to the product's own cost_price when no product_uom row exists.
     */
    private function resolveCostPrice(?object $productUom, Product $product): float
    {
        return (float) ($productUom?->cost_price ?? $product->cost_price ?? 0);
    }
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
                $start = $request->start_date ? Carbon::parse($request->start_date) : null;
                $end   = $request->end_date   ? Carbon::parse($request->end_date)   : null;

                if ($start && $end && $start->gt($end)) {
                    [$start, $end] = [$end, $start];
                }

                if ($start && $end) {
                    $q->whereBetween('sale_date', [$start->startOfDay(), $end->endOfDay()]);
                } elseif ($start) {
                    $q->where('sale_date', '>=', $start->startOfDay());
                } elseif ($end) {
                    $q->where('sale_date', '<=', $end->endOfDay());
                }
            })
            ->orderByDesc('id')
            ->paginate($request->per_page ?? 15)
            ->withQueryString();
    }
    public function confirmSale(array $data): Sale
    {
        return $this->createSale($data);
    }

    public function createSale(array $data): Sale
    {
        // Pre-validate stock BEFORE opening the transaction so blocked
        // attempts are logged even when the sale does not proceed.
        $validatedItems = [];

        foreach ($data['items'] as $item) {
            // productUom can be null for base UOMs.
            // We no longer throw here — stock guard uses the product_uom id
            // only when the row exists.
            $productUom = $this->getProductUom(
                $item['product_code'],
                $item['uom_code'] ?? null
            );

            // Only run the stock guard when we have a product_uom row.
            // Products with a base UOM (no product_uoms entry) skip the guard
            // and stock is deducted directly by InventoryService.
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
                    ->lockForUpdate()
                    ->firstOrFail();

                $this->inventoryService->deductStockWithCheck(
                    $item['product_code'],
                    $item['uom_code'] ?? null,
                    $item['quantity']
                );

                $sale->items()->create([
                    'product_id'   => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'quantity'     => $item['quantity'],
                    'uom_code'     => $item['uom_code'] ?? null,
                    'cost_price'   => $this->resolveCostPrice($productUom, $product),
                    'unit_price'   => $item['unit_price'],
                    'amount'       => $item['quantity'] * $item['unit_price'],
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
    /**
     *  Update an existing sale. This method will restore stock for the old items,
     *  deduct stock for the new items, and update the sale record.
     */
    public function updateSale(int $id, array $data): Sale
    {
        $sale = Sale::with('items')->findOrFail($id);

        if ($sale->status === 'voided') {
            throw new \Exception('Voided receipts cannot be edited.');
        }

        return DB::transaction(function () use ($sale, $data) {

            // Restore stock for every old item
            foreach ($sale->items as $oldItem) {
                $this->inventoryService->restoreStock(
                    $oldItem->product_code,
                    $oldItem->uom_code,
                    $oldItem->quantity
                );
            }

            if ($sale->status === 'completed') {
                $this->reverseCashTransaction($sale);
            }

            $sale->items()->delete();

            foreach ($data['items'] as $item) {
                $product = Product::where('code', $item['product_code'])
                    ->lockForUpdate()
                    ->firstOrFail();

                // productUom may be null for base UOMs
                $productUom = $this->getProductUom(
                    $item['product_code'],
                    $item['uom_code'] ?? null
                );

                if ($productUom) {
                    $check = $this->stockGuardService->checkAndBlock(
                        $productUom->id,
                        $item['quantity']
                    );

                    if (!$check['allowed']) {
                        throw new \Exception("{$check['product_name']} blocked: {$check['reason']}");
                    }
                }

                $this->inventoryService->deductStockWithCheck(
                    $item['product_code'],
                    $item['uom_code'] ?? null,
                    $item['quantity']
                );

                $sale->items()->create([
                    'product_id'   => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'quantity'     => $item['quantity'],
                    'uom_code'     => $item['uom_code'] ?? null,
                    'cost_price'   => $this->resolveCostPrice($productUom, $product),
                    'unit_price'   => $item['unit_price'],
                    'amount'       => $item['quantity'] * $item['unit_price'],
                ]);
            }

            $newStatus = $data['status'] ?? 'completed';

            $sale->update([
                'sale_date'       => $data['sale_date']      ?? now(),
                'sub_total'       => $data['sub_total'],
                'discount_amount' => $data['discount_amount'],
                'tax_amount'      => $data['tax_amount'],
                'total_amount'    => $data['total_amount'],
                'paid_amount'     => $data['paid_amount'],
                'change_amount'   => $data['change_amount'],
                'payment_method'  => $data['payment_method'] ?? 'cash',
                'status'          => $newStatus,
                'note'            => $data['note']           ?? null,
            ]);

            if ($newStatus === 'completed') {
                $this->addCashTransaction(
                    registerId:    $sale->register_id,
                    amount:        (float) $data['total_amount'],
                    paymentMethod: $data['payment_method'] ?? 'cash'
                );
            }

            Log::info('Sale updated', [
                'invoice_no'   => $sale->invoice_no,
                'total_amount' => $data['total_amount'],
                'new_status'   => $newStatus,
            ]);

            return $sale->refresh()->load('items');
        });
    }

    // void / cancel sale
    public function cancelSale(int $id): Sale
    {
        $sale = Sale::with('items')->findOrFail($id);
        if ($sale->status === 'voided') {
            throw new \Exception('This receipt has already been voided.');
        }
        // Only allow voiding completed or pending receipts
        if (!in_array($sale->status, ['completed', 'pending'])) {
            throw new \Exception('Only completed or pending receipts can be voided.');
        }
        // Restore stock for every item and reverse cash transaction if completed
        return DB::transaction(function () use ($sale) {

            foreach ($sale->items as $item) {
                $this->inventoryService->restoreStock(
                    $item->product_code,
                    $item->uom_code,
                    $item->quantity
                );
            }

            if ($sale->status === 'completed') {
                $this->reverseCashTransaction($sale);
            }

            $sale->update(['status' => 'voided']);

            Log::info('Sale voided', ['invoice_no' => $sale->invoice_no]);

            return $sale->refresh();
        });
    }

    /**
     *  cash register helper function to add a cash transaction to the register.
     *  This function will only update the register if it is open.
    */
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
        // Update cash_sales or non_cash_sales if the column exists
        if ($this->hasCashSalesColumn()) {
            if ($isCash) {
                $register->increment('cash_sales', $amount);
            } else {
                $register->increment('non_cash_sales', $amount);
            }
        }
        // Update expected_balance if the column exists and the payment method is cash
        if ($this->hasExpectedBalanceColumn() && $isCash) {
            $register->increment('expected_balance', $amount);
        }
    }

    private function reverseCashTransaction(Sale $sale): void
    {
        $register = CashRegister::where('id', $sale->register_id)
            ->lockForUpdate()
            ->first();

        if (!$register || $register->status !== 'open') {
            return;
        }

        $register->update([
            'total_sales'        => max(0, $register->total_sales - $sale->total_amount),
            'total_transactions' => max(0, $register->total_transactions - 1),
        ]);
    }
}
