<?php

namespace App\Services\Cash;

use App\Models\CashRegister;
use App\Models\Sale;
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
            ->with('user:id,name')

            // search
            ->when($request->search, function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($q) use ($search) {

                    // 1. invoice / id search
                    if (is_numeric($search)) {
                        $q->where('id', (int) $search);
                    } else {

                        // 2. cashier name search (FULL MATCH FLEXIBLE)
                        $q->whereHas('user', function ($u) use ($search) {
                            $u->where('name', 'like', "%{$search}%");
                        })

                        // 3. optional fallback: also search id as string
                        ->orWhere('id', 'like', "%{$search}%");
                    }
                });
            })
            // filter data by rang of date
            ->when($request->start_date && $request->end_date, function ($query) use ($request) {
                $query->whereBetween('opened_at', [
                    $request->start_date . ' 00:00:00',
                    $request->end_date . ' 23:59:59'
                ]);
            })
            // single date
            ->when($request->start_date && !$request->end_date, function ($query) use ($request) {
                $query->whereDate('opened_at', $request->start_date);
            })
            ->orderByDesc('id')
            ->paginate($request->per_page ?? 15)
            ->withQueryString();
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

        // CashRegister::create
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
