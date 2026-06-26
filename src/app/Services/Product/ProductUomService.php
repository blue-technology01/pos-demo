<?php

namespace App\Services\Product;

use App\Models\ProductUom;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\Paginator;

class ProductUomService
{
    public function getPOSProducts(Request $request): Paginator
    {
        $perPage = (int) $request->get('per_page', 25);

        $query = DB::table('products as p')
            ->select([
                'p.code as product_code',
                'p.name as product_name',
                'p.image as product_image',
                'p.category_code',
                'p.stock',
                'p.min_stock',
            ])
            ->where('p.status', 'active');

        if ($request->filled('category_code') && $request->category_code !== 'all') {
            $query->where('p.category_code', $request->category_code);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('p.name', 'like', "{$search}%")
                ->orWhere('p.code', 'like', "{$search}%");
            });
        }

        $paginator = $query
            ->orderBy('p.name')
            ->simplePaginate($perPage);

        $products = $paginator->getCollection();
        $codes    = $products->pluck('product_code')->all();

        if (empty($codes)) {
            return $paginator;
        }

        // join uoms table to get the display name
        $uoms = DB::table('product_uoms as pu')
            ->join('uoms as u', 'u.code', '=', 'pu.uom_code')
            ->select([
                'pu.product_code',
                'pu.uom_code',
                'u.name as uom_name',
                'pu.quantity_per_unit',
                'pu.selling_price',
                'pu.cost_price',
                'pu.is_default',
            ])
            ->whereIn('pu.product_code', $codes)
            ->where('pu.is_active', 1)
            ->orderByDesc('pu.is_default')
            ->get();

        $uomMap = [];
        foreach ($uoms as $u) {
            $uomMap[$u->product_code][] = [
                'uom_code'         => $u->uom_code,
                'uom_name'         => $u->uom_name,             //now has real name
                'quantity_per_unit'=> (float) $u->quantity_per_unit,
                'selling_price'    => (float) $u->selling_price,
                'cost_price'       => (float) $u->cost_price,
                'is_default'       => (bool)  $u->is_default,
            ];
        }

        $products->transform(function ($p) use ($uomMap) {
            return [
                'product_code'  => $p->product_code,
                'product_name'  => $p->product_name,
                'product_image' => $p->product_image,
                'category_code' => $p->category_code,
                'stock'         => (float) $p->stock,
                'min_stock'     => (float) $p->min_stock,
                'low_stock'     => $p->stock <= $p->min_stock,
                'uoms'          => $uomMap[$p->product_code] ?? [],
            ];
        });

        return $paginator;
    }


    public function getAll(Request $request): LengthAwarePaginator
    {
        $query = DB::table('product_uoms as pu')
            ->select([
                'pu.id',
                'pu.product_code',
                'pu.uom_code',
                'pu.quantity_per_unit',
                'pu.cost_price',
                'pu.selling_price',
                'pu.barcode',
                'pu.uom_role',
                'pu.is_default',
                'pu.created_at',

                // safe joins
                'p.name as product_name',
                'u.name as uom_name',
            ])
            ->leftJoin('products as p', 'p.code', '=', 'pu.product_code')
            ->leftJoin('uoms as u', 'u.code', '=', 'pu.uom_code')
            ->where('pu.is_active', 1)
            ->where('p.status', 'active')
            ->orderByDesc('pu.id');

         if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('p.name', 'like', "%{$search}%")
                ->orWhere('pu.barcode', 'like', "%{$search}%")
                ->orWhere('pu.product_code', 'like', "%{$search}%")
                ->orWhere('pu.uom_code', 'like', "%{$search}%");
            });
        }
        // Product filter
        if ($request->filled('product_code')) {
            $query->where('pu.product_code', $request->product_code);
        }
        // UOM filter
        if ($request->filled('uom_code')) {
            $query->where('pu.uom_code', $request->uom_code);
        }
        // Default filter
        if ($request->has('is_default') && $request->is_default !== '') {
            $query->where('pu.is_default', (int) $request->is_default);
        }
        return $query
            ->orderByDesc('pu.id')
            ->paginate((int) $request->get('per_page', 15))
            ->withQueryString();
    }

    public function findOrFail(int $id): ProductUom
    {
        return ProductUom::with(['product', 'uom'])
            ->where('is_active', 1)
            ->findOrFail($id);
    }

    public function create(array $data): ProductUom
    {
        return DB::transaction(function () use ($data) {

            // Check duplicate first
            $exists = ProductUom::where('product_code', $data['product_code'])
                ->where('uom_code', $data['uom_code'])
                ->first();

            if ($exists) {
                throw new \Exception('This UOM already exists for this product.');
            }

            $isDefault = !empty($data['is_default']);

            // If default → remove other defaults first
            if ($isDefault) {
                ProductUom::where('product_code', $data['product_code'])
                    ->update(['is_default' => false]);
            }

            $productUom = ProductUom::create([
                'product_code'      => $data['product_code'],
                'uom_code'          => $data['uom_code'],
                'quantity_per_unit' => $data['quantity_per_unit'] ?? 1,
                'cost_price'        => $data['cost_price'] ?? 0,
                'selling_price'     => $data['selling_price'] ?? 0,
                'barcode'           => $data['barcode'] ?? null,
                'is_default'        => $isDefault,
                'uom_role'          => $data['uom_role'] ?? 'retail',
                'is_active'         => true,
            ]);

            return $productUom;
        });
    }

    public function update(int $id, array $data): ProductUom
    {
        return DB::transaction(function () use ($id, $data) {

            $uom = ProductUom::where('is_active', 1)
                ->findOrFail($id);
             $uom->update([
                'quantity_per_unit' => $data['quantity_per_unit'] ?? $uom->quantity_per_unit,
                'cost_price'        => $data['cost_price'] ?? $uom->cost_price,
                'selling_price'     => $data['selling_price'] ?? $uom->selling_price,
                'barcode'           => $data['barcode'] ?? $uom->barcode,
                'uom_role'          => $data['uom_role'] ?? $uom->uom_role,
                'is_active'         => $data['is_active'] ?? $uom->is_active,
            ]);
            if (!empty($data['is_default'])) {
                $this->setDefault($uom);
            }
            return $uom->fresh();
        });
    }

    public function delete(int $id): ProductUom
    {
        $uom = $this->findOrFail($id);

        $productCode = $uom->product_code;

        $uom->update([
            'is_active' => false,
            'is_default' => false,
        ]);

        ProductUom::where('product_code', $productCode)
            ->where('is_active', 1)
            ->orderBy('id')
            ->first()
            ?->update(['is_default' => true]);

        return $uom->fresh();
    }

    public function setDefault(ProductUom $uom): ProductUom
    {
        // only active UOMs can be affected
        ProductUom::where('product_code', $uom->product_code)
            ->where('is_active', 1)
            ->where('id', '!=', $uom->id)
            ->update(['is_default' => false]);

        $uom->update([
            'is_default' => true,
            'is_active' => true, // safety rule
        ]);

        return $uom->fresh();
    }

    public function getByProduct(string $productCode)
    {
        return ProductUom::with('uom')
            ->where('product_code', $productCode)
            ->where('is_active', 1)
            ->orderByDesc('is_default')
            ->orderBy('id')
            ->get()
            ->map(fn($item) => [
                'id'                => $item->id,
                'product_code'      => $item->product_code,
                'uom_code'          => $item->uom_code,
                'uom_name'          => $item->uom->name ?? $item->uom_code,
                'quantity_per_unit' => (float) $item->quantity_per_unit,
                'cost_price'        => (float) $item->cost_price,
                'selling_price'     => (float) $item->selling_price,
                'uom_role'          => $item->uom_role,
                'barcode'           => $item->barcode,
                'is_default'        => (bool) $item->is_default,
            ]);
    }
}
