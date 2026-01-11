<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Field extends Model
{

    protected $fillable = [
        'user_id',
        'farm_id',
        'name',
        'location',
        'field_coordinates',
        'soil_type',
        'soil_ph',
        'organic_matter_content',
        'nitrogen_level',
        'phosphorus_level',
        'potassium_level',
        'size',
        'water_access',
        'water_source',
        'irrigation_type',
        'drainage_quality',
        'elevation',
        'slope',
        'previous_crop',
        'field_preparation_status',
        'notes',
    ];

    protected $casts = [
        'location' => 'array',
        'field_coordinates' => 'array',
        'size' => 'decimal:2',
        'soil_ph' => 'decimal:2',
        'organic_matter_content' => 'decimal:2',
        'nitrogen_level' => 'decimal:2',
        'phosphorus_level' => 'decimal:2',
        'potassium_level' => 'decimal:2',
        'elevation' => 'decimal:2',
        'slope' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $appends = [
        'available_area',
    ];

    /**
     * Get the user that owns the field
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plantings for this field
     */
    public function plantings()
    {
        return $this->hasMany(Planting::class);
    }

    /**
     * Get the weather logs for this field
     */
    public function weatherLogs()
    {
        return $this->hasMany(WeatherLog::class);
    }

    /**
     * Get the latest weather log
     */
    public function latestWeather()
    {
        return $this->hasOne(WeatherLog::class)->latest('recorded_at');
    }

    /**
     * Get the farm that owns this field
     */
    public function farm()
    {
        return $this->belongsTo(Farm::class);
    }

    /**
     * Get current rice planting
     */
    public function getCurrentRicePlanting()
    {
        if ($this->relationLoaded('plantings')) {
            return $this->plantings
                ->where('crop_type', 'rice')
                ->whereIn('status', ['planted', 'growing'])
                ->sortByDesc('planting_date')
                ->first();
        }

        return $this->plantings()
            ->where('crop_type', 'rice')
            ->whereIn('status', ['planted', 'growing'])
            ->with('riceVariety')
            ->latest('planting_date')
            ->first();
    }

    /**
     * Check if field is suitable for rice farming
     */
    public function isSuitableForRice()
    {
        // Rice typically grows well in pH 5.5-7.0
        $phSuitable = $this->soil_ph === null || ($this->soil_ph >= 5.5 && $this->soil_ph <= 7.0);

        // Need good water access for rice
        $waterSuitable = $this->water_access === 'good' || $this->water_access === 'excellent';

        // Drainage should be controllable for rice
        $drainageSuitable = in_array($this->drainage_quality, ['good', 'moderate']);

        return $phSuitable && $waterSuitable && $drainageSuitable;
    }

    /**
     * Get soil fertility status
     */
    public function getSoilFertilityStatus()
    {
        $scores = [];

        // pH score (optimal 6.0-6.8 for rice)
        if ($this->soil_ph === null) {
            $scores['ph'] = 'unknown';
        } elseif ($this->soil_ph >= 6.0 && $this->soil_ph <= 6.8) {
            $scores['ph'] = 'optimal';
        } elseif ($this->soil_ph >= 5.5 && $this->soil_ph <= 7.2) {
            $scores['ph'] = 'good';
        } else {
            $scores['ph'] = 'needs_attention';
        }

        // Organic matter (>2% is good)
        if ($this->organic_matter_content === null) {
            $scores['organic_matter'] = 'unknown';
        } elseif ($this->organic_matter_content >= 2.0) {
            $scores['organic_matter'] = 'good';
        } elseif ($this->organic_matter_content >= 1.0) {
            $scores['organic_matter'] = 'moderate';
        } else {
            $scores['organic_matter'] = 'low';
        }

        // NPK levels (basic assessment)
        $scores['nitrogen'] = $this->assessNutrientLevel($this->nitrogen_level);
        $scores['phosphorus'] = $this->assessNutrientLevel($this->phosphorus_level);
        $scores['potassium'] = $this->assessNutrientLevel($this->potassium_level);

        return $scores;
    }

    /**
     * Assess nutrient level
     */
    private function assessNutrientLevel($level)
    {
        if ($level === null)
            return 'unknown';
        if ($level >= 30)
            return 'high';
        if ($level >= 15)
            return 'medium';
        if ($level >= 5)
            return 'low';
        return 'very_low';
    }

    /**
     * Get recommended rice varieties for this field
     */
    public function getRecommendedRiceVarieties()
    {
        $currentSeason = (now()->month >= 5 && now()->month <= 10) ? 'wet' : 'dry';

        return RiceVariety::active()
            ->bySeason($currentSeason)
            ->get()
            ->filter(function ($variety) {
                // Additional filtering based on field conditions
                if (!$this->isSuitableForRice()) {
                    return false;
                }

                // Filter by resistance level if soil conditions are challenging
                if ($this->soil_ph !== null && ($this->soil_ph < 5.8 || $this->soil_ph > 7.0)) {
                    return $variety->resistance_level === 'high';
                }

                return true;
            });
    }

    /**
     * Calculate field productivity score
     */
    public function getProductivityScore()
    {
        $score = 0;
        $maxScore = 100;

        // Soil pH (20 points)
        if ($this->soil_ph === null) {
            $score += 10; // Neutral score for missing data
        } elseif ($this->soil_ph >= 6.0 && $this->soil_ph <= 6.8) {
            $score += 20;
        } elseif ($this->soil_ph >= 5.5 && $this->soil_ph <= 7.2) {
            $score += 15;
        } else {
            $score += 5;
        }

        // Water access (25 points)
        switch ($this->water_access) {
            case 'excellent':
                $score += 25;
                break;
            case 'good':
                $score += 20;
                break;
            case 'moderate':
                $score += 10;
                break;
            default:
                $score += 0;
        }

        // Drainage (15 points)
        switch ($this->drainage_quality) {
            case 'excellent':
            case 'good':
                $score += 15;
                break;
            case 'moderate':
                $score += 10;
                break;
            default:
                $score += 0;
        }

        // Soil fertility (25 points)
        $fertility = $this->getSoilFertilityStatus();
        $fertilityScore = 0;
        foreach (['nitrogen', 'phosphorus', 'potassium'] as $nutrient) {
            if (!isset($fertility[$nutrient]) || $fertility[$nutrient] === 'unknown') {
                $fertilityScore += 3; // Neutral assume low-avg
                continue;
            }
            switch ($fertility[$nutrient]) {
                case 'high':
                case 'medium':
                    $fertilityScore += 5;
                    break;
                case 'low':
                    $fertilityScore += 3;
                    break;
                default:
                    $fertilityScore += 0;
            }
        }
        $score += min($fertilityScore + 10, 25); // Base 10 + up to 15 from nutrients

        // Organic matter (15 points)
        if ($this->organic_matter_content === null) {
            $score += 8; // Neutral score
        } elseif ($this->organic_matter_content >= 3.0) {
            $score += 15;
        } elseif ($this->organic_matter_content >= 2.0) {
            $score += 12;
        } elseif ($this->organic_matter_content >= 1.0) {
            $score += 8;
        } else {
            $score += 3;
        }

        return min($score, $maxScore);
    }

    /**
     * Check if field needs preparation
     */
    public function needsPreparation()
    {
        return $this->field_preparation_status !== 'ready';
    }

    /**
     * Get field preparation recommendations
     */
    public function getPreparationRecommendations()
    {
        $recommendations = [];
        $fertility = $this->getSoilFertilityStatus();

        // pH adjustment
        if ($fertility['ph'] === 'needs_attention') {
            if ($this->soil_ph < 5.5) {
                $recommendations[] = 'Apply lime to increase soil pH';
            } elseif ($this->soil_ph > 7.2) {
                $recommendations[] = 'Apply organic matter to lower soil pH';
            }
        }

        // Organic matter
        if ($fertility['organic_matter'] === 'low') {
            $recommendations[] = 'Add compost or organic fertilizer to improve soil structure';
        }

        // Nutrient deficiencies
        if ($fertility['nitrogen'] === 'low' || $fertility['nitrogen'] === 'very_low') {
            $recommendations[] = 'Apply nitrogen fertilizer before planting';
        }

        if ($fertility['phosphorus'] === 'low' || $fertility['phosphorus'] === 'very_low') {
            $recommendations[] = 'Apply phosphorus fertilizer to improve root development';
        }

        if ($fertility['potassium'] === 'low' || $fertility['potassium'] === 'very_low') {
            $recommendations[] = 'Apply potassium fertilizer for better disease resistance';
        }

        // Water management
        if ($this->drainage_quality === 'poor') {
            $recommendations[] = 'Improve field drainage before planting';
        }

        if ($this->water_access === 'poor' || $this->water_access === 'none') {
            $recommendations[] = 'Ensure adequate water supply for rice cultivation';
        }

        return $recommendations;
    }
    /**
     * Get available area for planting
     */
    public function getAvailableAreaAttribute()
    {
        // Calculate used area from active plantings
        $usedArea = $this->plantings
            ->filter(function ($planting) {
                return in_array($planting->status, ['planned', 'planted', 'growing', 'ready']);
            })
            ->sum('area_planted');

        return max(0, $this->size - $usedArea);
    }
}