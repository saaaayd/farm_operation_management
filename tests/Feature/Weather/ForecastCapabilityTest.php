<?php

namespace Tests\Feature\Weather;

use App\Models\User;
use App\Models\Field;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Carbon\Carbon;

class ForecastCapabilityTest extends TestCase
{
    use DatabaseTransactions;

    protected $farmer;
    protected $field;

    protected function setUp(): void
    {
        parent::setUp();
        $this->farmer = User::factory()->create(['role' => 'farmer']);
        $this->field = Field::factory()->create([
            'user_id' => $this->farmer->id,
            'location' => ['lat' => 14.5995, 'lon' => 120.9842],
            'name' => 'Forecast Test Field'
        ]);

        // precise time for test stability
        Carbon::setTestNow(Carbon::create(2025, 1, 1, 12, 0, 0, 'Asia/Manila'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    /** @test */
    public function it_can_request_extended_forecast_up_to_10_days()
    {
        // Mock ColorfulClouds API response with 10 days of data
        $daysRequested = 10;

        // We need to mock the service or the HTTP client. 
        // Integrating directly with the service is better to test the full flow including the controller logic.

        $this->mock(\App\Services\ColorfulCloudsWeatherService::class, function ($mock) use ($daysRequested) {
            $mock->shouldReceive('getForecast')
                ->withArgs(function ($lat, $lon, $days) use ($daysRequested) {
                    // Controller requests days + 1
                    return $days >= $daysRequested;
                })
                ->andReturn($this->generateMockForecastData($daysRequested + 1));
        });

        // We also need to mock WeatherService as it might be used as fallback or dependency
        $this->mock(\App\Services\WeatherService::class);

        $response = $this->actingAs($this->farmer)
            ->getJson("/api/weather/fields/{$this->field->id}/forecast?days={$daysRequested}");

        if ($response->status() !== 200) {
            dump($response->json());
        }
        $response->assertStatus(200);

        $data = $response->json('forecast');

        // IF the controller limits it to 7, this assertion will fail
        $this->assertCount($daysRequested, $data, "Expected {$daysRequested} days of forecast, but got " . count($data));
    }

    private function generateMockForecastData($days)
    {
        $forecast = [];
        // Service returns array of daily forecasts

        for ($i = 0; $i < $days; $i++) {
            // Service returns dates as 'Y-m-d' strings
            $date = Carbon::now('Asia/Manila')->addDays($i)->format('Y-m-d');

            $forecast[] = [
                'date' => $date,
                'temperature' => 28,
                'temperature_high' => 32,
                'temperature_low' => 24,
                'humidity' => 80,
                'wind_speed' => 10,
                'wind_direction' => 90,
                'conditions' => 'clear',
                'description' => 'Clear day',
                'rain' => 0,
                'clouds' => 0,
            ];
        }

        return $forecast;
    }
}
