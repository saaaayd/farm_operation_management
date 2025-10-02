<?php

namespace Database\Factories;

use App\Models\Farm;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Farm>
 */
class FarmFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->company() . ' Farm',
            'location' => [
                'lat' => fake()->latitude(),
                'lon' => fake()->longitude(),
                'address' => fake()->streetAddress() . ', ' . fake()->city() . ', ' . fake()->stateAbbr()
            ],
            'size' => fake()->randomFloat(2, 10, 500),
            'description' => fake()->optional()->sentence(),
        ];
    }
}