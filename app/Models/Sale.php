<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{

    protected $fillable = [
        'harvest_id',
        'buyer_id',
        'user_id',
        'rice_order_id', // Link to marketplace order
        'quantity',
        'unit_price',
        'total_amount',
        'sale_date',
        'payment_method',
        'payment_status',
        'delivery_date',
        'delivery_address',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'sale_date' => 'datetime',
        'delivery_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the harvest that was sold
     */
    public function harvest(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Harvest::class);
    }

    /**
     * Get the buyer (for off-platform sales)
     */
    public function buyer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Buyer::class, 'buyer_id');
    }

    /**
     * Get the rice order (for marketplace sales)
     */
    public function riceOrder(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(RiceOrder::class);
    }

    /**
     * The farmer/farm owner who created the sale
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the unit price
     */
    public function getUnitPriceAttribute($value)
    {
        if (!is_null($value)) {
            return $value;
        }

        return $this->quantity > 0 ? $this->total_amount / $this->quantity : 0;
    }

    /**
     * Get the farmer (through harvest -> planting -> field -> user)
     */
    public function getFarmerAttribute()
    {
        return $this->harvest?->planting?->field?->user;
    }

    /**
     * Get the crop type (through harvest -> planting)
     */
    public function getCropTypeAttribute()
    {
        return $this->harvest?->planting?->crop_type;
    }
}