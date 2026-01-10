<?php

namespace Database\Factories;

use App\Models\RiceProduct;
use App\Models\RiceVariety;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class RiceProductFactory extends Factory
{
    protected $model = RiceProduct::class;

    public function definition()
    {
        return [
            'farmer_id' => User::factory(),
            'rice_variety_id' => RiceVariety::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->paragraph,
            'quantity_available' => $this->faker->randomFloat(2, 10, 1000),
            'unit' => 'kg',
            'price_per_unit' => $this->faker->randomFloat(2, 20, 100),
            'quality_grade' => $this->faker->randomElement(['premium', 'grade_a', 'grade_b', 'commercial']),
            'is_available' => true,
            'production_status' => 'available',
        ];
    }
}
