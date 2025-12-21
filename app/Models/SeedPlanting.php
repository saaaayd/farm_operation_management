<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeedPlanting extends Model
{
    protected $fillable = [
        'user_id',
        'rice_variety_id',
        'planting_date',
        'expected_transplant_date',
        'quantity',
        'unit',
        'status',
        'notes',
        'batch_id',
    ];

    protected $casts = [
        'planting_date' => 'date',
        'expected_transplant_date' => 'date',
        'quantity' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    const STATUS_SOWN = 'sown';
    const STATUS_GERMINATING = 'germinating';
    const STATUS_READY = 'ready';
    const STATUS_TRANSPLANTED = 'transplanted';
    const STATUS_FAILED = 'failed';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function riceVariety(): BelongsTo
    {
        return $this->belongsTo(RiceVariety::class);
    }

    public function plantings(): HasMany
    {
        return $this->hasMany(Planting::class);
    }

    public function scopeReady($query)
    {
        return $query->where('status', self::STATUS_READY);
    }
}
