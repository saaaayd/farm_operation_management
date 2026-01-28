<?php

namespace App\Http\Controllers;

use App\Models\RiceGrowthStage;
use Illuminate\Http\Request;

class RiceGrowthStageController extends Controller
{
    /**
     * Get all rice growth stages
     */
    public function index()
    {
        try {
            $stages = RiceGrowthStage::active()->ordered()->get();

            return response()->json([
                'stages' => $stages,
                'total' => $stages->count(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch rice growth stages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rice growth stages in order
     */
    public function getOrdered()
    {
        try {
            $stages = RiceGrowthStage::getAllStagesOrdered();

            return response()->json([
                'stages' => $stages,
                'total' => $stages->count(),
                'stage_sequence' => $stages->pluck('name', 'order_sequence'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch ordered growth stages',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific rice growth stage
     */
    public function show($id)
    {
        try {
            $stage = RiceGrowthStage::findOrFail($id);

            return response()->json([
                'stage' => $stage,
                'next_stage' => $stage->getNextStage(),
                'previous_stage' => $stage->getPreviousStage(),
                'is_first' => $stage->isFirstStage(),
                'is_last' => $stage->isLastStage(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Rice growth stage not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }
}