<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;
use App\Models\Uom;

class ProductUom extends Model
{
    protected $table = 'product_uoms';
    protected $fillable = [
        'product_code',
        'uom_code',
        'quantity_per_unit',
        'price',
        'cost_price',
        'is_default',
    ];

    // relationship with product
    public function product(){
        return $this->belongsTo(Product::class,'product_code','code');
    }
    // relationship with uom
    public function uom(){
        return $this->belongsTo(Uom::class,'uom_code','code');
    }
}
