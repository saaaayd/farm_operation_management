<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ColorfulCloudsWeatherService
{
    private $apiToken;
    private $baseUrl = 'https://api.caiyunapp.com/v2.5';

    public function __construct()
    {
        $this->apiToken = config('services.colorfulclouds.api_token', 'S45Fnpxcwyq0QT4b');
    }

    /**
     * Get current weather for coordinates
     * API format: https://api.caiyunapp.com/v2.5/{token}/{longitude},{latitude}/weather.json
     */
    public function getCurrentWeather($latitude, $longitude, $unit = 'imperial', $lang = 'en_US')
    {
        try {
            $cacheKey = "colorfulclouds_weather_{$latitude}_{$longitude}_{$unit}";
            
            return Cache::remember($cacheKey, 600, function () use ($latitude, $longitude, $unit, $lang) {
                $url = "{$this->baseUrl}/{$this->apiToken}/{$longitude},{$latitude}/weather.json";
                
                $response = Http::get($url, [
                    'lang' => $lang,
                    'unit' => $unit,
                    'granu' => 'realtime'
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    return $this->formatWeatherData($data, $unit);
                }

                Log::error('ColorfulClouds API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                throw new \Exception('Failed to fetch weather data from ColorfulClouds');
            });
        } catch (\Exception $e) {
            Log::error('ColorfulClouds Weather API Error: ' . $e->getMessage());
            return $this->getDefaultWeatherData($unit);
        }
    }

    /**
     * Get weather forecast
     */
    public function getForecast($latitude, $longitude, $days = 7, $unit = 'imperial', $lang = 'en_US')
    {
        try {
            $cacheKey = "colorfulclouds_forecast_{$latitude}_{$longitude}_{$days}_{$unit}";
            
            return Cache::remember($cacheKey, 1800, function () use ($latitude, $longitude, $days, $unit, $lang) {
                $url = "{$this->baseUrl}/{$this->apiToken}/{$longitude},{$latitude}/weather.json";
                
                $granu = $days <= 1 ? 'hourly' : 'daily';
                $params = [
                    'lang' => $lang,
                    'unit' => $unit,
                    'granu' => $granu,
                ];
                
                if ($granu === 'hourly') {
                    $params['hourlysteps'] = min($days * 24, 240);
                } else {
                    $params['dailysteps'] = min($days, 10);
                }
                
                $response = Http::get($url, $params);

                if ($response->successful()) {
                    $data = $response->json();
                    return $this->formatForecastData($data, $unit);
                }

                Log::error('ColorfulClouds Forecast API Error', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                
                throw new \Exception('Failed to fetch forecast data from ColorfulClouds');
            });
        } catch (\Exception $e) {
            Log::error('ColorfulClouds Forecast API Error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Format ColorfulClouds weather data for our application
     */
    private function formatWeatherData($data, $unit = 'imperial')
    {
        if (!isset($data['result']) || !isset($data['result']['realtime'])) {
            Log::error('Invalid ColorfulClouds weather data structure', ['data' => $data]);
            return $this->getDefaultWeatherData($unit);
        }

        $realtime = $data['result']['realtime'];
        $temperature = $realtime['temperature'] ?? 0;
        $humidity = $realtime['humidity'] ?? 0;
        $windSpeed = $realtime['wind']['speed'] ?? 0;
        $windDirection = $realtime['wind']['direction'] ?? 0;
        $pressure = $realtime['pressure'] ?? 0;
        $visibility = $realtime['visibility'] ?? 0;
        $cloudrate = ($realtime['cloudrate'] ?? 0) * 100; // Convert 0-1 to percentage
        $skycon = $realtime['skycon'] ?? 'UNKNOWN';
        $precipitation = $realtime['precipitation']['local']['intensity'] ?? 0;

        // Convert temperature if needed (ColorfulClouds returns in requested unit)
        // Wind speed is in km/h for metric, mph for imperial
        // Pressure is in Pa, convert to hPa
        $pressureHpa = $pressure / 100;

        return [
            'temperature' => round($temperature, 1),
            'humidity' => round($humidity * 100, 1), // Convert 0-1 to percentage
            'pressure' => round($pressureHpa, 1),
            'wind_speed' => round($windSpeed, 1),
            'wind_direction' => round($windDirection, 0),
            'conditions' => $this->mapSkyconToCondition($skycon),
            'description' => $this->getSkyconDescription($skycon),
            'visibility' => round($visibility, 1),
            'rain' => [
                'intensity' => $precipitation,
                'nearest' => $realtime['precipitation']['nearest']['distance'] ?? null,
            ],
            'clouds' => round($cloudrate, 0),
            'skycon' => $skycon,
            'comfort' => $realtime['life_index']['comfort']['desc'] ?? null,
            'ultraviolet' => $realtime['life_index']['ultraviolet']['desc'] ?? null,
            'recorded_at' => now()->toISOString(),
        ];
    }

    /**
     * Format forecast data
     */
    private function formatForecastData($data, $unit = 'imperial')
    {
        $forecast = [];
        
        if (!isset($data['result'])) {
            Log::error('Invalid ColorfulClouds forecast data structure', ['data' => $data]);
            return [];
        }

        $result = $data['result'];

        // Handle hourly forecast
        if (isset($result['hourly'])) {
            $hourly = $result['hourly'];
            $temperatures = $hourly['temperature'] ?? [];
            $skycons = $hourly['skycon'] ?? [];
            $humidity = $hourly['humidity'] ?? [];
            $wind = $hourly['wind'] ?? [];
            $precipitation = $hourly['precipitation'] ?? [];

            foreach ($temperatures as $index => $temp) {
                if (isset($temp['datetime'])) {
                    $forecast[] = [
                        'date' => date('Y-m-d H:i:s', $temp['datetime']),
                        'temperature' => round($temp['value'] ?? 0, 1),
                        'humidity' => isset($humidity[$index]) ? round($humidity[$index]['value'] * 100, 1) : 0,
                        'wind_speed' => isset($wind[$index]) ? round($wind[$index]['speed'] ?? 0, 1) : 0,
                        'wind_direction' => isset($wind[$index]) ? round($wind[$index]['direction'] ?? 0, 0) : 0,
                        'conditions' => $this->mapSkyconToCondition($skycons[$index]['value'] ?? 'UNKNOWN'),
                        'description' => $this->getSkyconDescription($skycons[$index]['value'] ?? 'UNKNOWN'),
                        'rain' => isset($precipitation[$index]) ? ($precipitation[$index]['value'] ?? 0) : 0,
                        'clouds' => 0,
                    ];
                }
            }
        }

        // Handle daily forecast
        if (isset($result['daily'])) {
            $daily = $result['daily'];
            $temperatures = $daily['temperature'] ?? [];
            $skycons = $daily['skycon'] ?? [];
            $humidity = $daily['humidity'] ?? [];
            $wind = $daily['wind'] ?? [];
            $precipitation = $daily['precipitation'] ?? [];

            foreach ($temperatures as $index => $temp) {
                if (isset($temp['date'])) {
                    $forecast[] = [
                        'date' => date('Y-m-d', strtotime($temp['date'])),
                        'temperature' => round(($temp['max'] ?? 0 + $temp['min'] ?? 0) / 2, 1),
                        'temperature_high' => round($temp['max'] ?? 0, 1),
                        'temperature_low' => round($temp['min'] ?? 0, 1),
                        'humidity' => isset($humidity[$index]) ? round($humidity[$index]['avg'] * 100 ?? 0, 1) : 0,
                        'wind_speed' => isset($wind[$index]) ? round($wind[$index]['max']['speed'] ?? 0, 1) : 0,
                        'wind_direction' => isset($wind[$index]) ? round($wind[$index]['avg']['direction'] ?? 0, 0) : 0,
                        'conditions' => $this->mapSkyconToCondition($skycons[$index]['value'] ?? 'UNKNOWN'),
                        'description' => $this->getSkyconDescription($skycons[$index]['value'] ?? 'UNKNOWN'),
                        'rain' => isset($precipitation[$index]) ? ($precipitation[$index]['max'] ?? 0) : 0,
                        'clouds' => 0,
                    ];
                }
            }
        }

        return $forecast;
    }

    /**
     * Map ColorfulClouds skycon to our condition format
     */
    private function mapSkyconToCondition($skycon)
    {
        $mapping = [
            'CLEAR_DAY' => 'clear',
            'CLEAR_NIGHT' => 'clear',
            'PARTLY_CLOUDY_DAY' => 'partly_cloudy',
            'PARTLY_CLOUDY_NIGHT' => 'partly_cloudy',
            'CLOUDY' => 'cloudy',
            'LIGHT_HAZE' => 'haze',
            'MODERATE_HAZE' => 'haze',
            'HEAVY_HAZE' => 'haze',
            'LIGHT_RAIN' => 'rain',
            'MODERATE_RAIN' => 'rain',
            'HEAVY_RAIN' => 'rain',
            'STORM_RAIN' => 'storm',
            'LIGHT_SNOW' => 'snow',
            'MODERATE_SNOW' => 'snow',
            'HEAVY_SNOW' => 'snow',
            'STORM_SNOW' => 'snow',
            'DUST' => 'dust',
            'SAND' => 'dust',
            'WIND' => 'windy',
            'FOG' => 'fog',
        ];

        return $mapping[$skycon] ?? 'unknown';
    }

    /**
     * Get human-readable description for skycon
     */
    private function getSkyconDescription($skycon)
    {
        $descriptions = [
            'CLEAR_DAY' => 'Clear day',
            'CLEAR_NIGHT' => 'Clear night',
            'PARTLY_CLOUDY_DAY' => 'Partly cloudy',
            'PARTLY_CLOUDY_NIGHT' => 'Partly cloudy',
            'CLOUDY' => 'Cloudy',
            'LIGHT_HAZE' => 'Light haze',
            'MODERATE_HAZE' => 'Moderate haze',
            'HEAVY_HAZE' => 'Heavy haze',
            'LIGHT_RAIN' => 'Light rain',
            'MODERATE_RAIN' => 'Moderate rain',
            'HEAVY_RAIN' => 'Heavy rain',
            'STORM_RAIN' => 'Storm',
            'LIGHT_SNOW' => 'Light snow',
            'MODERATE_SNOW' => 'Moderate snow',
            'HEAVY_SNOW' => 'Heavy snow',
            'STORM_SNOW' => 'Snow storm',
            'DUST' => 'Dust',
            'SAND' => 'Sand',
            'WIND' => 'Windy',
            'FOG' => 'Foggy',
        ];

        return $descriptions[$skycon] ?? 'Unknown';
    }

    /**
     * Get default weather data when API fails
     */
    private function getDefaultWeatherData($unit = 'imperial')
    {
        $temp = $unit === 'imperial' ? 72 : 22;
        $windUnit = $unit === 'imperial' ? 10 : 16; // mph or km/h

        return [
            'temperature' => $temp,
            'humidity' => 60,
            'pressure' => 1013,
            'wind_speed' => $windUnit,
            'wind_direction' => 180,
            'conditions' => 'clear',
            'description' => 'Clear sky',
            'visibility' => 10,
            'rain' => [],
            'clouds' => 20,
            'recorded_at' => now()->toISOString(),
        ];
    }
}




