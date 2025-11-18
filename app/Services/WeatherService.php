<?php

namespace App\Services;

use App\Models\Field;
use App\Models\WeatherLog;
use App\Exceptions\WeatherServiceException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
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
        // Validate API configuration
        if (empty($this->apiKey)) {
            Log::error('OpenWeather API key is not configured');
            throw new WeatherServiceException(
                'OpenWeather API key is not configured. Please set OPENWEATHER_API_KEY in your .env file.',
                ['lat' => $lat, 'lon' => $lon]
            );
        }

        if (empty($this->baseUrl)) {
            Log::error('OpenWeather base URL is not configured');
            throw new WeatherServiceException(
                'OpenWeather base URL is not configured. Please set OPENWEATHER_BASE_URL in your .env file.',
                ['lat' => $lat, 'lon' => $lon]
            );
        }

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

            // Handle specific API errors
            $statusCode = $response->status();
            $errorBody = $response->json();
            
            if ($statusCode === 401) {
                Log::error('OpenWeather API authentication failed', [
                    'status' => $statusCode,
                    'response' => $errorBody
                ]);
                throw new WeatherServiceException(
                    'Invalid OpenWeather API key. Please check your OPENWEATHER_API_KEY in .env',
                    ['lat' => $lat, 'lon' => $lon, 'status' => $statusCode]
                );
            }

            if ($statusCode === 429) {
                Log::error('OpenWeather API rate limit exceeded', [
                    'status' => $statusCode,
                    'response' => $errorBody
                ]);
                throw new WeatherServiceException(
                    'OpenWeather API rate limit exceeded. Please try again later.',
                    ['lat' => $lat, 'lon' => $lon, 'status' => $statusCode]
                );
            }

            Log::error('OpenWeatherMap API error', [
                'status' => $statusCode,
                'response' => $response->body()
            ]);

            return null;
        } catch (WeatherServiceException $e) {
            // Re-throw our custom exceptions
            throw $e;
        } catch (\Exception $e) {
            Log::error('Weather API request failed', [
                'error' => $e->getMessage(),
                'lat' => $lat,
                'lon' => $lon
            ]);

            throw new WeatherServiceException(
                'Failed to fetch weather data: ' . $e->getMessage(),
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
    public function updateFieldWeather(Field $field, ?array $weatherData = null): ?WeatherLog
    {
        // Get coordinates from location or fallback to field_coordinates
        $lat = null;
        $lon = null;
        
        if (isset($field->location['lat']) && isset($field->location['lon'])) {
            $lat = (float) $field->location['lat'];
            $lon = (float) $field->location['lon'];
        } elseif (isset($field->field_coordinates['lat']) && isset($field->field_coordinates['lon'])) {
            // Fallback to field_coordinates if location doesn't have coordinates
            $lat = (float) $field->field_coordinates['lat'];
            $lon = (float) $field->field_coordinates['lon'];
        }
        
        if ($lat === null || $lon === null) {
            Log::warning('Field location coordinates missing', ['field_id' => $field->id]);
            return null;
        }

        if ($weatherData === null) {
            $weatherData = $this->getCurrentWeather($lat, $lon);
        }

        if (!$weatherData) {
            return null;
        }

        if (!isset($weatherData['main']['temp'], $weatherData['main']['humidity'])) {
            Log::warning('Incomplete weather payload from weather provider', [
                'field_id' => $field->id,
                'weather_data' => $weatherData,
            ]);
            return null;
        }

        try {
            $windSpeed = isset($weatherData['wind']['speed'])
                ? round($weatherData['wind']['speed'] * 3.6, 1) // convert m/s to km/h
                : 0;

            $recordedAt = isset($weatherData['dt'])
                ? Carbon::createFromTimestamp($weatherData['dt'])
                : now();

            $weatherLog = WeatherLog::create([
                'field_id' => $field->id,
                'temperature' => round($weatherData['main']['temp'], 1),
                'humidity' => $weatherData['main']['humidity'],
                'wind_speed' => $windSpeed,
                'conditions' => $this->mapWeatherCondition($weatherData['weather'][0]['main'] ?? 'clear'),
                'recorded_at' => $recordedAt,
            ]);

            // Ensure subsequent calls use the freshly created log without re-querying
            $field->setRelation('latestWeather', $weatherLog);

            return $weatherLog;
        } catch (\Exception $e) {
            Log::error('Failed to save weather data', [
                'error' => $e->getMessage(),
                'field_id' => $field->id,
                'weather_data' => $weatherData
            ]);

            return null;
        }
    }

    /**
     * Format a weather log for API responses
     */
    public function formatWeatherLog(?WeatherLog $weatherLog): ?array
    {
        if (!$weatherLog) {
            return null;
        }

        return [
            'id' => $weatherLog->id,
            'field_id' => $weatherLog->field_id,
            'temperature' => (float) $weatherLog->temperature,
            'temperature_fahrenheit' => round(($weatherLog->temperature * 9/5) + 32, 1),
            'humidity' => (float) $weatherLog->humidity,
            'wind_speed' => (float) $weatherLog->wind_speed,
            'conditions' => $weatherLog->conditions,
            'recorded_at' => optional($weatherLog->recorded_at)->toIso8601String(),
            'is_favorable_for_farming' => $weatherLog->isFavorableForFarming(),
        ];
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
        $recordedAt = optional($latestWeather->recorded_at)->toIso8601String();
        $temperature = (float) $latestWeather->temperature;
        $humidity = (float) $latestWeather->humidity;
        $windSpeed = (float) $latestWeather->wind_speed;
        $conditions = $latestWeather->conditions;

        if ($temperature < 15) {
            $alerts[] = $this->makeAlert(
                type: 'extreme_temperature',
                severity: 'high',
                title: 'Cold Stress Warning',
                message: 'Temperature below 15°C can slow rice growth and development.',
                extra: [
                    'category' => 'temperature',
                    'rice_specific' => true,
                    'recommendation' => 'Consider water management to maintain soil temperature.',
                    'recorded_at' => $recordedAt,
                ]
            );
        } elseif ($temperature > 35) {
            $alerts[] = $this->makeAlert(
                type: 'extreme_temperature',
                severity: 'high',
                title: 'Heat Stress Warning',
                message: 'Temperature above 35°C can reduce rice yield, especially during flowering.',
                extra: [
                    'category' => 'temperature',
                    'rice_specific' => true,
                    'recommendation' => 'Ensure adequate water depth (5-10cm) to cool the crop.',
                    'recorded_at' => $recordedAt,
                ]
            );
        }

        // Rice growth stage specific alerts
        if ($currentStage) {
            $stageCode = $currentStage->riceGrowthStage->stage_code;
            
            // Flowering stage alerts
            if ($stageCode === 'flowering') {
                if ($temperature > 30) {
                    $alerts[] = $this->makeAlert(
                        type: 'extreme_temperature',
                        severity: 'critical',
                        title: 'Critical Heat Stress (Flowering)',
                        message: 'High temperature during flowering can cause spikelet sterility.',
                        extra: [
                            'category' => 'temperature',
                            'rice_specific' => true,
                            'growth_stage' => 'flowering',
                            'recommendation' => 'Maintain 5-10cm water depth and consider early morning irrigation.',
                            'recorded_at' => $recordedAt,
                        ]
                    );
                }
                
                if ($humidity < 60) {
                    $alerts[] = $this->makeAlert(
                        type: 'low_humidity',
                        severity: 'medium',
                        title: 'Low Humidity (Flowering)',
                        message: 'Low humidity during flowering may affect pollination.',
                        extra: [
                            'category' => 'humidity',
                            'rice_specific' => true,
                            'growth_stage' => 'flowering',
                            'recommendation' => 'Increase water depth to maintain field humidity.',
                            'recorded_at' => $recordedAt,
                        ]
                    );
                }
            }
            
            // Grain filling stage alerts
            if ($stageCode === 'grain_filling') {
                if ($temperature > 32) {
                    $alerts[] = $this->makeAlert(
                        type: 'extreme_temperature',
                        severity: 'high',
                        title: 'Heat Stress (Grain Filling)',
                        message: 'High temperature during grain filling can reduce grain quality.',
                        extra: [
                            'category' => 'temperature',
                            'rice_specific' => true,
                            'growth_stage' => 'grain_filling',
                            'recommendation' => 'Maintain consistent water supply and avoid water stress.',
                            'recorded_at' => $recordedAt,
                        ]
                    );
                }
            }
        }

        // Wind alerts for rice
        if ($windSpeed > 15) {
            $alerts[] = $this->makeAlert(
                type: 'typhoon',
                severity: 'high',
                title: 'Strong Wind Advisory',
                message: 'Strong winds can cause lodging in rice plants, especially near harvest.',
                extra: [
                    'category' => 'wind',
                    'rice_specific' => true,
                    'recommendation' => 'Monitor for lodging and consider early harvest if plants are mature.',
                    'recorded_at' => $recordedAt,
                ]
            );
        }

        // Humidity alerts for rice diseases
        if ($humidity > 85) {
            $alerts[] = $this->makeAlert(
                type: 'high_humidity',
                severity: 'medium',
                title: 'High Humidity Disease Risk',
                message: 'High humidity increases risk of rice blast and other fungal diseases.',
                extra: [
                    'category' => 'humidity',
                    'rice_specific' => true,
                    'recommendation' => 'Monitor for disease symptoms and consider preventive fungicide application.',
                    'recorded_at' => $recordedAt,
                ]
            );
        }

        // Rainfall and flooding alerts
        if (in_array($conditions, ['stormy', 'rainy'])) {
            $alerts[] = $this->makeAlert(
                type: 'heavy_rain',
                severity: 'medium',
                title: 'Heavy Rainfall Detected',
                message: 'Heavy rainfall detected. Monitor field water levels to prevent flooding damage.',
                extra: [
                    'category' => 'conditions',
                    'rice_specific' => true,
                    'recommendation' => 'Ensure proper drainage and check bund integrity.',
                    'recorded_at' => $recordedAt,
                ]
            );
        }

        return $alerts;
    }

    /**
     * Helper to build consistent alert payloads
     */
    private function makeAlert(string $type, string $severity, string $title, string $message, array $extra = []): array
    {
        return array_merge([
            'id' => (string) Str::uuid(),
            'type' => $type,
            'severity' => $severity,
            'title' => $title,
            'message' => $message,
        ], $extra);
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
                    'priority' => match ($alert['severity'] ?? 'medium') {
                        'critical' => 'high',
                        'high' => 'medium',
                        default => 'low',
                    }
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