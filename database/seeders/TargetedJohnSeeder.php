<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class TargetedJohnSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
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
                    'postal_code' => '8700'
                ],
                'phone_verified_at' => now(),
            ]
        );

        $farm = \App\Models\Farm::updateOrCreate(
            ['user_id' => $john->id, 'name' => "John's Managok Farm"],
            [
                'location' => 'Managok, Malaybalay City, Bukidnon',
                'farm_coordinates' => ['lat' => 8.0276, 'lon' => 125.1885],
                'total_area' => 10.5,
                'cultivated_area' => 8.0,
                'soil_type' => 'clay_loam',
                'is_setup_complete' => true,
            ]
        );

        \App\Models\Field::updateOrCreate(
            ['farm_id' => $farm->id, 'name' => 'Block 1'],
            [
                'user_id' => $john->id,
                'location' => ['lat' => 8.0276, 'lon' => 125.1885],
                'field_coordinates' => ['lat' => 8.0276, 'lon' => 125.1885],
                'size' => 5.0,
                'soil_type' => 'Clay Loam',
                'water_access' => 'good',
                'drainage_quality' => 'excellent',
            ]
        );

        echo "Seeded John Farmer, his Farm, and his Field successfully.\n";
    }
}
