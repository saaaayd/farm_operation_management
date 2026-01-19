<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WeatherLog extends Model
{

    protected $fillable = [
        'field_id',
        'temperature',
        'humidity',
        'wind_speed',
        'rainfall',
        'conditions',
        'recorded_at',
    ];

    protected $casts = [
        'temperature' => 'decimal:1',
        'humidity' => 'decimal:1',
        'wind_speed' => 'decimal:1',
        'rainfall' => 'decimal:2',
        'recorded_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Weather condition constants
     */
    const CONDITION_CLEAR = 'clear';
    const CONDITION_CLOUDY = 'cloudy';
    const CONDITION_RAINY = 'rainy';
    const CONDITION_STORMY = 'stormy';
    const CONDITION_SNOWY = 'snowy';
    const CONDITION_FOGGY = 'foggy';

    /**
     * Get the field that owns the weather log
     */
    public function field()
    {
        return $this->belongsTo(Field::class);
    }

    /**
     * Get temperature in Fahrenheit
     */
    public function getTemperatureFAttribute()
    {
        return ($this->temperature * 9 / 5) + 32;
    }

    /**
     * Check if conditions are favorable for farming
     */
    public function isFavorableForFarming(): bool
    {
        return $this->temperature >= 10 &&
            $this->temperature <= 35 &&
            $this->humidity >= 30 &&
            $this->humidity <= 80 &&
            $this->wind_speed < 20;
    }

    /**
     * Get weather icon based on conditions
     */
    public function getWeatherIconAttribute()
    {
        return match ($this->conditions) {
            self::CONDITION_CLEAR => 'sun',
            self::CONDITION_CLOUDY => 'cloud',
            self::CONDITION_RAINY => 'cloud-rain',
            self::CONDITION_STORMY => 'cloud-lightning',
            self::CONDITION_SNOWY => 'cloud-snow',
            self::CONDITION_FOGGY => 'eye-slash',
            default => 'question'
        };
    }

    /**
     * Scope for recent weather logs
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('recorded_at', '>=', now()->subDays($days));
    }

    /**
     * Scope for today's weather
     */
    public function scopeToday($query)
    {
        return $query->whereDate('recorded_at', today());
    }
}