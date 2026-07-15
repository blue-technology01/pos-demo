<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockAjustment extends Model
{
    protected $table = 'stock_adjustments';

    protected $fillable = [
        'product_code',
        'warehouse_id',
        'adjustment_date',
        'new_quantity',
        'reason_code',
        'remark',
        'created_by',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected $casts = [
        'adjustment_date' => 'date',
        'approved_at'     => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_code', 'code');
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
