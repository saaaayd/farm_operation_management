<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('fields', function (Blueprint $table) {
            $table->float('soil_ph')->nullable();
            $table->float('organic_matter_content')->nullable();
            $table->float('nitrogen_level')->nullable();
            $table->float('phosphorus_level')->nullable();
            $table->float('potassium_level')->nullable();
            $table->float('elevation')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('fields', function (Blueprint $table) {
            $table->dropColumn([
                'soil_ph',
                'organic_matter_content',
                'nitrogen_level',
                'phosphorus_level',
                'potassium_level',
                'elevation'
            ]);
        });
    }
};