<?php

namespace App\Http\Controllers\MarketPlace;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\RiceProduct;
use App\Models\RiceOrder;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CartController extends Controller
{
    /**
     * Get the current user's cart
     */
    public function index(): JsonResponse
    {
        $cartItems = CartItem::where('buyer_id', Auth::id())
            ->with(['riceProduct.farmer', 'riceProduct.riceVariety'])
            ->get();

        $total = $cartItems->sum(fn($item) => $item->quantity * ($item->riceProduct->price_per_unit ?? 0));
        $itemCount = $cartItems->sum('quantity');

        return response()->json([
            'items' => $cartItems,
            'total' => round($total, 2),
            'item_count' => $itemCount,
        ]);
    }

    /**
     * Add item to cart
     */
    public function addItem(Request $request): JsonResponse
    {
        $request->validate([
            'rice_product_id' => 'required|exists:rice_products,id',
            'quantity' => 'required|numeric|min:1',
        ]);

        $product = RiceProduct::findOrFail($request->rice_product_id);

        // Check if product is available
        if ($product->status !== 'available') {
            return response()->json(['message' => 'Product is not available'], 422);
        }

        // Check quantity against stock
        if ($request->quantity > $product->quantity_available) {
            return response()->json(['message' => 'Requested quantity exceeds available stock'], 422);
        }

        // Check if item already in cart
        $cartItem = CartItem::where('buyer_id', Auth::id())
            ->where('rice_product_id', $request->rice_product_id)
            ->first();

        if ($cartItem) {
            $newQty = $cartItem->quantity + $request->quantity;
            if ($newQty > $product->quantity_available) {
                return response()->json(['message' => 'Total quantity would exceed available stock'], 422);
            }
            $cartItem->update(['quantity' => $newQty]);
        } else {
            $cartItem = CartItem::create([
                'buyer_id' => Auth::id(),
                'rice_product_id' => $request->rice_product_id,
                'quantity' => $request->quantity,
            ]);
        }

        $cartItem->load('riceProduct');

        return response()->json([
            'message' => 'Item added to cart',
            'item' => $cartItem,
            'cart_count' => CartItem::where('buyer_id', Auth::id())->sum('quantity'),
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function updateItem(Request $request, CartItem $cartItem): JsonResponse
    {
        if ($cartItem->buyer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'quantity' => 'required|numeric|min:1',
        ]);

        $product = $cartItem->riceProduct;
        if ($request->quantity > $product->quantity_available) {
            return response()->json(['message' => 'Quantity exceeds available stock'], 422);
        }

        $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'message' => 'Cart updated',
            'item' => $cartItem->fresh(),
        ]);
    }

    /**
     * Remove item from cart
     */
    public function removeItem(CartItem $cartItem): JsonResponse
    {
        if ($cartItem->buyer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $cartItem->delete();

        return response()->json([
            'message' => 'Item removed from cart',
            'cart_count' => CartItem::where('buyer_id', Auth::id())->sum('quantity'),
        ]);
    }

    /**
     * Clear entire cart
     */
    public function clear(): JsonResponse
    {
        CartItem::where('buyer_id', Auth::id())->delete();

        return response()->json(['message' => 'Cart cleared']);
    }

    /**
     * Checkout - create orders from cart items
     */
    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'delivery_address' => 'required|array',
            'delivery_method' => 'required|string',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $cartItems = CartItem::where('buyer_id', Auth::id())
            ->with('riceProduct')
            ->get();

        if ($cartItems->isEmpty()) {
            return response()->json(['message' => 'Cart is empty'], 422);
        }

        $orders = [];

        DB::transaction(function () use ($cartItems, $request, &$orders) {
            foreach ($cartItems as $item) {
                $product = $item->riceProduct;

                // Verify stock
                if ($item->quantity > $product->quantity_available) {
                    throw new \Exception("Insufficient stock for {$product->name}");
                }

                // Create order
                $order = RiceOrder::create([
                    'buyer_id' => Auth::id(),
                    'rice_product_id' => $product->id,
                    'quantity' => $item->quantity,
                    'unit_price' => $product->price_per_unit,
                    'total_amount' => $item->quantity * $product->price_per_unit,
                    'status' => RiceOrder::STATUS_PENDING,
                    'payment_status' => RiceOrder::PAYMENT_PENDING,
                    'delivery_address' => $request->delivery_address,
                    'delivery_method' => $request->delivery_method,
                    'payment_method' => $request->payment_method,
                    'buyer_notes' => $request->notes,
                    'order_date' => now(),
                ]);

                // Notify farmer
                \App\Models\Notification::notify(
                    $product->farmer_id,
                    \App\Models\Notification::TYPE_ORDER_PLACED,
                    'New Order Received',
                    "You have a new order for {$item->quantity} kg of {$product->name}",
                    ['order_id' => $order->id],
                    "/farmer/orders/{$order->id}"
                );

                $orders[] = $order;
            }

            // Clear cart
            CartItem::where('buyer_id', Auth::id())->delete();
        });

        return response()->json([
            'message' => 'Checkout successful! ' . count($orders) . ' order(s) placed.',
            'orders' => $orders,
        ]);
    }

    /**
     * Get cart item count (for badge)
     */
    public function count(): JsonResponse
    {
        $count = CartItem::where('buyer_id', Auth::id())->sum('quantity');

        return response()->json(['count' => $count]);
    }
}
