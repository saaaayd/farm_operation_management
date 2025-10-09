<?php

namespace App\Services;

use App\Models\Field;
use App\Models\Farm;
use App\Models\WeatherLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class WeatherForecastService
{
    private string $apiKey;
    private string $baseUrl;
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->apiKey = config('services.openweather.api_key');
        $this->baseUrl = config('services.openweather.base_url');
        $this->weatherService = $weatherService;
    }

    /**
     * Get field weather forecast
     */
    public function getFieldForecast(Field $field, int $days = 7, string $forecastType = 'basic')
    {
        if (!isset($field->location['lat']) || !isset($field->location['lon'])) {
            return ['error' => 'Field location coordinates not available'];
        }

        $cacheKey = "weather_forecast_{$field->id}_{$days}_{$forecastType}";
        
        return Cache::remember($cacheKey, 1800, function () use ($field, $days, $forecastType) {
            $forecast = $this->fetchWeatherForecast($field->location['lat'], $field->location['lon'], $days);
            
            if (!$forecast) {
                return ['error' => 'Unable to fetch weather forecast'];
            }

            return $this->processForecast($forecast, $forecastType, $field);
        });
    }

    /**
     * Get extended weather forecast
     */
    public function getExtendedForecast(Field $field, string $period = 'weekly')
    {
        $days = match($period) {
            'weekly' => 7,
            'monthly' => 30,
            'seasonal' => 90,
            default => 7,
        };

        $forecast = $this->getFieldForecast($field, min($days, 14)); // API limit
        
        if ($period === 'seasonal') {
            // For seasonal forecasts, we'd typically use climate models
            $forecast['seasonal_trends'] = $this->getSeasonalTrends($field);
            $forecast['climate_outlook'] = $this->getClimateOutlook($field);
        }

        return [
            'period' => $period,
            'forecast' => $forecast,
            'extended_analysis' => $this->analyzeExtendedForecast($forecast, $period),
        ];
    }

    /**
     * Get agricultural weather forecast
     */
    public function getAgriculturalForecast(Field $field)
    {
        $basicForecast = $this->getFieldForecast($field, 7, 'detailed');
        $currentPlanting = $field->getCurrentRicePlanting();
        
        $agriculturalForecast = [
            'basic_forecast' => $basicForecast,
            'agricultural_insights' => $this->generateAgriculturalInsights($basicForecast, $field),
            'crop_specific_recommendations' => $this->getCropSpecificRecommendations($basicForecast, $currentPlanting),
            'irrigation_forecast' => $this->getIrrigationForecast($basicForecast, $field),
            'pest_disease_forecast' => $this->getPestDiseaseForecast($basicForecast, $field),
            'work_window_forecast' => $this->getWorkWindowForecast($basicForecast),
        ];

        return $agriculturalForecast;
    }

    /**
     * Get weather alerts from forecast
     */
    public function getWeatherAlerts(Field $field)
    {
        $forecast = $this->getFieldForecast($field, 5);
        $alerts = [];

        if (isset($forecast['daily_forecasts'])) {
            foreach ($forecast['daily_forecasts'] as $day) {
                $dayAlerts = $this->analyzeDayForAlerts($day, $field);
                $alerts = array_merge($alerts, $dayAlerts);
            }
        }

        return [
            'alerts' => $alerts,
            'alert_summary' => $this->summarizeAlerts($alerts),
            'recommended_actions' => $this->getRecommendedActions($alerts),
        ];
    }

    /**
     * Get farm-wide weather forecast
     */
    public function getFarmForecast(Farm $farm, int $days = 7)
    {
        $fields = $farm->fields;
        $fieldForecasts = [];
        
        foreach ($fields as $field) {
            if (isset($field->location['lat']) && isset($field->location['lon'])) {
                $fieldForecasts[$field->id] = $this->getFieldForecast($field, $days);
            }
        }

        return [
            'farm' => $farm,
            'field_forecasts' => $fieldForecasts,
            'farm_summary' => $this->generateFarmForecastSummary($fieldForecasts),
            'unified_recommendations' => $this->generateUnifiedRecommendations($fieldForecasts),
        ];
    }

    /**
     * Get activity recommendations based on weather forecast
     */
    public function getActivityRecommendations(Field $field)
    {
        $forecast = $this->getFieldForecast($field, 7);
        $currentPlanting = $field->getCurrentRicePlanting();
        
        $recommendations = [];

        if (isset($forecast['daily_forecasts'])) {
            foreach ($forecast['daily_forecasts'] as $day) {
                $dayRecommendations = $this->generateDayActivityRecommendations($day, $field, $currentPlanting);
                $recommendations[] = [
                    'date' => $day['date'],
                    'recommendations' => $dayRecommendations,
                ];
            }
        }

        return [
            'daily_recommendations' => $recommendations,
            'week_overview' => $this->generateWeekOverview($recommendations),
            'priority_activities' => $this->identifyPriorityActivities($recommendations),
        ];
    }

    /**
     * Get optimal timing for specific activities
     */
    public function getOptimalTiming(Field $field, string $activityType, int $planningDays = 14)
    {
        $forecast = $this->getFieldForecast($field, $planningDays);
        $optimalWindows = [];

        if (isset($forecast['daily_forecasts'])) {
            foreach ($forecast['daily_forecasts'] as $day) {
                $suitability = $this->evaluateActivitySuitability($day, $activityType, $field);
                
                if ($suitability['suitable']) {
                    $optimalWindows[] = [
                        'date' => $day['date'],
                        'suitability_score' => $suitability['score'],
                        'conditions' => $suitability['conditions'],
                        'recommendations' => $suitability['recommendations'],
                    ];
                }
            }
        }

        // Sort by suitability score
        usort($optimalWindows, function ($a, $b) {
            return $b['suitability_score'] <=> $a['suitability_score'];
        });

        return [
            'activity_type' => $activityType,
            'optimal_windows' => array_slice($optimalWindows, 0, 5), // Top 5 windows
            'best_day' => $optimalWindows[0] ?? null,
            'activity_guidelines' => $this->getActivityGuidelines($activityType),
        ];
    }

    /**
     * Get crop protection forecast
     */
    public function getCropProtectionForecast(Field $field)
    {
        $forecast = $this->getFieldForecast($field, 7);
        $currentPlanting = $field->getCurrentRicePlanting();
        
        return [
            'disease_risk_forecast' => $this->forecastDiseaseRisk($forecast, $field),
            'pest_risk_forecast' => $this->forecastPestRisk($forecast, $field),
            'spray_window_forecast' => $this->forecastSprayWindows($forecast),
            'protection_recommendations' => $this->generateProtectionRecommendations($forecast, $currentPlanting),
        ];
    }

    /**
     * Get seasonal weather outlook
     */
    public function getSeasonalOutlook(Farm $farm, string $season = null, int $monthsAhead = 3)
    {
        // This would typically integrate with seasonal climate models
        // For now, providing a structured placeholder
        
        return [
            'season' => $season,
            'outlook_period' => $monthsAhead,
            'temperature_outlook' => $this->getTemperatureOutlook($farm, $monthsAhead),
            'precipitation_outlook' => $this->getPrecipitationOutlook($farm, $monthsAhead),
            'seasonal_recommendations' => $this->getSeasonalRecommendations($farm, $season),
            'climate_risks' => $this->identifySeasonalRisks($farm, $season),
        ];
    }

    /**
     * Subscribe to weather alerts
     */
    public function subscribeToAlerts(Field $field, array $alertTypes, array $notificationMethods, array $thresholds = [])
    {
        // This would integrate with a notification system
        // For now, returning a structured response
        
        return [
            'field_id' => $field->id,
            'alert_types' => $alertTypes,
            'notification_methods' => $notificationMethods,
            'thresholds' => $thresholds,
            'subscription_status' => 'active',
            'created_at' => now(),
        ];
    }

    /**
     * Get forecast accuracy metrics
     */
    public function getForecastAccuracy(Field $field, int $periodDays = 30)
    {
        $startDate = now()->subDays($periodDays);
        $actualWeather = WeatherLog::where('field_id', $field->id)
            ->where('recorded_at', '>=', $startDate)
            ->get();

        // This would compare historical forecasts with actual weather
        // For now, providing placeholder metrics
        
        return [
            'period_days' => $periodDays,
            'temperature_accuracy' => 85.2,
            'humidity_accuracy' => 78.9,
            'precipitation_accuracy' => 72.1,
            'overall_accuracy' => 78.7,
            'accuracy_trend' => 'improving',
            'data_points' => $actualWeather->count(),
        ];
    }

    // Private helper methods

    private function fetchWeatherForecast(float $lat, float $lon, int $days)
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

            Log::error('Weather forecast API error', [
                'status' => $response->status(),
                'response' => $response->body()
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('Weather forecast request failed: ' . $e->getMessage());
            return null;
        }
    }

    private function processForecast(array $forecastData, string $forecastType, Field $field)
    {
        $dailyForecasts = $this->groupForecastsByDay($forecastData['list'] ?? []);
        
        $processed = [
            'daily_forecasts' => $dailyForecasts,
            'summary' => $this->generateForecastSummary($dailyForecasts),
        ];

        if ($forecastType === 'detailed' || $forecastType === 'agricultural') {
            $processed['hourly_details'] = $this->processHourlyDetails($forecastData['list'] ?? []);
            $processed['agricultural_insights'] = $this->generateAgriculturalInsights($processed, $field);
        }

        return $processed;
    }

    private function groupForecastsByDay(array $forecastList)
    {
        $dailyForecasts = [];
        
        foreach ($forecastList as $forecast) {
            $date = Carbon::parse($forecast['dt_txt'])->format('Y-m-d');
            
            if (!isset($dailyForecasts[$date])) {
                $dailyForecasts[$date] = [
                    'date' => $date,
                    'temperature' => ['min' => PHP_FLOAT_MAX, 'max' => PHP_FLOAT_MIN],
                    'humidity' => [],
                    'wind_speed' => [],
                    'conditions' => [],
                    'hourly_data' => [],
                ];
            }
            
            $temp = $forecast['main']['temp'];
            $dailyForecasts[$date]['temperature']['min'] = min($dailyForecasts[$date]['temperature']['min'], $temp);
            $dailyForecasts[$date]['temperature']['max'] = max($dailyForecasts[$date]['temperature']['max'], $temp);
            $dailyForecasts[$date]['humidity'][] = $forecast['main']['humidity'];
            $dailyForecasts[$date]['wind_speed'][] = $forecast['wind']['speed'] ?? 0;
            $dailyForecasts[$date]['conditions'][] = $forecast['weather'][0]['main'] ?? 'Unknown';
            $dailyForecasts[$date]['hourly_data'][] = $forecast;
        }

        // Calculate averages and most common conditions
        foreach ($dailyForecasts as &$day) {
            $day['temperature']['avg'] = ($day['temperature']['min'] + $day['temperature']['max']) / 2;
            $day['humidity_avg'] = array_sum($day['humidity']) / count($day['humidity']);
            $day['wind_speed_avg'] = array_sum($day['wind_speed']) / count($day['wind_speed']);
            $day['most_common_condition'] = $this->getMostCommonCondition($day['conditions']);
        }

        return array_values($dailyForecasts);
    }

    private function getMostCommonCondition(array $conditions)
    {
        $counts = array_count_values($conditions);
        arsort($counts);
        return array_key_first($counts);
    }

    private function generateForecastSummary(array $dailyForecasts)
    {
        if (empty($dailyForecasts)) {
            return null;
        }

        $temperatures = array_column($dailyForecasts, 'temperature');
        $avgTemps = array_column($temperatures, 'avg');
        
        return [
            'avg_temperature' => round(array_sum($avgTemps) / count($avgTemps), 1),
            'temperature_range' => [
                'min' => round(min(array_column($temperatures, 'min')), 1),
                'max' => round(max(array_column($temperatures, 'max')), 1),
            ],
            'dominant_condition' => $this->getDominantCondition($dailyForecasts),
            'forecast_confidence' => 'medium', // This would be calculated based on various factors
        ];
    }

    private function getDominantCondition(array $dailyForecasts)
    {
        $allConditions = [];
        foreach ($dailyForecasts as $day) {
            $allConditions[] = $day['most_common_condition'];
        }
        
        $counts = array_count_values($allConditions);
        arsort($counts);
        return array_key_first($counts);
    }

    private function processHourlyDetails(array $forecastList)
    {
        return array_map(function ($forecast) {
            return [
                'datetime' => $forecast['dt_txt'],
                'temperature' => $forecast['main']['temp'],
                'humidity' => $forecast['main']['humidity'],
                'wind_speed' => $forecast['wind']['speed'] ?? 0,
                'conditions' => $forecast['weather'][0]['main'] ?? 'Unknown',
                'precipitation_probability' => $forecast['pop'] ?? 0,
            ];
        }, $forecastList);
    }

    private function generateAgriculturalInsights(array $forecast, Field $field)
    {
        $insights = [];
        
        if (isset($forecast['daily_forecasts'])) {
            foreach ($forecast['daily_forecasts'] as $day) {
                $dayInsights = [];
                
                // Temperature insights
                if ($day['temperature']['max'] > 35) {
                    $dayInsights[] = 'High temperature may cause heat stress in rice plants';
                }
                
                if ($day['temperature']['min'] < 15) {
                    $dayInsights[] = 'Low temperature may slow rice growth';
                }
                
                // Humidity insights
                if ($day['humidity_avg'] > 85) {
                    $dayInsights[] = 'High humidity increases disease risk';
                }
                
                // Wind insights
                if ($day['wind_speed_avg'] > 15) {
                    $dayInsights[] = 'Strong winds may cause lodging in mature rice plants';
                }
                
                $insights[$day['date']] = $dayInsights;
            }
        }
        
        return $insights;
    }

    // Additional helper methods would be implemented here...
    // For brevity, including placeholders for remaining private methods

    private function getCropSpecificRecommendations($forecast, $planting)
    {
        return ['recommendations' => 'Crop-specific recommendations based on forecast'];
    }

    private function getIrrigationForecast($forecast, $field)
    {
        return ['irrigation_needs' => 'Irrigation forecast based on weather'];
    }

    private function getPestDiseaseForecast($forecast, $field)
    {
        return ['pest_disease_risk' => 'Pest and disease forecast'];
    }

    private function getWorkWindowForecast($forecast)
    {
        return ['work_windows' => 'Optimal work windows based on weather'];
    }

    private function analyzeDayForAlerts($day, $field)
    {
        $alerts = [];
        
        if ($day['temperature']['max'] > 35) {
            $alerts[] = [
                'type' => 'temperature',
                'severity' => 'warning',
                'message' => 'High temperature expected',
                'date' => $day['date'],
            ];
        }
        
        return $alerts;
    }

    private function summarizeAlerts($alerts)
    {
        return [
            'total_alerts' => count($alerts),
            'by_severity' => array_count_values(array_column($alerts, 'severity')),
        ];
    }

    private function getRecommendedActions($alerts)
    {
        return ['actions' => 'Recommended actions based on alerts'];
    }

    private function generateFarmForecastSummary($fieldForecasts)
    {
        return ['summary' => 'Farm-wide forecast summary'];
    }

    private function generateUnifiedRecommendations($fieldForecasts)
    {
        return ['recommendations' => 'Unified farm recommendations'];
    }

    private function generateDayActivityRecommendations($day, $field, $planting)
    {
        return ['recommendations' => 'Daily activity recommendations'];
    }

    private function generateWeekOverview($recommendations)
    {
        return ['overview' => 'Weekly overview of recommendations'];
    }

    private function identifyPriorityActivities($recommendations)
    {
        return ['priority_activities' => 'Priority activities for the week'];
    }

    private function evaluateActivitySuitability($day, $activityType, $field)
    {
        // Simplified suitability evaluation
        $score = 50; // Base score
        
        // Adjust based on conditions
        if ($day['most_common_condition'] === 'Clear') {
            $score += 20;
        }
        
        if ($day['wind_speed_avg'] < 10) {
            $score += 15;
        }
        
        return [
            'suitable' => $score >= 60,
            'score' => $score,
            'conditions' => $day,
            'recommendations' => ['recommendation' => 'Activity suitability assessment'],
        ];
    }

    private function getActivityGuidelines($activityType)
    {
        return ['guidelines' => "Guidelines for {$activityType} activity"];
    }

    private function forecastDiseaseRisk($forecast, $field)
    {
        return ['disease_risk' => 'Disease risk forecast'];
    }

    private function forecastPestRisk($forecast, $field)
    {
        return ['pest_risk' => 'Pest risk forecast'];
    }

    private function forecastSprayWindows($forecast)
    {
        return ['spray_windows' => 'Optimal spray windows'];
    }

    private function generateProtectionRecommendations($forecast, $planting)
    {
        return ['protection' => 'Crop protection recommendations'];
    }

    private function getSeasonalTrends($field)
    {
        return ['trends' => 'Seasonal weather trends'];
    }

    private function getClimateOutlook($field)
    {
        return ['outlook' => 'Climate outlook'];
    }

    private function analyzeExtendedForecast($forecast, $period)
    {
        return ['analysis' => "Extended forecast analysis for {$period}"];
    }

    private function getTemperatureOutlook($farm, $months)
    {
        return ['temperature' => 'Temperature outlook'];
    }

    private function getPrecipitationOutlook($farm, $months)
    {
        return ['precipitation' => 'Precipitation outlook'];
    }

    private function getSeasonalRecommendations($farm, $season)
    {
        return ['recommendations' => "Seasonal recommendations for {$season}"];
    }

    private function identifySeasonalRisks($farm, $season)
    {
        return ['risks' => "Seasonal risks for {$season}"];
    }
}