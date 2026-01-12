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
        User::updateOrCreate(
            ['email' => 'john@farmops.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Farmer',
                'name' => 'John Farmer',
                'password' => Hash::make('john123'),
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

        echo "Seeded John Farmer (john@farmops.com) successfully.\n";
    }
}
