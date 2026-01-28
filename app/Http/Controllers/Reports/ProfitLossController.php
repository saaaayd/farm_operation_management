<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\Harvest;
use App\Models\Planting;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProfitLossController extends Controller
{
    /**
     * Get overall profit/loss summary
     */
    public function summary(Request $request): JsonResponse
    {
        $user = Auth::user();
        $startDate = $request->start_date ? Carbon::parse($request->start_date) : now()->subYear();
        $endDate = $request->end_date ? Carbon::parse($request->end_date) : now();

        // Get total revenue from sales
        $totalRevenue = Sale::whereHas('harvest.planting.field', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->sum('total_amount');

        // Get total expenses
        $totalExpenses = Expense::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->sum('amount');

        $netProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        // Get expense breakdown by category
        $expensesByCategory = Expense::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->selectRaw('category, SUM(amount) as total')
            ->groupBy('category')
            ->pluck('total', 'category');

        // Monthly trend
        $monthlyData = $this->getMonthlyTrend($user->id, $startDate, $endDate);

        return response()->json([
            'summary' => [
                'total_revenue' => round($totalRevenue, 2),
                'total_expenses' => round($totalExpenses, 2),
                'net_profit' => round($netProfit, 2),
                'profit_margin' => round($profitMargin, 1),
                'is_profitable' => $netProfit > 0,
            ],
            'expenses_by_category' => $expensesByCategory,
            'monthly_trend' => $monthlyData,
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Get profit/loss by planting cycle
     */
    public function byPlanting(Request $request): JsonResponse
    {
        $user = Auth::user();

        $plantings = Planting::whereHas('field', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->with(['field', 'riceVariety', 'harvests.sales', 'expenses'])
            ->orderBy('planting_date', 'desc')
            ->get()
            ->map(function ($planting) {
                // Calculate revenue from all harvests
                $revenue = $planting->harvests->sum(function ($harvest) {
                    return $harvest->sales->sum('total_amount');
                });

                // Calculate expenses for this planting
                $expenses = $planting->expenses->sum('amount');

                $netProfit = $revenue - $expenses;
                $roi = $expenses > 0 ? (($revenue - $expenses) / $expenses) * 100 : 0;

                return [
                    'id' => $planting->id,
                    'field_name' => $planting->field->name ?? 'N/A',
                    'variety' => $planting->riceVariety->name ?? 'N/A',
                    'planting_date' => $planting->planting_date?->format('Y-m-d'),
                    'status' => $planting->status,
                    'area_hectares' => $planting->area_hectares,
                    'revenue' => round($revenue, 2),
                    'expenses' => round($expenses, 2),
                    'net_profit' => round($netProfit, 2),
                    'roi' => round($roi, 1),
                    'profit_per_hectare' => $planting->area_hectares > 0
                        ? round($netProfit / $planting->area_hectares, 2)
                        : 0,
                ];
            });

        // Summary stats
        $totals = [
            'total_revenue' => $plantings->sum('revenue'),
            'total_expenses' => $plantings->sum('expenses'),
            'total_net_profit' => $plantings->sum('net_profit'),
            'average_roi' => $plantings->count() > 0 ? $plantings->avg('roi') : 0,
        ];

        return response()->json([
            'plantings' => $plantings,
            'totals' => $totals,
        ]);
    }

    /**
     * Get monthly revenue and expense trend
     */
    private function getMonthlyTrend($userId, $startDate, $endDate): array
    {
        $months = [];
        $current = $startDate->copy()->startOfMonth();

        while ($current <= $endDate) {
            $monthStart = $current->copy()->startOfMonth();
            $monthEnd = $current->copy()->endOfMonth();

            $revenue = Sale::whereHas('harvest.planting.field', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })
                ->whereBetween('sale_date', [$monthStart, $monthEnd])
                ->sum('total_amount');

            $expenses = Expense::where('user_id', $userId)
                ->whereBetween('date', [$monthStart, $monthEnd])
                ->sum('amount');

            $months[] = [
                'month' => $current->format('M Y'),
                'revenue' => round($revenue, 2),
                'expenses' => round($expenses, 2),
                'profit' => round($revenue - $expenses, 2),
            ];

            $current->addMonth();
        }

        return $months;
    }
}
