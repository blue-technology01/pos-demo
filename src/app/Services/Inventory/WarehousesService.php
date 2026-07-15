<?php

namespace App\Services\Inventory;

use App\Models\Warehouse;
use Illuminate\Pagination\LengthAwarePaginator;

class WarehousesService
{
    // get warehouses filtered by search / status, paginated
    public function getFilteredWarehouses(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Warehouse::query();

        // search across name / location / phone
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('location', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // status filter
        if (isset($filters['is_active']) && $filters['is_active'] !== '' && $filters['is_active'] !== null) {
            $query->where('is_active', (bool) $filters['is_active']);
        }

        $allowedPerPage = [15, 25, 50];
        $perPage = in_array($perPage, $allowedPerPage, true) ? $perPage : 15;

        return $query->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }

    //  create new warehouse
    public function createWarehouse(array $data) {
        return Warehouse::create($data);
    }

    // update warehouse
    public function updateWarehouse(Warehouse $warehouses, array $data) {

        $warehouses->update($data);

        return $warehouses;
    }

    // remove warehouse
    public function deleteWarehouse(Warehouse $warehouse)
    {
        if ($warehouse->stockMovements()->exists() || $warehouse->stockAdjustments()->exists()) {
            throw new \Exception("Can't be remove this warehouse,becuase have product!");
        }
        return $warehouse->delete();
    }

    // get warehouse that active
    public function getActiveWarehouse() {

        return Warehouse::where('is_active', true)->get();

    }
}
