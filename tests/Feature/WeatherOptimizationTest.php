<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Field;
use App\Models\WeatherLog;
use App\Services\WeatherService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WeatherOptimizationTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $field;
    protected $weatherService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->field = Field::factory()->create([
            'user_id' => $this->user->id,
            'location' => [
                'lat' => 14.5995,
                'lon' => 120.9842
            ]
        ]);

        $this->weatherService = app(WeatherService::class);
    }

    /** @test */
    public function it_caches_weather_requests_in_service()
    {
        // Use real cache driver (array) to test integration
        Cache::flush();

        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 25.5,
                    'relative_humidity_2m' => 80,
                    'wind_speed_10m' => 10,
                    'precipitation' => 0,
                    'weather_code' => 1,
                    'time' => now()->toIso8601String()
                ]
            ], 200)
        ]);

        // First call - should hit API
        $this->weatherService->getCurrentWeather(14.60, 120.98);

        // Second call - should hit cache
        $this->weatherService->getCurrentWeather(14.60, 120.98);

        Http::assertSentCount(1);
    }

    /** @test */
    public function controller_prioritizes_recent_weather_log()
    {
        // specific time
        $now = now();

        // Create a recent weather log
        $log = WeatherLog::create([
            'field_id' => $this->field->id,
            'temperature' => 28.5, // Distinct value
            'humidity' => 75,
            'wind_speed' => 5.5,
            'conditions' => 'clear',
            'recorded_at' => $now->subMinutes(10) // 10 mins ago (fresh)
        ]);

        // Mock HTTP to ensure no external calls
        Http::fake();

        $response = $this->actingAs($this->user)
            ->getJson("/api/weather/fields/{$this->field->id}/current");

        $response->assertStatus(200);

        // Verify we got the data from the log
        $response->assertJsonPath('weather.temperature', 28.5);

        // Assert no HTTP calls were made
        Http::assertSentCount(0);
    }

    /** @test */
    public function it_deduplicates_requests_by_rounding_coordinates()
    {
        Cache::flush();

        Http::fake([
            'api.open-meteo.com/*' => Http::response([
                'current' => [
                    'temperature_2m' => 25.5,
                    'relative_humidity_2m' => 80,
                    'wind_speed_10m' => 10,
                    'precipitation' => 0,
                    'weather_code' => 1,
                    'time' => now()->toIso8601String()
                ]
            ], 200)
        ]);

        // Two coordinates that are very close (should round to same 14.60, 120.98)
        $lat1 = 14.5995;
        $lon1 = 120.9842;

        $lat2 = 14.6004; // Within rounding range
        $lon2 = 120.9841; // Within rounding range

        $this->weatherService->getCurrentWeather($lat1, $lon1);
        $this->weatherService->getCurrentWeather($lat2, $lon2);

        Http::assertSentCount(1);
    }
}
