<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Harvest extends Model
{

    protected $fillable = [
        'planting_id',
        'yield',
        'harvest_date',
        'quality',
    ];

    protected $casts = [
        'yield' => 'decimal:2',
        'harvest_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Quality constants
     */
    const QUALITY_EXCELLENT = 'excellent';
    const QUALITY_GOOD = 'good';
    const QUALITY_AVERAGE = 'average';
    const QUALITY_POOR = 'poor';

    /**
     * Get the planting that owns the harvest
     */
    public function planting()
    {
        return $this->belongsTo(Planting::class);
    }

    /**
     * Get the sales for this harvest
     */
    public function sales()
    {
        return $this->hasMany(Sale::class);
    }

    /**
     * Get available quantity (not yet sold)
     */
    public function getAvailableQuantityAttribute()
    {
        $soldQuantity = $this->sales()->sum('quantity');
        return $this->yield - $soldQuantity;
    }

    /**
     * Check if harvest is fully sold
     */
    public function isFullySold(): bool
    {
        return $this->available_quantity <= 0;
    }
}