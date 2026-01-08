<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CartItem extends Model
{
    protected $fillable = [
        'buyer_id',
        'rice_product_id',
        'quantity',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    public function riceProduct(): BelongsTo
    {
        return $this->belongsTo(RiceProduct::class);
    }

    /**
     * Get the subtotal for this cart item
     */
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * ($this->riceProduct->price_per_unit ?? 0);
    }
}
