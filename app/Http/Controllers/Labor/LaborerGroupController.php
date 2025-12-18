<?php

namespace App\Http\Controllers\Labor;

use App\Http\Controllers\Controller;
use App\Models\LaborerGroup;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Models\Task;

class LaborerGroupController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $groups = LaborerGroup::where('user_id', $request->user()->id)
            ->withCount('laborers')
            ->get();

        return response()->json([
            'groups' => $groups
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7', // e.g., #RRGGBB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $group = LaborerGroup::create([
            'name' => $request->name,
            'description' => $request->description,
            'color' => $request->color ?? '#10B981',
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Group created successfully',
            'group' => $group
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LaborerGroup $laborerGroup): JsonResponse
    {
        // Ensure user owns the group
        if ($laborerGroup->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'color' => 'nullable|string|max:7',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $laborerGroup->update($request->only(['name', 'description', 'color']));

        return response()->json([
            'message' => 'Group updated successfully',
            'group' => $laborerGroup
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, LaborerGroup $laborerGroup): JsonResponse
    {
        if ($laborerGroup->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Load members and tasks assigned directly to the group
        $laborerGroup->load(['laborers', 'tasks.planting.field', 'tasks.laborer']);

        // Calculate stats
        $tasks = $laborerGroup->tasks;
        $activeTaskCount = $tasks->whereIn('status', [Task::STATUS_PENDING, Task::STATUS_IN_PROGRESS])->count();
        $completedTaskCount = $tasks->where('status', Task::STATUS_COMPLETED)->count();

        // Task breakdown by type
        $taskTypeStats = $tasks->groupBy('task_type')->map->count();

        // Total Hourly Cost (sum of rates for hourly workers)
        // Check if rate is null, default to 0
        $totalHourlyCost = $laborerGroup->laborers
            ->filter(function ($laborer) {
                // Assume default is hourly if not specified, or explicitly check 'hourly'
                return strtolower($laborer->rate_type ?? 'hourly') === 'hourly';
            })
            ->sum('rate');

        $laborerGroup->stats = [
            'active_tasks' => $activeTaskCount,
            'completed_tasks' => $completedTaskCount,
            'total_hourly_cost' => $totalHourlyCost,
            'task_type_breakdown' => $taskTypeStats,
        ];

        return response()->json([
            'group' => $laborerGroup
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, LaborerGroup $laborerGroup): JsonResponse
    {
        if ($laborerGroup->user_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $laborerGroup->delete();

        return response()->json([
            'message' => 'Group deleted successfully'
        ]);
    }
}
