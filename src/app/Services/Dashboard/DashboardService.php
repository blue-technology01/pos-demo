<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DashboardService
{
    /**
     * Allowed periods for validation.
     */
    public const PERIODS = ['today', 'yesterday', 'week', 'month', 'year'];

    /**
     * Summary stats for the dashboard header cards.
     */
    public function getStats(string $period = 'today'): array
    {
        [$start, $end] = $this->getDateRange($period);
        [$startDate, $endDate] = [$start->toDateString(), $end->toDateString()];

        $revenue = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->where('s.status', 'completed')
            ->whereBetween('s.sale_date', [$startDate, $endDate])
            ->sum('si.amount');

        $orders = DB::table('sales')
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->count();

        // These are totals (not period-filtered) — intentional
        $customers = DB::table('customers')->count();
        $products  = DB::table('products')
            ->where('status', 'active')
            ->count();

        return compact('revenue', 'orders', 'customers', 'products');
    }

    /**
     * Revenue / cost / profit grouped by date for the column chart.
     */
    public function getColumnChart(string $period = 'today'): array
    {
        [$start, $end] = $this->getDateRange($period);
        [$startDate, $endDate] = [$start->toDateString(), $end->toDateString()];

        $rows = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->where('s.status', 'completed')
            ->whereBetween('s.sale_date', [$startDate, $endDate])
            ->selectRaw("
                s.sale_date                                 AS sale_date,
                SUM(si.amount)                              AS revenue,
                SUM(si.quantity * si.cost_price)            AS cost,
                SUM(si.amount - si.quantity * si.cost_price) AS profit
            ")
            ->groupBy('s.sale_date')
            ->orderBy('s.sale_date')
            ->get();

        return [
            'categories' => $rows->pluck('sale_date')->toArray(),
            'revenue'    => $rows->pluck('revenue')->map(fn($v) => (float) $v)->toArray(),
            'cost'       => $rows->pluck('cost')->map(fn($v) => (float) $v)->toArray(),
            'profit'     => $rows->pluck('profit')->map(fn($v) => (float) $v)->toArray(),
        ];
    }

    /**
     * Sales by category for the donut chart.
     */
    public function getDonutChart(string $period = 'today'): array
    {
        [$start, $end] = $this->getDateRange($period);
        [$startDate, $endDate] = [$start->toDateString(), $end->toDateString()];

        $rows = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->join('products as p', 'p.code', '=', 'si.product_code')
            ->join('categories as c', 'c.code', '=', 'p.category_code')
            ->where('s.status', 'completed')
            ->whereBetween('s.sale_date', [$startDate, $endDate])
            ->selectRaw('c.name AS category_name, SUM(si.amount) AS total_sales')
            ->groupBy('c.code', 'c.name')
            ->orderByDesc('total_sales')
            ->get();

        return [
            'labels' => $rows->pluck('category_name')->toArray(),
            'series' => $rows->pluck('total_sales')
                ->map(fn($v) => round((float) $v, 2))
                ->toArray(),
        ];
    }

    /**
     * Returns [Carbon $start, Carbon $end] for the given period.
     *
     * @throws \InvalidArgumentException
     */
    private function getDateRange(string $period): array
    {
        if (! in_array($period, self::PERIODS, true)) {
            throw new \InvalidArgumentException("Invalid period: {$period}");
        }

        return match ($period) {
            'today'     => [now()->startOfDay(),          now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week'      => [now()->startOfWeek(),         now()->endOfWeek()],
            'month'     => [now()->startOfMonth(),        now()->endOfMonth()],
            'year'      => [now()->startOfYear(),         now()->endOfYear()],
        };
    }
}
