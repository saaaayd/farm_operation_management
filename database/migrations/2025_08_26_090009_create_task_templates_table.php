<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rice_growth_stage_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->text('description');
            $table->enum('task_type', ['watering', 'fertilizing', 'weeding', 'pest_control', 'harvesting', 'maintenance']);
            $table->integer('days_from_stage_start')->default(0); // When to trigger this task
            $table->integer('estimated_duration_hours')->default(1); // How long the task takes
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->json('weather_conditions')->nullable(); // Ideal weather for this task
            $table->json('required_materials')->nullable(); // Materials/tools needed
            $table->json('instructions')->nullable(); // Step-by-step instructions
            $table->json('safety_notes')->nullable(); // Safety considerations
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('is_weather_dependent')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_templates');
    }
};