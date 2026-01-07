<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ScheduledReport extends Model
{
    protected $fillable = [
        'user_id',
        'farm_id',
        'report_type',
        'frequency',
        'email',
        'parameters',
        'is_active',
        'last_sent_at'
    ];

    protected $casts = [
        'parameters' => 'array',
        'is_active' => 'boolean',
        'last_sent_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }
}
