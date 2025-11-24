<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rice_orders', function (Blueprint $table) {
            $table->boolean('is_pre_order')->default(false);
            $table->date('available_date')->nullable();
            $table->boolean('notification_sent_available')->default(false);
            $table->boolean('notification_sent_day_before')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('rice_orders', function (Blueprint $table) {
            $table->dropColumn(['is_pre_order', 'available_date', 'notification_sent_available', 'notification_sent_day_before']);
        });
    }
};





