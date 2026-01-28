<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'middle_initial',
        'last_name',
        'name', // Keep for backward compatibility
        'email',
        'password',
        'role',
        'phone',
        'phone_verified_at',
        'verification_code',
        'address',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'address' => 'array',
    ];

    /**
     * Role constants
     */
    const ROLE_FARMER = 'farmer';
    const ROLE_USER = 'buyer'; // Marketplace buyer only

    /**
     * Check if user is farmer
     */
    public function isFarmer(): bool
    {
        return $this->role === self::ROLE_FARMER;
    }

    /**
     * Check if user is marketplace user (buyer only)
     */
    public function isUser(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    /**
     * Check if user can access marketplace as buyer
     */
    public function canBuy(): bool
    {
        return $this->role === self::ROLE_USER;
    }

    public function farm(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Farm::class);
    }
    /**
     * Check if user can sell in marketplace
     */
    public function canSell(): bool
    {
        return $this->role === self::ROLE_FARMER;
    }

    /**
     * Get the fields for this user
     */
    public function fields(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Field::class);
    }

    /**
     * Get the orders for this user (if buyer)
     */
    public function orders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    /**
     * Get the sales for this user (if farmer)
     */
    public function sales(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Sale::class, 'user_id');
    }

    /**
     * Get the rice products for this farmer
     */
    public function riceProducts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RiceProduct::class, 'farmer_id');
    }

    /**
     * Get the rice orders for this buyer
     */
    public function buyerRiceOrders(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(RiceOrder::class, 'buyer_id');
    }

    /**
     * Get activity logs for this user
     */
    public function activityLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }
}
