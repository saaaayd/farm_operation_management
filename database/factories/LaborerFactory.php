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
            'contact' => fake()->phoneNumber(),
            'rate' => fake()->randomFloat(2, 300, 800),
            'rate_type' => fake()->randomElement(['daily', 'per_job']),
        ];
    }
}