<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use App\Services\Report\SalePerformanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SalePerformanceController extends Controller
{
    public function __construct(
        protected SalePerformanceService $salePerformanceService
    ) {}
    //
    public function index(Request $request): View | JsonResponse {
        $startDate = $request->get('start_date', now()->subDays(29)->format('Y-m-d'));
        $endDate   = $request->get('end_date',   now()->format('Y-m-d'));
        $search    = $request->get('search',   '');
        $perPage   = (int) $request->get('per_page', 15);
        $page      = (int) $request->get('page',     1);

        // Fetch all data
        $performance  = $this->salePerformanceService->getStaffPerformance($startDate, $endDate, $search, $perPage, $page);
        $summary      = $this->salePerformanceService->getSummary($startDate, $endDate);
        $topPerformer = $this->salePerformanceService->getTopPerformer($startDate, $endDate);
        $chartData    = $this->salePerformanceService->getChartData($startDate, $endDate);

        $rows       = $performance['rows'];
        $pagination = $performance['pagination'];

        // AJAX request — return JSON for dynamic filter/pagination
        if ($request->ajax()) {
            return response()->json([
                'rows'         => $rows,
                'summary'      => $summary,
                'pagination'   => $pagination,
                'topPerformer' => $topPerformer,
                'chartData'    => $chartData,
            ]);
        }
        // Full page load
        return view('dashboard.report.sale-person', compact(
            'rows',
            'summary',
            'pagination',
            'topPerformer',
            'chartData',
            'startDate',
            'endDate',
        ));
    }
}
