<?php

namespace App\Services\Report;

use Carbon\Carbon;
use App\Models\Sale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RevenueTrackingService
{
    private const MAX_DAYS = 365;

    public function getData(Request $request): array
    {
        [$startDate, $endDate] = $this->resolveDateRange(
            $request->input('start_date'),
            $request->input('end_date'),
        );

        $rows = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->selectRaw('
                DATE(sale_date)             as date,
                COUNT(*)                    as total_orders,
                SUM(total_amount)           as total_revenue,
                ROUND(AVG(total_amount), 2) as average_sale
            ')

            ->groupBy(DB::raw('DATE(sale_date)'))
            ->orderByDesc(DB::raw('DATE(sale_date)'))
            ->paginate($request->input('per_page', 15))
            ->withQueryString(); // it make sure when click from to page to other it will not remove ( start - end ) date

        $summary = Sale::query()
            ->where('status', 'completed')
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->selectRaw('
                COUNT(*)                      as total_orders,
                SUM(total_amount)             as total_revenue,
                ROUND(AVG(total_amount), 2)   as average_sale
            ')
            ->first();

        return [
            'rows'      => $rows,
            'summary'   => [
                'total_orders'  => (int)   ($summary->total_orders  ?? 0),
                'total_revenue' => (float) ($summary->total_revenue ?? 0),
                'average_sale'  => (float) ($summary->average_sale  ?? 0),
            ],
            'startDate' => $startDate,
            'endDate'   => $endDate,
        ];
    }

    public function resolveDateRange(?string $startDate, ?string $endDate): array
    {
        if (!$startDate || !$endDate) {
            return [
                now()->subDays(29)->format('Y-m-d'),
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
}
