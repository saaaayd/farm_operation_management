<?php

namespace App\Http\Controllers\MarketPlace;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Harvest;
use App\Models\Buyer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class SaleController extends Controller
{
    /**
     * Display a listing of sales
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Sale::where('user_id', $user->id);
        
        // Apply filters
        if ($request->has('buyer_id')) {
            $query->where('buyer_id', $request->buyer_id);
        }
        
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->has('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }
        
        $sales = $query->with(['harvest.planting.field', 'harvest.planting.crop', 'buyer'])
            ->orderBy('sale_date', 'desc')
            ->get();
        
        return response()->json([
            'sales' => $sales
        ]);
    }

    /**
     * Store a newly created sale
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'harvest_id' => 'required|exists:harvests,id',
            'buyer_id' => 'required|exists:buyers,id',
            'quantity' => 'required|numeric|min:0',
            'unit_price' => 'required|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'sale_date' => 'required|date',
            'payment_method' => 'required|string|in:cash,bank_transfer,check,credit',
            'payment_status' => 'required|string|in:pending,paid,partial,overdue',
            'delivery_date' => 'nullable|date|after_or_equal:sale_date',
            'delivery_address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        // Check if user owns the harvest
        $harvest = Harvest::with('planting.field')->findOrFail($request->harvest_id);
        $user = $request->user();
        
        if ($harvest->planting->field->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access to harvest'
            ], 403);
        }

        // Check if user owns the buyer
        $buyer = Buyer::findOrFail($request->buyer_id);
        if ($buyer->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access to buyer'
            ], 403);
        }

        // Check if harvest is available for sale
        if ($harvest->sales()->exists()) {
            return response()->json([
                'message' => 'This harvest has already been sold'
            ], 422);
        }

        DB::beginTransaction();

        try {
            $sale = Sale::create([
                'harvest_id' => $request->harvest_id,
                'buyer_id' => $request->buyer_id,
                'quantity' => $request->quantity,
                'unit_price' => $request->unit_price,
                'total_amount' => $request->total_amount,
                'sale_date' => $request->sale_date,
                'payment_method' => $request->payment_method,
                'payment_status' => $request->payment_status,
                'delivery_date' => $request->delivery_date,
                'delivery_address' => $request->delivery_address,
                'notes' => $request->notes,
                'user_id' => $user->id,
            ]);

            DB::commit();

            return response()->json([
                'message' => 'Sale created successfully',
                'sale' => $sale->load(['harvest.planting.field', 'harvest.planting.crop', 'buyer'])
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'message' => 'Failed to create sale',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified sale
     */
    public function show(Request $request, Sale $sale): JsonResponse
    {
        $user = $request->user();
        
        if ($sale->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $sale->load(['harvest.planting.field', 'harvest.planting.crop', 'buyer']);

        return response()->json([
            'sale' => $sale
        ]);
    }

    /**
     * Update the specified sale
     */
    public function update(Request $request, Sale $sale): JsonResponse
    {
        $user = $request->user();
        
        if ($sale->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'quantity' => 'sometimes|required|numeric|min:0',
            'unit_price' => 'sometimes|required|numeric|min:0',
            'total_amount' => 'sometimes|required|numeric|min:0',
            'sale_date' => 'sometimes|required|date',
            'payment_method' => 'sometimes|required|string|in:cash,bank_transfer,check,credit',
            'payment_status' => 'sometimes|required|string|in:pending,paid,partial,overdue',
            'delivery_date' => 'nullable|date|after_or_equal:sale_date',
            'delivery_address' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $sale->update($request->only([
            'quantity', 'unit_price', 'total_amount', 'sale_date',
            'payment_method', 'payment_status', 'delivery_date',
            'delivery_address', 'notes'
        ]));

        return response()->json([
            'message' => 'Sale updated successfully',
            'sale' => $sale->load(['harvest.planting.field', 'harvest.planting.crop', 'buyer'])
        ]);
    }

    /**
     * Remove the specified sale
     */
    public function destroy(Request $request, Sale $sale): JsonResponse
    {
        $user = $request->user();
        
        if ($sale->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $sale->delete();

        return response()->json([
            'message' => 'Sale deleted successfully'
        ]);
    }

    /**
     * Update payment status
     */
    public function updatePaymentStatus(Request $request, Sale $sale): JsonResponse
    {
        $user = $request->user();
        
        if ($sale->user_id !== $user->id) {
            return response()->json([
                'message' => 'Unauthorized access'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'payment_status' => 'required|string|in:pending,paid,partial,overdue',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $sale->update(['payment_status' => $request->payment_status]);

        return response()->json([
            'message' => 'Payment status updated successfully',
            'sale' => $sale->load(['harvest.planting.field', 'harvest.planting.crop', 'buyer'])
        ]);
    }

    /**
     * Get sales summary
     */
    public function summary(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = Sale::where('user_id', $user->id);
        
        if ($request->has('date_from')) {
            $query->where('sale_date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('sale_date', '<=', $request->date_to);
        }

        $totalSales = $query->sum('total_amount');
        $totalQuantity = $query->sum('quantity');
        $salesCount = $query->count();
        
        $paymentStatusSummary = $query->selectRaw('payment_status, COUNT(*) as count, SUM(total_amount) as amount')
            ->groupBy('payment_status')
            ->get();

        return response()->json([
            'summary' => [
                'total_sales' => $totalSales,
                'total_quantity' => $totalQuantity,
                'sales_count' => $salesCount,
                'average_sale_amount' => $salesCount > 0 ? round($totalSales / $salesCount, 2) : 0,
            ],
            'payment_status_summary' => $paymentStatusSummary
        ]);
    }
}