<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class OpenWeatherAPIService
{
    private $apiKey;
    private $baseUrl = 'https://api.openweathermap.org/data/2.5';
    private $geoUrl = 'https://api.openweathermap.org/geo/1.0';

    public function __construct()
    {
        $this->apiKey = config('services.openweather.api_key');
    }

    /**
     * Get current weather for coordinates
     */
    public function getCurrentWeather($latitude, $longitude)
    {
        try {
            $cacheKey = "weather_current_{$latitude}_{$longitude}";
            
            return Cache::remember($cacheKey, 600, function () use ($latitude, $longitude) {
                $response = Http::get("{$this->baseUrl}/weather", [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'appid' => $this->apiKey,
                    'units' => 'metric'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $this->formatWeatherData($data);
                }

                throw new \Exception('Failed to fetch weather data');
            });
        } catch (\Exception $e) {
            Log::error('OpenWeather API Error: ' . $e->getMessage());
            return $this->getDefaultWeatherData();
        }
    }

    /**
     * Get 5-day weather forecast
     */
    public function getForecast($latitude, $longitude)
    {
        try {
            $cacheKey = "weather_forecast_{$latitude}_{$longitude}";
            
            return Cache::remember($cacheKey, 1800, function () use ($latitude, $longitude) {
                $response = Http::get("{$this->baseUrl}/forecast", [
                    'lat' => $latitude,
                    'lon' => $longitude,
                    'appid' => $this->apiKey,
                    'units' => 'metric'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $this->formatForecastData($data);
                }

                throw new \Exception('Failed to fetch forecast data');
            });
        } catch (\Exception $e) {
            Log::error('OpenWeather Forecast API Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get coordinates from city name
     */
    public function getCoordinates($city, $country = null)
    {
        try {
            $params = [
                'q' => $city . ($country ? ",{$country}" : ''),
                'appid' => $this->apiKey,
                'limit' => 1
            ];

            $response = Http::get("{$this->geoUrl}/direct", $params);

            if ($response->successful()) {
                $data = $response->json();
                if (!empty($data)) {
                    return [
                        'latitude' => $data[0]['lat'],
                        'longitude' => $data[0]['lon'],
                        'name' => $data[0]['name'],
                        'country' => $data[0]['country']
                    ];
                }
            }

            return null;
        } catch (\Exception $e) {
            Log::error('OpenWeather Geocoding API Error: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check for weather alerts based on rice farming conditions
     */
    public function checkWeatherAlerts($weatherData)
    {
        $alerts = [];

        // Heavy rain alert
        if (isset($weatherData['rain']['1h']) && $weatherData['rain']['1h'] > 20) {
            $alerts[] = [
                'type' => 'heavy_rain',
                'severity' => 'high',
                'message' => "Heavy rainfall detected: {$weatherData['rain']['1h']}mm in the last hour. Consider postponing outdoor activities.",
                'data' => $weatherData['rain']
            ];
        }

        // Extreme temperature alerts
        $temp = $weatherData['main']['temp'];
        if ($temp > 35) {
            $alerts[] = [
                'type' => 'extreme_temperature',
                'severity' => 'critical',
                'message' => "Extreme heat warning: {$temp}°C. Ensure adequate irrigation and crop protection.",
                'data' => ['temperature' => $temp]
            ];
        } elseif ($temp < 5) {
            $alerts[] = [
                'type' => 'extreme_temperature',
                'severity' => 'critical',
                'message' => "Freezing temperature warning: {$temp}°C. Protect sensitive crops from frost damage.",
                'data' => ['temperature' => $temp]
            ];
        }

        // High wind alert
        if (isset($weatherData['wind']['speed']) && $weatherData['wind']['speed'] > 20) {
            $alerts[] = [
                'type' => 'typhoon',
                'severity' => 'high',
                'message' => "Strong winds detected: {$weatherData['wind']['speed']} km/h. Secure farm equipment and protect crops.",
                'data' => $weatherData['wind']
            ];
        }

        // Low humidity alert
        if (isset($weatherData['main']['humidity']) && $weatherData['main']['humidity'] < 30) {
            $alerts[] = [
                'type' => 'drought',
                'severity' => 'medium',
                'message' => "Low humidity detected: {$weatherData['main']['humidity']}%. Consider irrigation to prevent crop stress.",
                'data' => ['humidity' => $weatherData['main']['humidity']]
            ];
        }

        return $alerts;
    }

    /**
     * Format weather data for our application
     */
    private function formatWeatherData($data)
    {
        return [
            'temperature' => round($data['main']['temp'], 1),
            'humidity' => $data['main']['humidity'],
            'pressure' => $data['main']['pressure'],
            'wind_speed' => round($data['wind']['speed'] * 3.6, 1), // Convert m/s to km/h
            'wind_direction' => $data['wind']['deg'] ?? 0,
            'conditions' => strtolower($data['weather'][0]['main']),
            'description' => $data['weather'][0]['description'],
            'visibility' => $data['visibility'] ?? 0,
            'rain' => $data['rain'] ?? [],
            'clouds' => $data['clouds']['all'] ?? 0,
            'sunrise' => date('H:i', $data['sys']['sunrise']),
            'sunset' => date('H:i', $data['sys']['sunset']),
            'recorded_at' => now()->toISOString(),
        ];
    }

    /**
     * Format forecast data for our application
     */
    private function formatForecastData($data)
    {
        $forecast = [];
        
        foreach ($data['list'] as $item) {
            $forecast[] = [
                'date' => date('Y-m-d H:i:s', $item['dt']),
                'temperature' => round($item['main']['temp'], 1),
                'humidity' => $item['main']['humidity'],
                'wind_speed' => round($item['wind']['speed'] * 3.6, 1),
                'conditions' => strtolower($item['weather'][0]['main']),
                'description' => $item['weather'][0]['description'],
                'rain' => $item['rain']['3h'] ?? 0,
                'clouds' => $item['clouds']['all'] ?? 0,
            ];
        }

        return $forecast;
    }

    /**
     * Get default weather data when API fails
     */
    private function getDefaultWeatherData()
    {
        return [
            'temperature' => 25.0,
            'humidity' => 60,
            'pressure' => 1013,
            'wind_speed' => 10.0,
            'wind_direction' => 180,
            'conditions' => 'clear',
            'description' => 'Clear sky',
            'visibility' => 10000,
            'rain' => [],
            'clouds' => 20,
            'sunrise' => '06:00',
            'sunset' => '18:00',
            'recorded_at' => now()->toISOString(),
        ];
    }
}
