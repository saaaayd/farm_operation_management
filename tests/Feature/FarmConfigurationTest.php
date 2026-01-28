<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\LaborerGroup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_laborer_groups()
    {
        $farmer = User::factory()->create(['role' => 'farmer']);

        $response = $this->actingAs($farmer)
            ->postJson('/api/laborers/groups', [
                'name' => 'Harvest Team A',
                'description' => 'Best team'
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('laborer_groups', ['name' => 'Harvest Team A']);
    }

    public function test_rice_varieties_list()
    {
        $farmer = \App\Models\User::factory()->create(['role' => 'farmer']);
        $response = $this->actingAs($farmer)->getJson('/api/rice-varieties');
        // Likely public route
        $response->assertStatus(200);
    }
}
