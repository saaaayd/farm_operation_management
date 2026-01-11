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
use App\Models\RiceProduct;
use App\Models\RiceOrder;
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
            'john@farmops.com' => 'john123',
            'mary@farmops.com' => 'mary123',
            'alice@farmops.com' => 'alice123',
            'bob@farmops.com' => 'bob123',
            'demo@farmops.com' => 'demo123',
        ];

        // Create Demo Farmer for Onboarding (No Farm created yet)
        $demoFarmer = User::updateOrCreate(
            ['email' => 'demo@farmops.com'],
            [
                'first_name' => 'Demo',
                'last_name' => 'Farmer',
                'name' => 'Demo Farmer',
                'password' => Hash::make($userPasswords['demo@farmops.com']),
                'role' => 'farmer',
                'phone' => '+1-555-0000', // Added dummy phone to satisfy DB constraint
                // Intentionally leaving address/profile incomplete for onboarding demo
            ]
        );




        // Create farmers
        $farmer1 = User::updateOrCreate(
            ['email' => 'john@farmops.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Farmer',
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
                ],
                'phone_verified_at' => now(),
            ]
        );

        $farmer2 = User::updateOrCreate(
            ['email' => 'mary@farmops.com'],
            [
                'first_name' => 'Mary',
                'last_name' => 'Grower',
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
                ],
                'phone_verified_at' => now(),
            ]
        );

        $varietyIR64 = RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-IR64'],
            [
                'name' => 'IR64',
                'description' => 'High-yielding, semi-dwarf Indica variety widely planted in SE Asia.',
                'maturity_days' => 120,
                'average_yield_per_hectare' => 5.6,
                'season' => 'wet',
                'grain_type' => 'long',
                'resistance_level' => 'medium',
                'characteristics' => [
                    'notes' => 'Performs best in irrigated lowland fields with good fertility.',
                ],
                'is_active' => true,
            ]
        );

        $varietyJasmine = RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-JASMINE'],
            [
                'name' => 'Thai Jasmine',
                'description' => 'Premium fragrant rice valued for aroma and soft texture.',
                'maturity_days' => 110,
                'average_yield_per_hectare' => 4.9,
                'season' => 'dry',
                'grain_type' => 'long',
                'resistance_level' => 'medium',
                'characteristics' => [
                    'notes' => 'Requires consistent irrigation and well-drained fields.',
                ],
                'is_active' => true,
            ]
        );

        $varietyBasmati = RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-BASMATI'],
            [
                'name' => 'Basmati 370',
                'description' => 'Traditional aromatic Basmati with elongated grains.',
                'maturity_days' => 135,
                'average_yield_per_hectare' => 4.3,
                'season' => 'dry',
                'grain_type' => 'long',
                'resistance_level' => 'medium',
                'characteristics' => [
                    'notes' => 'Prefers cool nights; suited for river-fed plains.',
                ],
                'is_active' => true,
            ]
        );

        $varietySticky = RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-STICKY'],
            [
                'name' => 'Glutinous Sticky Rice',
                'description' => 'Round-grain sticky rice used for traditional delicacies.',
                'maturity_days' => 105,
                'average_yield_per_hectare' => 4.6,
                'season' => 'wet',
                'grain_type' => 'short',
                'resistance_level' => 'high',
                'characteristics' => [
                    'notes' => 'Can tolerate temporary flooding; harvest promptly to retain stickiness.',
                ],
                'is_active' => true,
            ]
        );

        $varietyBrown = RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-BROWN'],
            [
                'name' => 'Wholegrain Brown Rice',
                'description' => 'Nutritious variety harvested and milled for brown rice.',
                'maturity_days' => 125,
                'average_yield_per_hectare' => 5.1,
                'season' => 'wet',
                'grain_type' => 'medium',
                'resistance_level' => 'medium',
                'characteristics' => [
                    'notes' => 'Responds well to organic fertilisation; ideal for health-conscious markets.',
                ],
                'is_active' => true,
            ]
        );

        $varietySwarna = RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-SWARNA'],
            [
                'name' => 'Swarna',
                'description' => 'High-yielding variety with strong disease resistance.',
                'maturity_days' => 130,
                'average_yield_per_hectare' => 6.3,
                'season' => 'wet',
                'grain_type' => 'medium',
                'resistance_level' => 'high',
                'characteristics' => [
                    'notes' => 'Handles flood-prone paddies; staple in South Asian production.',
                ],
                'is_active' => true,
            ]
        );

        $varietyRed = RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-RED'],
            [
                'name' => 'Heirloom Red Cargo',
                'description' => 'Deep-red wholegrain rice prized for antioxidants.',
                'maturity_days' => 140,
                'average_yield_per_hectare' => 3.9,
                'season' => 'dry',
                'grain_type' => 'medium',
                'resistance_level' => 'medium',
                'characteristics' => [
                    'notes' => 'Requires careful drying; fetches premium prices in niche markets.',
                ],
                'is_active' => true,
            ]
        );

        $varietyKoshihikari = RiceVariety::updateOrCreate(
            ['variety_code' => 'RICE-KOSHI'],
            [
                'name' => 'Koshihikari',
                'description' => 'Short-grain Japanese rice with excellent eating quality.',
                'maturity_days' => 118,
                'average_yield_per_hectare' => 5.3,
                'season' => 'dry',
                'grain_type' => 'short',
                'resistance_level' => 'high',
                'characteristics' => [
                    'notes' => 'Best grown in cooler climates; top choice for sushi-grade rice.',
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
                'first_name' => 'Alice',
                'last_name' => 'Buyer',
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
                ],
                'phone_verified_at' => now(),
            ]
        );

        $buyer2 = User::updateOrCreate(
            ['email' => 'bob@farmops.com'],
            [
                'first_name' => 'Bob',
                'last_name' => 'Merchant',
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
                ],
                'phone_verified_at' => now(),
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
                'user_id' => $farmer1->id,
            ], [
                'name' => 'Tom Worker',
                'phone' => '+1-555-0301',
                'rate' => 15.50,
                'rate_type' => 'daily',
                'user_id' => $farmer1->id,
            ]);

            $laborer2 = Laborer::updateOrCreate([
                'name' => 'Sarah Helper',
                'user_id' => $farmer1->id,
            ], [
                'name' => 'Sarah Helper',
                'phone' => '+1-555-0302',
                'rate' => 17.00,
                'rate_type' => 'daily',
                'user_id' => $farmer1->id,
            ]);

            $laborer3 = Laborer::updateOrCreate([
                'name' => 'Mike Laborer',
                'user_id' => $farmer1->id,
            ], [
                'name' => 'Mike Laborer',
                'phone' => '+1-555-0303',
                'rate' => 16.25,
                'rate_type' => 'daily',
                'user_id' => $farmer1->id,
            ]);

            // Create plantings
// Non-rice crops removed (Corn, Soybeans)
// Non-rice crops removed (Wheat, Tomatoes)

            // Create tasks
// Tasks for non-rice crops removed

            // Create inventory items
// Corn seeds removed

            InventoryItem::updateOrCreate([
                'name' => 'Nitrogen Fertilizer',
            ], [
                'name' => 'Nitrogen Fertilizer',
                'category' => 'fertilizer',
                'current_stock' => 8.0,
                'unit_price' => 45.00,
                'unit' => 'bags',
                'minimum_stock' => 5.0
            ]);

            InventoryItem::updateOrCreate([
                'name' => 'Pesticide Spray',
            ], [
                'name' => 'Pesticide Spray',
                'category' => 'pesticide',
                'current_stock' => 3.0,
                'unit_price' => 85.00,
                'unit' => 'liters',
                'minimum_stock' => 2.0
            ]);

            // Non-rice produce removed

            // Create harvests - linked to plantings which link to fields which link to farmer
// Harvests for non-rice crops removed


            // Create expenses
// Expenses for non-rice crops removed

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

            // Create Rice Products for marketplace
            $riceProduct1 = RiceProduct::updateOrCreate(
                [
                    'farmer_id' => $farmer1->id,
                    'name' => 'Premium IR64 Rice',
                ],
                [
                    'farmer_id' => $farmer1->id,
                    'rice_variety_id' => $varietyIR64->id,
                    'name' => 'Premium IR64 Rice',
                    'description' => 'High-quality IR64 rice, freshly harvested from organic farms.',
                    'quantity_available' => 500,
                    'price_per_unit' => 45.00,
                    'minimum_order_quantity' => 5,
                    'quality_grade' => 'premium',
                    'processing_method' => 'milled',
                    'moisture_content' => 14.0,
                    'is_organic' => true,
                    'production_status' => 'available',
                    'unit' => 'kg',
                ]
            );

            $riceProduct2 = RiceProduct::updateOrCreate(
                [
                    'farmer_id' => $farmer1->id,
                    'name' => 'Thai Jasmine Aromatic Rice',
                ],
                [
                    'farmer_id' => $farmer1->id,
                    'rice_variety_id' => $varietyJasmine->id,
                    'name' => 'Thai Jasmine Aromatic Rice',
                    'description' => 'Fragrant jasmine rice with soft texture, perfect for everyday meals.',
                    'quantity_available' => 300,
                    'price_per_unit' => 55.00,
                    'minimum_order_quantity' => 2,
                    'quality_grade' => 'grade_a',
                    'processing_method' => 'milled',
                    'moisture_content' => 13.5,
                    'is_organic' => false,
                    'production_status' => 'available',
                    'unit' => 'kg',
                ]
            );

            $riceProduct3 = RiceProduct::updateOrCreate(
                [
                    'farmer_id' => $farmer1->id,
                    'name' => 'Brown Rice - Healthy Choice',
                ],
                [
                    'farmer_id' => $farmer1->id,
                    'rice_variety_id' => $varietyBrown->id,
                    'name' => 'Brown Rice - Healthy Choice',
                    'description' => 'Nutritious whole grain brown rice, rich in fiber and nutrients.',
                    'quantity_available' => 200,
                    'price_per_unit' => 52.00,
                    'minimum_order_quantity' => 3,
                    'quality_grade' => 'commercial',
                    'processing_method' => 'milled',
                    'moisture_content' => 14.2,
                    'is_organic' => true,
                    'production_status' => 'available',
                    'unit' => 'kg',
                ]
            );

            // Create sample orders from buyers
            RiceOrder::updateOrCreate(
                [
                    'buyer_id' => $buyer1->id,
                    'rice_product_id' => $riceProduct1->id,
                    'order_date' => now()->subDays(1),
                ],
                [
                    'buyer_id' => $buyer1->id,
                    'rice_product_id' => $riceProduct1->id,
                    'quantity' => 25,
                    'unit_price' => 45.00,
                    'total_amount' => 1125.00,
                    'status' => 'pending',
                    'payment_status' => 'pending',
                    'payment_method' => 'cod',
                    'delivery_method' => 'courier',
                    'delivery_address' => [
                        'street' => '321 Market Street',
                        'city' => 'Commerce City',
                        'state' => 'CC',
                        'postal_code' => '13579'
                    ],
                    'order_date' => now()->subDays(1),
                    'buyer_notes' => 'Please pack carefully',
                ]
            );

            RiceOrder::updateOrCreate(
                [
                    'buyer_id' => $buyer2->id,
                    'rice_product_id' => $riceProduct2->id,
                    'order_date' => now()->subDays(3),
                ],
                [
                    'buyer_id' => $buyer2->id,
                    'rice_product_id' => $riceProduct2->id,
                    'quantity' => 50,
                    'unit_price' => 55.00,
                    'total_amount' => 2750.00,
                    'status' => 'confirmed',
                    'payment_status' => 'pending',
                    'payment_method' => 'bank_transfer',
                    'delivery_method' => 'pickup',
                    'delivery_address' => [
                        'street' => '654 Trade Boulevard',
                        'city' => 'Business Town',
                        'state' => 'BT',
                        'postal_code' => '24680'
                    ],
                    'order_date' => now()->subDays(3),
                    'expected_delivery_date' => now()->addDays(2),
                    'farmer_notes' => 'Ready for pickup tomorrow',
                ]
            );

            RiceOrder::updateOrCreate(
                [
                    'buyer_id' => $buyer1->id,
                    'rice_product_id' => $riceProduct3->id,
                    'order_date' => now()->subDays(5),
                ],
                [
                    'buyer_id' => $buyer1->id,
                    'rice_product_id' => $riceProduct3->id,
                    'quantity' => 15,
                    'unit_price' => 52.00,
                    'total_amount' => 780.00,
                    'status' => 'shipped',
                    'payment_status' => 'paid',
                    'payment_method' => 'cod',
                    'delivery_method' => 'courier',
                    'delivery_address' => [
                        'street' => '321 Market Street',
                        'city' => 'Commerce City',
                        'state' => 'CC',
                        'postal_code' => '13579'
                    ],
                    'order_date' => now()->subDays(5),
                    'expected_delivery_date' => now()->addDays(1),
                    'shipped_at' => now()->subDays(1),
                    'auto_confirm_at' => now()->addDays(6),
                    'tracking_number' => 'TRK-2026-001234',
                ]
            );

            echo "Marketplace products and orders seeded!\n";
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