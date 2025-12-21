<?php

namespace App\Http\Controllers;

use App\Models\SeedPlanting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SeedPlantingController extends Controller
{
    public function index()
    {
        $seedPlantings = SeedPlanting::where('user_id', Auth::id())
            ->with(['riceVariety'])
            ->latest('planting_date')
            ->get();

        return response()->json($seedPlantings);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rice_variety_id' => 'required|exists:rice_varieties,id',
            'inventory_item_id' => 'nullable|exists:inventory_items,id',
            'planting_date' => 'required|date',
            'expected_transplant_date' => 'nullable|date|after:planting_date',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string',
            'notes' => 'nullable|string',
            'batch_id' => 'nullable|string|max:50',
        ]);

        // Deduct from inventory if inventory_item_id is provided
        if (!empty($validated['inventory_item_id'])) {
            $inventoryItem = \App\Models\InventoryItem::find($validated['inventory_item_id']);

            if ($inventoryItem) {
                if (!$inventoryItem->removeStock($validated['quantity'])) {
                    return response()->json(['message' => "Insufficient stock. Available: {$inventoryItem->current_stock} {$inventoryItem->unit}"], 422);
                }

                // Log transaction
                \App\Models\InventoryTransaction::create([
                    'inventory_item_id' => $inventoryItem->id,
                    'user_id' => Auth::id(),
                    'transaction_type' => 'out',
                    'quantity' => $validated['quantity'],
                    'unit_cost' => $inventoryItem->unit_price,
                    'total_cost' => $validated['quantity'] * ($inventoryItem->unit_price ?? 0),
                    'reference_type' => 'SeedPlanting',
                    'reference_id' => null, // Will update after creation
                    'notes' => 'Used for nursery batch',
                    'transaction_date' => now(),
                ]);
            }
        }

        $seedPlanting = SeedPlanting::create([
            'user_id' => Auth::id(),
            'rice_variety_id' => $validated['rice_variety_id'],
            'planting_date' => $validated['planting_date'],
            'expected_transplant_date' => $validated['expected_transplant_date'] ?? null,
            'quantity' => $validated['quantity'],
            'unit' => $validated['unit'],
            'notes' => $validated['notes'] ?? null,
            'batch_id' => $validated['batch_id'] ?? null,
            'status' => 'sown'
        ]);

        // Update transaction reference if exists
        $latestTransaction = \App\Models\InventoryTransaction::where('inventory_item_id', $validated['inventory_item_id'] ?? null)
            ->where('reference_type', 'SeedPlanting')
            ->whereNull('reference_id')
            ->latest()
            ->first();

        if ($latestTransaction) {
            $latestTransaction->update(['reference_id' => $seedPlanting->id]);
        }

        return response()->json($seedPlanting, 201);
    }

    public function show(SeedPlanting $seedPlanting)
    {
        if ($seedPlanting->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($seedPlanting->load('riceVariety', 'plantings'));
    }

    public function update(Request $request, SeedPlanting $seedPlanting)
    {
        if ($seedPlanting->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'rice_variety_id' => 'exists:rice_varieties,id',
            'planting_date' => 'date',
            'expected_transplant_date' => 'nullable|date|after:planting_date',
            'quantity' => 'numeric|min:0',
            'unit' => 'string',
            'status' => 'in:sown,germinating,ready,transplanted,failed',
            'notes' => 'nullable|string',
            'batch_id' => 'nullable|string|max:50',
        ]);

        $seedPlanting->update($validated);

        return response()->json($seedPlanting);
    }

    public function destroy(SeedPlanting $seedPlanting)
    {
        if ($seedPlanting->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $seedPlanting->delete();

        return response()->json(['message' => 'Seed planting deleted successfully']);
    }

    /**
     * Get ready seed plantings for transplanting
     */
    public function getReady()
    {
        $ready = SeedPlanting::where('user_id', Auth::id())
            ->where('status', 'ready')
            ->with('riceVariety')
            ->get();

        return response()->json($ready);
    }
}
