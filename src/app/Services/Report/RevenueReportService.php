<?php

namespace App\Services\Report;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Cache;

class RevenueReportService
{
    /**
     * Get all report data in a single DB query.
     */
    public function getReportData(?string $startDate = null, ?string $endDate = null): array
    {
        [$startDate, $endDate] = $this->resolveDateRange($startDate, $endDate);

        $cacheKey = "report:{$startDate}:{$endDate}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($startDate, $endDate) {

            // Single query for both chart and summary
            $sales = Sale::query()
                ->where('status', 'completed')
                ->whereBetween('sale_date', [$startDate, $endDate])
                ->selectRaw("DATE(sale_date) as sale_date, SUM(total_amount) as revenue, COUNT(*) as orders")
                ->groupBy(DB::raw('DATE(sale_date)'))
                ->orderBy('sale_date')
                ->get()
                ->keyBy(function ($item) {
                    return Carbon::parse($item->sale_date)->format('Y-m-d');
                });

            // Build chart data
            $categories = [];
            $chartData  = [];
            $period     = CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                $dateKey      = $date->format('Y-m-d');
                $categories[] = $date->format('d M');
                $chartData[]  = (float) ($sales[$dateKey]->revenue ?? 0);
            }

            // Build summary from same result — no extra query
            $totalRevenue = $sales->sum('revenue');
            $totalOrders  = $sales->sum('orders');

            return [
                'chartData' => [
                    'categories' => $categories,
                    'series'     => [
                        'name' => 'Revenue',
                        'data' => $chartData,
                    ],
                ],
                'summary' => [
                    'total_revenue' => (float) $totalRevenue,
                    'total_orders'  => (int) $totalOrders,
                    'average_sale'  => $totalOrders > 0
                        ? round($totalRevenue / $totalOrders, 2)
                        : 0,
                ],
            ];
        });
    }

    /**
     * Return only chart data for the requested range.
     */
    public function getChartData(?string $startDate = null, ?string $endDate = null): array
    {
        $data = $this->getReportData($startDate, $endDate);

        return $data['chartData'] ?? ['categories' => [], 'series' => ['name' => 'Revenue', 'data' => []]];
    }

    /**
     * Return only summary data for the requested range.
     */
    public function getSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $data = $this->getReportData($startDate, $endDate);

        return $data['summary'] ?? ['total_revenue' => 0.0, 'total_orders' => 0, 'average_sale' => 0.0];
    }

    /**
     * Resolve date range, defaulting to last 7 days.
     */
    public function resolveDateRange(?string $startDate, ?string $endDate): array
    {
        if (!$startDate || !$endDate) {
            return [
                now()->subDays(6)->format('Y-m-d'),
                now()->format('Y-m-d'),
            ];
        }

        return [
            Carbon::parse($startDate)->format('Y-m-d'),
            Carbon::parse($endDate)->format('Y-m-d'),
        ];
    }
}
