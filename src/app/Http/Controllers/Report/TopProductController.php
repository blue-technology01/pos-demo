<?php

namespace App\Http\Controllers\Report;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Exports\TopProductExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Http\Controllers\Controller;
use App\Services\Report\TopProductService;

class TopProductController extends Controller
{
    // dependency injection
    public function __construct(

        protected TopProductService $topProductService

    ) {}

    // export file to excel
    public function export(Request $request)
    {
        return Excel::download(
            new TopProductExport($request),
            'top-product-report.xlsx'
        );
    }

    public function index(Request $request)
    {
        // get summary directly from database
        $summary = $this->topProductService->getSummary($request);

        // get only top product 10 for chart
        $chartData = $this->topProductService->getTopProducts($request, 10);

        // get data for table
        $products = $this->topProductService->getPaginatedProducts($request);

        // category
        $categories = Category::where('status', 'active')->orderBy('name')->get();

        return view('admin.reports.top-product', compact(
            'products',
            'summary',
            'categories',
            'chartData',
        ));
    }

}
