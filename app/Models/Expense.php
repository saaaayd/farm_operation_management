<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{

    protected $fillable = [
        'description',
        'amount',
        'category',
        'date',
        'planting_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Category constants
     */
    const CATEGORY_SEEDS = 'seeds';
    const CATEGORY_FERTILIZER = 'fertilizer';
    const CATEGORY_PESTICIDE = 'pesticide';
    const CATEGORY_LABOR = 'labor';
    const CATEGORY_EQUIPMENT = 'equipment';
    const CATEGORY_UTILITIES = 'utilities';
    const CATEGORY_MAINTENANCE = 'maintenance';
    const CATEGORY_OTHER = 'other';

    /**
     * Get the planting associated with this expense
     */
    public function planting()
    {
        return $this->belongsTo(Planting::class);
    }

    /**
     * Get the farmer (through planting -> field -> user)
     */
    public function getFarmerAttribute()
    {
        return $this->planting?->field?->user;
    }

    /**
     * Get the crop type (through planting)
     */
    public function getCropTypeAttribute()
    {
        return $this->planting?->crop_type;
    }

    /**
     * Scope for monthly expenses
     */
    public function scopeMonthly($query, $month = null, $year = null)
    {
        $month = $month ?? now()->month;
        $year = $year ?? now()->year;

        return $query->whereMonth('date', $month)
                    ->whereYear('date', $year);
    }

    /**
     * Scope for expenses by category
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }
}