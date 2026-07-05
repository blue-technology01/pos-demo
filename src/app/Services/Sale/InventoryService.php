<?php

namespace App\Services\Sale;

use App\Models\Product;
use App\Models\ProductUom;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function deductStockWithCheck(string $productCode, string $uomCode, int|float $qty): void
    {
        if ($qty <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }
        $product = Product::where('code', $productCode)
            ->lockForUpdate()
            ->firstOrFail();
        $uom = ProductUom::where('product_code', $productCode)
            ->where('uom_code', $uomCode)
            ->first();
        if (!$uom) {
            throw new \Exception("UOM '{$uomCode}' not found for product '{$productCode}'.");
        }
        $requiredStock = $qty * $uom->quantity_per_unit;
        // normalize to prevent float precision issues
        $requiredStock = (int) round($requiredStock);
        if ($product->stock < $requiredStock) {
            throw ValidationException::withMessages([
                'stock' => "{$product->name} is out of stock.",
            ]);
        }
        $product->decrement('stock', $requiredStock);
        Log::info('Stock deducted', [
            'product_code' => $productCode,
            'uom_code'     => $uomCode,
            'qty'          => $qty,
            'deducted'     => $requiredStock,
            'remaining'    => $product->stock, // already updated in memory after decrement
        ]);
    }

    public function restoreStock(string $productCode, string $uomCode, int|float $qty): void
    {
        if ($qty <= 0) {
            return;
        }
        $product = Product::where('code', $productCode)->lockForUpdate()->first();
        if (!$product) {
            return;
        }
        $uom = ProductUom::where('product_code', $productCode)->where('uom_code', $uomCode)->first();

        if (!$uom) {
            throw new \Exception("UOM '{$uomCode}' not found for product '{$productCode}'.");
        }

        $restoreAmount = (int) round($qty * $uom->quantity_per_unit);

        $product->increment('stock', $restoreAmount);

        Log::info('Stock restored', [
            'product_code' => $productCode,
            'uom_code'     => $uomCode,
            'qty'          => $qty,
            'restored'     => $restoreAmount,
            'remaining'    => $product->stock,
        ]);
    }
}