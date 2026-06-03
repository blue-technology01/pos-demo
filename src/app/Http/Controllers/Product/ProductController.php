<?php

namespace App\Http\Controllers\Product;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Product\ProductService;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductUpdateRequest;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService
    ) {}

    public function index(Request $request)
    {
        $products   = $this->productService->getAll($request);
        $categories = Category::all();

        if ($request->ajax()) {
            return response()->json([
                'table'      => view('admin.products._table', compact('products'))->render(),
                'pagination' => view('pagination::bootstrap-5', ['paginator' => $products])->render(),
            ]);
        }

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        return view('admin.products.create', [
            'categories' => Category::all(),
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
            'categories' => Category::all(),
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
