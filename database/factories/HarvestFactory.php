<?php

namespace Database\Factories;

use App\Models\Harvest;
use App\Models\Planting;
use Illuminate\Database\Eloquent\Factories\Factory;

class HarvestFactory extends Factory
{
    protected $model = Harvest::class;

    public function definition()
    {
        return [
            'planting_id' => Planting::factory(),
            'quantity' => $this->faker->randomFloat(2, 100, 1000),
            'yield' => $this->faker->randomFloat(2, 100, 1000),
            'unit' => 'kg',
            'harvest_date' => $this->faker->date(),
            'quality' => 'good',
            'quality_grade' => 'A',
            'price_per_unit' => $this->faker->randomFloat(2, 10, 50),
            'total_value' => $this->faker->randomFloat(2, 1000, 50000),
        ];
    }
}
