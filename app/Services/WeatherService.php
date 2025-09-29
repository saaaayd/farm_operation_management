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
     * Get weather alerts for farming conditions
     */
    public function getWeatherAlerts(Field $field): array
    {
        $alerts = [];
        $latestWeather = $field->latestWeather;

        if (!$latestWeather) {
            return $alerts;
        }

        // Temperature alerts
        if ($latestWeather->temperature < 5) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Frost warning: Temperature is below 5°C. Protect sensitive crops.',
                'category' => 'temperature'
            ];
        } elseif ($latestWeather->temperature > 35) {
            $alerts[] = [
                'type' => 'warning',
                'message' => 'Heat warning: Temperature is above 35°C. Ensure adequate watering.',
                'category' => 'temperature'
            ];
        }

        // Wind alerts
        if ($latestWeather->wind_speed > 20) {
            $alerts[] = [
                'type' => 'caution',
                'message' => 'Strong winds detected. Avoid spraying pesticides or fertilizers.',
                'category' => 'wind'
            ];
        }

        // Humidity alerts
        if ($latestWeather->humidity < 30) {
            $alerts[] = [
                'type' => 'info',
                'message' => 'Low humidity detected. Consider increasing irrigation.',
                'category' => 'humidity'
            ];
        } elseif ($latestWeather->humidity > 80) {
            $alerts[] = [
                'type' => 'caution',
                'message' => 'High humidity may increase disease risk. Monitor crops closely.',
                'category' => 'humidity'
            ];
        }

        // Weather condition alerts
        if (in_array($latestWeather->conditions, ['stormy', 'rainy'])) {
            $alerts[] = [
                'type' => 'info',
                'message' => 'Wet conditions detected. Delay outdoor activities if possible.',
                'category' => 'conditions'
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
     * Check if API key is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->baseUrl);
    }
}