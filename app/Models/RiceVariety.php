<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiceVariety extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'variety_code',
        'description',
        'maturity_days',
        'average_yield_per_hectare',
        'season',
        'grain_type',
        'resistance_level',
        'characteristics',
        'is_active',
    ];

    protected $casts = [
        'characteristics' => 'array',
        'average_yield_per_hectare' => 'decimal:2',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the plantings for this rice variety
     */
    public function plantings()
    {
        return $this->hasMany(Planting::class);
    }

    /**
     * Scope to get active varieties
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to filter by season
     */
    public function scopeBySeason($query, $season)
    {
        return $query->where(function ($q) use ($season) {
            $q->where('season', $season)->orWhere('season', 'both');
        });
    }

    /**
     * Get varieties suitable for current season
     */
    public static function getCurrentSeasonVarieties()
    {
        $currentMonth = now()->month;
        $season = ($currentMonth >= 5 && $currentMonth <= 10) ? 'wet' : 'dry';
        
        return static::active()->bySeason($season)->get();
    }

    /**
     * Get estimated harvest date based on planting date
     */
    public function getEstimatedHarvestDate($plantingDate)
    {
        return $plantingDate->addDays($this->maturity_days);
    }
}