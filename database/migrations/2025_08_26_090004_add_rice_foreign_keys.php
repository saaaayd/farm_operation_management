<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            $table->foreign('primary_rice_variety_id')->references('id')->on('rice_varieties')->onDelete('set null');
        });

        Schema::table('plantings', function (Blueprint $table) {
            $table->foreign('rice_variety_id')->references('id')->on('rice_varieties')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('farms', function (Blueprint $table) {
            $table->dropForeign(['primary_rice_variety_id']);
        });

        Schema::table('plantings', function (Blueprint $table) {
            $table->dropForeign(['rice_variety_id']);
        });
    }
};