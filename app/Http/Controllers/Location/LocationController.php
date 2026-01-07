<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

class LocationController extends Controller
{
    /**
     * Get list of provinces from PSGC
     */
    public function getProvinces(): JsonResponse
    {
        try {
            $response = Http::timeout(10)
                ->retry(2, 100)
                ->get('https://psgc.gitlab.io/api/provinces/');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            Log::warning('PSGC API returned non-200 status', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'error' => 'Failed to fetch provinces',
                'message' => 'Location service temporarily unavailable'
            ], 503);
        } catch (ConnectionException $e) {
            Log::error('PSGC API connection failed', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to location service'
            ], 503);
        } catch (\Exception $e) {
            Log::error('PSGC API error', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Service error',
                'message' => 'Location service error occurred'
            ], 500);
        }
    }

    /**
     * Get cities/municipalities for a province
     */
    public function getCities($code): JsonResponse
    {
        try {
            $response = Http::timeout(10)
                ->retry(2, 100)
                ->get("https://psgc.gitlab.io/api/provinces/{$code}/cities-municipalities/");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            Log::warning('PSGC API returned non-200 status', [
                'status' => $response->status(),
                'code' => $code,
                'body' => $response->body()
            ]);

            return response()->json([
                'error' => 'Failed to fetch cities',
                'message' => 'Location service temporarily unavailable'
            ], 503);
        } catch (ConnectionException $e) {
            Log::error('PSGC API connection failed', ['error' => $e->getMessage(), 'code' => $code]);
            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to location service'
            ], 503);
        } catch (\Exception $e) {
            Log::error('PSGC API error', ['error' => $e->getMessage(), 'code' => $code]);
            return response()->json([
                'error' => 'Service error',
                'message' => 'Location service error occurred'
            ], 500);
        }
    }

    /**
     * Get barangays for a city/municipality
     */
    public function getBarangays($code): JsonResponse
    {
        try {
            $response = Http::timeout(10)
                ->retry(2, 100)
                ->get("https://psgc.gitlab.io/api/cities-municipalities/{$code}/barangays/");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            Log::warning('PSGC API returned non-200 status', [
                'status' => $response->status(),
                'code' => $code,
                'body' => $response->body()
            ]);

            return response()->json([
                'error' => 'Failed to fetch barangays',
                'message' => 'Location service temporarily unavailable'
            ], 503);
        } catch (ConnectionException $e) {
            Log::error('PSGC API connection failed', ['error' => $e->getMessage(), 'code' => $code]);
            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to location service'
            ], 503);
        } catch (\Exception $e) {
            Log::error('PSGC API error', ['error' => $e->getMessage(), 'code' => $code]);
            return response()->json([
                'error' => 'Service error',
                'message' => 'Location service error occurred'
            ], 500);
        }
    }
}
