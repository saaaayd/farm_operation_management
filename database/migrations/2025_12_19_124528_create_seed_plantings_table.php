<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('seed_plantings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('rice_variety_id')->constrained();
            $table->date('planting_date');
            $table->date('expected_transplant_date')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->string('unit')->default('kg');
            $table->enum('status', ['sown', 'germinating', 'ready', 'transplanted', 'failed'])->default('sown');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seed_plantings');
    }
};
