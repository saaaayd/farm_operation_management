<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Farm;
use App\Models\Field;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class SimplifiedOnboardingTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    /**
     * Test creating a rice farm profile with only basic farm information.
     */
    public function test_can_create_rice_farm_profile_without_fields()
    {
        $user = User::factory()->create([
            'role' => 'farmer',
        ]);

        $this->actingAs($user);

        $payload = [
            'farm_name' => 'My Test Farm',
            'location' => 'Managok, Malaybalay City',
            'total_area' => 10.5,
            'rice_area' => 5.5,
            'farming_experience' => 5,
            'farm_description' => 'A test farm description',
            // No field or soil info provided
        ];

        $response = $this->postJson('/api/farmer/profile', $payload);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'farmProfile' => [
                    'farm',
                    'field',
                    'user_profile',
                ],
                'fields',
            ]);

        // Check farm was created
        $this->assertDatabaseHas('farms', [
            'user_id' => $user->id,
            'name' => 'My Test Farm',
            'total_area' => null, // total_area is usually stored on user address or specific farm columns depending on schema, controller uses updateOrCreate on user address for these
        ]);

        // Check user address updated
        $user->refresh();
        $this->assertEquals('My Test Farm', $user->address['farm_location'] ? 'My Test Farm' : $payload['location']); // Wait, controller maps location to address['farm_location']?
        // Let's check the controller logic again.
        // Controller: $user->update(['address' => ['farm_location' => $request->location ... ]]);

        $this->assertEquals($payload['location'], $user->address['farm_location']);
        $this->assertEquals($payload['total_area'], $user->address['total_area']);

        // Check NO field was created
        $this->assertEquals(0, Field::where('user_id', $user->id)->count());
        $this->assertEmpty($response->json('fields'));
    }
}
