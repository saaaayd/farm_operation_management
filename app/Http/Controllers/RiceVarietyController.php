<?php

namespace App\Http\Controllers;

use App\Models\RiceVariety;
use Illuminate\Http\Request;

class RiceVarietyController extends Controller
{
    /**
     * Get all rice varieties
     */
    public function index(Request $request)
    {
        try {
            $query = RiceVariety::active();

            // Filter by season if provided
            if ($request->has('season')) {
                $query->bySeason($request->season);
            }

            // Filter by grain type if provided
            if ($request->has('grain_type')) {
                $query->where('grain_type', $request->grain_type);
            }

            // Filter by resistance level if provided
            if ($request->has('resistance_level')) {
                $query->where('resistance_level', $request->resistance_level);
            }

            // Search by name if provided
            if ($request->has('search')) {
                $query->where(function ($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('variety_code', 'like', '%' . $request->search . '%')
                      ->orWhere('description', 'like', '%' . $request->search . '%');
                });
            }

            $varieties = $query->orderBy('name')->get();

            return response()->json([
                'varieties' => $varieties,
                'total' => $varieties->count(),
                'filters' => [
                    'season' => $request->season,
                    'grain_type' => $request->grain_type,
                    'resistance_level' => $request->resistance_level,
                    'search' => $request->search,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch rice varieties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific rice variety
     */
    public function show($id)
    {
        try {
            $variety = RiceVariety::findOrFail($id);

            return response()->json([
                'variety' => $variety,
                'estimated_harvest_date' => $variety->getEstimatedHarvestDate(now()),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Rice variety not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Get rice varieties suitable for current season
     */
    public function getCurrentSeason()
    {
        try {
            $varieties = RiceVariety::getCurrentSeasonVarieties();
            $currentSeason = (now()->month >= 5 && now()->month <= 10) ? 'wet' : 'dry';

            return response()->json([
                'varieties' => $varieties,
                'current_season' => $currentSeason,
                'season_info' => [
                    'wet_season' => 'May - October',
                    'dry_season' => 'November - April',
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch current season varieties',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}