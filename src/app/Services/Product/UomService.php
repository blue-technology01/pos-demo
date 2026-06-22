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
        $query = Uom::query()->select('code', 'name', 'status', 'created_at');
        // filter
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        } else {
            // default: only active data
            $query->where('status', 'active');
        }
        // sorting
        match ($request->get('sort', 'newest')) {
            'oldest'    => $query->oldest('created_at'),
            'name_asc'  => $query->orderBy('name', 'asc'),
            'name_desc' => $query->orderBy('name', 'desc'),
            'code_asc'  => $query->orderBy('code', 'asc'),
            default     => $query->latest('created_at'),
        };
        // pagination
        $perPage = (int) $request->get('per_page', 15);

        return $query
            ->paginate($perPage)
            ->withQueryString();
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
