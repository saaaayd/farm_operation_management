<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Laborer;
use App\Models\Task;
use App\Models\Field;
use App\Models\Planting;
use App\Models\RiceVariety;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LaborTaskTest extends TestCase
{
    use RefreshDatabase;

    protected $farmer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->farmer = User::factory()->create(['role' => 'farmer']);
    }

    public function test_can_add_laborer()
    {
        $laborerData = [
            'name' => 'Juan Dela Cruz',
            'phone' => '09123456789',
            'role' => 'general',
            'skill_level' => 'intermediate',
            'rate_type' => 'daily',
            'status' => 'active',
            'hire_date' => now()->toDateString()
        ];

        $response = $this->actingAs($this->farmer)
            ->postJson('/api/laborers', $laborerData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('laborers', ['name' => 'Juan Dela Cruz']);
    }

    public function test_can_create_task_and_assign()
    {
        $laborer = Laborer::create([
            'user_id' => $this->farmer->id,
            'name' => 'Test Laborer',
            'phone' => '09999999999',
            'status' => 'active',
            'skill_level' => 'intermediate',
            'rate_type' => 'daily',
            'hire_date' => now()
        ]);

        $field = Field::factory()->create([
            'user_id' => $this->farmer->id,
            'field_coordinates' => [['lat' => 10, 'lng' => 120]],
            'size' => 1.0
        ]);

        $planting = Planting::create([
            'user_id' => $this->farmer->id,
            'field_id' => $field->id,
            'planting_date' => now(),
            'status' => 'planted',
            'quantity_planted' => 10,
            'rice_variety_id' => RiceVariety::factory()->create()->id,
            'expected_harvest_date' => now()->addMonths(4),
            'area_planted' => 1.5,
            'season' => 'wet'
        ]);

        $taskData = [
            'title' => 'Rice Planting',
            'description' => 'Plant basics',
            'due_date' => now()->addDays(2)->toDateTimeString(),
            'priority' => 'high',
            'status' => 'pending',
            'planting_id' => $planting->id,
            'task_type' => 'maintenance', // Changed from invalid 'planting'
            'assigned_laborers' => [$laborer->id],
            'wage_type' => 'daily',
            'wage_amount' => 500
        ];

        $response = $this->actingAs($this->farmer)
            ->postJson('/api/tasks', $taskData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', ['description' => 'Plant basics']);
    }

    public function test_completing_task_records_expense()
    {
        $laborer = Laborer::create([
            'user_id' => $this->farmer->id,
            'name' => 'Test Laborer 2',
            'status' => 'active',
            'skill_level' => 'intermediate',
            'rate_type' => 'daily',
            'hire_date' => now()
        ]);

        $field = Field::factory()->create(['user_id' => $this->farmer->id, 'size' => 1, 'field_coordinates' => []]);
        $planting = Planting::create([
            'user_id' => $this->farmer->id,
            'field_id' => $field->id,
            'planting_date' => now(),
            'rice_variety_id' => RiceVariety::factory()->create()->id,
            'expected_harvest_date' => now()->addMonths(4),
            'area_planted' => 1.5,
            'season' => 'dry'
        ]);

        $task = new Task();
        $task->planting_id = $planting->id;
        $task->task_type = 'harvesting';
        $task->description = ' Harvest';
        $task->status = 'pending';
        $task->wage_amount = 1000;
        $task->due_date = now()->addDays(5); // Added required field
        $task->save();

        $task->assigned_to = $laborer->id;
        $task->save();

        $response = $this->actingAs($this->farmer)
            ->postJson("/api/tasks/{$task->id}/complete");

        $response->assertStatus(200);
        $this->assertEquals('completed', $task->refresh()->status);
    }
}
