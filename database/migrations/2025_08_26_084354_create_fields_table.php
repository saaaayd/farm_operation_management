<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('farm_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->json('location')->nullable(); // General location description
            $table->json('field_coordinates')->nullable(); // GPS coordinates
            $table->string('soil_type');
            $table->decimal('size', 10, 2); // in hectares
            $table->enum('water_access', ['excellent', 'good', 'moderate', 'poor'])->default('good');
            $table->enum('drainage_quality', ['excellent', 'good', 'moderate', 'poor'])->default('good');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fields');
    }
};