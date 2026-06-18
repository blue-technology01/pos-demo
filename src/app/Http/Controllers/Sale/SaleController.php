<?php

namespace App\Http\Controllers\Sale;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sale\SaleStoreRequest;
use App\Http\Requests\Sale\SaleUpdateRequest;
use App\Models\Sale;
use App\Services\Sale\SaleService;
use App\Services\Sale\StockGuardService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    protected $saleService;
    protected StockGuardService $stockGuardService;


    public function __construct(SaleService $saleService, StockGuardService $stockGuardService)
    {
        $this->saleService = $saleService;
        $this->stockGuardService = $stockGuardService;
    }

    /**
     * Display list of sales for admin.
     */
    public function index(Request $request)
    {
        $sales = $this->saleService->getAllSales($request);

        return view('admin.sales.sale-history.index', compact('sales'));
    }

    /**
     * Display a single sale detail / receipt.
     */
    public function show(int $id)
    {
        $sale = Sale::with('items')->findOrFail($id);

        return view('admin.sales.sale-history.show', compact('sale'));
    }

    /**
     * Show edit form for a sale.
     */
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
        try {
            $sale = $this->saleService->createSale($request->validated());
            return response()->json([
                'success'    => true,
                'message'    => "sale is #{$sale->invoice_no} successfully!",
                'invoice_no' => $sale->invoice_no,
                'sale_id'    => $sale->id
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * Update an existing sale.
     */
    public function update(SaleUpdateRequest $request, int $id)
    {
        try {
            $sale = $this->saleService->updateSale($id, $request->validated());

            return redirect()->route('admin.sales.index')
                ->with('success', "Reciept  #{$sale->invoice_no} edit successfullly.!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->withInput()
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Cancel a sale and restore stock.
     */
    public function cancel(int $id)
    {
        try {
            $sale = $this->saleService->cancelSale($id);

            return redirect()->back()
                ->with('success', "Reciept #{$sale->invoice_no} is cancel and restore to stock ready!");

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', $e->getMessage());
        }
    }
}
