<?php

namespace App\Services\Report;

use App\Models\Sale;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RevenueTrackingService
{
    public function getData(
        ?string $startDate = null,
        ?string $endDate   = null,
        ?string $search    = null,
        int     $perPage   = 25,
        int     $page      = 1
    ): array {
        [$startDate, $endDate] = $this->resolveDateRange($startDate, $endDate);

        $cacheKey = "revenue_tracking:{$startDate}:{$endDate}:{$search}:{$perPage}:{$page}";

        $ttl = $search ? 0 : now()->addMinutes(5);

        return Cache::remember($cacheKey, $ttl, function () use (
            $startDate, $endDate, $search, $perPage, $page
        ) {
            $base = Sale::query()
            ->where('status', 'completed')
            ->whereRaw('sale_date BETWEEN ? AND ?', [$startDate, $endDate])
            ->selectRaw('
                sale_date_only                as date,
                COUNT(*)                      as total_orders,
                SUM(total_amount)             as total_revenue,
                ROUND(AVG(total_amount), 2)   as average_sale
            ')
            ->groupBy('sale_date_only');

        if ($search) {
            $base->havingRaw('sale_date_only LIKE ?', ["%{$search}%"]);
        }

            $total = DB::table(DB::raw("({$base->toSql()}) as sub"))
                ->mergeBindings($base->getQuery())
                ->count();

            $rows = (clone $base)
                ->orderByDesc(DB::raw('DATE(sale_date)'))
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            $summary = Sale::query()
                ->where('status', 'completed')
                ->whereRaw('sale_date BETWEEN ? AND ?', [$startDate, $endDate])
                ->selectRaw('
                    COUNT(*)                    as total_orders,
                    SUM(total_amount)           as total_revenue,
                    ROUND(AVG(total_amount), 2) as average_sale
                ')
                ->first();

            $lastPage = $total > 0 ? (int) ceil($total / $perPage) : 1;

            return [
                'rows' => $rows->map(fn($row) => [
                    'date'          => Carbon::parse($row->date)->format('d M Y'),
                    'total_orders'  => (int)   $row->total_orders,
                    'total_revenue' => (float) $row->total_revenue,
                    'average_sale'  => (float) $row->average_sale,
                ])->toArray(),

                'summary' => [
                    'total_orders'  => (int)   ($summary->total_orders  ?? 0),
                    'total_revenue' => (float) ($summary->total_revenue ?? 0),
                    'average_sale'  => (float) ($summary->average_sale  ?? 0),
                ],

                'pagination' => [
                    'total'        => $total,
                    'per_page'     => $perPage,
                    'current_page' => $page,
                    'last_page'    => $lastPage,
                    'from'         => $total > 0 ? ($page - 1) * $perPage + 1 : 0,
                    'to'           => min($page * $perPage, $total),
                ],

                'startDate' => $startDate,
                'endDate'   => $endDate,
            ];
        });
    }

    public function resolveDateRange(?string $startDate, ?string $endDate): array
    {
        if (!$startDate || !$endDate) {
            return [
                now()->subDays(29)->format('Y-m-d'),
                now()->format('Y-m-d'),
            ];
        }

        return [
            Carbon::parse($startDate)->format('Y-m-d'),
            Carbon::parse($endDate)->format('Y-m-d'),
        ];
    }
}
