<?php

namespace App\Http\Controllers\Sale;

use Illuminate\Http\Request;
use App\Services\Sale\SaleService;
use App\Http\Controllers\Controller;
use App\Services\Sale\SaleItemService;
use App\Http\Requests\Sale\SaleItemStoreRequest;
use App\Http\Requests\Sale\SaleItemUpdateRequest;

class SaleItemController extends Controller
{
    public function __construct(
        protected SaleItemService $saleItemService,
        protected SaleService $saleService,
    ) {}

    public function store(SaleItemStoreRequest $request)
    {
        $data = $request->validated();
        unset($data['amount']);

        $items   = $this->saleItemService->addItem($data);
        $summary = $this->saleItemService->getSummary();

        return response()->json([
            'success' => true,
            'items'   => $items,
            'summary' => $summary,
        ]);
    }

    public function update(SaleItemUpdateRequest $request, string $rowId)
    {
        $data = $request->validated();
        unset($data['amount'], $data['sale_id']);

        $items   = $this->saleItemService->updateItem($rowId, $data);
        $summary = $this->saleItemService->getSummary();

        return response()->json([
            'success' => true,
            'items'   => $items,
            'summary' => $summary,
        ]);
    }

    public function destroy(string $rowId)
    {
        $items   = $this->saleItemService->removeItem($rowId);
        $summary = $this->saleItemService->getSummary();

        return response()->json([
            'success' => true,
            'items'   => $items,
            'summary' => $summary,
        ]);
    }

    public function clear()
    {
        $this->saleItemService->clearItems();

        return response()->json(['success' => true]);
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'payment_method' => ['required', 'string', 'in:cash,card,qr'],
            'paid_amount'    => ['required', 'numeric', 'min:0'],
            'tax_amount'     => ['nullable', 'numeric', 'min:0'],
            'total_amount'   => ['required', 'numeric', 'min:0'],
            'change_amount'  => ['nullable', 'numeric', 'min:0'],
            'customer_id'    => ['nullable', 'integer', 'exists:customers,id'],
            'register_id'    => ['nullable', 'integer', 'exists:cash_registers,id'],
        ]);

        try {
            $sale = $this->saleService->confirmSale($request->all());

            return response()->json([
                'success'    => true,
                'message'    => 'Sale completed.',
                'invoice_no' => $sale->invoice_no,
                'sale_id'    => $sale->id,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }
}
