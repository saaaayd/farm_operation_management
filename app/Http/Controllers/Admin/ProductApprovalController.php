<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RiceProduct;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductApprovalController extends Controller
{
    /**
     * Get pending product listings
     */
    public function getPendingProducts(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $products = RiceProduct::where('approval_status', 'pending')
            ->with(['farmer', 'riceVariety', 'approver'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json($products);
    }

    /**
     * Approve a product listing
     */
    public function approveProduct(Request $request, RiceProduct $product): JsonResponse
    {
        if ($product->isApproved()) {
            return response()->json([
                'message' => 'Product is already approved'
            ], 400);
        }

        $admin = $request->user();
        $oldValues = $product->toArray();

        $product->update([
            'approval_status' => 'approved',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);

        // Log the approval
        ActivityLog::log('product.approved', $product, $oldValues, $product->toArray(), "Product '{$product->name}' approved by admin");

        return response()->json([
            'message' => 'Product approved successfully',
            'product' => $product->load(['farmer', 'riceVariety', 'approver']),
        ]);
    }

    /**
     * Reject a product listing
     */
    public function rejectProduct(Request $request, RiceProduct $product): JsonResponse
    {
        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $admin = $request->user();
        $oldValues = $product->toArray();

        $product->update([
            'approval_status' => 'rejected',
            'approved_by' => $admin->id,
            'approved_at' => now(),
            'rejection_reason' => $request->rejection_reason,
            'is_available' => false, // Make unavailable when rejected
        ]);

        // Log the rejection
        ActivityLog::log('product.rejected', $product, $oldValues, $product->toArray(), "Product '{$product->name}' rejected by admin: {$request->rejection_reason}");

        return response()->json([
            'message' => 'Product rejected successfully',
            'product' => $product->load(['farmer', 'riceVariety', 'approver']),
        ]);
    }

    /**
     * Get all products with filters (for monitoring)
     */
    public function getAllProducts(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 15);
        $query = RiceProduct::with(['farmer', 'riceVariety', 'approver']);

        // Filter by approval status
        if ($request->has('approval_status')) {
            $query->where('approval_status', $request->approval_status);
        }

        // Filter by farmer
        if ($request->has('farmer_id')) {
            $query->where('farmer_id', $request->farmer_id);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $products = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($products);
    }

    /**
     * Get product approval statistics
     */
    public function getApprovalStats(): JsonResponse
    {
        $stats = [
            'pending' => RiceProduct::where('approval_status', 'pending')->count(),
            'approved' => RiceProduct::where('approval_status', 'approved')->count(),
            'rejected' => RiceProduct::where('approval_status', 'rejected')->count(),
            'total' => RiceProduct::count(),
        ];

        return response()->json($stats);
    }
}

