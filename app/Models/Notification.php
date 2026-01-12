<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'link',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
        'read_at' => 'datetime',
    ];

    // Notification types
    const TYPE_ORDER_PLACED = 'order_placed';
    const TYPE_ORDER_STATUS = 'order_status';
    const TYPE_LOW_STOCK = 'low_stock';
    const TYPE_TASK_REMINDER = 'task_reminder';
    const TYPE_WEATHER_ALERT = 'weather_alert';
    const TYPE_REVIEW_RECEIVED = 'review_received';
    const TYPE_EXPIRY_ALERT = 'expiry_alert';
    const TYPE_ORDER_DELIVERED = 'order_delivered';

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeRead($query)
    {
        return $query->whereNotNull('read_at');
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    /**
     * Create a notification for a user
     */
    public static function notify(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?array $data = null,
        ?string $link = null
    ): self {
        return self::create([
            'user_id' => $userId,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data,
            'link' => $link,
        ]);
    }
}
