<?php

namespace App\Services\Inventory;

use App\Models\Product;
use Illuminate\Support\Collection;

class InventoryNotificationService
{
    // expire products
    public function getExpiryNotifications(): Collection
    {
        return Product::query()
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', now()->addDays(3))
            ->get();
    }

    // low stock
    public function getLowStockNotifications(): Collection
    {
        return Product::query()
            ->whereColumn('stock', '<=', 'min_stock')
            ->get();
    }
}
