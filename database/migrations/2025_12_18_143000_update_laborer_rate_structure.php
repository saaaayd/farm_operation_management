<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('laborers', function (Blueprint $table) {
            $table->string('rate_type')->default('hourly')->after('hourly_rate');
            $table->renameColumn('hourly_rate', 'rate');
        });

        // Make rate nullable to support 'per_job' which might not have a fixed rate at assignment time, 
        // though the requirement says "if per job, we cant put a rate", implying it might be null or handled differently.
        // Let's make it nullable to be safe and flexible.
        Schema::table('laborers', function (Blueprint $table) {
            $table->decimal('rate', 8, 2)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laborers', function (Blueprint $table) {
            $table->renameColumn('rate', 'hourly_rate');
            $table->dropColumn('rate_type');
        });

        Schema::table('laborers', function (Blueprint $table) {
            $table->decimal('hourly_rate', 8, 2)->nullable(false)->change();
        });
    }
};
