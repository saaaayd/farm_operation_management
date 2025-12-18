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
        Schema::create('laborer_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->string('description')->nullable();
            $table->string('color')->nullable()->default('#10B981'); // Default green
            $table->timestamps();
        });

        Schema::create('group_laborer', function (Blueprint $table) {
            $table->id();
            $table->foreignId('laborer_group_id')->constrained('laborer_groups')->onDelete('cascade');
            $table->foreignId('laborer_id')->constrained('laborers')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('group_laborer');
        Schema::dropIfExists('laborer_groups');
    }
};
