<?php

namespace App\Http\Controllers\Weather;

use App\Http\Controllers\Controller;
use App\Models\WeatherLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class WeatherHistoryController extends Controller
{
    /**
     * Display weather history for a specific field
     */
    public function fieldHistory(Request $request, $fieldId): JsonResponse
    {
        $user = $request->user();
        
        $query = WeatherLog::where('field_id', $fieldId)
            ->whereHas('field', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        
        // Apply date filters
        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        
        $weatherLogs = $query->with(['field'])
            ->orderBy('date', 'desc')
            ->get();
        
        return response()->json([
            'field_id' => $fieldId,
            'weather_history' => $weatherLogs
        ]);
    }

    /**
     * Get weather statistics for a field
     */
    public function fieldStatistics(Request $request, $fieldId): JsonResponse
    {
        $user = $request->user();
        
        $query = WeatherLog::where('field_id', $fieldId)
            ->whereHas('field', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        
        // Apply date filters
        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        
        $logs = $query->get();
        
        if ($logs->isEmpty()) {
            return response()->json([
                'field_id' => $fieldId,
                'statistics' => null,
                'message' => 'No weather data available for the specified period'
            ]);
        }
        
        $statistics = [
            'total_records' => $logs->count(),
            'date_range' => [
                'from' => $logs->min('date'),
                'to' => $logs->max('date')
            ],
            'temperature' => [
                'average' => round($logs->avg('temperature'), 2),
                'min' => $logs->min('temperature'),
                'max' => $logs->max('temperature')
            ],
            'humidity' => [
                'average' => round($logs->avg('humidity'), 2),
                'min' => $logs->min('humidity'),
                'max' => $logs->max('humidity')
            ],
            'rainfall' => [
                'total' => round($logs->sum('rainfall'), 2),
                'average' => round($logs->avg('rainfall'), 2),
                'max_daily' => $logs->max('rainfall')
            ],
            'wind_speed' => [
                'average' => round($logs->avg('wind_speed'), 2),
                'min' => $logs->min('wind_speed'),
                'max' => $logs->max('wind_speed')
            ],
            'weather_conditions' => $logs->groupBy('weather_condition')
                ->map(function ($group) {
                    return $group->count();
                })
        ];
        
        return response()->json([
            'field_id' => $fieldId,
            'statistics' => $statistics
        ]);
    }

    /**
     * Get weather trends for a field
     */
    public function fieldTrends(Request $request, $fieldId): JsonResponse
    {
        $user = $request->user();
        
        $query = WeatherLog::where('field_id', $fieldId)
            ->whereHas('field', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        
        // Apply date filters
        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        
        $groupBy = $request->get('group_by', 'daily'); // daily, weekly, monthly
        
        switch ($groupBy) {
            case 'weekly':
                $logs = $query->selectRaw('
                    YEAR(date) as year,
                    WEEK(date) as week,
                    AVG(temperature) as avg_temperature,
                    AVG(humidity) as avg_humidity,
                    SUM(rainfall) as total_rainfall,
                    AVG(wind_speed) as avg_wind_speed,
                    COUNT(*) as record_count
                ')
                ->groupBy('year', 'week')
                ->orderBy('year', 'asc')
                ->orderBy('week', 'asc')
                ->get();
                break;
                
            case 'monthly':
                $logs = $query->selectRaw('
                    YEAR(date) as year,
                    MONTH(date) as month,
                    AVG(temperature) as avg_temperature,
                    AVG(humidity) as avg_humidity,
                    SUM(rainfall) as total_rainfall,
                    AVG(wind_speed) as avg_wind_speed,
                    COUNT(*) as record_count
                ')
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();
                break;
                
            default: // daily
                $logs = $query->selectRaw('
                    date,
                    AVG(temperature) as avg_temperature,
                    AVG(humidity) as avg_humidity,
                    SUM(rainfall) as total_rainfall,
                    AVG(wind_speed) as avg_wind_speed,
                    COUNT(*) as record_count
                ')
                ->groupBy('date')
                ->orderBy('date', 'asc')
                ->get();
        }
        
        return response()->json([
            'field_id' => $fieldId,
            'group_by' => $groupBy,
            'trends' => $logs
        ]);
    }

    /**
     * Get weather alerts for a field
     */
    public function fieldAlerts(Request $request, $fieldId): JsonResponse
    {
        $user = $request->user();
        
        $query = WeatherLog::where('field_id', $fieldId)
            ->whereHas('field', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        
        // Apply date filters
        if ($request->has('date_from')) {
            $query->where('date', '>=', $request->date_from);
        }
        
        if ($request->has('date_to')) {
            $query->where('date', '<=', $request->date_to);
        }
        
        $alerts = [];
        
        // High temperature alerts (> 35°C)
        $highTempLogs = $query->where('temperature', '>', 35)->get();
        foreach ($highTempLogs as $log) {
            $alerts[] = [
                'type' => 'high_temperature',
                'severity' => 'warning',
                'date' => $log->date,
                'value' => $log->temperature,
                'message' => "High temperature: {$log->temperature}°C"
            ];
        }
        
        // Low humidity alerts (< 30%)
        $lowHumidityLogs = $query->where('humidity', '<', 30)->get();
        foreach ($lowHumidityLogs as $log) {
            $alerts[] = [
                'type' => 'low_humidity',
                'severity' => 'warning',
                'date' => $log->date,
                'value' => $log->humidity,
                'message' => "Low humidity: {$log->humidity}%"
            ];
        }
        
        // High rainfall alerts (> 50mm)
        $highRainfallLogs = $query->where('rainfall', '>', 50)->get();
        foreach ($highRainfallLogs as $log) {
            $alerts[] = [
                'type' => 'high_rainfall',
                'severity' => 'warning',
                'date' => $log->date,
                'value' => $log->rainfall,
                'message' => "High rainfall: {$log->rainfall}mm"
            ];
        }
        
        // High wind speed alerts (> 25 km/h)
        $highWindLogs = $query->where('wind_speed', '>', 25)->get();
        foreach ($highWindLogs as $log) {
            $alerts[] = [
                'type' => 'high_wind',
                'severity' => 'warning',
                'date' => $log->date,
                'value' => $log->wind_speed,
                'message' => "High wind speed: {$log->wind_speed} km/h"
            ];
        }
        
        // Sort alerts by date
        usort($alerts, function ($a, $b) {
            return strtotime($b['date']) - strtotime($a['date']);
        });
        
        return response()->json([
            'field_id' => $fieldId,
            'alerts' => $alerts,
            'alert_count' => count($alerts)
        ]);
    }

    /**
     * Get weather comparison between fields
     */
    public function compareFields(Request $request): JsonResponse
    {
        $user = $request->user();
        
        $validator = Validator::make($request->all(), [
            'field_ids' => 'required|array|min:2|max:5',
            'field_ids.*' => 'integer|exists:fields,id',
            'date_from' => 'required|date',
            'date_to' => 'required|date|after_or_equal:date_from',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $comparison = [];
        
        foreach ($request->field_ids as $fieldId) {
            $query = WeatherLog::where('field_id', $fieldId)
                ->whereBetween('date', [$request->date_from, $request->date_to])
                ->whereHas('field', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            
            $logs = $query->get();
            
            if ($logs->isNotEmpty()) {
                $comparison[] = [
                    'field_id' => $fieldId,
                    'field_name' => $logs->first()->field->name ?? "Field {$fieldId}",
                    'statistics' => [
                        'avg_temperature' => round($logs->avg('temperature'), 2),
                        'avg_humidity' => round($logs->avg('humidity'), 2),
                        'total_rainfall' => round($logs->sum('rainfall'), 2),
                        'avg_wind_speed' => round($logs->avg('wind_speed'), 2),
                        'record_count' => $logs->count()
                    ]
                ];
            }
        }
        
        return response()->json([
            'date_range' => [
                'from' => $request->date_from,
                'to' => $request->date_to
            ],
            'comparison' => $comparison
        ]);
    }
}