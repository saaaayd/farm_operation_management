<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('weather_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('field_id')->constrained()->onDelete('cascade');
            $table->decimal('temperature', 5, 1);
            $table->decimal('humidity', 5, 1);
            $table->decimal('wind_speed', 5, 1);
            $table->enum('conditions', ['clear', 'cloudy', 'rainy', 'stormy', 'snowy', 'foggy']);
            $table->datetime('recorded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('weather_logs');
    }
};