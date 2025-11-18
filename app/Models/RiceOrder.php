<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RiceOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'buyer_id',
        'rice_product_id',
        'quantity',
        'unit_price',
        'total_amount',
        'status',
        'is_pre_order',
        'available_date',
        'notification_sent_available',
        'notification_sent_day_before',
        'delivery_address',
        'delivery_method',
        'payment_method',
        'payment_status',
        'notes',
        'order_date',
        'expected_delivery_date',
        'actual_delivery_date',
        'tracking_number',
        'farmer_notes',
        'buyer_notes',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'delivery_address' => 'array',
        'order_date' => 'datetime',
        'expected_delivery_date' => 'date',
        'actual_delivery_date' => 'date',
        'available_date' => 'date',
        'is_pre_order' => 'boolean',
        'notification_sent_available' => 'boolean',
        'notification_sent_day_before' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Order status constants
     */
    const STATUS_PENDING = 'pending';
    const STATUS_CONFIRMED = 'confirmed';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';

    /**
     * Payment status constants
     */
    const PAYMENT_PENDING = 'pending';
    const PAYMENT_PAID = 'paid';
    const PAYMENT_PARTIAL = 'partial';
    const PAYMENT_REFUNDED = 'refunded';
    const PAYMENT_FAILED = 'failed';

    /**
     * Delivery method constants
     */
    const DELIVERY_PICKUP = 'pickup';
    const DELIVERY_COURIER = 'courier';
    const DELIVERY_POSTAL = 'postal';
    const DELIVERY_TRUCK = 'truck';

    /**
     * Get the buyer for this order
     */
    public function buyer()
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the rice product for this order
     */
    public function riceProduct()
    {
        return $this->belongsTo(RiceProduct::class);
    }

    /**
     * Get the farmer through the product
     */
    public function farmer()
    {
        return $this->hasOneThrough(User::class, RiceProduct::class, 'id', 'id', 'rice_product_id', 'farmer_id');
    }

    /**
     * Get messages associated with the order
     */
    public function messages()
    {
        return $this->hasMany(RiceOrderMessage::class, 'rice_order_id')->latest();
    }

    /**
     * Scope to get orders by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get pending orders
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get confirmed orders
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', self::STATUS_CONFIRMED);
    }

    /**
     * Scope to get active orders (not cancelled or delivered)
     */
    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [self::STATUS_CANCELLED, self::STATUS_DELIVERED, self::STATUS_REFUNDED]);
    }

    /**
     * Scope to get orders for a specific farmer
     */
    public function scopeForFarmer($query, $farmerId)
    {
        return $query->whereHas('riceProduct', function ($q) use ($farmerId) {
            $q->where('farmer_id', $farmerId);
        });
    }

    /**
     * Scope to get orders for a specific buyer
     */
    public function scopeForBuyer($query, $buyerId)
    {
        return $query->where('buyer_id', $buyerId);
    }

    /**
     * Confirm the order
     */
    public function confirm($expectedDeliveryDate = null, $farmerNotes = null)
    {
        $this->update([
            'status' => self::STATUS_CONFIRMED,
            'expected_delivery_date' => $expectedDeliveryDate,
            'farmer_notes' => $farmerNotes,
        ]);

        // Reserve the quantity in the product
        $this->riceProduct->reserveQuantity($this->quantity);
    }

    /**
     * Cancel the order
     */
    public function cancel($reason = null)
    {
        // Release reserved quantity if order was confirmed
        if (in_array($this->status, [self::STATUS_CONFIRMED, self::STATUS_PROCESSING])) {
            $this->riceProduct->releaseQuantity($this->quantity);
        }

        $this->update([
            'status' => self::STATUS_CANCELLED,
            'farmer_notes' => $reason,
        ]);
    }

    /**
     * Mark as shipped
     */
    public function ship($trackingNumber = null)
    {
        $this->update([
            'status' => self::STATUS_SHIPPED,
            'tracking_number' => $trackingNumber,
        ]);
    }

    /**
     * Mark as delivered
     */
    public function deliver($actualDeliveryDate = null)
    {
        $this->update([
            'status' => self::STATUS_DELIVERED,
            'actual_delivery_date' => $actualDeliveryDate ?? now(),
        ]);
    }

    /**
     * Check if order can be cancelled
     */
    public function canBeCancelled()
    {
        return in_array($this->status, [self::STATUS_PENDING, self::STATUS_CONFIRMED]);
    }

    /**
     * Check if order can be confirmed
     */
    public function canBeConfirmed()
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Check if order can be shipped
     */
    public function canBeShipped()
    {
        return $this->status === self::STATUS_CONFIRMED;
    }

    /**
     * Check if order can be delivered
     */
    public function canBeDelivered()
    {
        return $this->status === self::STATUS_SHIPPED;
    }

    /**
     * Get order progress percentage
     */
    public function getProgressPercentage()
    {
        switch ($this->status) {
            case self::STATUS_PENDING:
                return 10;
            case self::STATUS_CONFIRMED:
                return 30;
            case self::STATUS_PROCESSING:
                return 50;
            case self::STATUS_SHIPPED:
                return 80;
            case self::STATUS_DELIVERED:
                return 100;
            case self::STATUS_CANCELLED:
            case self::STATUS_REFUNDED:
                return 0;
            default:
                return 0;
        }
    }

    /**
     * Get estimated delivery date if not set
     */
    public function getEstimatedDeliveryDate()
    {
        if ($this->expected_delivery_date) {
            return $this->expected_delivery_date;
        }

        // Calculate based on delivery method
        $daysToAdd = match($this->delivery_method) {
            self::DELIVERY_PICKUP => 1,
            self::DELIVERY_COURIER => 3,
            self::DELIVERY_POSTAL => 7,
            self::DELIVERY_TRUCK => 5,
            default => 5
        };

        return $this->order_date->addDays($daysToAdd);
    }

    /**
     * Check if order is overdue
     */
    public function isOverdue()
    {
        if ($this->status === self::STATUS_DELIVERED) {
            return false;
        }

        $expectedDate = $this->expected_delivery_date ?? $this->getEstimatedDeliveryDate();
        return now()->gt($expectedDate);
    }

    /**
     * Get days until delivery
     */
    public function getDaysUntilDelivery()
    {
        if ($this->status === self::STATUS_DELIVERED) {
            return 0;
        }

        $expectedDate = $this->expected_delivery_date ?? $this->getEstimatedDeliveryDate();
        return now()->diffInDays($expectedDate, false);
    }
}