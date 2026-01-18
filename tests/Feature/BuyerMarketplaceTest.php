<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\RiceProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuyerMarketplaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_add_to_cart()
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = RiceProduct::factory()->create();

        $response = $this->actingAs($buyer)
            ->postJson('/api/rice-marketplace/cart', [
                'product_id' => $product->id,
                'quantity' => 2
            ]);

        $response->assertStatus(200); // or 201

        // If DB cart exists
        $this->assertDatabaseHas('cart_items', [
            'user_id' => $buyer->id,
            'rice_product_id' => $product->id,
            'quantity' => 2
        ]);

        // OR if session based, check session
        // $response->assertSessionHas('cart');
    }

    public function test_toggle_favorites()
    {
        $buyer = User::factory()->create(['role' => 'buyer']);
        $product = RiceProduct::factory()->create();

        $response = $this->actingAs($buyer)
            ->postJson('/api/rice-marketplace/favorites', [
                'product_id' => $product->id
            ]);

        $response->assertStatus(200); // or 201 created
        $this->assertDatabaseHas('favorites', [
            'user_id' => $buyer->id,
            'rice_product_id' => $product->id
        ]);
    }
}
