<?php

namespace App\Services\Report;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class TopProductService
{
    /**
     * Get top products report data
     *
     * @param array $filters
     * @return Collection
     */
    public function getTopProducts(array $filters = []): Collection
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

        // Filter by category
        if (!empty($filters['category_code'])) {
            $query->where('p.category_code', $filters['category_code']);
        }

        // Filter by product name
        if (!empty($filters['search'])) {
            $query->where('si.product_name', 'like', '%' . $filters['search'] . '%');
        }

        // Filter by date range
        if (!empty($filters['date_from'])) {
            $query->whereDate('s.sale_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('s.sale_date', '<=', $filters['date_to']);
        }

        // Filter by limit
        if (!empty($filters['limit'])) {
            $query->limit($filters['limit']);
        }

        return $query->get()->map(function ($item, $index) {
            $item->rank         = $index + 1;
            $item->qty_sold     = (int) $item->qty_sold;
            $item->stock_status = $this->getStockStatus($item->stock_left);
            return $item;
        });
    }

    /**
     * Get stock status label based on stock quantity
     *
     * @param float $stock
     * @return string
     */
    public function getStockStatus(float $stock): string
    {
        if ($stock <= 0) {
            return 'Out of Stock';
        }

        if ($stock <= 10) {
            return 'Low Stock';
        }

        return 'In Stock';
    }

    /**
     * Get summary totals for the report
     *
     * @param Collection $products
     * @return array
     */
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
