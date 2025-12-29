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
                    $harvests->sum('yield_kg') / $plantings->sum(function ($p) {
                        return $p->field->size_hectares;
                    }) : 0,
            ],
            'crop_breakdown' => $plantings->groupBy('crop_type')->map(function ($cropPlantings) {
                $cropHarvests = $cropPlantings->flatMap->harvests;
                return [
                    'plantings_count' => $cropPlantings->count(),
                    'total_area' => $cropPlantings->sum(function ($p) {
                        return $p->field->size_hectares;
                    }),
                    'total_yield' => $cropHarvests->sum('yield_kg'),
                    'average_yield_per_hectare' => $cropPlantings->sum(function ($p) {
                        return $p->field->size_hectares;
                    }) > 0 ?
                        $cropHarvests->sum('yield_kg') / $cropPlantings->sum(function ($p) {
                            return $p->field->size_hectares;
                        }) : 0,
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

        $callback = function () use ($data) {
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

    /**
     * Get financial report for frontend
     */
    public function getFinancialReport(Request $request)
    {
        $user = Auth::user();
        $period = (int) ($request->get('period', 365));
        $startDate = now()->subDays($period);
        $endDate = now();

        // Get expenses
        $expenses = \App\Models\Expense::where('user_id', $user->id)
            ->whereBetween('date', [$startDate, $endDate])
            ->get();

        // Get sales/revenue
        $sales = \App\Models\Sale::where('user_id', $user->id)
            ->whereBetween('sale_date', [$startDate, $endDate])
            ->get();

        $totalExpenses = $expenses->sum('amount');
        $totalRevenue = $sales->sum('total_amount');
        $netProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        // Expense breakdown by category
        $expenseBreakdown = $expenses->groupBy('category')->map(function ($categoryExpenses, $category) use ($totalExpenses) {
            $amount = $categoryExpenses->sum('amount');
            return [
                'category' => ucfirst(str_replace('_', ' ', $category)),
                'amount' => $amount,
                'percentage' => $totalExpenses > 0 ? round(($amount / $totalExpenses) * 100, 1) : 0,
            ];
        })->values();

        // Add colors to expense breakdown
        $colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6', '#6B7280', '#EC4899', '#14B8A6'];
        $expenseBreakdown = $expenseBreakdown->map(function ($item, $index) use ($colors) {
            $item['color'] = $colors[$index % count($colors)];
            return $item;
        });

        // Monthly revenue trends
        $monthlyRevenue = [];
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $monthKey = $currentDate->format('Y-m');
            $monthRevenue = $sales->filter(function ($sale) use ($monthKey) {
                return Carbon::parse($sale->sale_date)->format('Y-m') === $monthKey;
            })->sum('total_amount');

            $monthlyRevenue[] = [
                'month' => $currentDate->format('M Y'),
                'revenue' => $monthRevenue,
            ];
            $currentDate->addMonth();
        }

        // Recent transactions (combine expenses and sales)
        $transactions = collect();
        foreach ($expenses->take(10) as $expense) {
            $transactions->push([
                'id' => 'expense_' . $expense->id,
                'date' => $expense->date,
                'description' => $expense->description,
                'category' => ucfirst(str_replace('_', ' ', $expense->category)),
                'type' => 'expense',
                'amount' => $expense->amount,
            ]);
        }
        foreach ($sales->take(10) as $sale) {
            $transactions->push([
                'id' => 'sale_' . $sale->id,
                'date' => $sale->sale_date,
                'description' => $sale->product_name ?? 'Sale',
                'category' => 'Crop Sales',
                'type' => 'income',
                'amount' => $sale->total_amount,
            ]);
        }
        $transactions = $transactions->sortByDesc('date')->take(10)->values();

        return response()->json([
            'data' => [
                'financial_summary' => [
                    'total_revenue' => round($totalRevenue, 2),
                    'total_expenses' => round($totalExpenses, 2),
                    'net_profit' => round($netProfit, 2),
                    'profit_margin' => round($profitMargin, 1),
                ],
                'expense_breakdown' => $expenseBreakdown,
                'revenue_trends' => $monthlyRevenue,
                'transactions' => $transactions,
            ],
        ]);
    }

    /**
     * Get crop yield report for frontend
     */
    public function getCropYieldReport(Request $request)
    {
        $user = Auth::user();
        $period = (int) ($request->get('period', 365));
        $startDate = now()->subDays($period);
        $endDate = now();

        // Get filter parameters
        $cropFilter = $request->get('crop', '');
        $fieldFilter = $request->get('field', '');

        // Get harvests with filters
        $harvestsQuery = \App\Models\Harvest::whereHas('planting.field', function ($q) use ($user, $fieldFilter) {
            $q->where('user_id', $user->id);
            if ($fieldFilter) {
                $q->where('id', $fieldFilter);
            }
        })
            ->whereBetween('harvest_date', [$startDate, $endDate])
            ->with(['planting.field', 'planting.riceVariety']);

        // Apply crop filter
        if ($cropFilter) {
            $harvestsQuery->whereHas('planting', function ($q) use ($cropFilter) {
                $q->where('rice_variety_id', $cropFilter)
                    ->orWhereHas('riceVariety', function ($rq) use ($cropFilter) {
                        $rq->where('name', 'like', "%{$cropFilter}%");
                    });
            });
        }

        $harvests = $harvestsQuery->get();

        $totalYield = $harvests->sum('yield');

        // Get total area from fields (apply filter if specified)
        $fieldsQuery = \App\Models\Field::where('user_id', $user->id);
        if ($fieldFilter) {
            $fieldsQuery->where('id', $fieldFilter);
        }
        $fields = $fieldsQuery->get();
        $totalArea = $fields->sum('size_hectares') ?: $fields->sum('size');
        $avgYieldPerHectare = $totalArea > 0 ? $totalYield / $totalArea : 0;

        // Calculate yield increase (compare with previous period)
        $previousStartDate = $startDate->copy()->subDays($period);
        $previousHarvests = \App\Models\Harvest::whereHas('planting.field', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->whereBetween('harvest_date', [$previousStartDate, $startDate])
            ->get();
        $previousTotalYield = $previousHarvests->sum('yield');
        $yieldIncrease = $previousTotalYield > 0 ? (($totalYield - $previousTotalYield) / $previousTotalYield) * 100 : 0;

        // Best performing field
        $fieldPerformance = $harvests->groupBy('planting.field_id')->map(function ($fieldHarvests, $fieldId) {
            $field = $fieldHarvests->first()->planting->field;
            $totalYield = $fieldHarvests->sum('yield');
            $area = $field->size_hectares ?: $field->size;
            return [
                'field_id' => $fieldId,
                'field_name' => $field->name,
                'total_yield' => $totalYield,
                'area' => $area,
                'yield_per_hectare' => $area > 0 ? $totalYield / $area : 0,
            ];
        });
        $bestField = $fieldPerformance->sortByDesc('yield_per_hectare')->first();

        // Crop comparison
        $cropComparison = $harvests->groupBy(function ($harvest) {
            return $harvest->planting->riceVariety->name ?? $harvest->planting->crop_type ?? 'Unknown';
        })->map(function ($cropHarvests, $cropName) {
            $plantings = $cropHarvests->pluck('planting')->unique('id');
            $totalArea = $plantings->sum(function ($planting) {
                return $planting->field->size_hectares ?: $planting->field->size;
            });
            $totalYield = $cropHarvests->sum('yield');
            $yieldPerHectare = $totalArea > 0 ? $totalYield / $totalArea : 0;

            // Get average market price from sales
            $sales = $cropHarvests->flatMap->sales;
            $marketPrice = $sales->count() > 0 ? $sales->avg('price_per_unit') : 0;
            $totalValue = $totalYield * $marketPrice;

            return [
                'name' => $cropName,
                'area' => round($totalArea, 2),
                'total_yield' => round($totalYield, 2),
                'yield_per_hectare' => round($yieldPerHectare, 2),
                'market_price' => round($marketPrice, 2),
                'total_value' => round($totalValue, 2),
            ];
        })->values();

        // Yield trends (monthly)
        $monthlyYield = [];
        $currentDate = clone $startDate;
        while ($currentDate <= $endDate) {
            $monthKey = $currentDate->format('Y-m');
            $monthYield = $harvests->filter(function ($harvest) use ($monthKey) {
                return Carbon::parse($harvest->harvest_date)->format('Y-m') === $monthKey;
            })->sum('yield');

            $monthlyYield[] = [
                'month' => $currentDate->format('M Y'),
                'yield' => $monthYield,
            ];
            $currentDate->addMonth();
        }

        return response()->json([
            'data' => [
                'yield_summary' => [
                    'total_yield' => round($totalYield, 2),
                    'avg_yield_per_hectare' => round($avgYieldPerHectare, 2),
                    'yield_increase' => round($yieldIncrease, 1),
                    'best_field' => $bestField ? $bestField['field_name'] : 'N/A',
                ],
                'field_performance' => $fieldPerformance->values(),
                'crop_comparison' => $cropComparison,
                'yield_trends' => $monthlyYield,
            ],
        ]);
    }

    /**
     * Get weather report for frontend
     */
    public function getWeatherReport(Request $request)
    {
        $user = Auth::user();
        $period = (int) ($request->get('period', 365));
        $days = min($period, 365); // Limit to 365 days for weather data

        // Get user's fields
        $fieldsQuery = \App\Models\Field::where('user_id', $user->id);

        if ($request->has('field_id') && $request->field_id) {
            $fieldsQuery->where('id', $request->field_id);
        }

        $fields = $fieldsQuery->get();

        if ($fields->isEmpty()) {
            return response()->json([
                'data' => [
                    'weather_summary' => [
                        'avg_temperature' => 0,
                        'total_rainfall' => 0,
                        'avg_wind_speed' => 0,
                        'sunshine_hours' => 0,
                    ],
                    'temperature_trends' => [],
                    'rainfall_distribution' => [],
                    'gdd_data' => [
                        'today' => 0,
                        'week' => 0,
                        'month' => 0,
                        'season' => 0,
                    ],
                    'weather_events' => [],
                ],
            ]);
        }

        // Get weather data from WeatherLog model
        $weatherData = \App\Models\WeatherLog::whereIn('field_id', $fields->pluck('id'))
            ->where('recorded_at', '>=', now()->subDays($days))
            ->orderBy('recorded_at', 'desc')
            ->get();

        // Calculate summary statistics
        $avgTemperature = $weatherData->avg('temperature') ?: 0;
        $avgHumidity = $weatherData->avg('humidity') ?: 0;
        $avgWindSpeed = $weatherData->avg('wind_speed') ?: 0;

        // Estimate rainfall from conditions (rainy/stormy conditions)
        $rainyDays = $weatherData->filter(function ($record) {
            return in_array($record->conditions, ['rainy', 'stormy']);
        })->unique(function ($record) {
            return Carbon::parse($record->recorded_at)->format('Y-m-d');
        })->count();
        $estimatedRainfall = $rainyDays * 5; // Rough estimate: 5mm per rainy day

        // Estimate sunshine hours (clear days = more sunshine)
        $clearDays = $weatherData->filter(function ($record) {
            return $record->conditions === 'clear';
        })->unique(function ($record) {
            return Carbon::parse($record->recorded_at)->format('Y-m-d');
        })->count();
        $estimatedSunshineHours = $clearDays * 8; // Rough estimate: 8 hours per clear day

        // Temperature trends (daily average)
        $temperatureTrends = $weatherData->groupBy(function ($record) {
            return Carbon::parse($record->recorded_at)->format('Y-m-d');
        })->map(function ($dayRecords, $date) {
            return [
                'date' => $date,
                'temperature' => round($dayRecords->avg('temperature') ?: 0, 1),
            ];
        })->sortBy('date')->values();

        // Humidity distribution (daily average) - used as proxy for rainfall
        $humidityDistribution = $weatherData->groupBy(function ($record) {
            return Carbon::parse($record->recorded_at)->format('Y-m-d');
        })->map(function ($dayRecords, $date) {
            $avgHumidity = $dayRecords->avg('humidity') ?: 0;
            // Convert high humidity to estimated rainfall (rough approximation)
            $estimatedRainfall = $avgHumidity > 80 ? ($avgHumidity - 70) * 0.5 : 0;
            return [
                'date' => $date,
                'rainfall' => round($estimatedRainfall, 2),
            ];
        })->sortBy('date')->values();

        // Growing Degree Days (GDD) - simplified calculation
        $baseTemp = 10; // Base temperature for rice
        $todayGDD = 0;
        $weekGDD = 0;
        $monthGDD = 0;
        $seasonGDD = 0;

        $today = now();
        $weekAgo = now()->subDays(7);
        $monthAgo = now()->subDays(30);
        $seasonStart = now()->subDays($days);

        foreach ($weatherData as $record) {
            $recordDate = Carbon::parse($record->recorded_at);
            $temp = $record->temperature ?: 0;
            $gdd = max(0, $temp - $baseTemp);

            if ($recordDate->isToday()) {
                $todayGDD += $gdd;
            }
            if ($recordDate >= $weekAgo) {
                $weekGDD += $gdd;
            }
            if ($recordDate >= $monthAgo) {
                $monthGDD += $gdd;
            }
            if ($recordDate >= $seasonStart) {
                $seasonGDD += $gdd;
            }
        }

        // Weather events (significant weather occurrences)
        $weatherEvents = [];
        $significantEvents = $weatherData->filter(function ($record) {
            return in_array($record->conditions, ['stormy', 'rainy']) ||
                ($record->wind_speed ?? 0) > 15 ||
                ($record->temperature ?? 0) > 35 ||
                ($record->temperature ?? 0) < 5;
        })
            ->groupBy(function ($record) {
                return Carbon::parse($record->recorded_at)->format('Y-m-d');
            })
            ->map(function ($dayRecords) {
                // Pick the most significant event for the day
                // Priority: Storm > Wind > Heat/Cold > Rain
                // Sort by arbitrary priority or specific metrics
                return $dayRecords->sortByDesc(function ($record) {
                    if ($record->conditions === 'stormy')
                        return 100;
                    if (($record->wind_speed ?? 0) > 15)
                        return 80;
                    if (($record->temperature ?? 0) > 35)
                        return 60;
                    if (($record->temperature ?? 0) < 5)
                        return 60;
                    return 40;
                })->first();
            })
            ->take(5);

        foreach ($significantEvents as $record) {
            $eventType = 'weather';
            $title = 'Weather Event';
            $description = '';

            if ($record->conditions === 'stormy') {
                $eventType = 'storm';
                $title = 'Storm Warning';
                $description = 'Stormy conditions detected';
            } elseif ($record->conditions === 'rainy') {
                $eventType = 'rain';
                $title = 'Rainfall Event';
                $description = 'Rainy conditions with ' . round($record->humidity ?? 0, 0) . '% humidity';
            } elseif (($record->wind_speed ?? 0) > 15) {
                $eventType = 'wind';
                $title = 'Strong Winds';
                $description = 'Wind speeds reached ' . round($record->wind_speed ?? 0, 1) . ' km/h';
            } elseif (($record->temperature ?? 0) > 35) {
                $eventType = 'heat';
                $title = 'High Temperature';
                $description = 'Temperature reached ' . round($record->temperature ?? 0, 1) . '°C';
            } elseif (($record->temperature ?? 0) < 5) {
                $eventType = 'frost';
                $title = 'Low Temperature';
                $description = 'Temperature dropped to ' . round($record->temperature ?? 0, 1) . '°C';
            }

            $weatherEvents[] = [
                'id' => 'event_' . ($record->id ?? uniqid()),
                'type' => $eventType,
                'title' => $title,
                'description' => $description,
                'date' => $record->recorded_at,
                'duration' => '1 day',
                'intensity' => 'Moderate',
                'impact' => 'Neutral',
            ];
        }

        // Daily History (Unified table data)
        $dailyHistory = $weatherData->groupBy(function ($record) {
            return Carbon::parse($record->recorded_at)->format('Y-m-d');
        })->map(function ($dayRecords, $date) {
            $avgTemp = $dayRecords->avg('temperature') ?: 0;
            $avgHumidity = $dayRecords->avg('humidity') ?: 0;
            $avgWind = $dayRecords->avg('wind_speed') ?: 0;

            // Estimate rainfall (same logic as summary)
            $estimatedRainfall = $avgHumidity > 80 ? ($avgHumidity - 70) * 0.5 : 0;

            // Determine dominant condition
            $conditionCounts = $dayRecords->groupBy('conditions')->map->count();
            $dominantCondition = $conditionCounts->sortDesc()->keys()->first() ?: 'Unknown';

            return [
                'date' => $date,
                'temperature' => round($avgTemp, 1),
                'rainfall' => round($estimatedRainfall, 1),
                'wind_speed' => round($avgWind, 1),
                'humidity' => round($avgHumidity, 0),
                'condition' => $dominantCondition,
            ];
        })->sortByDesc('date')->values();

        return response()->json([
            'data' => [
                'weather_summary' => [
                    'avg_temperature' => round($avgTemperature, 1),
                    'total_rainfall' => round($estimatedRainfall, 1),
                    'avg_wind_speed' => round($avgWindSpeed, 1),
                    'sunshine_hours' => round($estimatedSunshineHours, 1),
                ],
                'temperature_trends' => $temperatureTrends,
                'rainfall_distribution' => $humidityDistribution,
                'daily_history' => $dailyHistory,
                'gdd_data' => [
                    'today' => round($todayGDD, 1),
                    'week' => round($weekGDD, 1),
                    'month' => round($monthGDD, 1),
                    'season' => round($seasonGDD, 1),
                ],
                'weather_events' => $weatherEvents,
            ],
        ]);
    }

    /**
     * Get filter options for crop yield report
     */
    public function getCropYieldFilterOptions()
    {
        $user = Auth::user();

        // Get available seasons/years from harvests
        $harvestYears = \App\Models\Harvest::whereHas('planting.field', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })
            ->selectRaw('EXTRACT(YEAR FROM harvest_date) as year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year')
            ->filter()
            ->values();

        // If no harvests, include current year and previous 2 years
        if ($harvestYears->isEmpty()) {
            $currentYear = now()->year;
            $harvestYears = collect([$currentYear, $currentYear - 1, $currentYear - 2]);
        }

        $seasons = $harvestYears->map(function ($year) {
            return [
                'value' => (string) $year,
                'label' => "{$year} Season"
            ];
        });

        // Get available crop types (rice varieties)
        $riceVarieties = \App\Models\RiceVariety::active()
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(function ($variety) {
                return [
                    'value' => (string) $variety->id,
                    'label' => $variety->name
                ];
            });

        // Get user's fields  
        $fields = \App\Models\Field::where('user_id', $user->id)
            ->select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(function ($field) {
                return [
                    'value' => (string) $field->id,
                    'label' => $field->name
                ];
            });

        return response()->json([
            'data' => [
                'seasons' => $seasons,
                'crops' => $riceVarieties,
                'fields' => $fields,
            ]
        ]);
    }
}