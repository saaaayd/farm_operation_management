<?php

namespace App\Http\Controllers\Weather;

use App\Http\Controllers\Controller;
use App\Models\Field;
use App\Models\WeatherLog;
use App\Services\WeatherService;
use App\Exceptions\WeatherServiceException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class WeatherController extends Controller
{
    private WeatherService $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * Get current weather for a field
     */
    public function getCurrentWeather(Request $request, Field $field): JsonResponse
    {
        // Check if user can access this field
        $user = $request->user();
        if (!$user->isAdmin() && $field->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

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
            return response()->json([
                'message' => 'Field location coordinates are required'
            ], 400);
        }

        try {
            $weatherData = $this->weatherService->getCurrentWeather($lat, $lon);
        } catch (WeatherServiceException $e) {
            return response()->json([
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Unable to fetch weather data: ' . $e->getMessage()
            ], 500);
        }

        if (!$weatherData || !isset($weatherData['main'])) {
            return response()->json([
                'message' => 'Unable to fetch weather data'
            ], 500);
        }

        // Save weather data to database
        $weatherLog = $this->weatherService->updateFieldWeather($field, $weatherData);

        if (!$weatherLog) {
            return response()->json([
                'message' => 'Failed to store weather data'
            ], 500);
        }

        return response()->json([
            'field' => $field,
            'weather' => $this->weatherService->formatWeatherLog($field->latestWeather ?? $weatherLog),
            'alerts' => $this->weatherService->getWeatherAlerts($field)
        ]);
    }

    /**
     * Get weather forecast for a field
     */
    public function getForecast(Request $request, Field $field): JsonResponse
    {
        // Check if user can access this field
        $user = $request->user();
        if (!$user->isAdmin() && $field->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

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
            return response()->json([
                'message' => 'Field location coordinates are required'
            ], 400);
        }

        $days = $request->get('days', 5);
        if ($days > 5) $days = 5; // API limitation

        $forecastData = $this->weatherService->getForecast($lat, $lon, $days);

        if (!$forecastData) {
            return response()->json([
                'message' => 'Unable to fetch forecast data'
            ], 500);
        }

        return response()->json([
            'field' => $field,
            'forecast' => $forecastData
        ]);
    }

    /**
     * Get weather history for a field
     */
    public function getHistory(Request $request, Field $field): JsonResponse
    {
        // Check if user can access this field
        $user = $request->user();
        if (!$user->isAdmin() && $field->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'days' => 'integer|min:1|max:365',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:100'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $days = $request->get('days', 30);
        $perPage = $request->get('per_page', 20);

        $weatherLogs = WeatherLog::where('field_id', $field->id)
            ->where('recorded_at', '>=', now()->subDays($days))
            ->orderBy('recorded_at', 'desc')
            ->paginate($perPage);

        $stats = $this->weatherService->getFieldWeatherStats($field, $days);

        return response()->json([
            'field' => $field,
            'weather_logs' => $weatherLogs,
            'stats' => $stats
        ]);
    }

    /**
     * Get weather alerts for a field
     */
    public function getAlerts(Request $request, Field $field): JsonResponse
    {
        // Check if user can access this field
        $user = $request->user();
        if (!$user->isAdmin() && $field->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $alerts = $this->weatherService->getWeatherAlerts($field);

        return response()->json([
            'field' => $field,
            'alerts' => $alerts
        ]);
    }

    /**
     * Update weather data for a field
     */
    public function updateWeather(Request $request, Field $field): JsonResponse
    {
        // Check if user can access this field
        $user = $request->user();
        if (!$user->isAdmin() && $field->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $weatherLog = $this->weatherService->updateFieldWeather($field);

        if (!$weatherLog) {
            return response()->json([
                'message' => 'Failed to update weather data'
            ], 500);
        }

        return response()->json([
            'message' => 'Weather data updated successfully',
            'field' => $field,
            'latest_weather' => $this->weatherService->formatWeatherLog($field->latestWeather ?? $weatherLog),
            'alerts' => $this->weatherService->getWeatherAlerts($field)
        ]);
    }

    /**
     * Update weather data for all user's fields
     */
    public function updateAllWeather(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            $updated = $this->weatherService->updateAllFieldsWeather();
        } else {
            $fields = Field::where('user_id', $user->id)->get();
            $updated = 0;

            foreach ($fields as $field) {
                if ($this->weatherService->updateFieldWeather($field)) {
                    $updated++;
                }
            }
        }

        return response()->json([
            'message' => "Weather data updated for {$updated} fields",
            'updated_count' => $updated
        ]);
    }

    /**
     * Get weather dashboard data
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Field::query();
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $fields = $query->with('latestWeather')->get();

        $dashboardData = [
            'total_fields' => $fields->count(),
            'fields_with_weather' => $fields->filter(fn($field) => $field->latestWeather)->count(),
            'weather_alerts' => [],
            'field_weather' => []
        ];

        foreach ($fields as $field) {
            if ($field->latestWeather) {
                $alerts = $this->weatherService->getWeatherAlerts($field);
                $dashboardData['weather_alerts'] = array_merge(
                    $dashboardData['weather_alerts'],
                    array_map(fn($alert) => array_merge($alert, ['field' => $field->name ?? "Field {$field->id}"]), $alerts)
                );

                $dashboardData['field_weather'][] = [
                    'field' => $field,
                    'weather' => $this->weatherService->formatWeatherLog($field->latestWeather),
                    'alerts_count' => count($alerts)
                ];
            }
        }

        return response()->json($dashboardData);
    }

    /**
     * Get rice-specific weather analytics for a field
     */
    public function getRiceAnalytics(Request $request, Field $field): JsonResponse
    {
        // Check if user can access this field
        $user = $request->user();
        if (!$user->isAdmin() && $field->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'days' => 'integer|min:1|max:365',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $days = $request->get('days', 30);

        try {
            $analytics = $this->weatherService->getRiceWeatherAnalytics($field, $days);
            $recommendations = $this->weatherService->getRiceFarmingRecommendations($field);

            return response()->json([
                'field' => $field,
                'analytics' => $analytics,
                'recommendations' => $recommendations,
                'period_days' => $days
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get rice weather analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rice farming dashboard with weather insights
     */
    public function getRiceDashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Field::query();
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }

        $fields = $query->with(['latestWeather', 'plantings.riceVariety'])->get();

        $dashboardData = [
            'total_fields' => $fields->count(),
            'rice_fields' => 0,
            'active_plantings' => 0,
            'weather_alerts' => [],
            'rice_analytics_summary' => [
                'total_growing_degree_days' => 0,
                'heat_stress_fields' => 0,
                'optimal_conditions_fields' => 0,
                'disease_risk_fields' => 0,
            ],
            'field_details' => []
        ];

        foreach ($fields as $field) {
            $currentPlanting = $field->getCurrentRicePlanting();
            $isRiceField = $currentPlanting !== null;
            
            if ($isRiceField) {
                $dashboardData['rice_fields']++;
                $dashboardData['active_plantings']++;
            }

            if ($field->latestWeather) {
                $alerts = $this->weatherService->getWeatherAlerts($field);
                $riceAlerts = array_filter($alerts, fn($alert) => isset($alert['rice_specific']) && $alert['rice_specific']);
                
                $dashboardData['weather_alerts'] = array_merge(
                    $dashboardData['weather_alerts'],
                    array_map(fn($alert) => array_merge($alert, ['field' => $field->name ?? "Field {$field->id}"]), $riceAlerts)
                );

                // Get rice analytics for the field
                if ($isRiceField) {
                    try {
                        $analytics = $this->weatherService->getRiceWeatherAnalytics($field, 7);
                        if (isset($analytics['rice_analytics'])) {
                            $riceAnalytics = $analytics['rice_analytics'];
                            
                            $dashboardData['rice_analytics_summary']['total_growing_degree_days'] += $riceAnalytics['growing_degree_days'] ?? 0;
                            
                            if (($riceAnalytics['heat_stress_days'] ?? 0) > 0) {
                                $dashboardData['rice_analytics_summary']['heat_stress_fields']++;
                            }
                            
                            if (($riceAnalytics['weather_suitability_score'] ?? 0) >= 70) {
                                $dashboardData['rice_analytics_summary']['optimal_conditions_fields']++;
                            }
                            
                            if (($riceAnalytics['disease_risk_days'] ?? 0) > 2) {
                                $dashboardData['rice_analytics_summary']['disease_risk_fields']++;
                            }
                        }
                    } catch (\Exception $e) {
                        // Continue processing other fields if one fails
                    }
                }

                $dashboardData['field_details'][] = [
                    'field' => $field,
                    'weather' => $this->weatherService->formatWeatherLog($field->latestWeather),
                    'is_rice_field' => $isRiceField,
                    'current_planting' => $currentPlanting,
                    'alerts_count' => count($riceAlerts),
                    'critical_alerts' => count(array_filter($riceAlerts, fn($alert) => ($alert['severity'] ?? null) === 'critical')),
                ];
            }
        }

        return response()->json($dashboardData);
    }
}