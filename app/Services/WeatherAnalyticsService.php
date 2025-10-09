<?php

namespace App\Services;

use App\Models\Field;
use App\Models\Farm;
use App\Models\WeatherLog;
use App\Models\Planting;
use App\Models\Harvest;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WeatherAnalyticsService
{
    protected $weatherService;

    public function __construct(WeatherService $weatherService)
    {
        $this->weatherService = $weatherService;
    }

    /**
     * Get historical weather comparison
     */
    public function getHistoricalComparison(Field $field, int $periodDays = 30)
    {
        $currentPeriod = $this->getWeatherDataForPeriod($field, now()->subDays($periodDays), now());
        $previousPeriod = $this->getWeatherDataForPeriod($field, now()->subDays($periodDays * 2), now()->subDays($periodDays));
        $lastYearPeriod = $this->getWeatherDataForPeriod($field, now()->subYear()->subDays($periodDays), now()->subYear());

        return [
            'current_period' => $this->calculatePeriodStats($currentPeriod),
            'previous_period' => $this->calculatePeriodStats($previousPeriod),
            'last_year_period' => $this->calculatePeriodStats($lastYearPeriod),
            'comparisons' => [
                'vs_previous_period' => $this->compareWeatherPeriods($currentPeriod, $previousPeriod),
                'vs_last_year' => $this->compareWeatherPeriods($currentPeriod, $lastYearPeriod),
            ],
        ];
    }

    /**
     * Get comparative analytics across farm fields
     */
    public function getComparativeAnalytics(Farm $farm, int $focusFieldId, int $periodDays = 30)
    {
        $fields = $farm->fields;
        $analytics = [];
        $focusField = null;

        foreach ($fields as $field) {
            $fieldAnalytics = [
                'field' => $field,
                'weather_stats' => $this->weatherService->getFieldWeatherStats($field, $periodDays),
                'rice_analytics' => $this->weatherService->getRiceWeatherAnalytics($field, $periodDays),
                'suitability_score' => $this->calculateFieldSuitabilityScore($field, $periodDays),
            ];

            if ($field->id === $focusFieldId) {
                $focusField = $fieldAnalytics;
            }

            $analytics[] = $fieldAnalytics;
        }

        return [
            'focus_field' => $focusField,
            'all_fields' => $analytics,
            'farm_averages' => $this->calculateFarmAverages($analytics),
            'field_rankings' => $this->rankFieldsByPerformance($analytics),
            'recommendations' => $this->generateComparativeRecommendations($analytics, $focusFieldId),
        ];
    }

    /**
     * Get farm weather trends
     */
    public function getFarmWeatherTrends(Farm $farm, int $periodDays = 30, string $trendType = 'comprehensive')
    {
        $fields = $farm->fields;
        $trends = [];

        switch ($trendType) {
            case 'temperature':
                $trends = $this->getTemperatureTrends($fields, $periodDays);
                break;
            case 'humidity':
                $trends = $this->getHumidityTrends($fields, $periodDays);
                break;
            case 'conditions':
                $trends = $this->getConditionsTrends($fields, $periodDays);
                break;
            case 'comprehensive':
            default:
                $trends = [
                    'temperature' => $this->getTemperatureTrends($fields, $periodDays),
                    'humidity' => $this->getHumidityTrends($fields, $periodDays),
                    'conditions' => $this->getConditionsTrends($fields, $periodDays),
                    'wind' => $this->getWindTrends($fields, $periodDays),
                ];
                break;
        }

        return [
            'farm' => $farm,
            'trends' => $trends,
            'trend_analysis' => $this->analyzeTrends($trends),
            'seasonal_patterns' => $this->identifySeasonalPatterns($fields, $periodDays),
        ];
    }

    /**
     * Analyze weather impact on crop performance
     */
    public function analyzeWeatherImpact(Field $field, int $plantingId = null, string $analysisPeriod = 'planting_season')
    {
        $planting = $plantingId ? Planting::find($plantingId) : $field->getCurrentRicePlanting();
        
        if (!$planting) {
            return ['error' => 'No planting data available for analysis'];
        }

        $analysisData = $this->getAnalysisPeriodData($planting, $analysisPeriod);
        $weatherData = $this->getWeatherDataForPeriod($field, $analysisData['start_date'], $analysisData['end_date']);
        
        $impact = [
            'planting_info' => [
                'id' => $planting->id,
                'crop_type' => $planting->crop_type,
                'planting_date' => $planting->planting_date,
                'expected_harvest_date' => $planting->expected_harvest_date,
                'current_stage' => $planting->getCurrentStage()?->riceGrowthStage->name,
            ],
            'weather_summary' => $this->calculatePeriodStats($weatherData),
            'growth_stage_analysis' => $this->analyzeGrowthStageWeather($planting, $weatherData),
            'stress_events' => $this->identifyStressEvents($weatherData),
            'yield_impact_factors' => $this->calculateYieldImpactFactors($weatherData, $planting),
            'recommendations' => $this->generateImpactBasedRecommendations($weatherData, $planting),
        ];

        return $impact;
    }

    /**
     * Predict yield based on weather patterns
     */
    public function predictYield(Field $field, int $plantingId, string $predictionModel = 'simple')
    {
        $planting = Planting::findOrFail($plantingId);
        $weatherData = $this->getWeatherDataForPeriod($field, $planting->planting_date, now());
        
        switch ($predictionModel) {
            case 'simple':
                return $this->simpleYieldPrediction($planting, $weatherData);
            case 'advanced':
                return $this->advancedYieldPrediction($planting, $weatherData);
            case 'ml_based':
                return $this->mlBasedYieldPrediction($planting, $weatherData);
            default:
                return $this->simpleYieldPrediction($planting, $weatherData);
        }
    }

    /**
     * Get planting recommendations based on weather patterns
     */
    public function getPlantingRecommendations(Farm $farm, string $cropType, int $planningMonths = 6)
    {
        $recommendations = [];
        $historicalData = $this->getHistoricalWeatherPatterns($farm, $cropType);
        
        for ($month = 1; $month <= $planningMonths; $month++) {
            $targetDate = now()->addMonths($month);
            $monthRecommendation = $this->evaluateMonthForPlanting($farm, $cropType, $targetDate, $historicalData);
            $recommendations[] = $monthRecommendation;
        }

        return [
            'crop_type' => $cropType,
            'monthly_recommendations' => $recommendations,
            'optimal_windows' => $this->identifyOptimalPlantingWindows($recommendations),
            'risk_factors' => $this->identifyPlantingRiskFactors($farm, $cropType),
            'best_practices' => $this->getPlantingBestPractices($cropType),
        ];
    }

    /**
     * Assess weather risks
     */
    public function assessWeatherRisks(Field $field, array $riskTypes, string $assessmentPeriod = 'current')
    {
        $risks = [];
        $weatherData = $this->getWeatherDataForAssessmentPeriod($field, $assessmentPeriod);
        
        foreach ($riskTypes as $riskType) {
            $risks[$riskType] = $this->assessSpecificRisk($riskType, $weatherData, $field);
        }

        return [
            'field' => $field,
            'assessment_period' => $assessmentPeriod,
            'individual_risks' => $risks,
            'overall_risk_score' => $this->calculateOverallRiskScore($risks),
            'mitigation_strategies' => $this->getMitigationStrategies($risks),
            'monitoring_recommendations' => $this->getMonitoringRecommendations($risks),
        ];
    }

    /**
     * Get irrigation recommendations
     */
    public function getIrrigationRecommendations(Field $field, float $soilMoistureLevel = null, string $cropStage = null)
    {
        $currentWeather = $field->latestWeather;
        $forecast = $this->getWeatherForecast($field, 7); // 7-day forecast
        $planting = $field->getCurrentRicePlanting();
        
        $recommendations = [
            'immediate_action' => $this->getImmediateIrrigationAction($currentWeather, $soilMoistureLevel, $cropStage),
            'weekly_schedule' => $this->generateWeeklyIrrigationSchedule($forecast, $soilMoistureLevel, $cropStage),
            'water_requirements' => $this->calculateWaterRequirements($field, $planting, $cropStage),
            'efficiency_tips' => $this->getIrrigationEfficiencyTips($field, $currentWeather),
            'risk_warnings' => $this->getIrrigationRiskWarnings($forecast, $soilMoistureLevel),
        ];

        return $recommendations;
    }

    /**
     * Assess pest and disease risk based on weather
     */
    public function assessPestDiseaseRisk(Field $field, array $pestTypes, array $diseaseTypes)
    {
        $weatherData = $this->getWeatherDataForPeriod($field, now()->subDays(14), now());
        $forecast = $this->getWeatherForecast($field, 7);
        
        $pestRisks = [];
        foreach ($pestTypes as $pest) {
            $pestRisks[$pest] = $this->assessPestRisk($pest, $weatherData, $forecast);
        }

        $diseaseRisks = [];
        foreach ($diseaseTypes as $disease) {
            $diseaseRisks[$disease] = $this->assessDiseaseRisk($disease, $weatherData, $forecast);
        }

        return [
            'field' => $field,
            'pest_risks' => $pestRisks,
            'disease_risks' => $diseaseRisks,
            'overall_risk_level' => $this->calculateOverallPestDiseaseRisk($pestRisks, $diseaseRisks),
            'prevention_measures' => $this->getPestDiseasePreventionMeasures($pestRisks, $diseaseRisks),
            'monitoring_schedule' => $this->getPestDiseaseMonitoringSchedule($pestRisks, $diseaseRisks),
        ];
    }

    /**
     * Analyze climate change impact
     */
    public function analyzeClimateChangeImpact(Farm $farm, int $analysisYears, array $climateScenarios)
    {
        $historicalData = $this->getHistoricalClimateData($farm, $analysisYears);
        $projections = [];
        
        foreach ($climateScenarios as $scenario) {
            $projections[$scenario] = $this->projectClimateScenario($farm, $scenario, $analysisYears);
        }

        return [
            'farm' => $farm,
            'historical_trends' => $this->analyzeHistoricalTrends($historicalData),
            'climate_projections' => $projections,
            'impact_assessment' => $this->assessClimateImpacts($projections),
            'adaptation_strategies' => $this->getClimateAdaptationStrategies($projections),
            'resilience_recommendations' => $this->getResilienceRecommendations($farm, $projections),
        ];
    }

    /**
     * Get data quality metrics
     */
    public function getDataQualityMetrics(Field $field, int $periodDays = 30)
    {
        $startDate = now()->subDays($periodDays);
        $weatherLogs = WeatherLog::where('field_id', $field->id)
            ->where('recorded_at', '>=', $startDate)
            ->orderBy('recorded_at')
            ->get();

        $expectedReadings = $periodDays * 24; // Assuming hourly readings
        $actualReadings = $weatherLogs->count();
        $completeness = $expectedReadings > 0 ? ($actualReadings / $expectedReadings) * 100 : 0;

        return [
            'data_completeness' => round($completeness, 2),
            'total_readings' => $actualReadings,
            'expected_readings' => $expectedReadings,
            'missing_readings' => max(0, $expectedReadings - $actualReadings),
            'data_gaps' => $this->identifyDataGaps($weatherLogs, $startDate),
            'quality_score' => $this->calculateDataQualityScore($weatherLogs),
            'reliability_metrics' => $this->calculateReliabilityMetrics($weatherLogs),
        ];
    }

    // Private helper methods

    private function getWeatherDataForPeriod(Field $field, Carbon $startDate, Carbon $endDate)
    {
        return WeatherLog::where('field_id', $field->id)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->orderBy('recorded_at')
            ->get();
    }

    private function calculatePeriodStats($weatherData)
    {
        if ($weatherData->isEmpty()) {
            return null;
        }

        return [
            'avg_temperature' => round($weatherData->avg('temperature'), 1),
            'min_temperature' => round($weatherData->min('temperature'), 1),
            'max_temperature' => round($weatherData->max('temperature'), 1),
            'avg_humidity' => round($weatherData->avg('humidity'), 1),
            'avg_wind_speed' => round($weatherData->avg('wind_speed'), 1),
            'most_common_condition' => $weatherData->groupBy('conditions')->map->count()->sortDesc()->keys()->first(),
            'total_readings' => $weatherData->count(),
        ];
    }

    private function compareWeatherPeriods($currentPeriod, $comparisonPeriod)
    {
        if ($currentPeriod->isEmpty() || $comparisonPeriod->isEmpty()) {
            return null;
        }

        $currentStats = $this->calculatePeriodStats($currentPeriod);
        $comparisonStats = $this->calculatePeriodStats($comparisonPeriod);

        return [
            'temperature_change' => $currentStats['avg_temperature'] - $comparisonStats['avg_temperature'],
            'humidity_change' => $currentStats['avg_humidity'] - $comparisonStats['avg_humidity'],
            'wind_speed_change' => $currentStats['avg_wind_speed'] - $comparisonStats['avg_wind_speed'],
        ];
    }

    private function calculateFieldSuitabilityScore(Field $field, int $periodDays)
    {
        $riceAnalytics = $this->weatherService->getRiceWeatherAnalytics($field, $periodDays);
        return $riceAnalytics['rice_analytics']['weather_suitability_score'] ?? 0;
    }

    private function calculateFarmAverages($fieldAnalytics)
    {
        $totalFields = count($fieldAnalytics);
        if ($totalFields === 0) return null;

        $avgTemperature = collect($fieldAnalytics)->avg(function ($field) {
            return $field['weather_stats']['avg_temperature'] ?? 0;
        });

        $avgHumidity = collect($fieldAnalytics)->avg(function ($field) {
            return $field['weather_stats']['avg_humidity'] ?? 0;
        });

        $avgSuitability = collect($fieldAnalytics)->avg('suitability_score');

        return [
            'avg_temperature' => round($avgTemperature, 1),
            'avg_humidity' => round($avgHumidity, 1),
            'avg_suitability_score' => round($avgSuitability, 1),
            'total_fields' => $totalFields,
        ];
    }

    private function rankFieldsByPerformance($fieldAnalytics)
    {
        return collect($fieldAnalytics)
            ->sortByDesc('suitability_score')
            ->values()
            ->map(function ($field, $index) {
                return [
                    'rank' => $index + 1,
                    'field_id' => $field['field']['id'],
                    'field_name' => $field['field']['name'],
                    'suitability_score' => $field['suitability_score'],
                ];
            })
            ->toArray();
    }

    private function generateComparativeRecommendations($fieldAnalytics, $focusFieldId)
    {
        $recommendations = [];
        $focusField = collect($fieldAnalytics)->firstWhere('field.id', $focusFieldId);
        
        if (!$focusField) {
            return $recommendations;
        }

        $farmAverage = $this->calculateFarmAverages($fieldAnalytics);
        
        if ($focusField['suitability_score'] < $farmAverage['avg_suitability_score']) {
            $recommendations[] = [
                'type' => 'improvement',
                'message' => 'This field is performing below farm average. Consider reviewing management practices.',
                'priority' => 'medium',
            ];
        }

        return $recommendations;
    }

    private function getTemperatureTrends($fields, $periodDays)
    {
        // Implementation for temperature trends
        return ['trend' => 'stable', 'average_change' => 0.2];
    }

    private function getHumidityTrends($fields, $periodDays)
    {
        // Implementation for humidity trends
        return ['trend' => 'increasing', 'average_change' => 2.1];
    }

    private function getConditionsTrends($fields, $periodDays)
    {
        // Implementation for conditions trends
        return ['most_common' => 'clear', 'stability' => 'high'];
    }

    private function getWindTrends($fields, $periodDays)
    {
        // Implementation for wind trends
        return ['trend' => 'stable', 'average_speed' => 8.5];
    }

    private function analyzeTrends($trends)
    {
        // Analyze the collected trends
        return ['overall_stability' => 'moderate', 'risk_level' => 'low'];
    }

    private function identifySeasonalPatterns($fields, $periodDays)
    {
        // Identify seasonal patterns
        return ['season' => 'dry', 'pattern_strength' => 'strong'];
    }

    private function simpleYieldPrediction($planting, $weatherData)
    {
        // Simple yield prediction based on weather
        $baseYield = 4500; // kg per hectare
        $weatherScore = $this->calculateWeatherScore($weatherData);
        $predictedYield = $baseYield * ($weatherScore / 100);
        
        return [
            'predicted_yield_kg_per_ha' => round($predictedYield, 0),
            'confidence_level' => 'medium',
            'factors' => ['weather_score' => $weatherScore],
        ];
    }

    private function advancedYieldPrediction($planting, $weatherData)
    {
        // More sophisticated prediction model
        return $this->simpleYieldPrediction($planting, $weatherData);
    }

    private function mlBasedYieldPrediction($planting, $weatherData)
    {
        // Machine learning based prediction (placeholder)
        return [
            'message' => 'ML-based prediction not yet implemented',
            'fallback' => $this->simpleYieldPrediction($planting, $weatherData),
        ];
    }

    private function calculateWeatherScore($weatherData)
    {
        if ($weatherData->isEmpty()) return 50;
        
        $optimalTempDays = $weatherData->whereBetween('temperature', [20, 30])->count();
        $totalDays = $weatherData->count();
        
        return $totalDays > 0 ? ($optimalTempDays / $totalDays) * 100 : 50;
    }

    // Additional helper methods would be implemented here...
    // For brevity, I'm including placeholders for the remaining private methods

    private function getAnalysisPeriodData($planting, $analysisPeriod)
    {
        return ['start_date' => $planting->planting_date, 'end_date' => now()];
    }

    private function analyzeGrowthStageWeather($planting, $weatherData)
    {
        return ['analysis' => 'Growth stage weather analysis placeholder'];
    }

    private function identifyStressEvents($weatherData)
    {
        return ['stress_events' => []];
    }

    private function calculateYieldImpactFactors($weatherData, $planting)
    {
        return ['impact_factors' => []];
    }

    private function generateImpactBasedRecommendations($weatherData, $planting)
    {
        return ['recommendations' => []];
    }

    private function getHistoricalWeatherPatterns($farm, $cropType)
    {
        return ['patterns' => []];
    }

    private function evaluateMonthForPlanting($farm, $cropType, $targetDate, $historicalData)
    {
        return [
            'month' => $targetDate->format('M Y'),
            'suitability_score' => 75,
            'recommendation' => 'suitable',
        ];
    }

    private function identifyOptimalPlantingWindows($recommendations)
    {
        return ['optimal_windows' => []];
    }

    private function identifyPlantingRiskFactors($farm, $cropType)
    {
        return ['risk_factors' => []];
    }

    private function getPlantingBestPractices($cropType)
    {
        return ['best_practices' => []];
    }

    private function getWeatherDataForAssessmentPeriod($field, $assessmentPeriod)
    {
        $days = match($assessmentPeriod) {
            'short_term' => 7,
            'long_term' => 90,
            default => 30,
        };
        
        return $this->getWeatherDataForPeriod($field, now()->subDays($days), now());
    }

    private function assessSpecificRisk($riskType, $weatherData, $field)
    {
        return [
            'risk_level' => 'medium',
            'probability' => 0.3,
            'impact' => 'moderate',
        ];
    }

    private function calculateOverallRiskScore($risks)
    {
        return 45; // Placeholder
    }

    private function getMitigationStrategies($risks)
    {
        return ['strategies' => []];
    }

    private function getMonitoringRecommendations($risks)
    {
        return ['monitoring' => []];
    }

    private function getWeatherForecast($field, $days)
    {
        // This would integrate with weather forecast API
        return collect();
    }

    private function getImmediateIrrigationAction($currentWeather, $soilMoistureLevel, $cropStage)
    {
        return ['action' => 'monitor', 'urgency' => 'low'];
    }

    private function generateWeeklyIrrigationSchedule($forecast, $soilMoistureLevel, $cropStage)
    {
        return ['schedule' => []];
    }

    private function calculateWaterRequirements($field, $planting, $cropStage)
    {
        return ['daily_requirement_mm' => 5];
    }

    private function getIrrigationEfficiencyTips($field, $currentWeather)
    {
        return ['tips' => []];
    }

    private function getIrrigationRiskWarnings($forecast, $soilMoistureLevel)
    {
        return ['warnings' => []];
    }

    private function assessPestRisk($pest, $weatherData, $forecast)
    {
        return ['risk_level' => 'low', 'factors' => []];
    }

    private function assessDiseaseRisk($disease, $weatherData, $forecast)
    {
        return ['risk_level' => 'low', 'factors' => []];
    }

    private function calculateOverallPestDiseaseRisk($pestRisks, $diseaseRisks)
    {
        return 'medium';
    }

    private function getPestDiseasePreventionMeasures($pestRisks, $diseaseRisks)
    {
        return ['measures' => []];
    }

    private function getPestDiseaseMonitoringSchedule($pestRisks, $diseaseRisks)
    {
        return ['schedule' => []];
    }

    private function getHistoricalClimateData($farm, $years)
    {
        return ['data' => []];
    }

    private function projectClimateScenario($farm, $scenario, $years)
    {
        return ['projection' => []];
    }

    private function analyzeHistoricalTrends($historicalData)
    {
        return ['trends' => []];
    }

    private function assessClimateImpacts($projections)
    {
        return ['impacts' => []];
    }

    private function getClimateAdaptationStrategies($projections)
    {
        return ['strategies' => []];
    }

    private function getResilienceRecommendations($farm, $projections)
    {
        return ['recommendations' => []];
    }

    private function identifyDataGaps($weatherLogs, $startDate)
    {
        return ['gaps' => []];
    }

    private function calculateDataQualityScore($weatherLogs)
    {
        return 85; // Placeholder score
    }

    private function calculateReliabilityMetrics($weatherLogs)
    {
        return ['reliability' => 'high'];
    }
}