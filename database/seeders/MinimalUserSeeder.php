<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class MinimalUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks to allow truncation if needed (though we usually migrate:fresh)
        // DB::statement('SET session_replication_role = \'replica\';'); 

        // 1. Create User (John)
        $john = User::updateOrCreate(
            ['email' => 'john@farmops.com'],
            [
                'first_name' => 'John',
                'last_name' => 'Farmer',
                'name' => 'John Farmer',
                'password' => Hash::make('john123'),
                'role' => 'farmer',
                'phone' => '+1-555-0101',
                'phone_verified_at' => now(),
                // Minimal address structure if required by model casts, but empty
                'address' => [],
            ]
        );

        // Also create the Buyer user just in case (optional, but requested "john@farmops.com ONLY")
        // So I will stick to ONLY John.

        echo "Seeded John Farmer credentials only.\n";
    }
}
