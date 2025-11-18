<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Sale;
use App\Models\Expense;
use App\Models\RiceOrder;
use App\Models\InventoryItem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AdminReportsController extends Controller
{
    /**
     * Get sales trends report (FR-B.2)
     */
    public function getSalesTrends(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->subMonths(12)->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());

        $sales = Sale::whereBetween('sale_date', [$startDate, $endDate])
            ->with(['harvest.planting.field.user'])
            ->orderBy('sale_date', 'desc')
            ->get();

        // Group by month
        $monthlyData = $sales->groupBy(function($sale) {
            return Carbon::parse($sale->sale_date)->format('Y-m');
        })->map(function($monthSales) {
            return [
                'count' => $monthSales->count(),
                'total_amount' => $monthSales->sum('total_amount'),
                'average_amount' => $monthSales->avg('total_amount'),
            ];
        });

        // Group by farmer
        $farmerData = $sales->groupBy('user_id')->map(function($farmerSales, $userId) {
            $user = User::find($userId);
            return [
                'farmer_id' => $userId,
                'farmer_name' => $user?->name,
                'count' => $farmerSales->count(),
                'total_amount' => $farmerSales->sum('total_amount'),
            ];
        })->values();

        return response()->json([
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'summary' => [
                'total_sales' => $sales->count(),
                'total_amount' => $sales->sum('total_amount'),
                'average_amount' => $sales->avg('total_amount'),
            ],
            'monthly_breakdown' => $monthlyData,
            'farmer_breakdown' => $farmerData,
            'sales' => $sales,
        ]);
    }

    /**
     * Get expense summary report (FR-B.2)
     */
    public function getExpenseSummary(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->subMonths(12)->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());

        $expenses = Expense::whereBetween('date', [$startDate, $endDate])
            ->with(['planting.field.user'])
            ->orderBy('date', 'desc')
            ->get();

        // Group by category
        $categoryData = $expenses->groupBy('category')->map(function($categoryExpenses, $category) {
            return [
                'category' => $category,
                'count' => $categoryExpenses->count(),
                'total_amount' => $categoryExpenses->sum('amount'),
                'average_amount' => $categoryExpenses->avg('amount'),
            ];
        })->values();

        // Group by month
        $monthlyData = $expenses->groupBy(function($expense) {
            return Carbon::parse($expense->date)->format('Y-m');
        })->map(function($monthExpenses) {
            return [
                'count' => $monthExpenses->count(),
                'total_amount' => $monthExpenses->sum('amount'),
            ];
        });

        // Group by farmer
        $farmerData = $expenses->groupBy('user_id')->map(function($farmerExpenses, $userId) {
            $user = User::find($userId);
            return [
                'farmer_id' => $userId,
                'farmer_name' => $user?->name,
                'count' => $farmerExpenses->count(),
                'total_amount' => $farmerExpenses->sum('amount'),
            ];
        })->values();

        return response()->json([
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'summary' => [
                'total_expenses' => $expenses->count(),
                'total_amount' => $expenses->sum('amount'),
                'average_amount' => $expenses->avg('amount'),
            ],
            'category_breakdown' => $categoryData,
            'monthly_breakdown' => $monthlyData,
            'farmer_breakdown' => $farmerData,
            'expenses' => $expenses,
        ]);
    }

    /**
     * Get system-wide inventory usage report (FR-B.2)
     */
    public function getInventoryUsage(Request $request): JsonResponse
    {
        $inventoryItems = InventoryItem::with(['user'])
            ->get();

        // Group by category
        $categoryData = $inventoryItems->groupBy('category')->map(function($categoryItems, $category) {
            return [
                'category' => $category,
                'total_items' => $categoryItems->count(),
                'total_quantity' => $categoryItems->sum('quantity'),
                'total_value' => $categoryItems->sum(function($item) {
                    return $item->quantity * ($item->unit_price ?? 0);
                }),
            ];
        })->values();

        // Low stock items
        $lowStockItems = $inventoryItems->filter(function($item) {
            return $item->quantity <= $item->min_stock;
        })->values();

        // Group by farmer
        $farmerData = $inventoryItems->groupBy('user_id')->map(function($farmerItems, $userId) {
            $user = User::find($userId);
            return [
                'farmer_id' => $userId,
                'farmer_name' => $user?->name,
                'total_items' => $farmerItems->count(),
                'total_quantity' => $farmerItems->sum('quantity'),
            ];
        })->values();

        return response()->json([
            'summary' => [
                'total_items' => $inventoryItems->count(),
                'total_quantity' => $inventoryItems->sum('quantity'),
                'low_stock_count' => $lowStockItems->count(),
            ],
            'category_breakdown' => $categoryData,
            'farmer_breakdown' => $farmerData,
            'low_stock_items' => $lowStockItems,
            'all_items' => $inventoryItems,
        ]);
    }

    /**
     * Get financial audit report (FR-B.3)
     */
    public function getFinancialAudit(Request $request): JsonResponse
    {
        $startDate = $request->get('start_date', Carbon::now()->subMonths(12)->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());

        $sales = Sale::whereBetween('sale_date', [$startDate, $endDate])->get();
        $expenses = Expense::whereBetween('date', [$startDate, $endDate])->get();
        $orders = RiceOrder::whereBetween('created_at', [$startDate, $endDate])->get();

        return response()->json([
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'sales' => [
                'total_transactions' => $sales->count(),
                'total_amount' => $sales->sum('total_amount'),
                'transactions' => $sales,
            ],
            'expenses' => [
                'total_transactions' => $expenses->count(),
                'total_amount' => $expenses->sum('amount'),
                'transactions' => $expenses,
            ],
            'orders' => [
                'total_transactions' => $orders->count(),
                'total_amount' => $orders->where('status', 'delivered')->sum('total_amount'),
                'transactions' => $orders,
            ],
            'net_profit' => $sales->sum('total_amount') + $orders->where('status', 'delivered')->sum('total_amount') - $expenses->sum('amount'),
        ]);
    }
}

