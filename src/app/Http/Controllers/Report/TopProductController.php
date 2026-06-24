<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Services\Report\TopProductService;
use Illuminate\Http\Request;

class TopProductController extends Controller
{
    public function __construct(
        protected TopProductService $topProductService
    ) {}
    public function index(Request $request)
    {
        $allProducts = $this->topProductService->getTopProducts($request);
        $summary     = $this->topProductService->getSummary($allProducts);
        $chartData   = $allProducts->take(10)->map(fn($p) => [
            'name'          => $p->product_name,
            'qty_sold'      => (float) $p->qty_sold,
            'total_revenue' => (float) $p->total_revenue,
        ])->values();

        $products   = $this->topProductService->getPaginatedProducts($request);
        $categories = Category::where('status', 'active')->orderBy('name')->get();

        return view('admin.reports.top-product', compact(
            'products',
            'summary',
            'categories',
            'chartData',
        ));
    }
}
