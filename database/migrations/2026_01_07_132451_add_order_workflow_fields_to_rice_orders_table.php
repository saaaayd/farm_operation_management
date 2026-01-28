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
        Schema::table('rice_orders', function (Blueprint $table) {
            $table->timestamp('shipped_at')->nullable()->after('actual_delivery_date');
            $table->timestamp('auto_confirm_at')->nullable()->after('shipped_at');
            $table->text('dispute_reason')->nullable()->after('buyer_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rice_orders', function (Blueprint $table) {
            $table->dropColumn(['shipped_at', 'auto_confirm_at', 'dispute_reason']);
        });
    }
};
