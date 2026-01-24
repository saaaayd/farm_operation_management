<?php

namespace App\Http\Controllers\Analytics;

use App\Http\Controllers\Controller;
use App\Models\Field;
use App\Models\Planting;
use App\Models\PlantingStage;
use App\Models\Task;
use App\Models\Laborer;
use App\Models\InventoryItem;
use App\Models\WeatherLog;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\SeedPlanting;
use App\Models\PestIncident;
use App\Models\RiceOrder;
use App\Models\RiceProduct;
use App\Models\Harvest;
use App\Models\InventoryTransaction;
use App\Services\PestPredictionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DataAnalysisController extends Controller
{
    protected $pestPredictionService;

    public function __construct(PestPredictionService $pestPredictionService)
    {
        $this->pestPredictionService = $pestPredictionService;
    }

    /**
     * Get comprehensive data analysis with action suggestions
     */
    public function getComprehensiveAnalytics(Request $request): JsonResponse
    {
        $user = $request->user();
        $startDate = $request->input('start_date', Carbon::now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));

        $cacheKey = "data_analysis_{$user->id}_{$startDate}_{$endDate}";

        $data = Cache::remember($cacheKey, now()->addMinutes(10), function () use ($user, $startDate, $endDate) {
            $analytics = [
                'weather' => $this->getWeatherAnalytics($user->id, $startDate, $endDate),
                'sales' => $this->getSalesAnalytics($user->id, $startDate, $endDate),
                'expenses' => $this->getExpenseAnalytics($user->id, $startDate, $endDate),
                'fields' => $this->getFieldAnalytics($user->id),
                'nursery' => $this->getNurseryAnalytics($user->id),
                'inventory' => $this->getInventoryAnalytics($user->id, $startDate, $endDate),
                'pests' => $this->getPestAnalytics($user->id, $startDate, $endDate),
                'laborers' => $this->getLaborAnalytics($user->id, $startDate, $endDate),
                'tasks' => $this->getTaskAnalytics($user->id, $startDate, $endDate),
                'financial_forecast' => $this->getFinancialForecast($user->id),
            ];

            // Generate executive summary based on the aggregated data
            $analytics['executive_summary'] = $this->generateExecutiveSummary($analytics);
            $analytics['action_suggestions'] = $this->generateActionSuggestions($user->id);
            $analytics['date_range'] = [
                'start' => $startDate,
                'end' => $endDate,
            ];

            return $analytics;
        });

        return response()->json($data);
    }

    /**
     * Generate a narrative executive summary based on aggregated metrics
     */
    private function generateExecutiveSummary(array $data): array
    {
        $summary = [];
        $tone = 'neutral';

        // 1. Financial Assessment
        $revenue = $data['sales']['total_revenue'] ?? 0;
        $expenses = $data['expenses']['total_expenses'] ?? 0;
        $netProfit = $revenue - $expenses;
        $profitMargin = $revenue > 0 ? ($netProfit / $revenue) * 100 : 0;

        if ($netProfit > 0) {
            $summary[] = "The farm is currently profitable with a net income of ₱" . number_format($netProfit, 2) . ".";
            $tone = 'positive';
        } elseif ($expenses > 0 && $revenue == 0) {
            $summary[] = "The farm is in an investment phase with significant operational costs (₱" . number_format($expenses, 2) . ") and no revenue yet for this period.";
            $tone = 'neutral';
        } else {
            $summary[] = "The farm is operating at a deficit of ₱" . number_format(abs($netProfit), 2) . ".";
            $tone = 'concern';
        }

        // 2. Operational Efficiency
        $completionRate = $data['tasks']['completion_rate'] ?? 0;
        $overdueCount = $data['tasks']['overdue_tasks'] ?? 0;

        if ($overdueCount > 5) {
            $summary[] = "Operational bottlenecks are detected with {$overdueCount} overdue tasks affecting overall efficiency.";
            $tone = $tone === 'positive' ? 'neutral' : 'concern';
        } elseif ($completionRate > 85) {
            $summary[] = "Operations are running smoothly with a high task completion rate of {$completionRate}%.";
        } elseif ($completionRate < 50 && $data['tasks']['total_tasks'] > 0) {
            $summary[] = "Labor efficiency requires attention as only {$completionRate}% of assigned tasks are completed.";
        }

        // 3. Risk Factors
        $activePests = $data['pests']['active_incidents'] ?? 0;
        $weatherCondition = strtolower($data['weather']['condition_distribution'][0] ?? ''); // dominant condition

        if ($activePests > 0) {
            $summary[] = "Immediate attention is required for {$activePests} active pest incidents which may impact yield.";
            $tone = 'concern';
        }

        // 4. Inventory Health
        $lowStock = $data['inventory']['low_stock_count'] ?? 0;
        if ($lowStock > 0) {
            $summary[] = "Supply chain risk is elevated with {$lowStock} items below minimum stock levels.";
        }

        return [
            'text' => implode(' ', $summary),
            'tone' => $tone,
        ];
    }

    /**
     * Weather analytics - temperature trends, rainfall, conditions
     */
    private function getWeatherAnalytics($userId, $startDate, $endDate): array
    {
        $fieldIds = Field::where('user_id', $userId)->pluck('id');

        $weatherLogs = WeatherLog::whereIn('field_id', $fieldIds)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->get();

        if ($weatherLogs->isEmpty()) {
            return [
                'avg_temperature' => null,
                'max_temperature' => null,
                'min_temperature' => null,
                'total_rainfall' => 0,
                'avg_humidity' => null,
                'condition_distribution' => [],
                'daily_trends' => [],
            ];
        }

        $conditionCounts = $weatherLogs->groupBy('weather_condition')
            ->map(fn($group) => $group->count());

        $dailyTrends = $weatherLogs->groupBy(fn($log) => Carbon::parse($log->recorded_at)->format('Y-m-d'))
            ->map(fn($dayLogs) => [
                'date' => $dayLogs->first()->recorded_at,
                'avg_temp' => round($dayLogs->avg('temperature'), 1),
                'rainfall' => $dayLogs->sum('rainfall') ?? 0,
            ])
            ->values()
            ->take(30);

        return [
            'avg_temperature' => round($weatherLogs->avg('temperature'), 1),
            'max_temperature' => round($weatherLogs->max('temperature'), 1),
            'min_temperature' => round($weatherLogs->min('temperature'), 1),
            'total_rainfall' => round($weatherLogs->sum('rainfall') ?? 0, 1),
            'avg_humidity' => round($weatherLogs->avg('humidity'), 1),
            'condition_distribution' => $conditionCounts,
            'daily_trends' => $dailyTrends,
        ];
    }

    /**
     * Sales analytics - revenue, orders, products
     */
    private function getSalesAnalytics($userId, $startDate, $endDate): array
    {
        // Get sales from Sale model
        $fieldIds = Field::where('user_id', $userId)->pluck('id');
        $plantingIds = Planting::whereIn('field_id', $fieldIds)->pluck('id');
        $harvestIds = Harvest::whereIn('planting_id', $plantingIds)->pluck('id');

        $sales = Sale::whereIn('harvest_id', $harvestIds)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->get();

        // Get orders from RiceOrder
        $orders = RiceOrder::whereHas('riceProduct', function ($query) use ($userId) {
            $query->where('farmer_id', $userId);
        })
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalRevenue = $sales->sum('total_amount') + $orders->where('status', 'delivered')->sum('total_amount');

        $monthlyRevenue = $sales->groupBy(fn($s) => Carbon::parse($s->sale_date)->format('Y-m'))
            ->map(fn($group) => $group->sum('total_amount'));

        $orderMonthlyRevenue = $orders->where('status', 'delivered')
            ->groupBy(fn($o) => Carbon::parse($o->created_at)->format('Y-m'))
            ->map(fn($group) => $group->sum('total_amount'));

        // Merge revenues
        foreach ($orderMonthlyRevenue as $month => $amount) {
            $monthlyRevenue[$month] = ($monthlyRevenue[$month] ?? 0) + $amount;
        }

        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $orders->count(),
            'completed_orders' => $orders->where('status', 'delivered')->count(),
            'pending_orders' => $orders->whereIn('status', ['pending', 'confirmed', 'ready_for_pickup'])->count(),
            'avg_order_value' => $orders->where('status', 'delivered')->count() > 0
                ? round($orders->where('status', 'delivered')->sum('total_amount') / $orders->where('status', 'delivered')->count(), 2)
                : 0,
            'monthly_revenue' => $monthlyRevenue,
            'sales_count' => $sales->count(),
        ];
    }

    /**
     * Expense analytics - breakdown by category, trends
     */
    private function getExpenseAnalytics($userId, $startDate, $endDate): array
    {
        $fieldIds = Field::where('user_id', $userId)->pluck('id');
        $plantingIds = Planting::whereIn('field_id', $fieldIds)->pluck('id');

        $expenses = Expense::whereIn('planting_id', $plantingIds)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        $totalExpenses = $expenses->sum('amount');

        $byCategory = $expenses->groupBy('category')
            ->map(fn($group) => [
                'total' => $group->sum('amount'),
                'count' => $group->count(),
                'percentage' => $totalExpenses > 0 ? round(($group->sum('amount') / $totalExpenses) * 100, 1) : 0,
            ]);

        $monthlyExpenses = $expenses->groupBy(fn($e) => Carbon::parse($e->date)->format('Y-m'))
            ->map(fn($group) => $group->sum('amount'));

        // Calculate expense trend (compare to previous period)
        $periodLength = Carbon::parse($startDate)->diffInDays(Carbon::parse($endDate));
        $prevStart = Carbon::parse($startDate)->subDays($periodLength)->format('Y-m-d');
        $prevEnd = Carbon::parse($startDate)->subDay()->format('Y-m-d');

        $prevExpenses = Expense::whereIn('planting_id', $plantingIds)
            ->whereBetween('date', [$prevStart, $prevEnd])
            ->sum('amount');

        $trend = $prevExpenses > 0
            ? round((($totalExpenses - $prevExpenses) / $prevExpenses) * 100, 1)
            : 0;

        return [
            'total_expenses' => $totalExpenses,
            'by_category' => $byCategory,
            'monthly_expenses' => $monthlyExpenses,
            'trend_percentage' => $trend,
            'expense_count' => $expenses->count(),
        ];
    }

    /**
     * Field analytics - utilization, area, status
     */
    private function getFieldAnalytics($userId): array
    {
        $fields = Field::where('user_id', $userId)
            ->with(['plantings.pestIncidents'])
            ->get();

        $totalArea = $fields->sum('size');
        $plantedArea = $fields->sum(function ($field) {
            return $field->plantings->where('status', '!=', 'harvested')->sum('area_planted');
        });

        $utilizationRate = $totalArea > 0 ? round(($plantedArea / $totalArea) * 100, 1) : 0;

        $fieldStatus = $fields->map(function ($field) {
            $activePlantings = $field->plantings->where('status', '!=', 'harvested');
            $hasActivePests = $activePlantings->flatMap->pestIncidents
                ->where('status', 'active')->count() > 0;

            return [
                'id' => $field->id,
                'name' => $field->name,
                'size' => $field->size,
                'active_plantings' => $activePlantings->count(),
                'status' => $activePlantings->count() > 0 ? 'active' : 'idle',
                'location' => $field->location,
                'coordinates' => $field->field_coordinates,
                'has_pests' => $hasActivePests,
            ];
        });

        return [
            'total_fields' => $fields->count(),
            'total_area' => $totalArea,
            'planted_area' => $plantedArea,
            'utilization_rate' => $utilizationRate,
            'idle_fields' => $fieldStatus->where('status', 'idle')->count(),
            'field_status' => $fieldStatus,
        ];
    }

    /**
     * Nursery/Seedling analytics
     */
    private function getNurseryAnalytics($userId): array
    {
        $seedPlantings = SeedPlanting::where('user_id', $userId)->with('riceVariety')->get();

        $statusCounts = $seedPlantings->groupBy('status')->map(fn($g) => $g->count());

        $byVariety = $seedPlantings->groupBy(fn($sp) => $sp->riceVariety->name ?? 'Unknown')
            ->map(fn($group) => [
                'count' => $group->count(),
                'ready' => $group->where('status', 'ready')->count(),
            ]);

        return [
            'total_batches' => $seedPlantings->count(),
            'active_batches' => $seedPlantings->whereIn('status', ['sown', 'germinating', 'ready'])->count(),
            'ready_for_transplant' => $seedPlantings->where('status', 'ready')->count(),
            'status_distribution' => $statusCounts,
            'by_variety' => $byVariety,
        ];
    }

    /**
     * Inventory analytics - stock levels, usage, and historical trends
     */
    private function getInventoryAnalytics($userId, $startDate, $endDate): array
    {
        $items = InventoryItem::where('user_id', $userId)->get();
        $itemIds = $items->pluck('id');

        // Snapshot data (Current status)
        $totalValue = $items->sum(fn($item) => ($item->current_stock ?? 0) * ($item->unit_price ?? 0));

        $lowStockItems = $items->filter(function ($item) {
            return ($item->current_stock ?? 0) <= ($item->minimum_stock ?? 0);
        });

        $byCategory = $items->groupBy('category')->map(fn($group) => [
            'count' => $group->count(),
            'total_stock' => $group->sum('current_stock'),
            'low_stock' => $group->filter(fn($i) => ($i->current_stock ?? 0) <= ($i->minimum_stock ?? 0))->count(),
        ]);

        // Historical Data (Transactions within date range)
        $transactions = InventoryTransaction::whereIn('inventory_item_id', $itemIds)
            ->whereBetween('transaction_date', [$startDate, $endDate])
            ->get();

        $totalCoonsumed = $transactions->where('transaction_type', 'out')->sum('quantity');
        $totalRestocked = $transactions->where('transaction_type', 'in')->sum('quantity');

        $mostConsumed = $transactions->where('transaction_type', 'out')
            ->groupBy('inventory_item_id')
            ->map(function ($group) {
                return [
                    'item_id' => $group->first()->inventory_item_id,
                    'quantity' => $group->sum('quantity'),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('quantity')
            ->first();

        $mostConsumedItem = $mostConsumed ? $items->firstWhere('id', $mostConsumed['item_id']) : null;

        return [
            'total_items' => $items->count(),
            'total_value' => $totalValue,
            'low_stock_count' => $lowStockItems->count(),
            'low_stock_items' => $lowStockItems->map(fn($i) => [
                'id' => $i->id,
                'name' => $i->name,
                'current_stock' => $i->current_stock,
                'minimum_stock' => $i->minimum_stock,
                'unit' => $i->unit,
            ])->values(),
            'by_category' => $byCategory,
            // Historical metrics
            'historical_usage' => [
                'total_consumed' => $totalCoonsumed,
                'total_restocked' => $totalRestocked,
                'transaction_count' => $transactions->count(),
                'most_consumed_item' => $mostConsumedItem ? [
                    'name' => $mostConsumedItem->name,
                    'quantity' => $mostConsumed['quantity'],
                    'unit' => $mostConsumedItem->unit,
                ] : null,
            ],
        ];
    }

    /**
     * Pest incident analytics
     */
    private function getPestAnalytics($userId, $startDate, $endDate): array
    {
        $fieldIds = Field::where('user_id', $userId)->pluck('id');
        $plantingIds = Planting::whereIn('field_id', $fieldIds)->pluck('id');

        $incidents = PestIncident::whereIn('planting_id', $plantingIds)
            ->whereBetween('detected_date', [$startDate, $endDate])
            ->get();

        $bySeverity = $incidents->groupBy('severity')->map(fn($g) => $g->count());
        $byType = $incidents->groupBy('pest_type')->map(fn($g) => $g->count());

        $activeIncidents = $incidents->where('status', PestIncident::STATUS_ACTIVE);
        $resolvedIncidents = $incidents->where('status', PestIncident::STATUS_RESOLVED);

        // Get predictions for active fields (limit to 5 to avoid performance issues)
        $activeFields = Field::where('user_id', $userId)
            ->whereNotNull('location')
            ->orWhereNotNull('field_coordinates')
            ->take(5)
            ->get();

        $forecasts = [];
        foreach ($activeFields as $field) {
            $risks = $this->pestPredictionService->predictRisks($field);
            if (!empty($risks)) {
                $forecasts[] = [
                    'field_id' => $field->id,
                    'field_name' => $field->name,
                    'predictions' => $risks
                ];
            }
        }

        return [
            'total_incidents' => $incidents->count(),
            'active_incidents' => $activeIncidents->count(),
            'resolved_incidents' => $resolvedIncidents->count(),
            'by_severity' => $bySeverity,
            'by_type' => $byType,
            'active_details' => $activeIncidents->map(fn($i) => [
                'id' => $i->id,
                'pest_type' => $i->pest_type,
                'severity' => $i->severity,
                'field' => $i->planting->field->name ?? 'Unknown',
            ])->values(),
            'forecasts' => $forecasts,
        ];
    }

    /**
     * Labor analytics - laborers, costs, productivity
     */
    private function getLaborAnalytics($userId, $startDate, $endDate): array
    {
        $laborers = Laborer::where('user_id', $userId)->get();

        $fieldIds = Field::where('user_id', $userId)->pluck('id');
        $plantingIds = Planting::whereIn('field_id', $fieldIds)->pluck('id');

        $tasks = Task::whereIn('planting_id', $plantingIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $totalLaborCost = $tasks->sum('wage_amount');

        $tasksByLaborer = $tasks->whereNotNull('laborer_id')
            ->groupBy('laborer_id')
            ->map(fn($group) => [
                'completed' => $group->where('status', 'completed')->count(),
                'total' => $group->count(),
                'earnings' => $group->sum('wage_amount'),
            ]);

        return [
            'total_laborers' => $laborers->count(),
            'active_laborers' => $laborers->where('status', 'active')->count(),
            'total_labor_cost' => $totalLaborCost,
            'tasks_assigned' => $tasks->count(),
            'tasks_completed' => $tasks->where('status', 'completed')->count(),
            'completion_rate' => $tasks->count() > 0
                ? round(($tasks->where('status', 'completed')->count() / $tasks->count()) * 100, 1)
                : 0,
            'top_laborers' => $tasksByLaborer->sortByDesc('completed')->take(5),
        ];
    }

    /**
     * Task analytics - status, completion, overdue
     */
    private function getTaskAnalytics($userId, $startDate, $endDate): array
    {
        $fieldIds = Field::where('user_id', $userId)->pluck('id');
        $plantingIds = Planting::whereIn('field_id', $fieldIds)->pluck('id');

        $tasks = Task::whereIn('planting_id', $plantingIds)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get();

        $statusCounts = $tasks->groupBy('status')->map(fn($g) => $g->count());
        $byType = $tasks->groupBy('task_type')->map(fn($g) => $g->count());

        $overdueTasks = $tasks->filter(function ($task) {
            return $task->status !== 'completed'
                && $task->due_date
                && Carbon::parse($task->due_date)->isPast();
        });

        $weeklyTrend = $tasks->groupBy(fn($t) => Carbon::parse($t->created_at)->format('W'))
            ->map(fn($group) => [
                'total' => $group->count(),
                'completed' => $group->where('status', 'completed')->count(),
            ]);

        return [
            'total_tasks' => $tasks->count(),
            'completed_tasks' => $tasks->where('status', 'completed')->count(),
            'pending_tasks' => $tasks->where('status', 'pending')->count(),
            'in_progress_tasks' => $tasks->where('status', 'in_progress')->count(),
            'overdue_tasks' => $overdueTasks->count(),
            'status_distribution' => $statusCounts,
            'by_type' => $byType,
            'weekly_trend' => $weeklyTrend,
            'overdue_details' => $overdueTasks->take(5)->map(fn($t) => [
                'id' => $t->id,
                'task_type' => $t->task_type,
                'due_date' => $t->due_date,
                'days_overdue' => Carbon::parse($t->due_date)->diffInDays(now()),
                'field' => $t->planting->field->name ?? 'Unknown',
            ])->values(),
        ];
    }

    /**
     * Generate intelligent action suggestions based on data patterns
     */
    private function generateActionSuggestions($userId): array
    {
        $suggestions = [];

        // 1. Low inventory suggestions
        $lowStockItems = InventoryItem::where('user_id', $userId)
            ->whereColumn('current_stock', '<=', 'minimum_stock')
            ->limit(3)
            ->get();

        foreach ($lowStockItems as $item) {
            $suggestions[] = [
                'priority' => 'high',
                'category' => 'inventory',
                'icon' => '📦',
                'message' => "Restock {$item->name} - only {$item->current_stock} {$item->unit} remaining (below minimum of {$item->minimum_stock})",
                'action_url' => "/inventory/{$item->id}",
                'action_label' => 'View Item',
            ];
        }

        // 2. Overdue tasks
        $fieldIds = Field::where('user_id', $userId)->pluck('id');
        $plantingIds = Planting::whereIn('field_id', $fieldIds)->pluck('id');

        $overdueTasks = Task::whereIn('planting_id', $plantingIds)
            ->where('status', '!=', 'completed')
            ->where('due_date', '<', now())
            ->with('planting.field')
            ->limit(3)
            ->get();

        foreach ($overdueTasks as $task) {
            $daysOverdue = Carbon::parse($task->due_date)->diffInDays(now());
            $fieldName = $task->planting->field->name ?? 'Unknown field';
            $suggestions[] = [
                'priority' => $daysOverdue > 3 ? 'high' : 'medium',
                'category' => 'tasks',
                'icon' => '⚠️',
                'message' => "Complete overdue {$task->task_type} task for {$fieldName} - {$daysOverdue} days late",
                'action_url' => "/tasks/{$task->id}",
                'action_label' => 'View Task',
            ];
        }

        // 3. Ready seedlings for transplant
        $readySeedlings = SeedPlanting::where('user_id', $userId)
            ->where('status', 'ready')
            ->with('riceVariety')
            ->get();

        if ($readySeedlings->count() > 0) {
            $varietyNames = $readySeedlings->map(fn($s) => $s->riceVariety->name ?? 'Unknown')->unique()->implode(', ');
            $suggestions[] = [
                'priority' => 'medium',
                'category' => 'nursery',
                'icon' => '🌱',
                'message' => "Transplant {$varietyNames} seedlings - {$readySeedlings->count()} batches ready",
                'action_url' => '/seed-plantings',
                'action_label' => 'View Nursery',
            ];
        }

        // 4. Active pest incidents needing treatment
        $activePests = PestIncident::whereIn('planting_id', $plantingIds)
            ->where('status', PestIncident::STATUS_ACTIVE)
            ->with('planting.field')
            ->limit(2)
            ->get();

        foreach ($activePests as $pest) {
            $fieldName = $pest->planting->field->name ?? 'Unknown field';
            $suggestions[] = [
                'priority' => $pest->severity === 'critical' || $pest->severity === 'high' ? 'high' : 'medium',
                'category' => 'pests',
                'icon' => '🐛',
                'message' => "Apply treatment for {$pest->pest_type} in {$fieldName} - severity: {$pest->severity}",
                'action_url' => '/pest-tracker',
                'action_label' => 'View Pest Tracker',
            ];
        }

        // 5. Weather-based suggestions
        $latestWeather = WeatherLog::whereIn('field_id', $fieldIds)
            ->orderBy('recorded_at', 'desc')
            ->first();

        if ($latestWeather) {
            $condition = strtolower($latestWeather->weather_condition ?? '');
            if (str_contains($condition, 'rain') || str_contains($condition, 'storm')) {
                $suggestions[] = [
                    'priority' => 'medium',
                    'category' => 'weather',
                    'icon' => '🌧️',
                    'message' => 'Delay pesticide/fertilizer application due to rain conditions',
                    'action_url' => '/weather',
                    'action_label' => 'Check Forecast',
                ];
            }

            if ($latestWeather->temperature > 35) {
                $suggestions[] = [
                    'priority' => 'medium',
                    'category' => 'weather',
                    'icon' => '🌡️',
                    'message' => 'High temperature alert - irrigate crops early morning or evening',
                    'action_url' => '/weather',
                    'action_label' => 'Check Forecast',
                ];
            }
        }

        // 6. Pending orders to fulfill
        $pendingOrders = RiceOrder::whereHas('riceProduct', function ($q) use ($userId) {
            $q->where('farmer_id', $userId);
        })
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        if ($pendingOrders > 0) {
            $suggestions[] = [
                'priority' => 'high',
                'category' => 'sales',
                'icon' => '📋',
                'message' => "Fulfill {$pendingOrders} pending marketplace orders",
                'action_url' => '/farmer/orders',
                'action_label' => 'View Orders',
            ];
        }

        // 7. Crops ready for harvest (checking planting status)
        $maturePlantings = Planting::whereIn('field_id', $fieldIds)
            ->whereIn('status', ['maturity', 'ripening', 'ready_for_harvest'])
            ->with('field')
            ->limit(2)
            ->get();

        foreach ($maturePlantings as $planting) {
            $fieldName = $planting->field->name ?? 'Unknown';
            $suggestions[] = [
                'priority' => 'high',
                'category' => 'harvest',
                'icon' => '🌾',
                'message' => "Schedule harvesting for {$fieldName} - crop at {$planting->status} stage",
                'action_url' => '/harvests/create',
                'action_label' => 'Record Harvest',
            ];
        }

        // Sort by priority
        usort($suggestions, function ($a, $b) {
            $priorityOrder = ['high' => 0, 'medium' => 1, 'low' => 2];
            return ($priorityOrder[$a['priority']] ?? 2) - ($priorityOrder[$b['priority']] ?? 2);
        });

        return array_slice($suggestions, 0, 8); // Return top 8 suggestions
    }

    /**
     * Financial forecasting (Cash Flow Projection)
     */
    private function getFinancialForecast($userId): array
    {
        $fieldIds = Field::where('user_id', $userId)->pluck('id');

        // 1. Projected Revenue from Harvests (Next 6 months)
        $plantings = Planting::whereIn('field_id', $fieldIds)
            ->where('status', '!=', 'harvested')
            ->where('status', '!=', 'failed')
            ->whereNotNull('expected_harvest_date')
            ->where('expected_harvest_date', '>', now())
            ->with('riceVariety')
            ->get();

        // Get average market price (fallback to 20 if no products)
        $avgPrice = RiceProduct::where('farmer_id', $userId)->avg('price_per_unit') ?? 20;

        $projectedRevenue = [];
        $today = Carbon::now();

        // Initialize next 6 months
        for ($i = 0; $i < 6; $i++) {
            $month = $today->copy()->addMonths($i)->format('Y-m');
            $projectedRevenue[$month] = 0;
        }

        foreach ($plantings as $planting) {
            $harvestMonth = Carbon::parse($planting->expected_harvest_date)->format('Y-m');

            if (isset($projectedRevenue[$harvestMonth])) {
                $yieldPerHa = $planting->riceVariety->average_yield_per_hectare ?? 4000; // kg/ha
                $yield = $planting->area_planted * $yieldPerHa;
                $revenue = $yield * $avgPrice;
                $projectedRevenue[$harvestMonth] += $revenue;
            }
        }

        // 2. Projected Expenses (Based on 3-month average)
        $threeMonthsAgo = now()->subMonths(3);
        $plantingIds = Planting::whereIn('field_id', $fieldIds)->pluck('id');

        $pastExpenses = Expense::whereIn('planting_id', $plantingIds)
            ->where('date', '>=', $threeMonthsAgo)
            ->sum('amount');

        $avgMonthlyExpense = $pastExpenses / 3;

        $forecast = [];
        $months = [];
        $revenueData = [];
        $expenseData = [];
        $netData = [];

        foreach ($projectedRevenue as $month => $revenue) {
            $monthLabel = Carbon::parse($month . '-01')->format('M Y');
            $months[] = $monthLabel;
            $revenueData[] = round($revenue, 2);
            $expenseData[] = round($avgMonthlyExpense, 2);
            $netData[] = round($revenue - $avgMonthlyExpense, 2);
        }

        return [
            'months' => $months,
            'projected_revenue' => $revenueData,
            'projected_expenses' => $expenseData,
            'net_cash_flow' => $netData
        ];
    }
}
