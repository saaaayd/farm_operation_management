<?php

namespace Tests\Feature\Financial;

use App\Models\User;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpenseTest extends TestCase
{
    use RefreshDatabase;

    protected $farmer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->farmer = User::factory()->create(['role' => 'farmer']);
    }

    public function test_can_create_expense()
    {
        $data = [
            'description' => 'Bag of Seeds',
            'amount' => 1500.50,
            'category' => 'seeds',
            'date' => now()->toDateString(),
            'payment_method' => 'cash'
        ];

        $response = $this->actingAs($this->farmer)
            ->postJson('/api/expenses', $data);

        $response->assertStatus(201)
            ->assertJsonPath('expense.amount', '1500.50');

        $this->assertDatabaseHas('expenses', [
            'description' => 'Bag of Seeds',
            'user_id' => $this->farmer->id,
            'amount' => 1500.50
        ]);
    }

    public function test_can_list_expenses_with_date_filter()
    {
        Expense::factory()->create([
            'user_id' => $this->farmer->id,
            'date' => now()->subDays(10)->toDateString(),
            'description' => 'Old Expense'
        ]);

        Expense::factory()->create([
            'user_id' => $this->farmer->id,
            'date' => now()->toDateString(),
            'description' => 'New Expense'
        ]);

        $response = $this->actingAs($this->farmer)
            ->getJson('/api/expenses?date_from=' . now()->toDateString());

        $response->assertStatus(200)
            ->assertJsonCount(1, 'expenses')
            ->assertJsonPath('expenses.0.description', 'New Expense');
    }

    public function test_summary_aggregation()
    {
        Expense::factory()->create([
            'user_id' => $this->farmer->id,
            'category' => 'fertilizer',
            'amount' => 100
        ]);

        Expense::factory()->create([
            'user_id' => $this->farmer->id,
            'category' => 'fertilizer',
            'amount' => 200
        ]);

        Expense::factory()->create([
            'user_id' => $this->farmer->id,
            'category' => 'seeds',
            'amount' => 50
        ]);

        $response = $this->actingAs($this->farmer)
            ->getJson('/api/expenses/summary');

        $response->assertStatus(200)
            ->assertJsonPath('total_amount', 350) // Ensure string match if decimal
            ->assertJsonFragment(['category' => 'fertilizer', 'total_amount' => '300.00'])
            ->assertJsonFragment(['category' => 'seeds', 'total_amount' => '50.00']);
    }
}
