<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Efficiency Benchmarks (Yield per Currency Unit)
    |--------------------------------------------------------------------------
    |
    | These values represent the target yield (in kg) per 1 unit of currency (e.g., Peso)
    | spent on a specific resource.
    | Formula: Efficiency Score = (Actual Yield / Expense) / Benchmark * 10
    |
    */
    'efficiency_benchmarks' => [
        'water' => 0.1,      // Target: 0.1 kg yield per 1 peso spent on water/irrigation
        'fertilizer' => 0.15, // Target: 0.15 kg yield per 1 peso spent on fertilizer
        'labor' => 0.2,       // Target: 0.2 kg yield per 1 peso spent on labor
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Efficiency Targets
    |--------------------------------------------------------------------------
    |
    | Target maximum costs to be considered efficient.
    |
    */
    'cost_targets' => [
        'cost_per_kg' => 3.0,       // Target: Less than 3.0 currency units per kg
        'cost_per_hectare' => 2000, // Target: Less than 2000 currency units per hectare
    ],

    /*
    |--------------------------------------------------------------------------
    | Scoring Weights
    |--------------------------------------------------------------------------
    |
    | Weights used when calculating specific aggregate scores.
    |
    */
    'scoring_weights' => [
        'cost_efficiency' => [
            'per_kg' => 0.6,      // 60% weight on per-kg cost
            'per_hectare' => 0.4, // 40% weight on per-hectare cost
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Weather Risk Thresholds
    |--------------------------------------------------------------------------
    |
    | Score thresholds for determining weather suitability risk levels.
    |
    */
    'weather_score_thresholds' => [
        'low_risk' => 80,      // Scores >= 80 are Low Risk
        'moderate_risk' => 60, // Scores >= 60 are Moderate Risk
        'high_risk' => 40,     // Scores >= 40 are High Risk
        // Scores < 40 are Very High Risk
    ],
];
