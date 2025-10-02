<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Planting extends Model
{

    protected $fillable = [
        'field_id',
        'rice_variety_id',
        'crop_type',
        'planting_date',
        'expected_harvest_date',
        'actual_harvest_date',
        'status',
        'planting_method',
        'seed_rate',
        'area_planted',
        'notes',
        'weather_conditions',
    ];

    protected $casts = [
        'planting_date' => 'datetime',
        'expected_harvest_date' => 'datetime',
        'actual_harvest_date' => 'datetime',
        'seed_rate' => 'decimal:2',
        'area_planted' => 'decimal:2',
        'weather_conditions' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PLANTED = 'planted';
    const STATUS_GROWING = 'growing';
    const STATUS_READY = 'ready';
    const STATUS_HARVESTED = 'harvested';
    const STATUS_FAILED = 'failed';

    /**
     * Get the field that owns the planting
     */
    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    /**
     * Get the rice variety for this planting
     */
    public function riceVariety()
    {
        return $this->belongsTo(RiceVariety::class);
    }

    /**
     * Get the planting stages for this planting
     */
    public function plantingStages()
    {
        return $this->hasMany(PlantingStage::class);
    }

    /**
     * Get the harvests for this planting
     */
    public function harvests()
    {
        return $this->hasMany(Harvest::class);
    }

    /**
     * Get the tasks for this planting
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }

    /**
     * Get the expenses for this planting
     */
    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    /**
     * Check if planting is overdue
     */
    public function isOverdue(): bool
    {
        return $this->expected_harvest_date < Carbon::now() && 
               $this->status !== self::STATUS_HARVESTED;
    }

    /**
     * Get days until harvest
     */
    public function daysUntilHarvest(): int
    {
        return Carbon::now()->diffInDays($this->expected_harvest_date, false);
    }

    /**
     * Get total yield from harvests
     */
    public function getTotalYieldAttribute()
    {
        return $this->harvests()->sum('yield');
    }

    /**
     * Initialize planting stages based on rice growth stages
     */
    public function initializePlantingStages()
    {
        $growthStages = RiceGrowthStage::getAllStagesOrdered();
        
        foreach ($growthStages as $stage) {
            $this->plantingStages()->create([
                'rice_growth_stage_id' => $stage->id,
                'status' => 'pending',
            ]);
        }
    }

    /**
     * Get current growth stage
     */
    public function getCurrentStage()
    {
        return $this->plantingStages()
            ->with('riceGrowthStage')
            ->where('status', 'in_progress')
            ->first();
    }

    /**
     * Get next pending stage
     */
    public function getNextPendingStage()
    {
        return $this->plantingStages()
            ->with('riceGrowthStage')
            ->where('status', 'pending')
            ->join('rice_growth_stages', 'planting_stages.rice_growth_stage_id', '=', 'rice_growth_stages.id')
            ->orderBy('rice_growth_stages.order_sequence')
            ->first();
    }

    /**
     * Get progress percentage
     */
    public function getProgressPercentage()
    {
        $totalStages = $this->plantingStages()->count();
        $completedStages = $this->plantingStages()->completed()->count();
        
        if ($totalStages === 0) {
            return 0;
        }
        
        return round(($completedStages / $totalStages) * 100, 2);
    }

    /**
     * Check if planting is rice
     */
    public function isRice()
    {
        return $this->crop_type === 'rice' || $this->rice_variety_id !== null;
    }

    /**
     * Get estimated yield based on variety and area
     */
    public function getEstimatedYield()
    {
        if (!$this->riceVariety || !$this->area_planted) {
            return null;
        }

        // Convert area to hectares if needed and calculate estimated yield
        $yieldPerHectare = $this->riceVariety->average_yield_per_hectare;
        return $this->area_planted * $yieldPerHectare;
    }

    /**
     * Get days since planting
     */
    public function getDaysSincePlanting()
    {
        return $this->planting_date->diffInDays(now());
    }

    /**
     * Check if planting is in critical stage
     */
    public function isInCriticalStage()
    {
        $currentStage = $this->getCurrentStage();
        if (!$currentStage) {
            return false;
        }

        // Define critical stages (flowering, grain filling)
        $criticalStages = ['flowering', 'grain_filling', 'ripening'];
        return in_array($currentStage->riceGrowthStage->stage_code, $criticalStages);
    }
}