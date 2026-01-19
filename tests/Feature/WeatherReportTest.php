<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Field;
use App\Models\WeatherLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeatherReportTest extends TestCase
{
    use RefreshDatabase;

    protected $farmer;
    protected $field;

    protected function setUp(): void
    {
        parent::setUp();

        // Create farmer
        $this->farmer = User::factory()->create(['role' => 'farmer']);

        // Create field with location coordinates
        $this->field = Field::factory()->create([
            'user_id' => $this->farmer->id,
            'name' => 'Weather Test Field',
            'location' => ['lat' => 14.5995, 'lon' => 120.9842],
        ]);
    }

    public function test_weather_log_stores_rainfall_data()
    {
        // Create weather log with rainfall
        $weatherLog = WeatherLog::create([
            'field_id' => $this->field->id,
            'temperature' => 28.5,
            'humidity' => 75,
            'wind_speed' => 12.5,
            'rainfall' => 5.25,
            'conditions' => 'rainy',
            'recorded_at' => now(),
        ]);

        $this->assertDatabaseHas('weather_logs', [
            'id' => $weatherLog->id,
            'field_id' => $this->field->id,
            'temperature' => 28.5,
            'rainfall' => 5.25,
        ]);

        // Verify rainfall is accessible through model
        $retrieved = WeatherLog::find($weatherLog->id);
        $this->assertEquals(5.25, (float) $retrieved->rainfall);
    }

    public function test_weather_report_returns_accurate_rainfall_data()
    {
        // Create multiple weather logs with different rainfall values
        $logs = [
            ['rainfall' => 2.5, 'temperature' => 28, 'humidity' => 70, 'conditions' => 'rainy'],
            ['rainfall' => 0.0, 'temperature' => 32, 'humidity' => 60, 'conditions' => 'clear'],
            ['rainfall' => 7.8, 'temperature' => 26, 'humidity' => 85, 'conditions' => 'stormy'],
            ['rainfall' => 1.2, 'temperature' => 29, 'humidity' => 65, 'conditions' => 'cloudy'],
        ];

        foreach ($logs as $i => $log) {
            WeatherLog::create([
                'field_id' => $this->field->id,
                'temperature' => $log['temperature'],
                'humidity' => $log['humidity'],
                'wind_speed' => 10,
                'rainfall' => $log['rainfall'],
                'conditions' => $log['conditions'],
                'recorded_at' => now()->subDays($i),
            ]);
        }

        // Test the weather report endpoint
        $response = $this->actingAs($this->farmer)
            ->getJson('/api/reports/weather?period=30');

        $response->assertStatus(200);

        // Verify total rainfall is the sum of all rainfall values
        $expectedTotalRainfall = 2.5 + 0.0 + 7.8 + 1.2; // = 11.5
        $data = $response->json('data');

        $this->assertArrayHasKey('weather_summary', $data);
        $this->assertEquals(round($expectedTotalRainfall, 1), $data['weather_summary']['total_rainfall']);
    }

    public function test_weather_report_returns_gdd_data()
    {
        // Create weather logs for GDD calculation
        for ($i = 0; $i < 7; $i++) {
            WeatherLog::create([
                'field_id' => $this->field->id,
                'temperature' => 25 + ($i % 5), // Values between 25-29
                'humidity' => 70,
                'wind_speed' => 10,
                'rainfall' => 0,
                'conditions' => 'clear',
                'recorded_at' => now()->subDays($i),
            ]);
        }

        $response = $this->actingAs($this->farmer)
            ->getJson('/api/reports/weather?period=30');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertArrayHasKey('gdd_data', $data);
        $this->assertArrayHasKey('today', $data['gdd_data']);
        $this->assertArrayHasKey('week', $data['gdd_data']);
        $this->assertArrayHasKey('month', $data['gdd_data']);
    }

    public function test_weather_report_field_filter_works()
    {
        // Create second field
        $field2 = Field::factory()->create([
            'user_id' => $this->farmer->id,
            'name' => 'Second Field',
        ]);

        // Create weather logs for both fields
        WeatherLog::create([
            'field_id' => $this->field->id,
            'temperature' => 30,
            'humidity' => 70,
            'wind_speed' => 10,
            'rainfall' => 5.0,
            'conditions' => 'rainy',
            'recorded_at' => now(),
        ]);

        WeatherLog::create([
            'field_id' => $field2->id,
            'temperature' => 28,
            'humidity' => 65,
            'wind_speed' => 8,
            'rainfall' => 3.0,
            'conditions' => 'cloudy',
            'recorded_at' => now(),
        ]);

        // Test without field filter (all fields)
        $allFieldsResponse = $this->actingAs($this->farmer)
            ->getJson('/api/reports/weather?period=30');

        $allFieldsResponse->assertStatus(200);
        $allFieldsData = $allFieldsResponse->json('data');
        $this->assertEquals(8.0, $allFieldsData['weather_summary']['total_rainfall']); // 5 + 3

        // Test with specific field filter
        $singleFieldResponse = $this->actingAs($this->farmer)
            ->getJson("/api/reports/weather?period=30&field_id={$this->field->id}");

        $singleFieldResponse->assertStatus(200);
        $singleFieldData = $singleFieldResponse->json('data');
        $this->assertEquals(5.0, $singleFieldData['weather_summary']['total_rainfall']); // Only field 1
    }

    public function test_weather_report_includes_weather_events()
    {
        // Create stormy weather to trigger event detection
        WeatherLog::create([
            'field_id' => $this->field->id,
            'temperature' => 25,
            'humidity' => 90,
            'wind_speed' => 25, // High wind
            'rainfall' => 15.0,
            'conditions' => 'stormy',
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($this->farmer)
            ->getJson('/api/reports/weather?period=30');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertArrayHasKey('weather_events', $data);
    }

    public function test_weather_report_includes_temperature_trends()
    {
        // Create weather logs over several days
        for ($i = 0; $i < 5; $i++) {
            WeatherLog::create([
                'field_id' => $this->field->id,
                'temperature' => 25 + $i,
                'humidity' => 70,
                'wind_speed' => 10,
                'rainfall' => 0,
                'conditions' => 'clear',
                'recorded_at' => now()->subDays($i),
            ]);
        }

        $response = $this->actingAs($this->farmer)
            ->getJson('/api/reports/weather?period=30');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertArrayHasKey('temperature_trends', $data);
        $this->assertIsArray($data['temperature_trends']);
        $this->assertCount(5, $data['temperature_trends']); // 5 days of data
    }

    public function test_weather_report_includes_rainfall_distribution()
    {
        // Create weather logs with varying rainfall
        for ($i = 0; $i < 5; $i++) {
            WeatherLog::create([
                'field_id' => $this->field->id,
                'temperature' => 28,
                'humidity' => 70,
                'wind_speed' => 10,
                'rainfall' => $i * 2.5, // 0, 2.5, 5, 7.5, 10
                'conditions' => $i % 2 == 0 ? 'clear' : 'rainy',
                'recorded_at' => now()->subDays($i),
            ]);
        }

        $response = $this->actingAs($this->farmer)
            ->getJson('/api/reports/weather?period=30');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertArrayHasKey('rainfall_distribution', $data);
        $this->assertIsArray($data['rainfall_distribution']);
    }
}
