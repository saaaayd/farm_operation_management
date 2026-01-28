<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            // Ownership
            if (!Schema::hasColumn('inventory_items', 'user_id')) {
                $table->unsignedBigInteger('user_id')->nullable()->after('id');
            }

            // New column names expected by controllers
            if (!Schema::hasColumn('inventory_items', 'current_stock')) {
                $table->decimal('current_stock', 10, 2)->nullable()->after('category');
            }

            if (!Schema::hasColumn('inventory_items', 'unit_price')) {
                $table->decimal('unit_price', 8, 2)->nullable()->after('current_stock');
            }

            if (!Schema::hasColumn('inventory_items', 'minimum_stock')) {
                $table->decimal('minimum_stock', 10, 2)->nullable()->after('unit');
            }

            if (!Schema::hasColumn('inventory_items', 'supplier')) {
                $table->string('supplier')->nullable()->after('minimum_stock');
            }

            if (!Schema::hasColumn('inventory_items', 'location')) {
                $table->string('location')->nullable()->after('supplier');
            }

            if (!Schema::hasColumn('inventory_items', 'expiry_date')) {
                $table->date('expiry_date')->nullable()->after('location');
            }

            if (!Schema::hasColumn('inventory_items', 'notes')) {
                $table->text('notes')->nullable()->after('expiry_date');
            }
        });

        // Migrate existing data where possible (keep old columns intact)
        try {
            DB::statement('UPDATE inventory_items SET current_stock = quantity WHERE current_stock IS NULL');
            DB::statement('UPDATE inventory_items SET unit_price = price WHERE unit_price IS NULL');
            DB::statement('UPDATE inventory_items SET minimum_stock = min_stock WHERE minimum_stock IS NULL');
        } catch (\Throwable $e) {
            // If SQL update fails, ignore - keeping migration non-blocking
        }
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            // We won't drop old columns to avoid accidental data loss in down.
            if (Schema::hasColumn('inventory_items', 'current_stock')) {
                $table->dropColumn('current_stock');
            }
            if (Schema::hasColumn('inventory_items', 'unit_price')) {
                $table->dropColumn('unit_price');
            }
            if (Schema::hasColumn('inventory_items', 'minimum_stock')) {
                $table->dropColumn('minimum_stock');
            }
            if (Schema::hasColumn('inventory_items', 'supplier')) {
                $table->dropColumn('supplier');
            }
            if (Schema::hasColumn('inventory_items', 'location')) {
                $table->dropColumn('location');
            }
            if (Schema::hasColumn('inventory_items', 'expiry_date')) {
                $table->dropColumn('expiry_date');
            }
            if (Schema::hasColumn('inventory_items', 'notes')) {
                $table->dropColumn('notes');
            }
            if (Schema::hasColumn('inventory_items', 'user_id')) {
                $table->dropColumn('user_id');
            }
        });
    }
};
