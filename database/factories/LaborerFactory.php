<?php

namespace Database\Factories;

use App\Models\Laborer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Laborer>
 */
class LaborerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->safeEmail(),
            'skill_level' => fake()->randomElement(['beginner', 'intermediate', 'advanced', 'expert']),
            'rate' => fake()->randomFloat(2, 300, 800),
            'rate_type' => fake()->randomElement(['daily', 'per_job']),
            'status' => 'active',
            'hire_date' => now(),
        ];
    }
}