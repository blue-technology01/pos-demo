<?php

namespace App\Services\Report;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;

class TopProductService
{

    // get paginate product for the list table
    public function getPaginatedProducts(Request $request): LengthAwarePaginator
    {
        $paginator = $this->buildQuery($request)
            ->paginate($request->per_page ?? 15)
            ->withQueryString();

        $offset = ($paginator->currentPage() - 1) * $paginator->perPage();

        $paginator->getCollection()->transform(function ($item, $index) use ($offset) {
            // offset = (2-1) * 15  = 15
            $item->rank         = $offset + $index + 1; // rank = 15+0+1
            $item->qty_sold     = (int) $item->qty_sold;
            $item->stock_status = $this->getStockStatus((float) $item->stock_left);
            return $item;
        });

        return $paginator;
    }

    // get full collection for summary cards and chart
    public function getTopProducts(Request $request, int $limit = 20): Collection
    {
        return $this->buildQuery($request)
            ->limit($limit)  // limit number that need for chart
            ->get()
            ->map(function ($item, $index) {
                $item->rank         = $index + 1;
                $item->qty_sold     = (int) $item->qty_sold;
                $item->stock_status = $this->getStockStatus((float) $item->stock_left);
                return $item;
            });
    }

    // shared base query used by all method below
    private function buildQuery(Request $request)
    {
        $query = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->join('products as p', 'p.code', '=', 'si.product_code')
            ->leftJoin('categories as c', 'c.code', '=', 'p.category_code')
            ->where('s.status', 'completed')
            ->select([
                'si.product_code',
                'si.product_name',
                'c.name as category',
                'p.stock as stock_left',
                DB::raw('AVG(si.unit_price) as unit_price'),
                DB::raw('SUM(si.quantity) as qty_sold'),
                DB::raw('SUM((si.amount / NULLIF(s.sub_total, 0)) * s.total_amount) as total_revenue'),
            ])
            ->groupBy(
                'si.product_code',
                'si.product_name',
                'c.name',
                'p.stock'
            )
            ->orderByDesc('qty_sold');

        $this->applyFilters($query, $request);

        return $query;
    }

    private function applyFilters($query, Request $request): void
    {
        if ($search = trim((string) $request->input('search'))) {
            $query->where('si.product_name', 'like', "%{$search}%");
        }

        if ($categoryCode = $request->input('category_code')) {
            $query->where('p.category_code', $categoryCode);
        }

        $dateFrom = $request->input('date_from');
        $dateTo   = $request->input('date_to');

        if ($dateFrom) {
            $query->where('s.sale_date', '>=', Carbon::parse($dateFrom)->startOfDay());
        }
        if ($dateTo) {
            $query->where('s.sale_date', '<=', Carbon::parse($dateTo)->endOfDay());
        }
    }

    // function for check stock status
    public function getStockStatus(float $stock): string
    {
        if ($stock <= 0)  return 'Out of Stock';
        if ($stock <= 10) return 'Low Stock';

        return 'In Stock';
    }

    // function for get summary
    public function getSummary(Request $request): array
    {
        // clonse query for store base query
        $query= $this->buildQuery($request);

        // make subquery from query for calculate  summary
        $data = DB::table(DB::raw("({$query->toSql()}) as sub"))
            ->mergeBindings($query)
            ->selectRaw('
                COUNT(*) as total_products,
                SUM(qty_sold) as total_qty_sold,
                SUM(total_revenue) as total_revenue,
                SUM(CASE WHEN stock_left <= 0 THEN 1 ELSE 0 END) as out_of_stock,
                SUM(CASE WHEN stock_left BETWEEN 1 AND 10 THEN 1 ELSE 0 END) as low_stock
            ')
            ->first();

        return [
            'total_products' => (int) $data->total_products,
            'total_qty_sold' => (int) $data->total_qty_sold,
            'total_revenue'  => (float) $data->total_revenue,
            'out_of_stock'   => (int) $data->out_of_stock,
            'low_stock'      => (int) $data->low_stock,
        ];
    }

    // export filtered products to excel
    public function getExportProducts(Request $request): Collection
    {
        $base = $this->buildQuery($request);

        $subQuery = DB::table(DB::raw("({$base->toSql()}) as sub"))
            ->mergeBindings($base);

        return collect([
            [
                'total_products' => (clone $subQuery)->count(),
                'total_qty_sold' => (int) (clone $subQuery)->sum('qty_sold'),
                'total_revenue'  => (float) (clone $subQuery)->sum('total_revenue'),
                'out_of_stock'   => (int) (clone $subQuery)->where('stock_left', '<=', 0)->count(),
                'low_stock'      => (int) (clone $subQuery)->whereBetween('stock_left', [1, 10])->count(),
            ]
        ]);
    }
}
