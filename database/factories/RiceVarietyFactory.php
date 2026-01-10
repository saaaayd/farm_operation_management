<?php

namespace Database\Factories;

use App\Models\RiceVariety;
use Illuminate\Database\Eloquent\Factories\Factory;

class RiceVarietyFactory extends Factory
{
    protected $model = RiceVariety::class;

    public function definition()
    {
        return [
            'variety_code' => $this->faker->unique()->bothify('RICE-####'),
            'name' => $this->faker->word,
            'description' => $this->faker->sentence,
            'maturity_days' => $this->faker->numberBetween(90, 150),
            'average_yield_per_hectare' => $this->faker->randomFloat(2, 3, 8),
            'season' => $this->faker->randomElement(['wet', 'dry']),
            'grain_type' => $this->faker->randomElement(['long', 'medium', 'short']),
            'resistance_level' => $this->faker->randomElement(['low', 'medium', 'high']),
            'is_active' => true,
        ];
    }
}
