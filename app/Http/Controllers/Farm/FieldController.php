<?php

namespace App\Http\Controllers\Farm;

use App\Http\Controllers\Controller;
use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FieldController extends Controller
{
    /**
    * Display a listing of fields
    */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Field::query();
        
        if (!$user->isAdmin()) {
            $query->where('user_id', $user->id);
        }
        
        $fields = $query->with(['plantings', 'latestWeather'])->get();
        
        return response()->json([
            'fields' => $fields
        ]);
    }
    
    /**
    * Store a newly created field
    */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255', // <-- ADDED THIS
            'location' => 'required|array',
            'location.lat' => 'required|numeric|between:-90,90',
            'location.lon' => 'required|numeric|between:-180,180',
            'location.address' => 'required|string|max:255',
            'soil_type' => 'required|string|max:255',
            'size' => 'required|numeric|min:0',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        $field = Field::create([
            'user_id' => $request->user()->id,
            'name' => $request->name, // <-- ADDED THIS
            'location' => $request->location,
            'soil_type' => $request->soil_type,
            'size' => $request->size,
        ]);
        
        return response()->json([
            'message' => 'Field created successfully',
            'field' => $field->load(['plantings', 'latestWeather'])
        ], 201);
    }
    
    
    /**
    * Display the specified field
    */
    public function show(Request $request, Field $field): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $field->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $field->load([
            'plantings.harvests',
            'plantings.tasks',
            'plantings.expenses',
            'weatherLogs' => function($query) {
                $query->orderBy('recorded_at', 'desc')->limit(10);
            }
        ]);
        
        return response()->json([
            'field' => $field
        ]);
    }
    
    /**
    * Update the specified field
    */
    public function update(Request $request, Field $field): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $field->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255', // <-- ADDED THIS
            'location' => 'sometimes|array',
            'location.lat' => 'sometimes|numeric|between:-90,90',
            'location.lon' => 'sometimes|numeric|between:-180,180',
            'location.address' => 'nullable|string',
            'soil_type' => 'sometimes|string|max:255',
            'size' => 'sometimes|numeric|min:0',
        ]);
        
        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }
        
        // <-- UPDATED THIS
        $field->update($request->only(['name', 'location', 'soil_type', 'size']));
        
        return response()->json([
            'message' => 'Field updated successfully',
            'field' => $field->load(['plantings', 'latestWeather'])
        ]);
    }
    
    /**
    * Remove the specified field
    */
    public function destroy(Request $request, Field $field): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $field->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        // Check if field has active plantings
        $activePlantings = $field->plantings()->whereIn('status', [
            'planted', 'growing', 'ready'
            ])->count();
            
            if ($activePlantings > 0) {
                return response()->json([
                    'message' => 'Cannot delete field with active plantings'
                ], 400);
            }
            
            $field->delete();
            
            return response()->json([
                'message' => 'Field deleted successfully'
            ]);
    } // <-- This brace closes the destroy() function

} // <-- This brace closes the class