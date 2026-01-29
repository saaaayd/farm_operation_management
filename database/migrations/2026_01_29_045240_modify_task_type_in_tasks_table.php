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
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('task_type')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // We cannot easily revert to enum if there are values that don't match the enum list.
            // For now, we will leave it as string in down, or we could try to revert to enum
            // but that would require data cleanup which is risky.
            // $table->enum('task_type', ['watering', 'fertilizing', 'weeding', 'pest_control', 'harvesting', 'maintenance'])->change();
        });
    }
};
