<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Events\LowInventoryAlert;
use App\Exceptions\InventoryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class InventoryService
{
    /**
     * Get inventory items for a user
     */
    public function getUserInventory($userId, $filters = [])
    {
        $query = InventoryItem::where('user_id', $userId);

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['low_stock'])) {
            // Fix: Changed quantity to current_stock
            $query->whereRaw('current_stock <= minimum_stock');
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Create a new inventory item
     */
    public function createInventoryItem($itemData)
    {
        try {
            // Fix: Mapped array keys to match Database Columns exactly
            $item = InventoryItem::create([
                'user_id' => $itemData['user_id'],
                'name' => $itemData['name'],
                'description' => $itemData['description'] ?? '',
                'category' => $itemData['category'],
                'current_stock' => $itemData['quantity'] ?? $itemData['current_stock'] ?? 0, // Handle both inputs
                'unit' => $itemData['unit'],
                'unit_price' => $itemData['price'] ?? $itemData['unit_price'] ?? 0,
                'minimum_stock' => $itemData['min_stock'] ?? $itemData['minimum_stock'] ?? 0,
                'supplier' => $itemData['supplier'] ?? '',
                'location' => $itemData['location'] ?? '',
                'expiry_date' => $itemData['expiry_date'] ?? null,
            ]);

            return $item;
        } catch (\Exception $e) {
            Log::error('Failed to create inventory item: ' . $e->getMessage());
            throw new InventoryException('Failed to create inventory item: ' . $e->getMessage());
        }
    }

    /**
     * Update inventory item
     */
    public function updateInventoryItem($itemId, $itemData)
    {
        try {
            $item = InventoryItem::findOrFail($itemId);

            // Map legacy keys if they exist in update data
            if (isset($itemData['quantity'])) {
                $itemData['current_stock'] = $itemData['quantity'];
                unset($itemData['quantity']);
            }
            if (isset($itemData['min_stock'])) {
                $itemData['minimum_stock'] = $itemData['min_stock'];
                unset($itemData['min_stock']);
            }
            if (isset($itemData['price'])) {
                $itemData['unit_price'] = $itemData['price'];
                unset($itemData['price']);
            }

            $item->update($itemData);
            return $item;
        } catch (\Exception $e) {
            Log::error('Failed to update inventory item: ' . $e->getMessage());
            throw new InventoryException('Failed to update inventory item');
        }
    }

    public function deleteInventoryItem($itemId)
    {
        try {
            $item = InventoryItem::findOrFail($itemId);
            $item->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete inventory item: ' . $e->getMessage());
            throw new InventoryException('Failed to delete inventory item');
        }
    }

    public function updateQuantity($itemId, $quantity, $operation = 'set')
    {
        try {
            $item = InventoryItem::findOrFail($itemId);

            switch ($operation) {
                case 'add':
                    $item->increment('current_stock', $quantity);
                    break;
                case 'subtract':
                    // Prevent negative stock
                    if ($item->current_stock >= $quantity) {
                        $item->decrement('current_stock', $quantity);
                    } else {
                        $item->update(['current_stock' => 0]);
                    }
                    break;
                case 'set':
                default:
                    $item->update(['current_stock' => $quantity]);
                    break;
            }

            $this->checkLowStockAlert($item);
            return $item->fresh();
        } catch (\Exception $e) {
            Log::error('Failed to update inventory quantity: ' . $e->getMessage());
            throw new InventoryException('Failed to update inventory quantity');
        }
    }

    public function checkLowStockAlert(InventoryItem $item)
    {
        if ($item->current_stock <= $item->minimum_stock) {
            event(new LowInventoryAlert($item));
        }
    }

    public function getInventoryStats($userId)
    {
        $items = InventoryItem::where('user_id', $userId)->get();

        return [
            'total_items' => $items->count(),
            'total_value' => $items->sum(function ($item) {
                return $item->current_stock * $item->unit_price;
            }),
            'low_stock_items' => $items->filter(function ($item) {
                return $item->current_stock <= $item->minimum_stock;
            })->count(),
            'out_of_stock_items' => $items->where('current_stock', '<=', 0)->count(),
        ];
    }

    /**
     * Get all inventory categories
     */
    public function getCategories()
    {
        return InventoryItem::CATEGORIES;
    }

    /**
     * Get low stock items for a user
     */
    public function getLowStockItems($userId)
    {
        return InventoryItem::where('user_id', $userId)
            ->whereRaw('current_stock <= minimum_stock')
            ->orderBy('name')
            ->get();
    }
}