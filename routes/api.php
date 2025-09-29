<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Weather\WeatherController;

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
    // Authentication routes
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'user']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);
    Route::put('/change-password', [AuthController::class, 'changePassword']);

    // Dashboard routes
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // Weather routes
    Route::prefix('weather')->group(function () {
        Route::get('/dashboard', [WeatherController::class, 'dashboard']);
        Route::post('/update-all', [WeatherController::class, 'updateAllWeather']);
        
        Route::prefix('fields/{field}')->group(function () {
            Route::get('/current', [WeatherController::class, 'getCurrentWeather']);
            Route::get('/forecast', [WeatherController::class, 'getForecast']);
            Route::get('/history', [WeatherController::class, 'getHistory']);
            Route::get('/alerts', [WeatherController::class, 'getAlerts']);
            Route::post('/update', [WeatherController::class, 'updateWeather']);
        });
    });

    // Field management routes
    Route::prefix('fields')->group(function () {
        Route::get('/', [\App\Http\Controllers\Farm\FieldController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Farm\FieldController::class, 'store']);
        Route::get('/{field}', [\App\Http\Controllers\Farm\FieldController::class, 'show']);
        Route::put('/{field}', [\App\Http\Controllers\Farm\FieldController::class, 'update']);
        Route::delete('/{field}', [\App\Http\Controllers\Farm\FieldController::class, 'destroy']);
    });

    // Planting management routes
    Route::prefix('plantings')->group(function () {
        Route::get('/', [\App\Http\Controllers\Farm\PlantingController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Farm\PlantingController::class, 'store']);
        Route::get('/{planting}', [\App\Http\Controllers\Farm\PlantingController::class, 'show']);
        Route::put('/{planting}', [\App\Http\Controllers\Farm\PlantingController::class, 'update']);
        Route::delete('/{planting}', [\App\Http\Controllers\Farm\PlantingController::class, 'destroy']);
    });

    // Task management routes
    Route::prefix('tasks')->group(function () {
        Route::get('/', [\App\Http\Controllers\Labor\TaskController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Labor\TaskController::class, 'store']);
        Route::get('/{task}', [\App\Http\Controllers\Labor\TaskController::class, 'show']);
        Route::put('/{task}', [\App\Http\Controllers\Labor\TaskController::class, 'update']);
        Route::delete('/{task}', [\App\Http\Controllers\Labor\TaskController::class, 'destroy']);
        Route::post('/{task}/complete', [\App\Http\Controllers\Labor\TaskController::class, 'markCompleted']);
    });

    // Harvest management routes
    Route::prefix('harvests')->group(function () {
        Route::get('/', [\App\Http\Controllers\Farm\HarvestController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Farm\HarvestController::class, 'store']);
        Route::get('/{harvest}', [\App\Http\Controllers\Farm\HarvestController::class, 'show']);
        Route::put('/{harvest}', [\App\Http\Controllers\Farm\HarvestController::class, 'update']);
        Route::delete('/{harvest}', [\App\Http\Controllers\Farm\HarvestController::class, 'destroy']);
    });

    // Labor management routes
    Route::prefix('laborers')->group(function () {
        Route::get('/', [\App\Http\Controllers\Labor\LaborerController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Labor\LaborerController::class, 'store']);
        Route::get('/{laborer}', [\App\Http\Controllers\Labor\LaborerController::class, 'show']);
        Route::put('/{laborer}', [\App\Http\Controllers\Labor\LaborerController::class, 'update']);
        Route::delete('/{laborer}', [\App\Http\Controllers\Labor\LaborerController::class, 'destroy']);
    });

    Route::prefix('labor-wages')->group(function () {
        Route::get('/', [\App\Http\Controllers\Labor\WageController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Labor\WageController::class, 'store']);
        Route::get('/{laborWage}', [\App\Http\Controllers\Labor\WageController::class, 'show']);
        Route::put('/{laborWage}', [\App\Http\Controllers\Labor\WageController::class, 'update']);
        Route::delete('/{laborWage}', [\App\Http\Controllers\Labor\WageController::class, 'destroy']);
    });

    // Inventory management routes
    Route::prefix('inventory')->group(function () {
        Route::get('/', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'store']);
        Route::get('/{item}', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'show']);
        Route::put('/{item}', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'update']);
        Route::delete('/{item}', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'destroy']);
        Route::post('/{item}/add-stock', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'addStock']);
        Route::post('/{item}/remove-stock', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'removeStock']);
        Route::get('/alerts/low-stock', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'lowStockAlerts']);
    });

    // Marketplace routes
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
    Route::prefix('orders')->group(function () {
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
    Route::prefix('sales')->group(function () {
        Route::get('/', [\App\Http\Controllers\MarketPlace\SaleController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\MarketPlace\SaleController::class, 'store']);
        Route::get('/{sale}', [\App\Http\Controllers\MarketPlace\SaleController::class, 'show']);
        Route::put('/{sale}', [\App\Http\Controllers\MarketPlace\SaleController::class, 'update']);
        Route::delete('/{sale}', [\App\Http\Controllers\MarketPlace\SaleController::class, 'destroy']);
    });

    // Financial management routes
    Route::prefix('expenses')->group(function () {
        Route::get('/', [\App\Http\Controllers\Financial\ExpenseController::class, 'index']);
        Route::post('/', [\App\Http\Controllers\Financial\ExpenseController::class, 'store']);
        Route::get('/{expense}', [\App\Http\Controllers\Financial\ExpenseController::class, 'show']);
        Route::put('/{expense}', [\App\Http\Controllers\Financial\ExpenseController::class, 'update']);
        Route::delete('/{expense}', [\App\Http\Controllers\Financial\ExpenseController::class, 'destroy']);
    });

    // Reports routes (simplified)
    Route::prefix('reports')->group(function () {
        Route::get('/financial', [\App\Http\Controllers\Financial\ExpenseController::class, 'index']);
        Route::get('/crop-yield', [\App\Http\Controllers\Farm\HarvestController::class, 'index']);
        Route::get('/labor-cost', [\App\Http\Controllers\Labor\WageController::class, 'index']);
        Route::get('/weather-analysis', [\App\Http\Controllers\Weather\WeatherController::class, 'dashboard']);
        Route::get('/inventory-usage', [\App\Http\Controllers\Inventory\InventoryItemController::class, 'index']);
    });

    // Admin routes
    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/users', [\App\Http\Controllers\Admin\UserManagementController::class, 'index']);
        Route::get('/users/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'show']);
        Route::put('/users/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'update']);
        Route::delete('/users/{user}', [\App\Http\Controllers\Admin\UserManagementController::class, 'destroy']);
        Route::get('/system-stats', [\App\Http\Controllers\Admin\SystemSettingsController::class, 'index']);
    });
});