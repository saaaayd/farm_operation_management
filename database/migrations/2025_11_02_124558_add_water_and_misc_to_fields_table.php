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
            // These are the ones from your controller
            $table->string('water_source')->nullable();
            // $table->string('water_access')->nullable();
            $table->string('irrigation_type')->nullable();
            // $table->string('drainage_quality')->nullable();
            $table->string('previous_crop')->nullable();
            $table->string('field_preparation_status')->nullable()->default('needs_assessment');
            
            // These are also in your controller, set to null
            // $table->json('field_coordinates')->nullable();
            $table->float('slope')->nullable();
        });
    }
    
    /**
    * Reverse the migrations.
    */
    public function down(): void
    {
        Schema::table('fields', function (Blueprint $table) {
            $table->dropColumn([
                'water_source',
                // 'water_access',
                'irrigation_type',
                // 'drainage_quality',
                'previous_crop',
                'field_preparation_status',
                // 'field_coordinates',
                'slope'
            ]);
        });
    }
};