<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductUom extends Model
{
    protected $table = 'product_uoms';

    // Normal auto-increment ID (as per your migration)
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'product_code',
        'uom_code',
        'quantity_per_unit',
        'cost_price',
        'selling_price',
        'barcode',
        'is_default',
        'uom_role',
    ];

    protected $casts = [
        'quantity_per_unit' => 'decimal:2',
        'cost_price'        => 'decimal:2',
        'selling_price'     => 'decimal:2',
        'is_default'        => 'boolean',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_code', 'code');
    }

    public function uom(): BelongsTo
    {
        return $this->belongsTo(Uom::class, 'uom_code', 'code');
    }
}
