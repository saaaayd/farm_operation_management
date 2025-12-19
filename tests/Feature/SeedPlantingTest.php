<?php

namespace Tests\Feature;

use App\Models\Field;
use App\Models\RiceVariety;
use App\Models\SeedPlanting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SeedPlantingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $variety;
    protected $field;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'farmer']);
        $this->variety = RiceVariety::factory()->create();
        $this->field = Field::factory()->create(['user_id' => $this->user->id]);
    }

    public function test_can_create_seed_planting()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/seed-plantings', [
                'rice_variety_id' => $this->variety->id,
                'quantity' => 10,
                'unit' => 'kg',
                'planting_date' => now()->toDateString(),
                'notes' => 'Test Sowing'
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('seed_plantings', [
            'quantity' => 10,
            'status' => 'sown'
        ]);
    }

    public function test_can_list_seed_plantings()
    {
        SeedPlanting::create([
            'user_id' => $this->user->id,
            'rice_variety_id' => $this->variety->id,
            'quantity' => 5,
            'planting_date' => now(),
            'status' => 'sown'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/seed-plantings');

        $response->assertStatus(200)
            ->assertJsonCount(1);
    }

    public function test_can_get_ready_seed_plantings()
    {
        SeedPlanting::create([
            'user_id' => $this->user->id,
            'rice_variety_id' => $this->variety->id,
            'quantity' => 5,
            'planting_date' => now(),
            'status' => 'sown'
        ]);

        $ready = SeedPlanting::create([
            'user_id' => $this->user->id,
            'rice_variety_id' => $this->variety->id,
            'quantity' => 5,
            'planting_date' => now(),
            'status' => 'ready'
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/seed-plantings/ready');

        $response->assertStatus(200)
            ->assertJsonCount(1)
            ->assertJsonFragment(['id' => $ready->id]);
    }

    public function test_can_update_status()
    {
        $planting = SeedPlanting::create([
            'user_id' => $this->user->id,
            'rice_variety_id' => $this->variety->id,
            'quantity' => 5,
            'planting_date' => now(),
            'status' => 'sown'
        ]);

        $response = $this->actingAs($this->user)
            ->putJson("/api/seed-plantings/{$planting->id}", [
                'status' => 'ready'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('seed_plantings', [
            'id' => $planting->id,
            'status' => 'ready'
        ]);
    }

    public function test_transplanting_updates_seed_planting_status()
    {
        $seedPlanting = SeedPlanting::create([
            'user_id' => $this->user->id,
            'rice_variety_id' => $this->variety->id,
            'quantity' => 5,
            'planting_date' => now()->subDays(20),
            'status' => 'ready'
        ]);

        $response = $this->actingAs($this->user)
            ->postJson('/api/plantings', [
                'field_id' => $this->field->id,
                'seed_planting_id' => $seedPlanting->id,
                'rice_variety_id' => $this->variety->id,
                'planting_date' => now()->toDateString(),
                'planting_method' => 'transplanting',
                'area_planted' => 1,
            ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('plantings', [
            'seed_planting_id' => $seedPlanting->id,
        ]);

        $this->assertDatabaseHas('seed_plantings', [
            'id' => $seedPlanting->id,
            'status' => 'transplanted'
        ]);
    }
}
