<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Laborer;
use App\Models\Field;
use App\Models\Planting;
use App\Models\RiceVariety;
use App\Models\Task;
use App\Models\Expense;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class LaborTaskTest extends TestCase
{
    use DatabaseTransactions;

    protected $farmer;
    protected $laborer;
    protected $planting;

    protected function setUp(): void
    {
        parent::setUp();
        $this->farmer = User::factory()->create(['role' => 'farmer']);

        $field = Field::factory()->create(['user_id' => $this->farmer->id]);
        $variety = RiceVariety::factory()->create();

        $this->planting = Planting::create([
            'field_id' => $field->id,
            'rice_variety_id' => $variety->id,
            'planting_date' => now(),
            'expected_harvest_date' => now()->addDays(120),
            'status' => 'planted',
            'planting_method' => 'transplanting',
            'area_planted' => 1.5,
            'crop_type' => 'rice',
            'season' => 'dry',
        ]);

        $this->laborer = Laborer::factory()->create([
            'user_id' => $this->farmer->id,
            'rate' => 500,
            'rate_type' => 'daily'
        ]);
    }

    public function test_can_create_task()
    {
        $response = $this->actingAs($this->farmer)
            ->postJson('/api/tasks', [
                'planting_id' => $this->planting->id,
                'task_type' => 'weeding',
                'due_date' => now()->addDays(2)->toDateString(),
                'description' => 'Remove weeds from field',
                'assigned_to' => $this->laborer->id,
                'wage_amount' => 500 // Specific wage for this task if needed
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('task.task_type', 'weeding');

        $this->assertDatabaseHas('tasks', [
            'planting_id' => $this->planting->id,
            'assigned_to' => $this->laborer->id,
            'status' => 'pending'
        ]);
    }

    public function test_completing_task_creates_expense()
    {
        $task = Task::create([
            'planting_id' => $this->planting->id,
            'task_type' => 'fertilizing',
            'due_date' => now(),
            'description' => 'Apply fertilizer',
            'assigned_to' => $this->laborer->id,
            'wage_amount' => 500, // Daily rate logic in controller uses this if present
            'status' => 'pending'
        ]);

        $response = $this->actingAs($this->farmer)
            ->postJson("/api/tasks/{$task->id}/complete", [
                'hours_worked' => 8
            ]);

        $response->assertStatus(200);

        $this->assertEquals('completed', $task->fresh()->status);

        // Verify Expense
        $this->assertDatabaseHas('expenses', [
            'user_id' => $this->farmer->id,
            'amount' => 500,
            'category' => 'labor', // Expense::CATEGORY_LABOR
            'related_entity_type' => 'task', // Expense::ENTITY_TYPE_TASK
            'related_entity_id' => $task->id
        ]);

        // Verify LaborWage
        $this->assertDatabaseHas('labor_wages', [
            'laborer_id' => $this->laborer->id,
            'task_id' => $task->id,
            'wage_amount' => 500
        ]);
    }

    public function test_can_list_laborers()
    {
        $response = $this->actingAs($this->farmer)
            ->getJson('/api/laborers');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'laborers');
    }
}
