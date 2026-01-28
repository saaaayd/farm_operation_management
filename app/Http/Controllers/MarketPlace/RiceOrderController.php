<?php

namespace App\Http\Controllers\MarketPlace;

use App\Http\Controllers\Controller;
use App\Models\RiceOrder;
use App\Models\RiceProduct;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
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

        $perPage = $request->input('per_page', 10);
        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

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

        $perPage = $request->input('per_page', 10);
        $orders = $query->orderBy('created_at', 'desc')->paginate($perPage);

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
            'delivery_method' => 'required|string|in:pickup',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
            'offer_price' => 'nullable|numeric|min:0.1',
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
                'offer_price' => $request->input('offer_price'),
            ]);

            // If offer price is present and less than unit price, set status to negotiating
            if ($request->filled('offer_price') && (float) $request->input('offer_price') < (float) $unitPrice) {
                $order->update([
                    'status' => RiceOrder::STATUS_NEGOTIATING,
                    'total_amount' => $request->quantity * $request->input('offer_price'),
                ]);
            }

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

            // Invalidate farmer order stats cache
            Cache::forget("farmer_order_stats_{$product->farmer_id}");

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

        // Notify buyer that order has been accepted
        \App\Models\Notification::notify(
            $order->buyer_id,
            \App\Models\Notification::TYPE_ORDER_STATUS,
            'Order Accepted',
            "Your order #{$order->id} has been accepted by the farmer." . ($expectedDelivery ? " Expected delivery: {$expectedDelivery}" : ""),
            ['order_id' => $order->id],
            "/orders/{$order->id}"
        );

        // Invalidate farmer order stats cache
        Cache::forget("farmer_order_stats_{$order->riceProduct->farmer_id}");

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

        // Notify buyer that order has been rejected
        \App\Models\Notification::notify(
            $order->buyer_id,
            \App\Models\Notification::TYPE_ORDER_STATUS,
            'Order Rejected',
            "Your order #{$order->id} has been rejected by the farmer. Reason: {$reason}",
            ['order_id' => $order->id],
            "/orders/{$order->id}"
        );

        // Invalidate farmer order stats cache
        Cache::forget("farmer_order_stats_{$order->riceProduct->farmer_id}");

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

        // If cancelled by farmer, notify buyer
        if ($isFarmer) {
            \App\Models\Notification::notify(
                $order->buyer_id,
                \App\Models\Notification::TYPE_ORDER_STATUS,
                'Order Cancelled',
                "Your order #{$order->id} has been cancelled by the farmer. Reason: {$reason}",
                ['order_id' => $order->id],
                "/orders/{$order->id}"
            );
        }
        // If cancelled by buyer, notify farmer (optional, but good practice)
        else if ($isBuyer) {
            \App\Models\Notification::notify(
                $order->riceProduct->farmer_id,
                \App\Models\Notification::TYPE_ORDER_STATUS,
                'Order Cancelled',
                "Order #{$order->id} for {$order->riceProduct->name} has been cancelled by the buyer.",
                ['order_id' => $order->id],
                "/farmer/orders"
            );
        }

        return response()->json([
            'message' => 'Order cancelled',
            'order' => $order->fresh()
        ]);
    }

    /**
     * Farmer marks order as ready for pickup
     */
    public function markReadyForPickup(Request $request, RiceOrder $order): JsonResponse
    {
        // Authorization check
        if ($order->riceProduct->farmer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$order->canBeMarkedReady()) {
            return response()->json([
                'message' => 'Order cannot be marked ready in current state'
            ], 422);
        }

        $order->markReadyForPickup();

        // Notify buyer that order is ready for pickup
        \App\Models\Notification::notify(
            $order->buyer_id,
            \App\Models\Notification::TYPE_ORDER_STATUS,
            'Order Ready for Pickup',
            "Your order #{$order->id} is ready for pickup",
            ['order_id' => $order->id],
            "/orders/{$order->id}"
        );

        // Invalidate farmer order stats cache
        Cache::forget("farmer_order_stats_{$order->riceProduct->farmer_id}");

        return response()->json([
            'message' => 'Order marked as ready for pickup',
            'order' => $order->fresh()
        ]);
    }

    /**
     * Farmer marks order as paid
     */
    public function markAsPaid(RiceOrder $order): JsonResponse
    {
        // Authorization check
        if ($order->riceProduct->farmer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($order->payment_status === RiceOrder::PAYMENT_PAID) {
            return response()->json([
                'message' => 'Order is already marked as paid'
            ], 422);
        }

        $order->update([
            'payment_status' => RiceOrder::PAYMENT_PAID,
            // If it was COD, we might assume it's now paid via Cash, but keeping original method is safer unless specified
            // 'payment_method' => 'cash' 
        ]);

        // Invalidate farmer order stats cache
        Cache::forget("farmer_order_stats_{$order->riceProduct->farmer_id}");

        return response()->json([
            'message' => 'Order marked as paid',
            'order' => $order->fresh()
        ]);
    }

    /**
     * Farmer confirms pickup (buyer has picked up the order)
     */
    public function confirmPickup(RiceOrder $order): JsonResponse
    {
        // Authorization check - farmer confirms when buyer picks up
        if ($order->riceProduct->farmer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!$order->canBePickedUp()) {
            return response()->json([
                'message' => 'Order cannot be marked as picked up in current state'
            ], 422);
        }

        $order->markPickedUp();

        // Create a Sale record for this completed order
        $this->createSaleFromOrder($order);

        // Notify buyer that order has been marked as picked up
        \App\Models\Notification::notify(
            $order->buyer_id,
            \App\Models\Notification::TYPE_ORDER_DELIVERED,
            'Order Picked Up',
            "Your order #{$order->id} has been marked as picked up",
            ['order_id' => $order->id],
            "/orders/{$order->id}"
        );

        // Invalidate farmer order stats cache
        Cache::forget("farmer_order_stats_{$order->riceProduct->farmer_id}");

        return response()->json([
            'message' => 'Pickup confirmed',
            'order' => $order->fresh()
        ]);
    }

    /**
     * Create a Sale record from a completed order
     */
    private function createSaleFromOrder(RiceOrder $order): void
    {
        // Use a nested transaction (savepoint) to prevent PostgreSQL from aborting the outer transaction
        // if the sale creation fails. The try-catch is inside the transaction to properly handle errors.
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($order) {
                // Check if sale already exists for this order
                $existingSale = \App\Models\Sale::where('rice_order_id', $order->id)->first();
                if ($existingSale) {
                    return; // Sale already created
                }

                // Find or create a Buyer record for this marketplace user
                $farmerId = $order->riceProduct->farmer_id;
                $buyerUser = \App\Models\User::find($order->buyer_id);

                $buyer = \App\Models\Buyer::where('user_id', $farmerId)
                    ->where('email', $buyerUser->email)
                    ->first();

                if (!$buyer) {
                    $buyer = \App\Models\Buyer::create([
                        'user_id' => $farmerId,
                        'name' => $buyerUser->name,
                        'email' => $buyerUser->email,
                        'contact_info' => $buyerUser->phone ?? $buyerUser->email,
                        'address' => is_array($order->delivery_address) ? ($order->delivery_address['address'] ?? 'Marketplace') : 'Marketplace',
                        'type' => 'individual',
                        'status' => 'active',
                        'notes' => 'Created from Marketplace Order',
                    ]);
                }

                \App\Models\Sale::create([
                    'user_id' => $farmerId,
                    'rice_order_id' => $order->id,
                    'harvest_id' => $order->riceProduct->harvest_id ?? null,
                    'buyer_id' => $buyer->id, // Link to Buyer record, not User
                    'quantity' => $order->quantity,
                    'unit_price' => $order->offer_price ?? $order->unit_price,
                    'total_amount' => $order->total_amount,
                    'sale_date' => now(),
                    'payment_method' => $order->payment_method ?? 'marketplace',
                    'payment_status' => $order->payment_status === RiceOrder::PAYMENT_PAID ? 'paid' : 'pending',
                    'notes' => "Marketplace order #{$order->id}",
                ]);

                \Log::info("Sale created for order #{$order->id}");
            });
        } catch (\Exception $e) {
            \Log::error("Failed to create sale for order #{$order->id}: " . $e->getMessage());
            // Don't throw - sale creation failure shouldn't block order completion
        }
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

        if ($order->status !== RiceOrder::STATUS_READY_FOR_PICKUP) {
            return response()->json([
                'message' => 'Only orders ready for pickup can be disputed'
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
                'status' => RiceOrder::STATUS_PICKED_UP,
                'actual_delivery_date' => now(),
            ]);
        }

        return response()->json([
            'message' => 'Dispute resolved',
            'order' => $order->fresh()
        ]);
    }
}
