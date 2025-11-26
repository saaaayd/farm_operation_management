<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class InventoryItemController extends Controller
{
    /**
     * Display a listing of inventory items
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            
            if (!$user) {
                return response()->json(['message' => 'Unauthenticated'], 401);
            }
            
            $query = InventoryItem::query();
            
            if (!$user->isAdmin()) {
                $query->where('user_id', $user->id);
            }
            
            // Apply filters
            if ($request->has('category') && $request->category) {
                $query->where('category', $request->category);
            }
            
            // Fix: Ensure we use the correct database columns for comparison
            if ($request->has('low_stock')) {
                $query->whereRaw('COALESCE(current_stock, 0) <= COALESCE(minimum_stock, 0)');
            }
            
            $inventoryItems = $query->orderBy('name')->get();
            
            return response()->json([
                'inventory_items' => $inventoryItems
            ]);
        } catch (\Exception $e) {
            Log::error('Inventory index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'message' => 'Failed to fetch inventory items',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    /**
     * Store a newly created inventory item
     */
    public function store(Request $request): JsonResponse
    {
        // Allow both legacy 'quantity' and new 'current_stock'
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|string|max:255',
            'unit' => 'required|string|max:50',
            'current_stock' => 'nullable|numeric|min:0',
            'quantity' => 'nullable|numeric|min:0', // Legacy support
            'minimum_stock' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|numeric|min:0', // Legacy support
            'unit_price' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Normalize Category
            $category = strtolower($request->category);
            $category = match ($category) {
                'fertilizer', 'fertilizers' => 'fertilizer',
                'pesticide', 'pesticides' => 'pesticide',
                'produce', 'harvest', 'harvested_rice' => 'produce',
                default => $category,
            };

            // Normalize Unit
            $unit = strtolower($request->unit);
            $unit = match ($unit) {
                'lbs', 'pounds' => 'pounds',
                'bag', 'bags', 'packet', 'packets' => 'packets',
                'liter', 'liters' => 'liters',
                default => $unit,
            };

            // Normalize Stock Values (Prioritize current_stock, fallback to quantity)
            $currentStock = $request->input('current_stock', $request->input('quantity', 0));
            $minimumStock = $request->input('minimum_stock', $request->input('min_stock', 0));

            $inventoryItem = InventoryItem::create([
                'name' => $request->name,
                'description' => $request->description,
                'category' => $category,
                'unit' => $unit,
                'current_stock' => $currentStock,
                'minimum_stock' => $minimumStock,
                'unit_price' => $request->unit_price ?? 0,
                'supplier' => $request->supplier,
                'location' => $request->location,
                'expiry_date' => $request->expiry_date,
                'notes' => $request->notes,
                'user_id' => $request->user()->id,
            ]);

            return response()->json([
                'message' => 'Inventory item created successfully',
                'inventory_item' => $inventoryItem
            ], 201);

        } catch (\Exception $e) {
            Log::error('Inventory store error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json([
                'message' => 'Failed to create inventory item',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred while creating the item'
            ], 500);
        }
    }

    /**
     * Display the specified inventory item
     */
    public function show(Request $request, InventoryItem $inventoryItem): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $inventoryItem->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'inventory_item' => $inventoryItem
        ]);
    }

    /**
     * Update the specified inventory item
     */
    public function update(Request $request, InventoryItem $inventoryItem): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $inventoryItem->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'sometimes|required|string|max:255',
            'unit' => 'sometimes|required|string|max:50',
            'current_stock' => 'sometimes|numeric|min:0',
            'quantity' => 'sometimes|numeric|min:0',
            'minimum_stock' => 'sometimes|numeric|min:0',
            'min_stock' => 'sometimes|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'supplier' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'expiry_date' => 'nullable|date',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Prepare data for update
        $data = $request->except(['quantity', 'min_stock']); // Remove legacy keys initially

        // Map legacy keys to DB columns if they exist
        if ($request->has('quantity')) {
            $data['current_stock'] = $request->input('quantity');
        }
        if ($request->has('min_stock')) {
            $data['minimum_stock'] = $request->input('min_stock');
        }

        // Normalize Category if present
        if ($request->has('category')) {
            $data['category'] = match (strtolower($request->category)) {
                'fertilizer', 'fertilizers' => 'fertilizer',
                'pesticide', 'pesticides' => 'pesticide',
                'produce', 'harvest', 'harvested_rice' => 'produce',
                default => strtolower($request->category),
            };
        }

        $inventoryItem->update($data);

        return response()->json([
            'message' => 'Inventory item updated successfully',
            'inventory_item' => $inventoryItem
        ]);
    }

    /**
     * Remove the specified inventory item
     */
    public function destroy(Request $request, InventoryItem $inventoryItem): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $inventoryItem->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $inventoryItem->delete();

        return response()->json([
            'message' => 'Inventory item deleted successfully'
        ]);
    }

    /**
     * Update stock for an inventory item
     */
    public function updateStock(Request $request, InventoryItem $inventoryItem): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $inventoryItem->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric',
            'operation' => 'required|string|in:add,subtract,set',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $quantity = $request->quantity;
        
        switch ($request->operation) {
            case 'add':
                $inventoryItem->current_stock += $quantity;
                break;
            case 'subtract':
                $inventoryItem->current_stock -= $quantity;
                if ($inventoryItem->current_stock < 0) {
                    $inventoryItem->current_stock = 0;
                }
                break;
            case 'set':
                $inventoryItem->current_stock = $quantity;
                break;
        }

        $inventoryItem->save();

        return response()->json([
            'message' => 'Stock updated successfully',
            'inventory_item' => $inventoryItem
        ]);
    }

    /**
     * Add stock (POST /inventory/{item}/add-stock)
     */
    public function addStock(Request $request, InventoryItem $item): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && $item->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $item->current_stock = ($item->current_stock ?? 0) + (float)$request->quantity;
        $item->save();

        return response()->json([
            'message' => 'Stock added successfully',
            'inventory_item' => $item
        ]);
    }

    /**
     * Remove stock (POST /inventory/{item}/remove-stock)
     */
    public function removeStock(Request $request, InventoryItem $item): JsonResponse
    {
        $user = $request->user();
        if (!$user->isAdmin() && $item->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|numeric|min:0.01'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $item->current_stock = max(0, ($item->current_stock ?? 0) - (float)$request->quantity);
        $item->save();

        return response()->json([
            'message' => 'Stock removed successfully',
            'inventory_item' => $item
        ]);
    }

    /**
     * Get low stock items
     */
    public function lowStock(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = InventoryItem::query();
        
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }
        
        // Corrected to use current_stock
        $lowStockItems = $query->whereRaw('current_stock <= minimum_stock')
            ->orderBy('name')
            ->get();
        
        return response()->json([
            'low_stock_items' => $lowStockItems
        ]);
    }

    /**
     * Get low stock alerts
     */
    public function lowStockAlerts(Request $request): JsonResponse
    {
        return $this->lowStock($request);
    }

    /**
     * Get transactions for an inventory item
     */
    public function getTransactions(Request $request, InventoryItem $item): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $item->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized access'], 403);
        }

        $transactions = $item->transactions()
            ->with('user:id,name,email')
            ->orderBy('transaction_date', 'desc')
            ->limit(50)
            ->get();

        return response()->json([
            'transactions' => $transactions
        ]);
    }
}