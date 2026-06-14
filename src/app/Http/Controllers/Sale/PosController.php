<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Product\ProductUomService;
use Illuminate\Http\Request;

class PosController extends Controller
{
    public function __construct(
        private readonly ProductUomService $productUomService
    ) {}

    public function index(Request $request)
    {
        $products = $this->productUomService->getPOSProducts($request);

        $categories = Category::where('status', 'active')
            ->orderBy('name')
            ->get(['code', 'name']);

        return view('cashier.pos.index', compact(
            'products',
            'categories'
        ));
    }
}
