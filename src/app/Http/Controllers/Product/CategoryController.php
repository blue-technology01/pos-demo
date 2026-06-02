<?php

namespace App\Http\Controllers\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\CategoryRequest;
use App\Services\Product\CategoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $categoryService
    ) {}

    /**
     * Display all active categories.
     */
    public function index(Request $request): View
    {
        $categories = $this->categoryService->getAll($request);

        return view('admin.products.category', compact('categories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        $this->categoryService->create($request->validated());

        return redirect()
            ->route('admin.category')
            ->with('success', 'Category created successfully.');
    }

    /**
     * Update an existing category.
     */
    public function update(CategoryRequest $request, string $code): RedirectResponse
    {
        $this->categoryService->update($code, $request->validated());

        return redirect()
            ->route('admin.category')
            ->with('success', 'Category updated successfully.');
    }

    /**
     * Deactivate a category (soft delete).
     */
    public function destroy(string $code): RedirectResponse
    {
        $this->categoryService->deactivate($code);
        
        return redirect()
            ->route('admin.category')
            ->with('success', 'Category deactivated successfully.');
    }
}