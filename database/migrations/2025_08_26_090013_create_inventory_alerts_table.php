<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->onDelete('cascade');
            $table->enum('alert_type', ['low_stock', 'out_of_stock', 'expiring', 'expired', 'reorder_point']);
            $table->enum('severity', ['low', 'medium', 'high', 'critical']);
            $table->string('title');
            $table->text('message');
            $table->json('alert_data')->nullable(); // Current levels, thresholds, etc.
            $table->json('recommendations')->nullable(); // Suggested actions
            $table->boolean('is_active')->default(true);
            $table->boolean('is_read')->default(false);
            $table->datetime('expires_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_alerts');
    }
};