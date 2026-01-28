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
        Schema::table('fields', function (Blueprint $table) {
            $table->string('nickname')->nullable()->after('name');
            $table->string('planting_method')->nullable();
            $table->integer('cropping_seasons')->nullable();
            $table->decimal('target_yield', 8, 2)->nullable();
            $table->text('infrastructure_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fields', function (Blueprint $table) {
            $table->dropColumn([
                'nickname',
                'planting_method',
                'cropping_seasons',
                'target_yield',
                'infrastructure_notes',
            ]);
        });
    }
};
