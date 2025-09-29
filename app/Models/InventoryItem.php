<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{

    protected $fillable = [
        'name',
        'category',
        'quantity',
        'price',
        'unit',
        'min_stock',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price' => 'decimal:2',
        'min_stock' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Category constants
     */
    const CATEGORY_SEEDS = 'seeds';
    const CATEGORY_FERTILIZER = 'fertilizer';
    const CATEGORY_PESTICIDE = 'pesticide';
    const CATEGORY_EQUIPMENT = 'equipment';
    const CATEGORY_PRODUCE = 'produce';

    /**
     * Get the order items for this inventory item
     */
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Check if item is low stock
     */
    public function isLowStock(): bool
    {
        return $this->quantity <= $this->min_stock;
    }

    /**
     * Check if item is out of stock
     */
    public function isOutOfStock(): bool
    {
        return $this->quantity <= 0;
    }

    /**
     * Add stock
     */
    public function addStock($quantity)
    {
        $this->increment('quantity', $quantity);
    }

    /**
     * Remove stock
     */
    public function removeStock($quantity)
    {
        if ($this->quantity >= $quantity) {
            $this->decrement('quantity', $quantity);
            return true;
        }
        return false;
    }

    /**
     * Get stock status
     */
    public function getStockStatusAttribute()
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        } elseif ($this->isLowStock()) {
            return 'low_stock';
        }
        return 'in_stock';
    }
}