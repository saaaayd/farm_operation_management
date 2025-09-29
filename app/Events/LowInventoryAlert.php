<?php

namespace App\Events;

use App\Models\InventoryItem;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowInventoryAlert implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $inventoryItem;
    public $currentStock;
    public $minStockLevel;

    /**
     * Create a new event instance.
     */
    public function __construct(InventoryItem $inventoryItem)
    {
        $this->inventoryItem = $inventoryItem;
        $this->currentStock = $inventoryItem->quantity;
        $this->minStockLevel = $inventoryItem->min_stock;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('farm-alerts.' . $this->inventoryItem->user_id),
        ];
    }

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith(): array
    {
        return [
            'inventory_item' => [
                'id' => $this->inventoryItem->id,
                'name' => $this->inventoryItem->name,
                'category' => $this->inventoryItem->category,
                'current_stock' => $this->currentStock,
                'min_stock_level' => $this->minStockLevel,
                'unit' => $this->inventoryItem->unit,
            ],
            'alert_type' => 'low_inventory',
            'message' => "Low stock alert: {$this->inventoryItem->name} is running low ({$this->currentStock} {$this->inventoryItem->unit} remaining)",
            'severity' => $this->currentStock <= 0 ? 'critical' : 'warning',
            'created_at' => now()->toISOString(),
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs(): string
    {
        return 'low-inventory-alert';
    }
}
