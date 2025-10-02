<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProductReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'rice_product_id',
        'buyer_id',
        'rice_order_id',
        'rating',
        'title',
        'review_text',
        'quality_rating',
        'delivery_rating',
        'farmer_rating',
        'would_recommend',
        'verified_purchase',
        'images',
        'helpful_votes',
        'is_approved',
    ];

    protected $casts = [
        'rating' => 'decimal:1',
        'quality_rating' => 'decimal:1',
        'delivery_rating' => 'decimal:1',
        'farmer_rating' => 'decimal:1',
        'would_recommend' => 'boolean',
        'verified_purchase' => 'boolean',
        'is_approved' => 'boolean',
        'images' => 'array',
        'helpful_votes' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the rice product being reviewed
     */
    public function riceProduct()
    {
        return $this->belongsTo(RiceProduct::class);
    }

    /**
     * Get the buyer who wrote the review
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the order this review is for
     */
    public function riceOrder()
    {
        return $this->belongsTo(RiceOrder::class);
    }

    /**
     * Scope to get approved reviews
     */
    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    /**
     * Scope to get verified purchase reviews
     */
    public function scopeVerified($query)
    {
        return $query->where('verified_purchase', true);
    }

    /**
     * Scope to get reviews by rating
     */
    public function scopeByRating($query, $rating)
    {
        return $query->where('rating', $rating);
    }

    /**
     * Scope to get recent reviews
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }

    /**
     * Get the overall rating (average of all rating components)
     */
    public function getOverallRatingAttribute()
    {
        $ratings = array_filter([
            $this->rating,
            $this->quality_rating,
            $this->delivery_rating,
            $this->farmer_rating
        ]);

        return count($ratings) > 0 ? round(array_sum($ratings) / count($ratings), 1) : 0;
    }

    /**
     * Check if review is helpful (has votes)
     */
    public function isHelpful()
    {
        return $this->helpful_votes > 0;
    }

    /**
     * Increment helpful votes
     */
    public function markAsHelpful()
    {
        $this->increment('helpful_votes');
    }
}