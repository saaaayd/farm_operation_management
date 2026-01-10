<?php

namespace Tests\Feature;

use App\Models\RiceOrder;
use App\Models\RiceProduct;
use App\Models\User;
use App\Models\RiceVariety; // Assuming this is needed for factory
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class OrderStockTest extends TestCase
{
    use DatabaseTransactions; // Safe for dev DB

    public function test_order_creation_reserves_stock_immediately()
    {
        // 1. Setup Data
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);

        // Create a product with specific stock
        $initialStock = 100;
        $product = RiceProduct::factory()->create([
            'farmer_id' => $farmer->id,
            'quantity_available' => $initialStock,
            'price_per_unit' => 10,
            'is_available' => true,
        ]);

        $orderQuantity = 10;

        // 2. Act: Place Order via API (or Controller directly if API issues)
        // Using API is better integration test
        $response = $this->actingAs($buyer)->postJson('/api/rice-marketplace/orders', [
            'rice_product_id' => $product->id,
            'quantity' => $orderQuantity,
            'delivery_address' => ['address' => '123 Test St'],
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
        ]);

        // 3. Assert: Check Response
        $response->assertStatus(201); // Created

        // 4. Assert: Check Database (Stock should be deducted)
        $this->assertDatabaseHas('rice_orders', [
            'buyer_id' => $buyer->id,
            'rice_product_id' => $product->id,
            'quantity' => $orderQuantity,
            'status' => 'pending', // Pending orders SHOULD have reserved stock now
        ]);

        // Verify stock deduction on the product model
        $updatedProduct = $product->fresh();
        $this->assertEquals(
            $initialStock - $orderQuantity,
            $updatedProduct->quantity_available,
            "Stock should be deducted immediately upon order creation."
        );
    }

    public function test_cannot_order_more_than_available()
    {
        $farmer = User::factory()->create(['role' => 'farmer']);
        $buyer = User::factory()->create(['role' => 'buyer']);

        $product = RiceProduct::factory()->create([
            'farmer_id' => $farmer->id,
            'quantity_available' => 5, // Low stock
            'is_available' => true,
        ]);

        // Try to order 10
        $response = $this->actingAs($buyer)->postJson('/api/rice-marketplace/orders', [
            'rice_product_id' => $product->id,
            'quantity' => 10,
            'delivery_address' => ['address' => '123 Test St'],
            'delivery_method' => 'pickup',
            'payment_method' => 'cash',
        ]);

        $response->assertStatus(422); // Unprocessable Entity

        // Verify stock remains unchanged
        $this->assertEquals(5, $product->fresh()->quantity_available);
    }
}
