<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // PostgreSQL: Need to drop and recreate the check constraint
        DB::statement('ALTER TABLE expenses DROP CONSTRAINT IF EXISTS expenses_category_check');
        DB::statement("ALTER TABLE expenses ADD CONSTRAINT expenses_category_check CHECK (category::text = ANY (ARRAY['seeds'::text, 'fertilizer'::text, 'pesticide'::text, 'labor'::text, 'equipment'::text, 'utilities'::text, 'maintenance'::text, 'inventory_purchase'::text, 'other'::text]))");
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE expenses DROP CONSTRAINT IF EXISTS expenses_category_check');
        DB::statement("ALTER TABLE expenses ADD CONSTRAINT expenses_category_check CHECK (category::text = ANY (ARRAY['seeds'::text, 'fertilizer'::text, 'pesticide'::text, 'labor'::text, 'equipment'::text, 'utilities'::text, 'maintenance'::text, 'other'::text]))");
    }
};
