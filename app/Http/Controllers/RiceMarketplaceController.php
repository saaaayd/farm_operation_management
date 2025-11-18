<?php

namespace App\Http\Controllers;

use App\Models\RiceProduct;
use App\Models\RiceOrder;
use App\Models\ProductReview;
use App\Models\RiceVariety;
use App\Models\User;
use App\Services\SmsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RiceMarketplaceController extends Controller
{
    /**
     * Get all rice products in marketplace
     */
    public function getProducts(Request $request)
    {
        try {
            // For buyers, show only approved products that are available or in production (for pre-order)
            $query = RiceProduct::with(['riceVariety', 'farmer', 'reviews'])
                ->where('approval_status', 'approved') // Only show approved products
                ->availableOrPreOrder();

            // Apply filters
            if ($request->has('variety_id')) {
                $query->byVariety($request->variety_id);
            }

            if ($request->has('grade')) {
                $query->byGrade($request->grade);
            }

            if ($request->has('organic') && $request->organic) {
                $query->organic();
            }

            if ($request->has('production_status')) {
                $query->where('production_status', $request->production_status);
            }

            if ($request->has('min_price') && $request->has('max_price')) {
                $query->priceRange($request->min_price, $request->max_price);
            }

            if ($request->has('location') && $request->has('radius')) {
                $location = $request->location;
                $radius = $request->radius ?? 50;
                
                if (isset($location['latitude']) && isset($location['longitude'])) {
                    $query->nearLocation($location['latitude'], $location['longitude'], $radius);
                }
            }

            // Search by name or description
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');

            switch ($sortBy) {
                case 'price':
                    $query->orderBy('price_per_unit', $sortOrder);
                    break;
                case 'rating':
                    $query->withAvg('reviews', 'rating')
                          ->orderBy('reviews_avg_rating', $sortOrder);
                    break;
                case 'quantity':
                    $query->orderBy('quantity_available', $sortOrder);
                    break;
                case 'harvest_date':
                    $query->orderBy('harvest_date', $sortOrder);
                    break;
                case 'available_from':
                    $query->orderBy('available_from', $sortOrder);
                    break;
                default:
                    $query->orderBy('created_at', $sortOrder);
            }

            $perPage = min($request->get('per_page', 20), 50);
            $products = $query->paginate($perPage);

            // Add calculated fields
            $products->getCollection()->transform(function ($product) {
                $product->quality_score = $product->getQualityScore();
                $product->freshness_indicator = $product->getFreshnessIndicator();
                $product->average_rating = $product->average_rating;
                $product->reviews_count = $product->reviews_count;
                $product->can_pre_order = $product->canBePreOrdered();
                return $product;
            });

            return response()->json([
                'products' => $products,
                'filters' => [
                    'varieties' => RiceVariety::active()->get(['id', 'name']),
                    'grades' => [
                        RiceProduct::GRADE_PREMIUM => 'Premium',
                        RiceProduct::GRADE_GRADE_A => 'Grade A',
                        RiceProduct::GRADE_GRADE_B => 'Grade B',
                        RiceProduct::GRADE_COMMERCIAL => 'Commercial',
                    ],
                    'processing_methods' => [
                        RiceProduct::PROCESSING_MILLED => 'Milled',
                        RiceProduct::PROCESSING_BROWN => 'Brown Rice',
                        RiceProduct::PROCESSING_PARBOILED => 'Parboiled',
                        RiceProduct::PROCESSING_ORGANIC => 'Organic',
                    ],
                    'production_statuses' => [
                        RiceProduct::STATUS_AVAILABLE => 'Available',
                        RiceProduct::STATUS_IN_PRODUCTION => 'In Production',
                        RiceProduct::STATUS_OUT_OF_STOCK => 'Out of Stock',
                    ],
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch rice products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific rice product
     */
    public function getProduct($id)
    {
        try {
            $product = RiceProduct::with([
                'riceVariety',
                'farmer',
                'harvest',
                'reviews.buyer',
                'reviews' => function ($query) {
                    $query->approved()->latest();
                }
            ])->findOrFail($id);

            // Add calculated fields
            $product->quality_score = $product->getQualityScore();
            $product->freshness_indicator = $product->getFreshnessIndicator();
            $product->average_rating = $product->average_rating;
            $product->reviews_count = $product->reviews_count;
            $product->can_pre_order = $product->canBePreOrdered();

            // Get estimated delivery time if user location is available
            $user = auth()->user();
            if ($user && isset($user->address['location'])) {
                $product->estimated_delivery = $product->getEstimatedDeliveryTime($user->address['location']);
            }

            // Get similar products
            $similarProducts = RiceProduct::with(['riceVariety', 'farmer'])
                ->available()
                ->where('id', '!=', $product->id)
                ->where(function ($query) use ($product) {
                    $query->where('rice_variety_id', $product->rice_variety_id)
                          ->orWhere('quality_grade', $product->quality_grade);
                })
                ->limit(4)
                ->get();

            return response()->json([
                'product' => $product,
                'similar_products' => $similarProducts,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Rice product not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create a new rice product (farmers only)
     */
    public function createProduct(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rice_variety_id' => 'required|exists:rice_varieties,id',
            'harvest_id' => 'nullable|exists:harvests,id',
            'name' => 'required|string|max:255',
            'description' => 'required|string|max:2000',
            'quantity_available' => 'required|numeric|min:0',
            'unit' => 'required|string|in:kg,tons,bags,sacks',
            'price_per_unit' => 'required|numeric|min:0',
            'quality_grade' => 'required|string|in:premium,grade_a,grade_b,commercial',
            'moisture_content' => 'nullable|numeric|between:5,25',
            'purity_percentage' => 'nullable|numeric|between:50,100',
            'harvest_date' => 'nullable|date|before_or_equal:today',
            'processing_method' => 'nullable|string|in:milled,brown,parboiled,organic',
            'storage_conditions' => 'nullable|string|max:500',
            'certification' => 'nullable|string|max:255',
            'images' => 'nullable|array|max:5',
            'images.*' => 'string|url',
            'location' => 'nullable|array',
            'location.latitude' => 'required_with:location|numeric|between:-90,90',
            'location.longitude' => 'required_with:location|numeric|between:-180,180',
            'location.address' => 'required_with:location|string|max:255',
            'is_organic' => 'boolean',
            'minimum_order_quantity' => 'nullable|numeric|min:0',
            'packaging_options' => 'nullable|array',
            'delivery_options' => 'nullable|array',
            'payment_terms' => 'nullable|string|max:500',
            'contact_info' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = auth()->user();

            // Check if user is a farmer
            if (!$user->isFarmer()) {
                return response()->json(['message' => 'Only farmers can create rice products'], 403);
            }

            $validated = $validator->validated();
            $product = RiceProduct::create(array_merge($validated, [
                'farmer_id' => $user->id,
                'is_available' => true,
                'approval_status' => 'pending', // Products require admin approval
            ]));

            // Log product creation
            \App\Models\ActivityLog::log('product.created', $product, null, $product->toArray(), "New product listing pending approval");

            return response()->json([
                'message' => 'Rice product created successfully',
                'product' => $product->load(['riceVariety', 'farmer']),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create rice product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update rice product (farmers only - own products)
     */
    public function updateProduct(Request $request, $id)
    {
        try {
            $product = RiceProduct::findOrFail($id);
            $user = auth()->user();

            // Check if user owns this product
            if ($product->farmer_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'string|max:255',
                'description' => 'string|max:2000',
                'quantity_available' => 'numeric|min:0',
                'price_per_unit' => 'numeric|min:0',
                'quality_grade' => 'string|in:premium,grade_a,grade_b,commercial',
                'moisture_content' => 'nullable|numeric|between:5,25',
                'purity_percentage' => 'nullable|numeric|between:50,100',
                'processing_method' => 'nullable|string|in:milled,brown,parboiled,organic',
                'storage_conditions' => 'nullable|string|max:500',
                'certification' => 'nullable|string|max:255',
                'images' => 'nullable|array|max:5',
                'images.*' => 'string|url',
                'is_organic' => 'boolean',
                'is_available' => 'boolean',
                'minimum_order_quantity' => 'nullable|numeric|min:0',
                'packaging_options' => 'nullable|array',
                'delivery_options' => 'nullable|array',
                'payment_terms' => 'nullable|string|max:500',
                'contact_info' => 'nullable|array',
                'notes' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $product->update($validator->validated());

            return response()->json([
                'message' => 'Rice product updated successfully',
                'product' => $product->load(['riceVariety', 'farmer']),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update rice product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete rice product (farmers only - own products)
     */
    public function deleteProduct($id)
    {
        try {
            $product = RiceProduct::findOrFail($id);
            $user = auth()->user();

            // Check if user owns this product
            if ($product->farmer_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Check if there are active orders
            $activeOrders = RiceOrder::where('rice_product_id', $product->id)
                ->active()
                ->count();

            if ($activeOrders > 0) {
                return response()->json([
                    'message' => 'Cannot delete product with active orders'
                ], 400);
            }

            $product->delete();

            return response()->json([
                'message' => 'Rice product deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete rice product',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create an order for rice product (users only)
     */
    public function createOrder(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'rice_product_id' => 'required|exists:rice_products,id',
            'quantity' => 'required|numeric|min:0.1',
            'delivery_address' => 'required|array',
            'delivery_address.street' => 'required|string|max:255',
            'delivery_address.city' => 'required|string|max:100',
            'delivery_address.state' => 'required|string|max:100',
            'delivery_address.postal_code' => 'required|string|max:20',
            'delivery_address.country' => 'required|string|max:100',
            'delivery_method' => 'required|string|in:pickup,courier,postal,truck',
            'payment_method' => 'required|string|max:50',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();

            // Check if user can buy (is a regular user, not farmer)
            if (!$user->canBuy()) {
                return response()->json(['message' => 'Only marketplace users can place orders'], 403);
            }

            $product = RiceProduct::findOrFail($request->rice_product_id);

            // Check if product is available or can be pre-ordered
            if (!$product->is_available && !$product->canBePreOrdered()) {
                return response()->json(['message' => 'Product is not available for order'], 400);
            }

            $isPreOrder = $product->isInProduction();

            // For available products, check quantity
            if (!$isPreOrder) {
                // Check if sufficient quantity is available
                if (!$product->hasSufficientQuantity($request->quantity)) {
                    return response()->json([
                        'message' => 'Insufficient quantity available',
                        'available_quantity' => $product->quantity_available
                    ], 400);
                }
            }

            // Check minimum order quantity
            if ($product->minimum_order_quantity && $request->quantity < $product->minimum_order_quantity) {
                return response()->json([
                    'message' => 'Order quantity is below minimum required',
                    'minimum_quantity' => $product->minimum_order_quantity
                ], 400);
            }

            // Calculate total amount
            $totalAmount = $request->quantity * $product->price_per_unit;

            // Determine available date for pre-orders
            $availableDate = $isPreOrder ? $product->available_from : null;

            // Create the order
            $order = RiceOrder::create([
                'buyer_id' => $user->id,
                'rice_product_id' => $product->id,
                'quantity' => $request->quantity,
                'unit_price' => $product->price_per_unit,
                'total_amount' => $totalAmount,
                'status' => RiceOrder::STATUS_PENDING,
                'is_pre_order' => $isPreOrder,
                'available_date' => $availableDate,
                'delivery_address' => $request->delivery_address,
                'delivery_method' => $request->delivery_method,
                'payment_method' => $request->payment_method,
                'payment_status' => RiceOrder::PAYMENT_PENDING,
                'notes' => $request->notes,
                'order_date' => now(),
            ]);

            DB::commit();

            return response()->json([
                'message' => $isPreOrder ? 'Pre-order created successfully' : 'Order created successfully',
                'order' => $order->load(['riceProduct.riceVariety', 'riceProduct.farmer']),
                'is_pre_order' => $isPreOrder,
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'message' => 'Failed to create order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get orders for current user
     */
    public function getOrders(Request $request)
    {
        try {
            $user = auth()->user();
            
            $query = RiceOrder::with(['riceProduct.riceVariety', 'riceProduct.farmer']);

            if ($user->isFarmer()) {
                // Farmer sees orders for their products
                $query->forFarmer($user->id);
            } else {
                // Buyer sees their own orders
                $query->forBuyer($user->id);
            }

            // Filter by status
            if ($request->has('status')) {
                $query->byStatus($request->status);
            }

            // Filter by date range
            if ($request->has('from_date')) {
                $query->where('order_date', '>=', $request->from_date);
            }

            if ($request->has('to_date')) {
                $query->where('order_date', '<=', $request->to_date);
            }

            $orders = $query->orderBy('order_date', 'desc')->paginate(20);

            // Add calculated fields
            $orders->getCollection()->transform(function ($order) {
                $order->progress_percentage = $order->getProgressPercentage();
                $order->is_overdue = $order->isOverdue();
                $order->days_until_delivery = $order->getDaysUntilDelivery();
                return $order;
            });

            return response()->json([
                'orders' => $orders,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific order
     */
    public function getOrder($id)
    {
        try {
            $user = auth()->user();
            
            $order = RiceOrder::with([
                'riceProduct.riceVariety',
                'riceProduct.farmer',
                'buyer'
            ])->findOrFail($id);

            // Check if user can access this order
            $canAccess = ($user->isFarmer() && $order->riceProduct->farmer_id === $user->id) ||
                        ($user->canBuy() && $order->buyer_id === $user->id);

            if (!$canAccess) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Add calculated fields
            $order->progress_percentage = $order->getProgressPercentage();
            $order->is_overdue = $order->isOverdue();
            $order->days_until_delivery = $order->getDaysUntilDelivery();

            return response()->json([
                'order' => $order,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Order not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Confirm order (farmers only)
     */
    public function confirmOrder(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'expected_delivery_date' => 'nullable|date|after:today',
            'farmer_notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = auth()->user();
            $order = RiceOrder::with('riceProduct')->findOrFail($id);

            // Check if user is the farmer for this product
            if (!$user->isFarmer() || $order->riceProduct->farmer_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Check if order can be confirmed
            if (!$order->canBeConfirmed()) {
                return response()->json(['message' => 'Order cannot be confirmed'], 400);
            }

            $order->confirm($request->expected_delivery_date, $request->farmer_notes);

            return response()->json([
                'message' => 'Order confirmed successfully',
                'order' => $order->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to confirm order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel order
     */
    public function cancelOrder(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'reason' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = auth()->user();
            $order = RiceOrder::with('riceProduct')->findOrFail($id);

            // Check if user can cancel this order
            $canCancel = ($user->isFarmer() && $order->riceProduct->farmer_id === $user->id) ||
                        ($user->canBuy() && $order->buyer_id === $user->id);

            if (!$canCancel) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Check if order can be cancelled
            if (!$order->canBeCancelled()) {
                return response()->json(['message' => 'Order cannot be cancelled'], 400);
            }

            $order->cancel($request->reason);

            return response()->json([
                'message' => 'Order cancelled successfully',
                'order' => $order->fresh(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to cancel order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get marketplace statistics
     */
    public function getMarketplaceStats()
    {
        try {
            $stats = [
                'total_products' => RiceProduct::available()->count(),
                'total_farmers' => User::where('role', User::ROLE_FARMER)
                    ->whereHas('riceProducts', function ($query) {
                        $query->available();
                    })->count(),
                'total_orders' => RiceOrder::count(),
                'active_orders' => RiceOrder::active()->count(),
                'total_rice_varieties' => RiceVariety::active()->count(),
                'by_grade' => RiceProduct::available()
                    ->select('quality_grade', DB::raw('count(*) as count'))
                    ->groupBy('quality_grade')
                    ->pluck('count', 'quality_grade'),
                'by_processing' => RiceProduct::available()
                    ->select('processing_method', DB::raw('count(*) as count'))
                    ->groupBy('processing_method')
                    ->pluck('count', 'processing_method'),
                'organic_products' => RiceProduct::available()->organic()->count(),
                'average_price' => RiceProduct::available()->avg('price_per_unit'),
                'recent_products' => RiceProduct::with(['riceVariety', 'farmer'])
                    ->available()
                    ->latest()
                    ->limit(5)
                    ->get(),
            ];

            return response()->json([
                'stats' => $stats,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch marketplace statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}