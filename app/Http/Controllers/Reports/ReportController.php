<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Services\FinancialService;
use App\Services\InventoryService;
use App\Services\LaborService;
use App\Services\WeatherService;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ReportController extends Controller
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
     * Display the reports dashboard
     */
    public function index()
    {
        $user = Auth::user();
        $farms = Farm::where('user_id', $user->id)->get();
        
        return response()->json([
            'farms' => $farms,
            'available_reports' => [
                'financial' => 'Financial Reports',
                'inventory' => 'Inventory Reports',
                'labor' => 'Labor Reports',
                'weather' => 'Weather Reports',
                'production' => 'Production Reports',
                'comprehensive' => 'Comprehensive Reports',
            ]
        ]);
    }

    /**
     * Generate financial report
     */
    public function generateFinancialReport(Request $request)
    {
        $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'report_type' => 'in:summary,detailed,trends,budget_analysis',
        ]);

        $farmId = $request->farm_id;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $reportType = $request->report_type ?? 'summary';

        $report = [];

        switch ($reportType) {
            case 'summary':
                $report = $this->financialService->generateFinancialReport($farmId, $startDate, $endDate, 'summary');
                break;
                
            case 'detailed':
                $report = $this->financialService->generateFinancialReport($farmId, $startDate, $endDate, 'detailed');
                break;
                
            case 'trends':
                $months = $startDate->diffInMonths($endDate) + 1;
                $report = [
                    'monthly_trends' => $this->financialService->getMonthlyTrends($farmId, $months),
                    'crop_profitability' => $this->financialService->getCropProfitabilityAnalysis($farmId, $startDate->diffInDays($endDate)),
                    'kpis' => $this->financialService->getFinancialKPIs($farmId, $startDate->diffInDays($endDate)),
                ];
                break;
                
            case 'budget_analysis':
                // This would require budget data from the request
                $budgetData = $request->budget_data ?? [];
                $report = $this->financialService->getBudgetAnalysis($farmId, $budgetData, $startDate->diffInDays($endDate));
                break;
        }

        return response()->json([
            'report' => $report,
            'generated_at' => now(),
            'report_type' => $reportType,
        ]);
    }

    /**
     * Generate inventory report
     */
    public function generateInventoryReport(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'report_type' => 'in:summary,detailed,low_stock,valuation',
        ]);

        $userId = $request->user_id;
        $reportType = $request->report_type ?? 'summary';

        $report = [];

        switch ($reportType) {
            case 'summary':
                $report = [
                    'stats' => $this->inventoryService->getInventoryStats($userId),
                    'categories' => $this->inventoryService->getCategories(),
                ];
                break;
                
            case 'detailed':
                $report = [
                    'items' => $this->inventoryService->getUserInventory($userId),
                    'stats' => $this->inventoryService->getInventoryStats($userId),
                ];
                break;
                
            case 'low_stock':
                $report = [
                    'low_stock_items' => $this->inventoryService->getLowStockItems($userId),
                    'recommendations' => 'Review minimum stock levels and consider reordering items below threshold.',
                ];
                break;
                
            case 'valuation':
                $items = $this->inventoryService->getUserInventory($userId);
                $totalValue = $items->sum(function ($item) {
                    return $item->quantity * $item->price;
                });
                
                $report = [
                    'total_inventory_value' => $totalValue,
                    'items_by_value' => $items->sortByDesc(function ($item) {
                        return $item->quantity * $item->price;
                    })->values(),
                    'category_values' => $items->groupBy('category')->map(function ($categoryItems) {
                        return $categoryItems->sum(function ($item) {
                            return $item->quantity * $item->price;
                        });
                    }),
                ];
                break;
        }

        return response()->json([
            'report' => $report,
            'generated_at' => now(),
            'report_type' => $reportType,
        ]);
    }

    /**
     * Generate labor report
     */
    public function generateLaborReport(Request $request)
    {
        $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'period_days' => 'integer|min:1|max:365',
            'report_type' => 'in:summary,detailed,productivity,costs',
        ]);

        $farmId = $request->farm_id;
        $periodDays = $request->period_days ?? 30;
        $reportType = $request->report_type ?? 'summary';

        $report = [];

        switch ($reportType) {
            case 'summary':
                $report = [
                    'laborers' => $this->laborService->getLaborers($farmId),
                    'cost_summary' => $this->laborService->getLaborCostSummary($farmId, $periodDays),
                ];
                break;
                
            case 'detailed':
                $laborers = $this->laborService->getLaborers($farmId);
                $detailedReport = [];
                
                foreach ($laborers as $laborer) {
                    $detailedReport[] = [
                        'laborer' => $laborer,
                        'wages' => $this->laborService->getLaborerWages($laborer->id, $periodDays),
                    ];
                }
                
                $report = [
                    'detailed_laborers' => $detailedReport,
                    'period_days' => $periodDays,
                ];
                break;
                
            case 'productivity':
                $report = [
                    'productivity_metrics' => $this->laborService->getLaborProductivity($farmId, $periodDays),
                    'available_laborers' => $this->laborService->getAvailableLaborers($farmId),
                ];
                break;
                
            case 'costs':
                $startDate = now()->subDays($periodDays);
                $endDate = now();
                
                $report = [
                    'cost_analysis' => $this->laborService->calculateLaborCost($farmId, $startDate, $endDate),
                    'cost_summary' => $this->laborService->getLaborCostSummary($farmId, $periodDays),
                ];
                break;
        }

        return response()->json([
            'report' => $report,
            'generated_at' => now(),
            'report_type' => $reportType,
        ]);
    }

    /**
     * Generate weather report
     */
    public function generateWeatherReport(Request $request)
    {
        $request->validate([
            'field_id' => 'required|exists:fields,id',
            'period_days' => 'integer|min:1|max:365',
            'report_type' => 'in:summary,analytics,alerts,recommendations',
        ]);

        $fieldId = $request->field_id;
        $periodDays = $request->period_days ?? 30;
        $reportType = $request->report_type ?? 'summary';

        $field = \App\Models\Field::findOrFail($fieldId);
        $report = [];

        switch ($reportType) {
            case 'summary':
                $report = [
                    'field' => $field,
                    'weather_stats' => $this->weatherService->getFieldWeatherStats($field, $periodDays),
                    'latest_weather' => $field->latestWeather,
                ];
                break;
                
            case 'analytics':
                $report = [
                    'rice_analytics' => $this->weatherService->getRiceWeatherAnalytics($field, $periodDays),
                    'weather_stats' => $this->weatherService->getFieldWeatherStats($field, $periodDays),
                ];
                break;
                
            case 'alerts':
                $report = [
                    'current_alerts' => $this->weatherService->getWeatherAlerts($field),
                    'field_info' => $field,
                ];
                break;
                
            case 'recommendations':
                $report = [
                    'recommendations' => $this->weatherService->getRiceFarmingRecommendations($field),
                    'weather_analytics' => $this->weatherService->getRiceWeatherAnalytics($field, 7),
                ];
                break;
        }

        return response()->json([
            'report' => $report,
            'generated_at' => now(),
            'report_type' => $reportType,
        ]);
    }

    /**
     * Generate production report
     */
    public function generateProductionReport(Request $request)
    {
        $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $farmId = $request->farm_id;
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);

        $farm = Farm::findOrFail($farmId);
        
        // Get plantings in the period
        $plantings = \App\Models\Planting::whereHas('field', function ($q) use ($farmId) {
            $q->where('farm_id', $farmId);
        })
        ->whereBetween('planting_date', [$startDate, $endDate])
        ->with(['field', 'harvests', 'expenses', 'plantingStages.riceGrowthStage'])
        ->get();

        // Get harvests in the period
        $harvests = \App\Models\Harvest::whereHas('planting.field', function ($q) use ($farmId) {
            $q->where('farm_id', $farmId);
        })
        ->whereBetween('harvest_date', [$startDate, $endDate])
        ->with(['planting.field', 'sales'])
        ->get();

        $report = [
            'farm' => $farm,
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'summary' => [
                'total_plantings' => $plantings->count(),
                'total_harvests' => $harvests->count(),
                'total_area_planted' => $plantings->sum(function ($planting) {
                    return $planting->field->size_hectares;
                }),
                'total_yield' => $harvests->sum('yield_kg'),
                'average_yield_per_hectare' => $plantings->count() > 0 ? 
                    $harvests->sum('yield_kg') / $plantings->sum(function ($p) { return $p->field->size_hectares; }) : 0,
            ],
            'crop_breakdown' => $plantings->groupBy('crop_type')->map(function ($cropPlantings) {
                $cropHarvests = $cropPlantings->flatMap->harvests;
                return [
                    'plantings_count' => $cropPlantings->count(),
                    'total_area' => $cropPlantings->sum(function ($p) { return $p->field->size_hectares; }),
                    'total_yield' => $cropHarvests->sum('yield_kg'),
                    'average_yield_per_hectare' => $cropPlantings->sum(function ($p) { return $p->field->size_hectares; }) > 0 ?
                        $cropHarvests->sum('yield_kg') / $cropPlantings->sum(function ($p) { return $p->field->size_hectares; }) : 0,
                ];
            }),
            'plantings' => $plantings,
            'harvests' => $harvests,
        ];

        return response()->json([
            'report' => $report,
            'generated_at' => now(),
        ]);
    }

    /**
     * Generate comprehensive report
     */
    public function generateComprehensiveReport(Request $request)
    {
        $request->validate([
            'farm_id' => 'required|exists:farms,id',
            'period_days' => 'integer|min:1|max:365',
        ]);

        $farmId = $request->farm_id;
        $periodDays = $request->period_days ?? 30;
        $userId = Auth::id();

        $farm = Farm::findOrFail($farmId);
        $startDate = now()->subDays($periodDays);
        $endDate = now();

        $report = [
            'farm' => $farm,
            'period' => [
                'days' => $periodDays,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
            ],
            'financial_summary' => $this->financialService->getFarmFinancialSummary($farmId, $periodDays),
            'inventory_stats' => $this->inventoryService->getInventoryStats($userId),
            'labor_summary' => $this->laborService->getLaborCostSummary($farmId, $periodDays),
            'productivity_metrics' => $this->laborService->getLaborProductivity($farmId, $periodDays),
            'financial_kpis' => $this->financialService->getFinancialKPIs($farmId, $periodDays),
            'crop_profitability' => $this->financialService->getCropProfitabilityAnalysis($farmId, $periodDays),
            'low_stock_items' => $this->inventoryService->getLowStockItems($userId),
        ];

        // Add weather data for each field
        $weatherSummary = [];
        foreach ($farm->fields as $field) {
            $weatherSummary[$field->id] = [
                'field_name' => $field->name,
                'weather_stats' => $this->weatherService->getFieldWeatherStats($field, $periodDays),
                'alerts' => $this->weatherService->getWeatherAlerts($field),
            ];
        }
        $report['weather_summary'] = $weatherSummary;

        return response()->json([
            'report' => $report,
            'generated_at' => now(),
        ]);
    }

    /**
     * Export report to PDF (placeholder)
     */
    public function exportToPdf(Request $request)
    {
        // This would integrate with a PDF generation library like DomPDF or Snappy
        return response()->json([
            'message' => 'PDF export functionality would be implemented here',
            'status' => 'not_implemented'
        ]);
    }

    /**
     * Export report to Excel (placeholder)
     */
    public function exportToExcel(Request $request)
    {
        // This would integrate with Laravel Excel package
        return response()->json([
            'message' => 'Excel export functionality would be implemented here',
            'status' => 'not_implemented'
        ]);
    }

    /**
     * Schedule automated report
     */
    public function scheduleReport(Request $request)
    {
        $request->validate([
            'report_type' => 'required|string',
            'farm_id' => 'required|exists:farms,id',
            'frequency' => 'required|in:daily,weekly,monthly',
            'email' => 'required|email',
        ]);

        // This would integrate with Laravel's job scheduling system
        return response()->json([
            'message' => 'Report scheduling functionality would be implemented here',
            'status' => 'not_implemented',
            'scheduled_report' => $request->all()
        ]);
    }
}