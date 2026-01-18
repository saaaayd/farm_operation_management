<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Field;
use App\Models\Planting;
use App\Models\RiceVariety;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PestIncidentTest extends TestCase
{
    use RefreshDatabase;

    public function test_report_pest_incident()
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $field = Field::factory()->create(['user_id' => $farmer->id, 'field_coordinates' => [['lat' => 10, 'lng' => 120]], 'size' => 1.0]);

        $variety = RiceVariety::factory()->create();

        $planting = Planting::create([
            'user_id' => $farmer->id,
            'field_id' => $field->id,
            'rice_variety_id' => $variety->id, // Added required link
            'planting_date' => now(),
            'status' => 'planted',
            'quantity_planted' => 100,
            'expected_harvest_date' => now()->addMonths(4),
            'area_planted' => 1.5,
            'season' => 'wet' // Added required field
        ]);

        $data = [
            'planting_id' => $planting->id,
            'pest_type' => 'insect',
            'pest_name' => 'Stem Borer',
            'severity' => 'low',  // Changed from invalid 'minor'
            'detected_date' => now()->toDateString(),
            'notes' => 'Found multiple clusters'
        ];

        $response = $this->actingAs($farmer)
            ->postJson('/api/pest-incidents', $data);

        $response->assertStatus(201);
        $this->assertDatabaseHas('pest_incidents', ['pest_name' => 'Stem Borer']);
    }
}
