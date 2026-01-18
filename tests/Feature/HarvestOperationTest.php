<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Field;
use App\Models\Planting;
use App\Models\Harvest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HarvestOperationTest extends TestCase
{
    use RefreshDatabase;

    protected $farmer;
    protected $planting;

    protected function setUp(): void
    {
        parent::setUp();
        $this->farmer = User::factory()->create(['role' => 'farmer']);

        $field = Field::factory()->create(['user_id' => $this->farmer->id]);
        // Create planting
        $this->planting = Planting::create([
            'user_id' => $this->farmer->id,
            'field_id' => $field->id,
            'variety' => 'RC160',
            'planting_date' => now()->subMonths(4),
            'status' => 'harvesting',
            'method' => 'manual',
            'quantity_planted' => 10
        ]);
    }

    public function test_can_record_harvest()
    {
        $data = [
            'planting_id' => $this->planting->id,
            'harvest_date' => now()->toDateString(),
            'yield_quantity' => 5000, // kg
            'notes' => 'Good harvest',
            'create_inventory_record' => true // assuming feature exists
        ];

        $response = $this->actingAs($this->farmer)
            ->postJson('/api/harvests', $data);

        $response->assertStatus(201);

        $this->assertDatabaseHas('harvests', [
            'planting_id' => $this->planting->id,
            'yield_quantity' => 5000
        ]);
    }
}
