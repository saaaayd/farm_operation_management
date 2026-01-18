<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Field;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Services\WeatherService;

class WeatherModuleTest extends TestCase
{
    use RefreshDatabase;

    protected $farmer;
    protected $field;

    protected function setUp(): void
    {
        parent::setUp();
        // Create Farmer
        $this->farmer = User::factory()->create(['role' => 'farmer']);

        $this->field = Field::factory()->create([
            'user_id' => $this->farmer->id,
            'name' => 'Test Field',
            'field_coordinates' => json_encode([['lat' => 10, 'lng' => 120]]),
            'size' => 1.5
        ]);
    }

    public function test_can_fetch_field_weather_dashboard()
    {
        // Mocking the WeatherService
        $this->mock(WeatherService::class, function ($mock) {
            $mock->shouldReceive('getCurrentWeather')
                ->andReturn([
                    'temp' => 30,
                    'condition' => 'Sunny',
                    'humidity' => 70
                ]);
            $mock->shouldReceive('getForecast')
                ->andReturn([]);
        });

        $response = $this->actingAs($this->farmer)
            ->getJson("/api/weather/fields/{$this->field->id}/current");

        $response->assertStatus(200)
            ->assertJsonPath('temp', 30);
    }

    public function test_rice_specific_weather_analytics()
    {
        // Mock essential methods for analytics
        $this->mock(WeatherService::class, function ($mock) {
            $mock->shouldReceive('getRiceWeatherAnalytics')
                ->andReturn(['suitability_score' => 85]);
            $mock->shouldReceive('getRiceFarmingRecommendations')
                ->andReturn(['Irrigate now']);
        });

        $response = $this->actingAs($this->farmer)
            ->getJson("/api/weather/fields/{$this->field->id}/rice-analytics");

        $response->assertStatus(200);
    }
}
