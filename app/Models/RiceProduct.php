<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiceProduct extends Model
{
    use HasFactory;

    protected $fillable = [
        'farmer_id',
        'rice_variety_id',
        'harvest_id',
        'name',
        'description',
        'quantity_available',
        'unit',
        'price_per_unit',
        'quality_grade',
        'moisture_content',
        'purity_percentage',
        'harvest_date',
        'processing_method',
        'storage_conditions',
        'certification',
        'images',
        'location',
        'is_organic',
        'is_available',
        'production_status',
        'available_from',
        'minimum_order_quantity',
        'packaging_options',
        'delivery_options',
        'payment_terms',
        'contact_info',
        'notes',
    ];

    protected $casts = [
        'quantity_available' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'moisture_content' => 'decimal:2',
        'purity_percentage' => 'decimal:2',
        'minimum_order_quantity' => 'decimal:2',
        'harvest_date' => 'date',
        'available_from' => 'date',
        'images' => 'array',
        'location' => 'array',
        'packaging_options' => 'array',
        'delivery_options' => 'array',
        'contact_info' => 'array',
        'is_organic' => 'boolean',
        'is_available' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Quality grade constants
     */
    const GRADE_PREMIUM = 'premium';
    const GRADE_GRADE_A = 'grade_a';
    const GRADE_GRADE_B = 'grade_b';
    const GRADE_COMMERCIAL = 'commercial';

    /**
     * Processing method constants
     */
    const PROCESSING_MILLED = 'milled';
    const PROCESSING_BROWN = 'brown';
    const PROCESSING_PARBOILED = 'parboiled';
    const PROCESSING_ORGANIC = 'organic';

    /**
     * Unit constants
     */
    const UNIT_KG = 'kg';
    const UNIT_TONS = 'tons';
    const UNIT_BAGS = 'bags';
    const UNIT_SACKS = 'sacks';

    /**
     * Production status constants
     */
    const STATUS_AVAILABLE = 'available';
    const STATUS_IN_PRODUCTION = 'in_production';
    const STATUS_OUT_OF_STOCK = 'out_of_stock';

    /**
     * Get the farmer who owns this product
     */
    public function farmer()
    {
        return $this->belongsTo(User::class, 'farmer_id');
    }

    /**
     * Get the rice variety for this product
     */
    public function riceVariety()
    {
        return $this->belongsTo(RiceVariety::class);
    }

    /**
     * Get the harvest this product came from
     */
    public function harvest()
    {
        return $this->belongsTo(Harvest::class);
    }

    /**
     * Get the orders for this product
     */
    public function orders()
    {
        return $this->hasMany(RiceOrder::class, 'rice_product_id');
    }

    /**
     * Get the reviews for this product
     */
    public function reviews()
    {
        return $this->hasMany(ProductReview::class, 'rice_product_id');
    }

    /**
     * Scope to get available products
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true)
            ->where('quantity_available', '>', 0);
    }

    /**
     * Scope to get products in production
     */
    public function scopeInProduction($query)
    {
        return $query->where('production_status', self::STATUS_IN_PRODUCTION);
    }

    /**
     * Scope to get products that are available or can be pre-ordered
     */
    public function scopeAvailableOrPreOrder($query)
    {
        return $query->where('is_available', true)
            ->where(function ($q) {
                $q->where('production_status', self::STATUS_AVAILABLE)
                    ->orWhere('production_status', self::STATUS_IN_PRODUCTION);
            });
    }

    /**
     * Check if product is in production
     */
    public function isInProduction(): bool
    {
        return $this->production_status === self::STATUS_IN_PRODUCTION;
    }

    /**
     * Check if product can be pre-ordered
     */
    public function canBePreOrdered(): bool
    {
        return $this->production_status === self::STATUS_IN_PRODUCTION && $this->available_from !== null;
    }

    /**
     * Scope to filter by quality grade
     */
    public function scopeByGrade($query, $grade)
    {
        return $query->where('quality_grade', $grade);
    }

    /**
     * Scope to filter by rice variety
     */
    public function scopeByVariety($query, $varietyId)
    {
        return $query->where('rice_variety_id', $varietyId);
    }

    /**
     * Scope to filter by organic
     */
    public function scopeOrganic($query)
    {
        return $query->where('is_organic', true);
    }

    /**
     * Scope to filter by location
     */
    public function scopeNearLocation($query, $latitude, $longitude, $radiusKm = 50)
    {
        return $query->whereRaw(
            "(6371 * acos(cos(radians(?)) * cos(radians(JSON_EXTRACT(location, '$.latitude'))) * cos(radians(JSON_EXTRACT(location, '$.longitude')) - radians(?)) + sin(radians(?)) * sin(radians(JSON_EXTRACT(location, '$.latitude'))))) < ?",
            [$latitude, $longitude, $latitude, $radiusKm]
        );
    }

    /**
     * Scope to filter by price range
     */
    public function scopePriceRange($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween('price_per_unit', [$minPrice, $maxPrice]);
    }

    /**
     * Get average rating
     */
    public function getAverageRatingAttribute()
    {
        return $this->reviews()->avg('rating') ?? 0;
    }

    /**
     * Get total reviews count
     */
    public function getReviewsCountAttribute()
    {
        return $this->reviews()->count();
    }

    /**
     * Check if product has sufficient quantity
     */
    public function hasSufficientQuantity($requestedQuantity)
    {
        return $this->quantity_available >= $requestedQuantity;
    }

    /**
     * Reserve quantity for an order
     */
    public function reserveQuantity($quantity)
    {
        // Use a transaction and lockForUpdate to ensure atomicity and prevent race conditions
        \Illuminate\Support\Facades\DB::transaction(function () use ($quantity) {
            // Re-fetch the product with a lock
            $freshProduct = self::where('id', $this->id)->lockForUpdate()->first();

            if (!$freshProduct->hasSufficientQuantity($quantity)) {
                throw new \Exception('Insufficient quantity available');
            }

            // Decrement on the locked instance
            $freshProduct->decrement('quantity_available', $quantity);

            // Mark as unavailable if no quantity left (using the fresh instance)
            if ($freshProduct->quantity_available <= 0) {
                $freshProduct->update(['is_available' => false]);
            }

            // Sync current instance with fresh data to reflect changes in the calling scope
            $this->quantity_available = $freshProduct->quantity_available;
            $this->is_available = $freshProduct->is_available;
        });
    }

    /**
     * Release reserved quantity (e.g., when order is cancelled)
     */
    public function releaseQuantity($quantity)
    {
        $this->increment('quantity_available', $quantity);

        // Mark as available if quantity is restored
        if ($this->quantity_available > 0) {
            $this->update(['is_available' => true]);
        }
    }

    /**
     * Get quality score based on various factors
     */
    public function getQualityScore()
    {
        $score = 0;

        // Grade scoring
        switch ($this->quality_grade) {
            case self::GRADE_PREMIUM:
                $score += 40;
                break;
            case self::GRADE_GRADE_A:
                $score += 30;
                break;
            case self::GRADE_GRADE_B:
                $score += 20;
                break;
            case self::GRADE_COMMERCIAL:
                $score += 10;
                break;
        }

        // Moisture content scoring (optimal 12-14%)
        if ($this->moisture_content >= 12 && $this->moisture_content <= 14) {
            $score += 20;
        } elseif ($this->moisture_content >= 10 && $this->moisture_content <= 16) {
            $score += 15;
        } else {
            $score += 5;
        }

        // Purity percentage scoring
        if ($this->purity_percentage >= 95) {
            $score += 20;
        } elseif ($this->purity_percentage >= 90) {
            $score += 15;
        } elseif ($this->purity_percentage >= 85) {
            $score += 10;
        } else {
            $score += 5;
        }

        // Organic bonus
        if ($this->is_organic) {
            $score += 10;
        }

        // Certification bonus
        if ($this->certification) {
            $score += 10;
        }

        return min(100, $score);
    }

    /**
     * Get freshness indicator based on harvest date
     */
    public function getFreshnessIndicator()
    {
        if (!$this->harvest_date) {
            return 'unknown';
        }

        $daysSinceHarvest = $this->harvest_date->diffInDays(now());

        if ($daysSinceHarvest <= 30) {
            return 'very_fresh';
        } elseif ($daysSinceHarvest <= 90) {
            return 'fresh';
        } elseif ($daysSinceHarvest <= 180) {
            return 'good';
        } elseif ($daysSinceHarvest <= 365) {
            return 'acceptable';
        } else {
            return 'old';
        }
    }

    /**
     * Get estimated delivery time based on location
     */
    public function getEstimatedDeliveryTime($buyerLocation = null)
    {
        if (!$buyerLocation || !$this->location) {
            return 'contact_seller';
        }

        // Simple distance-based estimation
        $distance = $this->calculateDistance(
            $this->location['latitude'] ?? 0,
            $this->location['longitude'] ?? 0,
            $buyerLocation['latitude'] ?? 0,
            $buyerLocation['longitude'] ?? 0
        );

        if ($distance <= 50) {
            return '1-2 days';
        } elseif ($distance <= 200) {
            return '2-4 days';
        } elseif ($distance <= 500) {
            return '4-7 days';
        } else {
            return '1-2 weeks';
        }
    }

    /**
     * Calculate distance between two points
     */
    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371; // km

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

}