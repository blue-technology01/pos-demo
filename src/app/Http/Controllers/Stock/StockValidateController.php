<?php

namespace App\Http\Controllers\Stock;

use Illuminate\Http\Request;
use App\Models\BlockedSaleAttempt;
use App\Http\Controllers\Controller;

class StockValidateController extends Controller
{
    // tracking cashiar that trying sale product on stock
    public function index(Request $request)
    {
        $attempts = BlockedSaleAttempt::with('productUom.product')
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('productUom.product', function ($q) use ($request) {
                    $q->where('name', 'LIKE', "%{$request->search}%");
                });
            })
            ->when($request->status, function ($q) use ($request) {
                if ($request->status === 'allowed') {
                    $q->where('reason', 'available');
                }

                if ($request->status === 'blocked') {
                    $q->whereIn('reason', [
                        'out_of_stock',
                        'insufficient_stock'
                    ]);
                }
            })
            ->orderByDesc('id')
            ->paginate($request->per_page ?? 15)
            ->withQueryString();

        $reasonCounts = BlockedSaleAttempt::query()
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('productUom.product', function ($q) use ($request) {
                    $q->where('name', 'LIKE', "%{$request->search}%");
                });
            })
            ->selectRaw('reason, COUNT(*) as total')
            ->groupBy('reason')
            ->pluck('total', 'reason');

        return view('admin.stocks.stock-validation', compact('attempts', 'reasonCounts'));
    }
}
