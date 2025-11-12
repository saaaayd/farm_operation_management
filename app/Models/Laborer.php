<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Laborer extends Model
{

    protected $fillable = [
        'name',
        'phone',
        'contact',
        'email',
        'address',
        'skill_level',
        'specialization',
        'hourly_rate',
        'status',
        'hire_date',
        'emergency_contact',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the tasks assigned to this laborer
     */
    public function tasks()
    {
        return $this->hasMany(Task::class, 'assigned_to');
    }

    /**
     * Get the labor wages for this laborer
     */
    public function laborWages()
    {
        return $this->hasMany(LaborWage::class);
    }

    /**
     * Alias for wages relationship expected by controllers
     */
    public function wages()
    {
        return $this->hasMany(LaborWage::class);
    }

    /**
     * Get total hours worked this month
     */
    public function getMonthlyHoursAttribute()
    {
        return $this->laborWages()
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('hours_worked');
    }

    /**
     * Get total earnings this month
     */
    public function getMonthlyEarningsAttribute()
    {
        return $this->laborWages()
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('wage_amount');
    }

    /**
     * Get active tasks count
     */
    public function getActiveTasksCountAttribute()
    {
        return $this->tasks()
            ->whereIn('status', [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS])
            ->count();
    }
}