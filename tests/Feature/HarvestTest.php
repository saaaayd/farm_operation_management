<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Field;
use App\Models\Planting;
use App\Models\RiceVariety;
use App\Models\Harvest;
use App\Models\InventoryItem;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class HarvestTest extends TestCase
{
    use DatabaseTransactions;

    protected $farmer;
    protected $planting;
    protected $variety;

    protected function setUp(): void
    {
        parent::setUp();
        $this->farmer = User::factory()->create(['role' => 'farmer']);

        $field = Field::factory()->create(['user_id' => $this->farmer->id]);
        $this->variety = RiceVariety::factory()->create(['name' => 'Test Variety Pro']);

        $this->planting = Planting::create([
            'field_id' => $field->id,
            'rice_variety_id' => $this->variety->id,
            'planting_date' => now()->subDays(120),
            'expected_harvest_date' => now(),
            'status' => 'planted',
            'planting_method' => 'transplanting',
            'area_planted' => 2.0,
            'crop_type' => 'rice',
            'season' => 'wet',
        ]);
    }

    public function test_can_record_harvest()
    {
        $response = $this->actingAs($this->farmer)
            ->postJson('/api/harvests', [
                'planting_id' => $this->planting->id,
                'harvest_date' => now()->toDateString(),
                'quantity' => 5000,
                'unit' => 'kg',
                'quality_grade' => 'A',
                'price_per_unit' => 25,
                'notes' => 'Bountiful harvest'
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('harvest.quantity', '5000.00'); // DB returns string for decimal

        // check harvest record
        $this->assertDatabaseHas('harvests', [
            'planting_id' => $this->planting->id,
            'quantity' => 5000 // DatabaseHas handles number/string loose match usually
        ]);

        // check planting status updated
        $this->assertEquals('harvested', $this->planting->fresh()->status);
        $this->assertNotNull($this->planting->fresh()->actual_harvest_date);

        // check inventory updated
        $expectedName = $this->variety->name . ' (Grade A)';

        $this->assertDatabaseHas('inventory_items', [
            'user_id' => $this->farmer->id,
            'name' => $expectedName,
            'current_stock' => '5000.00',
            'category' => 'produce',
        ]);
    }

    public function test_can_list_harvests()
    {
        // manually create a harvest
        Harvest::create([
            'planting_id' => $this->planting->id,
            'harvest_date' => now(),
            'quantity' => 100,
            'yield' => 100,
            'unit' => 'kg',
            'quality_grade' => 'B',
            'quality' => 'good' // Required by DB
        ]);

        $response = $this->actingAs($this->farmer)
            ->getJson('/api/harvests');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'harvests');
    }

    public function test_record_harvest_with_share_deduction()
    {
        $grossHarvest = 5000;
        $harvesterShare = 1000;
        $expectedNetStock = $grossHarvest - $harvesterShare;

        $response = $this->actingAs($this->farmer)
            ->postJson('/api/harvests', [
                'planting_id' => $this->planting->id,
                'harvest_date' => now()->toDateString(),
                'quantity' => $grossHarvest,
                'unit' => 'kg',
                'quality_grade' => 'A',
                'price_per_unit' => 20,
                'harvester_share' => $harvesterShare,
                'notes' => 'Harvest with share deduction'
            ]);

        $response->assertStatus(201);

        // Calculate expected inventory name
        $expectedName = $this->variety->name . ' (Grade A)';

        // 1. Verify that the harvest record stores the FULL quantity
        $this->assertDatabaseHas('harvests', [
            'planting_id' => $this->planting->id,
            'quantity' => $grossHarvest,
            'harvester_share' => $harvesterShare
        ]);

        // 2. Verify that the inventory item has the NET quantity (Gross - Share)
        $this->assertDatabaseHas('inventory_items', [
            'user_id' => $this->farmer->id,
            'name' => $expectedName,
            'current_stock' => (string) $expectedNetStock,
        ]);
    }
}
