<?php

namespace App\Services\Report;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class TopProductService
{
    /**
     * Get paginated products for the list table.
     */
    public function getPaginatedProducts(Request $request): LengthAwarePaginator
    {
        $paginator = $this->buildQuery($request)
            ->paginate($request->per_page ?? 15)
            ->withQueryString();

        $offset = ($paginator->currentPage() - 1) * $paginator->perPage();

        $paginator->getCollection()->transform(function ($item, $index) use ($offset) {
            $item->rank         = $offset + $index + 1;
            $item->qty_sold     = (int) $item->qty_sold;
            $item->stock_status = $this->getStockStatus((float) $item->stock_left);
            return $item;
        });

        return $paginator;
    }

    /**
     * Get full collection for summary cards and chart.
     */
    public function getTopProducts(Request $request): Collection
    {
        return $this->buildQuery($request)
            ->get()
            ->map(function ($item, $index) {
                $item->rank         = $index + 1;
                $item->qty_sold     = (int) $item->qty_sold;
                $item->stock_status = $this->getStockStatus((float) $item->stock_left);
                return $item;
            });
    }

    /**
     * Shared base query used by both methods above.
     */
    private function buildQuery(Request $request)
    {
        $query = DB::table('sale_items as si')
            ->join('sales as s', 's.id', '=', 'si.sale_id')
            ->join('products as p', 'p.code', '=', 'si.product_code')
            ->leftJoin('categories as c', 'c.code', '=', 'p.category_code')
            ->where('s.status', 'completed')
            ->groupBy(
                'si.product_code',
                'si.product_name',
                'c.name',
                'p.stock'
            )
            ->select([
                'si.product_code',
                'si.product_name',
                'c.name as category',
                'p.stock as stock_left',
                DB::raw('AVG(si.unit_price) as unit_price'),
                DB::raw('SUM(si.quantity) as qty_sold'),
                DB::raw('SUM((si.amount / NULLIF(s.sub_total, 0)) * s.total_amount) as total_revenue'),
            ])
            ->orderByDesc('qty_sold');

        if ($search = $request->search) {
            $query->where('si.product_name', 'like', "%{$search}%");
        }

        if ($categoryCode = $request->category_code) {
            $query->where('p.category_code', $categoryCode);
        }

        if ($request->date_from && $request->date_to) {
            $query->whereBetween('s.sale_date', [
                $request->date_from . ' 00:00:00',
                $request->date_to   . ' 23:59:59',
            ]);
        } elseif ($request->date_from) {
            $query->where('s.sale_date', '>=', $request->date_from . ' 00:00:00');
        } elseif ($request->date_to) {
            $query->where('s.sale_date', '<=', $request->date_to . ' 23:59:59');
        }

        return $query;
    }

    public function getStockStatus(float $stock): string
    {
        if ($stock <= 0)  return 'Out of Stock';
        if ($stock <= 10) return 'Low Stock';
        return 'In Stock';
    }

    public function getSummary(Collection $products): array
    {
        return [
            'total_products' => $products->count(),
            'total_qty_sold' => (int) $products->sum('qty_sold'),
            'total_revenue'  => $products->sum('total_revenue'),
            'out_of_stock'   => $products->where('stock_left', '<=', 0)->count(),
            'low_stock'      => $products->whereBetween('stock_left', [1, 10])->count(),
        ];
    }
}
