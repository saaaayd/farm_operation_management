<?php

namespace App\Services;

use App\Models\Field;
use Illuminate\Support\Facades\Log;

class PestPredictionService
{
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * Predict pest and disease risks for a field based on weather forecast
     *
     * @param Field $field
     * @return array
     */
    public function predictRisks(Field $field): array
    {
        // Get field coordinates
        $lat = $field->location['lat'] ?? $field->field_coordinates['lat'] ?? null;
        $lon = $field->location['lon'] ?? $field->field_coordinates['lon'] ?? null;

        if (!$lat || !$lon) {
            return [];
        }

        // Get 7-day forecast
        $forecast = $this->weatherService->getForecast((float) $lat, (float) $lon, 7);

        if (!$forecast || empty($forecast['list'])) {
            return [];
        }

        $predictions = [];
        $today = now();

        foreach ($forecast['list'] as $day) {
            $risks = $this->analyzeDailyRisk($day);

            if (!empty($risks)) {
                $predictions[] = [
                    'date' => date('Y-m-d', $day['dt']),
                    'day_name' => date('l', $day['dt']),
                    'weather_summary' => [
                        'temp' => round($day['main']['temp'], 1),
                        'humidity' => $day['main']['humidity'],
                        'condition' => $day['weather'][0]['main'] ?? 'Clear'
                    ],
                    'risks' => $risks
                ];
            }
        }

        return $predictions;
    }

    /**
     * Analyze weather conditions for a single day to identify specific risks
     */
    private function analyzeDailyRisk(array $day): array
    {
        $risks = [];
        $temp = $day['main']['temp'];
        $humidity = $day['main']['humidity'];
        $condition = strtolower($day['weather'][0]['main'] ?? '');
        $rainProb = $day['pop'] ?? 0; // Probability of precipitation (0-1)

        // 1. Rice Blast (Fungal)
        // Conditions: High humidity (>90%), frequent rain, temps 20-30°C
        if ($humidity >= 85 && $temp >= 20 && $temp <= 30 && ($condition === 'rainy' || $rainProb > 0.5)) {
            $risks[] = [
                'pest_name' => 'Rice Blast',
                'type' => 'Disease',
                'risk_level' => 'High',
                'description' => 'High humidity and rainfall favor Rice Blast development.',
                'recommendation' => 'Monitor for leaf lesions. Avoid excessive nitrogen application.'
            ];
        }

        // 2. Stem Borer (Insect)
        // Conditions: Warm temperatures (>28°C)
        if ($temp > 28) {
            $risks[] = [
                'pest_name' => 'Stem Borer',
                'type' => 'Insect',
                'risk_level' => 'Moderate',
                'description' => 'Warm temperatures may accelerate Stem Borer larval development.',
                'recommendation' => 'Check for "dead hearts" or "whiteheads".'
            ];
        }

        // 3. Brown Plant Hopper (Insect)
        // Conditions: High humidity and warm temperatures
        if ($humidity > 80 && $temp > 25) {
            $risks[] = [
                'pest_name' => 'Brown Plant Hopper',
                'type' => 'Insect',
                'risk_level' => $humidity > 90 ? 'High' : 'Moderate',
                'description' => 'Humid and warm conditions support hopper population growth.',
                'recommendation' => 'Inspect base of tillers. Drain field if infestation is high.'
            ];
        }

        // 4. Bacterial Leaf Blight (Disease)
        // Conditions: Strong winds and rain causing leaf wounds
        // Note: Using a proxy for wind since forecast mock might not have it, but assuming storm/rain implies some wind
        if (in_array($condition, ['stormy', 'rainy']) && $temp > 25) {
            $risks[] = [
                'pest_name' => 'Bacterial Leaf Blight',
                'type' => 'Disease',
                'risk_level' => 'Moderate',
                'description' => 'Rain and potential wind can spread bacterial blight.',
                'recommendation' => 'Avoid field operations when wet to prevent spreading.'
            ];
        }

        return $risks;
    }
}
