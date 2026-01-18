<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Field;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FieldManagementTest extends TestCase
{
    use RefreshDatabase;

    protected $farmer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->farmer = User::factory()->create(['role' => 'farmer']);
    }

    public function test_can_create_field()
    {
        $fieldData = [
            'name' => 'Rice Terrace 1',
            'location' => 'Banaue',
            'size' => 2.5,
            'soil_type' => 'Clay Loam',
            'irrigation_type' => 'Rainfed',
            'field_coordinates' => [['lat' => 16.9, 'lng' => 121.0], ['lat' => 16.91, 'lng' => 121.01]],
        ];

        $response = $this->actingAs($this->farmer)
            ->postJson('/api/fields', $fieldData);

        $response->assertStatus(201)
            ->assertJsonPath('data.name', 'Rice Terrace 1');

        $this->assertDatabaseHas('fields', [
            'name' => 'Rice Terrace 1',
            'user_id' => $this->farmer->id
        ]);
    }

    public function test_can_update_field_details()
    {
        $field = Field::factory()->create(['user_id' => $this->farmer->id]);

        $response = $this->actingAs($this->farmer)
            ->putJson("/api/fields/{$field->id}", [
                'name' => 'Updated Name',
                'soil_type' => 'Sandy',
                'area' => 3.0
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('fields', [
            'id' => $field->id,
            'name' => 'Updated Name'
        ]);
    }

    public function test_can_delete_field()
    {
        $field = Field::factory()->create(['user_id' => $this->farmer->id]);

        $response = $this->actingAs($this->farmer)
            ->deleteJson("/api/fields/{$field->id}");

        $response->assertStatus(200); // or 204
        $this->assertDatabaseMissing('fields', ['id' => $field->id]);
    }
}
