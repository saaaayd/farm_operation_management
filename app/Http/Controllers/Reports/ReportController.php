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
     * Export report to PDF
     */
    public function exportToPdf(Request $request)
    {
        try {
            $reportType = $request->get('type', 'financial');
            $period = $request->get('period', '365');
            $userId = auth()->id();

            // Get report data
            $reportData = $this->getReportData($reportType, $userId, $period);

            // Check if PDF library is available
            if (!class_exists('\Barryvdh\DomPDF\Facade\Pdf') && !class_exists('\Snappy\Pdf')) {
                return response()->json([
                    'message' => 'PDF export requires a PDF library. Please install barryvdh/laravel-dompdf or knplabs/knp-snappy.',
                    'status' => 'library_required',
                    'data' => $reportData, // Return data as JSON fallback
                ], 503);
            }

            // Generate PDF using available library
            // This is a template - actual implementation depends on the library used
            $html = view('reports.' . $reportType, ['data' => $reportData])->render();

            if (class_exists('\Barryvdh\DomPDF\Facade\Pdf')) {
                $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
                return $pdf->download('report-' . $reportType . '-' . now()->format('Y-m-d') . '.pdf');
            } elseif (class_exists('\Snappy\Pdf')) {
                $pdf = \Snappy\Pdf::loadHTML($html);
                return $pdf->download('report-' . $reportType . '-' . now()->format('Y-m-d') . '.pdf');
            }

            // Fallback: return JSON data
            return response()->json([
                'message' => 'PDF generation failed. Data returned as JSON.',
                'status' => 'fallback',
                'data' => $reportData,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to export PDF',
                'error' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    private function getReportData($type, $userId, $period)
    {
        $startDate = now()->subDays($period);
        
        switch ($type) {
            case 'financial':
                return $this->getFinancialReportData($userId, $startDate);
            case 'crop_yield':
                return $this->getCropYieldReportData($userId, $startDate);
            case 'weather':
                return $this->getWeatherReportData($userId, $startDate);
            default:
                return ['message' => 'Unknown report type'];
        }
    }

    private function getFinancialReportData($userId, $startDate)
    {
        // Get user's farm
        $farm = \App\Models\Farm::where('user_id', $userId)->first();
        
        if (!$farm) {
            return ['message' => 'No farm found for user'];
        }

        $endDate = now();
        
        // Get financial data directly
        $expenses = \App\Models\Expense::where('user_id', $userId)
            ->where('date', '>=', $startDate)
            ->where('date', '<=', $endDate)
            ->get();

        $sales = \App\Models\Sale::where('user_id', $userId)
            ->where('sale_date', '>=', $startDate)
            ->where('sale_date', '<=', $endDate)
            ->get();

        $totalExpenses = $expenses->sum('amount');
        $totalRevenue = $sales->sum('total_amount');
        $netProfit = $totalRevenue - $totalExpenses;

        return [
            'total_expenses' => $totalExpenses,
            'total_revenue' => $totalRevenue,
            'net_profit' => $netProfit,
            'expenses_by_category' => $expenses->groupBy('category')->map->sum('amount'),
            'period_start' => $startDate,
            'period_end' => $endDate,
        ];
    }

    private function getCropYieldReportData($userId, $startDate)
    {
        // Get crop yield data
        $fields = \App\Models\Field::where('user_id', $userId)->get();
        $plantings = \App\Models\Planting::whereHas('field', function ($query) use ($userId) {
            $query->where('user_id', $userId);
        })
        ->where('planting_date', '>=', $startDate)
        ->with(['harvests', 'field', 'riceVariety'])
        ->get();

        $yieldData = [];
        foreach ($plantings as $planting) {
            $totalYield = $planting->harvests->sum('yield');
            $yieldData[] = [
                'field_name' => $planting->field->name ?? 'Unknown',
                'variety' => $planting->riceVariety->name ?? 'Unknown',
                'planting_date' => $planting->planting_date,
                'area_planted' => $planting->area_planted,
                'total_yield' => $totalYield,
                'yield_per_hectare' => $planting->area_planted > 0 ? $totalYield / $planting->area_planted : 0,
            ];
        }

        return [
            'yield_data' => $yieldData,
            'total_yield' => collect($yieldData)->sum('total_yield'),
            'average_yield_per_hectare' => collect($yieldData)->avg('yield_per_hectare'),
            'period_start' => $startDate,
            'period_end' => now(),
        ];
    }

    private function getWeatherReportData($userId, $startDate)
    {
        $fields = \App\Models\Field::where('user_id', $userId)->get();
        $weatherData = [];

        foreach ($fields as $field) {
            $request = new Request([
                'field_id' => $field->id,
                'period_days' => now()->diffInDays($startDate),
                'report_type' => 'summary',
            ]);
            
            $response = $this->generateWeatherReport($request);
            $data = json_decode($response->getContent(), true);
            
            if (isset($data['report'])) {
                $weatherData[] = [
                    'field_name' => $field->name,
                    'field_id' => $field->id,
                    'weather_report' => $data['report'],
                ];
            }
        }

        return [
            'fields_weather' => $weatherData,
            'period_start' => $startDate,
            'period_end' => now(),
        ];
    }

    /**
     * Export report to Excel
     */
    public function exportToExcel(Request $request)
    {
        try {
            $reportType = $request->get('type', 'financial');
            $period = $request->get('period', '365');
            $userId = auth()->id();

            // Get report data
            $reportData = $this->getReportData($reportType, $userId, $period);

            // Check if Laravel Excel is available
            if (!class_exists('\Maatwebsite\Excel\Facades\Excel')) {
                // Fallback: return CSV format
                return $this->exportToCsv($reportData, $reportType);
            }

            // Generate Excel using Laravel Excel
            $exportClass = $this->getExportClass($reportType);
            
            if (!$exportClass) {
                return response()->json([
                    'message' => 'Export class not found for report type: ' . $reportType,
                    'status' => 'error'
                ], 400);
            }

            $filename = 'report-' . $reportType . '-' . now()->format('Y-m-d') . '.xlsx';
            
            return \Maatwebsite\Excel\Facades\Excel::download(
                new $exportClass($reportData),
                $filename
            );

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to export Excel',
                'error' => $e->getMessage(),
                'status' => 'error'
            ], 500);
        }
    }

    private function exportToCsv($data, $reportType)
    {
        $filename = 'report-' . $reportType . '-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Write headers if data is structured
            if (is_array($data) && !empty($data)) {
                if (isset($data[0]) && is_array($data[0])) {
                    fputcsv($file, array_keys($data[0]));
                }
                
                // Write data rows
                foreach ($data as $row) {
                    if (is_array($row)) {
                        fputcsv($file, array_values($row));
                    }
                }
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getExportClass($reportType)
    {
        $classes = [
            'financial' => 'App\Exports\FinancialReportExport',
            'crop_yield' => 'App\Exports\CropYieldReportExport',
            'weather' => 'App\Exports\WeatherReportExport',
        ];

        $className = $classes[$reportType] ?? null;
        
        if ($className && class_exists($className)) {
            return $className;
        }

        return null;
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