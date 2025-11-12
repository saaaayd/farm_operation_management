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
            // tasks table uses `assigned_to` as the FK to laborers — accept `laborer_id` as a filter param
            $query->where('assigned_to', $request->laborer_id);
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
        // Accept the payload sent by the frontend (task_type, planting_id, assigned_to)
        $validator = Validator::make($request->all(), [
            'task_type' => 'required|string|in:watering,fertilizing,weeding,pest_control,harvesting,maintenance',
            'planting_id' => 'required|exists:plantings,id',
            'due_date' => 'required|date',
            'description' => 'nullable|string',
            'assigned_to' => 'nullable|exists:laborers,id',
            'status' => 'nullable|string|in:pending,in_progress,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // If an assigned laborer was provided, ensure current user owns that laborer (unless admin)
        if ($request->filled('assigned_to')) {
            $laborer = Laborer::findOrFail($request->assigned_to);
            if (!$user->isAdmin() && $laborer->user_id !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized access to laborer'
                ], 403);
            }
        }

        // Create task using the fields present in the DB/migration and frontend payload
        $task = Task::create([
            'planting_id' => $request->planting_id,
            'task_type' => $request->task_type,
            'due_date' => $request->due_date,
            'description' => $request->description,
            'assigned_to' => $request->assigned_to ?? null,
            'status' => $request->status ?? Task::STATUS_PENDING,
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
        
        // Authorize access: tasks are owned via the laborer->user_id relationship (if assigned)
        if (!$user->isAdmin()) {
            $ownerId = optional($task->laborer)->user_id;
            if ($ownerId !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized access'
                ], 403);
            }
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
        
        if (!$user->isAdmin()) {
            $ownerId = optional($task->laborer)->user_id;
            if ($ownerId !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized access'
                ], 403);
            }
        }

        $validator = Validator::make($request->all(), [
            'task_type' => 'sometimes|required|string|in:watering,fertilizing,weeding,pest_control,harvesting,maintenance',
            'planting_id' => 'sometimes|required|exists:plantings,id',
            'due_date' => 'sometimes|required|date',
            'description' => 'nullable|string',
            'assigned_to' => 'sometimes|nullable|exists:laborers,id',
            'status' => 'sometimes|required|string|in:pending,in_progress,completed,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $task->update($request->only([
            'planting_id', 'task_type', 'description', 'assigned_to', 'status',
            'due_date'
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
        
        if (!$user->isAdmin()) {
            $ownerId = optional($task->laborer)->user_id;
            if ($ownerId !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized access'
                ], 403);
            }
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
        
        if (!$user->isAdmin()) {
            $ownerId = optional($task->laborer)->user_id;
            if ($ownerId !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized access'
                ], 403);
            }
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

    /**
     * Mark a task as completed (route: POST /tasks/{task}/complete)
     */
    public function markCompleted(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        if (!$user->isAdmin()) {
            $ownerId = optional($task->laborer)->user_id;
            if ($ownerId !== $user->id) {
                return response()->json([
                    'message' => 'Unauthorized access'
                ], 403);
            }
        }

        $task->update(['status' => Task::STATUS_COMPLETED]);

        return response()->json([
            'message' => 'Task marked as completed',
            'task' => $task->load(['laborer', 'relatedEntity'])
        ]);
    }
}