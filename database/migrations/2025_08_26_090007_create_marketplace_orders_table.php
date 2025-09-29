<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('farmer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('listing_id')->constrained('marketplace_listings')->onDelete('cascade');
            $table->decimal('quantity', 10, 2); // ordered quantity in kg
            $table->decimal('unit_price', 8, 2); // price per kg at time of order
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', ['pending', 'confirmed', 'preparing', 'ready_for_pickup', 'in_transit', 'delivered', 'cancelled', 'disputed'])->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'refunded', 'failed'])->default('pending');
            $table->enum('delivery_method', ['pickup', 'delivery', 'shipping']);
            $table->json('delivery_address')->nullable();
            $table->text('buyer_notes')->nullable();
            $table->text('farmer_notes')->nullable();
            $table->datetime('requested_delivery_date')->nullable();
            $table->datetime('confirmed_at')->nullable();
            $table->datetime('delivered_at')->nullable();
            $table->json('tracking_info')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_orders');
    }
};