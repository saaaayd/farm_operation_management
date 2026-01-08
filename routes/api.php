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
    Route::get('/locations/provinces', [\App\Http\Controllers\Location\LocationController::class, 'getProvinces']);

    Route::get('/locations/provinces/{code}/cities', [\App\Http\Controllers\Location\LocationController::class, 'getCities']);

    Route::get('/locations/cities/{code}/barangays', [\App\Http\Controllers\Location\LocationController::class, 'getBarangays']);

    // Geocoding proxy for Nominatim (to avoid CORS and set User-Agent)
    Route::get('/geocode', [\App\Http\Controllers\Location\GeocodingController::class, 'geocode']);

    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);
    Route::post('/profile/picture', [AuthController::class, 'uploadProfilePicture']);
    Route::delete('/profile/picture', [AuthController::class, 'deleteProfilePicture']);

    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Notification routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [\App\Http\Controllers\NotificationController::class, 'index']);
        Route::get('/unread-count', [\App\Http\Controllers\NotificationController::class, 'unreadCount']);
        Route::post('/{notification}/read', [\App\Http\Controllers\NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [\App\Http\Controllers\NotificationController::class, 'markAllAsRead']);
        Route::delete('/{notification}', [\App\Http\Controllers\NotificationController::class, 'destroy']);
    });

    // Rice Farm Profile routes
    Route::middleware('farmer')->prefix('farmer')->group(function () {
        Route::get('/profile', [RiceFarmProfileController::class, 'getProfile']);
        Route::post('/profile', [RiceFarmProfileController::class, 'createRiceFarmProfile']);
        Route::put('/profile', [RiceFarmProfileController::class, 'updateProfile']);
    });

    // Pest Incident routes
    Route::middleware('farmer')->prefix('pest-incidents')->group(function () {
        Route::get('/', [\App\Http\Controllers\Farm\PestIncidentController::class, 'index']);
        Route::get('/options', [\App\Http\Controllers\Farm\PestIncidentController::class, 'options']);
        Route::post('/', [\App\Http\Controllers\Farm\PestIncidentController::class, 'store']);
        Route::get('/{pestIncident}', [\App\Http\Controllers\Farm\PestIncidentController::class, 'show']);
        Route::put('/{pestIncident}', [\App\Http\Controllers\Farm\PestIncidentController::class, 'update']);
        Route::delete('/{pestIncident}', [\App\Http\Controllers\Farm\PestIncidentController::class, 'destroy']);
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
        Route::get('/colorfulclouds', [\App\Http\Controllers\Weather\WeatherProxyController::class, 'getColorfulCloudsWeather']);

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

    // Seed Planting (Nursery) routes
    Route::middleware('farmer')->prefix('seed-plantings')->group(function () {
        Route::get('/', [\App\Http\Controllers\SeedPlantingController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\SeedPlantingController::class, 'store']);
        Route::get('/ready', [\App\Http\Controllers\SeedPlantingController::class, 'getReady']);
        Route::get('/{seedPlanting}', [\App\Http\Controllers\SeedPlantingController::class, 'show']);
        Route::put('/{seedPlanting}', [\App\Http\Controllers\SeedPlantingController::class, 'update']);
        Route::delete('/{seedPlanting}', [\App\Http\Controllers\SeedPlantingController::class, 'destroy']);
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
        Route::post('/{laborer}/photo', [\App\Http\Controllers\Labor\LaborerController::class, 'uploadPhoto']);
        Route::delete('/{laborer}/photo', [\App\Http\Controllers\Labor\LaborerController::class, 'deletePhoto']);
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

        // Product reviews (public)
        Route::get('/products/{product}/reviews', [\App\Http\Controllers\MarketPlace\ProductReviewController::class, 'index']);

        // Product management (farmers only)
        Route::middleware('farmer')->group(function () {
            Route::post('/products', [\App\Http\Controllers\RiceMarketplaceController::class, 'createProduct']);
            Route::put('/products/{product}', [\App\Http\Controllers\RiceMarketplaceController::class, 'updateProduct']);
            Route::delete('/products/{product}', [\App\Http\Controllers\RiceMarketplaceController::class, 'deleteProduct']);

            // Farmer order statistics
            Route::get('/farmer/order-stats', [\App\Http\Controllers\RiceMarketplaceController::class, 'getFarmerOrderStats']);

            // Product image management
            Route::post('/products/images/upload', [\App\Http\Controllers\MarketPlace\ProductImageController::class, 'upload']);
            Route::post('/products/images/delete', [\App\Http\Controllers\MarketPlace\ProductImageController::class, 'delete']);
        });

        // Order management - General
        Route::get('/orders/{order}', [\App\Http\Controllers\MarketPlace\RiceOrderController::class, 'show']);

        // Buyer order routes
        Route::middleware('buyer')->group(function () {
            Route::get('/buyer/orders', [\App\Http\Controllers\MarketPlace\RiceOrderController::class, 'buyerOrders']);
            Route::post('/orders', [\App\Http\Controllers\MarketPlace\RiceOrderController::class, 'store']);
            Route::post('/orders/{order}/deliver', [\App\Http\Controllers\MarketPlace\RiceOrderController::class, 'confirmDelivery']);
            Route::post('/orders/{order}/dispute', [\App\Http\Controllers\MarketPlace\RiceOrderController::class, 'dispute']);
            Route::post('/orders/{order}/cancel', [\App\Http\Controllers\MarketPlace\RiceOrderController::class, 'cancel']);

            // Review routes
            Route::post('/reviews', [\App\Http\Controllers\MarketPlace\ProductReviewController::class, 'store']);
            Route::get('/reviews/my', [\App\Http\Controllers\MarketPlace\ProductReviewController::class, 'myReviews']);
            Route::get('/orders/{order}/can-review', [\App\Http\Controllers\MarketPlace\ProductReviewController::class, 'canReview']);

            // Cart routes
            Route::get('/cart', [\App\Http\Controllers\MarketPlace\CartController::class, 'index']);
            Route::get('/cart/count', [\App\Http\Controllers\MarketPlace\CartController::class, 'count']);
            Route::post('/cart', [\App\Http\Controllers\MarketPlace\CartController::class, 'addItem']);
            Route::put('/cart/{cartItem}', [\App\Http\Controllers\MarketPlace\CartController::class, 'updateItem']);
            Route::delete('/cart/{cartItem}', [\App\Http\Controllers\MarketPlace\CartController::class, 'removeItem']);
            Route::delete('/cart', [\App\Http\Controllers\MarketPlace\CartController::class, 'clear']);
            Route::post('/cart/checkout', [\App\Http\Controllers\MarketPlace\CartController::class, 'checkout']);

            // Favorites routes
            Route::get('/favorites', [\App\Http\Controllers\MarketPlace\FavoriteController::class, 'index']);
            Route::post('/favorites', [\App\Http\Controllers\MarketPlace\FavoriteController::class, 'store']);
            Route::post('/favorites/toggle', [\App\Http\Controllers\MarketPlace\FavoriteController::class, 'toggle']);
            Route::get('/favorites/check/{productId}', [\App\Http\Controllers\MarketPlace\FavoriteController::class, 'check']);
            Route::delete('/favorites/{favorite}', [\App\Http\Controllers\MarketPlace\FavoriteController::class, 'destroy']);
        });


        // Farmer order routes
        Route::middleware('farmer')->group(function () {
            Route::get('/farmer/orders', [\App\Http\Controllers\MarketPlace\RiceOrderController::class, 'farmerOrders']);
            Route::post('/orders/{order}/accept', [\App\Http\Controllers\MarketPlace\RiceOrderController::class, 'accept']);
            Route::post('/orders/{order}/reject', [\App\Http\Controllers\MarketPlace\RiceOrderController::class, 'reject']);
            Route::post('/orders/{order}/ship', [\App\Http\Controllers\MarketPlace\RiceOrderController::class, 'ship']);
            Route::post('/orders/{order}/resolve', [\App\Http\Controllers\MarketPlace\RiceOrderController::class, 'resolveDispute']);
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
        Route::get('/crop-yield/filter-options', [\App\Http\Controllers\Reports\ReportController::class, 'getCropYieldFilterOptions']);
        Route::get('/weather', [\App\Http\Controllers\Reports\ReportController::class, 'getWeatherReport']);
        Route::get('/labor-cost', [\App\Http\Controllers\Labor\WageController::class, 'index']);
        Route::get('/weather-analysis', [\App\Http\Controllers\Weather\WeatherController::class, 'dashboard']);
        Route::get('/inventory-usage', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'index']);

        // CSV Export routes
        Route::get('/export/expenses', [\App\Http\Controllers\Reports\ReportController::class, 'exportExpensesCsv']);
        Route::get('/export/inventory', [\App\Http\Controllers\Reports\ReportController::class, 'exportInventoryCsv']);
        Route::get('/export/sales', [\App\Http\Controllers\Reports\ReportController::class, 'exportSalesCsv']);

        // Profit/Loss routes
        Route::get('/profit-loss', [\App\Http\Controllers\Reports\ProfitLossController::class, 'summary']);
        Route::get('/profit-loss/by-planting', [\App\Http\Controllers\Reports\ProfitLossController::class, 'byPlanting']);
    });



});