<?php

namespace App\Http\Controllers\Weather;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Log;

class WeatherProxyController extends Controller
{
    /**
     * colorfulClouds Weather API proxy
     */
    public function getColorfulCloudsWeather(Request $request): JsonResponse
    {
        $lat = $request->query('lat');
        $lon = $request->query('lon');
        $unit = $request->query('unit', 'imperial');
        $lang = $request->query('lang', 'en_US');
        $granu = $request->query('granu', 'realtime');

        if (!$lat || !$lon) {
            return response()->json(['error' => 'Latitude and longitude are required'], 400);
        }

        try {
            $token = config('services.colorfulclouds.api_token', 'S45Fnpxcwyq0QT4b');
            $url = "https://api.caiyunapp.com/v2.5/{$token}/{$lon},{$lat}/weather.json";

            $response = Http::timeout(10)
                ->retry(2, 100)
                ->get($url, [
                    'lang' => $lang,
                    'unit' => $unit,
                    'granu' => $granu,
                ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }

            Log::warning('ColorfulClouds API returned non-200 status', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'error' => 'Failed to fetch weather data',
                'message' => 'Weather service temporarily unavailable'
            ], 503);
        } catch (ConnectionException $e) {
            Log::error('ColorfulClouds API connection failed', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to weather service'
            ], 503);
        } catch (\Exception $e) {
            Log::error('ColorfulClouds API error', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Service error',
                'message' => 'Weather service error occurred'
            ], 500);
        }
    }
}
