<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Weather\WeatherController;
use App\Http\Controllers\Farmer\RiceFarmProfileController;
use Illuminate\Support\Facades\Http;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/verify-phone', [\App\Http\Controllers\Auth\VerificationController::class, 'verify']);
Route::post('/resend-verification', [\App\Http\Controllers\Auth\VerificationController::class, 'resend']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Proxy routes for PSGC API
    Route::get('/locations/provinces', function () {
        try {
            $response = Http::timeout(10)
                ->retry(2, 100)
                ->get('https://psgc.gitlab.io/api/provinces/');

            if ($response->successful()) {
                return response()->json($response->json());
            }

            \Log::warning('PSGC API returned non-200 status', [
                'status' => $response->status(),
                'body' => $response->body()
            ]);

            return response()->json([
                'error' => 'Failed to fetch provinces',
                'message' => 'Location service temporarily unavailable'
            ], 503);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('PSGC API connection failed', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to location service'
            ], 503);
        } catch (\Exception $e) {
            \Log::error('PSGC API error', ['error' => $e->getMessage()]);
            return response()->json([
                'error' => 'Service error',
                'message' => 'Location service error occurred'
            ], 500);
        }
    });

    Route::get('/locations/provinces/{code}/cities', function ($code) {
        try {
            $response = Http::timeout(10)
                ->retry(2, 100)
                ->get("https://psgc.gitlab.io/api/provinces/{$code}/cities-municipalities/");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            \Log::warning('PSGC API returned non-200 status', [
                'status' => $response->status(),
                'code' => $code,
                'body' => $response->body()
            ]);

            return response()->json([
                'error' => 'Failed to fetch cities',
                'message' => 'Location service temporarily unavailable'
            ], 503);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('PSGC API connection failed', ['error' => $e->getMessage(), 'code' => $code]);
            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to location service'
            ], 503);
        } catch (\Exception $e) {
            \Log::error('PSGC API error', ['error' => $e->getMessage(), 'code' => $code]);
            return response()->json([
                'error' => 'Service error',
                'message' => 'Location service error occurred'
            ], 500);
        }
    });

    Route::get('/locations/cities/{code}/barangays', function ($code) {
        try {
            $response = Http::timeout(10)
                ->retry(2, 100)
                ->get("https://psgc.gitlab.io/api/cities-municipalities/{$code}/barangays/");

            if ($response->successful()) {
                return response()->json($response->json());
            }

            \Log::warning('PSGC API returned non-200 status', [
                'status' => $response->status(),
                'code' => $code,
                'body' => $response->body()
            ]);

            return response()->json([
                'error' => 'Failed to fetch barangays',
                'message' => 'Location service temporarily unavailable'
            ], 503);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('PSGC API connection failed', ['error' => $e->getMessage(), 'code' => $code]);
            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to location service'
            ], 503);
        } catch (\Exception $e) {
            \Log::error('PSGC API error', ['error' => $e->getMessage(), 'code' => $code]);
            return response()->json([
                'error' => 'Service error',
                'message' => 'Location service error occurred'
            ], 500);
        }
    });

    // Geocoding proxy for Nominatim (to avoid CORS and set User-Agent)
    Route::get('/geocode', function (Request $request) {
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
                \Log::warning('Nominatim API returned invalid JSON', [
                    'query' => $query,
                    'response' => $response->body()
                ]);
                return response()->json([
                    'error' => 'Invalid response format',
                    'message' => 'Geocoding service returned invalid data'
                ], 502);
            }

            \Log::warning('Nominatim API returned non-200 status', [
                'status' => $response->status(),
                'query' => $query,
                'body' => $response->body()
            ]);

            return response()->json([
                'error' => 'Failed to geocode location',
                'message' => 'Geocoding service temporarily unavailable'
            ], 503);
        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            \Log::error('Nominatim API connection failed', ['error' => $e->getMessage(), 'query' => $query]);
            return response()->json([
                'error' => 'Connection failed',
                'message' => 'Unable to connect to geocoding service'
            ], 503);
        } catch (\Exception $e) {
            \Log::error('Nominatim API error', ['error' => $e->getMessage(), 'query' => $query]);
            return response()->json([
                'error' => 'Service error',
                'message' => 'Geocoding service error occurred'
            ], 500);
        }
    });

    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);

    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Rice Farm Profile routes
    Route::middleware('farmer')->prefix('farmer')->group(function () {
        Route::get('/profile', [RiceFarmProfileController::class, 'getProfile']);
        Route::post('/profile', [RiceFarmProfileController::class, 'createRiceFarmProfile']);
        Route::put('/profile', [RiceFarmProfileController::class, 'updateProfile']);
    });

    // Rice Varieties routes
    Route::prefix('rice-varieties')->group(function () {
        Route::get('/', [\App\Http\Controllers\RiceVarietyController::class, 'index']);
        Route::get('/current-season', [\App\Http\Controllers\Farmer\RiceFarmProfileController::class, 'getCurrentSeasonVarieties']);
        Route::get('/recommended/{field}', [\App\Http\Controllers\Farmer\RiceFarmProfileController::class, 'getRecommendedVarieties']);
        Route::get('/fields/{field}/analysis', [\App\Http\Controllers\Farmer\RiceFarmProfileController::class, 'getFieldAnalysis']);

    });

    // Rice Growth Stages routes
    Route::prefix('rice-growth-stages')->group(function () {
        Route::get('/', [\App\Http\Controllers\RiceGrowthStageController::class, 'index']);
        Route::get('/ordered', [\App\Http\Controllers\RiceGrowthStageController::class, 'getOrdered']);
    });

    // Field Analysis routes
    // Route::get('/fields/{field}/analysis', [\App\Http\Controllers\RiceFarmProfileController::class, 'getFieldAnalysis']);

    // Weather routes
    Route::prefix('weather')->group(function () {
        Route::get('/dashboard', [WeatherController::class, 'dashboard']);
        Route::get('/rice-dashboard', [WeatherController::class, 'getRiceDashboard']);
        Route::post('/update-all', [WeatherController::class, 'updateAllWeather']);

        // ColorfulClouds Weather API proxy (to avoid CORS)
        Route::get('/colorfulclouds', function (Request $request) {
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

                \Log::warning('ColorfulClouds API returned non-200 status', [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return response()->json([
                    'error' => 'Failed to fetch weather data',
                    'message' => 'Weather service temporarily unavailable'
                ], 503);
            } catch (\Illuminate\Http\Client\ConnectionException $e) {
                \Log::error('ColorfulClouds API connection failed', ['error' => $e->getMessage()]);
                return response()->json([
                    'error' => 'Connection failed',
                    'message' => 'Unable to connect to weather service'
                ], 503);
            } catch (\Exception $e) {
                \Log::error('ColorfulClouds API error', ['error' => $e->getMessage()]);
                return response()->json([
                    'error' => 'Service error',
                    'message' => 'Weather service error occurred'
                ], 500);
            }
        });

        Route::prefix('fields/{field}')->group(function () {
            Route::get('/current', [WeatherController::class, 'getCurrentWeather']);
            Route::get('/forecast', [WeatherController::class, 'getForecast']);
            Route::get('/history', [WeatherController::class, 'getHistory']);
            Route::get('/alerts', [WeatherController::class, 'getAlerts']);
            Route::get('/rice-analytics', [WeatherController::class, 'getRiceAnalytics']);
            Route::post('/update', [WeatherController::class, 'updateWeather']);
        });
    });

    // Field management routes
    Route::middleware('farmer')->prefix('fields')->group(function () {
        Route::get('/', [\App\Http\Controllers\Farm\FieldController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Farm\FieldController::class, 'store']);
        Route::get('/{field}', [\App\Http\Controllers\Farm\FieldController::class, 'show']);
        Route::put('/{field}', [\App\Http\Controllers\Farm\FieldController::class, 'update']);
        Route::delete('/{field}', [\App\Http\Controllers\Farm\FieldController::class, 'destroy']);
    });

    // Planting management routes
    Route::middleware('farmer')->prefix('plantings')->group(function () {
        Route::get('/', [\App\Http\Controllers\Farm\PlantingController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Farm\PlantingController::class, 'store']);
        Route::get('/{planting}', [\App\Http\Controllers\Farm\PlantingController::class, 'show']);
        Route::put('/{planting}', [\App\Http\Controllers\Farm\PlantingController::class, 'update']);
        Route::delete('/{planting}', [\App\Http\Controllers\Farm\PlantingController::class, 'destroy']);
    });

    // Rice Farming Lifecycle routes
    Route::middleware('farmer')->prefix('rice-farming')->group(function () {
        Route::post('/plantings', [\App\Http\Controllers\RiceFarmingLifecycleController::class, 'createRicePlanting']);
        Route::get('/lifecycle-overview', [\App\Http\Controllers\RiceFarmingLifecycleController::class, 'getLifecycleOverview']);
        Route::get('/plantings/{planting}/lifecycle', [\App\Http\Controllers\RiceFarmingLifecycleController::class, 'getPlantingLifecycle']);
        Route::post('/plantings/{planting}/advance-stage', [\App\Http\Controllers\RiceFarmingLifecycleController::class, 'advanceToNextStage']);
        Route::get('/plantings/{planting}/recommendations', [\App\Http\Controllers\RiceFarmingLifecycleController::class, 'getStageRecommendations']);
        Route::post('/stages/{stage}/delay', [\App\Http\Controllers\RiceFarmingLifecycleController::class, 'markStageDelayed']);
    });

    // Task management routes
    Route::middleware('farmer')->prefix('tasks')->group(function () {
        Route::get('/', [\App\Http\Controllers\Labor\TaskController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Labor\TaskController::class, 'store']);
        Route::get('/{task}', [\App\Http\Controllers\Labor\TaskController::class, 'show']);
        Route::put('/{task}', [\App\Http\Controllers\Labor\TaskController::class, 'update']);
        Route::delete('/{task}', [\App\Http\Controllers\Labor\TaskController::class, 'destroy']);
        Route::post('/{task}/complete', [\App\Http\Controllers\Labor\TaskController::class, 'markCompleted']);
    });

    // Harvest management routes
    Route::middleware('farmer')->prefix('harvests')->group(function () {
        Route::get('/', [\App\Http\Controllers\Farm\HarvestController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Farm\HarvestController::class, 'store']);
        Route::get('/{harvest}', [\App\Http\Controllers\Farm\HarvestController::class, 'show']);
        Route::put('/{harvest}', [\App\Http\Controllers\Farm\HarvestController::class, 'update']);
        Route::delete('/{harvest}', [\App\Http\Controllers\Farm\HarvestController::class, 'destroy']);
    });

    // Labor management routes
    Route::middleware(['auth:sanctum', 'farmer'])->prefix('laborers')->group(function () {
        Route::get('/', [\App\Http\Controllers\Labor\LaborerController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Labor\LaborerController::class, 'store']);

        // Groups
        Route::prefix('groups')->group(function () {
            Route::get('/', [\App\Http\Controllers\Labor\LaborerGroupController::class, 'index']);
            Route::post('/', [\App\Http\Controllers\Labor\LaborerGroupController::class, 'store']);
            Route::get('/{laborerGroup}', [\App\Http\Controllers\Labor\LaborerGroupController::class, 'show']);
            Route::post('/{laborerGroup}/members', [\App\Http\Controllers\Labor\LaborerGroupController::class, 'addMembers']);
            Route::put('/{laborerGroup}', [\App\Http\Controllers\Labor\LaborerGroupController::class, 'update']);
            Route::delete('/{laborerGroup}', [\App\Http\Controllers\Labor\LaborerGroupController::class, 'destroy']);
        });

        Route::get('/{laborer}', [\App\Http\Controllers\Labor\LaborerController::class, 'show']);
        Route::put('/{laborer}', [\App\Http\Controllers\Labor\LaborerController::class, 'update']);
        Route::delete('/{laborer}', [\App\Http\Controllers\Labor\LaborerController::class, 'destroy']);
    });

    Route::middleware('farmer')->prefix('labor-wages')->group(function () {
        Route::get('/', [\App\Http\Controllers\Labor\WageController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Labor\WageController::class, 'store']);
        Route::get('/{laborWage}', [\App\Http\Controllers\Labor\WageController::class, 'show']);
        Route::put('/{laborWage}', [\App\Http\Controllers\Labor\WageController::class, 'update']);
        Route::delete('/{laborWage}', [\App\Http\Controllers\Labor\WageController::class, 'destroy']);
    });

    // Inventory management routes
    Route::middleware('farmer')->prefix('inventory')->group(function () {
        Route::get('/', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'store']);
        Route::get('/{item}', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'show']);
        Route::put('/{item}', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'update']);
        Route::delete('/{item}', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'destroy']);
        Route::post('/{item}/add-stock', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'addStock']);
        Route::post('/{item}/remove-stock', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'removeStock']);
        Route::get('/{item}/transactions', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'getTransactions']);
        Route::get('/alerts/low-stock', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'lowStockAlerts']);
    });

    // Rice Marketplace routes
    Route::prefix('rice-marketplace')->group(function () {
        Route::get('/products', [\App\Http\Controllers\RiceMarketplaceController::class, 'getProducts']);
        Route::get('/products/{product}', [\App\Http\Controllers\RiceMarketplaceController::class, 'getProduct']);
        Route::get('/stats', [\App\Http\Controllers\RiceMarketplaceController::class, 'getMarketplaceStats']);

        // Product management (farmers only)
        Route::middleware('farmer')->group(function () {
            Route::post('/products', [\App\Http\Controllers\RiceMarketplaceController::class, 'createProduct']);
            Route::put('/products/{product}', [\App\Http\Controllers\RiceMarketplaceController::class, 'updateProduct']);
            Route::delete('/products/{product}', [\App\Http\Controllers\RiceMarketplaceController::class, 'deleteProduct']);
        });

        // Order management
        Route::get('/orders', [\App\Http\Controllers\RiceMarketplaceController::class, 'getOrders']);
        Route::get('/orders/{order}', [\App\Http\Controllers\RiceMarketplaceController::class, 'getOrder']);

        Route::middleware('buyer')->group(function () {
            Route::post('/orders', [\App\Http\Controllers\RiceMarketplaceController::class, 'createOrder']);
        });

        Route::middleware('farmer')->group(function () {
            Route::post('/orders/{order}/confirm', [\App\Http\Controllers\RiceMarketplaceController::class, 'confirmOrder']);
            Route::post('/orders/{order}/cancel', [\App\Http\Controllers\RiceMarketplaceController::class, 'cancelOrder']);
        });

        // Order messaging
        Route::get('/orders/{order}/messages', [\App\Http\Controllers\RiceOrderMessageController::class, 'index']);
        Route::post('/orders/{order}/messages', [\App\Http\Controllers\RiceOrderMessageController::class, 'store']);
    });

    // Legacy Marketplace routes (for backward compatibility)
    Route::prefix('marketplace')->group(function () {
        Route::get('/products', [\App\Http\Controllers\MarketPlace\ProductController::class, 'getAvailableProducts']);
        Route::get('/products/{product}', [\App\Http\Controllers\MarketPlace\ProductController::class, 'show']);
        Route::get('/categories', [\App\Http\Controllers\MarketPlace\ProductController::class, 'index']);
        Route::get('/categories/{category}/products', [\App\Http\Controllers\MarketPlace\ProductController::class, 'getByCategory']);

        // Cart management (simplified - using session for now)
        Route::prefix('cart')->group(function () {
            Route::get('/', [\App\Http\Controllers\MarketPlace\ProductController::class, 'getAvailableProducts']);
            Route::post('/add', [\App\Http\Controllers\MarketPlace\ProductController::class, 'getAvailableProducts']);
        });
    });

    // Order management routes
    Route::middleware('farmer')->prefix('orders')->group(function () {
        Route::get('/', [\App\Http\Controllers\Inventory\OrderController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Inventory\OrderController::class, 'store']);
        Route::get('/{order}', [\App\Http\Controllers\Inventory\OrderController::class, 'show']);
        Route::put('/{order}', [\App\Http\Controllers\Inventory\OrderController::class, 'update']);
        Route::post('/{order}/confirm', [\App\Http\Controllers\Inventory\OrderController::class, 'confirm']);
        Route::post('/{order}/cancel', [\App\Http\Controllers\Inventory\OrderController::class, 'cancel']);
        Route::post('/{order}/ship', [\App\Http\Controllers\Inventory\OrderController::class, 'ship']);
        Route::post('/{order}/deliver', [\App\Http\Controllers\Inventory\OrderController::class, 'deliver']);
    });

    // Sales management routes
    Route::middleware('farmer')->prefix('sales')->group(function () {
        Route::get('/', [\App\Http\Controllers\MarketPlace\SaleController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\MarketPlace\SaleController::class, 'store']);
        Route::get('/{sale}', [\App\Http\Controllers\MarketPlace\SaleController::class, 'show']);
        Route::put('/{sale}', [\App\Http\Controllers\MarketPlace\SaleController::class, 'update']);
        Route::delete('/{sale}', [\App\Http\Controllers\MarketPlace\SaleController::class, 'destroy']);
    });

    // Financial management routes
    Route::middleware('farmer')->prefix('expenses')->group(function () {
        Route::get('/', [\App\Http\Controllers\Financial\ExpenseController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Financial\ExpenseController::class, 'store']);
        Route::get('/{expense}', [\App\Http\Controllers\Financial\ExpenseController::class, 'show']);
        Route::put('/{expense}', [\App\Http\Controllers\Financial\ExpenseController::class, 'update']);
        Route::delete('/{expense}', [\App\Http\Controllers\Financial\ExpenseController::class, 'destroy']);
    });

    // Rice Farming Analytics routes
    Route::middleware('farmer')->prefix('analytics')->group(function () {
        Route::get('/rice-farming', [\App\Http\Controllers\RiceFarmingAnalyticsController::class, 'getRiceFarmingAnalytics']);
    });

    // Reports routes
    Route::middleware('farmer')->prefix('reports')->group(function () {
        Route::get('/financial', [\App\Http\Controllers\Reports\ReportController::class, 'getFinancialReport']);
        Route::get('/crop-yield', [\App\Http\Controllers\Reports\ReportController::class, 'getCropYieldReport']);
        Route::get('/weather', [\App\Http\Controllers\Reports\ReportController::class, 'getWeatherReport']);
        Route::get('/labor-cost', [\App\Http\Controllers\Labor\WageController::class, 'index']);
        Route::get('/weather-analysis', [\App\Http\Controllers\Weather\WeatherController::class, 'dashboard']);
        Route::get('/inventory-usage', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'index']);
    });

});