<?php

namespace Database\Factories;

use App\Models\Field;

use App\Models\User;
use App\Models\Farm;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Field>
 */
class FieldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $soilTypes = ['Loamy', 'Clay', 'Sandy', 'Silty', 'Peaty', 'Chalky'];

        return [
            'user_id' => User::factory(),
            'farm_id' => Farm::factory(),
            'name' => fake()->randomElement(['North Field', 'South Field', 'East Field', 'West Field']) . ' ' . fake()->numerify('##'),
            'location' => [
                'lat' => fake()->latitude(),
                'lon' => fake()->longitude(),
                'address' => fake()->streetAddress() . ', ' . fake()->city()
            ],
            'soil_type' => fake()->randomElement($soilTypes),
            'size' => fake()->randomFloat(1, 5, 100),
        ];
    }
}