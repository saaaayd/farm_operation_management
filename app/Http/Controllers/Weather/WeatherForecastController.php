<?php

namespace App\Http\Controllers\Weather;

use App\Http\Controllers\Controller;
use App\Services\WeatherService;
use App\Services\WeatherForecastService;
use App\Models\Field;
use App\Models\Farm;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WeatherForecastController extends Controller
{
    protected $weatherService;
    protected $forecastService;

    public function __construct(WeatherService $weatherService, WeatherForecastService $forecastService)
    {
        $this->weatherService = $weatherService;
        $this->forecastService = $forecastService;
    }

    /**
     * Get weather forecast for a field
     */
    public function getFieldForecast(Request $request, $fieldId)
    {
        $request->validate([
            'days' => 'integer|min:1|max:14',
            'forecast_type' => 'in:basic,detailed,agricultural',
        ]);

        $field = Field::findOrFail($fieldId);
        $this->authorize('view', $field);

        $days = $request->days ?? 7;
        $forecastType = $request->forecast_type ?? 'basic';

        $forecast = $this->forecastService->getFieldForecast($field, $days, $forecastType);

        return response()->json([
            'field' => $field,
            'forecast' => $forecast,
            'forecast_type' => $forecastType,
            'days' => $days,
        ]);
    }

    /**
     * Get extended weather forecast
     */
    public function getExtendedForecast(Request $request, $fieldId)
    {
        $request->validate([
            'period' => 'in:weekly,monthly,seasonal',
        ]);

        $field = Field::findOrFail($fieldId);
        $this->authorize('view', $field);

        $period = $request->period ?? 'weekly';
        $forecast = $this->forecastService->getExtendedForecast($field, $period);

        return response()->json([
            'field' => $field,
            'extended_forecast' => $forecast,
            'period' => $period,
        ]);
    }

    /**
     * Get agricultural weather forecast
     */
    public function getAgriculturalForecast(Request $request, $fieldId)
    {
        $field = Field::findOrFail($fieldId);
        $this->authorize('view', $field);

        $forecast = $this->forecastService->getAgriculturalForecast($field);

        return response()->json([
            'field' => $field,
            'agricultural_forecast' => $forecast,
        ]);
    }

    /**
     * Get weather alerts and warnings
     */
    public function getWeatherAlerts(Request $request, $fieldId)
    {
        $field = Field::findOrFail($fieldId);
        $this->authorize('view', $field);

        $alerts = $this->forecastService->getWeatherAlerts($field);
        $currentAlerts = $this->weatherService->getWeatherAlerts($field);

        return response()->json([
            'field' => $field,
            'current_alerts' => $currentAlerts,
            'forecast_alerts' => $alerts,
        ]);
    }

    /**
     * Get farm-wide weather forecast
     */
    public function getFarmForecast(Request $request, $farmId)
    {
        $request->validate([
            'days' => 'integer|min:1|max:14',
        ]);

        $farm = Farm::findOrFail($farmId);
        $this->authorize('view', $farm);

        $days = $request->days ?? 7;
        $forecast = $this->forecastService->getFarmForecast($farm, $days);

        return response()->json([
            'farm' => $farm,
            'farm_forecast' => $forecast,
            'days' => $days,
        ]);
    }

    /**
     * Get weather-based activity recommendations
     */
    public function getActivityRecommendations(Request $request, $fieldId)
    {
        $field = Field::findOrFail($fieldId);
        $this->authorize('view', $field);

        $recommendations = $this->forecastService->getActivityRecommendations($field);

        return response()->json([
            'field' => $field,
            'activity_recommendations' => $recommendations,
        ]);
    }

    /**
     * Get optimal timing for farm activities
     */
    public function getOptimalTiming(Request $request, $fieldId)
    {
        $request->validate([
            'activity_type' => 'required|in:planting,harvesting,spraying,fertilizing,irrigation',
            'planning_days' => 'integer|min:1|max:30',
        ]);

        $field = Field::findOrFail($fieldId);
        $this->authorize('view', $field);

        $activityType = $request->activity_type;
        $planningDays = $request->planning_days ?? 14;

        $timing = $this->forecastService->getOptimalTiming($field, $activityType, $planningDays);

        return response()->json([
            'field' => $field,
            'activity_type' => $activityType,
            'optimal_timing' => $timing,
            'planning_days' => $planningDays,
        ]);
    }

    /**
     * Get weather-based crop protection recommendations
     */
    public function getCropProtectionForecast(Request $request, $fieldId)
    {
        $field = Field::findOrFail($fieldId);
        $this->authorize('view', $field);

        $protection = $this->forecastService->getCropProtectionForecast($field);

        return response()->json([
            'field' => $field,
            'crop_protection_forecast' => $protection,
        ]);
    }

    /**
     * Get seasonal weather outlook
     */
    public function getSeasonalOutlook(Request $request, $farmId)
    {
        $request->validate([
            'season' => 'in:spring,summer,autumn,winter,dry,wet',
            'months_ahead' => 'integer|min:1|max:6',
        ]);

        $farm = Farm::findOrFail($farmId);
        $this->authorize('view', $farm);

        $season = $request->season;
        $monthsAhead = $request->months_ahead ?? 3;

        $outlook = $this->forecastService->getSeasonalOutlook($farm, $season, $monthsAhead);

        return response()->json([
            'farm' => $farm,
            'seasonal_outlook' => $outlook,
            'season' => $season,
            'months_ahead' => $monthsAhead,
        ]);
    }

    /**
     * Subscribe to weather alerts
     */
    public function subscribeToAlerts(Request $request, $fieldId)
    {
        $request->validate([
            'alert_types' => 'required|array',
            'alert_types.*' => 'in:temperature,humidity,wind,precipitation,severe_weather',
            'notification_methods' => 'required|array',
            'notification_methods.*' => 'in:email,sms,push',
            'thresholds' => 'nullable|array',
        ]);

        $field = Field::findOrFail($fieldId);
        $this->authorize('update', $field);

        $subscription = $this->forecastService->subscribeToAlerts(
            $field,
            $request->alert_types,
            $request->notification_methods,
            $request->thresholds ?? []
        );

        return response()->json([
            'message' => 'Successfully subscribed to weather alerts',
            'subscription' => $subscription,
        ]);
    }

    /**
     * Get weather forecast accuracy metrics
     */
    public function getForecastAccuracy(Request $request, $fieldId)
    {
        $request->validate([
            'period_days' => 'integer|min:7|max:90',
        ]);

        $field = Field::findOrFail($fieldId);
        $this->authorize('view', $field);

        $periodDays = $request->period_days ?? 30;
        $accuracy = $this->forecastService->getForecastAccuracy($field, $periodDays);

        return response()->json([
            'field' => $field,
            'forecast_accuracy' => $accuracy,
            'period_days' => $periodDays,
        ]);
    }
}