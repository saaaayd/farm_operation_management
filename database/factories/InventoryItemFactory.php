<?php

namespace Database\Factories;

use App\Models\InventoryItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InventoryItem>
 */
class InventoryItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $categories = ['seeds', 'fertilizer', 'pesticide', 'produce', 'equipment', 'tools'];
        $units = ['kg', 'bags', 'liters', 'pieces', 'units'];
        
        $category = fake()->randomElement($categories);
        
        // Generate appropriate names based on category
        $names = [
            'seeds' => ['Corn Seeds', 'Wheat Seeds', 'Soybean Seeds', 'Rice Seeds', 'Tomato Seeds'],
            'fertilizer' => ['Nitrogen Fertilizer', 'Phosphate Fertilizer', 'Organic Compost', 'NPK Fertilizer'],
            'pesticide' => ['Pesticide Spray', 'Herbicide', 'Fungicide', 'Insecticide'],
            'produce' => ['Fresh Tomatoes', 'Organic Corn', 'Wheat Grain', 'Soybeans', 'Rice'],
            'equipment' => ['Tractor', 'Plow', 'Harvester', 'Irrigation System'],
            'tools' => ['Hoe', 'Rake', 'Shovel', 'Pruning Shears']
        ];
        
        return [
            'name' => fake()->randomElement($names[$category]),
            'category' => $category,
            'quantity' => fake()->randomFloat(1, 0, 500),
            'price' => fake()->randomFloat(2, 5, 200),
            'unit' => fake()->randomElement($units),
            'min_stock' => fake()->randomFloat(1, 0, 50),
        ];
    }
}