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
        $query = Category::select(
            'code',
            'name',
            'description',
            'image',
            'status',
            'created_at'
        )
        ->where('status', 'active');
        // Search
        if ($request->filled('search')) {
            $search = trim($request->search);

            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                ->orWhere('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Sort
        switch ($request->get('sort')) {
            case 'oldest':
                $query->oldest();
                break;

            case 'name_asc':
                $query->orderBy('name');
                break;

            case 'name_desc':
                $query->orderByDesc('name');
                break;

            case 'code_asc':
                $query->orderBy('code');
                break;

            default:
                $query->latest();
                break;
        }

        return $query
            ->paginate($request->get('per_page', 15))
            ->withQueryString();
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

        $category->update([
            'status' => 'inactive'
        ]);

        return $category->fresh();
    }
}
