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
                $table->decimal('quantity', 10, 2)->nullable()->after('yield');
            }
            if (!Schema::hasColumn('harvests', 'unit')) {
                $table->string('unit', 20)->default('kg')->after('quantity');
            }
            if (!Schema::hasColumn('harvests', 'quality_grade')) {
                $table->string('quality_grade', 1)->nullable()->after('quality');
            }
            if (!Schema::hasColumn('harvests', 'price_per_unit')) {
                $table->decimal('price_per_unit', 10, 2)->nullable()->after('quality_grade');
            }
            if (!Schema::hasColumn('harvests', 'total_value')) {
                $table->decimal('total_value', 10, 2)->nullable()->after('price_per_unit');
            }
            if (!Schema::hasColumn('harvests', 'notes')) {
                $table->text('notes')->nullable()->after('total_value');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('harvests', function (Blueprint $table) {
            $table->dropColumn([
                'quantity',
                'unit',
                'quality_grade',
                'price_per_unit',
                'total_value',
                'notes'
            ]);
        });
    }
};

