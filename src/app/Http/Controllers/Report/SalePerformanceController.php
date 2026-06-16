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

    public function index(Request $request): View|JsonResponse
    {
        // Always fall back to last 30 days — reset sends DEFAULT_START/END, not empty
        $startDate = $request->get('start_date') ?: now()->subDays(29)->format('Y-m-d');
        $endDate   = $request->get('end_date')   ?: now()->format('Y-m-d');
        $search    = (string) $request->get('search', '');
        $perPage   = max(1, (int) $request->get('per_page', 15));
        $page      = max(1, (int) $request->get('page', 1));

        $performance  = $this->salePerformanceService->getStaffPerformance($startDate, $endDate, $search, $perPage, $page);
        $summary      = $this->salePerformanceService->getSummary($startDate, $endDate);
        $topPerformer = $this->salePerformanceService->getTopPerformer($startDate, $endDate);
        $chartData    = $this->salePerformanceService->getChartData($startDate, $endDate);

        $rows       = $performance['rows'];
        $pagination = $performance['pagination'];

        if ($request->ajax()) {
            return response()->json([
                'rows'         => $rows,
                'summary'      => $summary,
                'pagination'   => $pagination,
                'topPerformer' => $topPerformer,
                'chartData'    => $chartData,
            ]);
        }

        return view('admin.reports.sale-person', compact(
            'rows',
            'summary',
            'pagination',
            'topPerformer',
            'chartData',
            'startDate',
            'endDate',
            'search',
            'perPage',
        ));
    }
}
