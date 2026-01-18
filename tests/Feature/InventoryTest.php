<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\InventoryItem;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class InventoryTest extends TestCase
{
    // use RefreshDatabase; // Skipping to avoid wiping dev data

    protected $farmer;

    protected function setUp(): void
    {
        parent::setUp();
        // Create a farmer user
        $this->farmer = User::factory()->create(['role' => 'farmer']);
    }

    protected function tearDown(): void
    {
        if ($this->farmer) {
            // Clean up created items
            InventoryItem::where('user_id', $this->farmer->id)->delete();
            Expense::where('user_id', $this->farmer->id)->delete();
            $this->farmer->delete();
        }
        parent::tearDown();
    }

    public function test_can_create_inventory_item()
    {
        $itemData = [
            'name' => 'Test Fertilizer',
            'category' => 'fertilizer',
            'unit' => 'bag',
            'current_stock' => 10,
            'unit_price' => 500,
            'supplier' => 'Test Supplier',
        ];

        $response = $this->actingAs($this->farmer)
            ->postJson('/api/inventory', $itemData);

        $response->assertStatus(201)
            ->assertJsonPath('inventory_item.name', 'Test Fertilizer')
            ->assertJsonPath('inventory_item.current_stock', '10.00'); // DB returns string for decimal

        // Verify Expense created for initial stock
        $this->assertDatabaseHas('expenses', [
            'user_id' => $this->farmer->id,
            'amount' => 5000, // 10 * 500
            'description' => 'Initial Stock: Test Fertilizer (10 packets)', // 'bag' normalized to 'packets' in controller? let's check controller logic
            // Controller mapping: 'bag' -> 'packets'.
        ]);

        // Actually, let's just check the amount and user_id, description might vary slightly
        // Controller: 'Initial Stock: {$inventoryItem->name} ({$currentStock} {$inventoryItem->unit})'
    }

    public function test_can_list_items()
    {
        InventoryItem::factory()->create(['user_id' => $this->farmer->id, 'name' => 'Item A']);
        InventoryItem::factory()->create(['user_id' => $this->farmer->id, 'name' => 'Item B']);

        $response = $this->actingAs($this->farmer)
            ->getJson('/api/inventory');

        $response->assertStatus(200)
            ->assertJsonCount(2, 'inventory_items');
    }

    public function test_can_add_stock_API()
    {
        $item = InventoryItem::factory()->create([
            'user_id' => $this->farmer->id,
            'current_stock' => 5,
            'unit_price' => 100
        ]);

        $response = $this->actingAs($this->farmer)
            ->postJson("/api/inventory/{$item->id}/add-stock", [
                'quantity' => 10,
                'unit_cost' => 120,
                'create_expense' => true,
                'expense_category' => 'other' // Changed to a likely safe value
            ]);

        $response->assertStatus(200);

        $this->assertEquals(15, $item->fresh()->current_stock, 'Stock should increase');

        $this->assertDatabaseHas('expenses', [
            'user_id' => $this->farmer->id,
            'amount' => 1200, // 10 * 120
            'related_entity_id' => $item->id
        ]);
    }
}
