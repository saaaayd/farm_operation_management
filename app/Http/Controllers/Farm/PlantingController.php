<?php

namespace App\Http\Controllers\Farm;

use App\Http\Controllers\Controller;
use App\Models\Field;
use App\Models\Planting;
use App\Models\RiceVariety;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PlantingController extends Controller
{
    /**
     * Display a listing of plantings
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Planting::query();
        
        if (!$user->isAdmin()) {
            $query->whereHas('field', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        $plantings = $query->with(['field', 'riceVariety'])->get();
        
        return response()->json([
            'plantings' => $plantings
        ]);
    }

    /**
     * Store a newly created planting
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'field_id' => 'required|exists:fields,id',
            'rice_variety_id' => 'nullable|exists:rice_varieties,id',
            'crop_name' => 'nullable|string|max:255',
            'crop_type' => 'nullable|string|max:255',
            'planting_date' => 'required|date',
            'expected_harvest_date' => 'nullable|date|after_or_equal:planting_date',
            'growth_duration' => 'nullable|integer|min:30|max:240',
            'planting_method' => 'nullable|string|in:direct_seeding,transplanting,broadcasting,broadcast',
            'seed_rate' => 'nullable|numeric|min:0',
            'seed_quantity' => 'nullable|numeric|min:0',
            'area_planted' => 'nullable|numeric|min:0',
            'season' => 'nullable|string|in:wet,dry',
            'status' => 'nullable|string|in:planned,planted,growing,ready,harvested,failed',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user owns the field
        $field = Field::findOrFail($request->field_id);
        $user = $request->user();
        
        if (!$user->isAdmin() && $field->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access to field'
            ], 403);
        }

        $plantingDate = Carbon::parse($request->planting_date);

        $expectedHarvest = $request->expected_harvest_date
            ? Carbon::parse($request->expected_harvest_date)
            : (clone $plantingDate)->addDays((int) $request->input('growth_duration', 120));

        $varietyId = $request->input('rice_variety_id');
        if (!$varietyId && $request->filled('variety')) {
            $variety = RiceVariety::where('name', $request->input('variety'))->first();
            if ($variety) {
                $varietyId = $variety->id;
            }
        }
        if (!$varietyId) {
            $varietyId = RiceVariety::value('id');
        }
        if (!$varietyId) {
            return response()->json([
                'message' => 'No rice varieties available. Please add rice varieties first.'
            ], 422);
        }

        $areaPlanted = $request->input('area_planted');
        if (!$areaPlanted || $areaPlanted <= 0) {
            $areaPlanted = $field->size ?? 1;
        }

        $seedRate = $request->input('seed_rate') ?? $request->input('seed_quantity');

        $season = $request->input('season') ?? $this->determineSeasonFromDate($plantingDate);

        $status = $request->input('status', Planting::STATUS_PLANTED);
        if ($plantingDate->isFuture() && $status === Planting::STATUS_PLANTED) {
            $status = Planting::STATUS_PLANNED;
        }

        $planting = Planting::create([
            'field_id' => $request->field_id,
            'rice_variety_id' => $varietyId,
            'crop_type' => $request->input('crop_name') ?? $request->input('crop_type') ?? 'Rice',
            'planting_date' => $plantingDate,
            'expected_harvest_date' => $expectedHarvest,
            'status' => $status,
            'planting_method' => $this->normalizePlantingMethod($request->input('planting_method')),
            'seed_rate' => $seedRate,
            'area_planted' => $areaPlanted,
            'season' => $season,
            'notes' => $request->notes,
        ]);

        return response()->json([
            'message' => 'Planting created successfully',
            'planting' => $planting->load(['field', 'riceVariety'])
        ], 201);
    }

    /**
     * Display the specified planting
     */
    public function show(Request $request, Planting $planting): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $planting->field->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $planting->load([
            'field',
            'riceVariety',
            'plantingStages.riceGrowthStage',
            'harvests',
            'tasks',
        ]);

        return response()->json([
            'planting' => $planting
        ]);
    }

    /**
     * Update the specified planting
     */
    public function update(Request $request, Planting $planting): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $planting->field->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'field_id' => 'sometimes|required|exists:fields,id',
            'rice_variety_id' => 'nullable|exists:rice_varieties,id',
            'crop_type' => 'sometimes|required|string|max:255',
            'planting_date' => 'sometimes|required|date',
            'expected_harvest_date' => 'nullable|date|after_or_equal:planting_date',
            'growth_duration' => 'nullable|integer|min:30|max:240',
            'planting_method' => 'nullable|string|in:direct_seeding,transplanting,broadcasting',
            'seed_rate' => 'nullable|numeric|min:0',
            'area_planted' => 'nullable|numeric|min:0',
            'season' => 'nullable|string|in:wet,dry',
            'status' => 'nullable|string|in:planned,planted,growing,ready,harvested,failed',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $data = [];

        if ($request->has('field_id')) {
            $field = Field::findOrFail($request->field_id);

            if (!$user->isAdmin() && $field->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized access to field'
                ], 403);
            }

            $data['field_id'] = $field->id;
        }

        if ($request->has('rice_variety_id')) {
            $varietyId = $request->input('rice_variety_id');

            if (!$varietyId) {
                $varietyId = RiceVariety::value('id');
            }

            if (!$varietyId) {
                return response()->json([
                    'message' => 'No rice variety available. Please add varieties first.'
                ], 422);
            }

            $data['rice_variety_id'] = $varietyId;
        }

        if ($request->has('crop_type')) {
            $data['crop_type'] = $request->crop_type;
        }

        if ($request->has('planting_method')) {
            $data['planting_method'] = $this->normalizePlantingMethod($request->planting_method);
        }

        if ($request->has('seed_rate')) {
            $data['seed_rate'] = $request->seed_rate;
        }

        if ($request->has('area_planted')) {
            $data['area_planted'] = $request->area_planted;
        }

        if ($request->has('status')) {
            $data['status'] = $request->status;
        }

        if ($request->has('notes')) {
            $data['notes'] = $request->notes;
        }

        $plantingDate = $planting->planting_date;
        if ($request->has('planting_date')) {
            $plantingDate = Carbon::parse($request->planting_date);
            $data['planting_date'] = $plantingDate;
            if (!$request->has('season')) {
                $data['season'] = $this->determineSeasonFromDate($plantingDate);
            }
        }

        if ($request->has('season')) {
            $data['season'] = $request->season;
        }

        if ($request->has('expected_harvest_date')) {
            $data['expected_harvest_date'] = Carbon::parse($request->expected_harvest_date);
        } elseif ($request->filled('growth_duration')) {
            $growthDuration = (int) $request->input('growth_duration');
            if (!$plantingDate) {
                $plantingDate = $planting->planting_date;
            }
            if ($plantingDate) {
                $expectedHarvest = (clone $plantingDate)->addDays($growthDuration);
                $data['expected_harvest_date'] = $expectedHarvest;
            }
        }

        if (!isset($data['status']) && isset($data['planting_date'])) {
            if ($data['planting_date'] instanceof Carbon && $data['planting_date']->isFuture() && $planting->status === Planting::STATUS_PLANTED) {
                $data['status'] = Planting::STATUS_PLANNED;
            } elseif ($data['planting_date'] instanceof Carbon && $data['planting_date']->isPast() && $planting->status === Planting::STATUS_PLANNED) {
                $data['status'] = Planting::STATUS_PLANTED;
            }
        }

        if (!empty($data)) {
            $planting->fill($data);
            $planting->save();
        }

        return response()->json([
            'message' => 'Planting updated successfully',
            'planting' => $planting->load(['field', 'riceVariety'])
        ]);
    }

    /**
     * Remove the specified planting
     */
    public function destroy(Request $request, Planting $planting): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $planting->field->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $planting->delete();

        return response()->json([
            'message' => 'Planting deleted successfully'
        ]);
    }

    private function normalizePlantingMethod(?string $method): string
    {
        return match ($method) {
            'broadcast' => 'broadcasting',
            'direct_seeding', 'transplanting', 'broadcasting' => $method,
            default => 'transplanting',
        };
    }

    private function determineSeasonFromDate(?Carbon $date): string
    {
        if (!$date) {
            return 'wet';
        }

        $month = (int) $date->format('n');

        // Philippines seasons according to PAGASA:
        // Rainy season: June (6) to November (11)
        // Dry season: December (12) to May (5)
        return ($month >= 6 && $month <= 11) ? 'wet' : 'dry';
    }
}