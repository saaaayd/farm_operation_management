<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Field;
use App\Models\Planting;
use App\Models\RiceGrowthStage;
use App\Models\RiceVariety;
use App\Models\RiceProduct;
use App\Models\RiceOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SystemSimulationTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_system_lifecycle()
    {
        // ==========================================
        // PHASE 1: SETUP & FARMER ONBOARDING
        // ==========================================

        // 1. Create Growth Stages (System Seeding)
        $stages = $this->seedGrowthStages();

        // 2. Register Farmer
        $farmer = User::factory()->create([
            'name' => 'Juan Farmer',
            'email' => 'juan@farm.com',
            'password' => bcrypt('password'),
            'role' => 'farmer'
        ]);

        $this->actingAs($farmer);

        // 3. Create Field
        $fieldId = $this->postJson('/api/fields', [
            'name' => 'North Field',
            'location' => [
                'lat' => 15.5,
                'lon' => 120.5,
                'address' => 'Nueva Ecija',
            ],
            'size' => 2.5,
            'soil_type' => 'clay_loam',
            'field_coordinates' => json_encode([['lat' => 15.5, 'lon' => 120.5]])
        ])->assertStatus(201)->json('field.id');

        // ==========================================
        // PHASE 2: FARM LIFECYCLE (Planting to Harvest)
        // ==========================================

        // 4. Create Planting
        $variety = RiceVariety::factory()->create(['name' => 'RC160']);

        $plantingData = [
            'field_id' => $fieldId,
            'rice_variety_id' => $variety->id,
            'planting_date' => now()->subMonths(3)->toDateString(),
            'planting_method' => 'transplanting',
            'seed_rate' => 40,
            'area_planted' => 2.5,
            'season' => 'wet',
            'expected_harvest_date' => now()->addMonth()->toDateString(),
            'notes' => 'Simulation Planting'
        ];

        $response = $this->postJson('/api/rice-farming/plantings', $plantingData);

        if ($response->status() !== 201) {
            dump($response->json());
        }
        $plantingId = $response->assertStatus(201)
            ->json('planting.id');

        // 5. Advance Stages (Simulate Growth)
        // Advance to Vegetative
        $this->postJson("/api/rice-farming/plantings/{$plantingId}/advance-stage", [
            'notes' => 'Transitioning to Vegetative',
            'completion_percentage' => 100
        ])->assertStatus(200);

        // Advance to Reproductive (Flowering) - assumes sequential order
        $this->postJson("/api/rice-farming/plantings/{$plantingId}/advance-stage", [
            'notes' => 'Flowering started'
        ])->assertStatus(200);

        // 6. Record Harvest (Creates Inventory)
        $harvestData = [
            'planting_id' => $plantingId,
            'harvest_date' => now()->toDateString(),
            'quantity' => 10000, // 10 tons
            'unit' => 'kg',
            'quality_grade' => 'A',
            'price_per_unit' => 20.00, // Cost price
            'notes' => 'Bountiful harvest'
        ];

        $harvestResponse = $this->postJson('/api/harvests', $harvestData)
            ->assertStatus(201);

        // Assert Inventory was created automatically
        $this->assertDatabaseHas('inventory_items', [
            'user_id' => $farmer->id,
            'unit' => 'kg',
            'current_stock' => 10000
        ]);

        $inventoryItem = \App\Models\InventoryItem::where('user_id', $farmer->id)
            ->where('category', \App\Models\InventoryItem::CATEGORY_PRODUCE)
            ->first();

        // ==========================================
        // PHASE 3: MARKETPLACE LISTING
        // ==========================================

        // 7. List Product for Sale
        $productData = [
            'name' => 'Premium RC160 Rice (New Harvest)', // changed title to name
            'description' => 'Freshly harvested, Grade A rice.',
            'price_per_unit' => 50.00, // changed price to price_per_unit
            'unit' => 'kg',
            'quantity_available' => 5000, // changed available_quantity to quantity_available
            'min_order_quantity' => 50,
            'quality_grade' => 'grade_a', // changed invalid 'A' to 'grade_a'
            'rice_variety_id' => $variety->id, // added required field
            'category' => 'rice_produce',
            'inventory_item_id' => $inventoryItem->id,
            'location' => [
                'latitude' => 15.5,
                'longitude' => 120.5,
                'address' => 'Nueva Ecija',
            ],
        ];

        $productId = $this->postJson('/api/rice-marketplace/products', $productData)
            ->assertStatus(201)
            ->json('product.id');

        // ==========================================
        // PHASE 4: BUYER JOURNEY
        // ==========================================

        // 8. Buyer Registration
        $buyer = User::factory()->create([
            'name' => 'Maria Buyer',
            'email' => 'maria@market.com',
            'role' => 'buyer'
        ]);

        $this->actingAs($buyer);

        // 9. Add to Cart
        $this->postJson('/api/rice-marketplace/cart', [
            'rice_product_id' => $productId,
            'quantity' => 100 // Buying 100kg
        ])->assertStatus(200);

        // 10. Checkout (Place Order)
        $orderResponse = $this->postJson('/api/rice-marketplace/cart/checkout', [
            'delivery_address' => [
                'recipient_name' => 'Maria Buyer',
                'phone_number' => '09171234567',
                'address_line1' => 'Unit 101, Market St.',
                'city' => 'Manila',
                'province' => 'Metro Manila',
                'postal_code' => '1000',
                'country' => 'Philippines'
            ],
            'delivery_method' => 'pickup', // Added required field
            'payment_method' => 'cash_on_delivery',
            'notes' => 'Please deliver by weekend'
        ])->assertStatus(200);

        // Handling scenarios where checkout might return a single order or list
        $orderId = $orderResponse->json('order.id')
            ?? $orderResponse->json('orders.0.id');

        $this->assertNotNull($orderId, 'Order ID should not be null');

        // ==========================================
        // PHASE 5: ORDER FULFILLMENT
        // ==========================================

        $this->actingAs($farmer);

        // 11. Farmer Accepts Order
        $this->postJson("/api/rice-marketplace/orders/{$orderId}/accept")
            ->assertStatus(200);

        // 12. Mark Ready for Pickup/Delivery
        $this->postJson("/api/rice-marketplace/orders/{$orderId}/ready-for-pickup")
            ->assertStatus(200);

        // 13. Confirm Pickup/Delivery Completes the cycle
        $this->postJson("/api/rice-marketplace/orders/{$orderId}/confirm-pickup")
            ->assertStatus(200);

        // Verify Final State
        $order = RiceOrder::find($orderId);
        $this->assertEquals('picked_up', $order->status);
        $this->assertEquals('pending', $order->payment_status); // COD orders are pending until paid

        // Verify Inventory Deduction (5000 listed - 100 sold = 4900 available in listing, but physical inventory?)
        // The implementation details of inventory sync during checkout determine this.
        // Usually: Listing quantity decreases.
        $product = RiceProduct::find($productId);
        $this->assertEquals(4900, $product->quantity_available);
    }

    private function seedGrowthStages()
    {
        return collect([
            ['order_sequence' => 1, 'name' => 'Seedling', 'stage_code' => 'seedling', 'typical_duration_days' => 20],
            ['order_sequence' => 2, 'name' => 'Vegetative', 'stage_code' => 'vegetative', 'typical_duration_days' => 30],
            ['order_sequence' => 3, 'name' => 'Reproductive', 'stage_code' => 'flowering', 'typical_duration_days' => 30],
            ['order_sequence' => 4, 'name' => 'Ripening', 'stage_code' => 'ripening', 'typical_duration_days' => 30],
            ['order_sequence' => 5, 'name' => 'Harvest', 'stage_code' => 'maturity', 'typical_duration_days' => 0],
        ])->each(fn($s) => RiceGrowthStage::create($s));
    }
}
