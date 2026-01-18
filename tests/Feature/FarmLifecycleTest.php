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
        // Create Growth Stages
        $stage1 = \App\Models\RiceGrowthStage::create(['order_sequence' => 1, 'name' => 'Seedling', 'stage_code' => 'seedling', 'typical_duration_days' => 14]);
        $stage2 = \App\Models\RiceGrowthStage::create(['order_sequence' => 2, 'name' => 'Vegetative', 'stage_code' => 'vegetative', 'typical_duration_days' => 30]);

        $planting = Planting::create([
            'user_id' => $farmer->id,
            'field_id' => $field->id,
            'rice_variety_id' => \App\Models\RiceVariety::factory()->create()->id, // Required
            'planting_date' => now(),
            'status' => 'planted',
            'expected_harvest_date' => now()->addMonths(4), // Added required field
            'area_planted' => 1.5, // Added required field
            'season' => 'wet', // Added required field
            'quantity_planted' => 10
        ]);

        // Initialize and start first stage
        $planting->initializePlantingStages();
        $planting->plantingStages()->first()->markAsStarted();

        $response = $this->actingAs($farmer)
            ->postJson("/api/rice-farming/plantings/{$planting->id}/advance-stage", [
                'new_stage' => 'vegetative_stage',
                'date' => now()->toDateString()
            ]);

        $response->assertStatus(200);

        // Verify next stage is started
        $this->assertEquals('Vegetative', $planting->refresh()->getCurrentStage()->riceGrowthStage->name);
    }
}
