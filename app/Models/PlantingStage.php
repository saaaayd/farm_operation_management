<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PlantingStage extends Model
{
    use HasFactory;

    protected $fillable = [
        'planting_id',
        'rice_growth_stage_id',
        'started_at',
        'completed_at',
        'status',
        'notes',
        'stage_data',
        'completion_percentage',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'stage_data' => 'array',
        'completion_percentage' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the planting that owns this stage
     */
    public function planting()
    {
        return $this->belongsTo(Planting::class);
    }

    /**
     * Get the rice growth stage
     */
    public function riceGrowthStage()
    {
        return $this->belongsTo(RiceGrowthStage::class);
    }

    /**
     * Scope to get stages by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get pending stages
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get in progress stages
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope to get completed stages
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope to get delayed stages
     */
    public function scopeDelayed($query)
    {
        return $query->where('status', 'delayed');
    }

    /**
     * Mark stage as started
     */
    public function markAsStarted()
    {
        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);
    }

    /**
     * Mark stage as completed
     */
    public function markAsCompleted($notes = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
            'completion_percentage' => 100,
            'notes' => $notes ?: $this->notes,
        ]);
    }

    /**
     * Mark stage as delayed
     */
    public function markAsDelayed($notes = null)
    {
        $this->update([
            'status' => 'delayed',
            'notes' => $notes ?: $this->notes,
        ]);
    }

    /**
     * Calculate duration in days
     */
    public function getDurationInDays()
    {
        if (!$this->started_at || !$this->completed_at) {
            return null;
        }

        return $this->started_at->diffInDays($this->completed_at);
    }

    /**
     * Check if stage is overdue based on typical duration
     */
    public function isOverdue()
    {
        if (!$this->started_at || $this->status === 'completed') {
            return false;
        }

        $expectedEndDate = $this->started_at->addDays($this->riceGrowthStage->typical_duration_days);
        return now()->gt($expectedEndDate);
    }

    /**
     * Get days remaining or overdue
     */
    public function getDaysRemaining()
    {
        if (!$this->started_at || $this->status === 'completed') {
            return null;
        }

        $expectedEndDate = $this->started_at->addDays($this->riceGrowthStage->typical_duration_days);
        return now()->diffInDays($expectedEndDate, false);
    }
}