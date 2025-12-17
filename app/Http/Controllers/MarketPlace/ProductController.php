<?php

namespace App\Http\Controllers\MarketPlace;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    /**
     * Display a listing of product categories
     */
    public function index(Request $request): JsonResponse
    {
        $categories = Category::orderBy('name')->get();
        
        return response()->json([
            'categories' => $categories
        ]);
    }

    /**
     * Store a newly created product category
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category = Category::create([
            'name' => $request->name,
            'description' => $request->description,
            'image_url' => $request->image_url,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'message' => 'Product category created successfully',
            'category' => $category
        ], 201);
    }

    /**
     * Display the specified product category
     */
    public function show(Category $category): JsonResponse
    {
        $category->load(['harvests']);

        return response()->json([
            'category' => $category
        ]);
    }

    /**
     * Update the specified product category
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255|unique:categories,name,' . $category->id,
            'description' => 'nullable|string',
            'image_url' => 'nullable|url',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $category->update($request->only([
            'name', 'description', 'image_url', 'is_active'
        ]));

        return response()->json([
            'message' => 'Product category updated successfully',
            'category' => $category
        ]);
    }

    /**
     * Remove the specified product category
     */
    public function destroy(Category $category): JsonResponse
    {
        $category->delete();

        return response()->json([
            'message' => 'Product category deleted successfully'
        ]);
    }

    /**
     * Get products by category
     */
    public function getByCategory(Request $request, Category $category): JsonResponse
    {
        $user = $request->user();
        
        $query = $category->harvests();
        
        if ($user && $user->isFarmer()) {
            $query->whereHas('planting.field', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        $harvests = $query->with(['planting.field', 'planting.crop'])
            ->orderBy('harvest_date', 'desc')
            ->get();
        
        return response()->json([
            'category' => $category,
            'products' => $harvests
        ]);
    }

    /**
     * Get available products for sale
     */
    public function getAvailableProducts(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $query = \App\Models\Harvest::query();
        
        if ($user && $user->isFarmer()) {
            $query->whereHas('planting.field', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }
        
        // Filter out products that are already sold
        $query->whereDoesntHave('sales');
        
        if ($request->has('category_id')) {
            $query->whereHas('planting.crop', function ($q) use ($request) {
                $q->where('category_id', $request->category_id);
            });
        }
        
        $products = $query->with(['planting.field', 'planting.crop.category'])
            ->orderBy('harvest_date', 'desc')
            ->get();
        
        return response()->json([
            'available_products' => $products
        ]);
    }
}