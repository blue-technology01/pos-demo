<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashRegister extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'opening_balance',
        'closing_balance',
        'expected_balance',
        'difference_amount',
        'total_sales',
        'total_transactions',
        'note',
        'status',
        'opened_at',
        'closed_at'
    ];
    protected $casts = [
        'opening_balance'    => 'decimal:2',
        'closing_balance'    => 'decimal:2',
        'expected_balance'   => 'decimal:2',
        'difference_amount'  => 'decimal:2',
        'total_sales'        => 'decimal:2',
        'total_transactions' => 'integer',
        'opened_at'          => 'datetime',
        'closed_at'          => 'datetime',
    ];
    // relationship with users that login system
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
