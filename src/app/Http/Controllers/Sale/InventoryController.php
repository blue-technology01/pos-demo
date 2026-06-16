<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\SaleItem;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $totalProducts = Product::count();
        $outOfStock    = Product::where('stock', 0)->count();
        $lowStock      = Product::whereColumn('stock', '<', 'min_stock')
                                ->where('stock', '>', 0)
                                ->count();
        $healthyStock  = Product::whereColumn('stock', '>=', 'min_stock')->count();

        $products = Product::with('category')
            ->when($search, fn($q) => $q->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            }))
            ->orderBy('stock')
            ->paginate(15)
            ->withQueryString();

        $lowStockProducts = Product::with('category')
            ->whereColumn('stock', '<', 'min_stock')
            ->orderBy('stock')
            ->get();

        $activities = SaleItem::with(['sale.user', 'product.category'])
            ->whereHas('sale', fn($q) => $q->whereDate('sale_date', today()))
            ->latest('id')
            ->paginate(20);

        return view('admin.stocks.stock-update', compact(
            'totalProducts',
            'outOfStock',
            'lowStock',
            'healthyStock',
            'products',
            'lowStockProducts',
            'activities',
            'search',
        ));
    }
}
