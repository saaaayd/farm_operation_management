<?php

namespace App\Http\Controllers\Weather;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use App\Services\WeatherAnalyticsService;
use App\Models\Field;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeatherAnalyticsController extends Controller
{
    protected $weatherService;
    protected $weatherAnalyticsService;

    public function __construct(WeatherService $weatherService, WeatherAnalyticsService $weatherAnalyticsService)
    {
        $this->weatherService = $weatherService;
        $this->weatherAnalyticsService = $weatherAnalyticsService;
    }

    /**
     * Get weather analytics for a field
     */
    public function getFieldAnalytics(Request $request, $fieldId)
    {
        $request->validate([
            'period_days' => 'integer|min:1|max:365',
            'analysis_type' => 'in:basic,rice_specific,detailed,comparative',
        ]);

        $field = Field::findOrFail($fieldId);
        $this->authorize('view', $field);

        $periodDays = $request->period_days ?? 30;
        $analysisType = $request->analysis_type ?? 'basic';

        $analytics = [];

        switch ($analysisType) {
            case 'basic':
                $analytics = [
                    'weather_stats' => $this->weatherService->getFieldWeatherStats($field, $periodDays),
                    'alerts' => $this->weatherService->getWeatherAlerts($field),
                ];
                break;

            case 'rice_specific':
                $analytics = $this->weatherService->getRiceWeatherAnalytics($field, $periodDays);
                break;

            case 'detailed':
                $analytics = [
                    'weather_stats' => $this->weatherService->getFieldWeatherStats($field, $periodDays),
                    'rice_analytics' => $this->weatherService->getRiceWeatherAnalytics($field, $periodDays),
                    'alerts' => $this->weatherService->getWeatherAlerts($field),
                    'recommendations' => $this->weatherService->getRiceFarmingRecommendations($field),
                    'historical_comparison' => $this->weatherAnalyticsService->getHistoricalComparison($field, $periodDays),
                ];
                break;

            case 'comparative':
                $farm = $field->farm;
                $analytics = $this->weatherAnalyticsService->getComparativeAnalytics($farm, $fieldId, $periodDays);
                break;
        }

        return response()->json([
            'field' => $field,
            'analytics' => $analytics,
            'period_days' => $periodDays,
            'analysis_type' => $analysisType,
        ]);
    }

    /**
     * Get weather trends for multiple fields
     */
    public function getFarmWeatherTrends(Request $request, $farmId)
    {
        $request->validate([
            'period_days' => 'integer|min:1|max:365',
            'trend_type' => 'in:temperature,humidity,conditions,comprehensive',
        ]);

        $farm = Farm::findOrFail($farmId);
        $this->authorize('view', $farm);

        $periodDays = $request->period_days ?? 30;
        $trendType = $request->trend_type ?? 'comprehensive';

        $trends = $this->weatherAnalyticsService->getFarmWeatherTrends($farm, $periodDays, $trendType);

        return response()->json([
            'farm' => $farm,
            'trends' => $trends,
            'period_days' => $periodDays,
            'trend_type' => $trendType,
        ]);
    }

    /**
     * Get weather impact analysis on crop performance
     */
    public function getWeatherImpactAnalysis(Request $request, $fieldId)
    {
        $request->validate([
            'planting_id' => 'nullable|exists:plantings,id',
            'analysis_period' => 'in:planting_season,growth_stages,full_cycle',
        ]);

        $field = Field::findOrFail($fieldId);
        $this->authorize('view', $field);

        $plantingId = $request->planting_id;
        $analysisPeriod = $request->analysis_period ?? 'planting_season';

        $impact = $this->weatherAnalyticsService->analyzeWeatherImpact($field, $plantingId, $analysisPeriod);

        return response()->json([
            'field' => $field,
            'impact_analysis' => $impact,
            'analysis_period' => $analysisPeriod,
        ]);
    }

    /**
     * Get weather-based yield predictions
     */
    public function getYieldPredictions(Request $request, $fieldId)
    {
        $request->validate([
            'planting_id' => 'required|exists:plantings,id',
            'prediction_model' => 'in:simple,advanced,ml_based',
        ]);

        $field = Field::findOrFail($fieldId);
        $this->authorize('view', $field);

        $plantingId = $request->planting_id;
        $predictionModel = $request->prediction_model ?? 'simple';

        $predictions = $this->weatherAnalyticsService->predictYield($field, $plantingId, $predictionModel);

        return response()->json([
            'field' => $field,
            'yield_predictions' => $predictions,
            'prediction_model' => $predictionModel,
        ]);
    }

    /**
     * Get optimal planting recommendations based on weather patterns
     */
    public function getPlantingRecommendations(Request $request, $farmId)
    {
        $request->validate([
            'crop_type' => 'required|string|max:50',
            'planning_months' => 'integer|min:1|max:12',
        ]);

        $farm = Farm::findOrFail($farmId);
        $this->authorize('view', $farm);

        $cropType = $request->crop_type;
        $planningMonths = $request->planning_months ?? 6;

        $recommendations = $this->weatherAnalyticsService->getPlantingRecommendations($farm, $cropType, $planningMonths);

        return response()->json([
            'farm' => $farm,
            'recommendations' => $recommendations,
            'crop_type' => $cropType,
            'planning_months' => $planningMonths,
        ]);
    }

    /**
     * Get weather risk assessment
     */
    public function getWeatherRiskAssessment(Request $request, $fieldId)
    {
        $request->validate([
            'risk_types' => 'array',
            'risk_types.*' => 'in:drought,flood,heat_stress,cold_stress,disease,pest',
            'assessment_period' => 'in:current,short_term,long_term',
        ]);

        $field = Field::findOrFail($fieldId);
        $this->authorize('view', $field);

        $riskTypes = $request->risk_types ?? ['drought', 'flood', 'heat_stress', 'cold_stress'];
        $assessmentPeriod = $request->assessment_period ?? 'current';

        $riskAssessment = $this->weatherAnalyticsService->assessWeatherRisks($field, $riskTypes, $assessmentPeriod);

        return response()->json([
            'field' => $field,
            'risk_assessment' => $riskAssessment,
            'risk_types' => $riskTypes,
            'assessment_period' => $assessmentPeriod,
        ]);
    }

    /**
     * Get irrigation recommendations based on weather data
     */
    public function getIrrigationRecommendations(Request $request, $fieldId)
    {
        $request->validate([
            'soil_moisture_level' => 'nullable|numeric|min:0|max:100',
            'crop_stage' => 'nullable|string|max:50',
        ]);

        $field = Field::findOrFail($fieldId);
        $this->authorize('view', $field);

        $soilMoistureLevel = $request->soil_moisture_level;
        $cropStage = $request->crop_stage;

        $recommendations = $this->weatherAnalyticsService->getIrrigationRecommendations($field, $soilMoistureLevel, $cropStage);

        return response()->json([
            'field' => $field,
            'irrigation_recommendations' => $recommendations,
            'soil_moisture_level' => $soilMoistureLevel,
            'crop_stage' => $cropStage,
        ]);
    }

    /**
     * Get weather-based pest and disease risk
     */
    public function getPestDiseaseRisk(Request $request, $fieldId)
    {
        $request->validate([
            'pest_types' => 'array',
            'pest_types.*' => 'in:brown_planthopper,stem_borer,leaf_folder,rice_bug',
            'disease_types' => 'array',
            'disease_types.*' => 'in:rice_blast,bacterial_blight,sheath_blight,tungro',
        ]);

        $field = Field::findOrFail($fieldId);
        $this->authorize('view', $field);

        $pestTypes = $request->pest_types ?? ['brown_planthopper', 'stem_borer'];
        $diseaseTypes = $request->disease_types ?? ['rice_blast', 'bacterial_blight'];

        $riskAssessment = $this->weatherAnalyticsService->assessPestDiseaseRisk($field, $pestTypes, $diseaseTypes);

        return response()->json([
            'field' => $field,
            'pest_disease_risk' => $riskAssessment,
            'pest_types' => $pestTypes,
            'disease_types' => $diseaseTypes,
        ]);
    }

    /**
     * Get climate change impact analysis
     */
    public function getClimateChangeImpact(Request $request, $farmId)
    {
        $request->validate([
            'analysis_years' => 'integer|min:1|max:10',
            'climate_scenarios' => 'array',
            'climate_scenarios.*' => 'in:current,moderate_warming,high_warming',
        ]);

        $farm = Farm::findOrFail($farmId);
        $this->authorize('view', $farm);

        $analysisYears = $request->analysis_years ?? 5;
        $climateScenarios = $request->climate_scenarios ?? ['current', 'moderate_warming'];

        $impact = $this->weatherAnalyticsService->analyzeClimateChangeImpact($farm, $analysisYears, $climateScenarios);

        return response()->json([
            'farm' => $farm,
            'climate_impact' => $impact,
            'analysis_years' => $analysisYears,
            'climate_scenarios' => $climateScenarios,
        ]);
    }

    /**
     * Export weather analytics report
     */
    public function exportAnalyticsReport(Request $request, $fieldId)
    {
        $request->validate([
            'format' => 'required|in:pdf,excel,csv',
            'report_type' => 'required|in:summary,detailed,comprehensive',
            'period_days' => 'integer|min:1|max:365',
        ]);

        $field = Field::findOrFail($fieldId);
        $this->authorize('view', $field);

        $format = $request->format ?? 'json';
        $reportType = $request->report_type ?? 'summary';
        $periodDays = $request->period_days ?? 30;

        // Get weather analytics data
        $weatherService = app(\App\Services\WeatherService::class);
        $analytics = $weatherService->getRiceWeatherAnalytics($field, $periodDays);
        
        $weatherLogs = \App\Models\WeatherLog::where('field_id', $field->id)
            ->where('recorded_at', '>=', now()->subDays($periodDays))
            ->orderBy('recorded_at', 'desc')
            ->get();

        $reportData = [
            'field' => [
                'id' => $field->id,
                'name' => $field->name,
            ],
            'analytics' => $analytics,
            'weather_logs' => $weatherLogs->map(function ($log) {
                return [
                    'date' => $log->recorded_at,
                    'temperature' => $log->temperature,
                    'humidity' => $log->humidity,
                    'rainfall' => $log->rainfall ?? 0,
                    'conditions' => $log->conditions,
                ];
            }),
            'period_days' => $periodDays,
            'generated_at' => now(),
        ];

        // Return based on format
        if ($format === 'csv') {
            return $this->exportWeatherDataToCsv($reportData, $field->name);
        } elseif ($format === 'json') {
            return response()->json([
                'message' => 'Weather analytics report',
                'data' => $reportData,
                'status' => 'success',
            ]);
        }

        return response()->json([
            'message' => 'Unsupported export format. Use json or csv.',
            'data' => $reportData,
            'status' => 'error',
        ], 400);
    }

    private function exportWeatherDataToCsv($data, $fieldName)
    {
        $filename = 'weather-report-' . $fieldName . '-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($data) {
            $file = fopen('php://output', 'w');
            
            // Write header row
            fputcsv($file, ['Date', 'Temperature (°C)', 'Humidity (%)', 'Rainfall (mm)', 'Conditions']);
            
            // Write data rows
            foreach ($data['weather_logs'] as $log) {
                fputcsv($file, [
                    $log['date'],
                    $log['temperature'],
                    $log['humidity'],
                    $log['rainfall'],
                    $log['conditions'],
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get weather data quality metrics
     */
    public function getDataQualityMetrics(Request $request, $fieldId)
    {
        $field = Field::findOrFail($fieldId);
        $this->authorize('view', $field);

        $periodDays = $request->period_days ?? 30;

        $qualityMetrics = $this->weatherAnalyticsService->getDataQualityMetrics($field, $periodDays);

        return response()->json([
            'field' => $field,
            'data_quality' => $qualityMetrics,
            'period_days' => $periodDays,
        ]);
    }
}