<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductUom extends Model
{
    protected $table = 'product_uoms';

    protected $fillable = [
        'product_code',
        'uom_code',
        'quantity_per_unit',
        'cost_price',
        'selling_price',
        'barcode',
        'is_default',
    ];

    // cast values correctly
    protected $casts = [
        'quantity_per_unit' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'selling_price' => 'decimal:2',
        'is_default' => 'boolean',
    ];

    /*
    |-----------------------------------------
    | RELATION: Product
    |-----------------------------------------
    */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_code', 'code');
    }

    /*
    |-----------------------------------------
    | RELATION: UOM
    |-----------------------------------------
    */
    public function uom()
    {
        return $this->belongsTo(Uom::class, 'uom_code', 'code');
    }

    /*
    |-----------------------------------------
    | SCOPE: Default UOM
    |-----------------------------------------
    */
    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    /*
    |-----------------------------------------
    | HELPER: Profit per unit
    |-----------------------------------------
    */
    public function getProfitAttribute()
    {
        return $this->selling_price - $this->cost_price;
    }
}
