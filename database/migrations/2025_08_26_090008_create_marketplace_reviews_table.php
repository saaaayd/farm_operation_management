<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketplace_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('marketplace_orders')->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('farmer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('listing_id')->constrained('marketplace_listings')->onDelete('cascade');
            $table->integer('rating')->check('rating >= 1 AND rating <= 5');
            $table->text('review_text')->nullable();
            $table->json('review_categories')->nullable(); // quality, delivery, communication, etc.
            $table->json('images')->nullable(); // review images
            $table->boolean('is_verified_purchase')->default(true);
            $table->text('farmer_response')->nullable();
            $table->datetime('farmer_responded_at')->nullable();
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            // Ensure one review per order
            $table->unique('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketplace_reviews');
    }
};