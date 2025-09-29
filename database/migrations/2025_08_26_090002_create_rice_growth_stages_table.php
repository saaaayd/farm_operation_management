<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rice_growth_stages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('stage_code')->unique();
            $table->text('description')->nullable();
            $table->integer('typical_duration_days'); // Typical duration for this stage
            $table->integer('order_sequence'); // Order of stages (1, 2, 3, etc.)
            $table->json('key_activities')->nullable(); // Key farming activities for this stage
            $table->json('weather_requirements')->nullable(); // Weather conditions needed
            $table->json('nutrient_requirements')->nullable(); // Fertilizer/nutrient needs
            $table->json('water_requirements')->nullable(); // Water management needs
            $table->json('common_problems')->nullable(); // Common issues in this stage
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rice_growth_stages');
    }
};