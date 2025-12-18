<?php

namespace App\Http\Controllers\Labor;

use App\Http\Controllers\Controller;
use App\Models\Planting;
use App\Models\Task;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class TaskController extends Controller
{
    /**
     * Display a listing of tasks
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Task::query()->with(['planting.field', 'laborer', 'laborerGroup']);

        // Only include tasks that have a planting (planting_id is not null)
        $query->whereNotNull('planting_id');

        // Only get tasks that have a planting with a field owned by the user
        $query->whereHas('planting', function ($q) use ($user) {
            $q->whereHas('field', function ($fieldQuery) use ($user) {
                $fieldQuery->where('user_id', $user->id);
            });
        });

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('task_type')) {
            $query->where('task_type', $request->task_type);
        }

        if ($request->filled('planting_id')) {
            $query->where('planting_id', $request->planting_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('due_date', '>=', Carbon::parse($request->date_from));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('due_date', '<=', Carbon::parse($request->date_to));
        }

        $tasks = $query->orderBy('due_date', 'asc')->get();

        return response()->json([
            'tasks' => $tasks,
        ]);
    }

    /**
     * Store a newly created task
     */
    public function store(Request $request): JsonResponse
    {
        // Accept the payload sent by the frontend (task_type, planting_id, assigned_to, laborer_group_id)
        $validator = Validator::make($request->all(), [
            'planting_id' => ['required', 'exists:plantings,id'],
            'task_type' => ['required', 'string', Rule::in($this->taskTypeOptions())],
            'due_date' => ['required', 'date'],
            'description' => ['required', 'string'],
            'status' => ['nullable', 'string', Rule::in($this->statusOptions())],
            'assigned_to' => ['nullable', 'exists:laborers,id'],
            'laborer_group_id' => ['nullable', 'exists:laborer_groups,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $planting = Planting::with('field')->findOrFail($request->planting_id);
        $user = $request->user();

        if (!$this->ownsPlanting($user->id, $planting)) {
            return response()->json([
                'message' => 'Unauthorized access to planting',
            ], 403);
        }

        // Create task using the fields present in the DB/migration and frontend payload
        $task = Task::create([
            'planting_id' => $planting->id,
            'task_type' => $request->task_type,
            'due_date' => Carbon::parse($request->due_date),
            'description' => $request->description,
            'status' => $request->input('status', Task::STATUS_PENDING),
            'assigned_to' => $request->assigned_to,
            'laborer_group_id' => $request->laborer_group_id,
        ]);

        $task->load(['planting.field', 'laborer', 'laborerGroup']);

        return response()->json([
            'message' => 'Task created successfully',
            'task' => $task,
        ], 201);
    }

    /**
     * Display the specified task
     */
    public function show(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        if (!$this->ownsTask($user->id, $task)) {
            return response()->json([
                'message' => 'Unauthorized access',
            ], 403);
        }

        $task->load(['planting.field', 'laborer', 'laborerGroup']);

        return response()->json([
            'task' => $task,
        ]);
    }

    /**
     * Update the specified task
     */
    public function update(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        if (!$this->ownsTask($user->id, $task)) {
            return response()->json([
                'message' => 'Unauthorized access',
            ], 403);
        }

        $requestData = $request->all();

        $validator = Validator::make($requestData, [
            'planting_id' => ['sometimes', 'required', 'exists:plantings,id'],
            'task_type' => ['sometimes', 'required', 'string', Rule::in($this->taskTypeOptions())],
            'due_date' => ['sometimes', 'required', 'date'],
            'description' => ['sometimes', 'nullable', 'string'],
            'status' => ['sometimes', 'required', 'string', Rule::in($this->statusOptions())],
            'assigned_to' => ['nullable', 'exists:laborers,id'],
            'laborer_group_id' => ['nullable', 'exists:laborer_groups,id'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $data = [];

        if ($request->has('planting_id')) {
            $planting = Planting::with('field')->findOrFail($request->planting_id);

            if (!$this->ownsPlanting($user->id, $planting)) {
                return response()->json([
                    'message' => 'Unauthorized access to planting',
                ], 403);
            }

            $data['planting_id'] = $planting->id;
        }

        if ($request->has('task_type')) {
            $data['task_type'] = $request->task_type;
        }

        if ($request->has('due_date')) {
            $data['due_date'] = Carbon::parse($request->due_date);
        }

        if ($request->has('description')) {
            $data['description'] = $request->description;
        }

        if ($request->has('status')) {
            $data['status'] = $request->status;
        }

        if (array_key_exists('assigned_to', $requestData)) {
            $data['assigned_to'] = $requestData['assigned_to'];
        }

        if (array_key_exists('laborer_group_id', $requestData)) {
            $data['laborer_group_id'] = $requestData['laborer_group_id'];
        }

        if (!empty($data)) {
            $task->fill($data);
            $task->save();
        }

        $task->load(['planting.field', 'laborer', 'laborerGroup']);

        return response()->json([
            'message' => 'Task updated successfully',
            'task' => $task,
        ]);
    }

    /**
     * Remove the specified task
     */
    public function destroy(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        if (!$this->ownsTask($user->id, $task)) {
            return response()->json([
                'message' => 'Unauthorized access',
            ], 403);
        }

        $task->delete();

        return response()->json([
            'message' => 'Task deleted successfully',
        ]);
    }

    /**
     * Mark a task as completed
     */
    public function markCompleted(Request $request, Task $task): JsonResponse
    {
        $user = $request->user();

        if (!$this->ownsTask($user->id, $task)) {
            return response()->json([
                'message' => 'Unauthorized access',
            ], 403);
        }

        $task->update([
            'status' => Task::STATUS_COMPLETED,
        ]);

        $task->load(['planting.field', 'laborer']);

        return response()->json([
            'message' => 'Task marked as completed',
            'task' => $task,
        ]);
    }

    /**
     * @return array<string>
     */
    private function taskTypeOptions(): array
    {
        return [
            Task::TYPE_WATERING,
            Task::TYPE_FERTILIZING,
            Task::TYPE_WEEDING,
            Task::TYPE_PEST_CONTROL,
            Task::TYPE_HARVESTING,
            Task::TYPE_MAINTENANCE,
        ];
    }

    /**
     * @return array<string>
     */
    private function statusOptions(): array
    {
        return [
            Task::STATUS_PENDING,
            Task::STATUS_IN_PROGRESS,
            Task::STATUS_COMPLETED,
            Task::STATUS_CANCELLED,
        ];
    }

    private function ownsPlanting(int $userId, Planting $planting): bool
    {
        $field = $planting->field;

        return $field && (int) $field->user_id === $userId;
    }

    private function ownsTask(int $userId, Task $task): bool
    {
        $task->loadMissing('planting.field');

        if (!$task->planting) {
            return false;
        }

        return $this->ownsPlanting($userId, $task->planting);
    }
}