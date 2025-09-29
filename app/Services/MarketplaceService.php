<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventoryItem;
use App\Models\User;
use App\Events\NewOrderPlaced;
use App\Exceptions\MarketplaceException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MarketplaceService
{
    /**
     * Get available products for marketplace
     */
    public function getAvailableProducts($filters = [])
    {
        $query = InventoryItem::where('category', 'produce')
            ->where('quantity', '>', 0)
            ->with(['user:id,name']);

        // Apply filters
        if (isset($filters['variety'])) {
            $query->where('name', 'like', '%' . $filters['variety'] . '%');
        }

        if (isset($filters['farmer_id'])) {
            $query->where('user_id', $filters['farmer_id']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        return $query->get()->map(function ($item) {
            return [
                'id' => $item->id,
                'name' => $item->name,
                'description' => $item->description,
                'price' => $item->price,
                'quantity' => $item->quantity,
                'unit' => $item->unit,
                'category' => $item->category,
                'quality_grade' => $item->quality_grade ?? 'A',
                'farmer' => [
                    'id' => $item->user->id,
                    'name' => $item->user->name,
                ],
                'created_at' => $item->created_at,
            ];
        });
    }

    /**
     * Create a new order
     */
    public function createOrder($orderData, $buyerId)
    {
        DB::beginTransaction();
        
        try {
            // Create order
            $order = Order::create([
                'order_number' => $this->generateOrderNumber(),
                'buyer_id' => $buyerId,
                'status' => 'pending',
                'total_amount' => 0,
                'shipping_address' => $orderData['shipping_address'] ?? '',
                'notes' => $orderData['notes'] ?? '',
            ]);

            $totalAmount = 0;

            // Create order items
            foreach ($orderData['items'] as $itemData) {
                $inventoryItem = InventoryItem::find($itemData['id']);
                
                if (!$inventoryItem || $inventoryItem->quantity < $itemData['quantity']) {
                    throw new MarketplaceException(
                        "Insufficient stock for {$inventoryItem->name}",
                        $order->id ?? null
                    );
                }

                $itemTotal = $inventoryItem->price * $itemData['quantity'];
                $totalAmount += $itemTotal;

                OrderItem::create([
                    'order_id' => $order->id,
                    'inventory_item_id' => $inventoryItem->id,
                    'quantity' => $itemData['quantity'],
                    'price' => $inventoryItem->price,
                    'total' => $itemTotal,
                ]);

                // Update inventory
                $inventoryItem->decrement('quantity', $itemData['quantity']);
            }

            // Update order total
            $order->update(['total_amount' => $totalAmount]);

            // Fire event
            event(new NewOrderPlaced($order));

            DB::commit();
            
            return $order->load(['orderItems.inventoryItem', 'buyer']);
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update order status
     */
    public function updateOrderStatus($orderId, $status, $userId = null)
    {
        $order = Order::findOrFail($orderId);
        
        // Check if user has permission to update this order
        if ($userId && $order->farmer_id !== $userId && $order->buyer_id !== $userId) {
            throw new \Exception('Unauthorized to update this order');
        }

        $order->update(['status' => $status]);
        
        return $order;
    }

    /**
     * Get orders for a user
     */
    public function getUserOrders($userId, $userRole)
    {
        $query = Order::with(['orderItems.inventoryItem', 'buyer', 'farmer']);

        if ($userRole === 'buyer') {
            $query->where('buyer_id', $userId);
        } elseif ($userRole === 'farmer') {
            $query->whereHas('orderItems.inventoryItem', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            });
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Get marketplace statistics
     */
    public function getMarketplaceStats()
    {
        return [
            'total_products' => InventoryItem::where('category', 'produce')->count(),
            'available_products' => InventoryItem::where('category', 'produce')->where('quantity', '>', 0)->count(),
            'total_orders' => Order::count(),
            'pending_orders' => Order::where('status', 'pending')->count(),
            'completed_orders' => Order::where('status', 'delivered')->count(),
            'total_sales' => Order::where('status', 'delivered')->sum('total_amount'),
        ];
    }

    /**
     * Get farmer's sales data
     */
    public function getFarmerSales($farmerId, $period = '30')
    {
        $startDate = now()->subDays($period);
        
        $orders = Order::whereHas('orderItems.inventoryItem', function ($q) use ($farmerId) {
            $q->where('user_id', $farmerId);
        })
        ->where('created_at', '>=', $startDate)
        ->with(['orderItems.inventoryItem'])
        ->get();

        $totalSales = $orders->sum('total_amount');
        $totalOrders = $orders->count();
        $totalQuantity = $orders->sum(function ($order) {
            return $order->orderItems->sum('quantity');
        });

        return [
            'total_sales' => $totalSales,
            'total_orders' => $totalOrders,
            'total_quantity_sold' => $totalQuantity,
            'average_order_value' => $totalOrders > 0 ? $totalSales / $totalOrders : 0,
            'orders' => $orders,
        ];
    }

    /**
     * Generate unique order number
     */
    private function generateOrderNumber()
    {
        do {
            $orderNumber = 'ORD-' . strtoupper(uniqid());
        } while (Order::where('order_number', $orderNumber)->exists());

        return $orderNumber;
    }

    /**
     * Get product categories
     */
    public function getCategories()
    {
        return [
            ['value' => 'IR64', 'label' => 'IR64 Rice'],
            ['value' => 'Jasmine', 'label' => 'Jasmine Rice'],
            ['value' => 'Basmati', 'label' => 'Basmati Rice'],
            ['value' => 'Arborio', 'label' => 'Arborio Rice'],
            ['value' => 'Brown Rice', 'label' => 'Brown Rice'],
            ['value' => 'Sticky Rice', 'label' => 'Sticky Rice'],
            ['value' => 'Wild Rice', 'label' => 'Wild Rice'],
        ];
    }

    /**
     * Search products
     */
    public function searchProducts($query, $filters = [])
    {
        $searchQuery = InventoryItem::where('category', 'produce')
            ->where('quantity', '>', 0)
            ->where(function ($q) use ($query) {
                $q->where('name', 'like', '%' . $query . '%')
                  ->orWhere('description', 'like', '%' . $query . '%');
            })
            ->with(['user:id,name']);

        // Apply additional filters
        if (isset($filters['min_price'])) {
            $searchQuery->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $searchQuery->where('price', '<=', $filters['max_price']);
        }

        return $searchQuery->get();
    }
}
