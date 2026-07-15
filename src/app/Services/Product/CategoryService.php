<?php

namespace App\Services\Product;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class CategoryService
{

    private const MAX_PER_PAGE    = 100;
    private const DEFAULT_PER_PAGE = 15;
    private const IMAGE_DISK      = 'public';
    private const IMAGE_DIR       = 'categories';

    // get paginate category with option search, filter, sort
    public function getAll(Request $request): LengthAwarePaginator
    {
        $query = Category::query()->select([
            'code',
            'name',
            'description',
            'image',
            'status',
            'created_at',
        ]);

        $this->applySearch($query, $request->input('search'));
        $this->applyStatus($query, $request->input('status'));
        $this->applySort($query, $request->input('sort'));

        return $query
            ->paginate($this->resolvePerPage($request))
            ->withQueryString();
    }

    // finding category by code, if not found it will give Exception
    public function findOrFail(string $code): Category
    {
        return Category::where('code', $code)->firstOrFail();
    }

    //  create new category
    public function create(array $data): Category
    {
        return Category::create([
            'code'        => $data['code'],
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'image'       => $this->uploadImage($data['image'] ?? null),
            'status'      => $data['status'] ?? 'active',
        ]);
    }

    // update category
    public function update(string $code, array $data): Category
    {
        // finding category first
        $category = $this->findOrFail($code);

        $image = ($data['image'] ?? null) instanceof UploadedFile // check user insert new file or not, if it true it will remove file and replace new file
            ? $this->replaceImage($category->image, $data['image']) //
            : $category->image;

        $category->update([
            'name'        => $data['name'] ?? $category->name,
            'description' => $data['description'] ?? $category->description,
            'image'       => $image,
            'status'      => $data['status'] ?? $category->status,
        ]);

        return $category->fresh();  // fresh
    }

    /* ??
    * if(asset($data[name])) {
            $name =  $data['name'];
      } else {
            $name = $category->name
        }'
    ! ??
        $name = this['name'] ?? 'unkhnow'
    */

    // for remove category by code and picture that have relate
    public function delete(string $code): void
    {
        $category = Category::findOrFail($code);

        $this->deleteImage($category->image);

        $category->delete();
    }

    // deactivate a category
    public function deactivate(string $code): Category
    {
        $category = Category::findOrFail($code);
        $category->update(['status' => 'inactive']);

        return $category->fresh();
    }
    // private helper
    private function applySearch($query, ?string $search): void
    {
        if (blank($search)) return;

        $term = trim($search);

        $query->where(function ($q) use ($term) {
            $q->where('code', 'like', $term . '%')
              ->orWhere('name', 'like', '%' . $term . '%')
              ->orWhere('description', 'like', '%' . $term . '%');
        });
    }

    private function applyStatus($query, ?string $status): void
    {
        if (blank($status)) return;

        $query->where('status', $status);
    }

    private function applySort($query, ?string $sort): void
    {
        match ($sort) {
            'oldest'    => $query->oldest(),
            'name_asc'  => $query->orderBy('name'),
            'name_desc' => $query->orderByDesc('name'),
            'code_asc'  => $query->orderBy('code'),
            default     => $query->latest(),
        };
    }

    private function resolvePerPage(Request $request): int
    {
        return min(
            (int) $request->input('per_page', self::DEFAULT_PER_PAGE),
            self::MAX_PER_PAGE
        );
    }

    private function uploadImage(mixed $file): ?string
    {
        if (!$file instanceof UploadedFile) return null;

        return $file->store(self::IMAGE_DIR, self::IMAGE_DISK);
    }

    private function replaceImage(?string $old, UploadedFile $new): string
    {
        $this->deleteImage($old);

        return $new->store(self::IMAGE_DIR, self::IMAGE_DISK);
    }

   private function deleteImage(?string $path): void
    {
        if (!$path) return;

        Storage::disk(self::IMAGE_DISK)->delete($path);
    }
}
