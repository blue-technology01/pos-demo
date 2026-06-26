<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;


class User extends Authenticatable
{
    use HasRoles, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'is_active',
        'last_login_at',
        'otp_code',
        'otp_expires_at',
    ];

    protected $hidden = [
        'remember_token',
        'otp_code',
        'password',
    ];

    protected $casts = [
        'is_active'      => 'boolean',
        'last_login_at'  => 'datetime',
        'otp_expires_at' => 'datetime',
    ];

    public function isOtpValid(): bool
    {
        return $this->otp_code !== null
            && $this->otp_expires_at !== null
            && $this->otp_expires_at->isFuture();
    }

    public function clearOtp(): void
    {
        $this->update([
            'otp_code'       => null,
            'otp_expires_at' => null,
        ]);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }
}
