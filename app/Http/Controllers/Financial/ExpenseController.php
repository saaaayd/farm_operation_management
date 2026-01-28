<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ExpenseController extends Controller
{
    /**
     * Display a listing of expenses
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Expense::where('user_id', $user->id);

        // Apply filters
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        $expenses = $query->orderBy('date', 'desc')->get();

        return response()->json([
            'expenses' => $expenses
        ]);
    }

    /**
     * Store a newly created expense
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'category' => 'required|string|in:seeds,fertilizer,fertilizers,pesticide,pesticides,labor,equipment,utilities,maintenance,inventory_purchase,transportation,other',
            'date' => 'required|date',
            'payment_method' => 'nullable|string|in:cash,bank_transfer,check,credit_card',
            'receipt_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'related_entity_type' => 'nullable|string|in:field,planting,harvest,task,laborer,labor_wage,inventory_item',
            'related_entity_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $expense = Expense::create([
            'description' => $request->description,
            'amount' => $request->amount,
            'category' => $request->category,
            'date' => $request->date,
            'payment_method' => $request->payment_method,
            'receipt_number' => $request->receipt_number,
            'notes' => $request->notes,
            'related_entity_type' => $request->related_entity_type,
            'related_entity_id' => $request->related_entity_id,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Expense created successfully',
            'expense' => $expense
        ], 201);
    }

    /**
     * Display the specified expense
     */
    public function show(Request $request, Expense $expense): JsonResponse
    {
        $user = $request->user();

        if ($expense->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        return response()->json([
            'expense' => $expense
        ]);
    }

    /**
     * Update the specified expense
     */
    public function update(Request $request, Expense $expense): JsonResponse
    {
        $user = $request->user();

        if ($expense->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'sometimes|required|string|max:255',
            'amount' => 'sometimes|required|numeric|min:0',
            'category' => 'sometimes|required|string|in:seeds,fertilizer,fertilizers,pesticide,pesticides,labor,equipment,utilities,maintenance,inventory_purchase,transportation,other',
            'date' => 'sometimes|required|date',
            'payment_method' => 'nullable|string|in:cash,bank_transfer,check,credit_card',
            'receipt_number' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'related_entity_type' => 'nullable|string|in:field,planting,harvest,task,laborer,labor_wage,inventory_item',
            'related_entity_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $expense->update($request->only([
            'description',
            'amount',
            'category',
            'date',
            'payment_method',
            'receipt_number',
            'notes',
            'related_entity_type',
            'related_entity_id'
        ]));

        return response()->json([
            'message' => 'Expense updated successfully',
            'expense' => $expense
        ]);
    }

    /**
     * Remove the specified expense
     */
    public function destroy(Request $request, Expense $expense): JsonResponse
    {
        $user = $request->user();

        if ($expense->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $expense->delete();

        return response()->json([
            'message' => 'Expense deleted successfully'
        ]);
    }

    /**
     * Get expense summary by category
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();

        $query = Expense::where('user_id', $user->id);

        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }

        $summary = $query->selectRaw('category, SUM(amount) as total_amount, COUNT(*) as count')
            ->groupBy('category')
            ->get();

        $total = $summary->sum('total_amount');

        return response()->json([
            'summary' => $summary,
            'total_amount' => $total
        ]);
    }
}