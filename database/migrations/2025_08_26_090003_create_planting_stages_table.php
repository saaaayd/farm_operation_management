<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('planting_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planting_id')->constrained()->onDelete('cascade');
            $table->foreignId('rice_growth_stage_id')->constrained()->onDelete('cascade');
            $table->datetime('started_at')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed', 'delayed', 'skipped'])->default('pending');
            $table->text('notes')->nullable();
            $table->json('stage_data')->nullable(); // Store stage-specific data
            $table->decimal('completion_percentage', 5, 2)->default(0); // 0-100%
            $table->timestamps();

            // Ensure unique combination of planting and stage
            $table->unique(['planting_id', 'rice_growth_stage_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('planting_stages');
    }
};