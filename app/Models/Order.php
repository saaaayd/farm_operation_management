<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{

    protected $fillable = [
        'buyer_id',
        'status',
        'total_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';

    /**
     * Get the buyer that owns the order
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the order items for this order
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Calculate total amount from order items
     */
    public function calculateTotalAmount()
    {
        $this->total_amount = $this->orderItems->sum(function ($item) {
            return $item->quantity * $item->unit_price;
        });
        $this->save();
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled(): bool
    {
        return in_array($this->status, [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED
        ]);
    }

    /**
     * Mark order as confirmed
     */
    public function markConfirmed()
    {
        $this->update(['status' => self::STATUS_CONFIRMED]);
    }

    /**
     * Mark order as shipped
     */
    public function markShipped()
    {
        $this->update(['status' => self::STATUS_SHIPPED]);
    }

    /**
     * Mark order as delivered
     */
    public function markDelivered()
    {
        $this->update(['status' => self::STATUS_DELIVERED]);
    }

    /**
     * Get order status badge color
     */
    public function getStatusColorAttribute()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_CONFIRMED => 'blue',
            self::STATUS_PROCESSING => 'purple',
            self::STATUS_SHIPPED => 'indigo',
            self::STATUS_DELIVERED => 'green',
            self::STATUS_CANCELLED => 'red',
            default => 'gray'
        };
    }
}