<?php

namespace App\Models;
use App\Models\Category;
use App\Models\ProductUom;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';
    protected $primaryKey = 'code';
    public $incrementing  = false;
    protected $keyType = 'string';

    protected $fillable = [
        'code',
        'name',
        'category_code',
        // 'cost_price',
        // 'price',
        'stock',
        'min_stock',
        'barcode',
        'description',
        'image',
        'expiry_date',
        'status'
    ];
    public function category(){
        return $this->belongsTo(Category::class, 'category_code','code');
    }
    public function uoms(){
        return $this->hasMany(ProductUom::class, 'product_code', 'code');
    }
}
