<?php

namespace App\Models;

use App\Services\Report\RevenueReportService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Sale extends Model
{
    use HasFactory;

    protected $table = 'sales';

    protected $fillable = [
        'register_id',
        'user_id',
        'customer_id',
        'invoice_no',
        'sale_date',
        'sub_total',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'paid_amount',
        'change_amount',
        'payment_method',
        'status',
        'note',
        'created_by',
        'voided_by',
    ];

    protected $casts = [
        'sale_date'       => 'date',
        'sub_total'       => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount'      => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'paid_amount'     => 'decimal:2',
        'change_amount'   => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function register()
    {
        return $this->belongsTo(
            CashRegister::class,
            'register_id'
        );
    }
    public function creator()
    {
        return $this->belongsTo(
            User::class,
            'created_by'
        );
    }
    public function voider()
    {
        return $this->belongsTo(
            User::class,
            'voided_by'
        );
    }
    public function items()
    {
        return $this->hasMany(
            SaleItem::class,
            'sale_id'
        );
    }

    public function getBalanceAttribute()
    {
        return $this->total_amount - $this->paid_amount;
    }
    public function getIsPaidAttribute()
    {
        return $this->paid_amount >= $this->total_amount;
    }

    protected static function booted(): void
    {
        static::saved(fn ()   => RevenueReportService::flushCache());
        static::deleted(fn () => RevenueReportService::flushCache());
    }
}
