<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('farmer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('harvest_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('rice_variety_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('description');
            $table->decimal('quantity_available', 10, 2); // in kg
            $table->decimal('price_per_kg', 8, 2);
            $table->string('unit')->default('kg');
            $table->enum('quality_grade', ['premium', 'grade_a', 'grade_b', 'standard']);
            $table->decimal('moisture_content', 5, 2)->nullable(); // percentage
            $table->json('harvest_details')->nullable(); // harvest date, field info, etc.
            $table->json('images')->nullable(); // product images
            $table->enum('status', ['draft', 'active', 'sold_out', 'expired', 'suspended'])->default('draft');
            $table->datetime('available_from')->nullable();
            $table->datetime('available_until')->nullable();
            $table->string('location'); // pickup/delivery location
            $table->json('delivery_options')->nullable(); // pickup, delivery, shipping
            $table->decimal('minimum_order', 8, 2)->default(1.00); // minimum order quantity
            $table->text('terms_conditions')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_listings');
    }
};