<?php

namespace App\Http\Controllers\Stock;

use App\Models\Product;
use Illuminate\View\View;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Models\StockAjustment;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use App\Services\Inventory\StockAjustmentService;
use App\Http\Requests\Stock\StockAjustmentStoreRequest;
use App\Http\Requests\Stock\StockAjustmentUpdateRequest;

class StockAjustmentController extends Controller
{
    // inject service
    public function __construct(

        protected StockAjustmentService $stockAjustmentService

    ) {}

    // display listing of stock ajustment
    public function index(Request $request)
    {
        $filters = $request->only([
            'status', 'warehouse_id', 'product_code', 'date_from', 'date_to', 'per_page',
        ]);

        $adjustments = $this->stockAjustmentService->list($filters);

        return view('admin.products.stock.index', compact('adjustments', 'filters'));
    }

    // show form create new stock ajustment
    public function create(): View
    {
        $products   = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('admin.products.stock.create', compact('products', 'warehouses'));
    }

    // created stock ajustment
    public function store(StockAjustmentStoreRequest $request): RedirectResponse
    {
        $adjustment = $this->stockAjustmentService->create([
            ...$request->validated(), // ... spreate operator, it mean insert all array
            'created_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('admin.products.stock.show', $adjustment)
            ->with('success', 'Stock adjustment created successfully.');
    }

    // display single stock ajustment
    public function show(StockAjustment $stockAjustment): View
    {
        $stockAjustment->load(['product', 'warehouse', 'creator', 'approver']);

        return view('admin.products.stock.show', compact('stockAjustment'));
    }

    /**
     * Show the form for editing a pending stock adjustment.
     */
    public function edit(StockAjustment $stockAjustment): View|RedirectResponse
    {
        if ($stockAjustment->status !== 'pending') {
            return redirect()
                ->route('stock-adjustments.show', $stockAjustment)
                ->with('error', 'Only pending adjustments can be edited.');
        }

        $products   = Product::orderBy('name')->get();
        $warehouses = Warehouse::orderBy('name')->get();

        return view('admin.products.stock.edit', compact('stockAjustment', 'products', 'warehouses'));
    }

    // update pending stock ajustment
    public function update(StockAjustmentUpdateRequest $request, StockAjustment $stockAjustment): RedirectResponse
    {
        try {
            $this->stockAjustmentService->update($stockAjustment, $request->validated());
        } catch (ValidationException $e) {
            return back()
                ->withErrors($e->errors())
                ->withInput();
        }

        return redirect()
            ->route('admin.products.stock.show', $stockAjustment)
            ->with('success', 'Stock adjustment updated successfully.');
    }

    // remove pending stock ajustment
    public function destroy(StockAjustment $stockAjustment): RedirectResponse
    {
        try {
            $this->stockAjustmentService->delete($stockAjustment);
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.products.stock.index')
            ->with('success', 'Stock adjustment deleted successfully.');
    }

    // aprove a pending stock adjustment
    public function approve(Request $request, StockAjustment $stockAjustment): RedirectResponse
    {
        try {
            $this->stockAjustmentService->approve($stockAjustment, $request->user()->id);
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.products.stock.show', $stockAjustment)
            ->with('success', 'Stock adjustment approved successfully.');
    }

    // reject a pending stock ajustment
    public function reject(Request $request, StockAjustment $stockAjustment): RedirectResponse
    {
        $request->validate([
            'remark' => ['nullable', 'string', 'max:1000'],
        ]);

        try {
            $this->stockAjustmentService->reject(
                $stockAjustment,
                $request->user()->id,
                $request->input('remark')
            );
        } catch (ValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.products.stock.show', $stockAjustment)
            ->with('success', 'Stock adjustment rejected successfully.');
    }
}
