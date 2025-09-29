<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->text('description');
            $table->decimal('amount', 10, 2);
            $table->enum('category', ['seeds', 'fertilizer', 'pesticide', 'labor', 'equipment', 'utilities', 'maintenance', 'other']);
            $table->datetime('date');
            $table->foreignId('planting_id')->nullable()->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expenses');
    }
};