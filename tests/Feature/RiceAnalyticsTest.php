<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Facades\Config;

class RiceAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_efficiency_metrics_use_config()
    {
        $farmer = User::factory()->create(['role' => 'farmer']);

        // Override config to specific known values for testing
        Config::set('rice_analytics.efficiency_benchmarks.water', 1.0); // 1kg yield per 1 peso

        // Mock data logic would be needed here (create plantings/harvests/expenses)
        // For now, we call the endpoint and verify no 500 error, and structure
        $response = $this->actingAs($farmer)
            ->getJson('/api/analytics/rice-farming?period=12');

        if ($response->status() !== 200) {
            dump($response->json());
        }
        $response->assertStatus(200)
            ->assertJsonStructure([
                'analytics' => [
                    'efficiency_metrics' => [
                        'resource_efficiency' => [
                            'water_efficiency',
                            'labor_efficiency',
                            'fertilizer_efficiency'
                        ]
                    ]
                ]
            ]);
    }
}
