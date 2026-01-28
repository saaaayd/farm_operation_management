<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_buyer_cannot_access_farmer_routes()
    {
        $buyer = User::factory()->create(['role' => 'buyer']);

        // Try to access inventory (Farmer only)
        $response = $this->actingAs($buyer)
            ->getJson('/api/inventory'); // Assuming /api/inventory is protected by 'farmer' middleware

        $response->assertStatus(403);
    }

    public function test_guest_cannot_access_protected_routes()
    {
        $response = $this->getJson('/api/dashboard');
        $response->assertStatus(401);
    }
}
