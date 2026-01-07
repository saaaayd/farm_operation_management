<?php

namespace App\Http\Controllers\MarketPlace;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ProductImageController extends Controller
{
    /**
     * Upload product images
     */
    public function upload(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'images.required' => 'Please select at least one image to upload.',
            'images.max' => 'You can upload a maximum of 5 images.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Only JPG, JPEG, PNG, and WebP images are allowed.',
            'images.*.max' => 'Each image must be less than 2MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $uploadedUrls = [];

            foreach ($request->file('images') as $image) {
                // Generate unique filename
                $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();

                // Store in public disk under products directory
                $path = $image->storeAs('products', $filename, 'public');

                // Generate public URL using asset helper
                $url = asset('storage/' . $path);
                $uploadedUrls[] = $url;
            }

            return response()->json([
                'message' => 'Images uploaded successfully',
                'urls' => $uploadedUrls
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload images',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a product image
     */
    public function delete(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'url' => 'required|string|url',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $url = $request->input('url');

            // Extract filename from URL
            $filename = basename(parse_url($url, PHP_URL_PATH));
            $path = 'products/' . $filename;

            // Check if file exists and delete
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);

                return response()->json([
                    'message' => 'Image deleted successfully'
                ]);
            }

            return response()->json([
                'message' => 'Image not found'
            ], 404);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete image',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
