<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RiceProduct;
use App\Models\RiceOrder;
use App\Models\RiceVariety;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_farmer_dashboard_returns_marketplace_stats()
    {
        // Create a farmer
        $farmer = User::factory()->create([
            'role' => 'farmer',
        ]);

        // Create some products
        RiceProduct::factory()->count(3)->create([
            'farmer_id' => $farmer->id,
            'is_available' => true,
            'quantity_available' => 100,
        ]);

        // Create an order
        $product = RiceProduct::first();
        RiceOrder::create([
            'buyer_id' => User::factory()->create(['role' => 'buyer'])->id,
            'rice_product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => $product->price_per_unit,
            'total_amount' => $product->price_per_unit * 10,
            'status' => RiceOrder::STATUS_PENDING,
            'delivery_address' => json_encode(['address' => 'Test Address']),
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
        ]);

        $response = $this->actingAs($farmer)
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'stats',
                'marketplace_stats' => [
                    'total_products',
                    'active_listings',
                    'pending_orders',
                    'total_revenue',
                ],
                'recent_products',
            ]);

        // Verify specific values
        $response->assertJsonPath('marketplace_stats.total_products', 3);
        $response->assertJsonPath('marketplace_stats.active_listings', 3);
        $response->assertJsonPath('marketplace_stats.pending_orders', 1);
    }
}
