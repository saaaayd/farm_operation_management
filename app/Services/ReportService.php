<?php

namespace App\Services;

use App\Models\Farm;
use App\Models\Field;
use App\Models\Planting;
use App\Models\Harvest;
use App\Models\Expense;
use App\Models\Sale;
use App\Models\LaborWage;
use App\Models\InventoryItem;
use App\Models\WeatherLog;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportService
{
    protected $financialService;
    protected $inventoryService;
    protected $laborService;
    protected $weatherService;

    public function __construct(
        FinancialService $financialService,
        InventoryService $inventoryService,
        LaborService $laborService,
        WeatherService $weatherService
    ) {
        $this->financialService = $financialService;
        $this->inventoryService = $inventoryService;
        $this->laborService = $laborService;
        $this->weatherService = $weatherService;
    }

    /**
     * Generate dashboard analytics
     */
    public function getDashboardAnalytics($userId, $farmId = null)
    {
        $farms = Farm::where('user_id', $userId)->get();
        
        if ($farmId) {
            $farms = $farms->where('id', $farmId);
        }

        $analytics = [
            'overview' => $this->getOverviewMetrics($userId, $farms),
            'financial_summary' => $this->getFinancialSummary($farms),
            'production_summary' => $this->getProductionSummary($farms),
            'alerts_summary' => $this->getAlertsSummary($userId, $farms),
            'recent_activities' => $this->getRecentActivities($farms),
        ];

        return $analytics;
    }

    /**
     * Get overview metrics
     */
    private function getOverviewMetrics($userId, $farms)
    {
        $totalArea = $farms->sum(function ($farm) {
            return $farm->fields->sum('size_hectares');
        });

        $activePlantings = Planting::whereHas('field', function ($q) use ($farms) {
            $q->whereIn('farm_id', $farms->pluck('id'));
        })
        ->where('status', 'active')
        ->count();

        $totalInventoryValue = InventoryItem::where('user_id', $userId)
            ->get()
            ->sum(function ($item) {
                return $item->quantity * $item->price;
            });

        $lowStockItems = $this->inventoryService->getLowStockItems($userId)->count();

        return [
            'total_farms' => $farms->count(),
            'total_area_hectares' => $totalArea,
            'active_plantings' => $activePlantings,
            'total_inventory_value' => $totalInventoryValue,
            'low_stock_alerts' => $lowStockItems,
        ];
    }

    /**
     * Get financial summary
     */
    private function getFinancialSummary($farms)
    {
        $totalRevenue = 0;
        $totalExpenses = 0;
        $totalLaborCosts = 0;

        foreach ($farms as $farm) {
            $summary = $this->financialService->getFarmFinancialSummary($farm->id, 30);
            $totalRevenue += $summary['total_revenue'];
            $totalExpenses += $summary['total_expenses'];
            $totalLaborCosts += $summary['total_labor_costs'];
        }

        $netProfit = $totalRevenue - $totalExpenses - $totalLaborCosts;
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        return [
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'total_labor_costs' => $totalLaborCosts,
            'net_profit' => $netProfit,
            'profit_margin' => round($profitMargin, 2),
        ];
    }

    /**
     * Get production summary
     */
    private function getProductionSummary($farms)
    {
        $farmIds = $farms->pluck('id');
        
        $recentHarvests = Harvest::whereHas('planting.field', function ($q) use ($farmIds) {
            $q->whereIn('farm_id', $farmIds);
        })
        ->where('harvest_date', '>=', now()->subDays(30))
        ->get();

        $upcomingHarvests = Planting::whereHas('field', function ($q) use ($farmIds) {
            $q->whereIn('farm_id', $farmIds);
        })
        ->where('expected_harvest_date', '>=', now())
        ->where('expected_harvest_date', '<=', now()->addDays(30))
        ->where('status', 'active')
        ->count();

        return [
            'recent_harvests_count' => $recentHarvests->count(),
            'recent_total_yield' => $recentHarvests->sum('yield_kg'),
            'upcoming_harvests' => $upcomingHarvests,
            'average_yield_per_harvest' => $recentHarvests->count() > 0 ? 
                $recentHarvests->avg('yield_kg') : 0,
        ];
    }

    /**
     * Get alerts summary
     */
    private function getAlertsSummary($userId, $farms)
    {
        $alerts = [
            'weather_alerts' => 0,
            'inventory_alerts' => 0,
            'financial_alerts' => 0,
            'production_alerts' => 0,
        ];

        // Weather alerts
        foreach ($farms as $farm) {
            foreach ($farm->fields as $field) {
                $weatherAlerts = $this->weatherService->getWeatherAlerts($field);
                $alerts['weather_alerts'] += count($weatherAlerts);
            }
        }

        // Inventory alerts (low stock)
        $alerts['inventory_alerts'] = $this->inventoryService->getLowStockItems($userId)->count();

        // Financial alerts (negative profit)
        foreach ($farms as $farm) {
            $summary = $this->financialService->getFarmFinancialSummary($farm->id, 30);
            if ($summary['net_profit'] < 0) {
                $alerts['financial_alerts']++;
            }
        }

        // Production alerts (overdue harvests)
        $overdueHarvests = Planting::whereHas('field', function ($q) use ($farms) {
            $q->whereIn('farm_id', $farms->pluck('id'));
        })
        ->where('expected_harvest_date', '<', now())
        ->where('status', 'active')
        ->count();

        $alerts['production_alerts'] = $overdueHarvests;

        return $alerts;
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities($farms)
    {
        $activities = [];
        $farmIds = $farms->pluck('id');

        // Recent expenses
        $recentExpenses = Expense::whereHas('planting.field', function ($q) use ($farmIds) {
            $q->whereIn('farm_id', $farmIds);
        })
        ->where('created_at', '>=', now()->subDays(7))
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

        foreach ($recentExpenses as $expense) {
            $activities[] = [
                'type' => 'expense',
                'description' => "Expense: {$expense->description}",
                'amount' => $expense->amount,
                'date' => $expense->created_at,
                'category' => $expense->category,
            ];
        }

        // Recent sales
        $recentSales = Sale::whereHas('harvest.planting.field', function ($q) use ($farmIds) {
            $q->whereIn('farm_id', $farmIds);
        })
        ->where('created_at', '>=', now()->subDays(7))
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

        foreach ($recentSales as $sale) {
            $activities[] = [
                'type' => 'sale',
                'description' => "Sale: {$sale->quantity}kg",
                'amount' => $sale->total_amount,
                'date' => $sale->created_at,
                'crop_type' => $sale->crop_type,
            ];
        }

        // Recent harvests
        $recentHarvests = Harvest::whereHas('planting.field', function ($q) use ($farmIds) {
            $q->whereIn('farm_id', $farmIds);
        })
        ->where('created_at', '>=', now()->subDays(7))
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

        foreach ($recentHarvests as $harvest) {
            $activities[] = [
                'type' => 'harvest',
                'description' => "Harvest: {$harvest->yield_kg}kg",
                'amount' => null,
                'date' => $harvest->created_at,
                'crop_type' => $harvest->planting->crop_type ?? 'Unknown',
            ];
        }

        // Sort activities by date
        usort($activities, function ($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return array_slice($activities, 0, 10);
    }

    /**
     * Generate performance benchmarks
     */
    public function getPerformanceBenchmarks($farmId, $period = 30)
    {
        $farm = Farm::findOrFail($farmId);
        
        // Get industry benchmarks (these would typically come from external data sources)
        $industryBenchmarks = $this->getIndustryBenchmarks();
        
        // Get farm performance
        $farmPerformance = [
            'yield_per_hectare' => $this->calculateYieldPerHectare($farmId, $period),
            'cost_per_hectare' => $this->financialService->getCostPerHectare($farmId, $period),
            'revenue_per_hectare' => $this->financialService->getRevenuePerHectare($farmId, $period),
            'profit_margin' => $this->financialService->getFarmFinancialSummary($farmId, $period)['profit_margin'],
            'labor_efficiency' => $this->calculateLaborEfficiency($farmId, $period),
        ];

        // Compare with benchmarks
        $comparison = [];
        foreach ($farmPerformance as $metric => $value) {
            $benchmark = $industryBenchmarks[$metric] ?? 0;
            $comparison[$metric] = [
                'farm_value' => $value,
                'industry_benchmark' => $benchmark,
                'difference' => $value - $benchmark,
                'percentage_difference' => $benchmark > 0 ? (($value - $benchmark) / $benchmark) * 100 : 0,
                'performance' => $value >= $benchmark ? 'above_benchmark' : 'below_benchmark',
            ];
        }

        return [
            'farm' => $farm,
            'period_days' => $period,
            'benchmarks' => $comparison,
            'overall_score' => $this->calculateOverallPerformanceScore($comparison),
        ];
    }

    /**
     * Calculate yield per hectare
     */
    private function calculateYieldPerHectare($farmId, $period)
    {
        $startDate = now()->subDays($period);
        
        $harvests = Harvest::whereHas('planting.field', function ($q) use ($farmId) {
            $q->where('farm_id', $farmId);
        })
        ->where('harvest_date', '>=', $startDate)
        ->with('planting.field')
        ->get();

        if ($harvests->isEmpty()) {
            return 0;
        }

        $totalYield = $harvests->sum('yield_kg');
        $totalArea = $harvests->sum(function ($harvest) {
            return $harvest->planting->field->size_hectares;
        });

        return $totalArea > 0 ? $totalYield / $totalArea : 0;
    }

    /**
     * Calculate labor efficiency
     */
    private function calculateLaborEfficiency($farmId, $period)
    {
        $laborSummary = $this->laborService->getLaborCostSummary($farmId, $period);
        $totalArea = Farm::findOrFail($farmId)->fields->sum('size_hectares');

        if ($totalArea == 0 || $laborSummary['total_hours'] == 0) {
            return 0;
        }

        return $laborSummary['total_hours'] / $totalArea; // Hours per hectare
    }

    /**
     * Get industry benchmarks (mock data)
     */
    private function getIndustryBenchmarks()
    {
        return [
            'yield_per_hectare' => 4500, // kg per hectare for rice
            'cost_per_hectare' => 1200, // USD per hectare
            'revenue_per_hectare' => 1800, // USD per hectare
            'profit_margin' => 25, // percentage
            'labor_efficiency' => 120, // hours per hectare
        ];
    }

    /**
     * Calculate overall performance score
     */
    private function calculateOverallPerformanceScore($comparison)
    {
        $scores = [];
        
        foreach ($comparison as $metric => $data) {
            if ($data['performance'] === 'above_benchmark') {
                $scores[] = min(100, 50 + abs($data['percentage_difference']));
            } else {
                $scores[] = max(0, 50 - abs($data['percentage_difference']));
            }
        }

        return count($scores) > 0 ? round(array_sum($scores) / count($scores), 1) : 0;
    }

    /**
     * Generate seasonal analysis
     */
    public function getSeasonalAnalysis($farmId, $years = 2)
    {
        $analysis = [];
        
        for ($year = 0; $year < $years; $year++) {
            $yearData = [];
            $currentYear = now()->subYears($year)->year;
            
            for ($month = 1; $month <= 12; $month++) {
                $startDate = Carbon::create($currentYear, $month, 1)->startOfMonth();
                $endDate = Carbon::create($currentYear, $month, 1)->endOfMonth();
                
                $monthlyData = [
                    'month' => $startDate->format('M'),
                    'year' => $currentYear,
                    'plantings' => $this->getMonthlyPlantings($farmId, $startDate, $endDate),
                    'harvests' => $this->getMonthlyHarvests($farmId, $startDate, $endDate),
                    'expenses' => $this->getMonthlyExpenses($farmId, $startDate, $endDate),
                    'sales' => $this->getMonthlySales($farmId, $startDate, $endDate),
                    'weather_summary' => $this->getMonthlyWeatherSummary($farmId, $startDate, $endDate),
                ];
                
                $yearData[] = $monthlyData;
            }
            
            $analysis[$currentYear] = $yearData;
        }

        return [
            'seasonal_data' => $analysis,
            'trends' => $this->identifySeasonalTrends($analysis),
            'recommendations' => $this->generateSeasonalRecommendations($analysis),
        ];
    }

    /**
     * Get monthly plantings
     */
    private function getMonthlyPlantings($farmId, $startDate, $endDate)
    {
        return Planting::whereHas('field', function ($q) use ($farmId) {
            $q->where('farm_id', $farmId);
        })
        ->whereBetween('planting_date', [$startDate, $endDate])
        ->count();
    }

    /**
     * Get monthly harvests
     */
    private function getMonthlyHarvests($farmId, $startDate, $endDate)
    {
        $harvests = Harvest::whereHas('planting.field', function ($q) use ($farmId) {
            $q->where('farm_id', $farmId);
        })
        ->whereBetween('harvest_date', [$startDate, $endDate])
        ->get();

        return [
            'count' => $harvests->count(),
            'total_yield' => $harvests->sum('yield_kg'),
        ];
    }

    /**
     * Get monthly expenses
     */
    private function getMonthlyExpenses($farmId, $startDate, $endDate)
    {
        return Expense::whereHas('planting.field', function ($q) use ($farmId) {
            $q->where('farm_id', $farmId);
        })
        ->whereBetween('date', [$startDate, $endDate])
        ->sum('amount');
    }

    /**
     * Get monthly sales
     */
    private function getMonthlySales($farmId, $startDate, $endDate)
    {
        return Sale::whereHas('harvest.planting.field', function ($q) use ($farmId) {
            $q->where('farm_id', $farmId);
        })
        ->whereBetween('sale_date', [$startDate, $endDate])
        ->sum('total_amount');
    }

    /**
     * Get monthly weather summary
     */
    private function getMonthlyWeatherSummary($farmId, $startDate, $endDate)
    {
        $farm = Farm::findOrFail($farmId);
        $weatherData = [];
        
        foreach ($farm->fields as $field) {
            $logs = WeatherLog::where('field_id', $field->id)
                ->whereBetween('recorded_at', [$startDate, $endDate])
                ->get();
                
            if ($logs->isNotEmpty()) {
                $weatherData[] = [
                    'field_id' => $field->id,
                    'avg_temperature' => $logs->avg('temperature'),
                    'avg_humidity' => $logs->avg('humidity'),
                    'total_readings' => $logs->count(),
                ];
            }
        }

        return $weatherData;
    }

    /**
     * Identify seasonal trends
     */
    private function identifySeasonalTrends($analysis)
    {
        // This would analyze the seasonal data to identify patterns
        // For now, returning a simplified structure
        return [
            'peak_planting_months' => ['March', 'April', 'May'],
            'peak_harvest_months' => ['September', 'October', 'November'],
            'highest_expense_months' => ['April', 'May', 'June'],
            'best_sales_months' => ['October', 'November', 'December'],
        ];
    }

    /**
     * Generate seasonal recommendations
     */
    private function generateSeasonalRecommendations($analysis)
    {
        return [
            'planting_recommendations' => [
                'Optimal planting window appears to be March-May based on historical data.',
                'Consider early planting in March for better yield potential.',
            ],
            'harvest_recommendations' => [
                'Plan for peak harvest activities in September-November.',
                'Ensure adequate storage and processing capacity during peak harvest.',
            ],
            'financial_recommendations' => [
                'Budget for higher expenses during April-June planting season.',
                'Plan cash flow around peak sales periods in October-December.',
            ],
        ];
    }
}