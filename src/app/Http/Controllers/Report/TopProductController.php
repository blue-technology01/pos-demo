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
        $filters = [
            'search'        => $request->input('search'),
            'category_code' => $request->input('category_code'),
            'date_from'     => $request->input('date_from'),
            'date_to'       => $request->input('date_to'),
        ];

        $products   = $this->topProductService->getTopProducts($filters);
        $summary    = $this->topProductService->getSummary($products);
        $categories = Category::where('status', 'active')->orderBy('name')->get();

        $chartData = $products->map(fn($p) => [
            'name'          => $p->product_name,
            'qty_sold'      => (float) $p->qty_sold,
            'total_revenue' => (float) $p->total_revenue,
        ])->values();

        return view('admin.reports.top-product', compact(
            'products',
            'summary',
            'categories',
            'chartData'
        ));
    }
}
