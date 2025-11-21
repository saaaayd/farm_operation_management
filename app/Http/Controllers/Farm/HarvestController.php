<?php

namespace App\Http\Controllers\Farm;

use App\Http\Controllers\Controller;
use App\Models\Harvest;
use App\Models\Planting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class HarvestController extends Controller
{
    /**
     * Display a listing of harvests
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Harvest::query();
        
        if (!$user->isAdmin()) {
            $query->whereHas('planting.field', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
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
        $planting = Planting::with('field')->findOrFail($request->planting_id);
        $user = $request->user();
        
        if (!$user->isAdmin() && $planting->field->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access to planting'
            ], 403);
        }

        $harvest = Harvest::create([
            'planting_id' => $request->planting_id,
            'harvest_date' => $request->harvest_date,
            'quantity' => $request->quantity,
            'yield' => $request->quantity, // Also set yield for backward compatibility
            'unit' => $request->unit,
            'quality_grade' => $request->quality_grade,
            'price_per_unit' => $request->price_per_unit,
            'total_value' => $request->total_value,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Harvest created successfully',
            'harvest' => $harvest->load(['planting.field', 'planting.riceVariety'])
        ], 201);
    }

    /**
     * Display the specified harvest
     */
    public function show(Request $request, Harvest $harvest): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $harvest->planting->field->user_id !== $user->id) {
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
        
        if (!$user->isAdmin() && $harvest->planting->field->user_id !== $user->id) {
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
        
        if (!$user->isAdmin() && $harvest->planting->field->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $harvest->delete();

        return response()->json([
            'message' => 'Harvest deleted successfully'
        ]);
    }
}