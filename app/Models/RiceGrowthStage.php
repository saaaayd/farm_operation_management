<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiceGrowthStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'stage_code',
        'description',
        'typical_duration_days',
        'order_sequence',
        'key_activities',
        'weather_requirements',
        'nutrient_requirements',
        'water_requirements',
        'common_problems',
        'is_active',
    ];

    protected $casts = [
        'key_activities' => 'array',
        'weather_requirements' => 'array',
        'nutrient_requirements' => 'array',
        'water_requirements' => 'array',
        'common_problems' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the planting stages for this growth stage
     */
    public function plantingStages()
    {
        return $this->hasMany(PlantingStage::class);
    }

    /**
     * Scope to get active stages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to order by sequence
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order_sequence');
    }

    /**
     * Get all stages in order
     */
    public static function getAllStagesOrdered()
    {
        return static::active()->ordered()->get();
    }

    /**
     * Get the next stage in sequence
     */
    public function getNextStage()
    {
        return static::active()
            ->where('order_sequence', '>', $this->order_sequence)
            ->ordered()
            ->first();
    }

    /**
     * Get the previous stage in sequence
     */
    public function getPreviousStage()
    {
        return static::active()
            ->where('order_sequence', '<', $this->order_sequence)
            ->orderBy('order_sequence', 'desc')
            ->first();
    }

    /**
     * Check if this is the first stage
     */
    public function isFirstStage()
    {
        return $this->order_sequence === 1;
    }

    /**
     * Check if this is the last stage
     */
    public function isLastStage()
    {
        $maxSequence = static::active()->max('order_sequence');
        return $this->order_sequence === $maxSequence;
    }
}