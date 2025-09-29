<?php

namespace App\Services;

use App\Models\Laborer;
use App\Models\LaborWage;
use App\Models\Task;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LaborService
{
    /**
     * Get all laborers for a farm
     */
    public function getLaborers($farmId)
    {
        return Laborer::where('farm_id', $farmId)
            ->orderBy('name')
            ->get();
    }

    /**
     * Create a new laborer
     */
    public function createLaborer($laborerData)
    {
        try {
            return Laborer::create([
                'farm_id' => $laborerData['farm_id'],
                'name' => $laborerData['name'],
                'phone' => $laborerData['phone'] ?? null,
                'address' => $laborerData['address'] ?? null,
                'skills' => $laborerData['skills'] ?? [],
                'hourly_rate' => $laborerData['hourly_rate'] ?? 0,
                'daily_rate' => $laborerData['daily_rate'] ?? 0,
                'is_active' => $laborerData['is_active'] ?? true,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to create laborer: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update laborer information
     */
    public function updateLaborer($laborerId, $laborerData)
    {
        try {
            $laborer = Laborer::findOrFail($laborerId);
            $laborer->update($laborerData);
            return $laborer;
        } catch (\Exception $e) {
            Log::error('Failed to update laborer: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete a laborer
     */
    public function deleteLaborer($laborerId)
    {
        try {
            $laborer = Laborer::findOrFail($laborerId);
            
            // Check if laborer has assigned tasks
            $hasTasks = Task::where('assigned_to', $laborerId)->exists();
            if ($hasTasks) {
                throw new \Exception('Cannot delete laborer with assigned tasks');
            }

            $laborer->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete laborer: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Record labor wage payment
     */
    public function recordWagePayment($wageData)
    {
        DB::beginTransaction();
        
        try {
            $wage = LaborWage::create([
                'laborer_id' => $wageData['laborer_id'],
                'task_id' => $wageData['task_id'] ?? null,
                'hours_worked' => $wageData['hours_worked'] ?? 0,
                'daily_rate' => $wageData['daily_rate'] ?? 0,
                'hourly_rate' => $wageData['hourly_rate'] ?? 0,
                'total_amount' => $wageData['total_amount'],
                'payment_date' => $wageData['payment_date'] ?? now(),
                'payment_method' => $wageData['payment_method'] ?? 'cash',
                'notes' => $wageData['notes'] ?? '',
            ]);

            DB::commit();
            return $wage;
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to record wage payment: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get wage payments for a laborer
     */
    public function getLaborerWages($laborerId, $period = '30')
    {
        $startDate = now()->subDays($period);
        
        return LaborWage::where('laborer_id', $laborerId)
            ->where('payment_date', '>=', $startDate)
            ->with(['laborer', 'task'])
            ->orderBy('payment_date', 'desc')
            ->get();
    }

    /**
     * Get labor cost summary
     */
    public function getLaborCostSummary($farmId, $period = '30')
    {
        $startDate = now()->subDays($period);
        
        $wages = LaborWage::whereHas('laborer', function ($q) use ($farmId) {
            $q->where('farm_id', $farmId);
        })
        ->where('payment_date', '>=', $startDate)
        ->get();

        $totalCost = $wages->sum('total_amount');
        $totalHours = $wages->sum('hours_worked');
        $totalDays = $wages->where('daily_rate', '>', 0)->count();
        $laborerCount = $wages->pluck('laborer_id')->unique()->count();

        return [
            'total_cost' => $totalCost,
            'total_hours' => $totalHours,
            'total_days' => $totalDays,
            'laborer_count' => $laborerCount,
            'average_hourly_rate' => $totalHours > 0 ? $totalCost / $totalHours : 0,
            'average_daily_rate' => $totalDays > 0 ? $wages->where('daily_rate', '>', 0)->avg('daily_rate') : 0,
            'wages' => $wages,
        ];
    }

    /**
     * Assign laborer to task
     */
    public function assignLaborerToTask($taskId, $laborerId)
    {
        try {
            $task = Task::findOrFail($taskId);
            $laborer = Laborer::findOrFail($laborerId);

            // Check if laborer is available
            if (!$laborer->is_active) {
                throw new \Exception('Laborer is not active');
            }

            $task->update([
                'assigned_to' => $laborerId,
                'status' => 'in_progress',
            ]);

            return $task;
        } catch (\Exception $e) {
            Log::error('Failed to assign laborer to task: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get available laborers for task assignment
     */
    public function getAvailableLaborers($farmId, $taskDate = null)
    {
        $query = Laborer::where('farm_id', $farmId)
            ->where('is_active', true);

        if ($taskDate) {
            // Check for conflicting tasks
            $busyLaborers = Task::where('due_date', $taskDate)
                ->where('status', 'in_progress')
                ->pluck('assigned_to')
                ->filter();

            if ($busyLaborers->isNotEmpty()) {
                $query->whereNotIn('id', $busyLaborers);
            }
        }

        return $query->get();
    }

    /**
     * Get labor productivity metrics
     */
    public function getLaborProductivity($farmId, $period = '30')
    {
        $startDate = now()->subDays($period);
        
        $tasks = Task::whereHas('laborer', function ($q) use ($farmId) {
            $q->where('farm_id', $farmId);
        })
        ->where('created_at', '>=', $startDate)
        ->with(['laborer'])
        ->get();

        $completedTasks = $tasks->where('status', 'completed');
        $totalTasks = $tasks->count();
        $completionRate = $totalTasks > 0 ? ($completedTasks->count() / $totalTasks) * 100 : 0;

        $laborerStats = $tasks->groupBy('assigned_to')->map(function ($laborerTasks) {
            return [
                'laborer_id' => $laborerTasks->first()->assigned_to,
                'laborer_name' => $laborerTasks->first()->laborer->name ?? 'Unknown',
                'total_tasks' => $laborerTasks->count(),
                'completed_tasks' => $laborerTasks->where('status', 'completed')->count(),
                'completion_rate' => $laborerTasks->count() > 0 ? 
                    ($laborerTasks->where('status', 'completed')->count() / $laborerTasks->count()) * 100 : 0,
            ];
        });

        return [
            'total_tasks' => $totalTasks,
            'completed_tasks' => $completedTasks->count(),
            'completion_rate' => $completionRate,
            'laborer_stats' => $laborerStats,
        ];
    }

    /**
     * Calculate labor cost for a specific period
     */
    public function calculateLaborCost($farmId, $startDate, $endDate)
    {
        $wages = LaborWage::whereHas('laborer', function ($q) use ($farmId) {
            $q->where('farm_id', $farmId);
        })
        ->whereBetween('payment_date', [$startDate, $endDate])
        ->get();

        return [
            'total_cost' => $wages->sum('total_amount'),
            'total_hours' => $wages->sum('hours_worked'),
            'wage_count' => $wages->count(),
            'laborers_paid' => $wages->pluck('laborer_id')->unique()->count(),
            'breakdown' => $wages->groupBy('laborer_id')->map(function ($laborerWages) {
                return [
                    'laborer_name' => $laborerWages->first()->laborer->name ?? 'Unknown',
                    'total_amount' => $laborerWages->sum('total_amount'),
                    'total_hours' => $laborerWages->sum('hours_worked'),
                    'payment_count' => $laborerWages->count(),
                ];
            }),
        ];
    }
}
