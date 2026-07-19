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
    // dependency injection
    public function __construct(

        private readonly ProductUomService $productUomService

    ) {}

    // show product uom list
    public function index(Request $request)
    {
        $productUoms = $this->productUomService->getAll($request); // controller -> productUomSerive -> Database

        $products = Product::select('code', 'name')->orderBy('name')->get();
        $uoms = Uom::select('code', 'name')->orderBy('name')->get();

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

    public function searchProduct(Request $request)
    {
        $products = $this->productUomService
            ->searchProducts($request->get('q', ''));

        return response()->json($products);
    }

    // show form create product uom
    public function create()
    {
        return view('admin.products.product-uom.create', [
            'products' => Product::select('code', 'name')->orderBy('name')->get(),
            'uoms'     => Uom::select('code', 'name')->orderBy('name')->get(),
        ]);
    }

    // save product uom new to database after user submit
    public function store(ProductUomStoreRequest $request)
    {
        $this->productUomService->create($request->validated());

        return redirect()
            ->route('admin.product-uom.index')
            ->with('success', 'Product UOM created successfully.');
    }

    // get product uom that have ready for show on form edit
    public function edit(string $id)
    {
        $productUom = $this->productUomService->findOrFail($id);

        return view('admin.products.product-uom.edit', [
            'productUom' => $productUom,
            'products'   => Product::select('code', 'name')->orderBy('name')->get(),
            'uoms'       => Uom::select('code', 'name')->orderBy('name')->get(),
        ]);
    }

    // update data after user submit
    public function update(ProductUomUpdateRequest $request, string $id)
    {
        $this->productUomService->update($id, $request->validated());

        return redirect()
            ->route('admin.product-uom.index')
            ->with('success', 'Product UOM updated successfully.');
    }

    // remove product from database
    public function destroy(string $id)
    {
        $this->productUomService->delete($id);

        return redirect()
            ->route('admin.product-uom.index')
            ->with('success', 'Product UOM deleted successfully.');
    }

    // get product uom
    public function getByProduct(string $productCode)
    {
        $uoms = $this->productUomService->getByProduct($productCode);

        return response()->json($uoms);
    }

    // get data for pos
    public function posProducts(Request $request)
    {
        $products = $this->productUomService->getPOSProducts($request);

        return response()->json([
            'status' => 'success',
            'data'   => $products,
        ]);
    }
}
