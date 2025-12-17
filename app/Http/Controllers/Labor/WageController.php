<?php

namespace App\Http\Controllers\Labor;

use App\Http\Controllers\Controller;
use App\Models\LaborWage;
use App\Models\Laborer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class WageController extends Controller
{
    /**
     * Display a listing of wages
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = LaborWage::where('user_id', $user->id);
        
        // Apply filters
        if ($request->has('laborer_id')) {
            $query->where('laborer_id', $request->laborer_id);
        }
        
        if ($request->has('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        $wages = $query->with(['laborer'])
            ->orderBy('payment_date', 'desc')
            ->get();
        
        return response()->json([
            'wages' => $wages
        ]);
    }

    /**
     * Store a newly created wage payment
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'laborer_id' => 'required|exists:laborers,id',
            'amount' => 'required|numeric|min:0',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,bank_transfer,check',
            'hours_worked' => 'required|numeric|min:0',
            'hourly_rate' => 'required|numeric|min:0',
            'status' => 'required|string|in:pending,paid,overdue',
            'notes' => 'nullable|string',
            'related_task_id' => 'nullable|exists:tasks,id',
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
        
        if ($laborer->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access to laborer'
            ], 403);
        }

        $wage = LaborWage::create([
            'laborer_id' => $request->laborer_id,
            'amount' => $request->amount,
            'payment_date' => $request->payment_date,
            'payment_method' => $request->payment_method,
            'hours_worked' => $request->hours_worked,
            'hourly_rate' => $request->hourly_rate,
            'status' => $request->status,
            'notes' => $request->notes,
            'related_task_id' => $request->related_task_id,
            'user_id' => $user->id,
        ]);

        return response()->json([
            'message' => 'Wage payment created successfully',
            'wage' => $wage->load(['laborer'])
        ], 201);
    }

    /**
     * Display the specified wage
     */
    public function show(Request $request, LaborWage $wage): JsonResponse
    {
        $user = $request->user();
        
        if ($wage->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $wage->load(['laborer', 'relatedTask']);

        return response()->json([
            'wage' => $wage
        ]);
    }

    /**
     * Update the specified wage
     */
    public function update(Request $request, LaborWage $wage): JsonResponse
    {
        $user = $request->user();
        
        if ($wage->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|required|numeric|min:0',
            'payment_date' => 'sometimes|required|date',
            'payment_method' => 'sometimes|required|string|in:cash,bank_transfer,check',
            'hours_worked' => 'sometimes|required|numeric|min:0',
            'hourly_rate' => 'sometimes|required|numeric|min:0',
            'status' => 'sometimes|required|string|in:pending,paid,overdue',
            'notes' => 'nullable|string',
            'related_task_id' => 'nullable|exists:tasks,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $wage->update($request->only([
            'amount', 'payment_date', 'payment_method', 'hours_worked',
            'hourly_rate', 'status', 'notes', 'related_task_id'
        ]));

        return response()->json([
            'message' => 'Wage payment updated successfully',
            'wage' => $wage->load(['laborer', 'relatedTask'])
        ]);
    }

    /**
     * Remove the specified wage
     */
    public function destroy(Request $request, LaborWage $wage): JsonResponse
    {
        $user = $request->user();
        
        if ($wage->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $wage->delete();

        return response()->json([
            'message' => 'Wage payment deleted successfully'
        ]);
    }

    /**
     * Update wage status
     */
    public function updateStatus(Request $request, LaborWage $wage): JsonResponse
    {
        $user = $request->user();
        
        if ($wage->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,paid,overdue',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $wage->update(['status' => $request->status]);

        return response()->json([
            'message' => 'Wage status updated successfully',
            'wage' => $wage->load(['laborer', 'relatedTask'])
        ]);
    }

    /**
     * Get wage summary for a laborer
     */
    public function laborerSummary(Request $request, Laborer $laborer): JsonResponse
    {
        $user = $request->user();
        
        if ($laborer->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $query = $laborer->wages();
        
        if ($request->has('date_from')) {
            $query->where('payment_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('payment_date', '<=', $request->date_to);
        }

        $totalWages = $query->sum('amount');
        $totalHours = $query->sum('hours_worked');
        $paidWages = $query->where('status', 'paid')->sum('amount');
        $pendingWages = $query->where('status', 'pending')->sum('amount');

        return response()->json([
            'laborer' => $laborer,
            'summary' => [
                'total_wages' => $totalWages,
                'total_hours' => $totalHours,
                'paid_wages' => $paidWages,
                'pending_wages' => $pendingWages,
                'average_hourly_rate' => $totalHours > 0 ? round($totalWages / $totalHours, 2) : 0,
            ]
        ]);
    }
}