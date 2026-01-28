<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rice_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('rice_product_id')->constrained('rice_products')->onDelete('cascade');
            $table->decimal('quantity', 12, 2);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_amount', 14, 2);
            $table->enum('status', [
                'pending',
                'confirmed',
                'processing',
                'shipped',
                'delivered',
                'cancelled',
                'refunded',
            ])->default('pending');
            $table->json('delivery_address');
            $table->enum('delivery_method', ['pickup', 'courier', 'postal', 'truck']);
            $table->string('payment_method');
            $table->enum('payment_status', ['pending', 'paid', 'partial', 'refunded', 'failed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('order_date')->useCurrent();
            $table->date('expected_delivery_date')->nullable();
            $table->date('actual_delivery_date')->nullable();
            $table->string('tracking_number')->nullable();
            $table->text('farmer_notes')->nullable();
            $table->text('buyer_notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'payment_status']);
            $table->index('order_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rice_orders');
    }
};

