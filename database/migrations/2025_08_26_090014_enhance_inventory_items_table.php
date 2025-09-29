<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            // Add new fields for enhanced inventory management
            $table->foreignId('category_id')->nullable()->after('category')->constrained('inventory_categories')->onDelete('set null');
            $table->string('sku')->nullable()->after('name');
            $table->string('brand')->nullable()->after('sku');
            $table->decimal('reorder_point', 10, 2)->nullable()->after('min_stock');
            $table->decimal('max_stock', 10, 2)->nullable()->after('reorder_point');
            $table->decimal('cost_per_unit', 8, 2)->nullable()->after('price');
            $table->date('expiry_date')->nullable()->after('cost_per_unit');
            $table->integer('shelf_life_days')->nullable()->after('expiry_date');
            $table->json('storage_requirements')->nullable()->after('shelf_life_days');
            $table->json('usage_instructions')->nullable()->after('storage_requirements');
            $table->json('safety_data')->nullable()->after('usage_instructions');
            $table->string('supplier')->nullable()->after('safety_data');
            $table->string('supplier_contact')->nullable()->after('supplier');
            $table->decimal('lead_time_days', 5, 1)->default(7)->after('supplier_contact');
            $table->boolean('is_active')->default(true)->after('lead_time_days');
            $table->json('metadata')->nullable()->after('is_active'); // Additional item-specific data
        });
    }

    public function down(): void
    {
        Schema::table('inventory_items', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'category_id', 'sku', 'brand', 'reorder_point', 'max_stock',
                'cost_per_unit', 'expiry_date', 'shelf_life_days',
                'storage_requirements', 'usage_instructions', 'safety_data',
                'supplier', 'supplier_contact', 'lead_time_days', 'is_active', 'metadata'
            ]);
        });
    }
};