<?php

namespace App\Http\Controllers\Product;

use App\Models\Uom;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Product\ProductUomService;
use App\Http\Requests\Product\ProductUomStoreRequest;
use App\Http\Requests\Product\ProductUomUpdateRequest;

class ProductUomController extends Controller
{
    public function __construct(
        private readonly ProductUomService $productUomService
    ) {}

    public function index(Request $request)
    {
        $productUoms = $this->productUomService->getAll($request);

        // Fixed queries
        $products = Product::select('code', 'name')->orderBy('name')->get();
        $uoms = Uom::select('code', 'name')->orderBy('name')->get();   // Changed 'id' to 'code'

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

    public function create()
    {
        return view('admin.products.product-uom.create', [
            'products' => Product::select('code', 'name')->orderBy('name')->get(),
            'uoms'     => Uom::select('code', 'name')->orderBy('name')->get(),   // Fixed
        ]);
    }

    public function store(ProductUomStoreRequest $request)
    {
        $this->productUomService->create($request->validated());

        return redirect()
            ->route('admin.product-uom.index')
            ->with('success', 'Product UOM created successfully.');
    }

    public function edit(string $id)
    {
        $productUom = $this->productUomService->findOrFail($id);

        return view('admin.products.product-uom.edit', [
            'productUom' => $productUom,
            'products'   => Product::select('code', 'name')->orderBy('name')->get(),
            'uoms'       => Uom::select('code', 'name')->orderBy('name')->get(),
        ]);
    }

    public function update(ProductUomUpdateRequest $request, string $id)
    {
        $this->productUomService->update($id, $request->validated());

        return redirect()
            ->route('admin.product-uom.index')
            ->with('success', 'Product UOM updated successfully.');
    }

    public function destroy(string $id)
    {
        $this->productUomService->delete($id);

        return redirect()
            ->route('admin.product-uom.index')
            ->with('success', 'Product UOM deleted successfully.');
    }

    public function getByProduct(string $productCode)
    {
        // Call the Service method instead of writing the mapping logic here
        $uoms = $this->productUomService->getByProduct($productCode);

        return response()->json($uoms);
    }

    public function posProducts(Request $request)
    {
        $products = $this->productUomService->getPOSProducts($request);

        return response()->json([
            'status' => 'success',
            'data'   => $products,
        ]);
    }
}
