<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryItem extends Model
{

    protected $fillable = [
        'name',
        'description',
        'category',
        'unit',
        'current_stock',
        'minimum_stock',
        'unit_price',
        'supplier',
        'location',
        'expiry_date',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'current_stock' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
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
     * Get the transactions for this inventory item
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(InventoryTransaction::class)->orderBy('transaction_date', 'desc');
    }

    /**
     * Check if item is low stock
     */
    public function isLowStock(): bool
    {
        return ($this->current_stock ?? 0) <= ($this->minimum_stock ?? 0);
    }

    /**
     * Check if item is out of stock
     */
    public function isOutOfStock(): bool
    {
        return ($this->current_stock ?? 0) <= 0;
    }

    /**
     * Add stock
     */
    public function addStock($quantity)
    {
        $this->increment('current_stock', $quantity);
    }

    /**
     * Remove stock
     */
    public function removeStock($quantity)
    {
        if (($this->current_stock ?? 0) >= $quantity) {
            $this->decrement('current_stock', $quantity);
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