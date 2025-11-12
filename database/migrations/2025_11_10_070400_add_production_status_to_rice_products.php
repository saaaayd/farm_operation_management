<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rice_products', function (Blueprint $table) {
            $table->enum('production_status', ['available', 'in_production', 'out_of_stock'])->default('available');
            $table->date('available_from')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rice_products', function (Blueprint $table) {
            $table->dropColumn(['production_status', 'available_from']);
        });
    }
};



