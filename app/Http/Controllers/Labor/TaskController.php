<?php

namespace App\Http\Controllers\Labor;

use App\Http\Controllers\Controller;
use App\Models\Task;
use App\Models\Laborer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Task::query();
        
        if (!$user->isAdmin()) {
            // Query for tasks "where has" a laborer that belongs to the current user
            $query->whereHas('laborer', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }
        
        if ($request->has('laborer_id')) {
            $query->where('laborer_id', $request->laborer_id);
        }
        
        if ($request->has('date_from')) {
            $query->where('due_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('due_date', '<=', $request->date_to);
        }
        
        $tasks = $query->with(['laborer', 'relatedEntity'])
            ->orderBy('due_date', 'asc')
            ->get();
        
        return response()->json([
            'tasks' => $tasks
        ]);
    }

    /**
     * Store a newly created task
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'laborer_id' => 'required|exists:laborers,id',
            'priority' => 'required|string|in:low,medium,high,urgent',
            'status' => 'required|string|in:pending,in_progress,completed,cancelled',
            'due_date' => 'required|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'hours_worked' => 'nullable|numeric|min:0',
            'related_entity_type' => 'nullable|string|in:field,planting,harvest',
            'related_entity_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user owns the laborer
        $laborer = Laborer::findOrFail($request->laborer_id);
        $user = $request->user();
        
        if (!$user->isAdmin() && $laborer->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access to laborer'
            ], 403);
        }

        $task = Task::create([
            'title' => $request->title,
            'description' => $request->description,
            'laborer_id' => $request->laborer_id,
            'priority' => $request->priority,
            'status' => $request->status,
            'due_date' => $request->due_date,
            'estimated_hours' => $request->estimated_hours,
            'hours_worked' => $request->hours_worked,
            'related_entity_type' => $request->related_entity_type,
            'related_entity_id' => $request->related_entity_id,
            'notes' => $request->notes,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task->load(['laborer', 'relatedEntity'])
        ], 201);
    }

    /**
     * Display the specified task
     */
    public function show(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $task->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $task->load(['laborer', 'relatedEntity']);

        return response()->json([
            'task' => $task
        ]);
    }

    /**
     * Update the specified task
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $task->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'laborer_id' => 'sometimes|required|exists:laborers,id',
            'priority' => 'sometimes|required|string|in:low,medium,high,urgent',
            'status' => 'sometimes|required|string|in:pending,in_progress,completed,cancelled',
            'due_date' => 'sometimes|required|date',
            'estimated_hours' => 'nullable|numeric|min:0',
            'hours_worked' => 'nullable|numeric|min:0',
            'related_entity_type' => 'nullable|string|in:field,planting,harvest',
            'related_entity_id' => 'nullable|integer',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $task->update($request->only([
            'title', 'description', 'laborer_id', 'priority', 'status',
            'due_date', 'estimated_hours', 'hours_worked',
            'related_entity_type', 'related_entity_id', 'notes'
        ]));

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task->load(['laborer', 'relatedEntity'])
        ]);
    }

    /**
     * Remove the specified task
     */
    public function destroy(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $task->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully'
        ]);
    }

    /**
     * Update task status
     */
    public function updateStatus(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();
        
        if (!$user->isAdmin() && $task->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,in_progress,completed,cancelled',
            'hours_worked' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $task->update([
            'status' => $request->status,
            'hours_worked' => $request->hours_worked ?? $task->hours_worked,
        ]);

        return response()->json([
            'message' => 'Task status updated successfully',
            'task' => $task->load(['laborer', 'relatedEntity'])
        ]);
    }
}