<?php

namespace App\Http\Controllers\MarketPlace;

use App\Http\Controllers\Controller;
use App\Models\Buyer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class BuyerController extends Controller
{
    /**
     * Display a listing of buyers
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Buyer::where('user_id', $user->id);
        
        // Apply filters
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }
        
        $buyers = $query->orderBy('name')->get();
        
        return response()->json([
            'buyers' => $buyers
        ]);
    }

    /**
     * Store a newly created buyer
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'type' => 'required|string|in:individual,business,wholesaler,retailer',
            'status' => 'required|string|in:active,inactive',
            'payment_terms' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $buyer = Buyer::create([
            'name' => $request->name,
            'contact_person' => $request->contact_person,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'type' => $request->type,
            'status' => $request->status,
            'payment_terms' => $request->payment_terms,
            'credit_limit' => $request->credit_limit,
            'notes' => $request->notes,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'message' => 'Buyer created successfully',
            'buyer' => $buyer
        ], 201);
    }

    /**
     * Display the specified buyer
     */
    public function show(Request $request, Buyer $buyer): JsonResponse
    {
        $user = $request->user();
        
        if ($buyer->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $buyer->load(['sales']);

        return response()->json([
            'buyer' => $buyer
        ]);
    }

    /**
     * Update the specified buyer
     */
    public function update(Request $request, Buyer $buyer): JsonResponse
    {
        $user = $request->user();
        
        if ($buyer->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'sometimes|required|string|max:20',
            'address' => 'nullable|string',
            'type' => 'sometimes|required|string|in:individual,business,wholesaler,retailer',
            'status' => 'sometimes|required|string|in:active,inactive',
            'payment_terms' => 'nullable|string|max:255',
            'credit_limit' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $buyer->update($request->only([
            'name', 'contact_person', 'email', 'phone', 'address',
            'type', 'status', 'payment_terms', 'credit_limit', 'notes'
        ]));

        return response()->json([
            'message' => 'Buyer updated successfully',
            'buyer' => $buyer
        ]);
    }

    /**
     * Remove the specified buyer
     */
    public function destroy(Request $request, Buyer $buyer): JsonResponse
    {
        $user = $request->user();
        
        if ($buyer->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $buyer->delete();

        return response()->json([
            'message' => 'Buyer deleted successfully'
        ]);
    }

    /**
     * Get buyer sales history
     */
    public function salesHistory(Request $request, Buyer $buyer): JsonResponse
    {
        $user = $request->user();
        
        if ($buyer->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $query = $buyer->sales();
        
        if ($request->has('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }

        $sales = $query->orderBy('sale_date', 'desc')->get();
        $totalSales = $sales->sum('total_amount');

        return response()->json([
            'buyer' => $buyer,
            'sales_history' => $sales,
            'total_sales' => $totalSales,
            'sales_count' => $sales->count(),
        ]);
    }
}