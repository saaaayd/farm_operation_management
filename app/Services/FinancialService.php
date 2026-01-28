<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Sale;
use App\Models\Harvest;
use App\Models\LaborWage;
use App\Models\Farm;
use App\Models\Planting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class FinancialService
{
    /**
     * Get financial summary for a farm
     */
    public function getFarmFinancialSummary($farmId, $period = '30')
    {
        $startDate = now()->subDays($period);
        
        $expenses = $this->getFarmExpenses($farmId, $startDate);
        $sales = $this->getFarmSales($farmId, $startDate);
        $laborCosts = $this->getFarmLaborCosts($farmId, $startDate);
        
        $totalExpenses = $expenses->sum('amount');
        $totalSales = $sales->sum('total_amount');
        $totalLaborCosts = $laborCosts->sum('total_amount');
        $totalCosts = $totalExpenses + $totalLaborCosts;
        
        $netProfit = $totalSales - $totalCosts;
        $profitMargin = $totalSales > 0 ? ($netProfit / $totalSales) * 100 : 0;
        
        return [
            'period_days' => $period,
            'total_revenue' => $totalSales,
            'total_expenses' => $totalExpenses,
            'total_labor_costs' => $totalLaborCosts,
            'total_costs' => $totalCosts,
            'net_profit' => $netProfit,
            'profit_margin' => round($profitMargin, 2),
            'roi' => $totalCosts > 0 ? round(($netProfit / $totalCosts) * 100, 2) : 0,
            'expense_breakdown' => $this->getExpenseBreakdown($expenses),
            'sales_breakdown' => $this->getSalesBreakdown($sales),
        ];
    }

    /**
     * Get farm expenses for a period
     */
    public function getFarmExpenses($farmId, $startDate = null)
    {
        $query = Expense::whereHas('planting.field', function ($q) use ($farmId) {
            $q->where('farm_id', $farmId);
        });

        if ($startDate) {
            $query->where('date', '>=', $startDate);
        }

        return $query->orderBy('date', 'desc')->get();
    }

    /**
     * Get farm sales for a period
     */
    public function getFarmSales($farmId, $startDate = null)
    {
        $query = Sale::whereHas('harvest.planting.field', function ($q) use ($farmId) {
            $q->where('farm_id', $farmId);
        });

        if ($startDate) {
            $query->where('sale_date', '>=', $startDate);
        }

        return $query->with(['harvest.planting', 'buyer'])->orderBy('sale_date', 'desc')->get();
    }

    /**
     * Get farm labor costs for a period
     */
    public function getFarmLaborCosts($farmId, $startDate = null)
    {
        $query = LaborWage::whereHas('laborer', function ($q) use ($farmId) {
            $q->where('farm_id', $farmId);
        });

        if ($startDate) {
            $query->where('payment_date', '>=', $startDate);
        }

        return $query->with(['laborer', 'task'])->orderBy('payment_date', 'desc')->get();
    }

    /**
     * Create expense record
     */
    public function createExpense($expenseData)
    {
        try {
            $expense = Expense::create([
                'description' => $expenseData['description'],
                'amount' => $expenseData['amount'],
                'category' => $expenseData['category'],
                'date' => $expenseData['date'] ?? now(),
                'planting_id' => $expenseData['planting_id'] ?? null,
            ]);

            return $expense;
        } catch (\Exception $e) {
            Log::error('Failed to create expense: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update expense record
     */
    public function updateExpense($expenseId, $expenseData)
    {
        try {
            $expense = Expense::findOrFail($expenseId);
            $expense->update($expenseData);
            return $expense;
        } catch (\Exception $e) {
            Log::error('Failed to update expense: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete expense record
     */
    public function deleteExpense($expenseId)
    {
        try {
            $expense = Expense::findOrFail($expenseId);
            $expense->delete();
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete expense: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get expense breakdown by category
     */
    public function getExpenseBreakdown($expenses)
    {
        return $expenses->groupBy('category')->map(function ($categoryExpenses) {
            return [
                'total' => $categoryExpenses->sum('amount'),
                'count' => $categoryExpenses->count(),
                'percentage' => 0, // Will be calculated later
            ];
        });
    }

    /**
     * Get sales breakdown by crop type
     */
    public function getSalesBreakdown($sales)
    {
        return $sales->groupBy(function ($sale) {
            return $sale->harvest->planting->crop_type ?? 'Unknown';
        })->map(function ($cropSales) {
            return [
                'total_amount' => $cropSales->sum('total_amount'),
                'total_quantity' => $cropSales->sum('quantity'),
                'count' => $cropSales->count(),
                'average_price' => $cropSales->avg(function ($sale) {
                    return $sale->unit_price;
                }),
            ];
        });
    }

    /**
     * Calculate cost per hectare
     */
    public function getCostPerHectare($farmId, $period = '30')
    {
        $farm = Farm::findOrFail($farmId);
        $totalArea = $farm->fields->sum('size_hectares');
        
        if ($totalArea == 0) {
            return 0;
        }

        $summary = $this->getFarmFinancialSummary($farmId, $period);
        return round($summary['total_costs'] / $totalArea, 2);
    }

    /**
     * Calculate revenue per hectare
     */
    public function getRevenuePerHectare($farmId, $period = '30')
    {
        $farm = Farm::findOrFail($farmId);
        $totalArea = $farm->fields->sum('size_hectares');
        
        if ($totalArea == 0) {
            return 0;
        }

        $summary = $this->getFarmFinancialSummary($farmId, $period);
        return round($summary['total_revenue'] / $totalArea, 2);
    }

    /**
     * Get monthly financial trends
     */
    public function getMonthlyTrends($farmId, $months = 12)
    {
        $trends = [];
        
        for ($i = $months - 1; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $startDate = $date->copy()->startOfMonth();
            $endDate = $date->copy()->endOfMonth();
            
            $monthlyExpenses = $this->getFarmExpenses($farmId, $startDate)
                ->where('date', '<=', $endDate)
                ->sum('amount');
                
            $monthlySales = $this->getFarmSales($farmId, $startDate)
                ->where('sale_date', '<=', $endDate)
                ->sum('total_amount');
                
            $monthlyLaborCosts = $this->getFarmLaborCosts($farmId, $startDate)
                ->where('payment_date', '<=', $endDate)
                ->sum('total_amount');
            
            $totalCosts = $monthlyExpenses + $monthlyLaborCosts;
            $netProfit = $monthlySales - $totalCosts;
            
            $trends[] = [
                'month' => $date->format('Y-m'),
                'month_name' => $date->format('M Y'),
                'revenue' => $monthlySales,
                'expenses' => $monthlyExpenses,
                'labor_costs' => $monthlyLaborCosts,
                'total_costs' => $totalCosts,
                'net_profit' => $netProfit,
                'profit_margin' => $monthlySales > 0 ? round(($netProfit / $monthlySales) * 100, 2) : 0,
            ];
        }
        
        return $trends;
    }

    /**
     * Get crop profitability analysis
     */
    public function getCropProfitabilityAnalysis($farmId, $period = '365')
    {
        $startDate = now()->subDays($period);
        
        // Get all plantings in the period
        $plantings = Planting::whereHas('field', function ($q) use ($farmId) {
            $q->where('farm_id', $farmId);
        })
        ->where('planting_date', '>=', $startDate)
        ->with(['expenses', 'harvests.sales', 'field'])
        ->get();

        $cropAnalysis = [];
        
        foreach ($plantings as $planting) {
            $cropType = $planting->crop_type;
            
            if (!isset($cropAnalysis[$cropType])) {
                $cropAnalysis[$cropType] = [
                    'total_area' => 0,
                    'total_expenses' => 0,
                    'total_revenue' => 0,
                    'total_labor_costs' => 0,
                    'planting_count' => 0,
                ];
            }
            
            $plantingExpenses = $planting->expenses->sum('amount');
            $plantingRevenue = $planting->harvests->sum(function ($harvest) {
                return $harvest->sales->sum('total_amount');
            });
            
            // Calculate labor costs for this planting (approximate)
            $laborCosts = LaborWage::whereHas('task', function ($q) use ($planting) {
                $q->where('planting_id', $planting->id);
            })->sum('total_amount');
            
            $cropAnalysis[$cropType]['total_area'] += $planting->field->size_hectares;
            $cropAnalysis[$cropType]['total_expenses'] += $plantingExpenses;
            $cropAnalysis[$cropType]['total_revenue'] += $plantingRevenue;
            $cropAnalysis[$cropType]['total_labor_costs'] += $laborCosts;
            $cropAnalysis[$cropType]['planting_count']++;
        }
        
        // Calculate profitability metrics
        foreach ($cropAnalysis as $cropType => &$analysis) {
            $totalCosts = $analysis['total_expenses'] + $analysis['total_labor_costs'];
            $netProfit = $analysis['total_revenue'] - $totalCosts;
            
            $analysis['total_costs'] = $totalCosts;
            $analysis['net_profit'] = $netProfit;
            $analysis['profit_margin'] = $analysis['total_revenue'] > 0 ? 
                round(($netProfit / $analysis['total_revenue']) * 100, 2) : 0;
            $analysis['roi'] = $totalCosts > 0 ? 
                round(($netProfit / $totalCosts) * 100, 2) : 0;
            $analysis['cost_per_hectare'] = $analysis['total_area'] > 0 ? 
                round($totalCosts / $analysis['total_area'], 2) : 0;
            $analysis['revenue_per_hectare'] = $analysis['total_area'] > 0 ? 
                round($analysis['total_revenue'] / $analysis['total_area'], 2) : 0;
            $analysis['profit_per_hectare'] = $analysis['total_area'] > 0 ? 
                round($netProfit / $analysis['total_area'], 2) : 0;
        }
        
        return $cropAnalysis;
    }

    /**
     * Get budget vs actual analysis
     */
    public function getBudgetAnalysis($farmId, $budgetData, $period = '30')
    {
        $actualSummary = $this->getFarmFinancialSummary($farmId, $period);
        
        $analysis = [];
        
        // Revenue analysis
        $analysis['revenue'] = [
            'budgeted' => $budgetData['revenue'] ?? 0,
            'actual' => $actualSummary['total_revenue'],
            'variance' => $actualSummary['total_revenue'] - ($budgetData['revenue'] ?? 0),
            'variance_percentage' => ($budgetData['revenue'] ?? 0) > 0 ? 
                round((($actualSummary['total_revenue'] - ($budgetData['revenue'] ?? 0)) / ($budgetData['revenue'] ?? 0)) * 100, 2) : 0,
        ];
        
        // Expense analysis by category
        foreach (Expense::class::getCategories() as $category) {
            $budgetedAmount = $budgetData['expenses'][$category] ?? 0;
            $actualAmount = $actualSummary['expense_breakdown'][$category]['total'] ?? 0;
            $variance = $actualAmount - $budgetedAmount;
            
            $analysis['expenses'][$category] = [
                'budgeted' => $budgetedAmount,
                'actual' => $actualAmount,
                'variance' => $variance,
                'variance_percentage' => $budgetedAmount > 0 ? 
                    round(($variance / $budgetedAmount) * 100, 2) : 0,
                'status' => $variance > 0 ? 'over_budget' : 'under_budget',
            ];
        }
        
        // Overall analysis
        $totalBudgetedExpenses = array_sum($budgetData['expenses'] ?? []);
        $totalBudgetedCosts = $totalBudgetedExpenses + ($budgetData['labor_costs'] ?? 0);
        $budgetedProfit = ($budgetData['revenue'] ?? 0) - $totalBudgetedCosts;
        
        $analysis['summary'] = [
            'budgeted_profit' => $budgetedProfit,
            'actual_profit' => $actualSummary['net_profit'],
            'profit_variance' => $actualSummary['net_profit'] - $budgetedProfit,
            'budget_performance' => $budgetedProfit != 0 ? 
                round((($actualSummary['net_profit'] - $budgetedProfit) / abs($budgetedProfit)) * 100, 2) : 0,
        ];
        
        return $analysis;
    }

    /**
     * Get cash flow projection
     */
    public function getCashFlowProjection($farmId, $months = 6)
    {
        $projection = [];
        $currentBalance = $this->getCurrentCashBalance($farmId);
        
        for ($i = 0; $i < $months; $i++) {
            $projectionDate = now()->addMonths($i);
            
            // Project revenue based on expected harvests
            $projectedRevenue = $this->projectMonthlyRevenue($farmId, $projectionDate);
            
            // Project expenses based on historical averages
            $projectedExpenses = $this->projectMonthlyExpenses($farmId, $projectionDate);
            
            // Project labor costs
            $projectedLaborCosts = $this->projectMonthlyLaborCosts($farmId, $projectionDate);
            
            $netCashFlow = $projectedRevenue - $projectedExpenses - $projectedLaborCosts;
            $currentBalance += $netCashFlow;
            
            $projection[] = [
                'month' => $projectionDate->format('Y-m'),
                'month_name' => $projectionDate->format('M Y'),
                'projected_revenue' => $projectedRevenue,
                'projected_expenses' => $projectedExpenses,
                'projected_labor_costs' => $projectedLaborCosts,
                'net_cash_flow' => $netCashFlow,
                'ending_balance' => $currentBalance,
            ];
        }
        
        return $projection;
    }

    /**
     * Get current cash balance (simplified - would need actual bank integration)
     */
    private function getCurrentCashBalance($farmId)
    {
        // This is a simplified calculation
        // In a real system, this would integrate with banking APIs
        $summary = $this->getFarmFinancialSummary($farmId, 365);
        return max(0, $summary['net_profit']); // Simplified assumption
    }

    /**
     * Project monthly revenue based on expected harvests
     */
    private function projectMonthlyRevenue($farmId, $date)
    {
        // Get historical average revenue for the same month
        $historicalRevenue = Sale::whereHas('harvest.planting.field', function ($q) use ($farmId) {
            $q->where('farm_id', $farmId);
        })
        ->whereMonth('sale_date', $date->month)
        ->whereYear('sale_date', '>=', $date->year - 2) // Last 2 years
        ->avg('total_amount');
        
        return $historicalRevenue ?? 0;
    }

    /**
     * Project monthly expenses based on historical data
     */
    private function projectMonthlyExpenses($farmId, $date)
    {
        $historicalExpenses = Expense::whereHas('planting.field', function ($q) use ($farmId) {
            $q->where('farm_id', $farmId);
        })
        ->whereMonth('date', $date->month)
        ->whereYear('date', '>=', $date->year - 2)
        ->avg('amount');
        
        return $historicalExpenses ?? 0;
    }

    /**
     * Project monthly labor costs
     */
    private function projectMonthlyLaborCosts($farmId, $date)
    {
        $historicalLaborCosts = LaborWage::whereHas('laborer', function ($q) use ($farmId) {
            $q->where('farm_id', $farmId);
        })
        ->whereMonth('payment_date', $date->month)
        ->whereYear('payment_date', '>=', $date->year - 2)
        ->avg('total_amount');
        
        return $historicalLaborCosts ?? 0;
    }

    /**
     * Get financial KPIs (Key Performance Indicators)
     */
    public function getFinancialKPIs($farmId, $period = '30')
    {
        $summary = $this->getFarmFinancialSummary($farmId, $period);
        $farm = Farm::findOrFail($farmId);
        $totalArea = $farm->fields->sum('size_hectares');
        
        return [
            'gross_profit_margin' => $summary['total_revenue'] > 0 ? 
                round((($summary['total_revenue'] - $summary['total_costs']) / $summary['total_revenue']) * 100, 2) : 0,
            'net_profit_margin' => $summary['profit_margin'],
            'return_on_investment' => $summary['roi'],
            'cost_per_hectare' => $totalArea > 0 ? round($summary['total_costs'] / $totalArea, 2) : 0,
            'revenue_per_hectare' => $totalArea > 0 ? round($summary['total_revenue'] / $totalArea, 2) : 0,
            'expense_ratio' => $summary['total_revenue'] > 0 ? 
                round(($summary['total_expenses'] / $summary['total_revenue']) * 100, 2) : 0,
            'labor_cost_ratio' => $summary['total_revenue'] > 0 ? 
                round(($summary['total_labor_costs'] / $summary['total_revenue']) * 100, 2) : 0,
            'break_even_point' => $this->calculateBreakEvenPoint($farmId),
        ];
    }

    /**
     * Calculate break-even point
     */
    private function calculateBreakEvenPoint($farmId)
    {
        $summary = $this->getFarmFinancialSummary($farmId, 365); // Annual data
        
        if ($summary['total_revenue'] == 0) {
            return 0;
        }
        
        $variableCostRatio = $summary['total_costs'] / $summary['total_revenue'];
        $contributionMargin = 1 - $variableCostRatio;
        
        // Simplified break-even calculation
        // In reality, you'd need to separate fixed and variable costs
        $fixedCosts = $summary['total_costs'] * 0.3; // Assume 30% are fixed costs
        
        return $contributionMargin > 0 ? round($fixedCosts / $contributionMargin, 2) : 0;
    }

    /**
     * Get expense categories
     */
    public function getExpenseCategories()
    {
        return [
            Expense::CATEGORY_SEEDS => 'Seeds',
            Expense::CATEGORY_FERTILIZER => 'Fertilizer',
            Expense::CATEGORY_PESTICIDE => 'Pesticide',
            Expense::CATEGORY_LABOR => 'Labor',
            Expense::CATEGORY_EQUIPMENT => 'Equipment',
            Expense::CATEGORY_UTILITIES => 'Utilities',
            Expense::CATEGORY_MAINTENANCE => 'Maintenance',
            Expense::CATEGORY_OTHER => 'Other',
        ];
    }

    /**
     * Generate financial report
     */
    public function generateFinancialReport($farmId, $startDate, $endDate, $reportType = 'summary')
    {
        $farm = Farm::findOrFail($farmId);
        
        $expenses = $this->getFarmExpenses($farmId, $startDate)
            ->where('date', '<=', $endDate);
        $sales = $this->getFarmSales($farmId, $startDate)
            ->where('sale_date', '<=', $endDate);
        $laborCosts = $this->getFarmLaborCosts($farmId, $startDate)
            ->where('payment_date', '<=', $endDate);
        
        $report = [
            'farm' => [
                'id' => $farm->id,
                'name' => $farm->name,
                'total_area' => $farm->fields->sum('size_hectares'),
            ],
            'period' => [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'days' => $startDate->diffInDays($endDate),
            ],
            'summary' => [
                'total_revenue' => $sales->sum('total_amount'),
                'total_expenses' => $expenses->sum('amount'),
                'total_labor_costs' => $laborCosts->sum('total_amount'),
                'net_profit' => $sales->sum('total_amount') - $expenses->sum('amount') - $laborCosts->sum('total_amount'),
            ],
        ];
        
        if ($reportType === 'detailed') {
            $report['details'] = [
                'expenses' => $expenses->toArray(),
                'sales' => $sales->toArray(),
                'labor_costs' => $laborCosts->toArray(),
            ];
        }
        
        return $report;
    }
}