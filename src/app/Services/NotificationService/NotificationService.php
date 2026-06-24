<?php

namespace App\Services\NotificationService;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

class NotificationService
{
    /**
     * Get all active products where stock <= min_stock.
     */
    public function getLowStockProducts(): Collection
    {
        return Product::query()
            ->select(['code', 'name', 'stock', 'min_stock', 'image'])
            ->where('status', 'active')
            ->whereColumn('stock', '<=', 'min_stock')
            ->orderBy('stock')
            ->get();
    }

    /**
     * Get count of low stock products.
     */
    public function getLowStockCount(): int
    {
        return Product::query()
            ->where('status', 'active')
            ->whereColumn('stock', '<=', 'min_stock')
            ->count();
    }
}
