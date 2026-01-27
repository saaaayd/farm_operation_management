<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LaborWage extends Model
{

    protected $fillable = [
        'laborer_id',
        'task_id',
        'hours_worked',
        'wage_amount',
        'date',
    ];

    protected $casts = [
        'hours_worked' => 'decimal:2',
        'wage_amount' => 'decimal:2',
        'date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the laborer that owns the wage record
     */
    public function laborer(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Laborer::class);
    }

    /**
     * Get the task associated with this wage
     */
    public function task(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    /**
     * Calculate wage amount based on hours and rate
     */
    public function calculateWageAmount()
    {
        if ($this->laborer && $this->hours_worked) {
            $this->wage_amount = $this->hours_worked * ($this->laborer->rate ?? 0);
            $this->save();
        }
    }

    /**
     * Boot method to auto-calculate wage amount
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($laborWage) {
            if (!$laborWage->wage_amount && $laborWage->laborer) {
                $laborWage->wage_amount = $laborWage->hours_worked * ($laborWage->laborer->rate ?? 0);
            }
        });
    }
}