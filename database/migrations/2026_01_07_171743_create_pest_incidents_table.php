<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('pest_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('planting_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('pest_type'); // insect, disease, weed, rodent, other
            $table->string('pest_name'); // Specific pest/disease name
            $table->string('severity'); // low, medium, high, critical
            $table->decimal('affected_area', 10, 2)->nullable(); // Hectares affected
            $table->date('detected_date');
            $table->text('symptoms')->nullable();
            $table->text('treatment_applied')->nullable();
            $table->date('treatment_date')->nullable();
            $table->decimal('treatment_cost', 10, 2)->nullable();
            $table->string('status')->default('active'); // active, treated, resolved
            $table->text('notes')->nullable();
            $table->json('images')->nullable();
            $table->timestamps();

            $table->index(['planting_id', 'detected_date']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pest_incidents');
    }
};
