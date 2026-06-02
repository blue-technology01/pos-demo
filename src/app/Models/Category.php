<?php

namespace App\Models;
use App\Models\Product;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    
    protected $table = 'categories';
    protected $primaryKey = 'code';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable =[
        'code',
        'name',
        'description',
        'image',
        'status'
    ];

    // relationships with products one to many relationship
    public function products()
    {
        return $this->hasMany(Product::class, 'category_code', 'code');
    }
}
