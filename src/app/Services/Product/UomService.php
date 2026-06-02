<?php

namespace App\Services\Product;

use App\Models\Uom;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class UomService
{
    /**
     * Get all active uoms with pagination.
     */
    public function getAll(Request $request): LengthAwarePaginator
    {
        return Uom::select('code', 'name', 'status', 'created_at')
            ->where('status', 'active')
            ->paginate($request->query('per_page', 15));
    }
    
    /**
     * Find a uom by code or throw 404.
     */
    public function findOrFail(string $code): Uom
    {
        return Uom::findOrFail($code);
    }

    /**
     * Create a new uom.
     */
    public function create(array $data): Uom
    {
        return Uom::create([
            'code'        => $data['code'],
            'name'        => $data['name'],
            'status'      => $data['status'] ?? 'active',
        ]);
    }

    /**
     * Update an existing uom by code.
     */
    public function update(string $code, array $data): Uom
    {
        $uom = Uom::findOrFail($code);

        $uom->update([
            'name'        => $data['name'] ?? $uom->name,
            'status'      => $data['status'] ?? $uom->status,
        ]);

        return $uom->fresh();
    }

    /**
     * Deactivate a uom (soft delete via status).
     */
    public function deactivate(string $code): Uom
    {
        $uom = Uom::findOrFail($code);
        $uom->update(['status' => 'inactive']);

        return $uom->fresh();
    }
}