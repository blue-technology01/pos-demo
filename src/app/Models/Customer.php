<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    const NAME_MAX_LENGTH = 100;
    const ADDRESS_MAX_LENGTH = 255;
    const STATUS_ACTIVE = 'active';
    const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'name',
        'phone',
        'email',
        'address',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function setEmailAttribute(?string $value): void
    {
        $this->attributes['email'] = $value ? strtolower(trim($value)) : null;
    }
}
