<?php

namespace App\Http\Controllers\Cash;

use App\Http\Controllers\Controller;
use App\Http\Requests\Cash\CashRegisterStoreRequest;
use App\Http\Requests\Cash\CashRegisterUpdateRequest;
use App\Models\CashRegister;
use App\Services\Cash\CashRegisterService;
use Illuminate\Http\Request;

class CashController extends Controller
{
    // inject service
    public function __construct(
        protected CashRegisterService $cashRegisterService
    ) {}

    // show cash register history ( admin/cashier )
    public function index(Request $request)
    {
        // pagination history
        $history = $this->cashRegisterService->getAllHistory($request);
        // check shift that opening
        $currentRegister = $this->cashRegisterService->getCurrentOpenRegister();

        return view('admin.sales.shift', compact('history', 'currentRegister'));
    }

    // open shift for cashier on POS
    public function open(CashRegisterStoreRequest $request)
    {
        try {

            $this->cashRegisterService->openRegister($request->validated());
            return redirect()->back()->with('success', 'Opening shift successfully.');

        } catch (\Exception $e) {

            return redirect()->back()->with('error', $e->getMessage());

        }
    }

    // function for close shift
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

    // view detail about cashier register
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
