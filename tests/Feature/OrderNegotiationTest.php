<?php

namespace Tests\Feature;

use App\Models\RiceOrder;
use App\Models\RiceProduct;
use App\Models\RiceVariety;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderNegotiationTest extends TestCase
{
    // use RefreshDatabase; // commenting out to avoid wiping user's dev data if configured wrong, but usually tests use separate DB. 
    // SAFEST: Use transaction or rely on explicit cleanup. For this environment, I'll trust standard Laravel test config or just clean up created data.
    // Actually, widespread practice in this env is to just run it. If it fails due to DB state, we handle it.
    // Let's use clean up in tearDown.

    protected $farmer;
    protected $buyer;
    protected $product;
    protected $order;

    protected function setUp(): void
    {
        parent::setUp();

        try {
            // Create Farmer
            $this->farmer = User::factory()->create([
                'role' => 'farmer',
                'name' => 'Test Farmer',
                'email' => 'farmer_' . uniqid() . '@test.com',
                'phone' => '09' . mt_rand(100000000, 999999999),
                'address' => [
                    'street' => 'Farm Rd',
                    'city' => 'Farm City',
                    'country' => 'Philippines'
                ]
            ]);

            // Create Buyer
            $this->buyer = User::factory()->create([
                'role' => 'buyer',
                'name' => 'Test Buyer',
                'email' => 'buyer_' . uniqid() . '@test.com',
                'phone' => '09' . mt_rand(100000000, 999999999),
                'address' => [
                    'street' => 'Buyer St',
                    'city' => 'Buyer City',
                    'country' => 'Philippines'
                ]
            ]);
        } catch (\Exception $e) {
            echo "ERROR CREATING USER: " . $e->getMessage();
            throw $e;
        }

        // Create Product
        $variety = RiceVariety::first();
        if (!$variety) {
            $variety = RiceVariety::create([
                'name' => 'Test Variety',
                'variety_code' => 'TEST-001',
                'description' => 'Test',
                'maturity_days' => 100,
                'average_yield_per_hectare' => 5.5,
                'season' => 'wet',
                'grain_type' => 'long',
                'resistance_level' => 'high'
            ]);
        }

        $this->product = RiceProduct::create([
            'farmer_id' => $this->farmer->id,
            'rice_variety_id' => $variety->id,
            'name' => 'Test Rice',
            'description' => 'Test Description',
            'price_per_unit' => 100.00,
            'quantity_available' => 100,
            'unit' => 'kg',
            'production_status' => 'available',
            'quality_grade' => 'premium',
            'is_available' => true,
        ]);
    }

    protected function tearDown(): void
    {
        if ($this->order)
            $this->order->delete();
        if ($this->product)
            $this->product->delete();
        if ($this->buyer)
            $this->buyer->delete();
        if ($this->farmer)
            $this->farmer->delete();
        parent::tearDown();
    }

    public function test_negotiation_flow()
    {
        // 1. Buyer creates order with negotiation
        $offerPrice = 80.00;
        $quantity = 10;

        $response = $this->actingAs($this->buyer)
            ->postJson('/api/rice-marketplace/orders', [
                'rice_product_id' => $this->product->id,
                'quantity' => $quantity,
                'delivery_address' => [
                    'street' => '123 Test St',
                    'city' => 'Test City',
                    'state' => 'Test State',
                    'postal_code' => '12345',
                    'country' => 'Philippines'
                ],
                'delivery_method' => 'pickup',
                'payment_method' => 'cod',
                'offer_price' => $offerPrice,
            ]);

        $response->assertStatus(201);
        $orderId = $response->json('order.id');
        $this->order = RiceOrder::find($orderId);

        $this->assertEquals($offerPrice, $this->order->offer_price, 'Offer price should be saved');
        $this->assertEquals(RiceOrder::STATUS_NEGOTIATING, $this->order->status, 'Order status should be negotiating');

        // 2. Farmer accepts negotiation
        $response = $this->actingAs($this->farmer)
            ->postJson("/api/rice-marketplace/orders/{$orderId}/negotiate", [
                'action' => 'accept'
            ]);

        $response->assertStatus(200);
        $this->order->refresh();

        $this->assertEquals(RiceOrder::STATUS_PENDING, $this->order->status, 'Order status should be pending after acceptance');
        $this->assertEquals($offerPrice, $this->order->unit_price, 'Unit price should be updated to offer price');

        echo "\nNegotiation Test Passed!\n";
    }

    public function test_farmer_reject_negotiation()
    {
        // 1. Buyer creates order with negotiation
        $offerPrice = 70.00;

        $response = $this->actingAs($this->buyer)
            ->postJson('/api/rice-marketplace/orders', [
                'rice_product_id' => $this->product->id,
                'quantity' => 5,
                'delivery_address' => ['street' => 'test', 'city' => 'test', 'state' => 'test', 'postal_code' => '123', 'country' => 'PH'],
                'delivery_method' => 'pickup',
                'payment_method' => 'cod',
                'offer_price' => $offerPrice,
            ]);

        $orderId = $response->json('order.id');
        $order = RiceOrder::find($orderId);

        // 2. Farmer rejects negotiation
        $response = $this->actingAs($this->farmer)
            ->postJson("/api/rice-marketplace/orders/{$orderId}/negotiate", [
                'action' => 'reject'
            ]);

        $response->assertStatus(200);
        $order->refresh();

        $this->assertEquals(RiceOrder::STATUS_CANCELLED, $order->status, 'Order should be cancelled after rejection');

        // Clean up this specific order
        $order->delete();

        echo "\nRejection Test Passed!\n";
    }
}
