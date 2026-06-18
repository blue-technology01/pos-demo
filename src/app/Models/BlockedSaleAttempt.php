<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlockedSaleAttempt extends Model
{
    use HasFactory;
    protected $table = "blocked_sale_attempts";

    protected $fillable = [
        'product_uom_id',
        'requested_qty',
        'available_stock',
        'reason',
        'user_id'
    ];
    // relationship
    public function productUom() {
        return $this->belongsTo(ProductUom::class);
    }
    public function user() {
        return $this->belongsTo(User::class);
    }
    public function getStatusLabelAttribute(): string {
        return match ($this->reason) {
            'out_of_stock' => 'Out of Stock',
            'insufficient_stock' => 'Insufficient Stock',
            default => 'Unknown'
        };
    }
    public function getSystemActionAttribute(): string {
        return 'blocked';
    }
}
