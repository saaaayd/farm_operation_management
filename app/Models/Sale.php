<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sale extends Model
{

    protected $fillable = [
        'harvest_id',
        'buyer_id',
        'quantity',
        'total_amount',
        'sale_date',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'sale_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the harvest that was sold
     */
    public function harvest()
    {
        return $this->belongsTo(Harvest::class);
    }

    /**
     * Get the buyer
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the unit price
     */
    public function getUnitPriceAttribute()
    {
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