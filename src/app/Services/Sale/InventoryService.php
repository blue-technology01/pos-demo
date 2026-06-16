<?php

namespace App\Services\Sale;

use App\Models\Product;
use App\Models\ProductUom;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    /**
     * Validate, check, and deduct stock for one sale item.
     *
     * NOTE: No DB::transaction() here — SaleService owns the outer transaction,
     * so all stock changes roll back automatically if anything else in the sale fails.
     */
    public function deductStockWithCheck(string $productCode, string $uomCode, float $qty): void
    {
        // Guard: quantity must be positive
        if ($qty <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }

        // Lock the product row to prevent race conditions
        $product = Product::where('code', $productCode)
            ->lockForUpdate()
            ->firstOrFail();

        // Resolve UOM multiplier — e.g. 1 carton = 24 bottles
        $uom = ProductUom::where('product_code', $productCode)
            ->where('uom_code', $uomCode)
            ->first();

        if (!$uom) {
            throw new \Exception("UOM '{$uomCode}' not found for product '{$productCode}'.");
        }

        // Real base units to deduct from stock
        $requiredStock = $qty * $uom->quantity_per_unit;

        // Check stock is sufficient before deducting
        if ($product->stock < $requiredStock) {
            throw ValidationException::withMessages([
                'stock' => "{$product->name} is out of stock.",
            ]);
        }

        // Deduct stock
        $product->decrement('stock', $requiredStock);

        // Log the movement (calculate remaining locally — no extra DB query)
        Log::info('Stock deducted', [
            'product_code' => $productCode,
            'uom_code'     => $uomCode,
            'qty'          => $qty,
            'deducted'     => $requiredStock,
            'remaining' => $product->fresh()->stock,

        ]);
    }

    /**
     * Restore stock when a sale is cancelled or updated.
     *
     * NOTE: Same as above — caller (SaleService) owns the transaction.
     */
    public function restoreStock(string $productCode, string $uomCode, float $qty): void
    {
        if ($qty <= 0) {
            return;
        }

        $product = Product::where('code', $productCode)
            ->lockForUpdate()
            ->first();

        // Product may have been deleted — skip silently
        if (!$product) {
            return;
        }

        // Resolve UOM multiplier — restore same base units that were originally deducted
        $uom = ProductUom::where('product_code', $productCode)
            ->where('uom_code', $uomCode)
            ->first();

        $qtyPerUnit    = $uom ? $uom->quantity_per_unit : 1.0;
        $restoreAmount = $qty * $qtyPerUnit;

        $product->increment('stock', $restoreAmount);

        Log::info('Stock restored', [
            'product_code' => $productCode,
            'uom_code'     => $uomCode,
            'qty'          => $qty,
            'restored'     => $restoreAmount,
            'remaining' => $product->fresh()->stock,
        ]);
    }
}
