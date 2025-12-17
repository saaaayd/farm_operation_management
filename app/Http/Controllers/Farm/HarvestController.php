<?php

namespace App\Http\Controllers\Farm;

use App\Http\Controllers\Controller;
use App\Models\Harvest;
use App\Models\Planting;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class HarvestController extends Controller
{
    /**
     * Display a listing of harvests
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Harvest::whereHas('planting.field', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        });
        
        $harvests = $query->with(['planting.field', 'planting.riceVariety'])->get();
        
        return response()->json([
            'harvests' => $harvests
        ]);
    }

    /**
     * Store a newly created harvest
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'planting_id' => 'required|exists:plantings,id',
            'harvest_date' => 'required|date',
            'quantity' => 'required|numeric|min:0',
            'unit' => 'required|string|in:kg,grams,pounds,bushels,tons',
            'quality_grade' => 'nullable|string|in:A,B,C,D',
            'price_per_unit' => 'nullable|numeric|min:0',
            'total_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user owns the planting's field
        $planting = Planting::with(['field', 'riceVariety'])->findOrFail($request->planting_id);
        $user = $request->user();
        
        if ($planting->field->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access to planting'
            ], 403);
        }

        // Map quality_grade to quality enum for backward compatibility
        $qualityMap = [
            'A' => 'excellent',
            'B' => 'good',
            'C' => 'average',
            'D' => 'poor',
        ];
        $quality = $request->quality_grade ? ($qualityMap[$request->quality_grade] ?? 'average') : 'average';

        DB::beginTransaction();
        try {
            $harvest = Harvest::create([
                'planting_id' => $request->planting_id,
                'harvest_date' => $request->harvest_date,
                'quantity' => $request->quantity,
                'yield' => $request->quantity, // Also set yield for backward compatibility
                'unit' => $request->unit,
                'quality' => $quality, // Set quality enum for backward compatibility
                'quality_grade' => $request->quality_grade,
                'price_per_unit' => $request->price_per_unit,
                'total_value' => $request->total_value,
                'notes' => $request->notes,
            ]);

            // Reload harvest with planting relationship for inventory
            $harvest->load('planting.riceVariety', 'planting.field');

            // Add to inventory automatically
            $this->addHarvestToInventory($harvest, $user);

            DB::commit();

            return response()->json([
                'message' => 'Harvest created successfully',
                'harvest' => $harvest->load(['planting.field', 'planting.riceVariety'])
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Display the specified harvest
     */
    public function show(Request $request, Harvest $harvest): JsonResponse
    {
        $user = $request->user();
        
        if ($harvest->planting->field->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $harvest->load(['planting.field', 'planting.riceVariety']);

        return response()->json([
            'harvest' => $harvest
        ]);
    }

    /**
     * Update the specified harvest
     */
    public function update(Request $request, Harvest $harvest): JsonResponse
    {
        $user = $request->user();
        
        if ($harvest->planting->field->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'harvest_date' => 'sometimes|required|date',
            'quantity' => 'sometimes|required|numeric|min:0',
            'unit' => 'sometimes|required|string|in:kg,grams,pounds,bushels,tons',
            'quality_grade' => 'nullable|string|in:A,B,C,D',
            'price_per_unit' => 'nullable|numeric|min:0',
            'total_value' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $updateData = $request->only([
            'harvest_date', 'quantity', 'unit', 'quality_grade',
            'price_per_unit', 'total_value', 'notes'
        ]);
        
        // Map quantity to yield if provided for backward compatibility
        if (isset($updateData['quantity'])) {
            $updateData['yield'] = $updateData['quantity'];
        }
        
        // Map quality_grade to quality enum for backward compatibility
        if (isset($updateData['quality_grade'])) {
            $qualityMap = [
                'A' => 'excellent',
                'B' => 'good',
                'C' => 'average',
                'D' => 'poor',
            ];
            $updateData['quality'] = $updateData['quality_grade'] 
                ? ($qualityMap[$updateData['quality_grade']] ?? 'average') 
                : 'average';
        }
        
        $harvest->update($updateData);

        return response()->json([
            'message' => 'Harvest updated successfully',
            'harvest' => $harvest->load(['planting.field', 'planting.riceVariety'])
        ]);
    }

    /**
     * Remove the specified harvest
     */
    public function destroy(Request $request, Harvest $harvest): JsonResponse
    {
        $user = $request->user();
        
        if ($harvest->planting->field->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $harvest->delete();

        return response()->json([
            'message' => 'Harvest deleted successfully'
        ]);
    }

    /**
     * Add harvest to inventory automatically
     */
    private function addHarvestToInventory(Harvest $harvest, $user): void
    {
        $planting = $harvest->planting;
        if (!$planting) {
            return;
        }

        // Reload planting with relationships if needed
        if (!$planting->relationLoaded('riceVariety') || !$planting->relationLoaded('field')) {
            $planting->load(['riceVariety', 'field']);
        }

        // Determine the product name from rice variety or crop type
        $productName = $planting->riceVariety?->name ?? $planting->crop_type ?? 'Rice';
        
        // If quality grade is provided, append it to the name
        if ($harvest->quality_grade) {
            $productName .= ' (Grade ' . $harvest->quality_grade . ')';
        }

        // Find or create inventory item for this product
        $inventoryItem = InventoryItem::firstOrCreate(
            [
                'user_id' => $user->id,
                'name' => $productName,
                'category' => InventoryItem::CATEGORY_PRODUCE,
                'unit' => $harvest->unit,
            ],
            [
                'description' => 'Harvested from ' . ($planting->field?->name ?? 'field'),
                'current_stock' => 0,
                'minimum_stock' => 0,
                'unit_price' => $harvest->price_per_unit ?? 0,
                'notes' => 'Auto-created from harvest',
            ]
        );

        // Add the harvested quantity to inventory
        $inventoryItem->addStock($harvest->quantity);
        
        // Update unit price if provided and different
        if ($harvest->price_per_unit && $harvest->price_per_unit != $inventoryItem->unit_price) {
            $inventoryItem->unit_price = $harvest->price_per_unit;
            $inventoryItem->save();
        }
    }
}