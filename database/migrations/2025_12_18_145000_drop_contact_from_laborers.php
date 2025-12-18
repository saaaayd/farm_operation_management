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
        Schema::table('laborers', function (Blueprint $table) {
            $table->dropColumn('contact');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('laborers', function (Blueprint $table) {
            $table->string('contact')->nullable(); // It was non-nullable before, but we can't easily restore non-null without data.
        });
    }
};
