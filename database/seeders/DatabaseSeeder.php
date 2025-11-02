<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Field;
use App\Models\Planting;
use App\Models\Task;
use App\Models\Laborer;
use App\Models\InventoryItem;
use App\Models\WeatherLog;
use App\Models\Harvest;
use App\Models\Expense;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Clear existing data (optional - uncomment if you want to clear on each seed)
        // DB::statement('SET CONSTRAINTS ALL DEFERRED');
        // Expense::truncate();
        // Harvest::truncate();
        // WeatherLog::truncate();
        // Task::truncate();
        // InventoryItem::truncate();
        // Planting::truncate();
        // Field::truncate();
        // Laborer::truncate();
        // User::truncate();

        // Create admin user (or find existing)
        $admin = User::firstOrCreate(
            ['email' => 'admin@farmops.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+1-555-0100',
                'address' => [
                    'street' => '123 Admin Street',
                    'city' => 'Admin City',
                    'state' => 'AC',
                    'country' => 'USA',
                    'postal_code' => '12345'
                ]
            ]
        );

        
        // Create farmers
        $farmer1 = User::firstOrCreate(
            ['email' => 'john@farmops.com'],
            [
                'name' => 'John Farmer',
                'password' => Hash::make('password'),
                'role' => 'farmer',
                'phone' => '+1-555-0101',
                'address' => [
                    'street' => '456 Farm Road',
                    'city' => 'Rural Town',
                    'state' => 'RT',
                    'country' => 'USA',
                    'postal_code' => '54321'
                ]
            ]
        );

        $farmer2 = User::firstOrCreate(
            ['email' => 'mary@farmops.com'],
            [
                'name' => 'Mary Grower',
                'password' => Hash::make('password'),
                'role' => 'farmer',
                'phone' => '+1-555-0102',
                'address' => [
                    'street' => '789 Agriculture Ave',
                    'city' => 'Farm Valley',
                    'state' => 'FV',
                    'country' => 'USA',
                    'postal_code' => '67890'
                ]
            ]
        );

        $farm1 = \App\Models\Farm::create([
            'user_id' => $farmer1->id,
            'name' => "John's Farm",
            'farm_coordinates' => json_encode([
                'lat' => 40.7306,
                'lon' => -73.9352,
            ]),
            'size' => 50.0
        ]);
        
        $farm2 = \App\Models\Farm::create([
            'user_id' => $farmer2->id,
            'name' => "Mary's Farm",
            'farm_coordinates' => json_encode([
                'lat' => 50.7306,
                'lon' => -83.9352,
            ]),
            'size' => 65.0
        ]);

        // Create buyers
        $buyer1 = User::firstOrCreate(
            ['email' => 'alice@farmops.com'],
            [
                'name' => 'Alice Buyer',
                'password' => Hash::make('password'),
                'role' => 'buyer',
                'phone' => '+1-555-0201',
                'address' => [
                    'street' => '321 Market Street',
                    'city' => 'Commerce City',
                    'state' => 'CC',
                    'country' => 'USA',
                    'postal_code' => '13579'
                ]
            ]
        );

        $buyer2 = User::firstOrCreate(
            ['email' => 'bob@farmops.com'],
            [
                'name' => 'Bob Merchant',
                'password' => Hash::make('password'),
                'role' => 'buyer',
                'phone' => '+1-555-0202',
                'address' => [
                    'street' => '654 Trade Boulevard',
                    'city' => 'Business Town',
                    'state' => 'BT',
                    'country' => 'USA',
                    'postal_code' => '24680'
                ]
            ]
        );

        // Skip if fields already exist for these users
        if (Field::where('user_id', $farmer1->id)->count() === 0) {
            // Create fields for farmers
            $field1 = Field::create([
                'user_id' => $farmer1->id,
                'farm_id' => $farm1->id, // ✅ added
                'location' => [
                    'lat' => 40.7128,
                    'lon' => -74.0060,
                    'address' => "North Field, John's Farm"
                ],
                'soil_type' => 'Loamy',
                'size' => 25.5
            ]);
            
            $field2 = Field::create([
                'user_id' => $farmer1->id,
                'farm_id' => $farm1->id,
                'location' => [
                    'lat' => 40.7580,
                    'lon' => -73.9855,
                    'address' => "South Field, John's Farm"
                ],
                'soil_type' => 'Clay',
                'size' => 18.2
            ]);
            
            $field3 = Field::create([
                'user_id' => $farmer2->id,
                'farm_id' => $farm2->id,
                'location' => [
                    'lat' => 41.8781,
                    'lon' => -87.6298,
                    'address' => "East Field, Mary's Farm"
                ],
                'soil_type' => 'Sandy',
                'size' => 32.0
            ]);
            

            // Create laborers
            $laborer1 = Laborer::create([
                'name' => 'Tom Worker',
                'contact' => '+1-555-0301',
                'hourly_rate' => 15.50
            ]);

            $laborer2 = Laborer::create([
                'name' => 'Sarah Helper',
                'contact' => '+1-555-0302',
                'hourly_rate' => 17.00
            ]);

            $laborer3 = Laborer::create([
                'name' => 'Mike Laborer',
                'contact' => '+1-555-0303',
                'hourly_rate' => 16.25
            ]);

            // Create plantings
            $planting1 = Planting::create([
                'field_id' => $field1->id,
                'crop_type' => 'Corn',
                'planting_date' => now()->subDays(45),
                'expected_harvest_date' => now()->addDays(75),
                'status' => 'growing'
            ]);

            $planting2 = Planting::create([
                'field_id' => $field1->id,
                'crop_type' => 'Soybeans',
                'planting_date' => now()->subDays(30),
                'expected_harvest_date' => now()->addDays(90),
                'status' => 'growing'
            ]);

            $planting3 = Planting::create([
                'field_id' => $field2->id,
                'crop_type' => 'Wheat',
                'planting_date' => now()->subDays(60),
                'expected_harvest_date' => now()->addDays(30),
                'status' => 'ready'
            ]);

            $planting4 = Planting::create([
                'field_id' => $field3->id,
                'crop_type' => 'Tomatoes',
                'planting_date' => now()->subDays(75),
                'expected_harvest_date' => now()->subDays(5),
                'status' => 'harvested'
            ]);

            // Create tasks
            Task::create([
                'planting_id' => $planting1->id,
                'task_type' => 'watering',
                'due_date' => now()->addDays(2),
                'description' => 'Water corn field - section A',
                'status' => 'pending',
                'assigned_to' => $laborer1->id
            ]);

            Task::create([
                'planting_id' => $planting1->id,
                'task_type' => 'fertilizing',
                'due_date' => now()->addDays(5),
                'description' => 'Apply nitrogen fertilizer to corn',
                'status' => 'pending',
                'assigned_to' => $laborer2->id
            ]);

            Task::create([
                'planting_id' => $planting2->id,
                'task_type' => 'weeding',
                'due_date' => now()->addDays(1),
                'description' => 'Remove weeds from soybean rows',
                'status' => 'pending',
                'assigned_to' => $laborer1->id
            ]);

            Task::create([
                'planting_id' => $planting3->id,
                'task_type' => 'harvesting',
                'due_date' => now()->addDays(3),
                'description' => 'Harvest wheat - ready for collection',
                'status' => 'pending',
                'assigned_to' => $laborer3->id
            ]);

            Task::create([
                'planting_id' => $planting4->id,
                'task_type' => 'harvesting',
                'due_date' => now()->subDays(10),
                'description' => 'Harvest tomatoes - completed',
                'status' => 'completed',
                'assigned_to' => $laborer2->id
            ]);

            // Create inventory items
            InventoryItem::create([
                'name' => 'Corn Seeds',
                'category' => 'seeds',
                'quantity' => 50.0,
                'price' => 25.00,
                'unit' => 'kg',
                'min_stock' => 10.0
            ]);

            InventoryItem::create([
                'name' => 'Nitrogen Fertilizer',
                'category' => 'fertilizer',
                'quantity' => 8.0,
                'price' => 45.00,
                'unit' => 'bags',
                'min_stock' => 5.0
            ]);

            InventoryItem::create([
                'name' => 'Pesticide Spray',
                'category' => 'pesticide',
                'quantity' => 3.0,
                'price' => 85.00,
                'unit' => 'liters',
                'min_stock' => 2.0
            ]);

            InventoryItem::create([
                'name' => 'Fresh Tomatoes',
                'category' => 'produce',
                'quantity' => 150.0,
                'price' => 4.50,
                'unit' => 'kg',
                'min_stock' => 0.0
            ]);

            InventoryItem::create([
                'name' => 'Organic Corn',
                'category' => 'produce',
                'quantity' => 200.0,
                'price' => 3.25,
                'unit' => 'kg',
                'min_stock' => 0.0
            ]);

            // Create harvests
            $harvest1 = Harvest::create([
                'planting_id' => $planting4->id,
                'yield' => 125.5,
                'harvest_date' => now()->subDays(7),
                'quality' => 'excellent'
            ]);

            // Create expenses
            Expense::create([
                'description' => 'Corn seeds purchase',
                'amount' => 125.00,
                'category' => 'seeds',
                'date' => now()->subDays(50),
                'planting_id' => $planting1->id
            ]);

            Expense::create([
                'description' => 'Fertilizer application',
                'amount' => 85.00,
                'category' => 'fertilizer',
                'date' => now()->subDays(35),
                'planting_id' => $planting1->id
            ]);

            Expense::create([
                'description' => 'Labor costs - weeding',
                'amount' => 120.00,
                'category' => 'labor',
                'date' => now()->subDays(20),
                'planting_id' => $planting2->id
            ]);

            // Create weather logs
            $fields = [$field1, $field2, $field3];
            $conditions = ['clear', 'cloudy', 'rainy', 'clear', 'cloudy'];
            
            foreach ($fields as $field) {
                for ($i = 0; $i < 7; $i++) {
                    WeatherLog::create([
                        'field_id' => $field->id,
                        'temperature' => rand(15, 30) + (rand(0, 9) / 10),
                        'humidity' => rand(40, 80),
                        'wind_speed' => rand(5, 20) + (rand(0, 9) / 10),
                        'conditions' => $conditions[array_rand($conditions)],
                        'recorded_at' => now()->subDays($i)->setTime(rand(6, 18), 0, 0)
                    ]);
                }
            }

            echo "Database seeded successfully!\n";
        } else {
            echo "Data already exists. Users updated/verified.\n";
        }

        echo "\nUsers available:\n";
        echo "- Admin: admin@farmops.com (password: password)\n";
        echo "- Farmer 1: john@farmops.com (password: password)\n";
        echo "- Farmer 2: mary@farmops.com (password: password)\n";
        echo "- Buyer 1: alice@farmops.com (password: password)\n";
        echo "- Buyer 2: bob@farmops.com (password: password)\n";
    }
}