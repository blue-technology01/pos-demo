<?php

namespace App\Http\Controllers\Cash;

use Illuminate\Http\Request;
use App\Models\CashRegister;
use App\Exports\CashRegisterExport;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\Cash\CashRegisterService;
use App\Http\Requests\Cash\CashRegisterStoreRequest;
use App\Http\Requests\Cash\CashRegisterUpdateRequest;

class CashController extends Controller
{
    // inject service
    public function __construct(
        protected CashRegisterService $cashRegisterService
    ) {}

    /* export data to excel */
    public function export() {
        return Excel::download(
            new CashRegisterExport(),
            'Cash-register-report.xlsx'
        );
    }

    /* show cash register history */
    public function index(Request $request)
    {
        // pagination history
        $history = $this->cashRegisterService->getAllHistory($request);

        // check shift that opening
        $currentRegister = $this->cashRegisterService->getCurrentOpenRegister();

        return view('admin.sales.shift', compact('history', 'currentRegister'));
    }

    /* open shift for cashier on POS */
    public function open(CashRegisterStoreRequest $request)
    {
        try {

            $this->cashRegisterService->openRegister($request->validated());
            return redirect()->back()->with('success', 'Opening shift successfully.');

        } catch (\Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());

        }
    }

    /* close shift */
    public function close(CashRegisterUpdateRequest $request)
    {
        try {
            $currentShift = $this->cashRegisterService->getCurrentOpenRegister();

            if (!$currentShift) {
                return redirect()->back()->with('error', 'No active shift found to close.');
            }

            $this->cashRegisterService->closeRegister($currentShift->id, $request->validated());

            return redirect()->back()->with('success', 'Shift closed successfully.');

        } catch (\Exception $e) {
           return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /* view detail about cashier register */
    public function show($id)
    {
        try {
            $register = CashRegister::with('user:id,name')->findOrFail($id);
            return view('admin.sales.shift-detail', compact('register'));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Data not found.');
        }
    }
}
