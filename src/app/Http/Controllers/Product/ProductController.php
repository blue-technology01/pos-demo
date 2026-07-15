<?php

namespace App\Http\Controllers\Product;

use Illuminate\Http\Request;
use App\Imports\ProductsImport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\Product\ProductService;
use App\Http\Requests\Product\ProductStoreRequest;
use App\Http\Requests\Product\ProductUpdateRequest;

class ProductController extends Controller
{
    public function __construct( // constructor run first controller

        private readonly ProductService $productService,

    ) {}

    // import data from file excel
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|max:2024'
            ]);
            Excel::import(new ProductsImport, $request->file('file'));
        } catch (\Exception $e) {
            dd($e->getMessage());
        }
    }

    // display listing of products
    public function index(Request $request)
    {
        $products = $this->productService->getForAdmin($request);
        $categories = $this->productService->getCategories();

        if ($request->ajax()) {
            return response()->json($products);
        }

        return view('admin.products.index', compact('products', 'categories'));
    }

    // show form create
    public function create()
    {
        return view('admin.products.create', [
            'categories' => $this->productService->getCategories(),
        ]);
    }

    // show product
    public function store(ProductStoreRequest $request)
    {
        $this->productService->create($request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product created successfully.');
    }

    // show form edit product
    public function edit(string $code)
    {
        $product = $this->productService->findOrFail($code);

        return view('admin.products.edit', [
            'product' => $product,
            'categories' => $this->productService->getCategories(),
        ]);
    }

    // recieve requet and redirect to product list
    public function update(ProductUpdateRequest $request, string $code)
    {
        $this->productService->update($code, $request->validated());

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product updated successfully.');
    }

    // function for remove product
    public function destroy(string $code)
    {
        $this->productService->deactivate($code);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Product deleted successfully.');
    }

}
