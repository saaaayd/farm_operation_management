<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RiceProduct;
use App\Models\RiceOrder;
use App\Models\RiceVariety;
use App\Models\Field;
use App\Models\Planting;
use App\Models\Task;
use App\Models\Farm;
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
    public function test_farmer_dashboard_stats_accuracy()
    {
        $farmer = User::factory()->create(['role' => 'farmer']);

        // Create Farm
        $farm = Farm::factory()->create(['user_id' => $farmer->id]);

        // Create 2 Fields
        $fields = Field::factory()->count(2)->create(['user_id' => $farmer->id, 'farm_id' => $farm->id]);

        // Create 1 Active Planting (Growing)
        $p1 = Planting::factory()->create([
            'field_id' => $fields[0]->id,
            'status' => 'growing',
            'season' => 'dry'
        ]);

        // Create 1 Harvested Planting
        $p2 = Planting::factory()->create([
            'field_id' => $fields[1]->id,
            'status' => 'harvested',
            'season' => 'wet'
        ]);

        // Create 1 Pending Task for Active Planting
        Task::factory()->create([
            'planting_id' => $p1->id,
            'status' => 'pending',
            'due_date' => now()->addDays(1)
        ]);

        // Create 1 Completed Task for Harvested Planting
        Task::factory()->create([
            'planting_id' => $p2->id,
            'status' => 'completed'
        ]);

        $response = $this->actingAs($farmer)
            ->getJson('/api/dashboard');

        $response->assertStatus(200)
            ->assertJsonPath('stats.total_fields', 2)
            ->assertJsonPath('stats.active_plantings', 1) // Only p1
            ->assertJsonPath('stats.pending_tasks', 1); // Only p1 task
    }
}
