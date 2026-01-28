<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Planting;
use App\Models\Field;
use App\Models\RiceVariety;

class PlantingFactory extends Factory
{
    protected $model = Planting::class;

    public function definition()
    {
        return [
            'field_id' => Field::factory(),
            'rice_variety_id' => RiceVariety::factory(),
            'crop_type' => 'rice',
            'planting_date' => now()->subDays(rand(10, 60)),
            'expected_harvest_date' => now()->addDays(rand(60, 100)),
            'status' => 'growing',
            'planting_method' => 'direct_seeding',
            'area_planted' => fake()->randomFloat(2, 0.5, 5.0),
            'season' => 'dry',
            'seed_rate' => fake()->randomFloat(2, 40, 80),
            'seed_unit' => 'kg',
        ];
    }
}
