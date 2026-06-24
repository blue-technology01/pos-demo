<?php

namespace App\Services\Product;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    /**
     * Get products for Admin listing (keeps full Eloquent models)
     */
    public function getForAdmin(Request $request)
    {
        $query = Product::query()
            ->select([
                'code',
                'name',
                'category_code',
                'price',
                'cost_price',
                'stock',
                'min_stock',
                'barcode',
                'image',
                'status',
                'created_at'
            ])
            // load only category
            ->with(['category:code,name'])
            ->orderBy('code');

        // search
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', $search)
                ->orWhere('barcode', $search)
                ->orWhere('name', 'like', "%{$search}%");
            });
        }

        // date range
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        } elseif ($request->start_date) {
            $query->where('created_at', '>=', $request->start_date . ' 00:00:00');
        } elseif ($request->end_date) {
            $query->where('created_at', '<=', $request->end_date . ' 23:59:59');
        }


        // category
        if ($categoryCode = $request->category_code) {
            $query->where('category_code', $categoryCode);
        }

        return $query
            ->paginate($request->per_page ?? 15)
            ->withQueryString();
    }

    /**
     * Get all active products with pagination returns arrays
     */
    public function getAll(Request $request)
    {
        return Product::query()
            ->with(['uoms.uom'])
            ->where('status', 'active')
            ->paginate($request->query('per_page', 15))
            ->through(function ($product) {

                // get retail UOM for POS grid
                $retailUom = $product->uoms->firstWhere('uom_role', 'retail')
                    ?? $product->uoms->firstWhere('is_default', true)
                    ?? $product->uoms->first();

                return [
                    'code' => $product->code,
                    'name' => $product->name,
                    'price' => (float) $product->price,
                    'cost_price' => (float) $product->cost_price,
                    'stock' => (float) $product->stock,
                    'barcode' => $product->barcode,
                    'category_code' => $product->category_code,
                    'image' => $product->image,

                    // FAST POS DISPLAY (ONLY ONE UOM)
                    'uom' => [
                        'uom_code'          => $retailUom->uom_code ?? null,
                        'uom_name'          => $retailUom->uom->name ?? null,
                        'quantity_per_unit' => (float) ($retailUom->quantity_per_unit ?? 1),
                        'selling_price'     => (float) ($retailUom->selling_price ?? 0),
                        'cost_price'        => (float) ($retailUom->cost_price ?? 0),
                        'barcode'           => $retailUom->barcode ?? null,
                        'is_default'        => (bool) ($retailUom->is_default ?? false),
                        'uom_role'          => $retailUom->uom_role ?? 'retail',
                    ],

                    // FULL MATRIX (only for product detail page)
                    'uom_matrix' => $product->uoms->map(function ($uom) {
                        return [
                            'uom_code'          => $uom->uom_code,
                            'uom_name'          => $uom->uom->name ?? $uom->uom_code,
                            'quantity_per_unit' => (float) $uom->quantity_per_unit,
                            'cost_price'        => (float) $uom->cost_price,
                            'selling_price'     => (float) $uom->selling_price,
                            'is_default'        => (bool) $uom->is_default,
                            'uom_role'          => $uom->uom_role ?? 'retail',
                            'barcode'           => $uom->barcode,
                        ];
                    })->values()->toArray(),
                ];
            });
    }

    /**
     * Find a product by code or throw 404.
     */
    public function findOrFail(string $code): Product
    {
        return Product::with(['uoms.uom'])->findOrFail($code);
    }

    /**
     * Create a new product.
     */
    public function create(array $data): Product
    {
        // Handle image upload
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $data['image']->store('products', 'public');
        }

        return Product::create([
            'code'          => $data['code'],
            'name'          => $data['name'],
            'category_code' => $data['category_code'] ?? null,
            'cost_price'    => $data['cost_price'] ?? 0,
            'price'         => $data['price'] ?? 0,
            'stock'         => $data['stock'] ?? 0,
            'min_stock'     => $data['min_stock'] ?? 0,
            'barcode'       => $data['barcode'] ?? null,
            'description'   => $data['description'] ?? null,
            'image'         => $data['image'] ?? null,
            'expiry_date'   => $data['expiry_date'] ?? null,
            'status'        => $data['status'] ?? 'active',
        ]);
    }

    /**
     * Update existing product by code.
     */
    public function update(string $code, array $data): Product
    {
        $product = $this->findOrFail($code);

        // Handle image update
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {

            // delete old image
            if ($product->image && Storage::disk('public')->exists($product->image)) {
                Storage::disk('public')->delete($product->image);
            }

            $data['image'] = $data['image']->store('products', 'public');
        }

        $product->update($data);

        return $product->fresh();
    }

    public function getCategories()
    {
        return Category::where('status', 'active')->orderBy('name')->get();
    }

    /**
     * Deactivate product (soft delete via status).
     */
    public function deactivate(string $code): Product
    {
        $product = $this->findOrFail($code);

        $product->update([
            'status' => 'inactive'
        ]);

        return $product->fresh();
    }
}
