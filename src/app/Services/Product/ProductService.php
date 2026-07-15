<?php

namespace App\Services\Product;

use Carbon\Carbon;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ProductService
{

    // get products for admin listing
    public function getForAdmin(Request $request)
    {
        $query = Product::query()
            ->select([
                'code',
                'name',
                'category_code',
                'stock',
                'min_stock',
                'barcode',
                'image',
                'status',
                'expiry_date',
                'created_at'
            ])
            ->where('status','active')
            ->with(['category:code,name'])
            ->orderBy('code');

        // search
        if ($search = $request->search) {
            $query->where(function ($q) use ($search) {
                $q->where('code', $search)
                    ->orWhere('barcode', $search)
                    ->orWhere('name', 'like', $search . '%');
            });
        }

        // date range
        if ($request->start_date && $request->end_date) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->start_date)->startOfDay(),
                Carbon::parse($request->end_date)->endOfDay(),
            ]);
        } elseif ($request->start_date) {
            $query->where('created_at', '>=', Carbon::parse($request->start_date)->startOfDay());
        } elseif ($request->end_date) {
            $query->where('created_at', '<=', Carbon::parse($request->end_date)->endOfDay());
        }
        // category
        if ($categoryCode = $request->category_code) {
            $query->where('category_code', $categoryCode);
        }

        return $query
            ->paginate($request->per_page ?? 15)
            ->withQueryString();
    }

    // get all active product with pagination return array
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
                    'stock' => (float) $product->stock,
                    'barcode' => $product->barcode,
                    'category_code' => $product->category_code,
                    'image' => $product->image,

                    'uom' => [
                        'uom_code'          => $retailUom->uom_code ?? null,
                        'uom_name'          => $retailUom->uom->name ?? null,
                        'quantity_per_unit' => (float) ($retailUom->quantity_per_unit ?? 1),
                        'barcode'           => $retailUom->barcode ?? null,
                        'is_default'        => (bool) ($retailUom->is_default ?? false),
                        'uom_role'          => $retailUom->uom_role ?? 'retail',
                    ],

                    'uom_matrix' => $product->uoms->map(function ($uom) {
                        return [
                            'uom_code'          => $uom->uom_code,
                            'uom_name'          => $uom->uom->name ?? $uom->uom_code,
                            'quantity_per_unit' => (float) $uom->quantity_per_unit,
                            'is_default'        => (bool) $uom->is_default,
                            'uom_role'          => $uom->uom_role ?? 'retail',
                            'barcode'           => $uom->barcode,
                        ];
                    })->values()->toArray(),
                ];
            });
    }

    // finding product code
    public function findOrFail(string $code): Product
    {
        return Product::with(['uoms.uom'])
            ->where('code', $code)
            ->firstOrFail();
    }

    // create new product
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
            'stock'         => $data['stock'] ?? 0,
            'min_stock'     => $data['min_stock'] ?? 0,
            'barcode'       => $data['barcode'] ?? null,
            'description'   => $data['description'] ?? null,
            'image'         => $data['image'] ?? null,
            'expiry_date'   => $data['expiry_date'] ?? null,
            'status'        => $data['status'] ?? 'active',
        ]);
    }

    // udpdate product code
    public function update(string $code, array $data): Product
    {
        $product = $this->findOrFail($code);

        if (($data['image'] ?? null) instanceof UploadedFile) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }

            $data['image'] = $data['image']->store('products', 'public');
        }

        $product->update([
            'name'          => $data['name'] ?? $product->name,
            'category_code' => $data['category_code'] ?? $product->category_code,
            'stock'         => $data['stock'] ?? $product->stock,
            'min_stock'     => $data['min_stock'] ?? $product->min_stock,
            'barcode'       => $data['barcode'] ?? $product->barcode,
            'description'   => $data['description'] ?? $product->description,
            'expiry_date'   => $data['expiry_date'] ?? $product->expiry_date,
            'status'        => $data['status'] ?? $product->status,
            'image'         => $data['image'] ?? $product->image,
        ]);

        return $product->refresh();
    }

    public function getCategories()
    {
        return Category::where('status', 'active')->orderBy('name')->get();
    }

    //  deactive product
    public function deactivate(string $code): Product
    {
        $product = $this->findOrFail($code);

        $product->update([
            'status' => 'inactive'
        ]);
        return $product->fresh();
    }
}
