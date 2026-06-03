<?php

namespace App\Http\Controllers\Product;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\Product\ProductUomStoreRequest;
use App\Http\Requests\Product\ProductUomUpdateRequest;
use App\Models\Product;
use App\Models\Uom;
use App\Services\Product\ProductUomService;

class ProductUomController extends Controller
{
    // service injection
    public function __construct(
        private readonly ProductUomService $productUomService
    ) {}

    // index
    public function index(Request $request)
    {
        $productUoms = $this->productUomService->getAll($request);
        $products = Product::all();
        $uoms = Uom::all();

        if ($request->ajax()) {
            return response()->json([
                'table' => view('admin.products.product-uom', compact('productUoms'))->render(),
                'pagination' => (string) $productUoms->links(),
            ]);
        }

        return view('admin.products.product-uom.index', compact(
            'productUoms',
            'products',
            'uoms'
        ));
    }

    // show form create
    public function create() {
        return view('admin.products.product-uom.create', [
            'products' => Product::all(),
            'uoms' => Uom::all(),
        ]);
    }

    // for store data
    public function store(ProductUomStoreRequest $request) {
        $this->productUomService->create($request->validated());

        return redirect()
            ->route('admin.product-uom.index')
            ->with('success', 'Product UOM created successfully.');
    }

    // function for edit
    public function edit(string $id) {
        $productUom = $this->productUomService->findOrFail($id);

        return view('admin.products.product-uom.edit', [
            'productUom' => $productUom,
            'products' => Product::all(),
            'uoms' => Uom::all(),
        ]);
    }

    public function update(ProductUomUpdateRequest $request, string $id) {
        $this->productUomService->update($id, $request->validated());

        return redirect()
            ->route('admin.product-uom.index')
            ->with('success', 'Update product uom success.');
    }

    public function destroy(string $id) {
        $this->productUomService->delete($id);

        return redirect()
            ->route('admin.product-uom.index')
            ->with('success', 'Product deleted successfully.');
    }
}
