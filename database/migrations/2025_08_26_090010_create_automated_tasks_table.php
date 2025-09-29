<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automated_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planting_id')->constrained()->onDelete('cascade');
            $table->foreignId('planting_stage_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_template_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->nullable()->constrained()->onDelete('set null'); // Generated task
            $table->string('title');
            $table->text('description');
            $table->datetime('scheduled_date');
            $table->datetime('due_date');
            $table->enum('status', ['scheduled', 'ready', 'weather_delayed', 'completed', 'skipped', 'cancelled'])->default('scheduled');
            $table->json('weather_requirements')->nullable();
            $table->json('generated_instructions')->nullable(); // Customized instructions based on conditions
            $table->text('delay_reason')->nullable();
            $table->datetime('completed_at')->nullable();
            $table->boolean('auto_generated')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automated_tasks');
    }
};