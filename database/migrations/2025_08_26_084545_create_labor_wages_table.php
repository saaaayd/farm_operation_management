<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labor_wages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laborer_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->constrained()->onDelete('cascade');
            $table->decimal('hours_worked', 8, 2);
            $table->decimal('wage_amount', 8, 2);
            $table->datetime('date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labor_wages');
    }
};