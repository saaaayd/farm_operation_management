<?php

namespace App\Http\Controllers\MarketPlace;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\RiceProduct;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class FavoriteController extends Controller
{
    /**
     * Get all favorites for the authenticated user.
     */
    public function index(Request $request): JsonResponse
    {
        $favorites = Favorite::where('user_id', $request->user()->id)
            ->with([
                'riceProduct' => function ($query) {
                    $query->with(['farmer:id,name', 'riceVariety:id,name']);
                }
            ])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'favorites' => $favorites,
        ]);
    }

    /**
     * Add a product to favorites.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'rice_product_id' => 'required|exists:rice_products,id',
        ]);

        $userId = $request->user()->id;
        $productId = $request->rice_product_id;

        // Check if already favorited
        $existing = Favorite::where('user_id', $userId)
            ->where('rice_product_id', $productId)
            ->first();

        if ($existing) {
            return response()->json([
                'message' => 'Product already in favorites',
                'favorite' => $existing,
            ], 200);
        }

        $favorite = Favorite::create([
            'user_id' => $userId,
            'rice_product_id' => $productId,
        ]);

        $favorite->load([
            'riceProduct' => function ($query) {
                $query->with(['farmer:id,name', 'riceVariety:id,name']);
            }
        ]);

        return response()->json([
            'message' => 'Product added to favorites',
            'favorite' => $favorite,
        ], 201);
    }

    /**
     * Remove a product from favorites.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        $favorite = Favorite::where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$favorite) {
            return response()->json([
                'message' => 'Favorite not found',
            ], 404);
        }

        $favorite->delete();

        return response()->json([
            'message' => 'Product removed from favorites',
        ]);
    }

    /**
     * Toggle favorite status for a product.
     */
    public function toggle(Request $request): JsonResponse
    {
        $request->validate([
            'rice_product_id' => 'required|exists:rice_products,id',
        ]);

        $userId = $request->user()->id;
        $productId = $request->rice_product_id;

        $existing = Favorite::where('user_id', $userId)
            ->where('rice_product_id', $productId)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json([
                'message' => 'Product removed from favorites',
                'is_favorited' => false,
            ]);
        }

        $favorite = Favorite::create([
            'user_id' => $userId,
            'rice_product_id' => $productId,
        ]);

        return response()->json([
            'message' => 'Product added to favorites',
            'is_favorited' => true,
            'favorite' => $favorite,
        ], 201);
    }

    /**
     * Check if a product is favorited.
     */
    public function check(Request $request, $productId): JsonResponse
    {
        $isFavorited = Favorite::where('user_id', $request->user()->id)
            ->where('rice_product_id', $productId)
            ->exists();

        return response()->json([
            'is_favorited' => $isFavorited,
        ]);
    }
}
