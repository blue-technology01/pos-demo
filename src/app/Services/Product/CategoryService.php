<?php

namespace App\Services\Product;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class CategoryService
{
    /**
     * Get all active categories with pagination.
     */
    public function getAll(Request $request): LengthAwarePaginator
    {
        return Category::select('code', 'name', 'description', 'image', 'status', 'created_at')
            ->where('status', 'active')
            ->paginate($request->query('per_page', 15));
    }

    /**
     * Find a category by code or throw 404.
     */
    public function findOrFail(string $code): Category
    {
        return Category::findOrFail($code);
    }

    /**
     * Create a new category.
     */
    public function create(array $data): Category
    {
        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            $data['image'] = $data['image']->store('categories', 'public');
        }

        return Category::create([
            'code'        => $data['code'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'image'       => $data['image'] ?? null,
            'status'      => $data['status'] ?? 'active',
        ]);
    }

    /**
     * Update an existing category by code.
     */
    public function update(string $code, array $data): Category
    {
        $category = Category::findOrFail($code);

        if (isset($data['image']) && $data['image'] instanceof UploadedFile) {
            // Delete old image if exists
            if ($category->image && Storage::disk('public')->exists($category->image)) {
                Storage::disk('public')->delete($category->image);
            }

            $data['image'] = $data['image']->store('categories', 'public');
        }

        $category->update([
            'name'        => $data['name'] ?? $category->name,
            'description' => $data['description'] ?? $category->description,
            'image'       => $data['image'] ?? $category->image,
            'status'      => $data['status'] ?? $category->status,
        ]);

        return $category->fresh();
    }

    /**
     * Deactivate a category (soft delete via status).
     */
    public function deactivate(string $code): Category
    {
        $category = Category::findOrFail($code);
        $category->update(['status' => 'inactive']);

        return $category->fresh();
    }
}