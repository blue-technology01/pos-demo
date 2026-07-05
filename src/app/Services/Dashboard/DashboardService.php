<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\DB;

class DashboardService
{
    public const PERIODS = ['today', 'yesterday', 'week', 'month', 'year'];

    /**
     * base sale query
     */
    private function salesBase($start, $end)
    {
        return DB::table('sales')
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$start, $end]);
    }

    /**
     * get start
     */
    public function getStats(string $period = 'today'): array
    {
        [$start, $end] = $this->getDateRange($period);

        // reuse same sales query
        $sales = $this->salesBase($start, $end);

        $summary = (clone $sales)
            ->selectRaw('SUM(total_amount) as revenue, COUNT(*) as orders')
            ->first();

        // cost
        $cost = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->where('s.status', 'completed')
            ->whereBetween('s.sale_date', [$start, $end])
            ->sum(DB::raw('si.quantity * si.cost_price'));

        return [
            'revenue'   => round((float) $summary->revenue, 2),
            'cost'      => round((float) $cost, 2),
            'profit'    => round((float) ($summary->revenue - $cost), 2),
            'orders'    => (int) $summary->orders,
            'customers' => DB::table('customers')->count(),
            'products'  => DB::table('products')->where('status', 'active')->count(),
        ];
    }

    /**
     * column chart
     */
    public function getColumnChart(string $period = 'today'): array
    {
        [$start, $end] = $this->getDateRange($period);

        // revenue
        $revenueRows = DB::table('sales')
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$start, $end])
            ->selectRaw('sale_date as date, SUM(total_amount) as revenue')
            ->groupBy('sale_date')
            ->pluck('revenue', 'date');

        // cost
        $costRows = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->where('s.status', 'completed')
            ->whereBetween('s.sale_date', [$start, $end])
            ->selectRaw('s.sale_date as date, SUM(si.quantity * si.cost_price) as cost')
            ->groupBy('s.sale_date')
            ->pluck('cost', 'date');

        $dates = collect($revenueRows->keys())
            ->merge($costRows->keys())
            ->unique()
            ->sort();

        $categories = [];
        $revenue = [];
        $cost = [];
        $profit = [];

        foreach ($dates as $date) {
            $r = (float) ($revenueRows[$date] ?? 0);
            $c = (float) ($costRows[$date] ?? 0);

            $categories[] = $date;
            $revenue[] = $r;
            $cost[] = $c;
            $profit[] = $r - $c;
        }

        return [
            'categories' => $categories,
            'revenue'    => $revenue,
            'cost'       => $cost,
            'profit'     => $profit,
        ];
    }

    /**
     * donut chart
     */
    public function getDonutChart(string $period = 'today'): array
    {
        [$start, $end] = $this->getDateRange($period);

        $rows = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->join('products as p', 'p.code', '=', 'si.product_code')
            ->join('categories as c', 'c.code', '=', 'p.category_code')
            ->where('s.status', 'completed')
            ->whereBetween('s.sale_date', [$start, $end])
            ->selectRaw('c.name as category, SUM(si.amount) as total_sales')
            ->groupBy('c.name')
            ->orderByDesc('total_sales')
            ->get();

        return [
            'labels' => $rows->pluck('category')->toArray(),
            'series' => $rows->pluck('total_sales')->map(fn ($v) => (float) $v)->toArray(),
        ];
    }

    /**
     * date range
     */
    private function getDateRange(string $period): array
    {
        if (!in_array($period, self::PERIODS, true)) {
            throw new \InvalidArgumentException("Invalid period: {$period}");
        }

        return match ($period) {
            'today'     => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'week'      => [now()->startOfWeek(), now()->endOfWeek()],
            'month'     => [now()->startOfMonth(), now()->endOfMonth()],
            'year'      => [now()->startOfYear(), now()->endOfYear()],
        };
    }
}
