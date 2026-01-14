<?php

namespace App\Http\Controllers\Farm;

use App\Http\Controllers\Controller;
use App\Models\Field;
use App\Models\Farm;
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
        try {
            $user = $request->user();

            $query = Field::where('user_id', $user->id);

            // Eager load plantings with rice variety and latest weather using a constraint
            $fields = $query->with([
                'plantings.riceVariety',
                'weatherLogs' => function ($query) {
                    $query->orderBy('recorded_at', 'desc')->limit(1);
                }
            ])->get();

            // Map weatherLogs to latestWeather and compute current_crop for each field
            $fields->each(function ($field) {
                $field->setRelation('latestWeather', $field->weatherLogs->first());
                $field->unsetRelation('weatherLogs');

                // Get current active planting from eager loaded plantings
                // Check for rice plantings (case-insensitive comparison)
                $currentPlanting = $field->plantings
                    ->filter(function ($planting) {
                        $cropType = strtolower(trim($planting->crop_type ?? ''));
                        $status = $planting->status ?? '';
                        // Check if it's rice (case-insensitive) and has valid status
                        // Also include plantings with rice_variety_id even if crop_type is different
                        $isRice = $cropType === 'rice' || !empty($planting->rice_variety_id);
                        $hasValidStatus = in_array($status, ['planned', 'planted', 'growing']);
                        return $isRice && $hasValidStatus;
                    })
                    ->sortByDesc('planting_date')
                    ->first();

                if ($currentPlanting && $currentPlanting->riceVariety) {
                    $field->current_crop = $currentPlanting->riceVariety->name;
                } elseif ($currentPlanting && $currentPlanting->crop_type) {
                    $field->current_crop = ucfirst($currentPlanting->crop_type);
                } else {
                    $field->current_crop = null;
                }

                // Calculate available area
                $occupiedArea = $field->plantings
                    ->filter(function ($planting) {
                        return in_array($planting->status, ['planned', 'planted', 'growing', 'ready']);
                    })
                    ->sum('area_planted');

                $field->available_area = max(0, $field->size - $occupiedArea);
            });

            return response()->json([
                'fields' => $fields
            ]);
        } catch (\Exception $e) {
            \Log::error('Error fetching fields', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to fetch fields',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }

    /**
     * Store a newly created field
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'location' => 'required|array',
                'location.lat' => 'required|numeric|between:-90,90',
                'location.lon' => 'required|numeric|between:-180,180',
                'location.address' => 'required|string|max:255',
                'soil_type' => 'required|string|max:255',
                'size' => 'required|numeric|min:0',
                'irrigation_type' => 'nullable|string|max:255',
                'water_source' => 'nullable|string|max:255',
                'water_access' => 'nullable|string|in:excellent,good,moderate,poor,very_poor',
                'drainage_quality' => 'nullable|string|in:excellent,good,moderate,poor',
                'nickname' => 'nullable|string|max:255',
                'planting_method' => 'nullable|string|max:255',
                'cropping_seasons' => 'nullable|integer|min:1|max:3',
                'target_yield' => 'nullable|numeric|min:0',
                'infrastructure_notes' => 'nullable|string|max:1000',
                'previous_crop' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();

            // Get or create a farm for the user
            $farm = Farm::where('user_id', $user->id)->latest()->first();

            if (!$farm) {
                // Create a default farm if none exists
                $farm = Farm::create([
                    'user_id' => $user->id,
                    'name' => $user->name . "'s Farm",
                    'location' => $request->location['address'] ?? 'Not specified',
                    'total_area' => 0,
                    'cultivated_area' => 0,
                    'is_setup_complete' => false,
                ]);
            }

            $field = Field::create([
                'user_id' => $user->id,
                'farm_id' => $farm->id,
                'name' => $request->name,
                'location' => $request->location,
                'soil_type' => $request->soil_type,
                'size' => $request->size,
                'irrigation_type' => $request->irrigation_type,
                'water_source' => $request->water_source,
                'water_access' => $request->water_access ?? 'good',
                'drainage_quality' => $request->drainage_quality ?? 'good',
                'nickname' => $request->nickname,
                'planting_method' => $request->planting_method,
                'cropping_seasons' => $request->cropping_seasons,
                'target_yield' => $request->target_yield,
                'infrastructure_notes' => $request->infrastructure_notes,
                'previous_crop' => $request->previous_crop,
                'notes' => $request->notes,
            ]);

            return response()->json([
                'message' => 'Field created successfully',
                'field' => $field->load(['plantings', 'latestWeather'])
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error creating field', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Failed to create field',
                'error' => config('app.debug') ? $e->getMessage() : 'An error occurred'
            ], 500);
        }
    }


    /**
     * Display the specified field
     */
    public function show(Request $request, Field $field): JsonResponse
    {
        $user = $request->user();

        if ($field->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $field->load([
            'plantings.harvests',
            'plantings.tasks',
            'plantings.expenses',
            'weatherLogs' => function ($query) {
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

        if ($field->user_id !== $user->id) {
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
            'irrigation_type' => 'nullable|string|max:255',
            'water_source' => 'nullable|string|max:255',
            'water_access' => 'nullable|string|in:excellent,good,moderate,poor,very_poor',
            'drainage_quality' => 'nullable|string|in:excellent,good,moderate,poor',
            'nickname' => 'nullable|string|max:255',
            'planting_method' => 'nullable|string|max:255',
            'cropping_seasons' => 'nullable|integer|min:1|max:3',
            'target_yield' => 'nullable|numeric|min:0',
            'infrastructure_notes' => 'nullable|string|max:1000',
            'previous_crop' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // <-- UPDATED THIS
        $field->update($request->only([
            'name',
            'location',
            'soil_type',
            'size',
            'irrigation_type',
            'water_source',
            'water_access',
            'drainage_quality',
            'nickname',
            'planting_method',
            'cropping_seasons',
            'target_yield',
            'infrastructure_notes',
            'previous_crop',
            'notes'
        ]));

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

        if ($field->user_id !== $user->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check if field has active plantings
        $activePlantings = $field->plantings()->whereIn('status', [
            'planted',
            'growing',
            'ready'
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