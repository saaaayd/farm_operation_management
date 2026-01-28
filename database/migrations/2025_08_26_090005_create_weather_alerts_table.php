<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained()->onDelete('cascade');
            $table->foreignId('planting_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('alert_type', ['drought', 'flood', 'extreme_heat', 'cold_stress', 'high_humidity', 'strong_wind', 'pest_risk', 'disease_risk']);
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->string('title');
            $table->text('description');
            $table->json('weather_data')->nullable(); // Current weather conditions
            $table->json('recommendations')->nullable(); // Recommended actions
            $table->datetime('alert_date');
            $table->datetime('expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_alerts');
    }
};