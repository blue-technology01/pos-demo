<?php

namespace App\Services\Product;

use App\Models\ProductUom;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductUomService
{
    // get all uom filter
    public function getAll(Request $request): LengthAwarePaginator
    {
        return ProductUom::query()
            ->select([
                'id',
                'product_code',
                'uom_code',
                'quantity_per_unit',
                'cost_price',
                'selling_price',
                'barcode',
                'is_default',
                'created_at'
            ])
            ->with([
                'product:code,name',
                'uom:code,name'
            ])

            ->when($request->product_code, function ($query) use ($request) {
                $query->where('product_code', $request->product_code);
            })
            ->when($request->uom_code, function ($query) use ($request) {
                $query->where('uom_code', $request->uom_code);
            })
            ->when($request->has('is_default') && $request->is_default !== null && $request->is_default !== '', function ($query) use ($request) {
                $query->where('is_default', $request->is_default);
            })
            ->orderByDesc('id')
            ->paginate($request->per_page ?? 15);
    }

    public function findOrFail(int $id): ProductUom
    {
        return ProductUom::with(['product', 'uom'])->findOrFail($id);
    }

    // create product uoms
    public function create(array $data): ProductUom
    {
        return DB::transaction(function () use ($data) {
            // Force boolean interpretation of the incoming view data
            $isDefault = isset($data['is_default']) && filter_var($data['is_default'], FILTER_VALIDATE_BOOLEAN);

            $productUom = ProductUom::create([
                'product_code'      => $data['product_code'],
                'uom_code'          => $data['uom_code'],
                'quantity_per_unit' => $data['quantity_per_unit'] ?? 1,
                'cost_price'        => $data['cost_price'] ?? 0,
                'selling_price'     => $data['selling_price'] ?? 0,
                'barcode'           => $data['barcode'] ?? null,
                'is_default'        => $isDefault,
            ]);

            // If this is set as default, handle resetting other UOMs for this product
            if ($isDefault) {
                $this->setDefault($productUom->id);
            }

            return $productUom;
        });
    }

    // update product uoms
    public function update(int $id, array $data): ProductUom
    {
        return DB::transaction(function () use ($id, $data) {
            $uom = ProductUom::findOrFail($id);

            // Checkboxes send nothing if unchecked. Convert explicitly.
            $isDefault = isset($data['is_default']) && filter_var($data['is_default'], FILTER_VALIDATE_BOOLEAN);

            $uom->update([
                'quantity_per_unit' => $data['quantity_per_unit'] ?? $uom->quantity_per_unit,
                'cost_price'        => $data['cost_price'] ?? $uom->cost_price,
                'selling_price'     => $data['selling_price'] ?? $uom->selling_price,
                'barcode'           => $data['barcode'] ?? $uom->barcode,
                'is_default'        => $isDefault,
            ]);

            // If toggled to true, run your structural reset routine
            if ($isDefault) {
                $this->setDefault($uom->id);
            }

            return $uom->fresh();
        });
    }

    // remove product uom
    public function delete(int $id): bool
    {
        return ProductUom::findOrFail($id)->delete();
    }

    // set default uom
    public function setDefault(int $id): ProductUom
    {
        $uom = ProductUom::findOrFail($id);

        // reset others for this specific product hierarchy
        ProductUom::where('product_code', $uom->product_code)
            ->where('id', '!=', $id) // Avoid updating target row twice
            ->update(['is_default' => false]);

        // set new default
        $uom->update(['is_default' => true]);

        return $uom->fresh();
    }

    // get uom by product
    public function getByProduct(string $productCode)
    {
        return ProductUom::with('uom')
            ->where('product_code', $productCode)
            ->get();
    }
}
