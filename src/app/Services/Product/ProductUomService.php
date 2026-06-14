<?php

namespace App\Services\Product;

use App\Models\ProductUom;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductUomService
{
   public function getPOSProducts(Request $request): LengthAwarePaginator
    {
        $query = DB::table('products as p')
            ->select([
                'p.code',
                'p.name',
                'p.image',
                'p.category_code',
                'p.stock',
                'p.min_stock',
            ])
            ->where('p.status', 'active');

        if ($request->category_code) {
            $query->where('p.category_code', $request->category_code);
        }

        if ($search = $request->search) {
            $query->where('p.name', 'like', "%{$search}%");
        }

        $paginator = $query
            ->orderBy('p.name')
            ->paginate($request->per_page ?? 20);

        // Pre-load all UOMs for this page's products in one query
        $codes = $paginator->getCollection()->pluck('code')->toArray();

        $uomsByProduct = DB::table('product_uoms as pu')
            ->leftJoin('uoms as u', 'u.code', '=', 'pu.uom_code')
            ->whereIn('pu.product_code', $codes)
            ->select([
                'pu.id', 'pu.product_code', 'pu.uom_code',
                'pu.quantity_per_unit', 'pu.selling_price',
                'pu.cost_price', 'pu.barcode', 'pu.uom_role',
                'pu.is_default', 'u.name as uom_name',
            ])
            ->get()
            ->groupBy('product_code');

        $paginator->getCollection()->transform(function ($item) use ($uomsByProduct) {
            $uoms = ($uomsByProduct[$item->code] ?? collect())->map(fn($u) => [
                'id'                => $u->id,
                'product_code'      => $u->product_code,
                'uom_code'          => $u->uom_code,
                'uom_name'          => $u->uom_name ?? $u->uom_code,
                'quantity_per_unit' => (float) $u->quantity_per_unit,
                'selling_price'     => (float) $u->selling_price,
                'cost_price'        => (float) $u->cost_price,
                'barcode'           => $u->barcode,
                'uom_role'          => $u->uom_role,
                'is_default'        => (bool) $u->is_default,
            ])->values()->toArray();

            return [
                'product_code'  => $item->code,
                'product_name'  => $item->name,
                'product_image' => $item->image,
                'category_code' => $item->category_code,
                'stock'         => (float) $item->stock,
                'min_stock'     => (float) $item->min_stock,
                'low_stock'     => $item->stock <= $item->min_stock,
                'uoms'          => $uoms,
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
                'p.name as product_name',
                'u.name as uom_name',
            ])
            ->join('products as p', 'p.code', '=', 'pu.product_code')
            ->leftJoin('uoms as u', 'u.code', '=', 'pu.uom_code')
            ->orderBy('pu.id', 'desc');

        if ($request->product_code) {
            $query->where('pu.product_code', $request->product_code);
        }
        if ($request->uom_code) {
            $query->where('pu.uom_code', $request->uom_code);
        }
        if ($request->filled('is_default')) {
            $query->where('pu.is_default', $request->boolean('is_default'));
        }

        return $query->paginate($request->per_page ?? 15);
    }

    public function findOrFail(int $id): ProductUom
    {
        return ProductUom::with(['product', 'uom'])->findOrFail($id);
    }

    public function create(array $data): ProductUom
    {
        return DB::transaction(function () use ($data) {
            $isDefault = !empty($data['is_default']);

            $productUom = ProductUom::create([
                'product_code'      => $data['product_code'],
                'uom_code'          => $data['uom_code'],
                'quantity_per_unit' => $data['quantity_per_unit'] ?? 1,
                'cost_price'        => $data['cost_price'] ?? 0,
                'selling_price'     => $data['selling_price'] ?? 0,
                'barcode'           => $data['barcode'] ?? null,
                'is_default'        => $isDefault,
                'uom_role'          => $data['uom_role'] ?? 'retail',
            ]);

            if ($isDefault) {
                $this->setDefault($productUom);
            }

            return $productUom;
        });
    }

    public function update(int $id, array $data): ProductUom
    {
        return DB::transaction(function () use ($id, $data) {
            $uom = $this->findOrFail($id);

            $isDefault = !empty($data['is_default']);

            $uom->update([
                'quantity_per_unit' => $data['quantity_per_unit'] ?? $uom->quantity_per_unit,
                'cost_price'        => $data['cost_price'] ?? $uom->cost_price,
                'selling_price'     => $data['selling_price'] ?? $uom->selling_price,
                'barcode'           => $data['barcode'] ?? $uom->barcode,
                'uom_role'          => $data['uom_role'] ?? $uom->uom_role,
                'is_default'        => $isDefault,
            ]);

            if ($isDefault) {
                $this->setDefault($uom);
            }

            return $uom->fresh();
        });
    }

    public function delete(int $id): bool
    {
        return $this->findOrFail($id)->delete();
    }

    public function setDefault(ProductUom $uom): ProductUom
    {
        ProductUom::where('product_code', $uom->product_code)
            ->where('id', '!=', $uom->id)
            ->update(['is_default' => false]);

        $uom->update(['is_default' => true]);

        return $uom->fresh();
    }

    public function getByProduct(string $productCode)
    {
        return ProductUom::with('uom')
            ->where('product_code', $productCode)
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
