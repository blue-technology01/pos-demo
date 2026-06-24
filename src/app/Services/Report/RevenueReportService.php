<?php

namespace App\Services\Report;

use App\Models\Sale;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Support\Facades\Cache;

class RevenueReportService
{
    private const CACHE_TTL = 10;
    private const MAX_DAYS  = 365;

    public function getReportData(?string $startDate = null, ?string $endDate = null): array
    {
        [$startDate, $endDate] = $this->resolveDateRange($startDate, $endDate);

        $cacheKey = "report:{$startDate}:{$endDate}";

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
                    'average_sale'  => $totalOrders > 0
                        ? round($totalRevenue / $totalOrders, 2)
                        : 0,
                ],
            ];
        });
    }

    public function getChartData(?string $startDate = null, ?string $endDate = null): array
    {
        $data = $this->getReportData($startDate, $endDate);

        return $data['chartData'] ?? [
            'categories' => [],
            'series'     => [['name' => 'Revenue', 'data' => []]],
        ];
    }

    public function getSummary(?string $startDate = null, ?string $endDate = null): array
    {
        $data = $this->getReportData($startDate, $endDate);

        return $data['summary'] ?? [
            'total_revenue' => 0.0,
            'total_orders'  => 0,
            'average_sale'  => 0.0,
        ];
    }

    public function resolveDateRange(?string $startDate, ?string $endDate): array
    {
        if (!$startDate || !$endDate) {
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

    public static function flushCache(): void
    {
        Cache::flush();
    }
}
