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
        $startDate = now()->subDays($periodDays);
        $endDate = now();
        
        $allTemps = [];
        $fieldTrends = [];
        
        foreach ($fields as $field) {
            $weatherLogs = WeatherLog::where('field_id', $field->id)
                ->whereBetween('recorded_at', [$startDate, $endDate])
                ->orderBy('recorded_at')
                ->get();
            
            if ($weatherLogs->isEmpty()) {
                continue;
            }
            
            // Split into two halves to compare
            $midPoint = (int)($weatherLogs->count() / 2);
            $firstHalf = $weatherLogs->take($midPoint);
            $secondHalf = $weatherLogs->skip($midPoint);
            
            $firstHalfAvg = $firstHalf->avg('temperature') ?? 0;
            $secondHalfAvg = $secondHalf->avg('temperature') ?? 0;
            $change = $secondHalfAvg - $firstHalfAvg;
            
            $allTemps = array_merge($allTemps, $weatherLogs->pluck('temperature')->toArray());
            
            $fieldTrends[] = [
                'field_id' => $field->id,
                'field_name' => $field->name,
                'average_temp' => round($weatherLogs->avg('temperature'), 1),
                'min_temp' => round($weatherLogs->min('temperature'), 1),
                'max_temp' => round($weatherLogs->max('temperature'), 1),
                'change' => round($change, 2),
            ];
        }
        
        $overallAvg = !empty($allTemps) ? array_sum($allTemps) / count($allTemps) : 0;
        $overallChange = !empty($fieldTrends) ? array_sum(array_column($fieldTrends, 'change')) / count($fieldTrends) : 0;
        
        $trend = abs($overallChange) < 0.5 ? 'stable' : ($overallChange > 0 ? 'increasing' : 'decreasing');
        
        return [
            'trend' => $trend,
            'average_change' => round($overallChange, 2),
            'overall_average' => round($overallAvg, 1),
            'field_trends' => $fieldTrends,
            'period_days' => $periodDays,
        ];
    }

    private function getHumidityTrends($fields, $periodDays)
    {
        $startDate = now()->subDays($periodDays);
        $endDate = now();
        
        $allHumidities = [];
        $fieldTrends = [];
        
        foreach ($fields as $field) {
            $weatherLogs = WeatherLog::where('field_id', $field->id)
                ->whereBetween('recorded_at', [$startDate, $endDate])
                ->orderBy('recorded_at')
                ->get();
            
            if ($weatherLogs->isEmpty()) {
                continue;
            }
            
            $midPoint = (int)($weatherLogs->count() / 2);
            $firstHalf = $weatherLogs->take($midPoint);
            $secondHalf = $weatherLogs->skip($midPoint);
            
            $firstHalfAvg = $firstHalf->avg('humidity') ?? 0;
            $secondHalfAvg = $secondHalf->avg('humidity') ?? 0;
            $change = $secondHalfAvg - $firstHalfAvg;
            
            $allHumidities = array_merge($allHumidities, $weatherLogs->pluck('humidity')->toArray());
            
            $fieldTrends[] = [
                'field_id' => $field->id,
                'field_name' => $field->name,
                'average_humidity' => round($weatherLogs->avg('humidity'), 1),
                'min_humidity' => round($weatherLogs->min('humidity'), 1),
                'max_humidity' => round($weatherLogs->max('humidity'), 1),
                'change' => round($change, 2),
            ];
        }
        
        $overallAvg = !empty($allHumidities) ? array_sum($allHumidities) / count($allHumidities) : 0;
        $overallChange = !empty($fieldTrends) ? array_sum(array_column($fieldTrends, 'change')) / count($fieldTrends) : 0;
        
        $trend = abs($overallChange) < 1 ? 'stable' : ($overallChange > 0 ? 'increasing' : 'decreasing');
        
        return [
            'trend' => $trend,
            'average_change' => round($overallChange, 2),
            'overall_average' => round($overallAvg, 1),
            'field_trends' => $fieldTrends,
            'period_days' => $periodDays,
        ];
    }

    private function getConditionsTrends($fields, $periodDays)
    {
        $startDate = now()->subDays($periodDays);
        $endDate = now();
        
        $allConditions = [];
        $fieldConditions = [];
        
        foreach ($fields as $field) {
            $weatherLogs = WeatherLog::where('field_id', $field->id)
                ->whereBetween('recorded_at', [$startDate, $endDate])
                ->get();
            
            if ($weatherLogs->isEmpty()) {
                continue;
            }
            
            $conditions = $weatherLogs->pluck('conditions')->filter()->toArray();
            $allConditions = array_merge($allConditions, $conditions);
            
            $conditionCounts = array_count_values($conditions);
            arsort($conditionCounts);
            $mostCommon = !empty($conditionCounts) ? array_key_first($conditionCounts) : 'unknown';
            $stability = count($conditionCounts) <= 2 ? 'high' : (count($conditionCounts) <= 4 ? 'moderate' : 'low');
            
            $fieldConditions[] = [
                'field_id' => $field->id,
                'field_name' => $field->name,
                'most_common' => $mostCommon,
                'stability' => $stability,
                'unique_conditions' => count($conditionCounts),
            ];
        }
        
        $overallConditionCounts = array_count_values($allConditions);
        arsort($overallConditionCounts);
        $mostCommon = !empty($overallConditionCounts) ? array_key_first($overallConditionCounts) : 'clear';
        $stability = count($overallConditionCounts) <= 2 ? 'high' : (count($overallConditionCounts) <= 4 ? 'moderate' : 'low');
        
        return [
            'most_common' => $mostCommon,
            'stability' => $stability,
            'unique_conditions_count' => count($overallConditionCounts),
            'condition_distribution' => $overallConditionCounts,
            'field_conditions' => $fieldConditions,
            'period_days' => $periodDays,
        ];
    }

    private function getWindTrends($fields, $periodDays)
    {
        $startDate = now()->subDays($periodDays);
        $endDate = now();
        
        $allWindSpeeds = [];
        $fieldTrends = [];
        
        foreach ($fields as $field) {
            $weatherLogs = WeatherLog::where('field_id', $field->id)
                ->whereBetween('recorded_at', [$startDate, $endDate])
                ->orderBy('recorded_at')
                ->get();
            
            if ($weatherLogs->isEmpty()) {
                continue;
            }
            
            $midPoint = (int)($weatherLogs->count() / 2);
            $firstHalf = $weatherLogs->take($midPoint);
            $secondHalf = $weatherLogs->skip($midPoint);
            
            $firstHalfAvg = $firstHalf->avg('wind_speed') ?? 0;
            $secondHalfAvg = $secondHalf->avg('wind_speed') ?? 0;
            $change = $secondHalfAvg - $firstHalfAvg;
            
            $allWindSpeeds = array_merge($allWindSpeeds, $weatherLogs->pluck('wind_speed')->filter()->toArray());
            
            $fieldTrends[] = [
                'field_id' => $field->id,
                'field_name' => $field->name,
                'average_speed' => round($weatherLogs->avg('wind_speed'), 1),
                'max_speed' => round($weatherLogs->max('wind_speed'), 1),
                'change' => round($change, 2),
            ];
        }
        
        $overallAvg = !empty($allWindSpeeds) ? array_sum($allWindSpeeds) / count($allWindSpeeds) : 0;
        $overallChange = !empty($fieldTrends) ? array_sum(array_column($fieldTrends, 'change')) / count($fieldTrends) : 0;
        
        $trend = abs($overallChange) < 0.5 ? 'stable' : ($overallChange > 0 ? 'increasing' : 'decreasing');
        
        return [
            'trend' => $trend,
            'average_speed' => round($overallAvg, 1),
            'average_change' => round($overallChange, 2),
            'field_trends' => $fieldTrends,
            'period_days' => $periodDays,
        ];
    }

    private function analyzeTrends($trends)
    {
        if (empty($trends)) {
            return ['overall_stability' => 'unknown', 'risk_level' => 'unknown', 'message' => 'No trend data available'];
        }
        
        $stabilityScores = [];
        $riskFactors = [];
        
        // Analyze temperature stability
        if (isset($trends['temperature'])) {
            $tempChange = abs($trends['temperature']['average_change'] ?? 0);
            if ($tempChange > 2) {
                $riskFactors[] = 'temperature_volatility';
                $stabilityScores[] = 3; // Low stability
            } elseif ($tempChange > 1) {
                $stabilityScores[] = 6; // Moderate stability
            } else {
                $stabilityScores[] = 9; // High stability
            }
        }
        
        // Analyze humidity stability
        if (isset($trends['humidity'])) {
            $humidityChange = abs($trends['humidity']['average_change'] ?? 0);
            if ($humidityChange > 5) {
                $riskFactors[] = 'humidity_volatility';
                $stabilityScores[] = 3;
            } elseif ($humidityChange > 2) {
                $stabilityScores[] = 6;
            } else {
                $stabilityScores[] = 9;
            }
        }
        
        // Analyze conditions stability
        if (isset($trends['conditions'])) {
            $stability = $trends['conditions']['stability'] ?? 'moderate';
            if ($stability === 'high') {
                $stabilityScores[] = 9;
            } elseif ($stability === 'moderate') {
                $stabilityScores[] = 6;
            } else {
                $stabilityScores[] = 3;
                $riskFactors[] = 'variable_weather_conditions';
            }
        }
        
        // Analyze wind stability
        if (isset($trends['wind'])) {
            $windChange = abs($trends['wind']['average_change'] ?? 0);
            $windSpeed = $trends['wind']['average_speed'] ?? 0;
            if ($windChange > 2 || $windSpeed > 15) {
                $riskFactors[] = 'wind_conditions';
                $stabilityScores[] = 4;
            } elseif ($windChange > 1) {
                $stabilityScores[] = 6;
            } else {
                $stabilityScores[] = 8;
            }
        }
        
        $avgStability = !empty($stabilityScores) ? array_sum($stabilityScores) / count($stabilityScores) : 5;
        
        $overallStability = $avgStability >= 8 ? 'high' : ($avgStability >= 5 ? 'moderate' : 'low');
        $riskLevel = count($riskFactors) >= 3 ? 'high' : (count($riskFactors) >= 1 ? 'medium' : 'low');
        
        return [
            'overall_stability' => $overallStability,
            'stability_score' => round($avgStability, 1),
            'risk_level' => $riskLevel,
            'risk_factors' => $riskFactors,
            'trend_summary' => [
                'temperature' => $trends['temperature']['trend'] ?? 'unknown',
                'humidity' => $trends['humidity']['trend'] ?? 'unknown',
                'conditions' => $trends['conditions']['stability'] ?? 'unknown',
                'wind' => $trends['wind']['trend'] ?? 'unknown',
            ],
        ];
    }

    private function identifySeasonalPatterns($fields, $periodDays)
    {
        $startDate = now()->subDays($periodDays);
        $endDate = now();
        
        $monthlyData = [];
        
        foreach ($fields as $field) {
            $weatherLogs = WeatherLog::where('field_id', $field->id)
                ->whereBetween('recorded_at', [$startDate, $endDate])
                ->get();
            
            if ($weatherLogs->isEmpty()) {
                continue;
            }
            
            // Group by month
            foreach ($weatherLogs as $log) {
                $month = Carbon::parse($log->recorded_at)->format('Y-m');
                if (!isset($monthlyData[$month])) {
                    $monthlyData[$month] = [
                        'temperature' => [],
                        'rainfall' => [],
                        'humidity' => [],
                    ];
                }
                
                if ($log->temperature !== null) {
                    $monthlyData[$month]['temperature'][] = $log->temperature;
                }
                if ($log->rainfall !== null) {
                    $monthlyData[$month]['rainfall'][] = $log->rainfall;
                }
                if ($log->humidity !== null) {
                    $monthlyData[$month]['humidity'][] = $log->humidity;
                }
            }
        }
        
        if (empty($monthlyData)) {
            return ['season' => 'unknown', 'pattern_strength' => 'none', 'message' => 'Insufficient data for seasonal analysis'];
        }
        
        // Determine season based on current month
        $currentMonth = (int)now()->format('n');
        $season = match(true) {
            in_array($currentMonth, [12, 1, 2]) => 'dry',
            in_array($currentMonth, [3, 4, 5]) => 'transition',
            in_array($currentMonth, [6, 7, 8]) => 'wet',
            default => 'transition',
        };
        
        // Calculate pattern strength based on data consistency
        $tempVariances = [];
        foreach ($monthlyData as $month => $data) {
            if (!empty($data['temperature'])) {
                $avg = array_sum($data['temperature']) / count($data['temperature']);
                $variance = 0;
                foreach ($data['temperature'] as $temp) {
                    $variance += pow($temp - $avg, 2);
                }
                $tempVariances[] = $variance / count($data['temperature']);
            }
        }
        
        $avgVariance = !empty($tempVariances) ? array_sum($tempVariances) / count($tempVariances) : 100;
        $patternStrength = $avgVariance < 5 ? 'strong' : ($avgVariance < 15 ? 'moderate' : 'weak');
        
        return [
            'season' => $season,
            'pattern_strength' => $patternStrength,
            'monthly_data' => array_map(function($data) {
                return [
                    'avg_temperature' => !empty($data['temperature']) ? round(array_sum($data['temperature']) / count($data['temperature']), 1) : null,
                    'total_rainfall' => !empty($data['rainfall']) ? round(array_sum($data['rainfall']), 1) : null,
                    'avg_humidity' => !empty($data['humidity']) ? round(array_sum($data['humidity']) / count($data['humidity']), 1) : null,
                ];
            }, $monthlyData),
            'data_points' => count($monthlyData),
        ];
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
        if ($weatherData->isEmpty()) {
            return $this->simpleYieldPrediction($planting, $weatherData);
        }
        
        $baseYield = 4500; // kg per hectare
        $weatherScore = $this->calculateWeatherScore($weatherData);
        
        // Factor 1: Temperature optimization (0-30% impact)
        $optimalTempDays = $weatherData->whereBetween('temperature', [22, 28])->count();
        $totalDays = $weatherData->count();
        $tempScore = $totalDays > 0 ? ($optimalTempDays / $totalDays) * 100 : 50;
        $tempFactor = 1 + (($tempScore - 50) / 100) * 0.3;
        
        // Factor 2: Rainfall adequacy (0-25% impact)
        $totalRainfall = $weatherData->sum('rainfall');
        $expectedRainfall = 1000; // mm per season
        $rainfallRatio = min(1.2, max(0.5, $totalRainfall / $expectedRainfall));
        $rainfallFactor = 0.75 + ($rainfallRatio * 0.25);
        
        // Factor 3: Humidity optimization (0-15% impact)
        $optimalHumidityDays = $weatherData->whereBetween('humidity', [65, 80])->count();
        $humidityScore = $totalDays > 0 ? ($optimalHumidityDays / $totalDays) * 100 : 50;
        $humidityFactor = 1 + (($humidityScore - 50) / 100) * 0.15;
        
        // Factor 4: Stress events (0-20% negative impact)
        $stressEvents = $this->identifyStressEvents($weatherData);
        $stressDays = 0;
        foreach ($stressEvents as $event) {
            $stressDays += $event['duration_days'] ?? 1;
        }
        $stressRatio = min(1, $stressDays / max(1, $totalDays));
        $stressFactor = 1 - ($stressRatio * 0.2);
        
        // Factor 5: Growth stage weather alignment (0-10% impact)
        $growthStageScore = $this->calculateGrowthStageAlignment($planting, $weatherData);
        $growthFactor = 0.9 + (($growthStageScore / 100) * 0.1);
        
        // Combine all factors
        $combinedFactor = $tempFactor * $rainfallFactor * $humidityFactor * $stressFactor * $growthFactor;
        $predictedYield = $baseYield * $combinedFactor;
        
        // Calculate confidence based on data quality
        $dataQuality = min(100, ($totalDays / 90) * 100); // Assuming 90 days is full season
        $confidenceLevel = $dataQuality >= 80 ? 'high' : ($dataQuality >= 50 ? 'medium' : 'low');
        
        return [
            'predicted_yield_kg_per_ha' => round($predictedYield, 0),
            'confidence_level' => $confidenceLevel,
            'confidence_score' => round($dataQuality, 1),
            'factors' => [
                'base_yield' => $baseYield,
                'temperature_factor' => round($tempFactor, 3),
                'rainfall_factor' => round($rainfallFactor, 3),
                'humidity_factor' => round($humidityFactor, 3),
                'stress_factor' => round($stressFactor, 3),
                'growth_stage_factor' => round($growthFactor, 3),
                'combined_factor' => round($combinedFactor, 3),
                'weather_score' => round($weatherScore, 1),
            ],
            'stress_events_count' => count($stressEvents),
            'stress_days' => $stressDays,
        ];
    }

    private function mlBasedYieldPrediction($planting, $weatherData)
    {
        // Note: This is a simplified ML-like approach using weighted factors
        // A true ML implementation would require training data and a model
        
        if ($weatherData->isEmpty()) {
            return [
                'message' => 'Insufficient data for ML-based prediction',
                'fallback' => $this->simpleYieldPrediction($planting, $weatherData),
            ];
        }
        
        // Use advanced prediction as base
        $advancedPrediction = $this->advancedYieldPrediction($planting, $weatherData);
        
        // Add ML-like adjustments based on historical patterns
        // This would normally use a trained model, but we'll use pattern matching
        
        $historicalYield = $this->getHistoricalYieldForPlanting($planting);
        if ($historicalYield) {
            // Adjust prediction based on historical performance
            $historicalAvg = $historicalYield['average_yield'] ?? 4500;
            $currentPrediction = $advancedPrediction['predicted_yield_kg_per_ha'];
            
            // Weighted average: 60% current prediction, 40% historical average
            $mlAdjustedYield = ($currentPrediction * 0.6) + ($historicalAvg * 0.4);
            
            return [
                'predicted_yield_kg_per_ha' => round($mlAdjustedYield, 0),
                'confidence_level' => 'high',
                'prediction_method' => 'ml_enhanced',
                'base_prediction' => $currentPrediction,
                'historical_adjustment' => $historicalAvg,
                'adjustment_weight' => 0.4,
                'factors' => array_merge($advancedPrediction['factors'] ?? [], [
                    'historical_baseline' => $historicalAvg,
                    'ml_adjustment_applied' => true,
                ]),
            ];
        }
        
        return [
            'message' => 'ML-based prediction using advanced model (no historical data available)',
            'prediction' => $advancedPrediction,
            'note' => 'Full ML model requires historical yield data for training',
        ];
    }
    
    private function getHistoricalYieldForPlanting($planting)
    {
        // Get historical yields for similar plantings
        $field = $planting->field;
        if (!$field) {
            return null;
        }
        
        $historicalHarvests = Harvest::whereHas('planting', function($q) use ($field) {
            $q->where('field_id', $field->id)
              ->where('crop_type', $planting->crop_type ?? 'rice');
        })
        ->where('id', '!=', $planting->id)
        ->get();
        
        if ($historicalHarvests->isEmpty()) {
            return null;
        }
        
        $yields = $historicalHarvests->pluck('yield')->filter();
        if ($yields->isEmpty()) {
            return null;
        }
        
        return [
            'average_yield' => round($yields->avg(), 0),
            'min_yield' => round($yields->min(), 0),
            'max_yield' => round($yields->max(), 0),
            'sample_size' => $yields->count(),
        ];
    }
    
    private function calculateGrowthStageAlignment($planting, $weatherData)
    {
        if ($weatherData->isEmpty() || !$planting) {
            return 50;
        }
        
        $currentStage = $planting->current_stage ?? 'seedling';
        $stageWeather = $this->getWeatherForStage($planting, $weatherData, $currentStage);
        
        if ($stageWeather->isEmpty()) {
            return 50;
        }
        
        $optimalDays = $this->countOptimalDays($stageWeather, $currentStage);
        $totalDays = $stageWeather->count();
        
        return $totalDays > 0 ? ($optimalDays / $totalDays) * 100 : 50;
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
        if ($weatherData->isEmpty() || !$planting) {
            return ['analysis' => 'Insufficient data for growth stage analysis'];
        }

        $currentStage = $planting->current_stage ?? 'seedling';
        $stageWeather = $this->getWeatherForStage($planting, $weatherData, $currentStage);
        
        $analysis = [
            'current_stage' => $currentStage,
            'temperature_avg' => $stageWeather->avg('temperature'),
            'humidity_avg' => $stageWeather->avg('humidity'),
            'rainfall_total' => $stageWeather->sum('rainfall'),
            'optimal_conditions_days' => $this->countOptimalDays($stageWeather, $currentStage),
            'stress_days' => $this->countStressDays($stageWeather, $currentStage),
            'recommendations' => $this->getStageRecommendations($currentStage, $stageWeather),
        ];

        return $analysis;
    }

    private function getWeatherForStage($planting, $weatherData, $stage)
    {
        // Get weather data for the current growth stage period
        $stageStartDate = $this->getStageStartDate($planting, $stage);
        return $weatherData->where('recorded_at', '>=', $stageStartDate);
    }

    private function getStageStartDate($planting, $stage)
    {
        // Estimate stage start date based on planting date and stage
        $daysPerStage = [
            'seedling' => 0,
            'tillering' => 15,
            'flowering' => 60,
            'grain_filling' => 90,
            'ripening' => 120,
        ];
        
        $daysOffset = $daysPerStage[$stage] ?? 0;
        return $planting->planting_date ? 
            Carbon::parse($planting->planting_date)->addDays($daysOffset) : 
            now()->subDays(30);
    }

    private function countOptimalDays($weatherData, $stage)
    {
        $optimalRanges = [
            'seedling' => ['temp' => [20, 30], 'humidity' => [70, 90]],
            'tillering' => ['temp' => [25, 32], 'humidity' => [70, 85]],
            'flowering' => ['temp' => [25, 30], 'humidity' => [70, 80]],
            'grain_filling' => ['temp' => [20, 28], 'humidity' => [60, 75]],
            'ripening' => ['temp' => [20, 28], 'humidity' => [50, 70]],
        ];

        $range = $optimalRanges[$stage] ?? ['temp' => [20, 30], 'humidity' => [60, 80]];
        
        return $weatherData->filter(function($log) use ($range) {
            $temp = $log->temperature ?? 0;
            $humidity = $log->humidity ?? 0;
            return $temp >= $range['temp'][0] && $temp <= $range['temp'][1] &&
                   $humidity >= $range['humidity'][0] && $humidity <= $range['humidity'][1];
        })->count();
    }

    private function countStressDays($weatherData, $stage)
    {
        $stressThresholds = [
            'seedling' => ['temp_high' => 35, 'temp_low' => 15, 'humidity_low' => 50],
            'tillering' => ['temp_high' => 38, 'temp_low' => 18, 'humidity_low' => 55],
            'flowering' => ['temp_high' => 35, 'temp_low' => 20, 'humidity_low' => 60],
            'grain_filling' => ['temp_high' => 32, 'temp_low' => 18, 'humidity_low' => 50],
            'ripening' => ['temp_high' => 30, 'temp_low' => 15, 'humidity_low' => 40],
        ];

        $thresholds = $stressThresholds[$stage] ?? ['temp_high' => 35, 'temp_low' => 15, 'humidity_low' => 50];
        
        return $weatherData->filter(function($log) use ($thresholds) {
            $temp = $log->temperature ?? 0;
            $humidity = $log->humidity ?? 0;
            return $temp > $thresholds['temp_high'] || 
                   $temp < $thresholds['temp_low'] || 
                   $humidity < $thresholds['humidity_low'];
        })->count();
    }

    private function getStageRecommendations($stage, $weatherData)
    {
        $recommendations = [];
        
        $avgTemp = $weatherData->avg('temperature');
        $avgHumidity = $weatherData->avg('humidity');
        
        if ($avgTemp > 32) {
            $recommendations[] = 'High temperature detected. Consider increasing irrigation frequency.';
        }
        if ($avgHumidity < 60) {
            $recommendations[] = 'Low humidity detected. Monitor water levels closely.';
        }
        if ($weatherData->where('rainfall', '>', 50)->count() > 0) {
            $recommendations[] = 'Heavy rainfall detected. Ensure proper drainage.';
        }
        
        return $recommendations;
    }

    private function identifyStressEvents($weatherData)
    {
        if ($weatherData->isEmpty()) {
        return ['stress_events' => []];
        }
        
        $stressEvents = [];
        $eventId = 1;
        
        // Identify heat stress events (temperature > 35°C)
        $heatEvents = $weatherData->filter(function($log) {
            return ($log->temperature ?? 0) > 35;
        });
        
        if ($heatEvents->isNotEmpty()) {
            $consecutiveHeatDays = $this->findConsecutivePeriods($heatEvents, 'temperature', 35, '>');
            foreach ($consecutiveHeatDays as $period) {
                $stressEvents[] = [
                    'id' => $eventId++,
                    'type' => 'heat_stress',
                    'severity' => $period['max_value'] > 38 ? 'severe' : ($period['max_value'] > 36 ? 'moderate' : 'mild'),
                    'start_date' => $period['start'],
                    'end_date' => $period['end'],
                    'duration_days' => $period['duration'],
                    'max_temperature' => $period['max_value'],
                    'description' => "Heat stress event with temperatures up to {$period['max_value']}°C",
                ];
            }
        }
        
        // Identify cold stress events (temperature < 15°C)
        $coldEvents = $weatherData->filter(function($log) {
            return ($log->temperature ?? 0) < 15;
        });
        
        if ($coldEvents->isNotEmpty()) {
            $consecutiveColdDays = $this->findConsecutivePeriods($coldEvents, 'temperature', 15, '<');
            foreach ($consecutiveColdDays as $period) {
                $stressEvents[] = [
                    'id' => $eventId++,
                    'type' => 'cold_stress',
                    'severity' => $period['min_value'] < 10 ? 'severe' : ($period['min_value'] < 12 ? 'moderate' : 'mild'),
                    'start_date' => $period['start'],
                    'end_date' => $period['end'],
                    'duration_days' => $period['duration'],
                    'min_temperature' => $period['min_value'],
                    'description' => "Cold stress event with temperatures as low as {$period['min_value']}°C",
                ];
            }
        }
        
        // Identify drought stress (low humidity < 40% for extended period)
        $droughtEvents = $weatherData->filter(function($log) {
            return ($log->humidity ?? 0) < 40 && ($log->rainfall ?? 0) < 1;
        });
        
        if ($droughtEvents->isNotEmpty()) {
            $consecutiveDroughtDays = $this->findConsecutivePeriods($droughtEvents, 'humidity', 40, '<');
            foreach ($consecutiveDroughtDays as $period) {
                if ($period['duration'] >= 3) { // Only count if 3+ days
                    $stressEvents[] = [
                        'id' => $eventId++,
                        'type' => 'drought_stress',
                        'severity' => $period['duration'] > 7 ? 'severe' : ($period['duration'] > 5 ? 'moderate' : 'mild'),
                        'start_date' => $period['start'],
                        'end_date' => $period['end'],
                        'duration_days' => $period['duration'],
                        'avg_humidity' => $period['avg_value'],
                        'description' => "Drought stress event with low humidity for {$period['duration']} days",
                    ];
                }
            }
        }
        
        // Identify flooding stress (heavy rainfall > 50mm in a day)
        $floodEvents = $weatherData->filter(function($log) {
            return ($log->rainfall ?? 0) > 50;
        });
        
        if ($floodEvents->isNotEmpty()) {
            foreach ($floodEvents as $log) {
                $stressEvents[] = [
                    'id' => $eventId++,
                    'type' => 'flooding_stress',
                    'severity' => $log->rainfall > 100 ? 'severe' : ($log->rainfall > 75 ? 'moderate' : 'mild'),
                    'start_date' => Carbon::parse($log->recorded_at)->toDateString(),
                    'end_date' => Carbon::parse($log->recorded_at)->toDateString(),
                    'duration_days' => 1,
                    'rainfall_mm' => $log->rainfall,
                    'description' => "Heavy rainfall event with {$log->rainfall}mm in one day",
                ];
            }
        }
        
        return [
            'stress_events' => $stressEvents,
            'total_events' => count($stressEvents),
            'event_summary' => $this->summarizeStressEvents($stressEvents),
        ];
    }
    
    private function findConsecutivePeriods($events, $field, $threshold, $operator)
    {
        $periods = [];
        $currentPeriod = null;
        
        foreach ($events->sortBy('recorded_at') as $log) {
            $date = Carbon::parse($log->recorded_at)->toDateString();
            $value = $log->$field ?? 0;
            $meetsCondition = $operator === '>' ? $value > $threshold : $value < $threshold;
            
            if ($meetsCondition) {
                if ($currentPeriod === null) {
                    $currentPeriod = [
                        'start' => $date,
                        'end' => $date,
                        'duration' => 1,
                        'max_value' => $value,
                        'min_value' => $value,
                        'avg_value' => $value,
                        'values' => [$value],
                    ];
                } else {
                    $currentPeriod['end'] = $date;
                    $currentPeriod['duration']++;
                    $currentPeriod['values'][] = $value;
                    $currentPeriod['max_value'] = max($currentPeriod['max_value'], $value);
                    $currentPeriod['min_value'] = min($currentPeriod['min_value'], $value);
                    $currentPeriod['avg_value'] = array_sum($currentPeriod['values']) / count($currentPeriod['values']);
                }
            } else {
                if ($currentPeriod !== null) {
                    $periods[] = $currentPeriod;
                    $currentPeriod = null;
                }
            }
        }
        
        if ($currentPeriod !== null) {
            $periods[] = $currentPeriod;
        }
        
        return $periods;
    }
    
    private function summarizeStressEvents($events)
    {
        $summary = [
            'heat_stress' => ['count' => 0, 'total_days' => 0],
            'cold_stress' => ['count' => 0, 'total_days' => 0],
            'drought_stress' => ['count' => 0, 'total_days' => 0],
            'flooding_stress' => ['count' => 0, 'total_days' => 0],
        ];
        
        foreach ($events as $event) {
            $type = $event['type'];
            if (isset($summary[$type])) {
                $summary[$type]['count']++;
                $summary[$type]['total_days'] += $event['duration_days'] ?? 1;
            }
        }
        
        return $summary;
    }

    private function calculateYieldImpactFactors($weatherData, $planting)
    {
        if ($weatherData->isEmpty() || !$planting) {
        return ['impact_factors' => []];
        }
        
        $factors = [];
        
        // Temperature impact
        $avgTemp = $weatherData->avg('temperature');
        $optimalTempRange = [25, 30]; // Optimal for rice
        if ($avgTemp < $optimalTempRange[0]) {
            $factors[] = [
                'factor' => 'low_temperature',
                'impact' => 'negative',
                'severity' => $avgTemp < 20 ? 'high' : 'medium',
                'description' => "Average temperature ({$avgTemp}°C) below optimal range",
                'yield_impact_percent' => $avgTemp < 20 ? -15 : -8,
            ];
        } elseif ($avgTemp > $optimalTempRange[1]) {
            $factors[] = [
                'factor' => 'high_temperature',
                'impact' => 'negative',
                'severity' => $avgTemp > 35 ? 'high' : 'medium',
                'description' => "Average temperature ({$avgTemp}°C) above optimal range",
                'yield_impact_percent' => $avgTemp > 35 ? -20 : -10,
            ];
        } else {
            $factors[] = [
                'factor' => 'optimal_temperature',
                'impact' => 'positive',
                'severity' => 'low',
                'description' => "Temperature within optimal range",
                'yield_impact_percent' => 5,
            ];
        }
        
        // Rainfall impact
        $totalRainfall = $weatherData->sum('rainfall');
        $optimalRainfall = 1000; // mm per season (approximate)
        $rainfallRatio = $totalRainfall / max($optimalRainfall, 1);
        
        if ($rainfallRatio < 0.7) {
            $factors[] = [
                'factor' => 'insufficient_rainfall',
                'impact' => 'negative',
                'severity' => $rainfallRatio < 0.5 ? 'high' : 'medium',
                'description' => "Total rainfall ({$totalRainfall}mm) below optimal",
                'yield_impact_percent' => $rainfallRatio < 0.5 ? -25 : -12,
            ];
        } elseif ($rainfallRatio > 1.5) {
            $factors[] = [
                'factor' => 'excessive_rainfall',
                'impact' => 'negative',
                'severity' => $rainfallRatio > 2 ? 'high' : 'medium',
                'description' => "Total rainfall ({$totalRainfall}mm) above optimal",
                'yield_impact_percent' => $rainfallRatio > 2 ? -18 : -8,
            ];
        } else {
            $factors[] = [
                'factor' => 'adequate_rainfall',
                'impact' => 'positive',
                'severity' => 'low',
                'description' => "Rainfall within acceptable range",
                'yield_impact_percent' => 3,
            ];
        }
        
        // Humidity impact
        $avgHumidity = $weatherData->avg('humidity');
        if ($avgHumidity < 60) {
            $factors[] = [
                'factor' => 'low_humidity',
                'impact' => 'negative',
                'severity' => $avgHumidity < 50 ? 'medium' : 'low',
                'description' => "Low average humidity ({$avgHumidity}%)",
                'yield_impact_percent' => -5,
            ];
        } elseif ($avgHumidity > 90) {
            $factors[] = [
                'factor' => 'high_humidity',
                'impact' => 'negative',
                'severity' => 'medium',
                'description' => "Very high humidity ({$avgHumidity}%) may increase disease risk",
                'yield_impact_percent' => -8,
            ];
        }
        
        // Stress events impact
        $stressEvents = $this->identifyStressEvents($weatherData);
        $totalStressDays = array_sum(array_column($stressEvents['event_summary'] ?? [], 'total_days'));
        if ($totalStressDays > 10) {
            $factors[] = [
                'factor' => 'extended_stress_periods',
                'impact' => 'negative',
                'severity' => $totalStressDays > 20 ? 'high' : 'medium',
                'description' => "Extended periods of weather stress ({$totalStressDays} days)",
                'yield_impact_percent' => $totalStressDays > 20 ? -15 : -8,
            ];
        }
        
        // Calculate overall yield impact
        $totalImpact = array_sum(array_column($factors, 'yield_impact_percent'));
        
        return [
            'impact_factors' => $factors,
            'total_yield_impact_percent' => round($totalImpact, 1),
            'expected_yield_adjustment' => $totalImpact,
        ];
    }

    private function generateImpactBasedRecommendations($weatherData, $planting)
    {
        if ($weatherData->isEmpty() || !$planting) {
            return ['recommendations' => []];
        }
        
        $recommendations = [];
        $impactFactors = $this->calculateYieldImpactFactors($weatherData, $planting);
        
        foreach ($impactFactors['impact_factors'] ?? [] as $factor) {
            if ($factor['impact'] === 'negative' && ($factor['severity'] ?? 'low') !== 'low') {
                $recommendations[] = [
                    'priority' => $factor['severity'] === 'high' ? 'high' : 'medium',
                    'category' => $factor['factor'],
                    'recommendation' => $this->getRecommendationForFactor($factor),
                    'expected_benefit' => "May reduce yield impact by " . abs(round($factor['yield_impact_percent'] * 0.5, 1)) . "%",
                ];
            }
        }
        
        // Add general recommendations
        $avgTemp = $weatherData->avg('temperature');
        $avgHumidity = $weatherData->avg('humidity');
        $totalRainfall = $weatherData->sum('rainfall');
        
        if ($avgTemp > 32) {
            $recommendations[] = [
                'priority' => 'medium',
                'category' => 'temperature_management',
                'recommendation' => 'Increase irrigation frequency to help cool crops during high temperatures',
                'expected_benefit' => 'Reduces heat stress on plants',
            ];
        }
        
        if ($avgHumidity > 85) {
            $recommendations[] = [
                'priority' => 'medium',
                'category' => 'disease_prevention',
                'recommendation' => 'Apply preventive fungicides and ensure good field ventilation',
                'expected_benefit' => 'Reduces disease risk from high humidity',
            ];
        }
        
        if ($totalRainfall < 500) {
            $recommendations[] = [
                'priority' => 'high',
                'category' => 'irrigation',
                'recommendation' => 'Implement supplemental irrigation to compensate for low rainfall',
                'expected_benefit' => 'Maintains adequate soil moisture for crop growth',
            ];
        }
        
        return [
            'recommendations' => $recommendations,
            'total_recommendations' => count($recommendations),
            'high_priority_count' => collect($recommendations)->where('priority', 'high')->count(),
        ];
    }
    
    private function getRecommendationForFactor($factor)
    {
        $recommendations = [
            'low_temperature' => 'Consider using cold-tolerant varieties or adjust planting dates to warmer periods',
            'high_temperature' => 'Increase irrigation frequency, provide shade if possible, and use heat-tolerant varieties',
            'insufficient_rainfall' => 'Implement supplemental irrigation and water conservation measures',
            'excessive_rainfall' => 'Improve field drainage and consider raised bed systems',
            'low_humidity' => 'Increase irrigation frequency and consider mulching to retain soil moisture',
            'high_humidity' => 'Improve field ventilation, apply preventive fungicides, and ensure proper drainage',
            'extended_stress_periods' => 'Review overall field management practices and consider stress-tolerant varieties',
        ];
        
        return $recommendations[$factor['factor']] ?? 'Monitor conditions closely and adjust management practices as needed';
    }

    private function getHistoricalWeatherPatterns($farm, $cropType)
    {
        $fields = $farm->fields;
        $patterns = [];
        
        // Get weather data for last 3 years
        $years = [now()->year - 2, now()->year - 1, now()->year];
        
        foreach ($fields as $field) {
            $fieldPatterns = [];
            
            foreach ($years as $year) {
                $yearLogs = WeatherLog::where('field_id', $field->id)
                    ->whereYear('recorded_at', $year)
                    ->get();
                
                if ($yearLogs->isNotEmpty()) {
                    // Group by month
                    $monthlyPatterns = [];
                    for ($month = 1; $month <= 12; $month++) {
                        $monthLogs = $yearLogs->filter(function($log) use ($year, $month) {
                            return Carbon::parse($log->recorded_at)->year == $year &&
                                   Carbon::parse($log->recorded_at)->month == $month;
                        });
                        
                        if ($monthLogs->isNotEmpty()) {
                            $monthlyPatterns[$month] = [
                                'avg_temperature' => round($monthLogs->avg('temperature'), 1),
                                'total_rainfall' => round($monthLogs->sum('rainfall'), 1),
                                'avg_humidity' => round($monthLogs->avg('humidity'), 1),
                            ];
                        }
                    }
                    
                    $fieldPatterns[$year] = [
                        'year' => $year,
                        'monthly_patterns' => $monthlyPatterns,
                        'annual_avg_temp' => round($yearLogs->avg('temperature'), 1),
                        'annual_total_rainfall' => round($yearLogs->sum('rainfall'), 1),
                    ];
                }
            }
            
            if (!empty($fieldPatterns)) {
                $patterns[$field->id] = [
                    'field_id' => $field->id,
                    'field_name' => $field->name,
                    'yearly_patterns' => $fieldPatterns,
                ];
            }
        }
        
        return ['patterns' => $patterns];
    }

    private function evaluateMonthForPlanting($farm, $cropType, $targetDate, $historicalData)
    {
        $targetMonth = (int)$targetDate->format('n');
        $targetYear = (int)$targetDate->format('Y');
        
        $suitabilityScore = 50; // Base score
        $factors = [];
        
        // Analyze historical patterns for this month
        $monthlyPatterns = [];
        foreach ($historicalData['patterns'] ?? [] as $fieldPattern) {
            foreach ($fieldPattern['yearly_patterns'] ?? [] as $yearData) {
                if (isset($yearData['monthly_patterns'][$targetMonth])) {
                    $monthlyPatterns[] = $yearData['monthly_patterns'][$targetMonth];
                }
            }
        }
        
        if (!empty($monthlyPatterns)) {
            // Calculate average conditions for this month
            $avgTemp = array_sum(array_column($monthlyPatterns, 'avg_temperature')) / count($monthlyPatterns);
            $avgRainfall = array_sum(array_column($monthlyPatterns, 'total_rainfall')) / count($monthlyPatterns);
            $avgHumidity = array_sum(array_column($monthlyPatterns, 'avg_humidity')) / count($monthlyPatterns);
            
            // Evaluate temperature suitability for rice
            if ($avgTemp >= 22 && $avgTemp <= 30) {
                $suitabilityScore += 20;
                $factors[] = 'optimal_temperature';
            } elseif ($avgTemp >= 20 && $avgTemp <= 32) {
                $suitabilityScore += 10;
                $factors[] = 'acceptable_temperature';
            } else {
                $suitabilityScore -= 15;
                $factors[] = 'suboptimal_temperature';
            }
            
            // Evaluate rainfall suitability
            if ($avgRainfall >= 100 && $avgRainfall <= 300) {
                $suitabilityScore += 15;
                $factors[] = 'adequate_rainfall';
            } elseif ($avgRainfall >= 50 && $avgRainfall <= 400) {
                $suitabilityScore += 5;
                $factors[] = 'moderate_rainfall';
            } else {
                $suitabilityScore -= 10;
                $factors[] = 'inadequate_rainfall';
            }
            
            // Evaluate humidity suitability
            if ($avgHumidity >= 65 && $avgHumidity <= 85) {
                $suitabilityScore += 10;
                $factors[] = 'optimal_humidity';
            } elseif ($avgHumidity >= 55 && $avgHumidity <= 90) {
                $suitabilityScore += 5;
                $factors[] = 'acceptable_humidity';
            } else {
                $suitabilityScore -= 5;
                $factors[] = 'suboptimal_humidity';
            }
        } else {
            // No historical data - use general seasonal knowledge
            $factors[] = 'no_historical_data';
            
            // General rice planting season (varies by region, but typically wet season)
            if (in_array($targetMonth, [6, 7, 8, 9])) {
                $suitabilityScore += 15;
                $factors[] = 'typical_planting_season';
            } elseif (in_array($targetMonth, [5, 10, 11])) {
                $suitabilityScore += 5;
                $factors[] = 'transition_season';
            } else {
                $suitabilityScore -= 10;
                $factors[] = 'off_season';
            }
        }
        
        // Clamp score between 0 and 100
        $suitabilityScore = max(0, min(100, $suitabilityScore));
        
        // Determine recommendation
        $recommendation = match(true) {
            $suitabilityScore >= 80 => 'highly_suitable',
            $suitabilityScore >= 60 => 'suitable',
            $suitabilityScore >= 40 => 'moderately_suitable',
            default => 'not_recommended',
        };
        
        return [
            'month' => $targetDate->format('M Y'),
            'month_number' => $targetMonth,
            'suitability_score' => round($suitabilityScore, 1),
            'recommendation' => $recommendation,
            'factors' => $factors,
            'historical_data_points' => count($monthlyPatterns),
        ];
    }

    private function identifyOptimalPlantingWindows($recommendations)
    {
        $windows = [];
        
        foreach ($recommendations as $recommendation) {
            $suitabilityScore = $recommendation['suitability_score'] ?? 0;
            $month = $recommendation['month'] ?? '';
            
            if ($suitabilityScore >= 80) {
                $windows[] = [
                    'month' => $month,
                    'suitability_score' => $suitabilityScore,
                    'status' => 'optimal',
                    'recommendation' => $recommendation['recommendation'] ?? 'suitable',
                ];
            } elseif ($suitabilityScore >= 60) {
                $windows[] = [
                    'month' => $month,
                    'suitability_score' => $suitabilityScore,
                    'status' => 'good',
                    'recommendation' => $recommendation['recommendation'] ?? 'suitable',
                ];
            }
        }
        
        // Sort by suitability score
        usort($windows, function($a, $b) {
            return ($b['suitability_score'] ?? 0) <=> ($a['suitability_score'] ?? 0);
        });
        
        return [
            'optimal_windows' => $windows,
            'best_window' => !empty($windows) ? $windows[0] : null,
            'total_optimal_months' => count($windows),
        ];
    }

    private function identifyPlantingRiskFactors($farm, $cropType)
    {
        $riskFactors = [];
        $fields = $farm->fields;
        
        // Analyze historical weather patterns
        $historicalData = $this->getHistoricalWeatherPatterns($farm, $cropType);
        
        foreach ($historicalData['patterns'] ?? [] as $fieldPattern) {
            $fieldRisks = [];
            
            foreach ($fieldPattern['yearly_patterns'] ?? [] as $yearData) {
                // Check for drought risk
                if (($yearData['annual_total_rainfall'] ?? 0) < 800) {
                    $fieldRisks[] = [
                        'type' => 'drought_risk',
                        'severity' => ($yearData['annual_total_rainfall'] ?? 0) < 600 ? 'high' : 'medium',
                        'description' => "Low annual rainfall ({$yearData['annual_total_rainfall']}mm) in {$yearData['year']}",
                    ];
                }
                
                // Check for extreme temperature risk
                if (($yearData['annual_avg_temp'] ?? 0) > 30) {
                    $fieldRisks[] = [
                        'type' => 'heat_risk',
                        'severity' => ($yearData['annual_avg_temp'] ?? 0) > 32 ? 'high' : 'medium',
                        'description' => "High average temperature ({$yearData['annual_avg_temp']}°C) in {$yearData['year']}",
                    ];
                }
            }
            
            if (!empty($fieldRisks)) {
                $riskFactors[$fieldPattern['field_id']] = [
                    'field_id' => $fieldPattern['field_id'],
                    'field_name' => $fieldPattern['field_name'],
                    'risks' => $fieldRisks,
                ];
            }
        }
        
        // General risk factors
        $generalRisks = [
            [
                'type' => 'weather_variability',
                'severity' => 'medium',
                'description' => 'Weather patterns may vary significantly from year to year',
            ],
            [
                'type' => 'climate_change',
                'severity' => 'low',
                'description' => 'Long-term climate trends may affect planting windows',
            ],
        ];
        
        return [
            'risk_factors' => array_values($riskFactors),
            'general_risks' => $generalRisks,
            'total_identified_risks' => count($riskFactors) + count($generalRisks),
        ];
    }

    private function getPlantingBestPractices($cropType)
    {
        $practices = [
            'rice' => [
                'Plant during optimal temperature range (25-30°C)',
                'Ensure adequate water availability (1000-1500mm per season)',
                'Prepare seedbed with proper soil moisture',
                'Use certified disease-free seeds',
                'Maintain proper field drainage',
                'Apply balanced fertilization',
                'Monitor weather forecasts before planting',
                'Time planting to avoid extreme weather periods',
                'Use appropriate planting density',
                'Implement integrated pest management',
            ],
            'corn' => [
                'Plant when soil temperature reaches 10°C',
                'Ensure adequate soil moisture at planting',
                'Use proper seed spacing',
                'Apply starter fertilizer',
                'Monitor for early season pests',
                'Time planting to avoid frost risk',
            ],
        ];
        
        $cropPractices = $practices[$cropType] ?? [
            'Monitor weather conditions before planting',
            'Ensure adequate soil preparation',
            'Use quality seeds',
            'Time planting for optimal conditions',
        ];
        
        return [
            'best_practices' => $cropPractices,
            'crop_type' => $cropType,
            'general_guidelines' => [
                'Monitor weather forecasts 1-2 weeks before planting',
                'Prepare fields based on expected weather conditions',
                'Have contingency plans for adverse weather',
                'Maintain field records for future planning',
            ],
        ];
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
        if (empty($risks) || !is_array($risks)) {
            return 0; // No risks = low risk score
        }

        $totalRisk = 0;
        $count = 0;
        
        foreach ($risks as $risk) {
            if (is_array($risk) && isset($risk['risk_level'])) {
                $riskValue = match($risk['risk_level']) {
                    'critical' => 90,
                    'high' => 70,
                    'medium' => 50,
                    'low' => 30,
                    default => 50,
                };
                
                // Adjust by probability if available
                if (isset($risk['probability'])) {
                    $riskValue *= $risk['probability'];
                }
                
                $totalRisk += $riskValue;
                $count++;
            } elseif (is_numeric($risk)) {
                $totalRisk += $risk;
                $count++;
            }
        }
        
        return $count > 0 ? min(100, round($totalRisk / $count)) : 0;
    }

    private function getMitigationStrategies($risks)
    {
        $strategies = [];
        
        foreach ($risks as $riskType => $risk) {
            if (!is_array($risk)) {
                continue;
            }
            
            $riskLevel = $risk['risk_level'] ?? 'low';
            $probability = $risk['probability'] ?? 0;
            
            if ($riskLevel === 'critical' || ($riskLevel === 'high' && $probability > 0.5)) {
                $strategies[] = [
                    'risk_type' => $riskType,
                    'risk_level' => $riskLevel,
                    'strategy' => $this->getStrategyForRiskType($riskType),
                    'priority' => 'high',
                    'timeline' => 'immediate',
                ];
            } elseif ($riskLevel === 'high' || ($riskLevel === 'medium' && $probability > 0.4)) {
                $strategies[] = [
                    'risk_type' => $riskType,
                    'risk_level' => $riskLevel,
                    'strategy' => $this->getStrategyForRiskType($riskType),
                    'priority' => 'medium',
                    'timeline' => 'within_week',
                ];
            }
        }
        
        return ['strategies' => $strategies];
    }
    
    private function getStrategyForRiskType($riskType)
    {
        $strategies = [
            'drought' => [
                'Implement water conservation measures',
                'Schedule irrigation during optimal times',
                'Consider drought-resistant crop varieties',
                'Install soil moisture sensors',
            ],
            'flooding' => [
                'Improve field drainage systems',
                'Monitor weather forecasts closely',
                'Prepare emergency drainage plans',
                'Elevate critical infrastructure',
            ],
            'extreme_temperature' => [
                'Provide shade or cooling measures',
                'Adjust irrigation timing',
                'Use heat-tolerant varieties',
                'Monitor crop stress indicators',
            ],
            'pest_outbreak' => [
                'Implement integrated pest management',
                'Apply preventive treatments',
                'Monitor pest populations',
                'Use biological controls',
            ],
            'disease_outbreak' => [
                'Apply fungicides preventively',
                'Improve field ventilation',
                'Remove infected plant material',
                'Use disease-resistant varieties',
            ],
        ];
        
        return $strategies[$riskType] ?? [
            'Monitor conditions closely',
            'Implement appropriate mitigation measures',
            'Prepare contingency plans',
        ];
    }

    private function getMonitoringRecommendations($risks)
    {
        $recommendations = [];
        $maxRisk = 0;
        $criticalRisks = [];
        
        foreach ($risks as $riskType => $risk) {
            if (!is_array($risk)) {
                continue;
            }
            
            $riskLevel = $risk['risk_level'] ?? 'low';
            $riskScore = match($riskLevel) {
                'critical' => 90,
                'high' => 70,
                'medium' => 50,
                'low' => 30,
                default => 0,
            };
            
            $maxRisk = max($maxRisk, $riskScore);
            
            if ($riskLevel === 'critical' || $riskLevel === 'high') {
                $criticalRisks[] = $riskType;
            }
        }
        
        // Determine monitoring frequency
        $frequency = match(true) {
            $maxRisk >= 80 => 'daily',
            $maxRisk >= 60 => 'every_2_days',
            $maxRisk >= 40 => 'weekly',
            default => 'bi_weekly',
        };
        
        $recommendations[] = [
            'frequency' => $frequency,
            'activities' => [
                'Check weather conditions and forecasts',
                'Monitor soil moisture levels',
                'Inspect crops for stress symptoms',
                'Review field conditions',
            ],
            'critical_risks_to_monitor' => $criticalRisks,
            'data_points' => [
                'Temperature (min, max, average)',
                'Humidity levels',
                'Rainfall amounts',
                'Wind speed and direction',
                'Soil moisture',
                'Crop growth stage',
            ],
        ];
        
        // Add specific monitoring for critical risks
        foreach ($criticalRisks as $riskType) {
            $recommendations[] = [
                'risk_type' => $riskType,
                'specific_monitoring' => $this->getSpecificMonitoringForRisk($riskType),
            ];
        }
        
        return ['monitoring' => $recommendations];
    }
    
    private function getSpecificMonitoringForRisk($riskType)
    {
        $monitoring = [
            'drought' => [
                'Monitor soil moisture daily',
                'Check plant wilting indicators',
                'Track evapotranspiration rates',
                'Monitor water source levels',
            ],
            'flooding' => [
                'Monitor rainfall accumulation',
                'Check field drainage systems',
                'Monitor water levels in fields',
                'Track weather forecasts hourly',
            ],
            'extreme_temperature' => [
                'Monitor temperature hourly during extreme periods',
                'Check for heat/cold stress symptoms',
                'Monitor soil temperature',
                'Track temperature forecasts',
            ],
            'pest_outbreak' => [
                'Inspect plants for pest presence',
                'Use pheromone traps',
                'Monitor pest population levels',
                'Check for damage symptoms',
            ],
            'disease_outbreak' => [
                'Inspect for disease symptoms',
                'Monitor humidity levels',
                'Check for favorable disease conditions',
                'Track disease progression',
            ],
        ];
        
        return $monitoring[$riskType] ?? ['Monitor conditions regularly'];
    }

    private function getWeatherForecast($field, $days)
    {
        // Integrate with WeatherForecastService
        $forecastService = app(WeatherForecastService::class);
        $forecast = $forecastService->getFieldForecast($field, $days);
        
        if (isset($forecast['error'])) {
        return collect();
        }
        
        // Convert forecast to collection format
        $forecastData = [];
        if (isset($forecast['daily_forecasts'])) {
            foreach ($forecast['daily_forecasts'] as $day) {
                $forecastData[] = (object)[
                    'date' => $day['date'] ?? now()->toDateString(),
                    'temperature' => $day['temperature']['avg'] ?? $day['temp']['day'] ?? 25,
                    'humidity' => $day['humidity'] ?? 70,
                    'rainfall' => $day['rainfall'] ?? $day['rain'] ?? 0,
                    'wind_speed' => $day['wind_speed'] ?? $day['wind']['speed'] ?? 5,
                    'conditions' => $day['conditions'] ?? $day['weather'][0]['main'] ?? 'clear',
                ];
            }
        }
        
        return collect($forecastData);
    }

    private function getImmediateIrrigationAction($currentWeather, $soilMoistureLevel, $cropStage)
    {
        if (!$currentWeather) {
            return [
                'action' => 'monitor',
                'urgency' => 'low',
                'message' => 'Weather data not available - monitor soil moisture manually',
            ];
        }
        
        $temp = $currentWeather->temperature ?? 0;
        $humidity = $currentWeather->humidity ?? 0;
        $rainfall = $currentWeather->rainfall ?? 0;
        
        // Determine action based on soil moisture
        if ($soilMoistureLevel !== null) {
            if ($soilMoistureLevel < 30) {
                return [
                    'action' => 'irrigate_immediately',
                    'urgency' => 'critical',
                    'message' => 'Soil moisture critically low - irrigate immediately to prevent crop stress',
                    'recommended_amount_mm' => 15,
                    'recommended_time' => $this->getOptimalIrrigationTime($temp, $humidity),
                ];
            } elseif ($soilMoistureLevel < 50) {
                return [
                    'action' => 'irrigate_soon',
                    'urgency' => 'high',
                    'message' => 'Soil moisture is low - irrigate within 24 hours',
                    'recommended_amount_mm' => 10,
                    'recommended_time' => $this->getOptimalIrrigationTime($temp, $humidity),
                ];
            } elseif ($soilMoistureLevel > 80) {
                return [
                    'action' => 'avoid_irrigation',
                    'urgency' => 'low',
                    'message' => 'Soil moisture is high - avoid irrigation to prevent waterlogging',
                    'recommended_amount_mm' => 0,
                ];
            }
        }
        
        // Determine action based on weather conditions
        if ($rainfall > 10) {
            return [
                'action' => 'monitor',
                'urgency' => 'low',
                'message' => 'Recent rainfall detected - monitor soil moisture before irrigating',
                'recommended_amount_mm' => 0,
            ];
        }
        
        if ($temp > 32 && $humidity < 50) {
            return [
                'action' => 'irrigate',
                'urgency' => 'medium',
                'message' => 'Hot and dry conditions - irrigation recommended',
                'recommended_amount_mm' => 8,
                'recommended_time' => 'early_morning',
            ];
        }
        
        return [
            'action' => 'monitor',
            'urgency' => 'low',
            'message' => 'Weather conditions are normal - continue monitoring',
            'recommended_amount_mm' => 0,
        ];
    }

    private function generateWeeklyIrrigationSchedule($forecast, $soilMoistureLevel, $cropStage)
    {
        if ($forecast->isEmpty()) {
            return ['schedule' => [], 'message' => 'No forecast data available'];
        }
        
        $schedule = [];
        $baseWaterNeed = $this->getBaseWaterRequirement($cropStage);
        
        foreach ($forecast as $index => $day) {
            $date = $day->date ?? now()->addDays($index)->toDateString();
            $rainfall = $day->rainfall ?? 0;
            $temp = $day->temperature ?? 0;
            $humidity = $day->humidity ?? 0;
            
            // Calculate water need adjustment
            $waterNeed = $baseWaterNeed;
            
            // Adjust for temperature
            if ($temp > 30) {
                $waterNeed *= 1.3; // 30% more water in hot weather
            } elseif ($temp < 20) {
                $waterNeed *= 0.8; // 20% less water in cool weather
            }
            
            // Adjust for humidity
            if ($humidity < 50) {
                $waterNeed *= 1.2; // 20% more water in dry conditions
            } elseif ($humidity > 85) {
                $waterNeed *= 0.7; // 30% less water in humid conditions
            }
            
            // Account for rainfall
            $netWaterNeed = max(0, $waterNeed - ($rainfall * 0.8)); // 80% of rainfall is effective
            
            $shouldIrrigate = $netWaterNeed > 2; // Irrigate if need > 2mm
            
            $schedule[] = [
                'date' => $date,
                'day' => Carbon::parse($date)->format('l'),
                'forecasted_rainfall' => round($rainfall, 1),
                'forecasted_temp' => round($temp, 1),
                'forecasted_humidity' => round($humidity, 1),
                'estimated_water_need' => round($waterNeed, 1),
                'net_water_need' => round($netWaterNeed, 1),
                'irrigation_needed' => $shouldIrrigate,
                'recommended_amount_mm' => $shouldIrrigate ? round($netWaterNeed, 1) : 0,
                'recommended_time' => $this->getOptimalIrrigationTime($temp, $humidity),
            ];
        }
        
        return [
            'schedule' => $schedule,
            'total_irrigation_days' => collect($schedule)->where('irrigation_needed', true)->count(),
            'total_water_requirement_mm' => round(collect($schedule)->sum('net_water_need'), 1),
        ];
    }
    
    private function getBaseWaterRequirement($cropStage)
    {
        $requirements = [
            'seedling' => 3,      // mm per day
            'tillering' => 5,
            'flowering' => 7,
            'grain_filling' => 6,
            'ripening' => 4,
        ];
        
        return $requirements[$cropStage] ?? 5;
    }
    
    private function getOptimalIrrigationTime($temp, $humidity)
    {
        if ($temp > 30) {
            return 'early_morning'; // Before 8 AM
        } elseif ($temp < 20) {
            return 'mid_morning'; // 9-11 AM
        } else {
            return 'early_morning'; // Default
        }
    }

    private function calculateWaterRequirements($field, $planting, $cropStage)
    {
        $baseRequirement = $this->getBaseWaterRequirement($cropStage);
        
        // Adjust for field area
        $fieldArea = $field->area ?? 1; // hectares
        $dailyVolume = $baseRequirement * 10 * $fieldArea; // mm * 10 = m³ per hectare
        
        // Adjust for soil type
        $soilMultiplier = match($field->soil_type) {
            'clay' => 0.9,      // Clay retains more water
            'sandy' => 1.2,     // Sandy soil needs more water
            'loamy' => 1.0,     // Loamy is optimal
            default => 1.0,
        };
        
        $adjustedRequirement = $baseRequirement * $soilMultiplier;
        $adjustedVolume = $dailyVolume * $soilMultiplier;
        
        return [
            'daily_requirement_mm' => round($adjustedRequirement, 1),
            'daily_volume_m3' => round($adjustedVolume, 1),
            'weekly_requirement_mm' => round($adjustedRequirement * 7, 1),
            'weekly_volume_m3' => round($adjustedVolume * 7, 1),
            'field_area_hectares' => $fieldArea,
            'crop_stage' => $cropStage,
            'soil_type' => $field->soil_type,
            'adjustment_factors' => [
                'soil_type_multiplier' => $soilMultiplier,
            ],
        ];
    }

    private function getIrrigationEfficiencyTips($field, $currentWeather)
    {
        $tips = [];
        
        if (!$currentWeather) {
            return ['tips' => ['Monitor weather conditions for optimal irrigation timing']];
        }
        
        $temp = $currentWeather->temperature ?? 0;
        $humidity = $currentWeather->humidity ?? 0;
        $rainfall = $currentWeather->rainfall ?? 0;
        
        // Temperature-based tips
        if ($temp > 30) {
            $tips[] = 'High temperature detected - irrigate in early morning or late evening to reduce evaporation';
        } elseif ($temp < 20) {
            $tips[] = 'Low temperature - reduce irrigation frequency to prevent waterlogging';
        }
        
        // Humidity-based tips
        if ($humidity > 85) {
            $tips[] = 'High humidity - reduce irrigation to prevent disease development';
        } elseif ($humidity < 50) {
            $tips[] = 'Low humidity - increase irrigation frequency to maintain soil moisture';
        }
        
        // Rainfall-based tips
        if ($rainfall > 10) {
            $tips[] = 'Recent rainfall - skip irrigation and monitor soil moisture levels';
        }
        
        // General efficiency tips
        $tips[] = 'Use drip irrigation or furrow irrigation for better water efficiency';
        $tips[] = 'Irrigate based on soil moisture sensors rather than fixed schedules';
        $tips[] = 'Apply mulch to reduce evaporation and maintain soil moisture';
        $tips[] = 'Schedule irrigation during low wind conditions to reduce drift';
        
        // Field-specific tips
        if ($field->soil_type === 'clay') {
            $tips[] = 'Clay soil - use longer intervals between irrigation to prevent waterlogging';
        } elseif ($field->soil_type === 'sandy') {
            $tips[] = 'Sandy soil - use shorter, more frequent irrigation cycles';
        }
        
        return ['tips' => array_unique($tips)];
    }

    private function getIrrigationRiskWarnings($forecast, $soilMoistureLevel)
    {
        $warnings = [];
        
        if ($forecast->isEmpty()) {
        return ['warnings' => []];
        }
        
        // Check forecast for heavy rainfall
        $heavyRainDays = $forecast->filter(function($day) {
            return ($day->rainfall ?? 0) > 20; // mm
        })->count();
        
        if ($heavyRainDays > 0) {
            $warnings[] = [
                'type' => 'heavy_rainfall',
                'severity' => 'high',
                'message' => "Heavy rainfall expected in next {$heavyRainDays} day(s) - avoid irrigation to prevent waterlogging",
                'action' => 'Postpone irrigation until after rainfall',
            ];
        }
        
        // Check for high humidity + temperature (disease risk)
        $highRiskDays = $forecast->filter(function($day) {
            return ($day->humidity ?? 0) > 85 && ($day->temperature ?? 0) > 25;
        })->count();
        
        if ($highRiskDays > 2) {
            $warnings[] = [
                'type' => 'disease_risk',
                'severity' => 'medium',
                'message' => "High humidity and temperature conditions expected - avoid overhead irrigation",
                'action' => 'Use drip irrigation or reduce irrigation frequency',
            ];
        }
        
        // Check soil moisture level
        if ($soilMoistureLevel !== null) {
            if ($soilMoistureLevel > 80) {
                $warnings[] = [
                    'type' => 'over_irrigation',
                    'severity' => 'medium',
                    'message' => 'Soil moisture is already high - avoid additional irrigation',
                    'action' => 'Monitor soil moisture and delay irrigation',
                ];
            } elseif ($soilMoistureLevel < 30) {
                $warnings[] = [
                    'type' => 'under_irrigation',
                    'severity' => 'high',
                    'message' => 'Soil moisture is critically low - irrigation needed urgently',
                    'action' => 'Irrigate immediately to prevent crop stress',
                ];
            }
        }
        
        // Check for extreme temperatures
        $extremeTempDays = $forecast->filter(function($day) {
            $temp = $day->temperature ?? 0;
            return $temp > 35 || $temp < 15;
        })->count();
        
        if ($extremeTempDays > 0) {
            $warnings[] = [
                'type' => 'extreme_temperature',
                'severity' => 'medium',
                'message' => "Extreme temperatures expected - adjust irrigation timing",
                'action' => 'Irrigate during cooler parts of the day',
            ];
        }
        
        return ['warnings' => $warnings];
    }

    private function assessPestRisk($pest, $weatherData, $forecast)
    {
        $riskFactors = [];
        $riskScore = 0;
        
        // Common pest risk factors based on weather
        $pestConditions = [
            'aphids' => ['temp_range' => [20, 30], 'humidity_range' => [60, 80], 'risk_temp' => 25],
            'rice_blast' => ['temp_range' => [20, 28], 'humidity_range' => [85, 100], 'risk_temp' => 24],
            'brown_planthopper' => ['temp_range' => [25, 32], 'humidity_range' => [70, 90], 'risk_temp' => 28],
            'stem_borer' => ['temp_range' => [22, 30], 'humidity_range' => [70, 85], 'risk_temp' => 26],
        ];
        
        $conditions = $pestConditions[$pest] ?? ['temp_range' => [20, 30], 'humidity_range' => [60, 80], 'risk_temp' => 25];
        
        // Analyze historical weather data
        if ($weatherData->isNotEmpty()) {
            $avgTemp = $weatherData->avg('temperature');
            $avgHumidity = $weatherData->avg('humidity');
            $daysInOptimalRange = $weatherData->filter(function($log) use ($conditions) {
                $temp = $log->temperature ?? 0;
                $humidity = $log->humidity ?? 0;
                return $temp >= $conditions['temp_range'][0] && 
                       $temp <= $conditions['temp_range'][1] &&
                       $humidity >= $conditions['humidity_range'][0] && 
                       $humidity <= $conditions['humidity_range'][1];
            })->count();
            
            $optimalDaysPercent = ($daysInOptimalRange / $weatherData->count()) * 100;
            
            if ($optimalDaysPercent > 60) {
                $riskScore += 40;
                $riskFactors[] = "High number of days with optimal conditions for {$pest}";
            }
            
            if ($avgTemp >= $conditions['risk_temp'] - 2 && $avgTemp <= $conditions['risk_temp'] + 2) {
                $riskScore += 30;
                $riskFactors[] = "Temperature in optimal range for {$pest} development";
            }
            
            if ($avgHumidity >= $conditions['humidity_range'][0]) {
                $riskScore += 20;
                $riskFactors[] = "High humidity favorable for {$pest}";
            }
        }
        
        // Analyze forecast
        if ($forecast->isNotEmpty()) {
            $forecastOptimalDays = $forecast->filter(function($day) use ($conditions) {
                $temp = $day->temperature ?? 0;
                $humidity = $day->humidity ?? 0;
                return $temp >= $conditions['temp_range'][0] && 
                       $temp <= $conditions['temp_range'][1] &&
                       $humidity >= $conditions['humidity_range'][0] && 
                       $humidity <= $conditions['humidity_range'][1];
            })->count();
            
            if ($forecastOptimalDays > 3) {
                $riskScore += 10;
                $riskFactors[] = "Forecast shows favorable conditions for next few days";
            }
        }
        
        $riskLevel = $riskScore >= 70 ? 'high' : ($riskScore >= 40 ? 'medium' : 'low');
        
        return [
            'risk_level' => $riskLevel,
            'risk_score' => min(100, $riskScore),
            'factors' => $riskFactors,
            'pest_type' => $pest,
        ];
    }

    private function assessDiseaseRisk($disease, $weatherData, $forecast)
    {
        $riskFactors = [];
        $riskScore = 0;
        
        // Common disease risk factors based on weather
        $diseaseConditions = [
            'rice_blast' => ['temp_range' => [20, 28], 'humidity_min' => 85, 'rainfall_risk' => true],
            'brown_spot' => ['temp_range' => [25, 30], 'humidity_min' => 80, 'rainfall_risk' => true],
            'bacterial_blight' => ['temp_range' => [25, 35], 'humidity_min' => 70, 'rainfall_risk' => true],
            'sheath_blight' => ['temp_range' => [28, 32], 'humidity_min' => 90, 'rainfall_risk' => true],
        ];
        
        $conditions = $diseaseConditions[$disease] ?? ['temp_range' => [20, 30], 'humidity_min' => 80, 'rainfall_risk' => true];
        
        // Analyze historical weather data
        if ($weatherData->isNotEmpty()) {
            $avgTemp = $weatherData->avg('temperature');
            $avgHumidity = $weatherData->avg('humidity');
            $totalRainfall = $weatherData->sum('rainfall');
            $highHumidityDays = $weatherData->filter(function($log) use ($conditions) {
                return ($log->humidity ?? 0) >= $conditions['humidity_min'];
            })->count();
            
            $highHumidityPercent = ($highHumidityDays / $weatherData->count()) * 100;
            
            if ($highHumidityPercent > 50) {
                $riskScore += 40;
                $riskFactors[] = "High humidity conditions (>{$conditions['humidity_min']}%) for extended periods";
            }
            
            if ($avgTemp >= $conditions['temp_range'][0] && $avgTemp <= $conditions['temp_range'][1]) {
                $riskScore += 30;
                $riskFactors[] = "Temperature in optimal range for {$disease} development";
            }
            
            if ($conditions['rainfall_risk'] && $totalRainfall > 50) {
                $riskScore += 20;
                $riskFactors[] = "High rainfall creates favorable conditions for {$disease}";
            }
            
            // Check for prolonged wet conditions
            $consecutiveWetDays = $this->countConsecutiveWetDays($weatherData, $conditions['humidity_min']);
            if ($consecutiveWetDays >= 3) {
                $riskScore += 10;
                $riskFactors[] = "Prolonged wet conditions ({$consecutiveWetDays} days)";
            }
        }
        
        // Analyze forecast
        if ($forecast->isNotEmpty()) {
            $forecastHighHumidity = $forecast->filter(function($day) use ($conditions) {
                return ($day->humidity ?? 0) >= $conditions['humidity_min'];
            })->count();
            
            if ($forecastHighHumidity > 2) {
                $riskScore += 10;
                $riskFactors[] = "Forecast indicates high humidity conditions ahead";
            }
        }
        
        $riskLevel = $riskScore >= 70 ? 'high' : ($riskScore >= 40 ? 'medium' : 'low');
        
        return [
            'risk_level' => $riskLevel,
            'risk_score' => min(100, $riskScore),
            'factors' => $riskFactors,
            'disease_type' => $disease,
        ];
    }
    
    private function countConsecutiveWetDays($weatherData, $humidityThreshold)
    {
        $maxConsecutive = 0;
        $currentConsecutive = 0;
        
        foreach ($weatherData as $log) {
            if (($log->humidity ?? 0) >= $humidityThreshold) {
                $currentConsecutive++;
                $maxConsecutive = max($maxConsecutive, $currentConsecutive);
            } else {
                $currentConsecutive = 0;
            }
        }
        
        return $maxConsecutive;
    }

    private function calculateOverallPestDiseaseRisk($pestRisks, $diseaseRisks)
    {
        return 'medium';
    }

    private function getPestDiseasePreventionMeasures($pestRisks, $diseaseRisks)
    {
        $measures = [];
        
        // High risk pests
        foreach ($pestRisks as $pest => $risk) {
            if (($risk['risk_level'] ?? 'low') === 'high' || ($risk['risk_score'] ?? 0) >= 70) {
                $measures[] = [
                    'type' => 'pest_prevention',
                    'pest' => $pest,
                    'priority' => 'high',
                    'actions' => $this->getPestPreventionActions($pest),
                ];
            }
        }
        
        // High risk diseases
        foreach ($diseaseRisks as $disease => $risk) {
            if (($risk['risk_level'] ?? 'low') === 'high' || ($risk['risk_score'] ?? 0) >= 70) {
                $measures[] = [
                    'type' => 'disease_prevention',
                    'disease' => $disease,
                    'priority' => 'high',
                    'actions' => $this->getDiseasePreventionActions($disease),
                ];
            }
        }
        
        // General prevention measures
        if (!empty($measures)) {
            $measures[] = [
                'type' => 'general',
                'priority' => 'medium',
                'actions' => [
                    'Monitor fields daily for early signs of pests/diseases',
                    'Maintain proper field drainage to reduce humidity',
                    'Consider preventive fungicide/insecticide application',
                    'Remove and destroy infected plant material',
                ],
            ];
        }
        
        return ['measures' => $measures];
    }
    
    private function getPestPreventionActions($pest)
    {
        $actions = [
            'aphids' => [
                'Apply neem oil or insecticidal soap',
                'Introduce beneficial insects (ladybugs)',
                'Remove weeds that host aphids',
                'Use yellow sticky traps for monitoring',
            ],
            'rice_blast' => [
                'Apply fungicide preventively',
                'Ensure proper field drainage',
                'Avoid excessive nitrogen fertilization',
                'Use resistant rice varieties',
            ],
            'brown_planthopper' => [
                'Apply appropriate insecticides',
                'Maintain proper water levels',
                'Remove alternate hosts',
                'Use resistant varieties',
            ],
            'stem_borer' => [
                'Apply systemic insecticides',
                'Remove and destroy infested plants',
                'Use pheromone traps',
                'Practice crop rotation',
            ],
        ];
        
        return $actions[$pest] ?? [
            'Monitor field regularly',
            'Apply appropriate pest control measures',
            'Maintain field hygiene',
        ];
    }
    
    private function getDiseasePreventionActions($disease)
    {
        $actions = [
            'rice_blast' => [
                'Apply fungicide (tricyclazole, propiconazole)',
                'Ensure proper field drainage',
                'Avoid excessive nitrogen',
                'Use resistant varieties',
                'Practice crop rotation',
            ],
            'brown_spot' => [
                'Apply fungicide (mancozeb, propiconazole)',
                'Improve field drainage',
                'Remove infected plant debris',
                'Use certified disease-free seeds',
            ],
            'bacterial_blight' => [
                'Apply copper-based bactericides',
                'Avoid overhead irrigation',
                'Remove infected plants',
                'Use resistant varieties',
            ],
            'sheath_blight' => [
                'Apply fungicide (validamycin, propiconazole)',
                'Reduce plant density',
                'Improve air circulation',
                'Maintain proper water levels',
            ],
        ];
        
        return $actions[$disease] ?? [
            'Apply appropriate fungicide',
            'Maintain field hygiene',
            'Use disease-resistant varieties',
            'Practice proper crop management',
        ];
    }

    private function getPestDiseaseMonitoringSchedule($pestRisks, $diseaseRisks)
    {
        $schedule = [];
        $maxRisk = 0;
        
        // Determine monitoring frequency based on highest risk
        foreach (array_merge($pestRisks, $diseaseRisks) as $risk) {
            $riskScore = $risk['risk_score'] ?? 0;
            $maxRisk = max($maxRisk, $riskScore);
        }
        
        $frequency = $maxRisk >= 70 ? 'daily' : ($maxRisk >= 40 ? 'every_2_days' : 'weekly');
        
        $schedule[] = [
            'frequency' => $frequency,
            'activities' => [
                'Visual inspection of plants for symptoms',
                'Check for pest presence (especially on lower leaves)',
                'Monitor weather conditions',
                'Record observations in field log',
            ],
            'focus_areas' => $this->getFocusAreas($pestRisks, $diseaseRisks),
        ];
        
        return ['schedule' => $schedule];
    }
    
    private function getFocusAreas($pestRisks, $diseaseRisks)
    {
        $focus = [];
        
        foreach ($pestRisks as $pest => $risk) {
            if (($risk['risk_level'] ?? 'low') !== 'low') {
                $focus[] = "Monitor for {$pest} - " . ($risk['factors'][0] ?? 'High risk conditions detected');
            }
        }
        
        foreach ($diseaseRisks as $disease => $risk) {
            if (($risk['risk_level'] ?? 'low') !== 'low') {
                $focus[] = "Monitor for {$disease} - " . ($risk['factors'][0] ?? 'High risk conditions detected');
            }
        }
        
        return $focus;
    }

    private function getHistoricalClimateData($farm, $years)
    {
        $startYear = now()->year - $years;
        $fields = $farm->fields;
        $historicalData = [];
        
        foreach ($fields as $field) {
            $fieldData = [];
            for ($year = $startYear; $year <= now()->year; $year++) {
                $yearStart = Carbon::create($year, 1, 1);
                $yearEnd = Carbon::create($year, 12, 31);
                
                $yearLogs = WeatherLog::where('field_id', $field->id)
                    ->whereYear('recorded_at', $year)
                    ->get();
                
                if ($yearLogs->isNotEmpty()) {
                    $fieldData[$year] = [
                        'year' => $year,
                        'avg_temperature' => round($yearLogs->avg('temperature'), 2),
                        'min_temperature' => round($yearLogs->min('temperature'), 2),
                        'max_temperature' => round($yearLogs->max('temperature'), 2),
                        'total_rainfall' => round($yearLogs->sum('rainfall'), 2),
                        'avg_humidity' => round($yearLogs->avg('humidity'), 2),
                        'avg_wind_speed' => round($yearLogs->avg('wind_speed'), 2),
                        'data_points' => $yearLogs->count(),
                    ];
                }
            }
            
            if (!empty($fieldData)) {
                $historicalData[$field->id] = [
                    'field_id' => $field->id,
                    'field_name' => $field->name,
                    'yearly_data' => $fieldData,
                ];
            }
        }
        
        return ['data' => $historicalData];
    }

    private function projectClimateScenario($farm, $scenario, $years)
    {
        $historicalData = $this->getHistoricalClimateData($farm, min($years, 10));
        $projections = [];
        
        // Calculate trends from historical data
        $trends = $this->calculateClimateTrends($historicalData);
        
        // Apply scenario multipliers
        $scenarioMultipliers = [
            'conservative' => ['temp' => 1.01, 'rainfall' => 0.98], // +1% temp, -2% rain
            'moderate' => ['temp' => 1.02, 'rainfall' => 0.95],     // +2% temp, -5% rain
            'severe' => ['temp' => 1.05, 'rainfall' => 0.90],        // +5% temp, -10% rain
        ];
        
        $multiplier = $scenarioMultipliers[$scenario] ?? $scenarioMultipliers['moderate'];
        
        $baseYear = now()->year;
        for ($i = 1; $i <= $years; $i++) {
            $projectedYear = $baseYear + $i;
            $projections[$projectedYear] = [
                'year' => $projectedYear,
                'projected_avg_temperature' => round(($trends['avg_temp'] ?? 25) * pow($multiplier['temp'], $i), 2),
                'projected_total_rainfall' => round(($trends['avg_rainfall'] ?? 1000) * pow($multiplier['rainfall'], $i), 2),
                'projected_avg_humidity' => round(($trends['avg_humidity'] ?? 70) * 0.99, 2), // Slight decrease
                'scenario' => $scenario,
            ];
        }
        
        return ['projection' => $projections, 'scenario' => $scenario, 'trends' => $trends];
    }
    
    private function calculateClimateTrends($historicalData)
    {
        $allTemps = [];
        $allRainfall = [];
        $allHumidity = [];
        
        foreach ($historicalData['data'] ?? [] as $fieldData) {
            foreach ($fieldData['yearly_data'] ?? [] as $yearData) {
                if (isset($yearData['avg_temperature'])) {
                    $allTemps[] = $yearData['avg_temperature'];
                }
                if (isset($yearData['total_rainfall'])) {
                    $allRainfall[] = $yearData['total_rainfall'];
                }
                if (isset($yearData['avg_humidity'])) {
                    $allHumidity[] = $yearData['avg_humidity'];
                }
            }
        }
        
        return [
            'avg_temp' => !empty($allTemps) ? array_sum($allTemps) / count($allTemps) : 25,
            'avg_rainfall' => !empty($allRainfall) ? array_sum($allRainfall) / count($allRainfall) : 1000,
            'avg_humidity' => !empty($allHumidity) ? array_sum($allHumidity) / count($allHumidity) : 70,
        ];
    }

    private function analyzeHistoricalTrends($historicalData)
    {
        $trends = [];
        
        foreach ($historicalData['data'] ?? [] as $fieldData) {
            $fieldTrends = [
                'field_id' => $fieldData['field_id'],
                'field_name' => $fieldData['field_name'],
                'temperature_trend' => $this->calculateTrend($fieldData['yearly_data'] ?? [], 'avg_temperature'),
                'rainfall_trend' => $this->calculateTrend($fieldData['yearly_data'] ?? [], 'total_rainfall'),
                'humidity_trend' => $this->calculateTrend($fieldData['yearly_data'] ?? [], 'avg_humidity'),
            ];
            
            $trends[] = $fieldTrends;
        }
        
        return [
            'trends' => $trends,
            'overall_temperature_trend' => $this->aggregateTrends($trends, 'temperature_trend'),
            'overall_rainfall_trend' => $this->aggregateTrends($trends, 'rainfall_trend'),
            'overall_humidity_trend' => $this->aggregateTrends($trends, 'humidity_trend'),
        ];
    }
    
    private function calculateTrend($yearlyData, $metric)
    {
        if (count($yearlyData) < 2) {
            return ['direction' => 'insufficient_data', 'rate' => 0];
        }
        
        $values = [];
        $years = [];
        foreach ($yearlyData as $year => $data) {
            if (isset($data[$metric])) {
                $values[] = $data[$metric];
                $years[] = $year;
            }
        }
        
        if (count($values) < 2) {
            return ['direction' => 'insufficient_data', 'rate' => 0];
        }
        
        // Simple linear regression
        $n = count($values);
        $sumX = array_sum($years);
        $sumY = array_sum($values);
        $sumXY = 0;
        $sumX2 = 0;
        
        for ($i = 0; $i < $n; $i++) {
            $sumXY += $years[$i] * $values[$i];
            $sumX2 += $years[$i] * $years[$i];
        }
        
        $slope = ($n * $sumXY - $sumX * $sumY) / ($n * $sumX2 - $sumX * $sumX);
        
        $direction = $slope > 0.1 ? 'increasing' : ($slope < -0.1 ? 'decreasing' : 'stable');
        
        return [
            'direction' => $direction,
            'rate' => round($slope, 3),
            'change_per_year' => round($slope, 2),
        ];
    }
    
    private function aggregateTrends($trends, $trendKey)
    {
        $directions = [];
        $rates = [];
        
        foreach ($trends as $trend) {
            if (isset($trend[$trendKey])) {
                $directions[] = $trend[$trendKey]['direction'] ?? 'stable';
                $rates[] = $trend[$trendKey]['rate'] ?? 0;
            }
        }
        
        $avgRate = !empty($rates) ? array_sum($rates) / count($rates) : 0;
        $mostCommonDirection = !empty($directions) ? $this->getMostCommon($directions) : 'stable';
        
        return [
            'direction' => $mostCommonDirection,
            'average_rate' => round($avgRate, 3),
        ];
    }
    
    private function getMostCommon($array)
    {
        $counts = array_count_values($array);
        arsort($counts);
        return array_key_first($counts);
    }

    private function assessClimateImpacts($projections)
    {
        $impacts = [];
        
        foreach ($projections as $scenario => $projection) {
            $projectionData = $projection['projection'] ?? [];
            if (empty($projectionData)) {
                continue;
            }
            
            $avgTempChange = 0;
            $avgRainfallChange = 0;
            $years = count($projectionData);
            
            if ($years > 0) {
                $firstYear = reset($projectionData);
                $lastYear = end($projectionData);
                
                $avgTempChange = ($lastYear['projected_avg_temperature'] ?? 0) - ($firstYear['projected_avg_temperature'] ?? 0);
                $avgRainfallChange = ($lastYear['projected_total_rainfall'] ?? 0) - ($firstYear['projected_total_rainfall'] ?? 0);
            }
            
            $impacts[$scenario] = [
                'scenario' => $scenario,
                'temperature_impact' => [
                    'change' => round($avgTempChange, 2),
                    'severity' => abs($avgTempChange) > 2 ? 'high' : (abs($avgTempChange) > 1 ? 'medium' : 'low'),
                    'description' => $this->describeTemperatureImpact($avgTempChange),
                ],
                'rainfall_impact' => [
                    'change' => round($avgRainfallChange, 2),
                    'severity' => abs($avgRainfallChange) > 200 ? 'high' : (abs($avgRainfallChange) > 100 ? 'medium' : 'low'),
                    'description' => $this->describeRainfallImpact($avgRainfallChange),
                ],
                'crop_impact' => $this->assessCropImpact($avgTempChange, $avgRainfallChange),
                'water_management_impact' => $this->assessWaterManagementImpact($avgTempChange, $avgRainfallChange),
            ];
        }
        
        return ['impacts' => $impacts];
    }
    
    private function describeTemperatureImpact($change)
    {
        if ($change > 2) {
            return 'Significant temperature increase will stress crops and increase water requirements';
        } elseif ($change > 1) {
            return 'Moderate temperature increase may affect crop growth and development';
        } elseif ($change < -1) {
            return 'Temperature decrease may extend growing seasons but could affect crop maturity';
        } else {
            return 'Minimal temperature change expected';
        }
    }
    
    private function describeRainfallImpact($change)
    {
        if ($change < -200) {
            return 'Significant rainfall decrease will require increased irrigation and water management';
        } elseif ($change < -100) {
            return 'Moderate rainfall decrease may affect crop water availability';
        } elseif ($change > 200) {
            return 'Significant rainfall increase may cause flooding and drainage issues';
        } elseif ($change > 100) {
            return 'Moderate rainfall increase may require improved drainage systems';
        } else {
            return 'Minimal rainfall change expected';
        }
    }
    
    private function assessCropImpact($tempChange, $rainfallChange)
    {
        $impacts = [];
        
        if ($tempChange > 1) {
            $impacts[] = 'Increased heat stress on crops';
            $impacts[] = 'Higher water requirements';
            $impacts[] = 'Potential yield reduction in heat-sensitive varieties';
        }
        
        if ($rainfallChange < -100) {
            $impacts[] = 'Increased irrigation needs';
            $impacts[] = 'Potential water scarcity issues';
            $impacts[] = 'Risk of drought stress';
        }
        
        if ($rainfallChange > 100) {
            $impacts[] = 'Increased flooding risk';
            $impacts[] = 'Disease pressure from high humidity';
            $impacts[] = 'Need for improved drainage';
        }
        
        return empty($impacts) ? ['Minimal crop impact expected'] : $impacts;
    }
    
    private function assessWaterManagementImpact($tempChange, $rainfallChange)
    {
        $impacts = [];
        
        if ($tempChange > 1 || $rainfallChange < -100) {
            $impacts[] = 'Increased irrigation water requirements';
            $impacts[] = 'Need for water storage systems';
            $impacts[] = 'Improved irrigation efficiency critical';
        }
        
        if ($rainfallChange > 100) {
            $impacts[] = 'Enhanced drainage systems needed';
            $impacts[] = 'Water harvesting opportunities';
            $impacts[] = 'Flood management infrastructure';
        }
        
        return empty($impacts) ? ['Current water management practices should be adequate'] : $impacts;
    }

    private function getClimateAdaptationStrategies($projections)
    {
        $strategies = [];
        
        foreach ($projections as $scenario => $projection) {
            $projectionData = $projection['projection'] ?? [];
            if (empty($projectionData)) {
                continue;
            }
            
            $lastYear = end($projectionData);
            $tempChange = ($lastYear['projected_avg_temperature'] ?? 25) - 25; // Assuming 25°C baseline
            $rainfallChange = ($lastYear['projected_total_rainfall'] ?? 1000) - 1000; // Assuming 1000mm baseline
            
            $scenarioStrategies = [];
            
            // Temperature adaptation strategies
            if ($tempChange > 2) {
                $scenarioStrategies[] = [
                    'category' => 'crop_selection',
                    'strategy' => 'Switch to heat-tolerant rice varieties',
                    'priority' => 'high',
                ];
                $scenarioStrategies[] = [
                    'category' => 'irrigation',
                    'strategy' => 'Implement efficient irrigation systems (drip, sprinkler)',
                    'priority' => 'high',
                ];
                $scenarioStrategies[] = [
                    'category' => 'field_management',
                    'strategy' => 'Adjust planting dates to avoid peak heat periods',
                    'priority' => 'medium',
                ];
            }
            
            // Rainfall adaptation strategies
            if ($rainfallChange < -200) {
                $scenarioStrategies[] = [
                    'category' => 'water_management',
                    'strategy' => 'Install water storage and harvesting systems',
                    'priority' => 'high',
                ];
                $scenarioStrategies[] = [
                    'category' => 'irrigation',
                    'strategy' => 'Upgrade to water-efficient irrigation methods',
                    'priority' => 'high',
                ];
                $scenarioStrategies[] = [
                    'category' => 'crop_selection',
                    'strategy' => 'Consider drought-resistant crop varieties',
                    'priority' => 'medium',
                ];
            } elseif ($rainfallChange > 200) {
                $scenarioStrategies[] = [
                    'category' => 'drainage',
                    'strategy' => 'Improve field drainage infrastructure',
                    'priority' => 'high',
                ];
                $scenarioStrategies[] = [
                    'category' => 'field_management',
                    'strategy' => 'Implement raised bed systems',
                    'priority' => 'medium',
                ];
            }
            
            // General adaptation strategies
            $scenarioStrategies[] = [
                'category' => 'monitoring',
                'strategy' => 'Implement weather monitoring and early warning systems',
                'priority' => 'high',
            ];
            $scenarioStrategies[] = [
                'category' => 'diversification',
                'strategy' => 'Diversify crop portfolio to reduce climate risk',
                'priority' => 'medium',
            ];
            
            $strategies[$scenario] = [
                'scenario' => $scenario,
                'strategies' => $scenarioStrategies,
                'implementation_timeline' => 'short_to_medium_term',
            ];
        }
        
        return ['strategies' => $strategies];
    }

    private function getResilienceRecommendations($farm, $projections)
    {
        $recommendations = [];
        
        // Infrastructure resilience
        $recommendations[] = [
            'category' => 'infrastructure',
            'priority' => 'high',
            'recommendations' => [
                'Improve irrigation infrastructure for water security',
                'Enhance drainage systems to handle extreme rainfall',
                'Install weather monitoring stations',
                'Build water storage capacity',
            ],
        ];
        
        // Crop management resilience
        $recommendations[] = [
            'category' => 'crop_management',
            'priority' => 'high',
            'recommendations' => [
                'Diversify crop varieties to spread climate risk',
                'Adopt climate-smart agricultural practices',
                'Implement crop rotation systems',
                'Use cover crops to improve soil resilience',
            ],
        ];
        
        // Financial resilience
        $recommendations[] = [
            'category' => 'financial',
            'priority' => 'medium',
            'recommendations' => [
                'Consider crop insurance for climate risks',
                'Build financial reserves for climate adaptation',
                'Diversify income sources',
            ],
        ];
        
        // Knowledge and capacity building
        $recommendations[] = [
            'category' => 'capacity_building',
            'priority' => 'medium',
            'recommendations' => [
                'Train staff on climate adaptation techniques',
                'Stay informed about climate trends and forecasts',
                'Participate in climate adaptation programs',
                'Share knowledge with other farmers',
            ],
        ];
        
        // Technology adoption
        $recommendations[] = [
            'category' => 'technology',
            'priority' => 'high',
            'recommendations' => [
                'Adopt precision agriculture technologies',
                'Use soil moisture sensors',
                'Implement automated irrigation systems',
                'Utilize weather forecasting tools',
            ],
        ];
        
        return [
            'recommendations' => $recommendations,
            'overall_resilience_score' => $this->calculateResilienceScore($farm, $projections),
            'priority_actions' => $this->getPriorityActions($recommendations),
        ];
    }
    
    private function calculateResilienceScore($farm, $projections)
    {
        // Simplified resilience score calculation
        $score = 50; // Base score
        
        // Add points for existing infrastructure
        if ($farm->fields->count() > 0) {
            $score += 10;
        }
        
        // Adjust based on projection severity
        $maxSeverity = 0;
        foreach ($projections as $projection) {
            $impacts = $projection['impact_assessment'] ?? [];
            foreach ($impacts as $impact) {
                $severity = $impact['severity'] ?? 'low';
                $severityValue = match($severity) {
                    'high' => 3,
                    'medium' => 2,
                    'low' => 1,
                    default => 0,
                };
                $maxSeverity = max($maxSeverity, $severityValue);
            }
        }
        
        // Higher severity = lower resilience score
        $score -= ($maxSeverity * 5);
        
        return max(0, min(100, $score));
    }
    
    private function getPriorityActions($recommendations)
    {
        $priorityActions = [];
        
        foreach ($recommendations as $category) {
            if (($category['priority'] ?? 'low') === 'high') {
                $priorityActions = array_merge($priorityActions, $category['recommendations'] ?? []);
            }
        }
        
        return array_slice($priorityActions, 0, 5); // Top 5 priority actions
    }

    private function identifyDataGaps($weatherLogs, $startDate)
    {
        if ($weatherLogs->isEmpty()) {
            return ['gaps' => [['start' => $startDate->toDateString(), 'end' => now()->toDateString(), 'duration_days' => $startDate->diffInDays(now())]]];
        }
        
        $gaps = [];
        $sortedLogs = $weatherLogs->sortBy('recorded_at');
        $previousDate = $startDate;
        
        foreach ($sortedLogs as $log) {
            $logDate = Carbon::parse($log->recorded_at);
            $daysDiff = $previousDate->diffInDays($logDate);
            
            // Consider gap if more than 24 hours between readings
            if ($daysDiff > 1) {
                $gaps[] = [
                    'start' => $previousDate->toDateString(),
                    'end' => $logDate->toDateString(),
                    'duration_days' => $daysDiff,
                    'gap_type' => $daysDiff > 7 ? 'major' : ($daysDiff > 3 ? 'moderate' : 'minor'),
                ];
            }
            
            $previousDate = $logDate;
        }
        
        // Check for gap at the end
        $lastLogDate = Carbon::parse($sortedLogs->last()->recorded_at);
        $endGap = $lastLogDate->diffInDays(now());
        if ($endGap > 1) {
            $gaps[] = [
                'start' => $lastLogDate->toDateString(),
                'end' => now()->toDateString(),
                'duration_days' => $endGap,
                'gap_type' => $endGap > 7 ? 'major' : ($endGap > 3 ? 'moderate' : 'minor'),
            ];
        }
        
        return [
            'gaps' => $gaps,
            'total_gaps' => count($gaps),
            'total_gap_days' => array_sum(array_column($gaps, 'duration_days')),
        ];
    }

    private function calculateDataQualityScore($weatherLogs)
    {
        if ($weatherLogs->isEmpty()) {
            return 0; // No data = no quality
        }

        $totalLogs = $weatherLogs->count();
        $completeLogs = 0;
        $recentLogs = 0;
        $now = now();
        
        foreach ($weatherLogs as $log) {
            // Check if log has essential fields
            $hasTemp = isset($log->temperature) && $log->temperature !== null;
            $hasHumidity = isset($log->humidity) && $log->humidity !== null;
            $hasConditions = isset($log->conditions) && !empty($log->conditions);
            
            if ($hasTemp && $hasHumidity && $hasConditions) {
                $completeLogs++;
            }
            
            // Check if log is recent (within last 7 days)
            if ($log->recorded_at && Carbon::parse($log->recorded_at)->diffInDays($now) <= 7) {
                $recentLogs++;
            }
        }
        
        // Calculate quality score based on:
        // - Completeness (40%): percentage of logs with all required fields
        // - Recency (30%): percentage of logs from last 7 days
        // - Consistency (30%): based on data spread (simplified)
        $completenessScore = ($completeLogs / $totalLogs) * 100;
        $recencyScore = min(100, ($recentLogs / max(1, $totalLogs)) * 100);
        $consistencyScore = $this->calculateConsistencyScore($weatherLogs);
        
        $qualityScore = ($completenessScore * 0.4) + ($recencyScore * 0.3) + ($consistencyScore * 0.3);
        
        return min(100, round($qualityScore));
    }

    private function calculateConsistencyScore($weatherLogs)
    {
        if ($weatherLogs->count() < 2) {
            return 50; // Can't determine consistency with less than 2 logs
        }
        
        $temps = $weatherLogs->pluck('temperature')->filter();
        $humidities = $weatherLogs->pluck('humidity')->filter();
        
        if ($temps->isEmpty() || $humidities->isEmpty()) {
            return 50;
        }
        
        // Calculate coefficient of variation (lower is better)
        $tempAvg = $temps->avg();
        $tempStd = $this->calculateStdDev($temps->toArray());
        $tempCV = $tempAvg > 0 ? ($tempStd / $tempAvg) * 100 : 100;
        
        $humidityAvg = $humidities->avg();
        $humidityStd = $this->calculateStdDev($humidities->toArray());
        $humidityCV = $humidityAvg > 0 ? ($humidityStd / $humidityAvg) * 100 : 100;
        
        // Lower CV = higher consistency score
        $consistencyScore = 100 - min(50, ($tempCV + $humidityCV) / 2);
        
        return max(0, $consistencyScore);
    }

    private function calculateStdDev($values)
    {
        $count = count($values);
        if ($count < 2) return 0;
        
        $mean = array_sum($values) / $count;
        $variance = 0;
        
        foreach ($values as $value) {
            $variance += pow($value - $mean, 2);
        }
        
        return sqrt($variance / $count);
    }

    private function calculateReliabilityMetrics($weatherLogs)
    {
        if ($weatherLogs->isEmpty()) {
            return [
                'reliability' => 'none',
                'reliability_score' => 0,
                'data_availability' => 0,
                'consistency_score' => 0,
                'message' => 'No weather data available',
            ];
        }
        
        $totalLogs = $weatherLogs->count();
        $now = now();
        
        // Calculate data availability (recent data)
        $recentLogs = $weatherLogs->filter(function($log) use ($now) {
            return $log->recorded_at && Carbon::parse($log->recorded_at)->diffInDays($now) <= 7;
        })->count();
        $availabilityScore = ($recentLogs / max(1, $totalLogs)) * 100;
        
        // Calculate completeness (logs with all essential fields)
        $completeLogs = $weatherLogs->filter(function($log) {
            return isset($log->temperature) && 
                   isset($log->humidity) && 
                   isset($log->conditions) &&
                   $log->temperature !== null &&
                   $log->humidity !== null &&
                   !empty($log->conditions);
        })->count();
        $completenessScore = ($completeLogs / $totalLogs) * 100;
        
        // Calculate consistency (low variance in readings)
        $temps = $weatherLogs->pluck('temperature')->filter();
        $humidities = $weatherLogs->pluck('humidity')->filter();
        
        $consistencyScore = 50; // Default
        if ($temps->count() >= 2 && $humidities->count() >= 2) {
            $tempStd = $this->calculateStdDev($temps->toArray());
            $humidityStd = $this->calculateStdDev($humidities->toArray());
            
            $tempAvg = $temps->avg();
            $humidityAvg = $humidities->avg();
            
            // Coefficient of variation (lower is better)
            $tempCV = $tempAvg > 0 ? ($tempStd / $tempAvg) * 100 : 100;
            $humidityCV = $humidityAvg > 0 ? ($humidityStd / $humidityAvg) * 100 : 100;
            
            // Lower CV = higher consistency
            $avgCV = ($tempCV + $humidityCV) / 2;
            $consistencyScore = max(0, 100 - min(50, $avgCV));
        }
        
        // Calculate timeliness (regular intervals)
        $timelinessScore = 50; // Default
        if ($totalLogs >= 2) {
            $sortedLogs = $weatherLogs->sortBy('recorded_at');
            $intervals = [];
            $previous = null;
            
            foreach ($sortedLogs as $log) {
                if ($previous) {
                    $interval = Carbon::parse($log->recorded_at)->diffInHours(Carbon::parse($previous->recorded_at));
                    $intervals[] = $interval;
                }
                $previous = $log->recorded_at;
            }
            
            if (!empty($intervals)) {
                $avgInterval = array_sum($intervals) / count($intervals);
                $expectedInterval = 24; // Expected hourly readings
                
                // Score based on how close to expected interval
                $intervalDeviation = abs($avgInterval - $expectedInterval) / $expectedInterval;
                $timelinessScore = max(0, 100 - ($intervalDeviation * 100));
            }
        }
        
        // Overall reliability score (weighted average)
        $reliabilityScore = (
            ($availabilityScore * 0.3) +
            ($completenessScore * 0.3) +
            ($consistencyScore * 0.25) +
            ($timelinessScore * 0.15)
        );
        
        $reliability = match(true) {
            $reliabilityScore >= 80 => 'high',
            $reliabilityScore >= 60 => 'moderate',
            $reliabilityScore >= 40 => 'low',
            default => 'poor',
        };
        
        return [
            'reliability' => $reliability,
            'reliability_score' => round($reliabilityScore, 1),
            'data_availability' => round($availabilityScore, 1),
            'completeness_score' => round($completenessScore, 1),
            'consistency_score' => round($consistencyScore, 1),
            'timeliness_score' => round($timelinessScore, 1),
            'total_readings' => $totalLogs,
            'recent_readings' => $recentLogs,
            'complete_readings' => $completeLogs,
        ];
    }
}