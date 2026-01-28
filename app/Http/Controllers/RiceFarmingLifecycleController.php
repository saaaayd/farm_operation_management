<?php

namespace App\Http\Controllers;

use App\Models\Field;
use App\Models\Planting;
use App\Models\PlantingStage;
use App\Models\RiceVariety;
use App\Models\RiceGrowthStage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class RiceFarmingLifecycleController extends Controller
{
    /**
     * Create a new rice planting with lifecycle management
     */
    public function createRicePlanting(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'field_id' => 'required|exists:fields,id',
            'rice_variety_id' => 'required|exists:rice_varieties,id',
            'planting_date' => 'required|date',
            'planting_method' => 'required|string|in:direct_seeding,transplanting,broadcasting,drilling',
            'seed_rate' => 'required|numeric|min:0',
            'area_planted' => 'required|numeric|min:0',
            'season' => 'nullable|string|in:wet,dry', // Add validation
            'notes' => 'nullable|string|max:1000',
            'weather_conditions' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $user = auth()->user();
            $field = Field::findOrFail($request->field_id);

            // Check if user owns this field
            if ($field->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $riceVariety = RiceVariety::findOrFail($request->rice_variety_id);

            // Calculate expected harvest date based on variety maturity days
            $plantingDate = \Carbon\Carbon::parse($request->planting_date);
            $expectedHarvestDate = $plantingDate->copy()->addDays($riceVariety->maturity_days);

            // Create the planting
            $planting = Planting::create([
                'field_id' => $request->field_id,
                'rice_variety_id' => $request->rice_variety_id,
                'crop_type' => 'rice',
                'planting_date' => $plantingDate,
                'expected_harvest_date' => $expectedHarvestDate,
                'status' => Planting::STATUS_PLANTED,
                'planting_method' => $request->planting_method,
                'seed_rate' => $request->seed_rate,
                'area_planted' => $request->area_planted,
                'season' => $request->season ?? 'wet', // Add season
                'notes' => $request->notes,
                // 'weather_conditions' => $request->weather_conditions, // Removed invalid column
            ]);

            // Initialize planting stages based on rice growth stages
            $planting->initializePlantingStages();

            // Start the appropriate stage based on planting method
            $plantingMethod = $planting->planting_method;

            if ($plantingMethod === 'transplanting') {
                // For transplanting, start Stage 2
                $stages = $planting->plantingStages()
                    ->join('rice_growth_stages', 'planting_stages.rice_growth_stage_id', '=', 'rice_growth_stages.id')
                    ->orderBy('rice_growth_stages.order_sequence')
                    ->select('planting_stages.*')
                    ->get();

                if ($stages->count() >= 2) {
                    $stages[0]->markAsCompleted('Completed in nursery (Transplanting)');
                    $stages[1]->markAsStarted();
                } elseif ($stages->count() > 0) {
                    $stages[0]->markAsStarted();
                }
            } else {
                // Start the first stage (usually seedling/germination)
                $firstStage = $planting->plantingStages()
                    ->join('rice_growth_stages', 'planting_stages.rice_growth_stage_id', '=', 'rice_growth_stages.id')
                    ->orderBy('rice_growth_stages.order_sequence')
                    ->first();

                if ($firstStage) {
                    $firstStage->markAsStarted();
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Rice planting created successfully',
                'planting' => $planting->load(['riceVariety', 'field', 'plantingStages.riceGrowthStage']),
                'lifecycle_info' => [
                    'expected_harvest_date' => $expectedHarvestDate->format('Y-m-d'),
                    'estimated_yield' => $planting->getEstimatedYield(),
                    'total_stages' => $planting->plantingStages()->count(),
                    'current_stage' => $planting->getCurrentStage()?->riceGrowthStage->name,
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Failed to create rice planting',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rice planting lifecycle details
     */
    public function getPlantingLifecycle($plantingId)
    {
        try {
            $planting = Planting::with([
                'riceVariety',
                'field',
                'plantingStages.riceGrowthStage',
                'harvests'
            ])->findOrFail($plantingId);

            // Check if user owns this planting
            if ($planting->field->user_id !== auth()->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Auto-initialize stages if none exist (for existing plantings without lifecycle)
            if ($planting->plantingStages->isEmpty()) {
                $planting->initializePlantingStages();

                // Start the appropriate stage based on planting method
                if ($planting->status !== Planting::STATUS_PLANNED) {
                    $plantingMethod = $planting->planting_method;

                    if ($plantingMethod === 'transplanting') {
                        // For transplanting, we assume the seedling stage (Stage 1) is done in nursery
                        $stages = $planting->plantingStages()
                            ->join('rice_growth_stages', 'planting_stages.rice_growth_stage_id', '=', 'rice_growth_stages.id')
                            ->orderBy('rice_growth_stages.order_sequence')
                            ->select('planting_stages.*')
                            ->get();

                        if ($stages->count() >= 2) {
                            $stages[0]->markAsCompleted('Completed in nursery (Transplanting)');
                            $stages[1]->markAsStarted();
                        } elseif ($stages->count() > 0) {
                            $stages[0]->markAsStarted();
                        }
                    } else {
                        // Direct seeding / Broadcasting starts at Stage 1
                        $firstStage = $planting->plantingStages()
                            ->join('rice_growth_stages', 'planting_stages.rice_growth_stage_id', '=', 'rice_growth_stages.id')
                            ->orderBy('rice_growth_stages.order_sequence')
                            ->select('planting_stages.*')
                            ->first();

                        if ($firstStage) {
                            $firstStage->markAsStarted();
                        }
                    }
                }

                // Reload the planting with the newly created stages
                $planting->load('plantingStages.riceGrowthStage');
            }

            $currentStage = $planting->getCurrentStage();
            $nextStage = $planting->getNextPendingStage();
            $progress = $planting->getProgressPercentage();

            // Get stage timeline
            $stageTimeline = $planting->plantingStages()
                ->with('riceGrowthStage')
                ->join('rice_growth_stages', 'planting_stages.rice_growth_stage_id', '=', 'rice_growth_stages.id')
                ->orderBy('rice_growth_stages.order_sequence')
                ->select('planting_stages.*')
                ->get();

            // Calculate days since planting and days to harvest
            $daysSincePlanting = $planting->getDaysSincePlanting();
            $daysToHarvest = $planting->daysUntilHarvest();

            return response()->json([
                'planting' => $planting,
                'lifecycle_status' => [
                    'current_stage' => $currentStage?->riceGrowthStage,
                    'next_stage' => $nextStage?->riceGrowthStage,
                    'progress_percentage' => $progress,
                    'days_since_planting' => $daysSincePlanting,
                    'days_to_harvest' => $daysToHarvest,
                    'is_overdue' => $planting->isOverdue(),
                    'is_critical_stage' => $planting->isInCriticalStage(),
                ],
                'stage_timeline' => $stageTimeline,
                'yield_info' => [
                    'estimated_yield' => $planting->getEstimatedYield(),
                    'actual_yield' => $planting->total_yield,
                    'yield_unit' => 'kg',
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get planting lifecycle',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Advance to next growth stage
     */
    public function advanceToNextStage(Request $request, $plantingId)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'nullable|string|max:1000',
            'completion_percentage' => 'nullable|numeric|between:0,100',
            'stage_data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            $planting = Planting::with(['plantingStages.riceGrowthStage'])->findOrFail($plantingId);

            // Check if user owns this planting
            if ($planting->field->user_id !== auth()->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $currentStage = $planting->getCurrentStage();
            if (!$currentStage) {
                return response()->json(['message' => 'No active stage found'], 400);
            }

            // Complete current stage
            $currentStage->update([
                'status' => 'completed',
                'completed_at' => now(),
                'completion_percentage' => $request->completion_percentage ?? 100,
                'notes' => $request->notes,
                'stage_data' => $request->stage_data,
            ]);

            // Start next stage
            $nextStage = $planting->getNextPendingStage();
            if ($nextStage) {
                $nextStage->markAsStarted();

                // Update planting status based on stage
                $this->updatePlantingStatusByStage($planting, $nextStage->riceGrowthStage->stage_code);
            } else {
                // All stages completed
                $planting->update(['status' => Planting::STATUS_READY]);
            }

            DB::commit();

            return response()->json([
                'message' => 'Successfully advanced to next stage',
                'completed_stage' => $currentStage->riceGrowthStage->name,
                'current_stage' => $nextStage?->riceGrowthStage->name,
                'planting_status' => $planting->status,
                'progress_percentage' => $planting->getProgressPercentage(),
            ]);

        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'message' => 'Failed to advance to next stage',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mark stage as delayed
     */
    public function markStageDelayed(Request $request, $stageId)
    {
        $validator = Validator::make($request->all(), [
            'notes' => 'required|string|max:1000',
            'expected_completion_date' => 'nullable|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $stage = PlantingStage::with(['planting.field', 'riceGrowthStage'])->findOrFail($stageId);

            // Check if user owns this planting
            if ($stage->planting->field->user_id !== auth()->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $stage->update([
                'status' => 'delayed',
                'notes' => $request->notes,
                'stage_data' => array_merge($stage->stage_data ?? [], [
                    'delay_reason' => $request->notes,
                    'expected_completion_date' => $request->expected_completion_date,
                    'delayed_at' => now()->toISOString(),
                ])
            ]);

            return response()->json([
                'message' => 'Stage marked as delayed',
                'stage' => $stage,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to mark stage as delayed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rice farming recommendations for current stage
     */
    public function getStageRecommendations($plantingId)
    {
        try {
            $planting = Planting::with(['riceVariety', 'field', 'plantingStages.riceGrowthStage'])->findOrFail($plantingId);

            // Check if user owns this planting
            if ($planting->field->user_id !== auth()->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $currentStage = $planting->getCurrentStage();
            if (!$currentStage) {
                return response()->json(['message' => 'No active stage found'], 400);
            }

            $stageInfo = $currentStage->riceGrowthStage;
            $recommendations = [];

            // General stage recommendations
            if ($stageInfo->key_activities) {
                $recommendations['activities'] = $stageInfo->key_activities;
            }

            if ($stageInfo->nutrient_requirements) {
                $recommendations['nutrients'] = $stageInfo->nutrient_requirements;
            }

            if ($stageInfo->water_requirements) {
                $recommendations['water_management'] = $stageInfo->water_requirements;
            }

            if ($stageInfo->weather_requirements) {
                $recommendations['weather_conditions'] = $stageInfo->weather_requirements;
            }

            if ($stageInfo->common_problems) {
                $recommendations['watch_for'] = $stageInfo->common_problems;
            }

            // Field-specific recommendations
            $fieldRecommendations = $planting->field->getPreparationRecommendations();
            if (!empty($fieldRecommendations)) {
                $recommendations['field_specific'] = $fieldRecommendations;
            }

            // Weather-based recommendations
            $weatherService = app(\App\Services\WeatherService::class);
            $weatherRecommendations = $weatherService->getRiceFarmingRecommendations($planting->field);
            if (!empty($weatherRecommendations)) {
                $recommendations['weather_based'] = $weatherRecommendations;
            }

            // Stage-specific timing recommendations
            $daysSinceStageStart = $currentStage->started_at ?
                $currentStage->started_at->diffInDays(now()) : 0;

            $expectedDuration = $stageInfo->typical_duration_days;
            $isOverdue = $currentStage->isOverdue();

            $recommendations['timing'] = [
                'days_in_current_stage' => $daysSinceStageStart,
                'expected_stage_duration' => $expectedDuration,
                'is_overdue' => $isOverdue,
                'days_remaining' => $currentStage->getDaysRemaining(),
            ];

            if ($isOverdue) {
                $recommendations['urgent_actions'] = [
                    'Stage is overdue. Consider consulting with agricultural extension services.',
                    'Review environmental conditions that may be causing delays.',
                    'Consider adjusting management practices for this stage.',
                ];
            }

            return response()->json([
                'planting' => $planting,
                'current_stage' => $stageInfo,
                'recommendations' => $recommendations,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get stage recommendations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get rice farming lifecycle overview for all plantings
     */
    public function getLifecycleOverview(Request $request)
    {
        try {
            $user = auth()->user();

            $plantings = Planting::with([
                'riceVariety',
                'field',
                'plantingStages.riceGrowthStage'
            ])
                ->whereHas('field', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                })
                ->where('crop_type', 'rice')
                ->whereIn('status', ['planted', 'growing'])
                ->get();

            $overview = [
                'total_plantings' => $plantings->count(),
                'by_stage' => [],
                'by_status' => [],
                'critical_plantings' => [],
                'overdue_stages' => [],
                'upcoming_activities' => [],
            ];

            foreach ($plantings as $planting) {
                $currentStage = $planting->getCurrentStage();

                if ($currentStage) {
                    $stageName = $currentStage->riceGrowthStage->name;
                    $overview['by_stage'][$stageName] = ($overview['by_stage'][$stageName] ?? 0) + 1;

                    // Check for critical stages
                    if ($planting->isInCriticalStage()) {
                        $overview['critical_plantings'][] = [
                            'planting_id' => $planting->id,
                            'field_name' => $planting->field->name,
                            'variety' => $planting->riceVariety->name,
                            'stage' => $stageName,
                            'days_since_planting' => $planting->getDaysSincePlanting(),
                        ];
                    }

                    // Check for overdue stages
                    if ($currentStage->isOverdue()) {
                        $overview['overdue_stages'][] = [
                            'planting_id' => $planting->id,
                            'field_name' => $planting->field->name,
                            'stage' => $stageName,
                            'days_overdue' => abs($currentStage->getDaysRemaining()),
                        ];
                    }

                    // Get upcoming activities
                    if ($currentStage->riceGrowthStage->key_activities) {
                        foreach ($currentStage->riceGrowthStage->key_activities as $activity) {
                            $overview['upcoming_activities'][] = [
                                'planting_id' => $planting->id,
                                'field_name' => $planting->field->name,
                                'stage' => $stageName,
                                'activity' => $activity,
                                'priority' => $planting->isInCriticalStage() ? 'high' : 'normal',
                            ];
                        }
                    }
                }

                $overview['by_status'][$planting->status] = ($overview['by_status'][$planting->status] ?? 0) + 1;
            }

            return response()->json([
                'overview' => $overview,
                'plantings' => $plantings,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get lifecycle overview',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update planting status based on growth stage
     */
    private function updatePlantingStatusByStage(Planting $planting, string $stageCode)
    {
        switch ($stageCode) {
            case 'seedling':
            case 'tillering':
            case 'stem_elongation':
            case 'booting':
                $planting->update(['status' => Planting::STATUS_GROWING]);
                break;
            case 'flowering':
            case 'grain_filling':
                $planting->update(['status' => Planting::STATUS_GROWING]);
                break;
            case 'ripening':
            case 'maturity':
                $planting->update(['status' => Planting::STATUS_READY]);
                break;
        }
    }
}