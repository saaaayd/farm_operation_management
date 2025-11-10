<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Farm;
use App\Models\Field;
use App\Models\Planting;
use App\Models\Task;
use App\Models\Laborer;
use App\Models\InventoryItem;
use App\Models\WeatherLog;
use App\Models\Harvest;
use App\Models\Expense;
use App\Models\RiceVariety;
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

        $userPasswords = [
            'admin@farmops.com' => 'admin123',
            'john@farmops.com' => 'john123',
            'mary@farmops.com' => 'mary123',
            'alice@farmops.com' => 'alice123',
            'bob@farmops.com' => 'bob123',
        ];

        // Create admin user (or update existing)
        $admin = User::updateOrCreate(
            ['email' => 'admin@farmops.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make($userPasswords['admin@farmops.com']),
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
        $farmer1 = User::updateOrCreate(
            ['email' => 'john@farmops.com'],
            [
                'name' => 'John Farmer',
                'password' => Hash::make($userPasswords['john@farmops.com']),
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

        $farmer2 = User::updateOrCreate(
            ['email' => 'mary@farmops.com'],
            [
                'name' => 'Mary Grower',
                'password' => Hash::make($userPasswords['mary@farmops.com']),
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

        $varietyCorn = RiceVariety::updateOrCreate(
            ['variety_code' => 'GEN-CORN'],
            [
                'name' => 'General Corn Hybrid',
                'description' => 'Demo variety used for corn-based planting scenarios.',
                'maturity_days' => 110,
                'average_yield_per_hectare' => 5.20,
                'season' => 'both',
                'grain_type' => 'long',
                'resistance_level' => 'medium',
                'characteristics' => [
                    'notes' => 'Suited for temperate climates',
                ],
                'is_active' => true,
            ]
        );

        $varietySoy = RiceVariety::updateOrCreate(
            ['variety_code' => 'GEN-SOY'],
            [
                'name' => 'Versatile Soy Hybrid',
                'description' => 'Demo variety supporting soybean rotations.',
                'maturity_days' => 95,
                'average_yield_per_hectare' => 3.80,
                'season' => 'wet',
                'grain_type' => 'medium',
                'resistance_level' => 'high',
                'characteristics' => [
                    'notes' => 'High resistance to common pests',
                ],
                'is_active' => true,
            ]
        );

        $varietyWheat = RiceVariety::updateOrCreate(
            ['variety_code' => 'GEN-WHEAT'],
            [
                'name' => 'Stable Wheat Variety',
                'description' => 'Demo variety representing wheat operations.',
                'maturity_days' => 120,
                'average_yield_per_hectare' => 4.60,
                'season' => 'dry',
                'grain_type' => 'short',
                'resistance_level' => 'medium',
                'characteristics' => [
                    'notes' => 'Performs well in cooler temperatures',
                ],
                'is_active' => true,
            ]
        );

        $varietyTomato = RiceVariety::updateOrCreate(
            ['variety_code' => 'GEN-TOMA'],
            [
                'name' => 'Greenhouse Tomato Hybrid',
                'description' => 'Demo variety covering tomato production.',
                'maturity_days' => 85,
                'average_yield_per_hectare' => 2.90,
                'season' => 'both',
                'grain_type' => 'medium',
                'resistance_level' => 'high',
                'characteristics' => [
                    'notes' => 'Great for all-season greenhouse setups',
                ],
                'is_active' => true,
            ]
        );

        $farm1 = Farm::updateOrCreate(
            [
                'user_id' => $farmer1->id,
                'name' => "John's Farm",
            ],
            [
                'location' => 'Queens, NY',
                'farm_coordinates' => [
                    'lat' => 40.7306,
                    'lon' => -73.9352,
                ],
                'total_area' => 50.0,
                'cultivated_area' => 40.0,
                'soil_type' => 'loam',
                'soil_ph' => 6.4,
                'water_source' => 'well',
                'irrigation_type' => 'sprinkler',
                'is_setup_complete' => true,
            ]
        );

        $farm2 = Farm::updateOrCreate(
            [
                'user_id' => $farmer2->id,
                'name' => "Mary's Farm",
            ],
            [
                'location' => 'Farm Valley, IL',
                'farm_coordinates' => [
                    'lat' => 41.8781,
                    'lon' => -87.6298,
                ],
                'total_area' => 65.0,
                'cultivated_area' => 52.0,
                'soil_type' => 'clay_loam',
                'soil_ph' => 6.1,
                'water_source' => 'river',
                'irrigation_type' => 'drip',
                'is_setup_complete' => true,
            ]
        );

        // Create buyers
        $buyer1 = User::updateOrCreate(
            ['email' => 'alice@farmops.com'],
            [
                'name' => 'Alice Buyer',
                'password' => Hash::make($userPasswords['alice@farmops.com']),
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

        $buyer2 = User::updateOrCreate(
            ['email' => 'bob@farmops.com'],
            [
                'name' => 'Bob Merchant',
                'password' => Hash::make($userPasswords['bob@farmops.com']),
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
            $field1 = Field::updateOrCreate([
                'user_id' => $farmer1->id,
                'farm_id' => $farm1->id,
                'name' => 'North Field',
            ], [
                'location' => [
                    'lat' => 40.7128,
                    'lon' => -74.0060,
                    'address' => "North Field, John's Farm"
                ],
                'field_coordinates' => [
                    'lat' => 40.7128,
                    'lon' => -74.0060,
                ],
                'soil_type' => 'Loamy',
                'size' => 25.5,
                'water_access' => 'good',
                'drainage_quality' => 'good',
            ]);
            
            $field2 = Field::updateOrCreate([
                'user_id' => $farmer1->id,
                'farm_id' => $farm1->id,
                'name' => 'South Field',
            ], [
                'location' => [
                    'lat' => 40.7580,
                    'lon' => -73.9855,
                    'address' => "South Field, John's Farm"
                ],
                'field_coordinates' => [
                    'lat' => 40.7580,
                    'lon' => -73.9855,
                ],
                'soil_type' => 'Clay',
                'size' => 18.2,
                'water_access' => 'moderate',
                'drainage_quality' => 'moderate',
            ]);
            
            $field3 = Field::updateOrCreate([
                'user_id' => $farmer2->id,
                'farm_id' => $farm2->id,
                'name' => 'East Field',
            ], [
                'location' => [
                    'lat' => 41.8781,
                    'lon' => -87.6298,
                    'address' => "East Field, Mary's Farm"
                ],
                'field_coordinates' => [
                    'lat' => 41.8781,
                    'lon' => -87.6298,
                ],
                'soil_type' => 'Sandy',
                'size' => 32.0,
                'water_access' => 'good',
                'drainage_quality' => 'good',
            ]);
            

            // Create laborers
            $laborer1 = Laborer::updateOrCreate([
                'name' => 'Tom Worker',
            ], [
                'name' => 'Tom Worker',
                'contact' => '+1-555-0301',
                'hourly_rate' => 15.50
            ]);

            $laborer2 = Laborer::updateOrCreate([
                'name' => 'Sarah Helper',
            ], [
                'name' => 'Sarah Helper',
                'contact' => '+1-555-0302',
                'hourly_rate' => 17.00
            ]);

            $laborer3 = Laborer::updateOrCreate([
                'name' => 'Mike Laborer',
            ], [
                'name' => 'Mike Laborer',
                'contact' => '+1-555-0303',
                'hourly_rate' => 16.25
            ]);

            // Create plantings
            $planting1 = Planting::updateOrCreate(
                [
                    'field_id' => $field1->id,
                    'crop_type' => 'Corn',
                ],
                [
                    'crop_type' => 'Corn',
                    'rice_variety_id' => $varietyCorn->id,
                    'planting_date' => now()->subDays(45),
                    'expected_harvest_date' => now()->addDays(75),
                    'status' => Planting::STATUS_GROWING,
                    'planting_method' => 'transplanting',
                    'area_planted' => 12.5,
                    'season' => 'wet',
                ]
            );

            $planting2 = Planting::updateOrCreate(
                [
                    'field_id' => $field1->id,
                    'crop_type' => 'Soybeans',
                ],
                [
                    'crop_type' => 'Soybeans',
                    'rice_variety_id' => $varietySoy->id,
                    'planting_date' => now()->subDays(30),
                    'expected_harvest_date' => now()->addDays(90),
                    'status' => Planting::STATUS_GROWING,
                    'planting_method' => 'direct_seeding',
                    'area_planted' => 9.8,
                    'season' => 'wet',
                ]
            );

            $planting3 = Planting::updateOrCreate(
                [
                    'field_id' => $field2->id,
                    'crop_type' => 'Wheat',
                ],
                [
                    'crop_type' => 'Wheat',
                    'rice_variety_id' => $varietyWheat->id,
                    'planting_date' => now()->subDays(60),
                    'expected_harvest_date' => now()->addDays(30),
                    'status' => Planting::STATUS_READY,
                    'planting_method' => 'direct_seeding',
                    'area_planted' => 15.0,
                    'season' => 'dry',
                ]
            );

            $planting4 = Planting::updateOrCreate(
                [
                    'field_id' => $field3->id,
                    'crop_type' => 'Tomatoes',
                ],
                [
                    'crop_type' => 'Tomatoes',
                    'rice_variety_id' => $varietyTomato->id,
                    'planting_date' => now()->subDays(75),
                    'expected_harvest_date' => now()->subDays(5),
                    'actual_harvest_date' => now()->subDays(4),
                    'status' => Planting::STATUS_HARVESTED,
                    'planting_method' => 'transplanting',
                    'area_planted' => 10.6,
                    'season' => 'dry',
                ]
            );

            // Create tasks
            Task::updateOrCreate([
                'planting_id' => $planting1->id,
                'task_type' => 'watering',
            ], [
                'task_type' => 'watering',
                'due_date' => now()->addDays(2),
                'description' => 'Water corn field - section A',
                'status' => 'pending',
                'assigned_to' => $laborer1->id
            ]);

            Task::updateOrCreate([
                'planting_id' => $planting1->id,
                'task_type' => 'fertilizing',
            ], [
                'task_type' => 'fertilizing',
                'due_date' => now()->addDays(5),
                'description' => 'Apply nitrogen fertilizer to corn',
                'status' => 'pending',
                'assigned_to' => $laborer2->id
            ]);

            Task::updateOrCreate([
                'planting_id' => $planting2->id,
                'task_type' => 'weeding',
            ], [
                'task_type' => 'weeding',
                'due_date' => now()->addDays(1),
                'description' => 'Remove weeds from soybean rows',
                'status' => 'pending',
                'assigned_to' => $laborer1->id
            ]);

            Task::updateOrCreate([
                'planting_id' => $planting3->id,
                'task_type' => 'harvesting',
            ], [
                'task_type' => 'harvesting',
                'due_date' => now()->addDays(3),
                'description' => 'Harvest wheat - ready for collection',
                'status' => 'pending',
                'assigned_to' => $laborer3->id
            ]);

            Task::updateOrCreate([
                'planting_id' => $planting4->id,
                'task_type' => 'harvesting',
            ], [
                'task_type' => 'harvesting',
                'due_date' => now()->subDays(10),
                'description' => 'Harvest tomatoes - completed',
                'status' => 'completed',
                'assigned_to' => $laborer2->id
            ]);

            // Create inventory items
            InventoryItem::updateOrCreate([
                'name' => 'Corn Seeds',
            ], [
                'name' => 'Corn Seeds',
                'category' => 'seeds',
                'quantity' => 50.0,
                'price' => 25.00,
                'unit' => 'kg',
                'min_stock' => 10.0
            ]);

            InventoryItem::updateOrCreate([
                'name' => 'Nitrogen Fertilizer',
            ], [
                'name' => 'Nitrogen Fertilizer',
                'category' => 'fertilizer',
                'quantity' => 8.0,
                'price' => 45.00,
                'unit' => 'bags',
                'min_stock' => 5.0
            ]);

            InventoryItem::updateOrCreate([
                'name' => 'Pesticide Spray',
            ], [
                'name' => 'Pesticide Spray',
                'category' => 'pesticide',
                'quantity' => 3.0,
                'price' => 85.00,
                'unit' => 'liters',
                'min_stock' => 2.0
            ]);

            InventoryItem::updateOrCreate([
                'name' => 'Fresh Tomatoes',
            ], [
                'name' => 'Fresh Tomatoes',
                'category' => 'produce',
                'quantity' => 150.0,
                'price' => 4.50,
                'unit' => 'kg',
                'min_stock' => 0.0
            ]);

            InventoryItem::updateOrCreate([
                'name' => 'Organic Corn',
            ], [
                'name' => 'Organic Corn',
                'category' => 'produce',
                'quantity' => 200.0,
                'price' => 3.25,
                'unit' => 'kg',
                'min_stock' => 0.0
            ]);

            // Create harvests
            $harvest1 = Harvest::updateOrCreate([
                'planting_id' => $planting4->id,
            ], [
                'yield' => 125.5,
                'harvest_date' => now()->subDays(7),
                'quality' => 'excellent'
            ]);

            // Create expenses
            Expense::updateOrCreate([
                'description' => 'Corn seeds purchase',
                'planting_id' => $planting1->id,
            ], [
                'amount' => 125.00,
                'category' => 'seeds',
                'date' => now()->subDays(50),
                'planting_id' => $planting1->id
            ]);

            Expense::updateOrCreate([
                'description' => 'Fertilizer application',
                'planting_id' => $planting1->id,
            ], [
                'amount' => 85.00,
                'category' => 'fertilizer',
                'date' => now()->subDays(35),
                'planting_id' => $planting1->id
            ]);

            Expense::updateOrCreate([
                'description' => 'Labor costs - weeding',
                'planting_id' => $planting2->id,
            ], [
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
                    $recordedAt = now()->subDays($i)->setTime(8 + $i, 0, 0);

                    WeatherLog::updateOrCreate(
                        [
                            'field_id' => $field->id,
                            'recorded_at' => $recordedAt,
                        ],
                        [
                            'temperature' => rand(15, 30) + (rand(0, 9) / 10),
                            'humidity' => rand(40, 80),
                            'wind_speed' => rand(5, 20) + (rand(0, 9) / 10),
                            'conditions' => $conditions[array_rand($conditions)],
                        ]
                    );
                }
            }

            echo "Database seeded successfully!\n";
        } else {
            echo "Data already exists. Users updated/verified.\n";
        }

        echo "\nUsers available:\n";
        foreach ($userPasswords as $email => $password) {
            echo "- {$email} (password: {$password})\n";
        }
    }
}