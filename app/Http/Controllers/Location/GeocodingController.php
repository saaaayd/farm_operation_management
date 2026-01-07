<?php

namespace App\Http\Controllers\Location;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

class GeocodingController extends Controller
{
    /**
     * Geocoding proxy for Nominatim
     */
    public function geocode(Request $request): JsonResponse
    {
        $query = $request->query('q');
        if (!$query) {
            return response()->json(['error' => 'Query parameter "q" is required'], 400);
        }

        try {
            $response = Http::timeout(10)
                ->retry(2, 100)
                ->withHeaders([
                    'User-Agent' => 'RiceFARM Application (https://ricefarm.app)',
                ])->get('https://nominatim.openstreetmap.org/search', [
                        'q' => $query,
                        'format' => 'json',
                        'limit' => 1,
                        'countrycodes' => 'ph',
                    ]);

            if ($response->successful()) {
                $data = $response->json();
                if (is_array($data)) {
                    return response()->json($data);
                }
                Log::warning('Nominatim API returned invalid JSON', [
                    'query' => $query,
                    'response' => $response->body()
                ]);
                return response()->json([
                    'error' => 'Invalid response format',
                    'message' => 'Geocoding service returned invalid data'
                ], 502);
            }

            Log::warning('Nominatim API returned non-200 status', [
                'status' => $response->status(),
                'query' => $query,
                'body' => $response->body()
            ]);

            return response()->json([
                'error' => 'Failed to geocode location',
                'message' => 'Geocoding service temporarily unavailable'
            ], 503);
        } catch (ConnectionException $e) {
            Log::error('Nominatim API connection failed', ['error' => $e->getMessage(), 'query' => $query]);
            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to geocoding service'
            ], 503);
        } catch (\Exception $e) {
            Log::error('Nominatim API error', ['error' => $e->getMessage(), 'query' => $query]);
            return response()->json([
                'error' => 'Service error',
                'message' => 'Geocoding service error occurred'
            ], 500);
        }
    }
}
