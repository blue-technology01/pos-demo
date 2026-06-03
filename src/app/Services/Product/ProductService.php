<?php

namespace App\Services\Product;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class ProductService
{
    /**
     * Get all active products with pagination.
     */
    public function getAll(Request $request): LengthAwarePaginator
    {
        return Product::query()
            ->select(
                'code',
                'name',
                'category_code',
                'cost_price',
                'price',
                'stock',
                'min_stock',
                'barcode',
                'description',
                'image',
                'expiry_date',
                'status'
            )
            ->when($request->search, function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                ->orWhere('code', 'like', "%{$request->search}%");
            })
            ->when($request->category_code, function ($q) use ($request) {
                $q->where('category_code', $request->category_code);
            })
            ->when($request->start_date, function ($q) use ($request) {
                $q->whereDate('created_at', '>=', $request->start_date);
            })
            ->when($request->end_date, function ($q) use ($request) {
                $q->whereDate('created_at', '<=', $request->end_date);
            })
            ->where('status', 'active')
            ->paginate($request->query('per_page', 15))
            ->withQueryString();
    }

    /**
     * Find a product by code or throw 404.
     */
    public function findOrFail(string $code): Product
    {
        return Product::findOrFail($code);
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

    public function getCategories(){

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
