<?php

namespace App\Http\Controllers\Farm;

use App\Http\Controllers\Controller;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FarmController extends Controller
{
    /**
     * Display a listing of farms
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Farm::where('user_id', $user->id);
        
        $farms = $query->with(['fields', 'user'])->get();
        
        return response()->json([
            'farms' => $farms
        ]);
    }

    /**
     * Store a newly created farm
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'required|array',
            'location.lat' => 'required|numeric|between:-90,90',
            'location.lon' => 'required|numeric|between:-180,180',
            'location.address' => 'nullable|string',
            'size' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $farm = Farm::create([
            'name' => $request->name,
            'description' => $request->description,
            'location' => $request->location,
            'size' => $request->size,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Farm created successfully',
            'farm' => $farm->load('user')
        ], 201);
    }

    /**
     * Display the specified farm
     */
    public function show(Request $request, Farm $farm): JsonResponse
    {
        $user = $request->user();
        
        if ($farm->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $farm->load(['fields.plantings', 'user']);

        return response()->json([
            'farm' => $farm
        ]);
    }

    /**
     * Update the specified farm
     */
    public function update(Request $request, Farm $farm): JsonResponse
    {
        $user = $request->user();
        
        if ($farm->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'location' => 'sometimes|required|array',
            'location.lat' => 'required_with:location|numeric|between:-90,90',
            'location.lon' => 'required_with:location|numeric|between:-180,180',
            'location.address' => 'nullable|string',
            'size' => 'sometimes|required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $farm->update($request->only(['name', 'description', 'location', 'size']));

        return response()->json([
            'message' => 'Farm updated successfully',
            'farm' => $farm->load('user')
        ]);
    }

    /**
     * Remove the specified farm
     */
    public function destroy(Request $request, Farm $farm): JsonResponse
    {
        $user = $request->user();
        
        if ($farm->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $farm->delete();

        return response()->json([
            'message' => 'Farm deleted successfully'
        ]);
    }
}