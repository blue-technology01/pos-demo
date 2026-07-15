<?php

namespace App\Services\Sale;

use App\Models\Product;
use App\Models\ProductUom;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class InventoryService
{

    // deduct stock for sale item, validating there's enoungh available
    public function deductStockWithCheck(string $productCode, ?string $uomCode, int|float $qty): void
    {
        if ($qty <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }

        $product = Product::where('code', $productCode)
            ->lockForUpdate() // for race conditions when 2 users buy the last unit
            ->firstOrFail();

        $quantityPerUnit = 1; // default: qty is already expressed in base units

        if ($uomCode !== null) {
            $uom = ProductUom::where('product_code', $productCode)
                ->where('uom_code', $uomCode)
                ->first();

            if (!$uom) {
                throw new \Exception("UOM '{$uomCode}' not found for product '{$productCode}'.");
            }

            $quantityPerUnit = $uom->quantity_per_unit;
        }

        // normalize to prevent float precision issues
        $requiredStock = bcmul((string) $qty, (string) $quantityPerUnit, 2);

        if (bccomp((string) $product->stock, $requiredStock, 2) === -1) {
            throw ValidationException::withMessages([
                'stock' => "{$product->name} is out of stock.",
            ]);
        }

        $product->decrement('stock', $requiredStock);
        // decrement() already updates the in-memory attribute, no refresh() needed

        Log::info('Stock deducted', [
            'product_code' => $productCode,
            'uom_code'     => $uomCode,
            'qty'          => $qty,
            'deducted'     => $requiredStock,
            'remaining'    => $product->stock,
        ]);
    }

    // restore stock when sale is voided or cancel
    public function restoreStock(string $productCode, ?string $uomCode, int|float $qty): void
    {
        if ($qty <= 0) {
            return;
        }

        $product = Product::where('code', $productCode)
            ->lockForUpdate()
            ->first();

        if (!$product) {
            return;
        }

        $quantityPerUnit = 1; // default: qty is already expressed in base units

        if ($uomCode !== null) {
            $uom = ProductUom::where('product_code', $productCode)
                ->where('uom_code', $uomCode)
                ->first();

            if (!$uom) {
                throw new \Exception("UOM '{$uomCode}' not found for product '{$productCode}'.");
            }

            $quantityPerUnit = $uom->quantity_per_unit;
        }

        $restoreAmount = bcmul((string) $qty, (string) $quantityPerUnit, 2);

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
