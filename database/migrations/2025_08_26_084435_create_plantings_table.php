<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plantings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('rice_variety_id');
            $table->string('crop_type')->default('rice');
            $table->datetime('planting_date');
            $table->datetime('expected_harvest_date');
            $table->datetime('actual_harvest_date')->nullable();
            $table->enum('status', ['planted', 'growing', 'ready', 'harvested', 'failed'])->default('planted');
            $table->enum('planting_method', ['direct_seeding', 'transplanting', 'broadcasting'])->default('transplanting');
            $table->decimal('seed_rate', 8, 2)->nullable(); // kg per hectare
            $table->decimal('area_planted', 10, 2); // in hectares
            $table->enum('season', ['wet', 'dry']);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plantings');
    }
};