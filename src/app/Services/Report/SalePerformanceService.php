<?php

namespace App\Services\Report;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class SalePerformanceService
{
    /**
     * Paginated staff performance rows.
     * Uses DB-level pagination for scalability.
     */
    public function getStaffPerformance(
        string $startDate,
        string $endDate,
        string $search  = '',
        int    $perPage = 15,
        int    $page    = 1
    ): array {
        // Guard: prevent division by zero
        $perPage = max(1, $perPage);

        // Guard: swap dates if inverted
        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        // Base query builder (reused for count + maxRevenue + rows)
        $baseQuery = DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.status', 'completed')
            ->when(
                $search,
                fn($q) => $q->where('users.name', 'like', "%{$search}%")
            );

        // Total count for pagination (DB-level, no full fetch)
        $total = (clone $baseQuery)
            ->groupBy('users.id', 'users.name', 'users.avatar')
            ->select('users.id')
            ->get()
            ->count();

        // Max revenue across ALL matching staff (for performance %)
        // Separate query so pagination doesn't affect it
        $maxRevenue = (clone $baseQuery)
            ->groupBy('users.id')
            ->selectRaw('SUM(sales.total_amount) as total_revenue')
            ->orderByDesc('total_revenue')
            ->value('total_revenue') ?: 1;

        // Paginated rows from DB
        $offset = ($page - 1) * $perPage;

        $rows = (clone $baseQuery)
            ->groupBy('users.id', 'users.name', 'users.avatar')
            ->select(
                'users.id',
                DB::raw('users.name    as staff_name'),
                'users.avatar',
                DB::raw('COUNT(sales.id)            as total_orders'),
                DB::raw('SUM(sales.total_amount)    as total_revenue'),
                DB::raw('AVG(sales.total_amount)    as avg_per_order'),
                DB::raw('SUM(sales.discount_amount) as total_discount'),
            )
            ->orderByDesc('total_revenue')
            ->offset($offset)
            ->limit($perPage)
            ->get()
            ->map(fn($row) => [
                'id'             => $row->id,
                'staff_name'     => $row->staff_name,
                'avatar'         => $row->avatar,
                'total_orders'   => (int)   $row->total_orders,
                'total_revenue'  => (float) $row->total_revenue,
                'avg_per_order'  => (float) $row->avg_per_order,
                'total_discount' => (float) $row->total_discount,
                'performance'    => (int) round(
                    (float) $row->total_revenue / $maxRevenue * 100
                ),
            ])
            ->toArray(); // Ensure clean JSON serialization

        $lastPage = (int) ceil($total / $perPage) ?: 1;

        return [
            'rows'       => $rows,
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

    /**
     * KPI summary — total revenue, orders, avg for header cards.
     */
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

    /**
     * Top performer — single best staff member this period.
     */
    public function getTopPerformer(string $startDate, string $endDate): ?object
    {
        return DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.status', 'completed')
            ->groupBy('users.id', 'users.name', 'users.avatar')
            ->select(
                'users.id',
                DB::raw('users.name    as staff_name'),
                'users.avatar',
                DB::raw('COUNT(sales.id)          as total_orders'),
                DB::raw('SUM(sales.total_amount)  as total_revenue'),
                DB::raw('AVG(sales.total_amount)  as avg_per_order'),
            )
            ->orderByDesc('total_revenue')
            ->first();
    }

    /**
     * Chart data — top 6 staff for donut chart.
     */
    public function getChartData(string $startDate, string $endDate): Collection
    {
        return DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.status', 'completed')
            ->groupBy('users.id', 'users.name')
            ->select(
                DB::raw('users.name as staff_name'),
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

    /**
     * Payment method breakdown per staff.
     */
    public function getPaymentBreakdown(string $startDate, string $endDate): Collection
    {
        return DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.status', 'completed')
            ->groupBy('users.id', 'users.name', 'sales.payment_method')
            ->select(
                DB::raw('users.name             as staff_name'),
                'sales.payment_method',
                DB::raw('COUNT(sales.id)            as total_orders'),
                DB::raw('SUM(sales.total_amount)    as total_revenue'),
            )
            ->orderBy('users.name')
            ->orderByDesc('total_revenue')
            ->get();
    }

    /**
     * Voided / refunded sales — audit per staff.
     */
    public function getVoidedSales(string $startDate, string $endDate): Collection
    {
        return DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->whereIn('sales.status', ['voided', 'refunded'])
            ->groupBy('users.id', 'users.name', 'sales.status')
            ->select(
                DB::raw('users.name             as staff_name'),
                'sales.status',
                DB::raw('COUNT(sales.id)            as total_count'),
                DB::raw('SUM(sales.total_amount)    as total_amount'),
            )
            ->orderByDesc('total_count')
            ->get();
    }
}
