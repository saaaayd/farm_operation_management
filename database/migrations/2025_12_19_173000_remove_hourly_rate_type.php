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
        // Migrate existing data: Convert 'hourly' to 'daily' as a safe fallback
        DB::table('laborers')
            ->where('rate_type', 'hourly')
            ->update(['rate_type' => 'daily']);

        // Change default value to 'daily'
        Schema::table('laborers', function (Blueprint $table) {
            $table->string('rate_type')->default('daily')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert default value to 'hourly'
        Schema::table('laborers', function (Blueprint $table) {
            $table->string('rate_type')->default('hourly')->change();
        });

        // We cannot easily revert data changes as we don't know which ones were originally hourly
    }
};
