<?php

namespace App\Services;

use App\Models\InventoryItem;
use App\Models\User;
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
            $query->whereRaw('quantity <= min_stock');
        }

        return $query->orderBy('name')->get();
    }

    /**
     * Create a new inventory item
     */
    public function createInventoryItem($itemData)
    {
        try {
            $item = InventoryItem::create([
                'user_id' => $itemData['user_id'],
                'name' => $itemData['name'],
                'description' => $itemData['description'] ?? '',
                'category' => $itemData['category'],
                'quantity' => $itemData['quantity'] ?? 0,
                'unit' => $itemData['unit'],
                'price' => $itemData['price'] ?? 0,
                'min_stock' => $itemData['min_stock'] ?? 0,
                'supplier' => $itemData['supplier'] ?? '',
                'quality_grade' => $itemData['quality_grade'] ?? 'A',
            ]);

            return $item;
        } catch (\Exception $e) {
            Log::error('Failed to create inventory item: ' . $e->getMessage());
            throw new InventoryException(
                'Failed to create inventory item',
                null,
                0,
                $e
            );
        }
    }

    /**
     * Update inventory item
     */
    public function updateInventoryItem($itemId, $itemData)
    {
        try {
            $item = InventoryItem::findOrFail($itemId);
            $item->update($itemData);
            return $item;
        } catch (\Exception $e) {
            Log::error('Failed to update inventory item: ' . $e->getMessage());
            throw new InventoryException(
                'Failed to update inventory item',
                $itemId,
                0,
                $e
            );
        }
    }

    /**
     * Delete inventory item
     */
    public function deleteInventoryItem($itemId)
    {
        try {
            $item = InventoryItem::findOrFail($itemId);
            $item->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete inventory item: ' . $e->getMessage());
            throw new InventoryException(
                'Failed to delete inventory item',
                $itemId,
                0,
                $e
            );
        }
    }

    /**
     * Update inventory quantity
     */
    public function updateQuantity($itemId, $quantity, $operation = 'set')
    {
        try {
            $item = InventoryItem::findOrFail($itemId);
            
            switch ($operation) {
                case 'add':
                    $item->increment('quantity', $quantity);
                    break;
                case 'subtract':
                    $item->decrement('quantity', $quantity);
                    break;
                case 'set':
                default:
                    $item->update(['quantity' => $quantity]);
                    break;
            }

            // Check for low stock alert
            $this->checkLowStockAlert($item);

            return $item->fresh();
        } catch (\Exception $e) {
            Log::error('Failed to update inventory quantity: ' . $e->getMessage());
            throw new InventoryException(
                'Failed to update inventory quantity',
                $itemId,
                0,
                $e
            );
        }
    }

    /**
     * Check for low stock alerts
     */
    public function checkLowStockAlert(InventoryItem $item)
    {
        if ($item->quantity <= $item->min_stock) {
            event(new LowInventoryAlert($item));
        }
    }

    /**
     * Get low stock items
     */
    public function getLowStockItems($userId = null)
    {
        $query = InventoryItem::whereRaw('quantity <= min_stock');

        if ($userId) {
            $query->where('user_id', $userId);
        }

        return $query->get();
    }

    /**
     * Get inventory statistics
     */
    public function getInventoryStats($userId)
    {
        $items = InventoryItem::where('user_id', $userId)->get();

        return [
            'total_items' => $items->count(),
            'total_value' => $items->sum(function ($item) {
                return $item->quantity * $item->price;
            }),
            'low_stock_items' => $items->where('quantity', '<=', function ($item) {
                return $item->min_stock;
            })->count(),
            'out_of_stock_items' => $items->where('quantity', 0)->count(),
            'categories' => $items->groupBy('category')->map(function ($categoryItems) {
                return [
                    'count' => $categoryItems->count(),
                    'total_value' => $categoryItems->sum(function ($item) {
                        return $item->quantity * $item->price;
                    }),
                ];
            }),
        ];
    }

    /**
     * Bulk update inventory
     */
    public function bulkUpdateInventory($updates)
    {
        DB::beginTransaction();
        
        try {
            foreach ($updates as $update) {
                $item = InventoryItem::findOrFail($update['id']);
                $item->update($update['data']);
                
                // Check for low stock alert
                $this->checkLowStockAlert($item);
            }

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Bulk inventory update failed: ' . $e->getMessage());
            throw new InventoryException(
                'Bulk inventory update failed',
                null,
                0,
                $e
            );
        }
    }

    /**
     * Get inventory categories
     */
    public function getCategories()
    {
        return [
            ['value' => 'seeds', 'label' => 'Seeds'],
            ['value' => 'fertilizers', 'label' => 'Fertilizers'],
            ['value' => 'pesticides', 'label' => 'Pesticides'],
            ['value' => 'equipment', 'label' => 'Equipment'],
            ['value' => 'produce', 'label' => 'Produce'],
            ['value' => 'tools', 'label' => 'Tools'],
            ['value' => 'materials', 'label' => 'Materials'],
        ];
    }

    /**
     * Search inventory items
     */
    public function searchInventory($userId, $query, $filters = [])
    {
        $searchQuery = InventoryItem::where('user_id', $userId)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%')
                  ->orWhere('supplier', 'like', '%' . $query . '%');
            });

        if (isset($filters['category'])) {
            $searchQuery->where('category', $filters['category']);
        }

        if (isset($filters['low_stock'])) {
            $searchQuery->whereRaw('quantity <= min_stock');
        }

        return $searchQuery->get();
    }
}
