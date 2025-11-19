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

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    // Proxy routes for PSGC API
    Route::get('/locations/provinces', function () {
        $response = Http::get('https://psgc.gitlab.io/api/provinces/');
        return response()->json($response->json());
    });

    Route::get('/locations/provinces/{code}/cities', function ($code) {
        $response = Http::get("https://psgc.gitlab.io/api/provinces/{$code}/cities-municipalities/");
        return response()->json($response->json());
    });

    Route::get('/locations/cities/{code}/barangays', function ($code) {
        $response = Http::get("https://psgc.gitlab.io/api/cities-municipalities/{$code}/barangays/");
        return response()->json($response->json());
    });

    // Geocoding proxy for Nominatim (to avoid CORS and set User-Agent)
    Route::get('/geocode', function (Request $request) {
        $query = $request->query('q');
        if (!$query) {
            return response()->json(['error' => 'Query parameter "q" is required'], 400);
        }

        $response = Http::withHeaders([
            'User-Agent' => 'RiceFARM Application (https://ricefarm.app)',
        ])->get('https://nominatim.openstreetmap.org/search', [
            'q' => $query,
            'format' => 'json',
            'limit' => 1,
            'countrycodes' => 'ph',
        ]);

        return response()->json($response->json());
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
    Route::middleware('farmer')->prefix('laborers')->group(function () {
        Route::get('/', [\App\Http\Controllers\Labor\LaborerController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Labor\LaborerController::class, 'store']);
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

    // Reports routes (simplified)
    Route::middleware('farmer')->prefix('reports')->group(function () {
        Route::get('/financial', [\App\Http\Controllers\Financial\ExpenseController::class, 'index']);
        Route::get('/crop-yield', [\App\Http\Controllers\Farm\HarvestController::class, 'index']);
        Route::get('/labor-cost', [\App\Http\Controllers\Labor\WageController::class, 'index']);
        Route::get('/weather-analysis', [\App\Http\Controllers\Weather\WeatherController::class, 'dashboard']);
        Route::get('/inventory-usage', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'index']);
    });

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        // User Management
        Route::get('/users', [\App\Http\Controllers\Admin\UserManagementController::class, 'index']);
        Route::post('/users', [\App\Http\Controllers\Admin\UserManagementController::class, 'store']);
        Route::get('/users/by-role/{role}', [\App\Http\Controllers\Admin\UserManagementController::class, 'getByRole']);
        Route::get('/users/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'show']);
        Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'update']);
        Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'destroy']);

        // User Approval (FR-A.2)
        Route::get('/user-approvals/pending', [\App\Http\Controllers\Admin\UserApprovalController::class, 'getPendingUsers']);
        Route::post('/user-approvals/{user}/approve', [\App\Http\Controllers\Admin\UserApprovalController::class, 'approveUser']);
        Route::post('/user-approvals/{user}/reject', [\App\Http\Controllers\Admin\UserApprovalController::class, 'rejectUser']);
        Route::get('/user-approvals/stats', [\App\Http\Controllers\Admin\UserApprovalController::class, 'getApprovalStats']);

        // Product Approval (FR-C.2)
        Route::get('/product-approvals/pending', [\App\Http\Controllers\Admin\ProductApprovalController::class, 'getPendingProducts']);
        Route::get('/product-approvals/all', [\App\Http\Controllers\Admin\ProductApprovalController::class, 'getAllProducts']);
        Route::post('/product-approvals/{product}/approve', [\App\Http\Controllers\Admin\ProductApprovalController::class, 'approveProduct']);
        Route::post('/product-approvals/{product}/reject', [\App\Http\Controllers\Admin\ProductApprovalController::class, 'rejectProduct']);
        Route::get('/product-approvals/stats', [\App\Http\Controllers\Admin\ProductApprovalController::class, 'getApprovalStats']);

        // Enhanced Dashboard (FR-B.1)
        Route::get('/dashboard', [\App\Http\Controllers\Admin\AdminDashboardController::class, 'index']);

        // Reports & Analytics (FR-B.2, FR-B.3)
        Route::get('/reports/sales-trends', [\App\Http\Controllers\Admin\AdminReportsController::class, 'getSalesTrends']);
        Route::get('/reports/expense-summary', [\App\Http\Controllers\Admin\AdminReportsController::class, 'getExpenseSummary']);
        Route::get('/reports/inventory-usage', [\App\Http\Controllers\Admin\AdminReportsController::class, 'getInventoryUsage']);
        Route::get('/reports/financial-audit', [\App\Http\Controllers\Admin\AdminReportsController::class, 'getFinancialAudit']);

        // Activity Logs & Audit Trails (FR-B.4)
        Route::get('/activity-logs', [\App\Http\Controllers\Admin\ActivityLogController::class, 'index']);
        Route::get('/activity-logs/stats', [\App\Http\Controllers\Admin\ActivityLogController::class, 'getStats']);
        Route::get('/activity-logs/audit-trail/{modelType}/{modelId}', [\App\Http\Controllers\Admin\ActivityLogController::class, 'getAuditTrail']);

        // Laborer Management (FR-A.4)
        Route::get('/laborers', [\App\Http\Controllers\Admin\LaborerManagementController::class, 'index']);
        Route::post('/laborers', [\App\Http\Controllers\Admin\LaborerManagementController::class, 'store']);
        Route::get('/laborers/{laborer}', [\App\Http\Controllers\Admin\LaborerManagementController::class, 'show']);
        Route::put('/laborers/{laborer}', [\App\Http\Controllers\Admin\LaborerManagementController::class, 'update']);
        Route::delete('/laborers/{laborer}', [\App\Http\Controllers\Admin\LaborerManagementController::class, 'destroy']);

        // Message Moderation (FR-C.3)
        Route::get('/messages', [\App\Http\Controllers\Admin\MessageModerationController::class, 'getAllMessages']);
        Route::get('/messages/orders/{order}', [\App\Http\Controllers\Admin\MessageModerationController::class, 'getOrderMessages']);
        Route::delete('/messages/{message}', [\App\Http\Controllers\Admin\MessageModerationController::class, 'deleteMessage']);
        Route::get('/messages/conversations', [\App\Http\Controllers\Admin\MessageModerationController::class, 'getActiveConversations']);

        // System Settings
        Route::get('/system-stats', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'index']);
    });
});