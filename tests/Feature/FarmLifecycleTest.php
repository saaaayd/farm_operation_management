<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Planting;
use App\Models\Field;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FarmLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_advance_stage()
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $field = Field::factory()->create(['user_id' => $farmer->id]);
        $planting = Planting::create([
            'user_id' => $farmer->id,
            'field_id' => $field->id,
            'variety' => 'RC160',
            'planting_date' => now(),
            'status' => 'seedling_stage', // Initial stage
            'quantity_planted' => 10
        ]);

        $response = $this->actingAs($farmer)
            ->postJson("/api/rice-farming/plantings/{$planting->id}/advance-stage", [
                'new_stage' => 'vegetative_stage',
                'date' => now()->toDateString()
            ]);

        $response->assertStatus(200);

        $this->assertEquals('vegetative_stage', $planting->refresh()->status);
    }
}
