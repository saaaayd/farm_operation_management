<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    /**
     * Display a listing of orders
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Order::query();
        
        if (!$user->isAdmin()) {
            $query->where('buyer_id', $user->id);
        }
        
        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('date_from')) {
            $query->where('order_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('order_date', '<=', $request->date_to);
        }
        
        $orders = $query->with(['orderItems', 'supplier'])
            ->orderBy('order_date', 'desc')
            ->get();
        
        return response()->json([
            'orders' => $orders
        ]);
    }

    /**
     * Store a newly created order
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'supplier_name' => 'required|string|max:255',
            'supplier_contact' => 'nullable|string|max:255',
            'order_date' => 'required|date',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'status' => 'required|string|in:pending,confirmed,shipped,delivered,cancelled',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_price' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $order = Order::create([
                'supplier_name' => $request->supplier_name,
                'supplier_contact' => $request->supplier_contact,
                'order_date' => $request->order_date,
                'expected_delivery_date' => $request->expected_delivery_date,
                'status' => $request->status,
                'notes' => $request->notes,
                'buyer_id' => $request->user()->id,
            ]);

            $totalAmount = 0;

            foreach ($request->items as $item) {
                $orderItem = OrderItem::create([
                    'order_id' => $order->id,
                    'inventory_item_id' => $item['inventory_item_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'] ?? 0,
                ]);

                $totalAmount += $orderItem->quantity * $orderItem->unit_price;
            }

            $order->update(['total_amount' => $totalAmount]);

            DB::commit();

            return response()->json([
                'message' => 'Order created successfully',
                'order' => $order->load(['orderItems.inventoryItem'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified order
     */
    public function show(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $order->buyer_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $order->load(['orderItems.inventoryItem']);

        return response()->json([
            'order' => $order
        ]);
    }

    /**
     * Update the specified order
     */
    public function update(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $order->buyer_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'supplier_name' => 'sometimes|required|string|max:255',
            'supplier_contact' => 'nullable|string|max:255',
            'order_date' => 'sometimes|required|date',
            'expected_delivery_date' => 'nullable|date|after:order_date',
            'status' => 'sometimes|required|string|in:pending,confirmed,shipped,delivered,cancelled',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order->update($request->only([
            'supplier_name', 'supplier_contact', 'order_date',
            'expected_delivery_date', 'status', 'notes'
        ]));

        return response()->json([
            'message' => 'Order updated successfully',
            'order' => $order->load(['orderItems.inventoryItem'])
        ]);
    }

    /**
     * Remove the specified order
     */
    public function destroy(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $order->buyer_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $order->delete();

        return response()->json([
            'message' => 'Order deleted successfully'
        ]);
    }

    /**
     * Update order status
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $order->buyer_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,confirmed,shipped,delivered,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order->update(['status' => $request->status]);

        // If order is delivered, update inventory stock
        if ($request->status === 'delivered') {
            foreach ($order->orderItems as $orderItem) {
                $inventoryItem = $orderItem->inventoryItem;
                $inventoryItem->current_stock += $orderItem->quantity;
                $inventoryItem->save();
            }
        }

        return response()->json([
            'message' => 'Order status updated successfully',
            'order' => $order->load(['orderItems.inventoryItem'])
        ]);
    }
}