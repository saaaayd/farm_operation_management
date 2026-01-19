<?php

namespace Tests\Feature\Labor;

use App\Models\Farm;
use App\Models\Field;
use App\Models\Laborer;
use App\Models\LaborerGroup;
use App\Models\Planting;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskPieceRateTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $planting;
    private $laborer;
    private $group;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->actingAs($this->user);

        $farm = Farm::factory()->create(['user_id' => $this->user->id]);
        $field = Field::factory()->create(['user_id' => $this->user->id, 'farm_id' => $farm->id]);
        $this->planting = Planting::factory()->create(['field_id' => $field->id]);

        $this->laborer = Laborer::factory()->create(['user_id' => $this->user->id, 'rate' => 500]);
        $this->group = LaborerGroup::create([
            'name' => 'Team Alpha',
            'user_id' => $this->user->id,
            'color' => '#ffffff'
        ]);
        $this->group->laborers()->attach($this->laborer);
    }

    /** @test */
    public function can_create_task_with_piece_rate()
    {
        $response = $this->postJson(route('tasks.store'), [
            'planting_id' => $this->planting->id,
            'task_type' => Task::TYPE_HARVESTING,
            'due_date' => now()->addDay()->toDateString(),
            'description' => 'Harvest rice',
            'payment_type' => Task::PAYMENT_TYPE_PIECE_RATE,
            'quantity' => 100,
            'unit' => 'bundles',
            'unit_price' => 5.50,
            'assigned_to' => $this->laborer->id,
        ]);

        $response->assertCreated();

        $task = Task::first();
        $this->assertEquals('piece_rate', $task->payment_type);
        $this->assertEquals(100, $task->quantity);
        $this->assertEquals(5.50, $task->unit_price);
        $this->assertEquals('bundles', $task->unit);
        // Verify calculated wage amount: 100 * 5.50 = 550
        $this->assertEquals(550.00, $task->wage_amount);
    }

    /** @test */
    public function can_create_group_task_with_piece_rate_and_distribute_wage()
    {
        // Add another laborer to group
        $laborer2 = Laborer::factory()->create(['user_id' => $this->user->id]);
        $this->group->laborers()->attach($laborer2);

        $response = $this->postJson(route('tasks.store'), [
            'planting_id' => $this->planting->id,
            'task_type' => Task::TYPE_HARVESTING,
            'due_date' => now()->addDay()->toDateString(),
            'description' => 'Group harvest',
            'payment_type' => Task::PAYMENT_TYPE_PIECE_RATE,
            'quantity' => 200, // 200 bundles
            'unit' => 'bundles',
            'unit_price' => 10,
            'laborer_group_id' => $this->group->id,
        ]);

        $response->assertCreated();
        $task = Task::latest()->first();

        // Total wage = 200 * 10 = 2000
        $this->assertEquals(2000, $task->wage_amount);

        // Mark completed to trigger wage distribution
        $response = $this->postJson(route('tasks.complete', $task));
        $response->assertOk();

        // Check labor wages
        // Should be 2 records (one for each laborer)
        $this->assertDatabaseCount('labor_wages', 2);

        // Each should get 2000 / 2 = 1000
        $wage1 = \App\Models\LaborWage::where('laborer_id', $this->laborer->id)->first();
        $wage2 = \App\Models\LaborWage::where('laborer_id', $laborer2->id)->first();

        $this->assertEquals(1000, $wage1->wage_amount);
        $this->assertEquals(1000, $wage2->wage_amount);
    }
}
