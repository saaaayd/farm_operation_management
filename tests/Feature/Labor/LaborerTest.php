<?php

namespace Tests\Feature\Labor;

use App\Models\User;
use App\Models\Laborer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class LaborerTest extends TestCase
{
    use RefreshDatabase;

    protected $farmer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->farmer = User::factory()->create(['role' => 'farmer']);
    }

    public function test_can_create_laborer()
    {
        $data = [
            'name' => 'John Doe',
            'phone' => '09171234567',
            'skill_level' => 'intermediate',
            'rate_type' => 'daily',
            'rate' => 500,
            'status' => 'active',
            'hire_date' => now()->toDateString(),
        ];

        $response = $this->actingAs($this->farmer)
            ->postJson('/api/laborers', $data);

        $response->assertStatus(201)
            ->assertJsonPath('laborer.name', 'John Doe');

        $this->assertDatabaseHas('laborers', [
            'name' => 'John Doe',
            'user_id' => $this->farmer->id
        ]);
    }

    public function test_can_update_laborer()
    {
        $laborer = Laborer::factory()->create(['user_id' => $this->farmer->id]);

        $response = $this->actingAs($this->farmer)
            ->putJson("/api/laborers/{$laborer->id}", [
                'name' => 'Jane Doe',
                'status' => 'inactive'
            ]);

        $response->assertStatus(200);

        $this->assertEquals('Jane Doe', $laborer->fresh()->name);
        $this->assertEquals('inactive', $laborer->fresh()->status);
    }

    public function test_can_delete_laborer()
    {
        $laborer = Laborer::factory()->create(['user_id' => $this->farmer->id]);

        $response = $this->actingAs($this->farmer)
            ->deleteJson("/api/laborers/{$laborer->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('laborers', ['id' => $laborer->id]);
    }

    public function test_cannot_access_other_farmers_laborers()
    {
        $otherFarmer = User::factory()->create(['role' => 'farmer']);
        $laborer = Laborer::factory()->create(['user_id' => $otherFarmer->id]);

        $response = $this->actingAs($this->farmer)
            ->getJson("/api/laborers/{$laborer->id}");

        $response->assertStatus(403);
    }
}
