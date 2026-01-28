<?php

namespace App\Http\Controllers\MarketPlace;

use App\Http\Controllers\Controller;
use App\Models\ProductReview;
use App\Models\RiceOrder;
use App\Models\RiceProduct;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ProductReviewController extends Controller
{
    /**
     * Get reviews for a product
     */
    public function index(RiceProduct $product): JsonResponse
    {
        $reviews = ProductReview::where('rice_product_id', $product->id)
            ->approved()
            ->with('buyer:id,name')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        $avgRating = ProductReview::where('rice_product_id', $product->id)
            ->approved()
            ->avg('rating');

        $ratingBreakdown = ProductReview::where('rice_product_id', $product->id)
            ->approved()
            ->selectRaw('rating, count(*) as count')
            ->groupBy('rating')
            ->pluck('count', 'rating');

        return response()->json([
            'reviews' => $reviews,
            'average_rating' => round($avgRating ?? 0, 1),
            'rating_breakdown' => $ratingBreakdown,
            'total_reviews' => ProductReview::where('rice_product_id', $product->id)->approved()->count(),
        ]);
    }

    /**
     * Submit a review for a delivered order
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'rice_order_id' => 'required|exists:rice_orders,id',
            'rating' => 'required|numeric|min:1|max:5',
            'title' => 'nullable|string|max:100',
            'review_text' => 'required|string|min:10|max:1000',
            'quality_rating' => 'nullable|numeric|min:1|max:5',
            'delivery_rating' => 'nullable|numeric|min:1|max:5',
            'farmer_rating' => 'nullable|numeric|min:1|max:5',
            'would_recommend' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $order = RiceOrder::findOrFail($request->rice_order_id);

        // Authorization: Only buyer can review their order
        if ($order->buyer_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Check order is delivered
        if ($order->status !== RiceOrder::STATUS_DELIVERED) {
            return response()->json([
                'message' => 'Only delivered orders can be reviewed'
            ], 422);
        }

        // Check not already reviewed
        $existingReview = ProductReview::where('rice_order_id', $order->id)->first();
        if ($existingReview) {
            return response()->json([
                'message' => 'You have already reviewed this order'
            ], 422);
        }

        $review = ProductReview::create([
            'rice_product_id' => $order->rice_product_id,
            'buyer_id' => Auth::id(),
            'rice_order_id' => $order->id,
            'rating' => $request->rating,
            'title' => $request->title,
            'review_text' => $request->review_text,
            'quality_rating' => $request->quality_rating,
            'delivery_rating' => $request->delivery_rating,
            'farmer_rating' => $request->farmer_rating,
            'would_recommend' => $request->would_recommend ?? true,
            'verified_purchase' => true,
            'is_approved' => true, // Auto-approve for now
        ]);

        return response()->json([
            'message' => 'Review submitted successfully',
            'review' => $review
        ], 201);
    }

    /**
     * Get buyer's own reviews
     */
    public function myReviews(): JsonResponse
    {
        $reviews = ProductReview::where('buyer_id', Auth::id())
            ->with(['riceProduct', 'riceOrder'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['reviews' => $reviews]);
    }

    /**
     * Check if order can be reviewed
     */
    public function canReview(RiceOrder $order): JsonResponse
    {
        if ($order->buyer_id !== Auth::id()) {
            return response()->json(['can_review' => false, 'reason' => 'Not your order']);
        }

        if ($order->status !== RiceOrder::STATUS_DELIVERED) {
            return response()->json(['can_review' => false, 'reason' => 'Order not delivered']);
        }

        $existingReview = ProductReview::where('rice_order_id', $order->id)->first();
        if ($existingReview) {
            return response()->json(['can_review' => false, 'reason' => 'Already reviewed', 'review' => $existingReview]);
        }

        return response()->json(['can_review' => true]);
    }
}
