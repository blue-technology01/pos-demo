<?php

namespace App\Services\Cash;

use Carbon\Carbon;
use App\Models\CashRegister;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;

class CashRegisterService
{
    /**
     * cash register that opening active
     * user for check UI POS
     */
    public function getCurrentOpenRegister()
    {
        return CashRegister::where('status', 'open')
            ->where('user_id', Auth::id())
            ->latest()
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

            ->when($request->search, function ($query) use ($request) {
                $search = trim($request->search);

                $query->where(function ($q) use ($search) {

                    if (is_numeric($search)) {
                        $q->where('id', (int) $search);
                    } else {
                        $q->where(function ($sub) use ($search) {
                            $sub->whereHas('user', function ($u) use ($search) {
                                $u->where('name', 'like', "%{$search}%");
                            })
                            ->orWhere('id', 'like', "%{$search}%");
                        });
                    }
                });
            })
            ->when($request->start_date && $request->end_date, function ($query) use ($request) {
                $start = Carbon::parse($request->start_date)->startOfDay();
                $end   = Carbon::parse($request->end_date)->endOfDay();

                $query->whereBetween('opened_at', [$start, $end]);
            })

            ->when($request->start_date && !$request->end_date, function ($query) use ($request) {
                $query->whereDate('opened_at', Carbon::parse($request->start_date));
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
        return DB::transaction(function () use ($data) {

            $existing = CashRegister::where('status', 'open')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                throw new \Exception('A cash register is already open.');
            }

            return CashRegister::create([
                'user_id'            => Auth::id(),
                'opening_balance'    => $data['opening_balance'] ?? 0,
                'closing_balance'    => 0,
                'expected_balance'   => 0,
                'difference_amount'  => 0,
                'total_sales'        => 0,
                'total_transactions' => 0,
                'status'             => 'open',
                'opened_at'          => now(),
            ]);
        });
    }

    /**
     * function for close shift after sale 8h
     */
    public function closeRegister(int $id, array $data): CashRegister
    {
        return DB::transaction(function () use ($id, $data) {

            $register = CashRegister::lockForUpdate()->findOrFail($id);

            if ($register->status === 'closed') {
                throw new \Exception('This shift is already closed.');
            }

            if ($register->user_id !== Auth::id()) {
                throw new \Exception('Unauthorized action.');
            }

            $closingBalance = $data['closing_balance'] ?? 0;

            $expectedBalance = $register->opening_balance + $register->total_sales;

            $differenceAmount = $closingBalance - $expectedBalance;

            $register->update([
                'closing_balance'   => $closingBalance,
                'expected_balance'  => $expectedBalance,
                'difference_amount' => $differenceAmount,
                'note'              => $data['note'] ?? null,
                'status'            => 'closed',
                'closed_at'         => now(),
            ]);

            return $register;
        });
    }
}