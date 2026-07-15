<?php

namespace App\Services\Inventory;

use App\Models\Product;
use App\Models\StockAjustment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class StockAjustmentService
{
    // create new stock ajustment request pending
    public function create(array $data): StockAjustment
    {
        $this->validateReasonCode($data['reason_code'] ?? null);
        $this->ensureProductExists($data['product_code']);

        return DB::transaction(function () use ($data) {
            return StockAjustment::create([
                'product_code'     => $data['product_code'],
                'warehouse_id'     => $data['warehouse_id'],
                'adjustment_date'  => $data['adjustment_date'] ?? now()->toDateString(),
                'new_quantity'     => $data['new_quantity'],
                'reason_code'      => $data['reason_code'],
                'remark'           => $data['remark'] ?? null,
                'created_by'       => $data['created_by'] ?? Auth::id(),
                'status'           => 'pending',
            ]);
        });
    }

    // update existing adjustment
    public function update(StockAjustment $adjustment, array $data): StockAjustment
    {
        $this->ensurePending($adjustment); // check record pending or not

        if (isset($data['reason_code'])) {
            $this->validateReasonCode($data['reason_code']);
        }

        if (isset($data['product_code'])) {
            $this->ensureProductExists($data['product_code']);
        }

        // update data and don't want lose old info
        $adjustment->fill([
            'product_code'    => $data['product_code'] ?? $adjustment->product_code,
            'warehouse_id'    => $data['warehouse_id'] ?? $adjustment->warehouse_id,
            'adjustment_date' => $data['adjustment_date'] ?? $adjustment->adjustment_date,
            'new_quantity'    => $data['new_quantity'] ?? $adjustment->new_quantity,
            'reason_code'     => $data['reason_code'] ?? $adjustment->reason_code,
            'remark'          => $data['remark'] ?? $adjustment->remark,
        ])->save();
        return $adjustment;
    }

    // aprove ajustment  and apply it
    public function approve(StockAjustment $adjustment, ?int $approvedBy = null): StockAjustment
    {
        $this->ensurePending($adjustment); // check pending or not

        return DB::transaction(function () use ($adjustment, $approvedBy) {

            $adjustment = StockAjustment::whereKey($adjustment->id)
                ->lockForUpdate()
                ->first();

            $this->ensurePending($adjustment);

            $product = Product::where('code', $adjustment->product_code)
                ->lockForUpdate()
                ->first();

            if (!$product) {
                throw ValidationException::withMessages([
                    'product_code' => "Product [{$adjustment->product_code}] not found.",
                ]);
            }

            // Update old stock to new_quality
            $product->stock = $adjustment->new_quantity;
            $product->save();

            // Update adjustment status
            $adjustment->update([
                'status'       => 'approved',
                'approved_by'  => $approvedBy ?? Auth::id(),
                'approved_at'  => now(),
            ]);

            return $adjustment;
        });
    }

    // reject an adjustment
    public function reject(StockAjustment $adjustment, ?int $rejectedBy = null, ?string $remark = null): StockAjustment
    {
        $this->ensurePending($adjustment);

        $adjustment->update([
            'status'       => 'rejected',
            'approved_by'  => $rejectedBy ?? Auth::id(),
            'approved_at'  => now(),
            'remark'       => $remark ?? $adjustment->remark,
        ]);

        return $adjustment;
    }

    // remove adjustment only allowe while pending
    public function delete(StockAjustment $adjustment): bool
    {
        $this->ensurePending($adjustment);

        return (bool) $adjustment->delete();
    }

    // list adjustment
    public function list(array $filters = [])
    {
        $query = StockAjustment::query()->with(['product', 'warehouse', 'creator', 'approver']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        if (!empty($filters['product_code'])) {
            $query->where('product_code', $filters['product_code']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('adjustment_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('adjustment_date', '<=', $filters['date_to']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query->latest('adjustment_date')->paginate($perPage)->withQueryString();
    }

    private function ensurePending(StockAjustment $adjustment): void
    {
        if ($adjustment->status !== 'pending') {
            throw ValidationException::withMessages([
                'status' => "This adjustment is already {$adjustment->status} and cannot be modified.",
            ]);
        }
    }

    private function ensureProductExists(string $productCode): void
    {
        if (!Product::where('code', $productCode)->exists()) {
            throw ValidationException::withMessages([
                'product_code' => "Product [{$productCode}] does not exist.",
            ]);
        }
    }

    private function validateReasonCode(?string $reasonCode): void
    {
        $allowed = ['damage', 'break', 'other'];

        if (!$reasonCode || !in_array(strtolower($reasonCode), $allowed, true)) {
            throw ValidationException::withMessages([
                'reason_code' => 'Invalid reason code. Allowed values: ' . implode(', ', $allowed),
            ]);
        }
    }
}
