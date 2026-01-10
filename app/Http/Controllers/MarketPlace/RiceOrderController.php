<?php

namespace App\Http\Controllers\MarketPlace;

use App\Http\Controllers\Controller;
use App\Models\RiceOrder;
use App\Models\RiceProduct;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class RiceOrderController extends Controller
{
    /**
     * Get buyer's orders
     */
    public function buyerOrders(Request $request): JsonResponse
    {
        $query = RiceOrder::where('buyer_id', Auth::id())
            ->with(['riceProduct.farmer', 'riceProduct.riceVariety']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->where('order_date', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->where('order_date', '<=', $request->to_date);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['orders' => $orders]);
    }

    /**
     * Get farmer's incoming orders
     */
    public function farmerOrders(Request $request): JsonResponse
    {
        $query = RiceOrder::forFarmer(Auth::id())
            ->with(['buyer', 'riceProduct']);

        // Filter by status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->has('from_date') && $request->from_date) {
            $query->where('order_date', '>=', $request->from_date);
        }

        if ($request->has('to_date') && $request->to_date) {
            $query->where('order_date', '<=', $request->to_date);
        }

        $orders = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['orders' => $orders]);
    }

    /**
     * Get single order details
     */
    public function show(RiceOrder $order): JsonResponse
    {
        // Check authorization - must be buyer or farmer
        $user = Auth::user();
        $isBuyer = $order->buyer_id === $user->id;
        $isFarmer = $order->riceProduct && $order->riceProduct->farmer_id === $user->id;

        if (!$isBuyer && !$isFarmer) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $order->load(['buyer', 'riceProduct.farmer', 'riceProduct.riceVariety', 'messages']);

        return response()->json(['order' => $order]);
    }

    /**
     * Buyer places a new order
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rice_product_id' => 'required|exists:rice_products,id',
            'quantity' => 'required|numeric|min:1',
            'delivery_address' => 'required|array',
            'delivery_method' => 'required|string|in:pickup,courier,postal,truck',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $product = RiceProduct::findOrFail($request->rice_product_id);

        // Use transaction to ensure atomicity
        return \Illuminate\Support\Facades\DB::transaction(function () use ($request, $product) {
            // Check availability and Reserve quantity (DB-level lock)
            try {
                $product->reserveQuantity($request->quantity);
            } catch (\Exception $e) {
                return response()->json([
                    'message' => 'Insufficient product quantity available'
                ], 422);
            }

            // Calculate total
            $unitPrice = $product->price_per_unit;
            $totalAmount = $unitPrice * $request->quantity;

            $order = RiceOrder::create([
                'buyer_id' => Auth::id(),
                'rice_product_id' => $request->rice_product_id,
                'quantity' => $request->quantity,
                'unit_price' => $unitPrice,
                'total_amount' => $totalAmount,
                'status' => RiceOrder::STATUS_PENDING,
                'payment_status' => RiceOrder::PAYMENT_PENDING,
                'delivery_address' => $request->delivery_address,
                'delivery_method' => $request->delivery_method,
                'payment_method' => $request->payment_method,
                'buyer_notes' => $request->notes,
                'order_date' => now(),
                'is_pre_order' => $product->production_status === 'in_production',
            ]);

            $order->load(['riceProduct.farmer']);

            // Notify farmer of new order
            \App\Models\Notification::notify(
                $product->farmer_id,
                \App\Models\Notification::TYPE_ORDER_PLACED,
                'New Order Received',
                "You have a new order for {$request->quantity} kg of {$product->name}",
                ['order_id' => $order->id],
                "/farmer/orders/{$order->id}"
            );

            return response()->json([
                'message' => 'Order placed successfully',
                'order' => $order
            ], 201);
        });
    }


    /**
     * Farmer accepts an order
     */
    public function accept(Request $request, RiceOrder $order): JsonResponse
    {
        // Authorization check
        if ($order->riceProduct->farmer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$order->canBeConfirmed()) {
            return response()->json([
                'message' => 'Order cannot be accepted in current state'
            ], 422);
        }

        $expectedDelivery = $request->input('expected_delivery_date');
        $farmerNotes = $request->input('farmer_notes');

        $order->confirm($expectedDelivery, $farmerNotes);

        return response()->json([
            'message' => 'Order accepted',
            'order' => $order->fresh()
        ]);
    }

    /**
     * Farmer rejects an order
     */
    public function reject(Request $request, RiceOrder $order): JsonResponse
    {
        // Authorization check
        if ($order->riceProduct->farmer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->status !== RiceOrder::STATUS_PENDING) {
            return response()->json([
                'message' => 'Only pending orders can be rejected'
            ], 422);
        }

        $reason = $request->input('reason', 'Rejected by farmer');
        $order->cancel($reason);

        return response()->json([
            'message' => 'Order rejected',
            'order' => $order->fresh()
        ]);
    }

    /**
     * Cancel an order (by buyer or farmer)
     */
    public function cancel(Request $request, RiceOrder $order): JsonResponse
    {
        $user = Auth::user();
        $isBuyer = $order->buyer_id === $user->id;
        $isFarmer = $order->riceProduct->farmer_id === $user->id;

        if (!$isBuyer && !$isFarmer) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$order->canBeCancelled()) {
            return response()->json([
                'message' => 'Order cannot be cancelled in current state'
            ], 422);
        }

        $reason = $request->input('reason', 'Cancelled by ' . ($isBuyer ? 'buyer' : 'farmer'));
        $order->cancel($reason);

        return response()->json([
            'message' => 'Order cancelled',
            'order' => $order->fresh()
        ]);
    }

    /**
     * Farmer marks order as shipped
     */
    public function ship(Request $request, RiceOrder $order): JsonResponse
    {
        // Authorization check
        if ($order->riceProduct->farmer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$order->canBeShipped()) {
            return response()->json([
                'message' => 'Order cannot be shipped in current state'
            ], 422);
        }

        $trackingNumber = $request->input('tracking_number');

        $order->update([
            'status' => RiceOrder::STATUS_SHIPPED,
            'tracking_number' => $trackingNumber,
            'shipped_at' => now(),
            'auto_confirm_at' => now()->addDays(7),
        ]);

        return response()->json([
            'message' => 'Order marked as shipped',
            'order' => $order->fresh()
        ]);
    }

    /**
     * Buyer confirms delivery
     */
    public function confirmDelivery(RiceOrder $order): JsonResponse
    {
        // Authorization check - only buyer can confirm
        if ($order->buyer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->status !== RiceOrder::STATUS_SHIPPED) {
            return response()->json([
                'message' => 'Only shipped orders can be confirmed as delivered'
            ], 422);
        }

        $order->deliver();

        return response()->json([
            'message' => 'Delivery confirmed',
            'order' => $order->fresh()
        ]);
    }

    /**
     * Buyer raises a dispute
     */
    public function dispute(Request $request, RiceOrder $order): JsonResponse
    {
        // Authorization check - only buyer can dispute
        if ($order->buyer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->status !== RiceOrder::STATUS_SHIPPED) {
            return response()->json([
                'message' => 'Only shipped orders can be disputed'
            ], 422);
        }

        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Dispute reason is required',
                'errors' => $validator->errors()
            ], 422);
        }

        $order->update([
            'status' => RiceOrder::STATUS_DISPUTED,
            'dispute_reason' => $request->reason,
            'auto_confirm_at' => null, // Stop auto-confirm
        ]);

        return response()->json([
            'message' => 'Dispute raised',
            'order' => $order->fresh()
        ]);
    }

    /**
     * Resolve a dispute (farmer)
     */
    public function resolveDispute(Request $request, RiceOrder $order): JsonResponse
    {
        // Authorization check
        if ($order->riceProduct->farmer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->status !== RiceOrder::STATUS_DISPUTED) {
            return response()->json([
                'message' => 'Order is not in disputed state'
            ], 422);
        }

        $resolution = $request->input('resolution', 'delivered'); // delivered or refunded

        if ($resolution === 'refunded') {
            // Release the reserved quantity
            $order->riceProduct->releaseQuantity($order->quantity);

            $order->update([
                'status' => RiceOrder::STATUS_REFUNDED,
                'payment_status' => RiceOrder::PAYMENT_REFUNDED,
            ]);
        } else {
            $order->update([
                'status' => RiceOrder::STATUS_DELIVERED,
                'actual_delivery_date' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Dispute resolved',
            'order' => $order->fresh()
        ]);
    }
}
