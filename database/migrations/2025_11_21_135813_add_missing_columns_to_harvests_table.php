<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            // Add new columns for enhanced harvest tracking
            if (!Schema::hasColumn('harvests', 'quantity')) {
                $table->decimal('quantity', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('harvests', 'unit')) {
                $table->string('unit', 20)->default('kg');
            }
            if (!Schema::hasColumn('harvests', 'quality_grade')) {
                $table->string('quality_grade', 1)->nullable();
            }
            if (!Schema::hasColumn('harvests', 'price_per_unit')) {
                $table->decimal('price_per_unit', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('harvests', 'total_value')) {
                $table->decimal('total_value', 10, 2)->nullable();
            }
            if (!Schema::hasColumn('harvests', 'notes')) {
                $table->text('notes')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            if (Schema::hasColumn('harvests', 'quantity')) {
                $table->dropColumn('quantity');
            }
            if (Schema::hasColumn('harvests', 'unit')) {
                $table->dropColumn('unit');
            }
            if (Schema::hasColumn('harvests', 'quality_grade')) {
                $table->dropColumn('quality_grade');
            }
            if (Schema::hasColumn('harvests', 'price_per_unit')) {
                $table->dropColumn('price_per_unit');
            }
            if (Schema::hasColumn('harvests', 'total_value')) {
                $table->dropColumn('total_value');
            }
            if (Schema::hasColumn('harvests', 'notes')) {
                $table->dropColumn('notes');
            }
        });
    }
};
