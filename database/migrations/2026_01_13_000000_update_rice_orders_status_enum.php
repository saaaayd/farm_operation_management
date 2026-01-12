<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing check constraint
        DB::statement("ALTER TABLE rice_orders DROP CONSTRAINT IF EXISTS rice_orders_status_check");

        // Add the new check constraint with updated values
        DB::statement("ALTER TABLE rice_orders ADD CONSTRAINT rice_orders_status_check CHECK (status IN ('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded', 'disputed', 'ready_for_pickup', 'picked_up'))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This is a destructive operation if data exists with new statuses, but for rollback:
        DB::statement("ALTER TABLE rice_orders DROP CONSTRAINT IF EXISTS rice_orders_status_check");
        DB::statement("ALTER TABLE rice_orders ADD CONSTRAINT rice_orders_status_check CHECK (status IN ('pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded', 'disputed'))");
    }
};
