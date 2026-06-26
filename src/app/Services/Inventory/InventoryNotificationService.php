<?php

namespace App\Services\Inventory;

use App\Models\Product;
use Illuminate\Support\Collection;

class InventoryNotificationService
{
    /**
     * Expiry products (next 3 days)
     */
    public function getExpiryNotifications(): Collection
    {
        return Product::query()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(3))
            ->get();
    }

    /**
     * Low stock products
     */
    public function getLowStockNotifications(): Collection
    {
        return Product::query()
            ->whereColumn('stock', '<=', 'min_stock')
            ->get();
    }
}
