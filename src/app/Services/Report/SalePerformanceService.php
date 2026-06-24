<?php

namespace App\Services\Report;

use Illuminate\Pagination\LengthAwarePaginator;
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
        string $search = '',
        int $perPage = 15,
        int $page = 1
    ): array {

        $perPage = max(1, $perPage);

        if ($startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }

        $baseQuery = DB::table('sales')
            ->join('users', 'users.id', '=', 'sales.user_id')
            ->whereBetween('sales.sale_date', [$startDate, $endDate])
            ->where('sales.status', 'completed')
            ->when(
                $search,
                fn ($q) => $q->where('users.name', 'like', "%{$search}%")
            );

        $total = (clone $baseQuery)
            ->groupBy('users.id', 'users.name', 'users.avatar')
            ->select('users.id')
            ->get()
            ->count();

        $maxRevenue = (clone $baseQuery)
            ->groupBy('users.id')
            ->selectRaw('SUM(sales.total_amount) as total_revenue')
            ->orderByDesc('total_revenue')
            ->value('total_revenue') ?: 1;

        $offset = ($page - 1) * $perPage;

        $items = (clone $baseQuery)
            ->groupBy('users.id', 'users.name', 'users.avatar')
            ->select(
                'users.id',
                DB::raw('users.name as staff_name'),
                'users.avatar',
                DB::raw('COUNT(sales.id) as total_orders'),
                DB::raw('SUM(sales.total_amount) as total_revenue'),
                DB::raw('AVG(sales.total_amount) as avg_per_order'),
                DB::raw('SUM(sales.discount_amount) as total_discount')
            )
            ->orderByDesc('total_revenue')
            ->offset($offset)
            ->limit($perPage)
            ->get()
            ->map(function ($row) use ($maxRevenue) {

                $row->total_orders = (int) $row->total_orders;
                $row->total_revenue = (float) $row->total_revenue;
                $row->avg_per_order = (float) $row->avg_per_order;
                $row->total_discount = (float) $row->total_discount;

                $row->performance = (int) round(
                    ($row->total_revenue / $maxRevenue) * 100
                );

                return $row;
            });

        $rows = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $page,
            [
                'path' => request()->url(),
                'query' => request()->query(),
            ]
        );

        return [
            'rows' => $rows,
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
