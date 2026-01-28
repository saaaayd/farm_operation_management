<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryOrderTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $item;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'farmer']);
        $this->item = InventoryItem::factory()->create([
            'user_id' => $this->user->id,
            'current_stock' => 10,
            'unit_price' => 100
        ]);
    }

    /** @test */
    public function order_delivery_updates_stock()
    {
        $order = Order::create([
            'buyer_id' => $this->user->id,
            'supplier_name' => 'Supplier A',
            'order_date' => now(),
            'status' => 'pending',
            'total_amount' => 500
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'inventory_item_id' => $this->item->id,
            'quantity' => 5,
            'unit_price' => 100
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/orders/{$order->id}/deliver");

        $response->assertStatus(200);

        // Stock should be 10 + 5 = 15
        $this->assertEquals(15, $this->item->fresh()->current_stock, "Stock should increase by order quantity");
    }

    /** @test */
    public function order_delivery_does_not_double_count_stock_on_repeat_update()
    {
        $order = Order::create([
            'buyer_id' => $this->user->id,
            'supplier_name' => 'Supplier A',
            'order_date' => now(),
            'status' => 'pending',
            'total_amount' => 500
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'inventory_item_id' => $this->item->id,
            'quantity' => 5,
            'unit_price' => 100
        ]);

        // First delivery
        $this->actingAs($this->user)
            ->postJson("/api/orders/{$order->id}/deliver");

        $this->assertEquals(15, $this->item->fresh()->current_stock, "Stock should increase once");

        // Second delivery (accidental or retry)
        $this->actingAs($this->user)
            ->postJson("/api/orders/{$order->id}/deliver");

        // Stock should STILL be 15
        $this->assertEquals(15, $this->item->fresh()->current_stock, "Stock should NOT increase twice");
    }
}
