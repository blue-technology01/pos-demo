<?php

namespace App\Services\Dashboard;

use Illuminate\Support\Facades\DB;

class DashboardService
{
    public const PERIODS = ['today', 'yesterday', 'week', 'month', 'year'];

    public function getStats(string $period = 'today'): array
    {
        [$start, $end] = $this->getDateRange($period);

        $summary = DB::table('sales')
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$start, $end])
            ->selectRaw('SUM(total_amount) as revenue, COUNT(*) as orders')
            ->first();

        $cost = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->where('s.status', 'completed')
            ->whereBetween('s.sale_date', [$start, $end])
            ->sum(DB::raw('si.quantity * si.cost_price'));

        return [
            'cost'      => round((float) $cost, 2),
            'profit'    => round((float) ($summary->revenue - $cost), 2),
            'orders'    => (int) $summary->orders,
            'customers' => DB::table('customers')->count(),
            'products'  => DB::table('products')->where('status', 'active')->count(),
        ];
    }

    // get data for show on chart
    public function getColumnChart(string $period = 'today'): array
    {
        [$start, $end] = $this->getDateRange($period);

        // get total revenue day by day
        $revenueRows = DB::table('sales')
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$start, $end])
            ->selectRaw('sale_date as date, SUM(total_amount) as revenue')
            ->groupBy('sale_date')
            ->pluck('revenue', 'date');  // pluck it will return collection  with key '2026-06-20' and value 100

        // find total costing that sale day by day
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

        // loop data
        foreach ($dates as $date) {
            // check date have sale or not ? if don't have (null) it wil replace 0
            $r = (float) ($revenueRows[$date] ?? 0);
            $c = (float) ($costRows[$date] ?? 0);

            $categories[] = $date; // insert date
            $revenue[] = $r; // insert revenue
            $cost[] = $c;
            $profit[] = $r - $c; // revenue - cost
        }

        return [
            'categories' => $categories,
            'revenue'    => $revenue,
            'cost'       => $cost,
            'profit'     => $profit,
        ];
    }

    // show data for donut chart
    public function getDonutChart(string $period = 'today'): array
    {
        [$start, $end] = $this->getDateRange($period);

        // get all completed sales in the period with their total_amount
        $salesTotals = DB::table('sales')
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$start, $end])
            ->pluck('total_amount', 'id');

        if ($salesTotals->isEmpty()) {
            return ['labels' => [], 'series' => []];
        }

        // get item by category
        $itemRows = DB::table('sale_items as si')
            ->join('products as p', 'p.code', '=', 'si.product_code')
            ->join('categories as c', 'c.code', '=', 'p.category_code')
            ->whereIn('si.sale_id', $salesTotals->keys()->toArray())
            ->selectRaw('si.sale_id, c.name as category, SUM(si.amount) as category_amount')
            ->groupBy('si.sale_id', 'c.name')
            ->get();

        // Organize revenue of by category
        $bySale = [];
        foreach ($itemRows as $r) {
            $saleId = $r->sale_id; // product it come from where invoice
            $cat = $r->category; // type of category
            $amt = (float) $r->category_amount; // price

            $bySale[$saleId][$cat] = ($bySale[$saleId][$cat] ?? 0) + $amt;
        }

        $categoryTotals = [];
        foreach ($salesTotals as $saleId => $saleTotal) {
            $saleTotal = (float) $saleTotal;
            $itemsForSale = $bySale[$saleId] ?? []; // get list by category for invoice
            // sum item
            $itemsSum = array_sum($itemsForSale);

            if ($itemsSum <= 0) {
                // If no item amounts, skip allocation
                continue;
            }

            foreach ($itemsForSale as $cat => $catAmt) {
                // allowcated amount = sale Total * (category item amount / total item amount in that sale )
                $alloc = $saleTotal * ($catAmt / $itemsSum);
                $categoryTotals[$cat] = ($categoryTotals[$cat] ?? 0) + $alloc;
            }
        }

        // Sort categories by total desc
        // big to small
        arsort($categoryTotals);

        $labels = array_keys($categoryTotals); // get all name category to make label on graph
        $series = array_map(fn ($v) => round((float) $v, 2), array_values($categoryTotals));

        return [
            'labels' => $labels,
            'series' => $series,
        ];
    }

    // get date range
    private function getDateRange(string $period): array
    {
        // check accurate of priroid
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
