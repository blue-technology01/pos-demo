<?php

namespace App\Services\Inventory;

use App\Models\StockMovement;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class StockMovementService
{
    public const TYPES = [
        'sale',
        'purchase',
        'transfer_in',
        'transfer_out',
        'adjustment',
        'return',
    ];

    // movement types that reduce stock
    public const OUTBOUND_TYPES = ['sale', 'transfer_out'];

    // movement types that increase stock
    public const INBOUND_TYPES = ['purchase', 'transfer_in', 'return'];

    // record a raw stock movement
    public function record(array $data): StockMovement
    {
        // check have type or not
        if (!in_array($data['movement_type'], self::TYPES, true)) {
            throw new \InvalidArgumentException("Invalid movement_type: {$data['movement_type']}");
        }

        return StockMovement::create([
            'created_by'    => $data['created_by'],
            'product_code'  => $data['product_code'],
            'quantity'      => $data['quantity'],
            'movement_type' => $data['movement_type'],
        ]);
    }

    // record a sale movement
    public function recordSale(string $productCode, int $quantity, int $userId): StockMovement
    {
        return $this->record([
            'created_by'    => $userId,
            'product_code'  => $productCode,
            'quantity'      => -abs($quantity),
            'movement_type' => 'sale',
        ]);
    }

    // current stock level for a product
    public function getCurrentStock(string $productCode): int
    {
        return (int) StockMovement::query()
            ->where('product_code', $productCode)
            ->sum('quantity');
    }

    // all movement for a given product
    public function forProduct(string $productCode): Collection
    {
        return StockMovement::query()
            ->with(['product', 'createdBy'])
            ->where('product_code', $productCode)
            ->latest('created_at')
            ->get();
    }

    // get stock movement like pagination
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = StockMovement::query()
            ->with(['product', 'createdBy']);

        $this->applyFilters($query, $filters);

        return $query->latest('created_at')->paginate($perPage);
    }

    // apply request filter to the query
    protected function applyFilters($query, array $filters): void
    {
        if (! empty($filters['product_code'])) {
            $query->where('product_code', 'like', '%' . $filters['product_code'] . '%');
        }

        if (! empty($filters['movement_type'])) {
            $query->where('movement_type', $filters['movement_type']);
        }

        if (! empty($filters['created_by'])) {
            $query->where('created_by', $filters['created_by']);
        }

        if (! empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (! empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }
    }
}
