<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Field;
use App\Services\WeatherService;
use Mockery\MockInterface;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WeatherTest extends TestCase
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
            'location' => ['lat' => 14.5995, 'lon' => 120.9842], // Manila
            'name' => 'Weather Test Field'
        ]);
    }

    public function test_can_get_current_weather()
    {
        // Mock ColorfulCloudsWeatherService (avoid real instantiation)
        $this->mock(\App\Services\ColorfulCloudsWeatherService::class, function (MockInterface $mock) {
            // No strict expectations, just don't crash
        });

        // Mock WeatherService
        $this->mock(WeatherService::class, function (MockInterface $mock) {
            $mock->shouldReceive('getCurrentWeather')
                ->once()
                ->with(14.5995, 120.9842)
                ->andReturn([
                    'main' => ['temp' => 30, 'humidity' => 70],
                    'weather' => [['main' => 'Clear', 'description' => 'sunny']],
                    'wind' => ['speed' => 5],
                    'dt' => time()
                ]);

            $mock->shouldReceive('updateFieldWeather')
                ->once()
                ->andReturn(new \App\Models\WeatherLog()); // Partial mock return

            $mock->shouldReceive('formatWeatherLog')
                ->andReturn(['temp' => 30, 'condition' => 'Clear']);

            $mock->shouldReceive('getWeatherAlerts')
                ->andReturn([]);
        });

        $response = $this->actingAs($this->farmer)
            ->getJson("/api/weather/fields/{$this->field->id}/current");

        $response->assertStatus(200)
            ->assertJsonPath('weather.temp', 30);
    }
}
