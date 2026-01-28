<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\RiceGrowthStage;
use App\Models\RiceVariety;
use App\Models\Planting;
use App\Models\Task;
use App\Models\Harvest;
use App\Models\InventoryItem;
use App\Models\Expense;
use App\Models\RiceProduct;
use App\Models\Sale;
use App\Models\Field;
use App\Models\Buyer;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TargetedJohnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 0. Seed Rice Growth Stages (Dependency)
        $stages = [
            [
                'name' => 'Germination & Seedling',
                'stage_code' => 'stage_1_seedling',
                'description' => 'From soaking seeds to transplanting.',
                'typical_duration_days' => 20,
                'order_sequence' => 1,
            ],
            [
                'name' => 'Tillering',
                'stage_code' => 'stage_2_tillering',
                'description' => 'Development of tillers.',
                'typical_duration_days' => 30,
                'order_sequence' => 2,
            ],
            [
                'name' => 'Panicle Initiation',
                'stage_code' => 'stage_3_panicle',
                'description' => 'Start of reproductive phase.',
                'typical_duration_days' => 15,
                'order_sequence' => 3,
            ],
            [
                'name' => 'Flowering',
                'stage_code' => 'stage_4_flowering',
                'description' => 'Pollination and grain formation.',
                'typical_duration_days' => 15,
                'order_sequence' => 4,
            ],
            [
                'name' => 'Ripening',
                'stage_code' => 'stage_5_ripening',
                'description' => 'Grain filling and maturation.',
                'typical_duration_days' => 30,
                'order_sequence' => 5,
            ],
        ];

        foreach ($stages as $stageData) {
            RiceGrowthStage::firstOrCreate(
                ['stage_code' => $stageData['stage_code']],
                array_merge($stageData, [
                    'is_active' => true,
                    'key_activities' => ['watering'],
                    'weather_requirements' => ['warm'],
                ])
            );
        }

        // 1. Create User with Onboarding Address Details
        $john = User::updateOrCreate(
            ['email' => 'john@farmops.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Farmer',
                'name' => 'John Farmer',
                'password' => Hash::make('john123'),
                'role' => 'farmer',
                'phone' => '+1-555-0101',
                'address' => [
                    'street' => 'Purok 5',
                    'city' => 'Managok, Malaybalay City',
                    'state' => 'Bukidnon',
                    'country' => 'Philippines',
                    'postal_code' => '8700',
                    // Onboarding Details
                    'farm_location' => 'Managok, Malaybalay City, Bukidnon',
                    'total_area' => 10.5,
                    'rice_area' => 8.0,
                    'farming_experience' => 15,
                ],
                'phone_verified_at' => now(),
            ]
        );

        // 2. Create Farm
        $farm = \App\Models\Farm::updateOrCreate(
            ['user_id' => $john->id, 'name' => "John's Managok Farm"],
            [
                'location' => 'Managok, Malaybalay City, Bukidnon',
                'farm_coordinates' => ['lat' => 8.0276, 'lon' => 125.1885],
                'total_area' => 10.5,
                'cultivated_area' => 8.0,
                'soil_type' => 'clay_loam',
                'description' => 'A family-owned sustainable rice farm specializing in high-yield varieties.',
                'is_setup_complete' => true,
            ]
        );

        // 3. Create Field
        $field = Field::updateOrCreate(
            ['farm_id' => $farm->id, 'name' => 'Block 1 - Main Rice Field'],
            [
                'user_id' => $john->id,
                'location' => ['lat' => 8.0276, 'lon' => 125.1885, 'address' => 'Managok, Malaybalay City, Bukidnon'],
                'field_coordinates' => ['lat' => 8.0276, 'lon' => 125.1885],
                'size' => 5.0,
                'soil_type' => 'clay_loam',
                'soil_ph' => 6.5,
                'organic_matter_content' => 3.5,
                'nitrogen_level' => 120,
                'phosphorus_level' => 45,
                'potassium_level' => 150,
                'water_source' => 'irrigation_canal',
                'irrigation_type' => 'flood',
                'water_access' => 'excellent',
                'drainage_quality' => 'good',
                'elevation' => 600,
                'previous_crop' => 'corn',
                'planting_method' => 'transplanting',
                'cropping_seasons' => 2,
                'target_yield' => 7.0,
                'infrastructure_notes' => 'Near main irrigation canal, good road access.',
                'notes' => 'Primary production field. Farmer has 15 years of rice farming experience.',
                'field_preparation_status' => 'ready',
            ]
        );

        // 4. Create Rice Variety
        $variety = RiceVariety::firstOrCreate(
            ['name' => 'NSIC Rc222'],
            [
                'variety_code' => 'NSIC_RC222',
                'description' => 'High yielding variety, flood tolerant, suitable for both seasons.',
                'maturity_days' => 114,
                'average_yield_per_hectare' => 6.0,
                'season' => 'both',
                'grain_type' => 'long',
                'resistance_level' => 'high',
                'characteristics' => ['resilient', 'high_yield', 'flood_tolerant'],
                'is_active' => true,
            ]
        );

        // 5. Active Planting
        $plantingDate = Carbon::now()->subDays(45);
        $activePlanting = Planting::updateOrCreate(
            [
                'field_id' => $field->id,
                'status' => 'growing',
                'season' => 'wet'
            ],
            [
                'rice_variety_id' => $variety->id,
                'crop_type' => 'Rice',
                'planting_date' => $plantingDate,
                'expected_harvest_date' => $plantingDate->copy()->addDays($variety->maturity_days),
                'planting_method' => 'transplanting',
                'seed_rate' => 80,
                'area_planted' => 5.0,
                'notes' => 'Wet season planting, good initial rain.',
            ]
        );

        $tilleringStage = RiceGrowthStage::where('stage_code', 'stage_2_tillering')->first();
        if ($tilleringStage) {
            \App\Models\PlantingStage::firstOrCreate(
                ['planting_id' => $activePlanting->id, 'rice_growth_stage_id' => $tilleringStage->id],
                [
                    'status' => 'in_progress',
                    'started_at' => Carbon::now()->subDays(5),
                ]
            );
        }

        // 6. Completed Planting
        $pastPlantingDate = Carbon::now()->subMonths(5);
        $harvestedPlanting = Planting::create([
            'field_id' => $field->id,
            'rice_variety_id' => $variety->id,
            'crop_type' => 'Rice',
            'planting_date' => $pastPlantingDate,
            'expected_harvest_date' => $pastPlantingDate->copy()->addDays($variety->maturity_days),
            'actual_harvest_date' => $pastPlantingDate->copy()->addDays($variety->maturity_days + 2),
            'status' => 'harvested',
            'planting_method' => 'transplanting',
            'seed_rate' => 80,
            'area_planted' => 5.0,
            'season' => 'dry',
            'notes' => 'Dry season harvest, successful.',
        ]);

        // 7. Tasks
        Task::create([
            'planting_id' => $activePlanting->id,
            'task_type' => 'fertilizing',
            'description' => 'Apply Urea for tillering stage support.',
            'due_date' => Carbon::now()->addDays(2),
            'status' => 'pending',
            'payment_type' => 'wage',
            'wage_amount' => 2500,
        ]);

        Task::create([
            'planting_id' => $harvestedPlanting->id,
            'task_type' => 'harvesting',
            'description' => 'Manual harvest of Block 1',
            'due_date' => $harvestedPlanting->actual_harvest_date,
            'status' => 'completed',
            'payment_type' => 'piece_rate',
            'wage_amount' => 15000,
        ]);

        // 8. Harvest Record
        $harvest = Harvest::create([
            'planting_id' => $harvestedPlanting->id,
            'harvest_date' => $harvestedPlanting->actual_harvest_date,
            'yield' => 28000,
            'quantity' => 28000,
            'unit' => 'kg',
            'quality' => 'excellent',
            'quality_grade' => 'Grade A',
            'notes' => 'Good quality grain.',
        ]);

        // 9. Inventory
        InventoryItem::updateOrCreate(
            ['user_id' => $john->id, 'name' => 'NSIC Rc222 Seeds'],
            [
                'category' => 'seeds',
                'quantity' => 40,
                'unit' => 'kg',
                'unit_price' => 60.00,
                'minimum_stock' => 20,
                'location' => 'Storage A',
                'notes' => 'Leftover from last planting',
            ]
        );

        InventoryItem::updateOrCreate(
            ['user_id' => $john->id, 'name' => 'Urea (46-0-0)'],
            [
                'category' => 'fertilizer',
                'quantity' => 2,
                'unit' => 'bags',
                'unit_price' => 2500.00,
                'minimum_stock' => 5,
                'location' => 'Storage B',
            ]
        );

        // 10. Buyer and Sale
        $buyer = Buyer::firstOrCreate(
            ['user_id' => $john->id, 'name' => 'NFA Buying Station'],
            [
                'contact_info' => '0917-123-4567',
                'address' => 'Malaybalay Buying Station',
                'email' => 'nfa@gov.ph',
            ]
        );

        Sale::create([
            'user_id' => $john->id,
            'buyer_id' => $buyer->id,
            'harvest_id' => $harvest->id,
            'quantity' => 8000,
            'unit_price' => 22.00,
            'total_amount' => 176000,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'sale_date' => $harvest->harvest_date->addDays(5),
            'notes' => 'Initial sale to NFA',
        ]);

        // 11. Expenses
        Expense::create([
            'user_id' => $john->id,
            'category' => 'labor',
            'amount' => 15000,
            'date' => $harvestedPlanting->actual_harvest_date,
            'description' => 'Harvest Labor',
            'planting_id' => $harvestedPlanting->id,
        ]);
        Expense::create([
            'user_id' => $john->id,
            'category' => 'fertilizer',
            'amount' => 5000,
            'date' => $harvestedPlanting->planting_date,
            'description' => 'Seeds and Fertilizer',
            'planting_id' => $activePlanting->id,
        ]);


        // 12. Marketplace Product
        RiceProduct::updateOrCreate(
            ['farmer_id' => $john->id, 'name' => 'Premium Dried Palay (Rc222)'], // Changed title to name, user_id to farmer_id
            [
                'rice_variety_id' => $variety->id, // Added required field
                'description' => 'High quality dried palay, 14% MC, ready for milling.',
                'price_per_unit' => 23.50, // Corrected price to price_per_unit
                'quantity_available' => 5000, // Corrected quantity to quantity_available
                'unit' => 'kg',
                'quality_grade' => 'grade_a', // Lowercase to match constant typically, or enum
                'location' => ['address' => 'Managok, Malaybalay City'], // Likely array/json
                'production_status' => 'available', // using status constant
                'is_available' => true,
                'available_from' => now(),
            ]
        );
        // Note: Field names corrected based on RiceProduct model inspection
        // 'price' -> 'price_per_unit'
        // 'quantity' -> 'quantity_available'
        // 'status' -> 'production_status'
        // 'location' -> json/array

        echo "Seeded John Farmer with comprehensive data including Growth Stages, Onboarding, and Lifecycle.\n";
    }
}
