<?php

namespace App\Services;

use App\Models\Field;
use App\Models\WeatherLog;
use App\Exceptions\WeatherServiceException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class WeatherService
{
    private string $apiKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.openweather.api_key');
        $this->baseUrl = config('services.openweather.base_url');
    }

    /**
     * Get current weather for a location
     */
    public function getCurrentWeather(float $lat, float $lon): ?array
    {
        try {
            $response = Http::get("{$this->baseUrl}/weather", [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => $this->apiKey,
                'units' => 'metric'
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('OpenWeatherMap API error', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Weather API request failed', [
                'error' => $e->getMessage(),
                'lat' => $lat,
                'lon' => $lon
            ]);

            throw new WeatherServiceException(
                'Failed to fetch weather data',
                ['lat' => $lat, 'lon' => $lon, 'error' => $e->getMessage()]
            );
        }
    }

    /**
     * Get weather forecast for a location
     */
    public function getForecast(float $lat, float $lon, int $days = 5): ?array
    {
        try {
            $response = Http::get("{$this->baseUrl}/forecast", [
                'lat' => $lat,
                'lon' => $lon,
                'appid' => $this->apiKey,
                'units' => 'metric',
                'cnt' => $days * 8 // 8 forecasts per day (3-hour intervals)
            ]);

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('OpenWeatherMap forecast API error', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Weather forecast API request failed', [
                'error' => $e->getMessage(),
                'lat' => $lat,
                'lon' => $lon
            ]);

            return null;
        }
    }

    /**
     * Update weather data for a field
     */
    public function updateFieldWeather(Field $field): bool
    {
        if (!isset($field->location['lat']) || !isset($field->location['lon'])) {
            Log::warning('Field location coordinates missing', ['field_id' => $field->id]);
            return false;
        }

        $weatherData = $this->getCurrentWeather(
            $field->location['lat'],
            $field->location['lon']
        );

        if (!$weatherData) {
            return false;
        }

        try {
            WeatherLog::create([
                'field_id' => $field->id,
                'temperature' => $weatherData['main']['temp'],
                'humidity' => $weatherData['main']['humidity'],
                'wind_speed' => $weatherData['wind']['speed'] ?? 0,
                'conditions' => $this->mapWeatherCondition($weatherData['weather'][0]['main']),
                'recorded_at' => now(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to save weather data', [
                'error' => $e->getMessage(),
                'field_id' => $field->id,
                'weather_data' => $weatherData
            ]);

            return false;
        }
    }

    /**
     * Update weather data for all fields
     */
    public function updateAllFieldsWeather(): int
    {
        $fields = Field::whereNotNull('location')->get();
        $updated = 0;

        foreach ($fields as $field) {
            if ($this->updateFieldWeather($field)) {
                $updated++;
            }
            
            // Add delay to respect API rate limits
            sleep(1);
        }

        return $updated;
    }

    /**
     * Get weather alerts for rice farming conditions
     */
    public function getWeatherAlerts(Field $field): array
    {
        $alerts = [];
        $latestWeather = $field->latestWeather;

        if (!$latestWeather) {
            return $alerts;
        }

        // Get current rice planting if exists
        $currentPlanting = $field->getCurrentRicePlanting();
        $currentStage = $currentPlanting?->getCurrentStage();

        // Rice-specific temperature alerts
        if ($latestWeather->temperature < 15) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Cold stress warning: Temperature below 15°C can slow rice growth and development.',
                'category' => 'temperature',
                'rice_specific' => true,
                'recommendation' => 'Consider water management to maintain soil temperature.'
            ];
        } elseif ($latestWeather->temperature > 35) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Heat stress warning: Temperature above 35°C can reduce rice yield, especially during flowering.',
                'category' => 'temperature',
                'rice_specific' => true,
                'recommendation' => 'Ensure adequate water depth (5-10cm) to cool the crop.'
            ];
        }

        // Rice growth stage specific alerts
        if ($currentStage) {
            $stageCode = $currentStage->riceGrowthStage->stage_code;
            
            // Flowering stage alerts
            if ($stageCode === 'flowering') {
                if ($latestWeather->temperature > 30) {
                    $alerts[] = [
                        'type' => 'critical',
                        'message' => 'Critical: High temperature during flowering can cause spikelet sterility.',
                        'category' => 'temperature',
                        'rice_specific' => true,
                        'growth_stage' => 'flowering',
                        'recommendation' => 'Maintain 5-10cm water depth and consider early morning irrigation.'
                    ];
                }
                
                if ($latestWeather->humidity < 60) {
                    $alerts[] = [
                        'type' => 'warning',
                        'message' => 'Low humidity during flowering may affect pollination.',
                        'category' => 'humidity',
                        'rice_specific' => true,
                        'growth_stage' => 'flowering',
                        'recommendation' => 'Increase water depth to maintain field humidity.'
                    ];
                }
            }
            
            // Grain filling stage alerts
            if ($stageCode === 'grain_filling') {
                if ($latestWeather->temperature > 32) {
                    $alerts[] = [
                        'type' => 'warning',
                        'message' => 'High temperature during grain filling can reduce grain quality.',
                        'category' => 'temperature',
                        'rice_specific' => true,
                        'growth_stage' => 'grain_filling',
                        'recommendation' => 'Maintain consistent water supply and avoid water stress.'
                    ];
                }
            }
        }

        // Wind alerts for rice
        if ($latestWeather->wind_speed > 15) {
            $alerts[] = [
                'type' => 'caution',
                'message' => 'Strong winds can cause lodging in rice plants, especially near harvest.',
                'category' => 'wind',
                'rice_specific' => true,
                'recommendation' => 'Monitor for lodging and consider early harvest if plants are mature.'
            ];
        }

        // Humidity alerts for rice diseases
        if ($latestWeather->humidity > 85) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'High humidity increases risk of rice blast and other fungal diseases.',
                'category' => 'humidity',
                'rice_specific' => true,
                'recommendation' => 'Monitor for disease symptoms and consider preventive fungicide application.'
            ];
        }

        // Rainfall and flooding alerts
        if (in_array($latestWeather->conditions, ['stormy', 'rainy'])) {
            $alerts[] = [
                'type' => 'info',
                'message' => 'Heavy rainfall detected. Monitor field water levels to prevent flooding damage.',
                'category' => 'conditions',
                'rice_specific' => true,
                'recommendation' => 'Ensure proper drainage and check bund integrity.'
            ];
        }

        return $alerts;
    }

    /**
     * Get weather statistics for a field
     */
    public function getFieldWeatherStats(Field $field, int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $weatherLogs = WeatherLog::where('field_id', $field->id)
            ->where('recorded_at', '>=', $startDate)
            ->orderBy('recorded_at')
            ->get();

        if ($weatherLogs->isEmpty()) {
            return [];
        }

        return [
            'avg_temperature' => round($weatherLogs->avg('temperature'), 1),
            'min_temperature' => round($weatherLogs->min('temperature'), 1),
            'max_temperature' => round($weatherLogs->max('temperature'), 1),
            'avg_humidity' => round($weatherLogs->avg('humidity'), 1),
            'avg_wind_speed' => round($weatherLogs->avg('wind_speed'), 1),
            'most_common_condition' => $weatherLogs->groupBy('conditions')
                ->map->count()
                ->sortDesc()
                ->keys()
                ->first(),
            'total_readings' => $weatherLogs->count(),
            'period_days' => $days
        ];
    }

    /**
     * Map OpenWeatherMap weather conditions to our system
     */
    private function mapWeatherCondition(string $condition): string
    {
        return match(strtolower($condition)) {
            'clear' => WeatherLog::CONDITION_CLEAR,
            'clouds' => WeatherLog::CONDITION_CLOUDY,
            'rain', 'drizzle' => WeatherLog::CONDITION_RAINY,
            'thunderstorm' => WeatherLog::CONDITION_STORMY,
            'snow' => WeatherLog::CONDITION_SNOWY,
            'mist', 'fog', 'haze' => WeatherLog::CONDITION_FOGGY,
            default => WeatherLog::CONDITION_CLEAR
        };
    }

    /**
     * Get rice-specific weather analytics
     */
    public function getRiceWeatherAnalytics(Field $field, int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);
        
        $weatherLogs = WeatherLog::where('field_id', $field->id)
            ->where('recorded_at', '>=', $startDate)
            ->orderBy('recorded_at')
            ->get();

        if ($weatherLogs->isEmpty()) {
            return [];
        }

        // Basic stats
        $basicStats = $this->getFieldWeatherStats($field, $days);

        // Rice-specific analytics
        $riceAnalytics = [
            'growing_degree_days' => $this->calculateGrowingDegreeDays($weatherLogs),
            'heat_stress_days' => $weatherLogs->where('temperature', '>', 35)->count(),
            'cold_stress_days' => $weatherLogs->where('temperature', '<', 15)->count(),
            'optimal_temperature_days' => $weatherLogs->whereBetween('temperature', [20, 30])->count(),
            'high_humidity_days' => $weatherLogs->where('humidity', '>', 85)->count(),
            'disease_risk_days' => $weatherLogs->where('humidity', '>', 80)->where('temperature', '>', 25)->count(),
            'drought_stress_risk' => $this->assessDroughtStressRisk($weatherLogs),
            'flood_risk_days' => $weatherLogs->whereIn('conditions', ['rainy', 'stormy'])->count(),
            'optimal_growth_days' => $this->calculateOptimalGrowthDays($weatherLogs),
            'weather_suitability_score' => $this->calculateWeatherSuitabilityScore($weatherLogs),
        ];

        // Growth stage specific analytics
        $currentPlanting = $field->getCurrentRicePlanting();
        if ($currentPlanting) {
            $riceAnalytics['current_planting'] = [
                'days_since_planting' => $currentPlanting->getDaysSincePlanting(),
                'current_stage' => $currentPlanting->getCurrentStage()?->riceGrowthStage->name,
                'stage_weather_suitability' => $this->assessStageWeatherSuitability($currentPlanting, $weatherLogs),
            ];
        }

        return array_merge($basicStats, ['rice_analytics' => $riceAnalytics]);
    }

    /**
     * Calculate Growing Degree Days for rice
     */
    private function calculateGrowingDegreeDays($weatherLogs): float
    {
        $baseTemp = 10; // Base temperature for rice (°C)
        $maxTemp = 30;  // Maximum effective temperature for rice (°C)
        
        $gdd = 0;
        foreach ($weatherLogs as $log) {
            $effectiveTemp = min($log->temperature, $maxTemp);
            if ($effectiveTemp > $baseTemp) {
                $gdd += ($effectiveTemp - $baseTemp);
            }
        }
        
        return round($gdd, 1);
    }

    /**
     * Calculate optimal growth days
     */
    private function calculateOptimalGrowthDays($weatherLogs): int
    {
        return $weatherLogs->filter(function ($log) {
            return $log->temperature >= 20 && $log->temperature <= 30 
                && $log->humidity >= 60 && $log->humidity <= 80;
        })->count();
    }

    /**
     * Calculate weather suitability score for rice (0-100)
     */
    private function calculateWeatherSuitabilityScore($weatherLogs): int
    {
        if ($weatherLogs->isEmpty()) {
            return 0;
        }

        $score = 0;
        $totalLogs = $weatherLogs->count();

        // Temperature score (40% weight)
        $optimalTempDays = $weatherLogs->whereBetween('temperature', [20, 30])->count();
        $tempScore = ($optimalTempDays / $totalLogs) * 40;

        // Humidity score (30% weight)
        $optimalHumidityDays = $weatherLogs->whereBetween('humidity', [60, 80])->count();
        $humidityScore = ($optimalHumidityDays / $totalLogs) * 30;

        // Weather conditions score (20% weight)
        $goodConditionDays = $weatherLogs->whereIn('conditions', ['clear', 'cloudy'])->count();
        $conditionsScore = ($goodConditionDays / $totalLogs) * 20;

        // Wind score (10% weight)
        $calmWindDays = $weatherLogs->where('wind_speed', '<=', 15)->count();
        $windScore = ($calmWindDays / $totalLogs) * 10;

        $score = $tempScore + $humidityScore + $conditionsScore + $windScore;

        return min(100, max(0, round($score)));
    }

    /**
     * Assess drought stress risk
     */
    private function assessDroughtStressRisk($weatherLogs): string
    {
        $recentLogs = $weatherLogs->sortByDesc('recorded_at')->take(7); // Last 7 days
        
        $lowHumidityDays = $recentLogs->where('humidity', '<', 50)->count();
        $highTempDays = $recentLogs->where('temperature', '>', 32)->count();
        $noRainDays = $recentLogs->whereNotIn('conditions', ['rainy', 'stormy'])->count();

        if ($lowHumidityDays >= 5 && $highTempDays >= 3 && $noRainDays >= 6) {
            return 'high';
        } elseif ($lowHumidityDays >= 3 && $highTempDays >= 2 && $noRainDays >= 4) {
            return 'moderate';
        } else {
            return 'low';
        }
    }

    /**
     * Assess weather suitability for current growth stage
     */
    private function assessStageWeatherSuitability($planting, $weatherLogs): array
    {
        $currentStage = $planting->getCurrentStage();
        if (!$currentStage) {
            return ['suitability' => 'unknown', 'recommendations' => []];
        }

        $stageCode = $currentStage->riceGrowthStage->stage_code;
        $recentWeather = $weatherLogs->sortByDesc('recorded_at')->take(7);
        
        $recommendations = [];
        $suitabilityScore = 100;

        switch ($stageCode) {
            case 'seedling':
                // Seedling stage needs moderate temperature and high humidity
                $coldDays = $recentWeather->where('temperature', '<', 18)->count();
                if ($coldDays > 2) {
                    $suitabilityScore -= 20;
                    $recommendations[] = 'Consider using row covers or windbreaks to protect seedlings from cold.';
                }
                break;

            case 'tillering':
                // Tillering stage benefits from warm temperatures
                $optimalDays = $recentWeather->whereBetween('temperature', [25, 30])->count();
                if ($optimalDays < 4) {
                    $suitabilityScore -= 15;
                    $recommendations[] = 'Maintain adequate water depth to regulate temperature.';
                }
                break;

            case 'flowering':
                // Critical stage - very sensitive to temperature and humidity
                $hotDays = $recentWeather->where('temperature', '>', 30)->count();
                $lowHumidityDays = $recentWeather->where('humidity', '<', 60)->count();
                
                if ($hotDays > 1) {
                    $suitabilityScore -= 30;
                    $recommendations[] = 'Critical: Maintain 5-10cm water depth during flowering to prevent heat stress.';
                }
                if ($lowHumidityDays > 2) {
                    $suitabilityScore -= 20;
                    $recommendations[] = 'Increase field humidity through proper water management.';
                }
                break;

            case 'grain_filling':
                // Grain filling needs consistent moderate temperatures
                $stressDays = $recentWeather->where('temperature', '>', 32)->count();
                if ($stressDays > 2) {
                    $suitabilityScore -= 25;
                    $recommendations[] = 'Avoid water stress during grain filling to maintain grain quality.';
                }
                break;

            case 'ripening':
                // Ripening stage benefits from dry conditions
                $rainyDays = $recentWeather->whereIn('conditions', ['rainy', 'stormy'])->count();
                if ($rainyDays > 3) {
                    $suitabilityScore -= 15;
                    $recommendations[] = 'Monitor for grain quality issues due to wet conditions.';
                }
                break;
        }

        $suitability = match(true) {
            $suitabilityScore >= 80 => 'excellent',
            $suitabilityScore >= 60 => 'good',
            $suitabilityScore >= 40 => 'moderate',
            default => 'poor'
        };

        return [
            'suitability' => $suitability,
            'score' => max(0, $suitabilityScore),
            'stage' => $currentStage->riceGrowthStage->name,
            'recommendations' => $recommendations
        ];
    }

    /**
     * Get rice farming weather recommendations
     */
    public function getRiceFarmingRecommendations(Field $field): array
    {
        $weatherAnalytics = $this->getRiceWeatherAnalytics($field, 7); // Last 7 days
        $alerts = $this->getWeatherAlerts($field);
        
        $recommendations = [];
        
        // Extract recommendations from alerts
        foreach ($alerts as $alert) {
            if (isset($alert['recommendation'])) {
                $recommendations[] = [
                    'type' => $alert['type'],
                    'category' => $alert['category'],
                    'recommendation' => $alert['recommendation'],
                    'priority' => $alert['type'] === 'critical' ? 'high' : ($alert['type'] === 'warning' ? 'medium' : 'low')
                ];
            }
        }

        // Add general recommendations based on analytics
        if (isset($weatherAnalytics['rice_analytics'])) {
            $analytics = $weatherAnalytics['rice_analytics'];
            
            if ($analytics['heat_stress_days'] > 2) {
                $recommendations[] = [
                    'type' => 'general',
                    'category' => 'irrigation',
                    'recommendation' => 'Increase irrigation frequency due to recent heat stress conditions.',
                    'priority' => 'medium'
                ];
            }
            
            if ($analytics['weather_suitability_score'] < 60) {
                $recommendations[] = [
                    'type' => 'general',
                    'category' => 'monitoring',
                    'recommendation' => 'Weather conditions are suboptimal. Monitor crops closely for stress symptoms.',
                    'priority' => 'medium'
                ];
            }
        }

        return $recommendations;
    }

    /**
     * Check if API key is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->baseUrl);
    }
}