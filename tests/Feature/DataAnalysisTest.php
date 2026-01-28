<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Farm;
use App\Models\Field;
use App\Models\Planting;
use App\Models\Task;
use App\Models\Expense;
use App\Models\InventoryItem;
use App\Models\SeedPlanting;
use App\Models\RiceVariety;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DataAnalysisTest extends TestCase
{
    use RefreshDatabase;

    protected $farmer;
    protected $farm;
    protected $field;
    protected $planting;
    protected $variety;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a farmer user
        $this->farmer = User::factory()->create([
            'role' => 'farmer',
            'phone_verified_at' => now(),
        ]);

        // Create farm
        $this->farm = Farm::factory()->create([
            'user_id' => $this->farmer->id,
            'name' => 'Test Farm',
        ]);

        // Create field manually
        $this->field = Field::create([
            'user_id' => $this->farmer->id,
            'farm_id' => $this->farm->id,
            'name' => 'Test Field',
            'size' => 2.5,
            'soil_type' => 'loam',
            'irrigation_source' => 'canal',
        ]);

        // Create rice variety manually
        $this->variety = RiceVariety::create([
            'name' => 'Test Variety',
            'variety_code' => 'TEST001',
            'type' => 'lowland',
            'description' => 'Test variety for testing',
            'maturity_days' => 120,
            'average_yield_per_hectare' => 5000,
            'season' => 'both',
            'grain_type' => 'long',
            'resistance_level' => 'high',
        ]);

        // Create planting manually
        $this->planting = Planting::create([
            'field_id' => $this->field->id,
            'rice_variety_id' => $this->variety->id,
            'status' => 'growing',
            'area_planted' => 2.0,
            'planting_date' => now()->subDays(30),
            'expected_harvest_date' => now()->addDays(90),
            'planting_method' => 'transplanting',
            'season' => 'dry',
        ]);
    }

    /** @test */
    public function farmer_can_access_data_analysis_endpoint()
    {
        $response = $this->actingAs($this->farmer, 'sanctum')
            ->getJson('/api/analytics/data-analysis');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'weather',
                'sales',
                'expenses',
                'fields',
                'nursery',
                'inventory',
                'pests',
                'laborers',
                'tasks',
                'action_suggestions',
                'date_range' => ['start', 'end'],
            ]);
    }

    /** @test */
    public function data_analysis_returns_correct_field_data()
    {
        $response = $this->actingAs($this->farmer, 'sanctum')
            ->getJson('/api/analytics/data-analysis');

        $response->assertStatus(200);

        $data = $response->json();

        $this->assertEquals(1, $data['fields']['total_fields']);
        $this->assertEquals(2.5, $data['fields']['total_area']);
    }

    /** @test */
    public function data_analysis_returns_tasks_data()
    {
        // Create some tasks
        Task::create([
            'planting_id' => $this->planting->id,
            'task_type' => 'watering',
            'description' => 'Test task completed',
            'status' => 'completed',
            'priority' => 'medium',
            'due_date' => now()->subDay(),
        ]);

        Task::create([
            'planting_id' => $this->planting->id,
            'task_type' => 'fertilizing',
            'description' => 'Test task overdue',
            'status' => 'pending',
            'priority' => 'high',
            'due_date' => now()->subDays(2), // Overdue
        ]);

        $response = $this->actingAs($this->farmer, 'sanctum')
            ->getJson('/api/analytics/data-analysis');

        $response->assertStatus(200);

        $data = $response->json();

        $this->assertEquals(2, $data['tasks']['total_tasks']);
        $this->assertEquals(1, $data['tasks']['completed_tasks']);
        $this->assertEquals(1, $data['tasks']['overdue_tasks']);
    }

    /** @test */
    public function data_analysis_generates_action_suggestions_for_low_stock()
    {
        // Create low stock inventory item
        InventoryItem::create([
            'user_id' => $this->farmer->id,
            'name' => 'Seeds',
            'category' => 'seeds',
            'current_stock' => 5,
            'minimum_stock' => 20,
            'unit' => 'kg',
        ]);

        $response = $this->actingAs($this->farmer, 'sanctum')
            ->getJson('/api/analytics/data-analysis');

        $response->assertStatus(200);

        $data = $response->json();

        // Check that low stock suggestion is generated
        $inventorySuggestions = collect($data['action_suggestions'])
            ->where('category', 'inventory');

        $this->assertTrue($inventorySuggestions->count() > 0);
    }

    /** @test */
    public function data_analysis_respects_date_range_filter()
    {
        // Create expense outside date range
        Expense::create([
            'planting_id' => $this->planting->id,
            'user_id' => $this->farmer->id,
            'category' => 'fertilizer',
            'description' => 'Old expense',
            'date' => now()->subMonths(6),
            'amount' => 1000,
        ]);

        // Create expense inside date range
        Expense::create([
            'planting_id' => $this->planting->id,
            'user_id' => $this->farmer->id,
            'category' => 'fertilizer',
            'description' => 'Recent expense',
            'date' => now()->subDays(30),
            'amount' => 500,
        ]);

        $response = $this->actingAs($this->farmer, 'sanctum')
            ->getJson('/api/analytics/data-analysis?' . http_build_query([
                'start_date' => now()->subMonths(2)->format('Y-m-d'),
                'end_date' => now()->format('Y-m-d'),
            ]));

        $response->assertStatus(200);

        $data = $response->json();

        // Only the recent expense should be included
        $this->assertEquals(500, $data['expenses']['total_expenses']);
    }

    /** @test */
    public function unauthorized_user_cannot_access_data_analysis()
    {
        $response = $this->getJson('/api/analytics/data-analysis');

        $response->assertStatus(401);
    }

    /** @test */
    public function buyer_cannot_access_data_analysis()
    {
        $buyer = User::factory()->create([
            'role' => 'buyer',
            'phone_verified_at' => now(),
        ]);

        $response = $this->actingAs($buyer, 'sanctum')
            ->getJson('/api/analytics/data-analysis');

        $response->assertStatus(403);
    }
}
