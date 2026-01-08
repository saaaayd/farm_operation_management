<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PestIncident extends Model
{
    protected $fillable = [
        'planting_id',
        'user_id',
        'pest_type',
        'pest_name',
        'severity',
        'affected_area',
        'detected_date',
        'symptoms',
        'treatment_applied',
        'treatment_date',
        'treatment_cost',
        'status',
        'notes',
        'images',
    ];

    protected $casts = [
        'detected_date' => 'date',
        'treatment_date' => 'date',
        'affected_area' => 'decimal:2',
        'treatment_cost' => 'decimal:2',
        'images' => 'array',
    ];

    // Pest types
    const TYPE_INSECT = 'insect';
    const TYPE_DISEASE = 'disease';
    const TYPE_WEED = 'weed';
    const TYPE_RODENT = 'rodent';
    const TYPE_OTHER = 'other';

    const TYPES = [
        self::TYPE_INSECT,
        self::TYPE_DISEASE,
        self::TYPE_WEED,
        self::TYPE_RODENT,
        self::TYPE_OTHER,
    ];

    // Severity levels
    const SEVERITY_LOW = 'low';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_CRITICAL = 'critical';

    const SEVERITIES = [
        self::SEVERITY_LOW,
        self::SEVERITY_MEDIUM,
        self::SEVERITY_HIGH,
        self::SEVERITY_CRITICAL,
    ];

    // Status
    const STATUS_ACTIVE = 'active';
    const STATUS_TREATED = 'treated';
    const STATUS_RESOLVED = 'resolved';

    public function planting(): BelongsTo
    {
        return $this->belongsTo(Planting::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('detected_date', '>=', now()->subDays($days));
    }
}
