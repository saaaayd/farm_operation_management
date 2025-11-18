<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ActivityLogController extends Controller
{
    /**
     * Get activity logs (FR-B.4)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->get('per_page', 50);
        $query = ActivityLog::with('user');

        // Filter by user
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter by model type
        if ($request->has('model_type')) {
            $query->where('model_type', $request->model_type);
        }

        // Filter by date range
        if ($request->has('start_date')) {
            $query->where('created_at', '>=', $request->start_date);
        }
        if ($request->has('end_date')) {
            $query->where('created_at', '<=', $request->end_date);
        }

        // Search in description
        if ($request->has('search')) {
            $search = $request->search;
            $query->where('description', 'like', "%{$search}%");
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($logs);
    }

    /**
     * Get activity log statistics
     */
    public function getStats(): JsonResponse
    {
        $stats = [
            'total_logs' => ActivityLog::count(),
            'today_logs' => ActivityLog::whereDate('created_at', today())->count(),
            'this_week_logs' => ActivityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month_logs' => ActivityLog::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
            'by_action' => ActivityLog::selectRaw('action, COUNT(*) as count')
                ->groupBy('action')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
            'by_user' => ActivityLog::selectRaw('user_id, COUNT(*) as count')
                ->whereNotNull('user_id')
                ->groupBy('user_id')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->with('user:id,name,email')
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Get audit trail for a specific model (FR-B.4)
     */
    public function getAuditTrail(Request $request, string $modelType, int $modelId): JsonResponse
    {
        $logs = ActivityLog::where('model_type', $modelType)
            ->where('model_id', $modelId)
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'model_type' => $modelType,
            'model_id' => $modelId,
            'logs' => $logs,
        ]);
    }
}

