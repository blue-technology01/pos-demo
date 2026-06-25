<?php

namespace App\Http\Controllers\Sale;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Product\ProductUomService;

class PosController extends Controller
{
    public function __construct(
        private readonly ProductUomService $productUomService
    ) {}

    public function index(Request $request)
    {
        $categories = Category::where('status', 'active')
            ->orderBy('name')
            ->get(['code', 'name']);

        return view('cashier.pos.index', compact('categories'));
    }
}
