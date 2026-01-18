<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Expense;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_expense_breakdown()
    {
        $farmer = User::factory()->create(['role' => 'farmer']);

        Expense::create([
            'user_id' => $farmer->id,
            'amount' => 5000,
            'category' => 'fertilizer',
            'description' => 'Bag of Urea',
            'date' => now(),
            'notes' => 'Urea'
        ]);

        Expense::create([
            'user_id' => $farmer->id,
            'amount' => 2000,
            'category' => 'labor',
            'description' => 'Planting wages',
            'date' => now(),
            'notes' => 'Planting'
        ]);

        $response = $this->actingAs($farmer)
            ->getJson('/api/reports/financial?period=30');

        $response->assertStatus(200);
        // Assert total expenses matches 7000
        $response->assertJsonPath('data.financial_summary.total_expenses', 7000);
    }
}
