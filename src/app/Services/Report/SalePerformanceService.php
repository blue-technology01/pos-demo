<?php

namespace App\Services\Report;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class SalePerformanceService
{
    // paginated staff performance rows
    public function getStaffPerformance(
        string $startDate,
        string $endDate,
        string $search   = '',
        int    $perPage  = 15,
        int    $page     = 1
    ): array {
        $query = DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.status', 'completed')
            ->when($search, fn($q) => $q->where('users.name', 'like', "%{$search}%"))
            ->groupBy('users.id', 'users.name', 'users.avatar')
            ->select(
                'users.id',
                'users.name   as staff_name',
                'users.avatar',
                DB::raw('COUNT(sales.id)              as total_orders'),
                DB::raw('SUM(sales.total_amount)      as total_revenue'),
                DB::raw('AVG(sales.total_amount)      as avg_per_order'),
                DB::raw('SUM(sales.discount_amount)   as total_discount'),
            )
            ->orderByDesc('total_revenue');

        $allRows    = $query->get();
        $total      = $allRows->count();
        $maxRevenue = $allRows->max('total_revenue') ?: 1;

        // Map + calculate performance %
        $allRows = $allRows->map(fn($row) => [
            'id'            => $row->id,
            'staff_name'    => $row->staff_name,
            'avatar'        => $row->avatar,
            'total_orders'  => (int)   $row->total_orders,
            'total_revenue' => (float) $row->total_revenue,
            'avg_per_order' => (float) $row->avg_per_order,
            'total_discount'=> (float) $row->total_discount,
            'performance'   => (int) round(
                (float) $row->total_revenue / $maxRevenue * 100
            ),
        ]);

        // Manual pagination
        $offset   = ($page - 1) * $perPage;
        $rows     = $allRows->slice($offset, $perPage)->values();
        $lastPage = (int) ceil($total / $perPage) ?: 1;

        return [
            'rows' => $rows,
            'pagination' => [
                'total'        => $total,
                'per_page'     => $perPage,
                'current_page' => $page,
                'last_page'    => $lastPage,
                'from'         => $total > 0 ? $offset + 1 : 0,
                'to'           => min($offset + $perPage, $total),
            ],
        ];
    }

    // KPI SUMMARY — total revenue, orders, avg for header cards
    public function getSummary(string $startDate, string $endDate): array
    {
        $result = DB::table('sales')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->where('status', 'completed')
            ->selectRaw('
                COUNT(id)               as total_orders,
                SUM(total_amount)       as total_revenue,
                AVG(total_amount)       as avg_per_order,
                SUM(discount_amount)    as total_discount,
                COUNT(DISTINCT user_id) as total_staff
            ')
            ->first();

        return [
            'total_orders'   => (int)   ($result->total_orders   ?? 0),
            'total_revenue'  => (float) ($result->total_revenue  ?? 0),
            'avg_per_order'  => (float) ($result->avg_per_order  ?? 0),
            'total_discount' => (float) ($result->total_discount ?? 0),
            'total_staff'    => (int)   ($result->total_staff    ?? 0),
        ];
    }

    // TOP PERFORMER — single best staff this period
    public function getTopPerformer(string $startDate, string $endDate): ?object
    {
        return DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.status', 'completed')
            ->groupBy('users.id', 'users.name', 'users.avatar')
            ->select(
                'users.id',
                'users.name   as staff_name',
                'users.avatar',
                DB::raw('COUNT(sales.id)          as total_orders'),
                DB::raw('SUM(sales.total_amount)  as total_revenue'),
                DB::raw('AVG(sales.total_amount)  as avg_per_order'),
            )
            ->orderByDesc('total_revenue')
            ->first();
    }

    // CHART DATA — top 6 staff for donut chart
    public function getChartData(string $startDate, string $endDate): Collection
    {
        return DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.status', 'completed')
            ->groupBy('users.id', 'users.name')
            ->select(
                'users.name as staff_name',
                DB::raw('SUM(sales.total_amount) as total_revenue'),
            )
            ->orderByDesc('total_revenue')
            ->limit(6)
            ->get()
            ->map(fn($row) => [
                'name'    => $row->staff_name,
                'revenue' => (float) $row->total_revenue,
            ]);
    }

    // PAYMENT METHOD BREAKDOWN PER STAFF
    public function getPaymentBreakdown(string $startDate, string $endDate): Collection
    {
        return DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.status', 'completed')
            ->groupBy('users.id', 'users.name', 'sales.payment_method')
            ->select(
                'users.name             as staff_name',
                'sales.payment_method',
                DB::raw('COUNT(sales.id)            as total_orders'),
                DB::raw('SUM(sales.total_amount)    as total_revenue'),
            )
            ->orderBy('users.name')
            ->orderByDesc('total_revenue')
            ->get();
    }

    // VOIDED / REFUNDED — audit per staff
    public function getVoidedSales(string $startDate, string $endDate): Collection
    {
        return DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->whereIn('sales.status', ['voided', 'refunded'])
            ->groupBy('users.id', 'users.name', 'sales.status')
            ->select(
                'users.name             as staff_name',
                'sales.status',
                DB::raw('COUNT(sales.id)            as total_count'),
                DB::raw('SUM(sales.total_amount)    as total_amount'),
            )
            ->orderByDesc('total_count')
            ->get();
    }
}
