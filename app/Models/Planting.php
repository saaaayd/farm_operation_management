<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Planting extends Model
{

    protected $fillable = [
        'field_id',
        'crop_type',
        'planting_date',
        'expected_harvest_date',
        'status',
    ];

    protected $casts = [
        'planting_date' => 'datetime',
        'expected_harvest_date' => 'datetime',
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
}