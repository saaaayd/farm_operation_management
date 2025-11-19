<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Make legacy columns nullable since they've been replaced by new columns
        // quantity -> current_stock, price -> unit_price, min_stock -> minimum_stock
        if (Schema::hasColumn('inventory_items', 'quantity')) {
            DB::statement('ALTER TABLE inventory_items ALTER COLUMN quantity DROP NOT NULL');
        }
        
        if (Schema::hasColumn('inventory_items', 'price')) {
            DB::statement('ALTER TABLE inventory_items ALTER COLUMN price DROP NOT NULL');
        }
        
        if (Schema::hasColumn('inventory_items', 'min_stock')) {
            DB::statement('ALTER TABLE inventory_items ALTER COLUMN min_stock DROP NOT NULL');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore NOT NULL constraints (only if columns have no NULL values)
        if (Schema::hasColumn('inventory_items', 'quantity')) {
            // First, set any NULL values to 0
            DB::statement('UPDATE inventory_items SET quantity = 0 WHERE quantity IS NULL');
            DB::statement('ALTER TABLE inventory_items ALTER COLUMN quantity SET NOT NULL');
        }
        
        if (Schema::hasColumn('inventory_items', 'price')) {
            DB::statement('UPDATE inventory_items SET price = 0 WHERE price IS NULL');
            DB::statement('ALTER TABLE inventory_items ALTER COLUMN price SET NOT NULL');
        }
        
        if (Schema::hasColumn('inventory_items', 'min_stock')) {
            DB::statement('UPDATE inventory_items SET min_stock = 0 WHERE min_stock IS NULL');
            DB::statement('ALTER TABLE inventory_items ALTER COLUMN min_stock SET NOT NULL');
        }
    }
};
