<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Jenssegers\Mongodb\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $connection = 'mongodb';
    protected $collection = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'phone',
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
    const ROLE_ADMIN = 'admin';
    const ROLE_FARMER = 'farmer';
    const ROLE_BUYER = 'buyer';

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    /**
     * Check if user is farmer
     */
    public function isFarmer(): bool
    {
        return $this->role === self::ROLE_FARMER;
    }

    /**
     * Check if user is buyer
     */
    public function isBuyer(): bool
    {
        return $this->role === self::ROLE_BUYER;
    }

    /**
     * Get the fields for this user
     */
    public function fields()
    {
        return $this->hasMany(Field::class);
    }

    /**
     * Get the orders for this user (if buyer)
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'buyer_id');
    }

    /**
     * Get the sales for this user (if farmer)
     */
    public function sales()
    {
        return $this->hasMany(Sale::class, 'buyer_id');
    }
}