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
        $search =trim($request->get('seach', ''));
        $perPage = (int) $request->get('per_page', 15);
        // dashboard statetic
        $totalProducts = Product::count();
        $outOfStock = Product::where('stock', 0)-> count();
        $lowStock = Product::whereColumn('stock', '<', 'min_stock')->where('stock', '>', 0)->count();
        $healthyStock = Product::whereColumn('stock', '>=', 'min_stock')->count();
        // product oveview
        $products = Product::with('category')
             ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('code', 'like', "%{$search}%");
                });
            })
            ->orderBy('stock', 'asc')
            ->paginate($perPage)
            ->withQueryString();
        // low stock product
        $lowStockProducts = Product::with('category')
            ->whereColumn('stock', '<', 'min_stock')
            ->orderBy('stock', 'asc')
            ->get();
        // stock active
        $activities = SaleItem::with([
            'sale.user',
            'product.category',
        ])
        ->whereHas('sale', function($query){
            $query->whereDate('sale_date', today());
        })
        ->latest('id')
        ->paginate($perPage)
        ->withQueryString();

        return view('admin.stocks.stock-update', [
            'totalProducts'   => $totalProducts,
            'outOfStock'      => $outOfStock,
            'lowStock'        => $lowStock,
            'healthyStock'    => $healthyStock,
            'products'        => $products,
            'lowStockProducts'=> $lowStockProducts,
            'activities'      => $activities,
            'search'          => $search,
        ]);
    }
}
