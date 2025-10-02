<?php

namespace App\Http\Controllers;

use App\Models\Field;
use App\Models\Planting;
use App\Models\Harvest;
use App\Models\RiceProduct;
use App\Models\RiceOrder;
use App\Models\WeatherLog;
use App\Models\Expense;
use App\Services\WeatherService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RiceFarmingAnalyticsController extends Controller
{
    private WeatherService $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * Get comprehensive rice farming analytics
     */
    public function getRiceFarmingAnalytics(Request $request)
    {
        try {
            $user = auth()->user();
            $period = $request->get('period', '12'); // months
            $startDate = Carbon::now()->subMonths($period);

            $analytics = [
                'production_analytics' => $this->getProductionAnalytics($user->id, $startDate),
                'financial_analytics' => $this->getFinancialAnalytics($user->id, $startDate),
                'field_performance' => $this->getFieldPerformanceAnalytics($user->id, $startDate),
                'weather_impact' => $this->getWeatherImpactAnalytics($user->id, $startDate),
                'market_performance' => $this->getMarketPerformanceAnalytics($user->id, $startDate),
                'efficiency_metrics' => $this->getEfficiencyMetrics($user->id, $startDate),
                'growth_trends' => $this->getGrowthTrends($user->id, $startDate),
            ];

            return response()->json([
                'analytics' => $analytics,
                'period' => $period,
                'generated_at' => now(),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate rice farming analytics',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get production analytics
     */
    private function getProductionAnalytics($userId, $startDate)
    {
        $plantings = Planting::whereHas('field', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->where('crop_type', 'rice')
        ->where('planting_date', '>=', $startDate)
        ->with(['riceVariety', 'harvests', 'field'])
        ->get();

        $harvests = Harvest::whereHas('planting.field', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->where('harvest_date', '>=', $startDate)
        ->get();

        $totalArea = $plantings->sum('area_planted');
        $totalYield = $harvests->sum('yield');
        $averageYieldPerHectare = $totalArea > 0 ? $totalYield / $totalArea : 0;

        // Yield by variety
        $yieldByVariety = $plantings->groupBy('rice_variety_id')
            ->map(function ($varietyPlantings) {
                $variety = $varietyPlantings->first()->riceVariety;
                $totalYield = $varietyPlantings->sum(function ($planting) {
                    return $planting->harvests->sum('yield');
                });
                $totalArea = $varietyPlantings->sum('area_planted');
                
                return [
                    'variety_name' => $variety->name,
                    'total_yield' => $totalYield,
                    'total_area' => $totalArea,
                    'yield_per_hectare' => $totalArea > 0 ? $totalYield / $totalArea : 0,
                    'plantings_count' => $varietyPlantings->count(),
                ];
            })->values();

        // Monthly production trends
        $monthlyProduction = $harvests->groupBy(function ($harvest) {
            return $harvest->harvest_date->format('Y-m');
        })->map(function ($monthHarvests, $month) {
            return [
                'month' => $month,
                'total_yield' => $monthHarvests->sum('yield'),
                'harvest_count' => $monthHarvests->count(),
                'average_quality' => $monthHarvests->avg('quality_grade') ?: 0,
            ];
        })->values();

        // Success rate by growth stage
        $stageSuccessRate = $plantings->map(function ($planting) {
            $totalStages = $planting->plantingStages()->count();
            $completedStages = $planting->plantingStages()->where('status', 'completed')->count();
            return $totalStages > 0 ? ($completedStages / $totalStages) * 100 : 0;
        });

        return [
            'total_plantings' => $plantings->count(),
            'total_area_planted' => $totalArea,
            'total_yield' => $totalYield,
            'average_yield_per_hectare' => round($averageYieldPerHectare, 2),
            'yield_by_variety' => $yieldByVariety,
            'monthly_production' => $monthlyProduction,
            'average_success_rate' => round($stageSuccessRate->avg(), 2),
            'completed_harvests' => $harvests->count(),
            'active_plantings' => $plantings->whereIn('status', ['planted', 'growing'])->count(),
        ];
    }

    /**
     * Get financial analytics
     */
    private function getFinancialAnalytics($userId, $startDate)
    {
        // Revenue from rice sales
        $riceOrders = RiceOrder::whereHas('riceProduct', function ($query) use ($userId) {
            $query->where('farmer_id', $userId);
        })
        ->where('order_date', '>=', $startDate)
        ->where('status', 'delivered')
        ->get();

        $totalRevenue = $riceOrders->sum('total_amount');
        $totalOrdersCount = $riceOrders->count();
        $averageOrderValue = $totalOrdersCount > 0 ? $totalRevenue / $totalOrdersCount : 0;

        // Expenses
        $expenses = Expense::whereHas('planting.field', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->where('expense_date', '>=', $startDate)
        ->get();

        $totalExpenses = $expenses->sum('amount');
        $expensesByCategory = $expenses->groupBy('category')
            ->map(function ($categoryExpenses, $category) {
                return [
                    'category' => $category,
                    'total_amount' => $categoryExpenses->sum('amount'),
                    'count' => $categoryExpenses->count(),
                ];
            })->values();

        // Profit calculation
        $netProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        // Monthly financial trends
        $monthlyFinancials = collect();
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $monthRevenue = $riceOrders->whereBetween('order_date', [$monthStart, $monthEnd])->sum('total_amount');
            $monthExpenses = $expenses->whereBetween('expense_date', [$monthStart, $monthEnd])->sum('amount');

            $monthlyFinancials->push([
                'month' => $month->format('Y-m'),
                'revenue' => $monthRevenue,
                'expenses' => $monthExpenses,
                'profit' => $monthRevenue - $monthExpenses,
            ]);
        }

        return [
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'profit_margin' => round($profitMargin, 2),
            'total_orders' => $totalOrdersCount,
            'average_order_value' => round($averageOrderValue, 2),
            'expenses_by_category' => $expensesByCategory,
            'monthly_trends' => $monthlyFinancials,
            'roi' => $totalExpenses > 0 ? round(($netProfit / $totalExpenses) * 100, 2) : 0,
        ];
    }

    /**
     * Get field performance analytics
     */
    private function getFieldPerformanceAnalytics($userId, $startDate)
    {
        $fields = Field::where('user_id', $userId)
            ->with(['plantings' => function ($query) use ($startDate) {
                $query->where('planting_date', '>=', $startDate)
                      ->where('crop_type', 'rice')
                      ->with('harvests');
            }])
            ->get();

        $fieldPerformance = $fields->map(function ($field) {
            $plantings = $field->plantings;
            $totalYield = $plantings->sum(function ($planting) {
                return $planting->harvests->sum('yield');
            });
            $totalArea = $plantings->sum('area_planted');
            $yieldPerHectare = $totalArea > 0 ? $totalYield / $totalArea : 0;

            $productivityScore = $field->getProductivityScore();
            $soilFertility = $field->getSoilFertilityStatus();

            return [
                'field_id' => $field->id,
                'field_name' => $field->name,
                'size' => $field->size,
                'total_yield' => $totalYield,
                'yield_per_hectare' => round($yieldPerHectare, 2),
                'plantings_count' => $plantings->count(),
                'productivity_score' => $productivityScore,
                'soil_fertility' => $soilFertility,
                'is_suitable_for_rice' => $field->isSuitableForRice(),
            ];
        });

        // Best and worst performing fields
        $bestField = $fieldPerformance->sortByDesc('yield_per_hectare')->first();
        $worstField = $fieldPerformance->sortBy('yield_per_hectare')->first();

        return [
            'field_performance' => $fieldPerformance,
            'best_performing_field' => $bestField,
            'worst_performing_field' => $worstField,
            'average_productivity_score' => round($fieldPerformance->avg('productivity_score'), 2),
            'total_fields' => $fields->count(),
            'suitable_fields_count' => $fieldPerformance->where('is_suitable_for_rice', true)->count(),
        ];
    }

    /**
     * Get weather impact analytics
     */
    private function getWeatherImpactAnalytics($userId, $startDate)
    {
        $fields = Field::where('user_id', $userId)->get();
        $weatherImpact = [];

        foreach ($fields as $field) {
            $analytics = $this->weatherService->getRiceWeatherAnalytics($field, 365);
            
            if (!empty($analytics)) {
                $weatherImpact[] = [
                    'field_id' => $field->id,
                    'field_name' => $field->name,
                    'weather_suitability_score' => $analytics['rice_analytics']['weather_suitability_score'] ?? 0,
                    'heat_stress_days' => $analytics['rice_analytics']['heat_stress_days'] ?? 0,
                    'optimal_growth_days' => $analytics['rice_analytics']['optimal_growth_days'] ?? 0,
                    'disease_risk_days' => $analytics['rice_analytics']['disease_risk_days'] ?? 0,
                    'growing_degree_days' => $analytics['rice_analytics']['growing_degree_days'] ?? 0,
                ];
            }
        }

        $averageWeatherSuitability = collect($weatherImpact)->avg('weather_suitability_score');
        $totalHeatStressDays = collect($weatherImpact)->sum('heat_stress_days');
        $totalOptimalDays = collect($weatherImpact)->sum('optimal_growth_days');

        return [
            'field_weather_impact' => $weatherImpact,
            'average_weather_suitability' => round($averageWeatherSuitability, 2),
            'total_heat_stress_days' => $totalHeatStressDays,
            'total_optimal_days' => $totalOptimalDays,
            'weather_risk_assessment' => $this->assessWeatherRisk($weatherImpact),
        ];
    }

    /**
     * Get market performance analytics
     */
    private function getMarketPerformanceAnalytics($userId, $startDate)
    {
        $products = RiceProduct::where('farmer_id', $userId)
            ->with(['orders' => function ($query) use ($startDate) {
                $query->where('order_date', '>=', $startDate);
            }, 'reviews'])
            ->get();

        $marketPerformance = $products->map(function ($product) {
            $orders = $product->orders;
            $totalSold = $orders->where('status', 'delivered')->sum('quantity');
            $totalRevenue = $orders->where('status', 'delivered')->sum('total_amount');
            $averageRating = $product->reviews->avg('rating') ?: 0;

            return [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'variety' => $product->riceVariety->name ?? 'Unknown',
                'total_sold' => $totalSold,
                'total_revenue' => $totalRevenue,
                'orders_count' => $orders->count(),
                'average_rating' => round($averageRating, 2),
                'reviews_count' => $product->reviews->count(),
                'current_price' => $product->price_per_unit,
                'quality_score' => $product->getQualityScore(),
            ];
        });

        $bestSellingProduct = $marketPerformance->sortByDesc('total_sold')->first();
        $highestRatedProduct = $marketPerformance->sortByDesc('average_rating')->first();

        return [
            'product_performance' => $marketPerformance,
            'best_selling_product' => $bestSellingProduct,
            'highest_rated_product' => $highestRatedProduct,
            'total_products' => $products->count(),
            'average_rating' => round($marketPerformance->avg('average_rating'), 2),
            'total_market_revenue' => $marketPerformance->sum('total_revenue'),
        ];
    }

    /**
     * Get efficiency metrics
     */
    private function getEfficiencyMetrics($userId, $startDate)
    {
        $plantings = Planting::whereHas('field', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->where('crop_type', 'rice')
        ->where('planting_date', '>=', $startDate)
        ->with(['plantingStages', 'harvests'])
        ->get();

        // Time efficiency
        $averageGrowthCycleDays = $plantings->map(function ($planting) {
            if ($planting->actual_harvest_date && $planting->planting_date) {
                return $planting->planting_date->diffInDays($planting->actual_harvest_date);
            }
            return null;
        })->filter()->avg();

        // Stage completion efficiency
        $stageEfficiency = $plantings->map(function ($planting) {
            $totalStages = $planting->plantingStages()->count();
            $completedOnTime = $planting->plantingStages()
                ->where('status', 'completed')
                ->whereRaw('completed_at <= DATE_ADD(started_at, INTERVAL typical_duration_days DAY)')
                ->count();
            
            return $totalStages > 0 ? ($completedOnTime / $totalStages) * 100 : 0;
        })->avg();

        // Resource efficiency
        $resourceEfficiency = $this->calculateResourceEfficiency($userId, $startDate);

        return [
            'average_growth_cycle_days' => round($averageGrowthCycleDays, 1),
            'stage_completion_efficiency' => round($stageEfficiency, 2),
            'resource_efficiency' => $resourceEfficiency,
            'yield_efficiency' => $this->calculateYieldEfficiency($plantings),
            'cost_efficiency' => $this->calculateCostEfficiency($userId, $startDate),
        ];
    }

    /**
     * Get growth trends
     */
    private function getGrowthTrends($userId, $startDate)
    {
        $monthlyData = collect();
        
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthStart = $month->copy()->startOfMonth();
            $monthEnd = $month->copy()->endOfMonth();

            $plantingsCount = Planting::whereHas('field', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->where('crop_type', 'rice')
            ->whereBetween('planting_date', [$monthStart, $monthEnd])
            ->count();

            $harvestsCount = Harvest::whereHas('planting.field', function ($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->whereBetween('harvest_date', [$monthStart, $monthEnd])
            ->count();

            $monthlyData->push([
                'month' => $month->format('Y-m'),
                'plantings' => $plantingsCount,
                'harvests' => $harvestsCount,
            ]);
        }

        return [
            'monthly_trends' => $monthlyData,
            'growth_rate' => $this->calculateGrowthRate($monthlyData),
            'seasonal_patterns' => $this->identifySeasonalPatterns($monthlyData),
        ];
    }

    /**
     * Helper methods
     */
    private function assessWeatherRisk($weatherImpact)
    {
        $avgSuitability = collect($weatherImpact)->avg('weather_suitability_score');
        
        if ($avgSuitability >= 80) return 'low';
        if ($avgSuitability >= 60) return 'moderate';
        if ($avgSuitability >= 40) return 'high';
        return 'very_high';
    }

    private function calculateResourceEfficiency($userId, $startDate)
    {
        // Simplified resource efficiency calculation
        return [
            'water_efficiency' => 75, // Placeholder
            'fertilizer_efficiency' => 82, // Placeholder
            'labor_efficiency' => 68, // Placeholder
        ];
    }

    private function calculateYieldEfficiency($plantings)
    {
        $actualYields = $plantings->map(function ($planting) {
            return $planting->harvests->sum('yield');
        });
        
        $expectedYields = $plantings->map(function ($planting) {
            return $planting->getEstimatedYield();
        });

        $totalActual = $actualYields->sum();
        $totalExpected = $expectedYields->sum();

        return $totalExpected > 0 ? round(($totalActual / $totalExpected) * 100, 2) : 0;
    }

    private function calculateCostEfficiency($userId, $startDate)
    {
        // Simplified cost efficiency calculation
        return [
            'cost_per_kg' => 2.5, // Placeholder
            'cost_per_hectare' => 1500, // Placeholder
            'efficiency_score' => 78, // Placeholder
        ];
    }

    private function calculateGrowthRate($monthlyData)
    {
        $firstHalf = $monthlyData->take(6);
        $secondHalf = $monthlyData->skip(6);

        $firstHalfAvg = $firstHalf->avg('plantings');
        $secondHalfAvg = $secondHalf->avg('plantings');

        return $firstHalfAvg > 0 ? round((($secondHalfAvg - $firstHalfAvg) / $firstHalfAvg) * 100, 2) : 0;
    }

    private function identifySeasonalPatterns($monthlyData)
    {
        // Simplified seasonal pattern identification
        $wetSeasonMonths = [5, 6, 7, 8, 9, 10]; // May to October
        $drySeasonMonths = [11, 12, 1, 2, 3, 4]; // November to April

        $wetSeasonPlantings = $monthlyData->filter(function ($data) use ($wetSeasonMonths) {
            $month = (int) date('n', strtotime($data['month'] . '-01'));
            return in_array($month, $wetSeasonMonths);
        })->avg('plantings');

        $drySeasonPlantings = $monthlyData->filter(function ($data) use ($drySeasonMonths) {
            $month = (int) date('n', strtotime($data['month'] . '-01'));
            return in_array($month, $drySeasonMonths);
        })->avg('plantings');

        return [
            'wet_season_avg' => round($wetSeasonPlantings, 2),
            'dry_season_avg' => round($drySeasonPlantings, 2),
            'preferred_season' => $wetSeasonPlantings > $drySeasonPlantings ? 'wet' : 'dry',
        ];
    }
}