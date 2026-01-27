<?php

namespace Tests\Feature\Marketplace;

use App\Models\User;
use App\Models\RiceProduct;
use App\Models\RiceOrder;
use App\Models\Sale;
use App\Models\RiceVariety;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceSaleIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected $farmer;
    protected $buyer;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->farmer = User::factory()->create(['role' => 'farmer']);
        $this->buyer = User::factory()->create(['role' => 'buyer']);

        $harvest = \App\Models\Harvest::factory()->create([
            'planting_id' => \App\Models\Planting::factory()->create([
                'field_id' => \App\Models\Field::factory()->create(['user_id' => $this->farmer->id])
            ])
        ]);

        $this->product = RiceProduct::factory()->create([
            'farmer_id' => $this->farmer->id,
            'harvest_id' => $harvest->id,
            'quantity_available' => 100,
            'price_per_unit' => 50,
            'is_available' => true,
            'production_status' => 'available'
        ]);
    }

    public function test_order_completion_creates_sale_record()
    {
        // 1. Buyer places order
        $orderData = [
            'rice_product_id' => $this->product->id,
            'quantity' => 10,
            'delivery_address' => ['address' => '123 Main St'],
            'delivery_method' => 'pickup',
            'payment_method' => 'cash'
        ];

        // Route: /api/rice-marketplace/orders
        $response = $this->actingAs($this->buyer)
            ->postJson('/api/rice-marketplace/orders', $orderData);

        $response->assertStatus(201);
        $orderId = $response->json('order.id');
        $order = RiceOrder::find($orderId);

        // Check stock deducted immediately
        $this->assertEquals(90, $this->product->fresh()->quantity_available);

        // 2. Farmer accepts order
        // Route: /api/rice-marketplace/orders/{order}/accept
        $this->actingAs($this->farmer)
            ->postJson("/api/rice-marketplace/orders/{$order->id}/accept", [
                'expected_delivery_date' => now()->addDays(2)->toDateString()
            ])
            ->assertStatus(200);

        // 3. Farmer marks ready
        // Route: /api/rice-marketplace/orders/{order}/ready-for-pickup
        $this->actingAs($this->farmer)
            ->postJson("/api/rice-marketplace/orders/{$order->id}/ready-for-pickup")
            ->assertStatus(200);

        $order->refresh();
        $this->assertEquals('ready_for_pickup', $order->status);

        // 4. Farmer confirms pickup (completion)
        // Route: /api/rice-marketplace/orders/{order}/confirm-pickup
        $this->actingAs($this->farmer)
            ->postJson("/api/rice-marketplace/orders/{$order->id}/confirm-pickup")
            ->assertStatus(200);

        $order->refresh();
        $this->assertEquals('picked_up', $order->status);

        // 5. Verify Sale record created
        $this->assertDatabaseHas('sales', [
            'rice_order_id' => $order->id,
            'user_id' => $this->farmer->id,
            'buyer_id' => $this->buyer->id,
            'quantity' => 10,
            'total_amount' => 500, // 10 * 50
        ]);

        // Verify stock remains deducted (not double deducted)
        $this->assertEquals(90, $this->product->fresh()->quantity_available);
    }
}
