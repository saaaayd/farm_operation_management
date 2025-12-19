<?php

namespace App\Http\Controllers\Labor;

use App\Http\Controllers\Controller;
use App\Models\Laborer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class LaborerController extends Controller
{
    /**
     * Display a listing of laborers
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Laborer::where('user_id', $user->id);

        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('skill_level')) {
            $query->where('skill_level', $request->skill_level);
        }

        if ($request->has('skill_level')) {
            $query->where('skill_level', $request->skill_level);
        }

        $laborers = $query->with('groups')->orderBy('name')->get();

        return response()->json([
            'laborers' => $laborers
        ]);
    }

    /**
     * Store a newly created laborer
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'skill_level' => 'required|string|in:beginner,intermediate,advanced,expert',
            'specialization' => 'nullable|string|max:255',
            'rate' => 'nullable|numeric|min:0',
            'rate_type' => 'required|string|in:daily,per_job',
            'status' => 'required|string|in:active,inactive,on_leave',
            'hire_date' => 'required|date',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:laborer_groups,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $laborer = Laborer::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'address' => $request->address,
            'skill_level' => $request->skill_level,
            'specialization' => $request->specialization,
            'rate' => $request->rate_type === 'per_job' ? null : $request->rate,
            'rate_type' => $request->rate_type,
            'status' => $request->status,
            'hire_date' => $request->hire_date,
            'emergency_contact_name' => $request->emergency_contact_name,
            'emergency_contact_phone' => $request->emergency_contact_phone,
            'notes' => $request->notes,
            'user_id' => $request->user()->id,
        ]);

        if ($request->has('groups')) {
            $laborer->groups()->sync($request->groups);
        }

        $laborer->load('groups');

        return response()->json([
            'message' => 'Laborer created successfully',
            'laborer' => $laborer
        ], 201);
    }

    /**
     * Display the specified laborer
     */
    public function show(Request $request, Laborer $laborer): JsonResponse
    {
        $user = $request->user();

        if ($laborer->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $laborer->load(['tasks', 'wages', 'groups']);

        return response()->json([
            'laborer' => $laborer
        ]);
    }

    /**
     * Update the specified laborer
     */
    public function update(Request $request, Laborer $laborer): JsonResponse
    {
        $user = $request->user();

        if ($laborer->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'skill_level' => 'sometimes|required|string|in:beginner,intermediate,advanced,expert',
            'specialization' => 'nullable|string|max:255',
            'rate' => 'nullable|numeric|min:0',
            'rate_type' => 'sometimes|required|string|in:daily,per_job',
            'status' => 'sometimes|required|string|in:active,inactive,on_leave',
            'hire_date' => 'sometimes|required|date',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'notes' => 'nullable|string',
            'groups' => 'nullable|array',
            'groups.*' => 'exists:laborer_groups,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $laborer->update($request->only([
            'name',
            'phone',
            'email',
            'address',
            'skill_level',
            'specialization',
            'rate',
            'rate_type',
            'status',
            'hire_date',
            'emergency_contact_name',
            'emergency_contact_phone',
            'notes'
        ]));

        if ($request->has('groups')) {
            $laborer->groups()->sync($request->groups);
        }

        $laborer->load('groups');

        return response()->json([
            'message' => 'Laborer updated successfully',
            'laborer' => $laborer
        ]);
    }

    /**
     * Remove the specified laborer
     */
    public function destroy(Request $request, Laborer $laborer): JsonResponse
    {
        $user = $request->user();

        if ($laborer->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $laborer->delete();

        return response()->json([
            'message' => 'Laborer deleted successfully'
        ]);
    }

    /**
     * Get laborer performance summary
     */
    public function performance(Request $request, Laborer $laborer): JsonResponse
    {
        $user = $request->user();

        if ($laborer->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $tasks = $laborer->tasks();

        if ($request->has('date_from')) {
            $tasks->where('created_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $tasks->where('created_at', '<=', $request->date_to);
        }

        $completedTasks = $tasks->where('status', 'completed')->count();
        $totalTasks = $tasks->count();
        $totalHours = $tasks->where('status', 'completed')->sum('hours_worked');
        $totalWages = $laborer->wages()->sum('amount');

        return response()->json([
            'laborer' => $laborer,
            'performance' => [
                'total_tasks' => $totalTasks,
                'completed_tasks' => $completedTasks,
                'completion_rate' => $totalTasks > 0 ? round(($completedTasks / $totalTasks) * 100, 2) : 0,
                'total_hours' => $totalHours,
                'total_wages' => $totalWages,
            ]
        ]);
    }
}