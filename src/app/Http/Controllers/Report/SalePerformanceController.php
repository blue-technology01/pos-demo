<?php

namespace App\Http\Controllers\Report;

use Illuminate\View\View;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Services\Report\SalePerformanceService;

class SalePerformanceController extends Controller
{
    public function __construct(
        protected SalePerformanceService $salePerformanceService
    ) {}

    public function index(Request $request): View|JsonResponse
    {
        $startDate = $request->get('start_date')
            ?: now()->subDays(29)->format('Y-m-d');

        $endDate = $request->get('end_date')
            ?: now()->format('Y-m-d');

        $search = (string) $request->get('search', '');
        $perPage = max(1, (int) $request->get('per_page', 15));
        $page = max(1, (int) $request->get('page', 1));

        $performance = $this->salePerformanceService->getStaffPerformance(
            $startDate,
            $endDate,
            $search,
            $perPage,
            $page
        );

        $summary = $this->salePerformanceService->getSummary(
            $startDate,
            $endDate
        );

        $topPerformer = $this->salePerformanceService->getTopPerformer(
            $startDate,
            $endDate
        );

        $chartData = $this->salePerformanceService->getChartData(
            $startDate,
            $endDate
        );

        $rows = $performance['rows'];

        if ($request->ajax()) {
            return response()->json([
                'rows' => $rows->items(),

                'pagination' => [
                    'current_page' => $rows->currentPage(),
                    'last_page'    => $rows->lastPage(),
                    'per_page'     => $rows->perPage(),
                    'total'        => $rows->total(),
                    'from'         => $rows->firstItem(),
                    'to'           => $rows->lastItem(),
                ],

                'summary'      => $summary,
                'topPerformer' => $topPerformer,
                'chartData'    => $chartData,
            ]);
        }

        return view('admin.reports.sale-person', [
            'rows'         => $rows,
            'summary'      => $summary,
            'topPerformer' => $topPerformer,
            'chartData'    => $chartData,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'search'       => $search,
            'perPage'      => $perPage,
        ]);
    }
}
