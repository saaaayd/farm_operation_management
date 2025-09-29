<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{

    protected $fillable = [
        'order_id',
        'inventory_item_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the order that owns the order item
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the inventory item
     */
    public function inventoryItem()
    {
        return $this->belongsTo(InventoryItem::class);
    }

    /**
     * Get the total price for this item
     */
    public function getTotalPriceAttribute()
    {
        return $this->quantity * $this->unit_price;
    }

    /**
     * Boot method to update order total when order item changes
     */
    protected static function boot()
    {
        parent::boot();

        static::created(function ($orderItem) {
            $orderItem->order->calculateTotalAmount();
        });

        static::updated(function ($orderItem) {
            $orderItem->order->calculateTotalAmount();
        });

        static::deleted(function ($orderItem) {
            $orderItem->order->calculateTotalAmount();
        });
    }
}