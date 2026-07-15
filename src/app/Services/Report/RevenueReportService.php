<?php

namespace App\Services\Report;

use Carbon\Carbon;
use App\Models\Sale;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RevenueReportService
{
    private const CACHE_TTL = 10;  // it will store on cache 10 mn
    private const MAX_DAYS  = 365;

    // get all report of sale
    public function getReportData(?string $startDate = null, ?string $endDate = null): array
    {
        [$startDate, $endDate] = $this->resolveDateRange($startDate, $endDate);

        $version = Cache::get('revenue_report_version', 1);

        $cacheKey = "report:v{$version}:{$startDate}:{$endDate}";

        return Cache::remember($cacheKey, now()->addMinutes(self::CACHE_TTL), function () use ($startDate, $endDate) {

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

            $categories = [];
            $chartData  = [];
            $period     = CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                $dateKey      = $date->format('Y-m-d');
                $categories[] = $date->format('d M');
                $chartData[]  = (float) ($sales[$dateKey]->revenue ?? 0);
            }

            $totalRevenue = (float) $sales->sum('revenue');
            $totalOrders  = (int)   $sales->sum('orders');

            return [
                'chartData' => [
                    'categories' => $categories,
                    'series'     => [
                        [
                            'name' => 'Revenue',
                            'data' => $chartData,
                        ],
                    ],
                ],
                'summary' => [
                    'total_revenue' => $totalRevenue,
                    'total_orders'  => $totalOrders,
                    'average_sale'  => $totalOrders > 0 ? round($totalRevenue / $totalOrders, 2) : 0,
                ],
            ];
        });
    }

    // function for get chart data
    public function getChartData(?string $startDate = null, ?string $endDate = null): array
    {
        $data = $this->getReportData($startDate, $endDate);

        return $data['chartData'] ?? [
            'categories' => [],
            'series'     => [['name' => 'Revenue', 'data' => []]],
        ];
    }

    // get summary data
    public function getSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $data = $this->getReportData($startDate, $endDate);

        return $data['summary'] ?? [
            'total_revenue' => 0.0,
            'total_orders'  => 0,
            'average_sale'  => 0.0,
        ];
    }

    // get resolve date range
    public function resolveDateRange(?string $startDate, ?string $endDate): array
    {
        if (!$startDate || !$endDate) {
            // it will show last 7 day
            return [
                now()->subDays(6)->format('Y-m-d'),
                now()->format('Y-m-d'),
            ];
        }

        $start = Carbon::parse($startDate)->startOfDay();
        $end   = Carbon::parse($endDate)->startOfDay();

        if ($start->gt($end)) {
            [$start, $end] = [$end, $start];
        }

        if ($start->diffInDays($end) > self::MAX_DAYS) {
            $end = $start->copy()->addDays(self::MAX_DAYS);
        }

        return [
            $start->format('Y-m-d'),
            $end->format('Y-m-d'),
        ];
    }

    // function write make sure it all sale it new and accurate
    public static function flushCache(): void
    {
        Cache::increment('revenue_report_version');
    }
}
