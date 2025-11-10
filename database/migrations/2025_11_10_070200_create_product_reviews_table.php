<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rice_product_id')->constrained('rice_products')->onDelete('cascade');
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('rice_order_id')->nullable()->constrained('rice_orders')->onDelete('set null');
            $table->decimal('rating', 3, 1);
            $table->string('title')->nullable();
            $table->text('review_text')->nullable();
            $table->decimal('quality_rating', 3, 1)->nullable();
            $table->decimal('delivery_rating', 3, 1)->nullable();
            $table->decimal('farmer_rating', 3, 1)->nullable();
            $table->boolean('would_recommend')->default(false);
            $table->boolean('verified_purchase')->default(false);
            $table->json('images')->nullable();
            $table->integer('helpful_votes')->default(0);
            $table->boolean('is_approved')->default(false);
            $table->timestamps();

            $table->index(['rice_product_id', 'is_approved']);
            $table->index('buyer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_reviews');
    }
};

