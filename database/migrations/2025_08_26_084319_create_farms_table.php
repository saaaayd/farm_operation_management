<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('farms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('location');
            $table->text('description')->nullable();
            $table->decimal('total_area', 10, 2)->nullable(); // in hectares
            $table->decimal('cultivated_area', 10, 2)->nullable(); // in hectares
            $table->enum('soil_type', ['clay', 'loam', 'sandy_loam', 'clay_loam', 'silt_loam'])->nullable();
            $table->decimal('soil_ph', 3, 1)->nullable();
            $table->enum('water_source', ['rainfall', 'irrigation', 'river', 'well', 'pond'])->nullable();
            $table->enum('irrigation_type', ['flood', 'sprinkler', 'drip', 'furrow'])->nullable();
            $table->unsignedBigInteger('primary_rice_variety_id')->nullable();
            $table->json('farm_coordinates')->nullable(); // GPS coordinates
            $table->decimal('elevation', 8, 2)->nullable(); // meters above sea level
            $table->decimal('slope', 5, 2)->nullable(); // percentage
            $table->enum('drainage_system', ['good', 'moderate', 'poor'])->nullable();
            $table->boolean('is_setup_complete')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('farms');
    }
};