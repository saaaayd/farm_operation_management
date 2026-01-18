<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RiceOrder;
use App\Models\RiceProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MarketplaceInteractionTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_messaging()
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = RiceProduct::factory()->create(['farmer_id' => $farmer->id]);

        $order = RiceOrder::create([
            'buyer_id' => $buyer->id,
            'rice_product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 100,
            'total_amount' => 1000,
            'status' => 'confirmed',
            'delivery_address' => 'Test Address',
            'delivery_method' => 'pickup',
            'payment_status' => 'paid',
            'payment_method' => 'cash'
        ]);

        // Buyer sends message
        $response = $this->actingAs($buyer)
            ->postJson("/api/rice-marketplace/orders/{$order->id}/messages", [
                'message' => 'When can I pick this up?'
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('rice_order_messages', ['message' => 'When can I pick this up?']);
    }

    public function test_buyer_can_review_purchased_product()
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = RiceProduct::factory()->create();

        $order = RiceOrder::create([
            'buyer_id' => $buyer->id,
            'rice_product_id' => $product->id,
            'quantity' => 10,
            'unit_price' => 100,
            'total_amount' => 1000,
            'status' => 'delivered',
            'delivery_address' => 'Test Address',
            'delivery_method' => 'pickup',
            'payment_status' => 'paid',
            'payment_method' => 'cash'
        ]);

        $response = $this->actingAs($buyer)
            ->postJson('/api/rice-marketplace/reviews', [
                'rice_product_id' => $product->id,
                'rice_order_id' => $order->id, // Correct key
                'rating' => 5,
                'review_text' => 'Great rice!' // Correct key
            ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('product_reviews', ['comment' => 'Great rice!']);
    }
}
