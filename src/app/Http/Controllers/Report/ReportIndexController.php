<?php

namespace App\Http\Controllers\Report;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Report\RevenueReportService;

class ReportIndexController extends Controller
{
    public function __construct(
        private readonly RevenueReportService $revenueReportService
    ) {}

    public function index(Request $request)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date'   => 'nullable|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date') ?? now()->subDays(6)->format('Y-m-d');
        $endDate   = $request->input('end_date')   ?? now()->format('Y-m-d');
        
        // return json for ajax requests
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'chartData' => $this->revenueReportService->getChartData($startDate, $endDate),
                'summary'   => $this->revenueReportService->getSummary($startDate, $endDate),
                'startDate' => $startDate,
                'endDate'   => $endDate,
            ]);
        }

        return view('admin.reports.index', [
            'chartData' => $this->revenueReportService->getChartData($startDate, $endDate),
            'summary'   => $this->revenueReportService->getSummary($startDate, $endDate),
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ]);
    }
}
