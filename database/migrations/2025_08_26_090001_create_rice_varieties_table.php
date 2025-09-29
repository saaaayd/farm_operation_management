<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rice_varieties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('variety_code')->unique();
            $table->text('description')->nullable();
            $table->integer('maturity_days'); // Days to maturity
            $table->decimal('average_yield_per_hectare', 8, 2); // kg per hectare
            $table->enum('season', ['wet', 'dry', 'both']); // Growing season
            $table->enum('grain_type', ['long', 'medium', 'short']); // Grain type
            $table->enum('resistance_level', ['high', 'medium', 'low']); // Disease resistance
            $table->json('characteristics')->nullable(); // Additional characteristics
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rice_varieties');
    }
};