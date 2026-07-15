<?php

namespace App\Http\Controllers\Warehouse;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Inventory\WarehousesService;
use App\Http\Requests\Stock\WarehouseStoreRequest;
use App\Http\Requests\Stock\WarehouseUpdateRequest;

class WarehouseController extends Controller
{
     public function __construct(protected WarehousesService $warehouseService) {}

    // get all warehouses
    public function index(Request $request)
    {
        $warehouses = $this->warehouseService->getFilteredWarehouses(
            $request->only(['search', 'is_active']),
            (int) $request->input('per_page', 15)
        );

        return view('admin.warehouses.index', compact('warehouses'));
    }

    // create new warehouse
    public function store(WarehouseStoreRequest $request)
    {
        $this->warehouseService->createWarehouse($request->validated());

        return redirect()
            ->route('admin.warehouses.index')
            ->with('success', 'Warehouse created successfully.');
    }

    // Update warehouse
    public function update(WarehouseUpdateRequest $request, Warehouse $warehouse) {

        $this->warehouseService->updateWarehouse(
            $warehouse,
            $request->validated()
        );

        return redirect()
            ->route('admin.warehouses.index')
            ->with('success', 'Warehouse updated successfully.');
    }

    // remove warehouse
    public function destroy(Warehouse $warehouse)
    {
        try {
            $this->warehouseService->deleteWarehouse($warehouse);
            return redirect()->route('admin.warehouses.index')->with('success', 'Warehouse remove!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
