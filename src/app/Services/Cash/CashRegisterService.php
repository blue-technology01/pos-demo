<?php

namespace App\Services\Cash;

use App\Models\CashRegister;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class CashRegisterService
{
    /**
     * cash register that opening active
     * user for check UI POS
     */
    public function getCurrentOpenRegister()
    {
        return CashRegister::where('status', 'open')
            ->orderByDesc('id')
            ->first();
    }

    /**
     * get history list with pagination
     * show UI for admin and check day by day
     */
    public function getAllHistory(Request $request): LengthAwarePaginator
    {
        return CashRegister::query()
            ->with('user:id,name') // get name cashier that sale
            ->when($request->status, function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->date, function ($query) use ($request) {
                $query->whereDate('opened_at', $request->date);
            })
            ->orderByDesc('id')
            ->paginate($request->per_page ?? 15);
    }

    /**
     * open shift on morning
     */
    public function openRegister(array $data): CashRegister
    {
        // check existing opening shift
        $existing = $this->getCurrentOpenRegister();
        if ($existing) {
            throw new \Exception('Have older user open shift ready!');
        }

        // ប្រើប្រាស់ CashRegister::create ត្រឹមត្រូវតាមស្ដង់ដារ Laravel
        return CashRegister::create([
            'user_id'            => Auth::id(),  // cashier user id that login
            'opening_balance'    => $data['opening_balance'] ?? 0.00,
            'closing_balance'    => 0.00,
            'expected_balance'   => 0.00,
            'difference_amount'  => 0.00,
            'total_sales'        => 0.00,
            'total_transactions' => 0,
            'status'             => 'open',
            'opened_at'          => Carbon::now(),
        ]);
    }

    /**
     * function for close shift after sale 8h
     */
    public function closeRegister(int $id, array $data): CashRegister
    {
        $register = CashRegister::findOrFail($id);
        if ($register->status === 'closed') {
            throw new \Exception('This shift closed ready!.');
        }

        // money that cashier count at afternoon
        $closingBalance = $data['closing_balance'] ?? 0.00;
        // calculate expected balance
        $expectedBalance = $register->opening_balance + $register->total_sales;
        // calculate difference amount
        $differenceAmount = $closingBalance - $expectedBalance;

        $register->update([
            'closing_balance'   => $closingBalance,
            'expected_balance'  => $expectedBalance,
            'difference_amount' => $differenceAmount,
            'note'              => $data['note'] ?? null,
            'status'            => 'closed',
            'closed_at'         => Carbon::now(),
        ]);

        return $register;
    }
}
