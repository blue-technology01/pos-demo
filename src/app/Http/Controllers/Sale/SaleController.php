<?php

namespace App\Http\Controllers\Sale;

use App\Models\Sale;
use Illuminate\Http\Request;
use App\Services\Sale\SaleService;
use App\Http\Controllers\Controller;
use App\Services\Sale\StockGuardService;
use App\Http\Requests\Sale\SaleStoreRequest;
use App\Http\Requests\Sale\SaleUpdateRequest;

class SaleController extends Controller
{
    // inject service
    public function __construct(

        protected SaleService $saleService,
        protected StockGuardService $stockGuardService

    ) {}

    // display list of sale history for admin
    public function index(Request $request)
    {
        $sales = $this->saleService->getAllSales($request);

        return view('admin.sales.sale-history.index', compact('sales'));
    }

    // display a single sale detail
    public function show(int $id)
    {
        $sale = Sale::with('items')->findOrFail($id);
        return view('admin.sales.sale-history.show', compact('sale'));
    }

    // for show form edit
    public function edit(int $id)
    {
        $sale = Sale::with('items')->findOrFail($id);
        return view('admin.sales.sale-history.edit', compact('sale'));
    }

    /**
     * Store a new sale from POS.
    */

    public function store(SaleStoreRequest $request)
    {
        \Log::info('Sale request data', [
            'customer_id' => $request->validated()['customer_id'] ?? 'NOT FOUND',
            'total_amount' => $request->validated()['total_amount'] ?? 'NOT FOUND',
        ]);

        try {
            $sale = $this->saleService->createSale($request->validated());
            return response()->json([
                'success'    => true,
                'message'    => "Sale #{$sale->invoice_no} created successfully!",
                'invoice_no' => $sale->invoice_no,
                'sale_id'    => $sale->id,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    // update sale existing sale
    public function update(SaleUpdateRequest $request, int $id)
    {
        try {
            $sale = $this->saleService->updateSale($id, $request->validated());

            return redirect()->route('admin.sales.index')
                ->with('success', "Receipt #{$sale->invoice_no} updated successfully!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    // cancel a sale and restore stock
    public function cancel(int $id)
    {
        try {
            $sale = $this->saleService->cancelSale($id);

            return redirect()->back()
                ->with('success', "Receipt #{$sale->invoice_no} has been voided and stock restored!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
