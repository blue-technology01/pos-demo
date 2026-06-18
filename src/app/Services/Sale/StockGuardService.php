<?php

namespace App\Services\Sale;

use App\Models\BlockedSaleAttempt;
use App\Models\ProductUom;
use Illuminate\Support\Facades\Auth;

class StockGuardService
{
    public function checkAndBlock(int $productUomId, float $qty):  array {
        $productUom = ProductUom::with('product')->findOrFail($productUomId);
        $stock = $productUom->stock ?? 0;
        if ($stock <= 0) {
            $reason = 'out_of_stock';
            $allowed = false;
        } elseif ($stock < $qty) {
            $reason = 'insufficient_stock'; // distinct reason is more useful
            $allowed = false;
        } else {
            $reason = 'available';
            $allowed = true;
        }
        if(!$allowed) {
            BlockedSaleAttempt::create([
                'product_uom_id' => $productUom->id,
                'requested_qty' => $qty,
                'available_stock' => $stock,
                'reason' => $reason,
                'user_id' => Auth::id(),
            ]);
        }
        return [
            'allowed' => $allowed,
            'reason' => $reason,
            'product_name' => $productUom->product->name,
            'current_stock' => $stock,
            'requested_qty' => $qty,
        ];
    }
}
