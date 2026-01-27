<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Task;
use App\Models\Planting;
use App\Models\Laborer;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    public function definition()
    {
        return [
            'planting_id' => Planting::factory(),
            'task_type' => fake()->randomElement(['watering', 'fertilizing', 'weeding', 'pest_control', 'harvesting']),
            'due_date' => now()->addDays(rand(1, 10)),
            'description' => fake()->sentence(),
            'status' => 'pending',
            'assigned_to' => Laborer::factory(),
            'payment_type' => 'wage',
            'wage_amount' => fake()->randomFloat(2, 300, 600),
            'unit' => 'day',
            'quantity' => 1,
            'unit_price' => 0,
        ];
    }
}
