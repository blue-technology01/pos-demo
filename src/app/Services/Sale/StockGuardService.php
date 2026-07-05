<?php

namespace App\Services\Sale;

use App\Models\BlockedSaleAttempt;
use App\Models\ProductUom;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StockGuardService
{
    public function checkAndBlock(int $productUomId, float $qty): array
    {
        $productUom = ProductUom::with('product')->findOrFail($productUomId);

        $baseStock = $productUom->product->stock ?? 0;
        $divisor   = $productUom->quantity_per_unit > 0 ? $productUom->quantity_per_unit : 1;
        $stock     = $baseStock / $divisor;
        $minStock  = $productUom->product->min_stock ?? 0;

        if ($stock <= 0) {
            $reason  = 'out_of_stock';
            $allowed = false;
        } elseif ($stock < $qty) {
            $reason  = 'insufficient_stock';
            $allowed = false;
        } else {
            // Enough stock to fulfill the sale always allowed,
            // even if it drops below min_stock (that's just a low-stock warning).
            $reason  = ($stock - $qty) < $minStock ? 'low_stock_warning' : 'available';
            $allowed = true; // always true here, never blocked for low stock
        }

        if (!$allowed) {
            try {
                BlockedSaleAttempt::create([
                    'product_uom_id'  => $productUom->id,
                    'requested_qty'   => $qty,
                    'available_stock' => $stock,
                    'reason'          => $reason,
                    'user_id'         => Auth::id(),
                ]);
            } catch (\Throwable $e) {
                Log::error('STOCK_GUARD_INSERT_FAILED', [
                    'message'        => $e->getMessage(),
                    'product_uom_id' => $productUom->id,
                    'qty'            => $qty,
                    'stock'          => $stock,
                    'reason'         => $reason,
                ]);
                throw $e;
            }
        }

        return [
            'allowed'       => $allowed,
            'reason'        => $reason,
            'product_name'  => $productUom->product->name,
            'current_stock' => $stock,
            'requested_qty' => $qty,
        ];
    }
}
