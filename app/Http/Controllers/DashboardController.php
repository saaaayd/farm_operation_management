<?php

namespace App\Http\Controllers;

use App\Models\Field;
use App\Models\Planting;
use App\Models\Task;
use App\Models\Order;
use App\Models\InventoryItem;
use App\Models\WeatherLog;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SeedPlanting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get farmer dashboard data
     */
    public function farmerDashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isFarmer()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $fields = Field::where('user_id', $user->id)->get();
        $fieldIds = $fields->pluck('id');

        // Get plantings for user's fields
        $plantings = Planting::whereIn('field_id', $fieldIds)->get();
        $plantingIds = $plantings->pluck('id');

        // Dashboard stats
        $stats = [
            'total_fields' => $fields->count(),
            'active_plantings' => $plantings->where('status', '!=', 'harvested')->count(),
            'active_seed_plantings' => SeedPlanting::where('user_id', $user->id)
                ->whereIn('status', ['sown', 'germinating', 'ready'])
                ->count(),
            'ready_seed_plantings' => SeedPlanting::where('user_id', $user->id)
                ->where('status', 'ready')
                ->count(),
            'pending_tasks' => Task::whereIn('planting_id', $plantingIds)
                ->where('status', Task::STATUS_PENDING)
                ->count(),
            'overdue_tasks' => Task::whereIn('planting_id', $plantingIds)
                ->where('status', Task::STATUS_PENDING)
                ->where('due_date', '<', now())
                ->count(),
            'low_stock_items' => InventoryItem::where('user_id', $user->id)
                ->whereColumn('current_stock', '<=', 'minimum_stock')
                ->count(),
        ];

        // Recent activities
        $recentTasks = Task::whereIn('planting_id', $plantingIds)
            ->with(['planting.field', 'laborer'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $upcomingTasks = Task::whereIn('planting_id', $plantingIds)
            ->where('status', Task::STATUS_PENDING)
            ->where('due_date', '>=', now())
            ->with(['planting.field', 'laborer'])
            ->orderBy('due_date', 'asc')
            ->limit(5)
            ->get();

        // Efficiently fetch latest weather for all fields in one query
        $fields->load([
            'weatherLogs' => function ($query) {
                $query->latest('recorded_at')->take(1);
            }
        ]);

        $weatherData = $fields->map(function ($field) {
            $latestWeather = $field->weatherLogs->first();
            if ($latestWeather) {
                return [
                    'field' => $field,
                    'weather' => $latestWeather
                ];
            }
            return null;
        })->filter()->values();

        // Monthly expenses
        $monthlyExpenses = Expense::whereIn('planting_id', $plantingIds)
            ->whereMonth('date', now()->month)
            ->whereYear('date', now()->year)
            ->sum('amount');

        // Monthly sales
        $monthlySales = Sale::whereHas('harvest.planting.field', function ($query) use ($user) {
            $query->where('user_id', $user->id);
        })
            ->whereMonth('sale_date', now()->month)
            ->whereYear('sale_date', now()->year)
            ->sum('total_amount');

        return response()->json([
            'stats' => $stats,
            'recent_tasks' => $recentTasks,
            'upcoming_tasks' => $upcomingTasks,
            'weather_data' => $weatherData,
            'monthly_expenses' => $monthlyExpenses,
            'monthly_sales' => $monthlySales,
        ]);
    }

    /**
     * Get buyer dashboard data
     */
    public function buyerDashboard(Request $request): JsonResponse
    {
        $user = $request->user();

        if (!$user->isUser()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        // Dashboard stats
        $stats = [
            'total_orders' => Order::where('buyer_id', $user->id)->count(),
            'pending_orders' => Order::where('buyer_id', $user->id)
                ->where('status', Order::STATUS_PENDING)
                ->count(),
            'completed_orders' => Order::where('buyer_id', $user->id)
                ->where('status', Order::STATUS_DELIVERED)
                ->count(),
            'total_spent' => Order::where('buyer_id', $user->id)
                ->where('status', Order::STATUS_DELIVERED)
                ->sum('total_amount'),
        ];

        // Recent orders
        $recentOrders = Order::where('buyer_id', $user->id)
            ->with('orderItems.inventoryItem')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Available products
        $availableProducts = InventoryItem::where('category', InventoryItem::CATEGORY_PRODUCE)
            ->where('current_stock', '>', 0)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return response()->json([
            'stats' => $stats,
            'recent_orders' => $recentOrders,
            'available_products' => $availableProducts,
        ]);
    }

    /**
     * Get dashboard data based on user role
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        return match ($user->role) {
            'farmer' => $this->farmerDashboard($request),
            'buyer' => $this->buyerDashboard($request),
            default => response()->json(['message' => 'Invalid user role'], 400)
        };
    }
}