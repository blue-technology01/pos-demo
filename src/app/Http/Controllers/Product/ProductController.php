<?php

namespace App\Http\Controllers\Product;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Product\ProductService;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductUpdateRequest;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    /**
     * Display a listing of the products.
     */
    public function index(Request $request)
    {
        $products = $this->productService->getForAdmin($request);
        $categories = $this->productService->getCategories();

        if ($request->ajax()) {
            return response()->json($products);
        }

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        return view('admin.products.create', [
            'categories' => $this->productService->getCategories(),
        ]);
    }

    public function store(ProductStoreRequest $request)
    {
        $this->productService->create($request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(string $code)
    {
        $product = $this->productService->findOrFail($code);

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $this->productService->getCategories(),
        ]);
    }

    public function update(ProductUpdateRequest $request, string $code)
    {
        $this->productService->update($code, $request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(string $code)
    {
        $this->productService->deactivate($code);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }


}
