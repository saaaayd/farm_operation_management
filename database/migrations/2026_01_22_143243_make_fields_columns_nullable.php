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
        Schema::table('fields', function (Blueprint $table) {
            // Use raw SQL for PostgreSQL enum/string modification or if dbal is missing
            DB::statement('ALTER TABLE fields ALTER COLUMN soil_type DROP NOT NULL');
            DB::statement('ALTER TABLE fields ALTER COLUMN water_access DROP NOT NULL');
            DB::statement('ALTER TABLE fields ALTER COLUMN drainage_quality DROP NOT NULL');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fields', function (Blueprint $table) {
            DB::statement('ALTER TABLE fields ALTER COLUMN soil_type SET NOT NULL');
            DB::statement('ALTER TABLE fields ALTER COLUMN water_access SET NOT NULL');
            DB::statement('ALTER TABLE fields ALTER COLUMN drainage_quality SET NOT NULL');
        });
    }
};
