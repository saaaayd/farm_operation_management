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
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('payment_type')->default('wage')->after('status'); // wage, share
            $table->decimal('revenue_share_percentage', 5, 2)->nullable()->after('payment_type');
        });

        Schema::table('harvests', function (Blueprint $table) {
            $table->decimal('harvester_share', 10, 2)->nullable()->after('total_value'); // The calculated share amount
            $table->decimal('harvester_share_percentage', 5, 2)->nullable()->after('harvester_share'); // The percentage used
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropColumn(['payment_type', 'revenue_share_percentage']);
        });

        Schema::table('harvests', function (Blueprint $table) {
            $table->dropColumn(['harvester_share', 'harvester_share_percentage']);
        });
    }
};
