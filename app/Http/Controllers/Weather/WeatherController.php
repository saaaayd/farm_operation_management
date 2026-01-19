<?php

namespace App\Http\Controllers\Weather;

use App\Http\Controllers\Controller;
use App\Models\Field;
use App\Models\WeatherLog;
use App\Services\WeatherService;
use App\Services\ColorfulCloudsWeatherService;
use App\Exceptions\WeatherServiceException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class WeatherController extends Controller
{
    private WeatherService $weatherService;
    private ColorfulCloudsWeatherService $colorfulCloudsService;

    public function __construct(WeatherService $weatherService, ColorfulCloudsWeatherService $colorfulCloudsService)
    {
        $this->weatherService = $weatherService;
        $this->colorfulCloudsService = $colorfulCloudsService;
    }

    /**
     * Get current weather for a field
     */
    public function getCurrentWeather(Request $request, Field $field): JsonResponse
    {
        // Check if user can access this field
        $user = $request->user();
        if ($field->user_id !== $user->id) {
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
        if ($field->user_id !== $user->id) {
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

        $days = $request->get('days', 7);
        if ($days > 7)
            $days = 7; // Limit to 7 days

        // Use ColorfulClouds for forecasts (supports up to 10 days, better than OpenWeatherMap's 5 days)
        // Request one extra day to ensure we get the full requested number (API might exclude today)
        try {
            $forecastData = $this->colorfulCloudsService->getForecast($lat, $lon, $days + 1, 'metric', 'en_US');

            if (empty($forecastData)) {
                // Fallback to OpenWeatherMap if ColorfulClouds fails
                $forecastData = $this->weatherService->getForecast($lat, $lon, $days);

                if (!$forecastData || !isset($forecastData['list'])) {
                    return response()->json([
                        'message' => 'Unable to fetch forecast data'
                    ], 500);
                }

                // Process OpenWeatherMap forecast data to daily format
                $dailyForecasts = $this->processForecastToDaily($forecastData['list'], $days);
            } else {
                // Process ColorfulClouds forecast data to daily format
                $dailyForecasts = $this->processColorfulCloudsForecast($forecastData, $days);
            }
        } catch (\Exception $e) {
            // Fallback to OpenWeatherMap on error
            $forecastData = $this->weatherService->getForecast($lat, $lon, $days);

            if (!$forecastData || !isset($forecastData['list'])) {
                return response()->json([
                    'message' => 'Unable to fetch forecast data: ' . $e->getMessage()
                ], 500);
            }

            $dailyForecasts = $this->processForecastToDaily($forecastData['list'], $days);
        }

        return response()->json([
            'field' => $field,
            'forecast' => $dailyForecasts
        ]);
    }

    /**
     * Get weather history for a field
     */
    public function getHistory(Request $request, Field $field): JsonResponse
    {
        // Check if user can access this field
        $user = $request->user();
        if ($field->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'days' => 'integer|min:1|max:1200',
            'page' => 'integer|min:1',
            'per_page' => 'integer|min:1|max:5000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $days = (int) $request->get('days', 30);
        $perPage = (int) $request->get('per_page', 5000);

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
        if ($field->user_id !== $user->id) {
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
        if ($field->user_id !== $user->id) {
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

        $fields = Field::where('user_id', $user->id)->get();
        $updated = 0;

        foreach ($fields as $field) {
            if ($this->weatherService->updateFieldWeather($field)) {
                $updated++;
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

        $fields = Field::where('user_id', $user->id)
            ->with('latestWeather')
            ->get();

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
        if ($field->user_id !== $user->id) {
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

        $fields = Field::where('user_id', $user->id)
            ->with(['latestWeather', 'plantings.riceVariety'])
            ->get();

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

    /**
     * Process forecast data from 3-hourly intervals to daily forecasts
     */
    private function processForecastToDaily(array $forecastList, int $days): array
    {
        $dailyForecasts = [];
        $today = new \DateTime('today', new \DateTimeZone('Asia/Manila'));
        $todayStr = $today->format('Y-m-d');

        foreach ($forecastList as $forecast) {
            if (!isset($forecast['dt']) || !isset($forecast['main']) || !isset($forecast['weather'][0])) {
                continue;
            }

            // Use Carbon with Asia/Manila timezone to ensure consistent date comparison
            $forecastDate = \Carbon\Carbon::createFromTimestamp($forecast['dt'])
                ->setTimezone('Asia/Manila')
                ->format('Y-m-d');

            // Skip past dates - only include today and future dates
            if ($forecastDate < $todayStr) {
                continue;
            }

            if (!isset($dailyForecasts[$forecastDate])) {
                $dailyForecasts[$forecastDate] = [
                    'date' => $forecastDate,
                    'time' => $forecastDate,
                    'high' => PHP_FLOAT_MIN,
                    'low' => PHP_FLOAT_MAX,
                    'temperature_max' => PHP_FLOAT_MIN,
                    'temperature_min' => PHP_FLOAT_MAX,
                    'max_temp' => PHP_FLOAT_MIN,
                    'min_temp' => PHP_FLOAT_MAX,
                    'condition' => '',
                    'weather' => '',
                    'description' => '',
                    'weather_description' => '',
                    'rain_chance' => 0,
                    'precipitation_probability' => 0,
                    'precipitation_chance' => 0,
                    'wind_speed' => 0,
                    'wind' => 0,
                    'weather_code' => 0,
                    'code' => 0,
                    'humidity' => 0,
                    'icon' => '🌤️',
                    'count' => 0 // Track number of forecasts for this day
                ];
            }

            // Keep temperature in Celsius (API returns metric/Celsius)
            $tempC = $forecast['main']['temp'] ?? 0;

            $dailyForecasts[$forecastDate]['high'] = max($dailyForecasts[$forecastDate]['high'], $tempC);
            $dailyForecasts[$forecastDate]['low'] = min($dailyForecasts[$forecastDate]['low'], $tempC);
            $dailyForecasts[$forecastDate]['temperature_max'] = max($dailyForecasts[$forecastDate]['temperature_max'], $tempC);
            $dailyForecasts[$forecastDate]['temperature_min'] = min($dailyForecasts[$forecastDate]['temperature_min'], $tempC);
            $dailyForecasts[$forecastDate]['max_temp'] = max($dailyForecasts[$forecastDate]['max_temp'], $tempC);
            $dailyForecasts[$forecastDate]['min_temp'] = min($dailyForecasts[$forecastDate]['min_temp'], $tempC);

            // Calculate average temperature
            $dailyForecasts[$forecastDate]['temperature'] = ($dailyForecasts[$forecastDate]['high'] + $dailyForecasts[$forecastDate]['low']) / 2;

            // Get most common condition (use the first one for now, could be improved)
            if (empty($dailyForecasts[$forecastDate]['condition'])) {
                $dailyForecasts[$forecastDate]['condition'] = $forecast['weather'][0]['main'] ?? 'Clear';
                $dailyForecasts[$forecastDate]['weather'] = $forecast['weather'][0]['main'] ?? 'Clear';
                $dailyForecasts[$forecastDate]['description'] = $forecast['weather'][0]['description'] ?? 'Clear skies';
                $dailyForecasts[$forecastDate]['weather_description'] = $forecast['weather'][0]['description'] ?? 'Clear skies';
            }

            // Get max precipitation probability
            $pop = $forecast['pop'] ?? 0;
            $dailyForecasts[$forecastDate]['rain_chance'] = max($dailyForecasts[$forecastDate]['rain_chance'], $pop * 100);
            $dailyForecasts[$forecastDate]['precipitation_probability'] = max($dailyForecasts[$forecastDate]['precipitation_probability'], $pop * 100);
            $dailyForecasts[$forecastDate]['precipitation_chance'] = max($dailyForecasts[$forecastDate]['precipitation_chance'], $pop * 100);

            // Keep wind speed in m/s (can convert to km/h if needed, but keep metric)
            $windSpeed = $forecast['wind']['speed'] ?? 0; // m/s
            $dailyForecasts[$forecastDate]['wind_speed'] = max($dailyForecasts[$forecastDate]['wind_speed'], $windSpeed);
            $dailyForecasts[$forecastDate]['wind'] = max($dailyForecasts[$forecastDate]['wind'], $windSpeed);

            // Weather code
            $dailyForecasts[$forecastDate]['weather_code'] = $forecast['weather'][0]['id'] ?? 0;
            $dailyForecasts[$forecastDate]['code'] = $forecast['weather'][0]['id'] ?? 0;

            // Average humidity
            $humidity = $forecast['main']['humidity'] ?? 0;
            if ($dailyForecasts[$forecastDate]['humidity'] === 0) {
                $dailyForecasts[$forecastDate]['humidity'] = $humidity;
            } else {
                $dailyForecasts[$forecastDate]['humidity'] = ($dailyForecasts[$forecastDate]['humidity'] + $humidity) / 2;
            }

            $dailyForecasts[$forecastDate]['count']++;
        }

        // Sort by date and get first N days (starting from today)
        ksort($dailyForecasts);
        $result = array_values($dailyForecasts);

        // Ensure we return exactly the requested number of days starting from today
        // Build result array ensuring we have exactly $days days starting from today
        $finalResult = [];
        $currentDate = clone $today;
        $resultIndex = 0;

        for ($i = 0; $i < $days; $i++) {
            $dateStr = $currentDate->format('Y-m-d');

            // Find matching forecast for this date from the processed results
            $found = false;
            for ($j = $resultIndex; $j < count($result); $j++) {
                if ($result[$j]['date'] === $dateStr) {
                    // Remove 'count' field before adding to result
                    unset($result[$j]['count']);
                    $finalResult[] = $result[$j];
                    $resultIndex = $j + 1; // Move index forward
                    $found = true;
                    break;
                }
            }

            // If no forecast found for this date, we've run out of API data
            // For a 7-day forecast, if API only provides 5-6 days, we'll return what we have
            // The frontend should handle displaying fewer days gracefully
            if (!$found) {
                // We've exhausted available forecast data
                // Return what we have rather than creating placeholder data
                break;
            }

            // Move to next day
            $currentDate->modify('+1 day');
        }

        // Return the days we have (should be at least what API provides, up to requested days)
        return $finalResult;
    }

    /**
     * Process ColorfulClouds forecast data to match frontend format
     */
    private function processColorfulCloudsForecast(array $forecastData, int $days): array
    {
        $dailyForecasts = [];
        $today = new \DateTime('today', new \DateTimeZone('Asia/Manila'));
        $todayStr = $today->format('Y-m-d');

        // Map skycon to weather code for icon display
        $skyconToCode = [
            'CLEAR_DAY' => 800,
            'CLEAR_NIGHT' => 800,
            'PARTLY_CLOUDY_DAY' => 801,
            'PARTLY_CLOUDY_NIGHT' => 801,
            'CLOUDY' => 803,
            'LIGHT_HAZE' => 721,
            'MODERATE_HAZE' => 721,
            'HEAVY_HAZE' => 721,
            'LIGHT_RAIN' => 500,
            'MODERATE_RAIN' => 501,
            'HEAVY_RAIN' => 502,
            'STORM_RAIN' => 211,
            'LIGHT_SNOW' => 600,
            'MODERATE_SNOW' => 601,
            'HEAVY_SNOW' => 602,
            'STORM_SNOW' => 622,
            'DUST' => 731,
            'SAND' => 731,
            'WIND' => 771,
            'FOG' => 741,
        ];

        // Map condition to OpenWeatherMap-style condition
        $conditionMap = [
            'clear' => 'Clear',
            'partly_cloudy' => 'Partly Cloudy',
            'cloudy' => 'Cloudy',
            'haze' => 'Haze',
            'rain' => 'Rain',
            'storm' => 'Thunderstorm',
            'snow' => 'Snow',
            'dust' => 'Dust',
            'windy' => 'Windy',
            'fog' => 'Fog',
        ];

        // Process all forecast entries
        foreach ($forecastData as $forecast) {
            if (!isset($forecast['date'])) {
                continue;
            }

            $forecastDate = $forecast['date'];

            // Normalize date format - handle different formats
            if (strpos($forecastDate, ' ') !== false) {
                // If it's a datetime string, extract just the date part
                $forecastDate = date('Y-m-d', strtotime($forecastDate));
            }

            // Ensure we're working with Asia/Manila dates
            if (is_numeric($forecastDate) || strtotime($forecastDate)) {
                $forecastDate = \Carbon\Carbon::parse($forecastDate)
                    ->setTimezone('Asia/Manila')
                    ->format('Y-m-d');
            }

            // Skip past dates - only include today and future dates
            if ($forecastDate < $todayStr) {
                continue;
            }

            // Skip if we already have this date
            if (isset($dailyForecasts[$forecastDate])) {
                continue;
            }

            // Get skycon from conditions if available, or use default
            $skycon = 'CLEAR_DAY';
            if (isset($forecast['conditions'])) {
                // Reverse map condition to skycon for code lookup
                $condition = strtolower($forecast['conditions']);
                $skyconMap = [
                    'clear' => 'CLEAR_DAY',
                    'partly_cloudy' => 'PARTLY_CLOUDY_DAY',
                    'cloudy' => 'CLOUDY',
                    'haze' => 'LIGHT_HAZE',
                    'rain' => 'LIGHT_RAIN',
                    'storm' => 'STORM_RAIN',
                    'snow' => 'LIGHT_SNOW',
                    'dust' => 'DUST',
                    'windy' => 'WIND',
                    'fog' => 'FOG',
                ];
                $skycon = $skyconMap[$condition] ?? 'CLEAR_DAY';
            }

            $weatherCode = $skyconToCode[$skycon] ?? 800;
            $condition = $conditionMap[strtolower($forecast['conditions'] ?? 'clear')] ?? 'Clear';

            // Calculate rain chance from precipitation (simplified - ColorfulClouds doesn't provide probability)
            // Use precipitation amount as indicator (0-1mm = 0%, 1-5mm = 30%, 5-10mm = 60%, >10mm = 90%)
            $precipitation = $forecast['rain'] ?? 0;
            $rainChance = 0;
            if ($precipitation > 0) {
                if ($precipitation < 1) {
                    $rainChance = 20;
                } elseif ($precipitation < 5) {
                    $rainChance = 50;
                } elseif ($precipitation < 10) {
                    $rainChance = 70;
                } else {
                    $rainChance = 90;
                }
            }

            // Wind speed is in km/h from ColorfulClouds (metric unit)
            // Convert to m/s for consistency with OpenWeatherMap format (frontend will convert)
            $windSpeedKmh = $forecast['wind_speed'] ?? 0;
            $windSpeedMs = $windSpeedKmh / 3.6; // Convert km/h to m/s

            $dailyForecasts[$forecastDate] = [
                'date' => $forecastDate,
                'time' => $forecastDate,
                'high' => $forecast['temperature_high'] ?? $forecast['temperature'] ?? 0,
                'low' => $forecast['temperature_low'] ?? $forecast['temperature'] ?? 0,
                'temperature_max' => $forecast['temperature_high'] ?? $forecast['temperature'] ?? 0,
                'temperature_min' => $forecast['temperature_low'] ?? $forecast['temperature'] ?? 0,
                'max_temp' => $forecast['temperature_high'] ?? $forecast['temperature'] ?? 0,
                'min_temp' => $forecast['temperature_low'] ?? $forecast['temperature'] ?? 0,
                'condition' => $condition,
                'weather' => $condition,
                'description' => $forecast['description'] ?? 'Clear skies',
                'weather_description' => $forecast['description'] ?? 'Clear skies',
                'rain_chance' => $rainChance,
                'precipitation_probability' => $rainChance,
                'precipitation_chance' => $rainChance,
                'wind_speed' => $windSpeedMs, // m/s for consistency
                'wind' => $windSpeedMs,
                'weather_code' => $weatherCode,
                'code' => $weatherCode,
                'humidity' => $forecast['humidity'] ?? 0,
                'temperature' => $forecast['temperature'] ?? (($dailyForecasts[$forecastDate]['high'] + $dailyForecasts[$forecastDate]['low']) / 2),
            ];

            // Sanitize values to avoid frontend NaN issues
            if ($dailyForecasts[$forecastDate]['high'] === null)
                $dailyForecasts[$forecastDate]['high'] = 0;
            if ($dailyForecasts[$forecastDate]['low'] === null)
                $dailyForecasts[$forecastDate]['low'] = 0;
            if ($dailyForecasts[$forecastDate]['rain_chance'] === null)
                $dailyForecasts[$forecastDate]['rain_chance'] = 0;
            if ($dailyForecasts[$forecastDate]['wind_speed'] === null)
                $dailyForecasts[$forecastDate]['wind_speed'] = 0;
        }

        // Sort by date
        ksort($dailyForecasts);
        $result = array_values($dailyForecasts);

        // Build final result ensuring exactly $days starting from today
        // ColorfulClouds might not include today, so we need to handle that
        $finalResult = [];
        $currentDate = clone $today;
        $hasToday = false;

        // Check if today is in the results
        foreach ($result as $forecast) {
            if ($forecast['date'] === $todayStr) {
                $hasToday = true;
                break;
            }
        }

        // If today is not in results and we have data, the API might start from tomorrow
        // In that case, we'll use the first available day as "today" or start from the first day
        $startIndex = 0;
        if (!$hasToday && count($result) > 0) {
            // Check if first result is tomorrow
            $firstDate = new \DateTime($result[0]['date']);
            $tomorrow = clone $today;
            $tomorrow->modify('+1 day');

            if ($firstDate->format('Y-m-d') === $tomorrow->format('Y-m-d')) {
                // API starts from tomorrow, so we have tomorrow + 6 more days = 7 days total
                // But we want today + 6 future days, so we need to add today
                // For now, let's use the first day as day 1 and continue
                $startIndex = 0;
            }
        }

        // Build result array starting from today
        // First, try to match dates exactly
        $usedIndices = [];
        for ($i = 0; $i < $days; $i++) {
            $dateStr = $currentDate->format('Y-m-d');
            $found = false;

            // Find matching forecast for this date (skip already used ones)
            for ($j = 0; $j < count($result); $j++) {
                if (in_array($j, $usedIndices)) {
                    continue;
                }

                if ($result[$j]['date'] === $dateStr) {
                    $finalResult[] = $result[$j];
                    $usedIndices[] = $j;
                    $found = true;
                    break;
                }
            }

            // If not found and this is today, try to use first available forecast as today
            if (!$found && $i === 0 && count($result) > 0 && !in_array(0, $usedIndices)) {
                // Use first forecast as today if API doesn't include today
                $firstForecast = $result[0];
                $firstForecast['date'] = $dateStr;
                $firstForecast['time'] = $dateStr;
                $finalResult[] = $firstForecast;
                $usedIndices[] = 0;
                $found = true;
            }

            // If still not found, try to use next available forecast
            if (!$found && count($result) > count($usedIndices)) {
                // Find next unused forecast
                for ($j = 0; $j < count($result); $j++) {
                    if (!in_array($j, $usedIndices)) {
                        $nextForecast = $result[$j];
                        $nextForecast['date'] = $dateStr;
                        $nextForecast['time'] = $dateStr;
                        $finalResult[] = $nextForecast;
                        $usedIndices[] = $j;
                        $found = true;
                        break;
                    }
                }
            }

            // If still not found, we've run out of data - use last available forecast
            if (!$found && count($result) > 0) {
                $lastForecast = end($result);
                $lastForecast['date'] = $dateStr;
                $lastForecast['time'] = $dateStr;
                $finalResult[] = $lastForecast;
            }

            // Move to next day
            $currentDate->modify('+1 day');
        }

        // Ensure we have exactly $days
        return array_slice($finalResult, 0, $days);
    }
}