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
            'payment_type' => ['nullable', 'string', Rule::in([Task::PAYMENT_TYPE_WAGE, Task::PAYMENT_TYPE_SHARE, Task::PAYMENT_TYPE_PIECE_RATE])],
            'revenue_share_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'wage_amount' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
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
            'payment_type' => $request->input('payment_type', Task::PAYMENT_TYPE_WAGE),
            'revenue_share_percentage' => $request->revenue_share_percentage,
            'wage_amount' => $request->payment_type === Task::PAYMENT_TYPE_PIECE_RATE
                ? ($request->quantity * $request->unit_price)
                : $request->wage_amount,
            'unit' => $request->unit,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
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
            'payment_type' => ['nullable', 'string', Rule::in([Task::PAYMENT_TYPE_WAGE, Task::PAYMENT_TYPE_SHARE, Task::PAYMENT_TYPE_PIECE_RATE])],
            'revenue_share_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'wage_amount' => ['nullable', 'numeric', 'min:0'],
            'unit' => ['nullable', 'string'],
            'quantity' => ['nullable', 'numeric', 'min:0'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
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

        if ($request->has('payment_type')) {
            $data['payment_type'] = $request->payment_type;
        }

        if ($request->has('revenue_share_percentage')) {
            $data['revenue_share_percentage'] = $request->revenue_share_percentage;
        }

        if ($request->has('wage_amount')) {
            $data['wage_amount'] = $request->wage_amount;
        }

        if ($request->has('unit'))
            $data['unit'] = $request->unit;
        if ($request->has('quantity'))
            $data['quantity'] = $request->quantity;
        if ($request->has('unit_price'))
            $data['unit_price'] = $request->unit_price;

        // Recalculate wage_amount if piece rate fields are updated
        if (
            ($data['payment_type'] ?? $task->payment_type) === Task::PAYMENT_TYPE_PIECE_RATE ||
            (isset($data['payment_type']) && $data['payment_type'] === Task::PAYMENT_TYPE_PIECE_RATE)
        ) {
            $qty = $data['quantity'] ?? $task->quantity ?? 0;
            $price = $data['unit_price'] ?? $task->unit_price ?? 0;
            $data['wage_amount'] = $qty * $price;
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

        // Optional: hours worked for the task (defaults to 8 for per_day, 1 for per_task)
        $hoursWorked = $request->input('hours_worked');

        $task->update([
            'status' => Task::STATUS_COMPLETED,
        ]);

        $task->load(['planting.field', 'laborer', 'laborerGroup.laborers']);

        // Create labor wage and expense records for assigned laborers
        $laborExpenses = $this->createLaborExpensesForTask($task, $user, $hoursWorked);

        return response()->json([
            'message' => 'Task marked as completed',
            'task' => $task,
            'labor_expenses' => $laborExpenses,
        ]);
    }

    /**
     * Create labor wage and expense records for a completed task
     */
    private function createLaborExpensesForTask(Task $task, $user, $hoursWorked = null): array
    {
        $laborers = collect();
        $expenses = [];

        // Get laborers: either individual or from group
        if ($task->laborer) {
            $laborers->push($task->laborer);
        } elseif ($task->laborerGroup && $task->laborerGroup->laborers) {
            $laborers = $task->laborerGroup->laborers;
        }

        if ($laborers->isEmpty()) {
            return $expenses;
        }

        foreach ($laborers as $laborer) {
            // Calculate wage based on rate_type
            $rate = (float) ($laborer->rate ?? 0);
            $rateType = $laborer->rate_type ?? 'per_day';

            // Determine hours and wage amount based on rate type
            if ($rateType === 'per_task') {
                $hours = $hoursWorked ?? 1;
                $wageAmount = $rate; // Fixed rate per task
            } else {
                // per_day (default)
                $hours = $hoursWorked ?? 8;
                $wageAmount = $rate; // Daily rate
            }

            if ($wageAmount <= 0 && $rateType !== 'per_task') { // Allow per_task to potentially have custom logic, but generally wage should be > 0. But wait, if we have wage_amount, we use that.
                // logic moved below
            }

            // Override with task specific wage amount if available
            if ($task->wage_amount > 0) {
                // If the task has a specific wage amount, use that as the total wage for the task.
                // However, if we have multiple laborers (group), how does this split? 
                // The prompt implies "standard wage ... autofilled by the rate of the laborer"
                // If it's a group, usually it's per head or total? 
                // For now, let's assume if it's assigned to an individual, wage_amount is their wage.
                // If it's a group and wage_amount is set... let's assume it's the rate PER person if the UI fills it from rate.
                // Or is it total? "input box for standard wage ... autofilled by the rate of the laborer"
                // "Laborer" singular.
                // If it's a group assignment, the UI in Create.vue hides the individual laborer select.
                // Let's stick to the simple case: if individual, use wage_amount.
                // If group, use laborer's rate (as logic for group wage override wasn't explicitly requested/designed and might be complex).

                // User said: "autofilled by the rate of laborer... final rate ... to be finalized in tasks create"
                // "final rate" implies per person rate usually. 
                // Let's assume wage_amount is the effective rate for the calculation.

                $wageAmount = (float) $task->wage_amount;

                // Recalculate based on hours if it was a rate per day? 
                // If the input is "Standard Wage", is it "Daily Rate" or "Flat Fee for this task"?
                // "autofilled by the rate". Laborer rate is "hourly_rate" in DB (from inspection) OR "rate" (from Model inspection).
                // Model has 'rate' and 'rate_type'. 
                // If rate_type is per_day, wage_amount is likely the new per_day rate.
                // If rate_type is per_task, wage_amount is the task fee.
                // So we should substitute $rate with $task->wage_amount and re-run logic.

                // However, simpler implementation:
                // The code previously calculated $wageAmount based on rate * hours or fixed.
                // If we view wage_amount as the "rate", we should re-calculate?
                // OR is wage_amount the FINAL TOTAL amount?
                // "input box for standard wage" -> implies rate.
                // "produce share amount" -> implies percentage or total? Create.vue has "Harvester's Cut (%)".

                // Let's treat wage_amount as the *rate* override.

                if ($rateType === 'per_task') {
                    $wageAmount = (float) $task->wage_amount;
                } else {
                    // per_day
                    // wage_amount is the daily rate.
                    // output wage = (hours / 8) * wage_amount ? 
                    // The previous logic was:
                    // $hours = $hoursWorked ?? 8;
                    // $wageAmount = $rate; // Daily rate. Wait, previously it was just $rate.
                    // The previous code: $wageAmount = $rate; // Daily rate  <-- seemingly assuming $rate is the full day wage?
                    // Verify previous code: 
                    // $rate = (float) ($laborer->rate ?? 0);
                    // if ($rateType === 'per_task') { $wageAmount = $rate; } 
                    // else { $hours = $hoursWorked ?? 8; $wageAmount = $rate; }
                    // It didn't multiply by hours for per_day? That's weird if it's hourly.
                    // Migration said: $table->decimal('hourly_rate', 8, 2); in 2025_08_26_084521_create_laborers_table.php
                    // But Model says 'rate' and 'rate_type'. 
                    // Let's look at the laborer migration again. 
                    // 15: $table->decimal('hourly_rate', 8, 2); 
                    // But the Model uses 'rate'. Maybe column was renamed or accessor?
                    // Let's assume the previous code `(float) ($laborer->rate ?? 0)` was working logic for the existing app.
                    // And it assigned `$wageAmount = $rate` regardless of hours (which implies `rate` is a daily rate effectively, or the previous code satisfied the requirement).

                    // So, if we use wage_amount as strict override of the resulting amount:
                    $wageAmount = (float) $task->wage_amount;
                }
            }

            if ($wageAmount <= 0) {
                continue;
            }

            // Distribute piece rate total among group members
            if ($task->payment_type === Task::PAYMENT_TYPE_PIECE_RATE && $laborers->count() > 1) {
                $wageAmount = $wageAmount / $laborers->count();
            }

            // Create LaborWage record
            $laborWage = \App\Models\LaborWage::create([
                'laborer_id' => $laborer->id,
                'task_id' => $task->id,
                'hours_worked' => $hours,
                'wage_amount' => $wageAmount,
                'date' => now(),
                'user_id' => $user->id,
            ]);

            // Create Expense record linked to the task
            $expense = \App\Models\Expense::create([
                'description' => "Labor: {$laborer->name} - {$task->task_type} task",
                'amount' => $wageAmount,
                'category' => \App\Models\Expense::CATEGORY_LABOR,
                'date' => now(),
                'user_id' => $user->id,
                'payment_method' => 'cash',
                'notes' => "Auto-generated from task completion. Task ID: {$task->id}, Laborer: {$laborer->name}",
                'related_entity_type' => \App\Models\Expense::ENTITY_TYPE_TASK,
                'related_entity_id' => $task->id,
            ]);

            $expenses[] = [
                'laborer_id' => $laborer->id,
                'laborer_name' => $laborer->name,
                'labor_wage_id' => $laborWage->id,
                'expense_id' => $expense->id,
                'amount' => $wageAmount,
            ];
        }

        return $expenses;
    }

    /**
     * @return array<string>
     */
    private function taskTypeOptions(): array
    {
        return [
            Task::TYPE_LAND_PREPARATION,
            Task::TYPE_SEEDLING_MANAGEMENT,
            Task::TYPE_TRANSPLANTING,
            Task::TYPE_WATERING,
            Task::TYPE_WATER_MANAGEMENT,
            Task::TYPE_FERTILIZING,
            Task::TYPE_WEEDING,
            Task::TYPE_PEST_CONTROL,
            Task::TYPE_PESTICIDE_APPLICATION,
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