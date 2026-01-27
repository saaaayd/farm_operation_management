<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    // use RefreshDatabase; // Skipping RefreshDatabase as per env constraints, will rely on unique emails

    public function test_user_can_register()
    {
        $userData = [
            'first_name' => 'Test',
            'last_name' => 'User',
            'phone' => '09' . mt_rand(100000000, 999999999),
            'email' => 'test_register_' . uniqid() . '@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'farmer',
            'verification_method' => 'email', // or 'sms' depending on config
            'terms' => true,
        ];

        $response = $this->postJson('/api/register', $userData);

        $response->assertStatus(201);
    }

    public function test_user_can_login()
    {
        $password = 'password123';
        $user = User::factory()->create([
            'email' => 'test_login_' . uniqid() . '@example.com',
            'password' => bcrypt($password)
        ]);

        $response = $this->postJson('/api/login', [
            'login_id' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(200); // API returns 200 OK
        // $this->assertAuthenticatedAs($user); // Session auth assertion doesn't apply to token-based login response
        $response->assertJsonStructure(['token', 'user']); // Assuming response returns token and user
    }
    public function test_unverified_user_cannot_login()
    {
        $password = 'password123';
        $user = User::factory()->unverified()->create([
            'email' => 'unverified_' . uniqid() . '@example.com',
            'password' => bcrypt($password),
            'phone_verified_at' => null, // Explicitly nullify phone verification
        ]);

        $response = $this->postJson('/api/login', [
            'login_id' => $user->email,
            'password' => $password,
        ]);

        $response->assertStatus(403);
    }
}
