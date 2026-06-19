<?php

namespace App\Services\Sale;

use App\Models\CashRegister;
use App\Models\Sale;
use App\Models\Product;
use App\Models\ProductUom;
use App\Services\Cash\CashRegisterService;
use App\Services\Sale\InventoryService;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SaleService
{
    protected $cashRegisterService;
    protected $inventoryService;
    protected $stockGuardService;

    // Cache schema checks to avoid running inside every transaction
    private static ?bool $hasCashSalesColumn       = null;
    private static ?bool $hasExpectedBalanceColumn = null;

    public function __construct(CashRegisterService $cashRegisterService, InventoryService $inventoryService, StockGuardService $stockGuardService)
    {
        $this->cashRegisterService = $cashRegisterService;
        $this->inventoryService = $inventoryService;
        $this->stockGuardService = $stockGuardService;
    }
    // check column helper
    private function hasCashSalesColumn(): bool
    {
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
     * Resolve the base quantity multiplier for a product/UOM combination.
     * Returns 1 if no UOM record is found (safe default).
     */
    private function getQtyPerUnit(string $productCode, string $uomCode): float
    {
        $productUom = DB::table('product_uoms')
            ->where('product_code', $productCode)
            ->where('uom_code', $uomCode)
            ->first();

        return $productUom ? (float) $productUom->quantity_per_unit : 1.0;
    }

    /**
     * Returns the full product_uom row (or null) for use when creating sale items.
     */
    private function getProductUom(string $productCode, string $uomCode): ?object
    {
        return ProductUom::where('product_code', $productCode)
            ->where('uom_code', $uomCode)
            ->first();
    }
    // listing
    public function getAllSales(Request $request): LengthAwarePaginator
    {
        return Sale::query()
            ->with(['items'])
            ->when($request->invoice_no, fn($q) => $q->where('invoice_no', 'LIKE', "%{$request->invoice_no}%"))
            ->when($request->status,     fn($q) => $q->where('status', $request->status))
            ->when(
                $request->date && !$request->date_from && !$request->date_to,
                fn($q) => $q->whereDate('sale_date', $request->date)
            )
            ->when(
                $request->date_from && $request->date_to,
                fn($q) => $q->whereBetween('sale_date', [$request->date_from, $request->date_to])
            )
            ->orderByDesc('id')
            ->paginate($request->per_page ?? 15);
    }
    // create / confirm
    public function confirmSale(array $data): Sale
    {
        return $this->createSale($data);
    }

    public function createSale(array $data): Sale
    {
        // Pre-validate stock for every item BEFORE opening the transaction,
        // so blocked attempts are logged even if the sale itself doesn't proceed.
        $validatedItems = [];

        foreach ($data['items'] as $item) {
            $productUom = $this->getProductUom(
                $item['product_code'],
                $item['uom_code']
            );

            if (!$productUom) {
                throw new \Exception("UOM not found: {$item['uom_code']}");
            }

            $check = $this->stockGuardService->checkAndBlock($productUom->id, $item['quantity']);

            if (!$check['allowed']) {
                throw new \Exception("{$check['product_name']} blocked: {$check['reason']}");
            }

            $validatedItems[] = [
                'item'        => $item,
                'productUom'  => $productUom,
            ];
        }

        return DB::transaction(function () use ($data, $validatedItems) {

            $invoiceNo = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));

            $sale = Sale::create([
                'invoice_no'      => $invoiceNo,
                'register_id'     => $data['register_id'],
                'user_id'         => Auth::id(),
                'customer_id'     => $data['customer_id'] ?? null,
                'sub_total'       => $data['sub_total'],
                'discount_amount' => $data['discount_amount'] ?? 0,
                'tax_amount'      => $data['tax_amount'] ?? 0,
                'total_amount'    => $data['total_amount'],
                'paid_amount'     => $data['paid_amount'],
                'change_amount'   => $data['change_amount'] ?? 0,
                'payment_method'  => $data['payment_method'] ?? 'cash',
                'sale_date'       => now(),
                'status'          => 'completed',
            ]);

            foreach ($validatedItems as $validated) {
                $item       = $validated['item'];
                $productUom = $validated['productUom'];

                $product = Product::where('code', $item['product_code'])
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    throw new \Exception("Product not found: {$item['product_code']}");
                }

                // Stock was already validated above — deduct directly.
                $this->inventoryService->deductStockWithCheck(
                    $item['product_code'],
                    $item['uom_code'],
                    $item['quantity']
                );

                $sale->items()->create([
                    'product_id'   => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'quantity'     => $item['quantity'],
                    'uom_code'     => $item['uom_code'],
                    'cost_price'   => $productUom->cost_price ?? $product->cost_price,
                    'unit_price'   => $item['unit_price'],
                    'amount'       => $item['quantity'] * $item['unit_price'],
                ]);
            }

            // Update cash register totals
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

    // update sale
    public function updateSale(int $id, array $data): Sale
    {
        $sale = Sale::with('items')->findOrFail($id);

        if ($sale->status === 'voided') {
            // Clear error message
            throw new \Exception('Voided receipts cannot be edited.');
        }

        return DB::transaction(function () use ($sale, $data) {

            // Restore old stock for every old item
            foreach ($sale->items as $oldItem) {
                $this->inventoryService->restoreStock(
                    $oldItem->product_code,
                    $oldItem->uom_code,
                    $oldItem->quantity
                );
            }
            // Reverse old cash transaction if sale was completed
            if ($sale->status === 'completed') {
                $this->reverseCashTransaction($sale);
            }
            // Delete old items
            $sale->items()->delete();
            // Insert new items and deduct new stock
            foreach ($data['items'] as $item) {
                // get product with lock
                $product = Product::where('code', $item['product_code'])
                    ->lockForUpdate()
                    ->first();

                if (!$product) {
                    throw new \Exception("Product not found: {$item['product_code']}");
                }
                // get product uom
                $productUom = $this->getproductUom(
                    $item['product_code'],
                    $item['uom_code']
                );
                if(!$productUom) {
                    throw new \Exception("UOM not found: {$item['uom_code']}");
                }
                // stock check
                $check = $this->stockGuardService->checkAndBlock(
                    $productUom->id,
                    $item['quantity'],
                );

                $this->inventoryService->deductStockWithCheck(
                    $item['product_code'],
                    $item['uom_code'],
                    $item['quantity']
                );

                if(!$check['allowed']) {

                }

                $productUom = $this->getProductUom($item['product_code'], $item['uom_code']);

                $sale->items()->create([
                    'product_id'   => $product->id,
                    'product_code' => $product->code,
                    'product_name' => $product->name,
                    'quantity'     => $item['quantity'],
                    'uom_code'     => $item['uom_code'],
                    'cost_price'   => $productUom ? $productUom->cost_price : $product->cost_price,
                    'unit_price'   => $item['unit_price'],
                    'amount'       => $item['quantity'] * $item['unit_price'],
                ]);
            }

            // Capture new status BEFORE updating, then use it for cash register logic
            $newStatus = $data['status'] ?? 'completed';
            // update sale header
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
            // Add new cash transaction if new status is completed
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

    // Cancel / void
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

            // Restore stock for each item via InventoryService
            foreach ($sale->items as $item) {
                $this->inventoryService->restoreStock(
                    $item->product_code,
                    $item->uom_code,
                    $item->quantity
                );
            }
            // Reverse cash register if sale was completed
            if ($sale->status === 'completed') {
                $this->reverseCashTransaction($sale);
            }

            $sale->update(['status' => 'voided']);

            Log::info('Sale voided', [
                'invoice_no' => $sale->invoice_no,
            ]);

            return $sale->refresh();
        });
    }

    // Cash register helper
    /**
     * Add a sale's amount to the cash register totals.
     * Uses increment() instead of raw DB::raw() to prevent SQL injection.
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
    /**
     * Subtract a completed sale's amount from the cash register.
     * Called when a sale is voided or before an update.
    */
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
