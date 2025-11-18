<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Field;
use App\Models\Planting;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\RiceOrder;
use App\Models\RiceProduct;
use App\Models\InventoryItem;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AdminDashboardController extends Controller
{
    /**
     * Get comprehensive admin dashboard data (FR-B.1)
     */
    public function index(Request $request): JsonResponse
    {
        // High-level system metrics
        $stats = [
            // User metrics
            'total_users' => User::count(),
            'active_users' => User::where('approval_status', 'approved')
                ->where('role', '!=', 'admin')
                ->count(),
            'pending_users' => User::where('approval_status', 'pending')
                ->where('role', '!=', 'admin')
                ->count(),
            'total_farmers' => User::where('role', 'farmer')
                ->where('approval_status', 'approved')
                ->count(),
            'total_buyers' => User::where('role', 'buyer')
                ->where('approval_status', 'approved')
                ->count(),

            // Farm metrics
            'total_fields' => Field::count(),
            'active_plantings' => Planting::where('status', '!=', 'harvested')->count(),

            // Marketplace metrics
            'total_products' => RiceProduct::count(),
            'approved_products' => RiceProduct::where('approval_status', 'approved')->count(),
            'pending_products' => RiceProduct::where('approval_status', 'pending')->count(),

            // Order metrics
            'total_orders' => RiceOrder::count(),
            'pending_orders' => RiceOrder::where('status', 'pending')->count(),
            'completed_orders' => RiceOrder::where('status', 'delivered')->count(),

            // Financial metrics (FR-B.1)
            'total_sales_volume' => Sale::sum('total_amount'),
            'total_expenses' => Expense::sum('amount'),
            'monthly_sales' => Sale::whereMonth('sale_date', now()->month)
                ->whereYear('sale_date', now()->year)
                ->sum('total_amount'),
            'monthly_expenses' => Expense::whereMonth('date', now()->month)
                ->whereYear('date', now()->year)
                ->sum('amount'),
            'total_revenue' => RiceOrder::where('status', 'delivered')
                ->sum('total_amount'),

            // Inventory metrics
            'total_inventory_items' => InventoryItem::count(),
            'low_stock_items' => InventoryItem::whereRaw('quantity <= min_stock')->count(),
        ];

        // Recent activities
        $recentUsers = User::where('role', '!=', 'admin')
            ->with('approver')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentOrders = RiceOrder::with(['buyer', 'riceProduct.farmer'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentProducts = RiceProduct::with(['farmer', 'riceVariety'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // User growth data (last 30 days)
        $userGrowth = $this->getUserGrowthData(30);

        // Sales trends (last 12 months)
        $salesTrends = $this->getSalesTrends(12);

        // Expense trends (last 12 months)
        $expenseTrends = $this->getExpenseTrends(12);

        return response()->json([
            'stats' => $stats,
            'recent_users' => $recentUsers,
            'recent_orders' => $recentOrders,
            'recent_products' => $recentProducts,
            'user_growth' => $userGrowth,
            'sales_trends' => $salesTrends,
            'expense_trends' => $expenseTrends,
        ]);
    }

    /**
     * Get user growth data
     */
    private function getUserGrowthData(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days)->startOfDay();
        $endDate = Carbon::now()->endOfDay();

        $baselineUsers = User::where('created_at', '<', $startDate)
            ->where('role', '!=', 'admin')
            ->count();

        $dailyCounts = User::selectRaw('DATE(created_at)::text as date, COUNT(*) as count')
            ->where('created_at', '>=', $startDate)
            ->where('created_at', '<=', $endDate)
            ->where('role', '!=', 'admin')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('date', 'asc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->date => (int) $item->count];
            });

        $labels = [];
        $data = [];
        $cumulative = $baselineUsers;

        for ($date = $startDate->copy(); $date <= $endDate; $date->addDay()) {
            $dateKey = $date->format('Y-m-d');
            $labels[] = $date->format('M d');
            
            $count = $dailyCounts->get($dateKey, 0);
            $cumulative += $count;
            $data[] = $cumulative;
        }

        return [
            'labels' => $labels,
            'daily_registrations' => $dailyCounts->toArray(),
            'cumulative_users' => $data,
            'total_new_users' => $dailyCounts->sum(),
            'baseline_users' => $baselineUsers,
        ];
    }

    /**
     * Get sales trends
     */
    private function getSalesTrends(int $months = 12): array
    {
        $startDate = Carbon::now()->subMonths($months)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $monthlySales = Sale::selectRaw('EXTRACT(YEAR FROM sale_date)::integer as year, EXTRACT(MONTH FROM sale_date)::integer as month, SUM(total_amount) as total')
            ->where('sale_date', '>=', $startDate)
            ->where('sale_date', '<=', $endDate)
            ->groupByRaw('EXTRACT(YEAR FROM sale_date), EXTRACT(MONTH FROM sale_date)')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $labels = [];
        $data = [];

        for ($date = $startDate->copy(); $date <= $endDate; $date->addMonth()) {
            $labels[] = $date->format('M Y');
            $monthData = $monthlySales->firstWhere(function($item) use ($date) {
                return $item->year == $date->year && $item->month == $date->month;
            });
            $data[] = $monthData ? (float) $monthData->total : 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * Get expense trends
     */
    private function getExpenseTrends(int $months = 12): array
    {
        $startDate = Carbon::now()->subMonths($months)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $monthlyExpenses = Expense::selectRaw('EXTRACT(YEAR FROM date)::integer as year, EXTRACT(MONTH FROM date)::integer as month, SUM(amount) as total')
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->groupByRaw('EXTRACT(YEAR FROM date), EXTRACT(MONTH FROM date)')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        $labels = [];
        $data = [];

        for ($date = $startDate->copy(); $date <= $endDate; $date->addMonth()) {
            $labels[] = $date->format('M Y');
            $monthData = $monthlyExpenses->firstWhere(function($item) use ($date) {
                return $item->year == $date->year && $item->month == $date->month;
            });
            $data[] = $monthData ? (float) $monthData->total : 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}

