<?php

namespace App\Services\Sale;

use App\Models\BlockedSaleAttempt;
use App\Models\ProductUom;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class StockGuardService
{
    // function that write for check stock and block stock
    public function checkAndBlock(int $productUomId, float $qty): array
    {
        Log::info('STOCK_GUARD_CALLED', [
            'product_uom_id' => $productUomId,
            'qty' => $qty,
            'user_id' => Auth::id()
        ]);

        $productUom = ProductUom::with('product')->findOrFail($productUomId);

        $baseStock = $productUom->product->stock ?? 0;
        $divisor   = $productUom->quantity_per_unit > 0 ? $productUom->quantity_per_unit : 1;

        //use bcdiv for accurate
        $stock = (float) bcdiv($baseStock, $divisor, 2);
        $minStock = (float)($productUom->product->min_stock ?? 0);

        // manage stock  return
        if ($stock <= 0) {
            $reason = 'out_of_stock';
            $allowed = false;
        } elseif (bccomp((string)$stock, (string)$qty, 2) === -1) {
            $reason = 'insufficient_stock';
            $allowed = false;
        } else {
            $finalStock = (float) bcsub((string)$stock, (string)$qty, 2);
            $reason = ($finalStock < $minStock) ? 'low_stock_warning' : 'available';
            $allowed = true;
        }

        // note error if stock not enought
        if (!$allowed || ($stock - $qty) <= 0) {
            $this->logBlockedAttempt($productUom, $qty, $stock, $reason);
        }

        return [
            'allowed'       => $allowed,
            'reason'        => $reason,
            'product_name'  => $productUom->product->name,
            'current_stock' => $stock,
            'requested_qty' => $qty,
        ];
    }

    // function that help note log to database
    private function logBlockedAttempt(ProductUom $productUom, float $qty, float $stock, string $reason): void
    {
        $finalStock = (float) bcsub((string)$stock, (string)$qty, 2);  // Binary Calculator Subtraction
        $logReason = ($finalStock <= 0) ? 'out_of_stock' : $reason;

        try {
            $attempt = BlockedSaleAttempt::create([
                'product_uom_id'  => $productUom->id,
                'requested_qty'   => $qty,
                'available_stock' => $finalStock,
                'reason'          => $logReason,
                'user_id'         => Auth::id(),
            ]);

            Log::info('STOCK_GUARD_BLOCKED_ATTEMPT', [
                'attempt_id'      => $attempt->id,
                'product_uom_id'  => $productUom->id,
                'reason'          => $logReason
            ]);
        } catch (\Throwable $e) {
            Log::error('STOCK_GUARD_INSERT_FAILED', [
                'message' => $e->getMessage(),
                'product_uom_id' => $productUom->id
            ]);
        }
    }
}
