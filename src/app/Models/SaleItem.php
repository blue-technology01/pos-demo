<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SaleItem extends Model
{
    public $timestamps=false;
    protected $fillable = [
        'sale_id',
        'product_code',
        'uom_code',
        'product_name',
        'quantity',
        'cost_price',
        'unit_price',
        'discount_percentage',
        'discount_amount',
        'amount',
    ];

    protected $casts = [
        'quantity'            => 'decimal:2',
        'cost_price'          => 'decimal:2',
        'unit_price'          => 'decimal:2',
        'discount_percentage' => 'decimal:2',
        'discount_amount'     => 'decimal:2',
        'amount'              => 'decimal:2',
    ];

    public function sale(): BelongsTo
    {
        return $this->belongsTo(Sale::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(
            Product::class,
            'product_code',
            'code'
        );
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(
            Uom::class,
            'uom_code',
            'code'
        );
    }
}
