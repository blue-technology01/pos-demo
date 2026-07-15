<?php

namespace App\Http\Controllers\Stock;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Inventory\StockMovementService;

class StockMovementController extends Controller
{
    // dependency injection
    public function __construct(

        protected StockMovementService $stockMovementService

    ) {}

    // display stock movement hsitory
    public function index(Request $request): View
    {
    $filters = $request->validate([
            'product_code'  => ['nullable', 'string'],
            'date_from'     => ['nullable', 'date'],
            'date_to'       => ['nullable', 'date', 'after_or_equal:date_from'],
            'movement_type' => ['nullable', 'string', 'in:' . implode(',', StockMovementService::TYPES)],
        ]);

        $perPage = (int) $request->input('per_page', 15);

        $movements = $this->stockMovementService
            ->paginate($filters, $perPage)
            ->withQueryString();

        return view('admin.stocks.stock-movement', [
            'movements' => $movements,
            'filters'   => $filters,
        ]);
    }
}
