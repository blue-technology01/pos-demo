<?php

namespace App\Http\Controllers\Product;

use Illuminate\View\View;
use Illuminate\Http\Request;
use App\Services\Product\UomService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use App\Http\Requests\Product\UomRequest;

class UomController extends Controller
{
    // injection of service
    public function __construct(
        private readonly UomService $uomService
    ) {}

    // Display all active uoms.
    public function index(Request $request): View
    {
        $uoms = $this->uomService->getAll($request);

        return view('admin.products.unit', compact('uoms'));
    }
    // store new created uom
    public function store(UomRequest $request): RedirectResponse
    {
        $this->uomService->create($request->validated());
        return redirect()
            ->route('admin.unit')
            ->with('success', 'Unit of Measure created successfully.');
    }

    // update existing uom
    public function update(UomRequest $request, string $code)
    {
        $this->uomService->update($code, $request->validated());
        return redirect()->back()->with('success', 'Updated successfully.');
    }

    // deactivate uom
    public function destroy(string $code): RedirectResponse
    {
        $this->uomService->deactivate($code);

        return redirect()
            ->route('admin.unit')
            ->with('success', 'Unit of Measure deactivated successfully.');
    }
}
