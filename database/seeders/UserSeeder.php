<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seed just the users table.
     */
    public function run(): void
    {
        // Create a farmer user (verified, ready to login)
        $farmer = User::updateOrCreate(
            ['email' => 'farmer@ricefarm.com'],
            [
                'first_name' => 'Juan',
                'middle_initial' => 'D',
                'last_name' => 'Dela Cruz',
                'name' => 'Juan Dela Cruz',
                'password' => Hash::make('password123'),
                'role' => 'farmer',
                'phone' => '09171234567',
                'address' => [
                    'street' => '123 Rice Field Road',
                    'city' => 'San Jose',
                    'state' => 'Nueva Ecija',
                    'country' => 'Philippines',
                    'postal_code' => '3100'
                ],
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
            ]
        );

        // Create a buyer user
        $buyer = User::updateOrCreate(
            ['email' => 'buyer@ricefarm.com'],
            [
                'first_name' => 'Maria',
                'middle_initial' => 'S',
                'last_name' => 'Santos',
                'name' => 'Maria Santos',
                'password' => Hash::make('password123'),
                'role' => 'buyer',
                'phone' => '09189876543',
                'address' => [
                    'street' => '456 Market Street',
                    'city' => 'Cabanatuan',
                    'state' => 'Nueva Ecija',
                    'country' => 'Philippines',
                    'postal_code' => '3100'
                ],
                'phone_verified_at' => now(),
                'email_verified_at' => now(),
            ]
        );

        echo "\n=== Users Seeded ===\n";
        echo "Farmer: farmer@ricefarm.com / password123\n";
        echo "Buyer:  buyer@ricefarm.com / password123\n";
    }
}
