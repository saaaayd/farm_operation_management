<?php

namespace App\Http\Controllers\Farm;

use App\Http\Controllers\Controller;
use App\Models\PestIncident;
use App\Models\Planting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class PestIncidentController extends Controller
{
    /**
     * List all pest incidents for the user
     */
    public function index(Request $request): JsonResponse
    {
        $query = PestIncident::where('user_id', Auth::id())
            ->with(['planting.field', 'planting.riceVariety'])
            ->orderBy('detected_date', 'desc');

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->severity) {
            $query->where('severity', $request->severity);
        }

        if ($request->planting_id) {
            $query->where('planting_id', $request->planting_id);
        }

        $incidents = $query->paginate(20);

        // Summary stats
        $stats = [
            'total' => PestIncident::where('user_id', Auth::id())->count(),
            'active' => PestIncident::where('user_id', Auth::id())->active()->count(),
            'treated' => PestIncident::where('user_id', Auth::id())->where('status', 'treated')->count(),
            'resolved' => PestIncident::where('user_id', Auth::id())->where('status', 'resolved')->count(),
        ];

        return response()->json([
            'incidents' => $incidents,
            'stats' => $stats,
        ]);
    }

    /**
     * Create a new pest incident
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'planting_id' => 'required|exists:plantings,id',
            'pest_type' => 'required|in:' . implode(',', PestIncident::TYPES),
            'pest_name' => 'required|string|max:100',
            'severity' => 'required|in:' . implode(',', PestIncident::SEVERITIES),
            'affected_area' => 'nullable|numeric|min:0',
            'detected_date' => 'required|date',
            'symptoms' => 'nullable|string|max:1000',
            'treatment_applied' => 'nullable|string|max:500',
            'treatment_date' => 'nullable|date',
            'treatment_cost' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        // Verify planting belongs to user
        $planting = Planting::whereHas('field', function ($q) {
            $q->where('user_id', Auth::id());
        })->find($request->planting_id);

        if (!$planting) {
            return response()->json(['message' => 'Planting not found'], 404);
        }

        $incident = PestIncident::create([
            ...$request->only([
                'planting_id',
                'pest_type',
                'pest_name',
                'severity',
                'affected_area',
                'detected_date',
                'symptoms',
                'treatment_applied',
                'treatment_date',
                'treatment_cost',
                'notes'
            ]),
            'user_id' => Auth::id(),
            'status' => $request->treatment_applied ? PestIncident::STATUS_TREATED : PestIncident::STATUS_ACTIVE,
        ]);

        $incident->load(['planting.field']);

        return response()->json([
            'message' => 'Pest incident recorded',
            'incident' => $incident
        ], 201);
    }

    /**
     * Get a specific pest incident
     */
    public function show(PestIncident $pestIncident): JsonResponse
    {
        if ($pestIncident->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $pestIncident->load(['planting.field', 'planting.riceVariety']);

        return response()->json(['incident' => $pestIncident]);
    }

    /**
     * Update a pest incident (e.g., add treatment)
     */
    public function update(Request $request, PestIncident $pestIncident): JsonResponse
    {
        if ($pestIncident->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'pest_type' => 'sometimes|in:' . implode(',', PestIncident::TYPES),
            'pest_name' => 'sometimes|string|max:100',
            'severity' => 'sometimes|in:' . implode(',', PestIncident::SEVERITIES),
            'affected_area' => 'nullable|numeric|min:0',
            'symptoms' => 'nullable|string|max:1000',
            'treatment_applied' => 'nullable|string|max:500',
            'treatment_date' => 'nullable|date',
            'treatment_cost' => 'nullable|numeric|min:0',
            'status' => 'sometimes|in:active,treated,resolved',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed', 'errors' => $validator->errors()], 422);
        }

        $pestIncident->update($request->only([
            'pest_type',
            'pest_name',
            'severity',
            'affected_area',
            'symptoms',
            'treatment_applied',
            'treatment_date',
            'treatment_cost',
            'status',
            'notes'
        ]));

        return response()->json([
            'message' => 'Pest incident updated',
            'incident' => $pestIncident->fresh()
        ]);
    }

    /**
     * Delete a pest incident
     */
    public function destroy(PestIncident $pestIncident): JsonResponse
    {
        if ($pestIncident->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $pestIncident->delete();

        return response()->json(['message' => 'Pest incident deleted']);
    }

    /**
     * Get pest types and common pests for dropdown
     */
    public function options(): JsonResponse
    {
        return response()->json([
            'pest_types' => PestIncident::TYPES,
            'severities' => PestIncident::SEVERITIES,
            'common_pests' => [
                'insect' => ['Rice Stem Borer', 'Brown Planthopper', 'Green Leafhopper', 'Rice Bug', 'Armyworm'],
                'disease' => ['Blast', 'Bacterial Leaf Blight', 'Sheath Blight', 'Tungro', 'Brown Spot'],
                'weed' => ['Barnyard Grass', 'Chinese Sprangletop', 'Red Sprangletop', 'Jungle Rice'],
                'rodent' => ['Rice Field Rat', 'House Mouse'],
                'other' => ['Birds', 'Snails', 'Crabs'],
            ],
        ]);
    }
}
