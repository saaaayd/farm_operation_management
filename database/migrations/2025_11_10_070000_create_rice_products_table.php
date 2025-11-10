<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rice_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('rice_variety_id')->constrained('rice_varieties')->onDelete('cascade');
            $table->foreignId('harvest_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->text('description');
            $table->decimal('quantity_available', 12, 2)->default(0);
            $table->string('unit', 20)->default('kg');
            $table->decimal('price_per_unit', 12, 2);
            $table->enum('quality_grade', ['premium', 'grade_a', 'grade_b', 'commercial']);
            $table->decimal('moisture_content', 5, 2)->nullable();
            $table->decimal('purity_percentage', 5, 2)->nullable();
            $table->date('harvest_date')->nullable();
            $table->enum('processing_method', ['milled', 'brown', 'parboiled', 'organic'])->nullable();
            $table->string('storage_conditions')->nullable();
            $table->string('certification')->nullable();
            $table->json('images')->nullable();
            $table->json('location')->nullable();
            $table->boolean('is_organic')->default(false);
            $table->boolean('is_available')->default(true);
            $table->decimal('minimum_order_quantity', 12, 2)->nullable();
            $table->json('packaging_options')->nullable();
            $table->json('delivery_options')->nullable();
            $table->string('payment_terms')->nullable();
            $table->json('contact_info')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rice_products');
    }
};

