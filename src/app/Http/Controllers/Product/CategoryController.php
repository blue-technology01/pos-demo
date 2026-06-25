<?php

namespace App\Http\Controllers\Product;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Services\Product\CategoryService;
use App\Http\Requests\Product\CategoryRequest;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryService $service
    ) {}

    /**
     * Display paginated list of categories.
     */
    public function index(Request $request): View
    {
        $categories = $this->service->getAll($request);

        return view('admin.products.category', compact('categories'));
    }

    /**
     * Store a newly created category.
     */
    public function store(CategoryRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());

        return $this->redirectWithSuccess('Category created successfully.');
    }

    /**
     * Update an existing category.
     */
    public function update(CategoryRequest $request, string $code): RedirectResponse
    {
        $this->service->update($code, $request->validated());

        return $this->redirectWithSuccess('Category updated successfully.');
    }

    /**
     * Delete a category and its associated image.
     */
    public function destroy(string $code): RedirectResponse
    {
        $this->service->delete($code);

        return $this->redirectWithSuccess('Category deleted successfully.');
    }

    /* ─────────────────────────────────────────
    |  Private helpers
    ───────────────────────────────────────── */

    private function redirectWithSuccess(string $message): RedirectResponse
    {
        return redirect()
            ->route('admin.category.index')
            ->with('success', $message);
    }
}
