<?php

namespace App\Http\Controllers\Farmer;

use App\Models\User;
use App\Models\Farm;
use App\Models\Field;
use App\Models\RiceVariety;
use App\Services\OpenWeatherAPIService;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RiceFarmProfileController extends Controller
{
    /**
     * Create a comprehensive rice farm profile
     */
    public function createRiceFarmProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Basic Information
            'farm_name' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'total_area' => 'required|numeric|min:0',
            'rice_area' => 'required|numeric|min:0|lte:total_area',
            'farming_experience' => 'nullable|integer|min:0',
            'farm_description' => 'nullable|string|max:1000',
            
            // Soil Information
            'soil_type' => 'required|string|in:clay,loam,sandy,silt,clay_loam,sandy_loam,silty_clay,silty_loam',
            'soil_ph' => 'nullable|numeric|between:3.0,10.0',
            'organic_matter_content' => 'nullable|numeric|between:0,20',
            'nitrogen_level' => 'nullable|numeric|min:0',
            'phosphorus_level' => 'nullable|numeric|min:0',
            'potassium_level' => 'nullable|numeric|min:0',
            'elevation' => 'nullable|numeric|min:0',
            
            // Water Management
            'water_source' => 'required|string|in:irrigation_canal,river,well,shallow_well,pond,rainfall,spring',
            'irrigation_type' => 'required|string|in:flood,furrow,sprinkler,drip,manual,none',
            'water_access' => 'required|string|in:excellent,good,moderate,poor,very_poor',
            'drainage_quality' => 'required|string|in:excellent,good,moderate,poor',
            
            // Rice Varieties and Practices
            'preferred_varieties' => 'nullable|array',
            'preferred_varieties.*' => 'string',
            'planting_method' => 'nullable|string|in:direct_seeding,transplanting,broadcasting,drilling',
            'previous_yield' => 'nullable|numeric|min:0',
            'target_yield' => 'nullable|numeric|min:0',
            'cropping_seasons' => 'nullable|string|in:1,2,3',
            'farming_challenges' => 'nullable|array',
            'farming_challenges.*' => 'string',
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

            // Create or update farm
            $farm = Farm::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'name' => $request->farm_name,
                    'location' => $request->location,
                    'description' => $request->farm_description,
                ]
            );

            // Geocode the location to get coordinates for weather data
            $coordinates = $this->geocodeLocation($request->location);
            
            // Prepare location data with coordinates if available
            $locationData = ['address' => $request->location];
            if ($coordinates) {
                $locationData['lat'] = $coordinates['latitude'];
                $locationData['lon'] = $coordinates['longitude'];
            }
            
            // Create main rice field with comprehensive data
            $field = Field::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'farm_id' => $farm->id,
                    'name' => 'Main Rice Field'
                ],
                [
                    'location' => $locationData,
                    'field_coordinates' => $coordinates ? [
                        'lat' => $coordinates['latitude'],
                        'lon' => $coordinates['longitude']
                    ] : null,
                    'soil_type' => $request->soil_type,
                    'soil_ph' => $request->soil_ph,
                    'organic_matter_content' => $request->organic_matter_content,
                    'nitrogen_level' => $request->nitrogen_level,
                    'phosphorus_level' => $request->phosphorus_level,
                    'potassium_level' => $request->potassium_level,
                    'size' => $request->rice_area,
                    'water_access' => $request->water_access,
                    'water_source' => $request->water_source,
                    'irrigation_type' => $request->irrigation_type,
                    'drainage_quality' => $request->drainage_quality,
                    'elevation' => $request->elevation,
                    'slope' => null, // Can be added later
                    'previous_crop' => 'rice',
                    'field_preparation_status' => 'needs_assessment',
                    'notes' => $this->generateFieldNotes($request),
                ]
            );

            // Update user profile with farming experience
            $user->update([
                'address' => [
                    'farm_location' => $request->location,
                    'total_area' => $request->total_area,
                    'rice_area' => $request->rice_area,
                    'farming_experience' => $request->farming_experience,
                    'preferred_varieties' => $request->preferred_varieties,
                    'planting_method' => $request->planting_method,
                    'previous_yield' => $request->previous_yield,
                    'target_yield' => $request->target_yield,
                    'cropping_seasons' => $request->cropping_seasons,
                    'farming_challenges' => $request->farming_challenges,
                ]
            ]);

            DB::commit();

            // Get field recommendations
            $recommendations = $field->getPreparationRecommendations();
            $suitabilityScore = $field->getProductivityScore();
            $recommendedVarieties = $field->getRecommendedRiceVarieties();

            return response()->json([
                'message' => 'Rice farm profile created successfully',
                'farmProfile' => [
                    'farm' => $farm,
                    'field' => $field,
                    'user_profile' => $user->address,
                ],
                'fields' => [$field],
                'analysis' => [
                    'suitability_score' => $suitabilityScore,
                    'soil_fertility' => $field->getSoilFertilityStatus(),
                    'recommendations' => $recommendations,
                    'recommended_varieties' => $recommendedVarieties,
                    'is_suitable_for_rice' => $field->isSuitableForRice(),
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'message' => 'Failed to create rice farm profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Generate field notes based on input data
     */
    private function generateFieldNotes(Request $request)
    {
        $notes = [];
        
        if ($request->farming_experience) {
            $notes[] = "Farmer has {$request->farming_experience} years of rice farming experience.";
        }
        
        if ($request->previous_yield) {
            $notes[] = "Previous average yield: {$request->previous_yield} tons/ha.";
        }
        
        if ($request->target_yield) {
            $notes[] = "Target yield: {$request->target_yield} tons/ha.";
        }
        
        if ($request->preferred_varieties) {
            $varieties = implode(', ', $request->preferred_varieties);
            $notes[] = "Preferred rice varieties: {$varieties}.";
        }
        
        if ($request->planting_method) {
            $notes[] = "Preferred planting method: " . str_replace('_', ' ', $request->planting_method) . ".";
        }
        
        if ($request->cropping_seasons) {
            $notes[] = "Plans to grow {$request->cropping_seasons} season(s) per year.";
        }
        
        if ($request->farming_challenges) {
            $challenges = implode(', ', array_map(function($challenge) {
                return str_replace('_', ' ', $challenge);
            }, $request->farming_challenges));
            $notes[] = "Main challenges: {$challenges}.";
        }
        
        return implode(' ', $notes);
    }

    /**
     * Get rice varieties suitable for current season
     */
    public function getCurrentSeasonVarieties()
    {
        try {
            $varieties = RiceVariety::getCurrentSeasonVarieties();
            
            return response()->json([
                'varieties' => $varieties,
                'current_season' => (now()->month >= 5 && now()->month <= 10) ? 'wet' : 'dry'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch rice varieties',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recommended rice varieties for a specific field
     */
    public function getRecommendedVarieties($fieldId)
    {
        try {
            $field = Field::findOrFail($fieldId);
            
            // Check if user owns this field
            if ($field->user_id !== auth()->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            $recommendedVarieties = $field->getRecommendedRiceVarieties();
            
            return response()->json([
                'field' => $field,
                'recommended_varieties' => $recommendedVarieties,
                'field_analysis' => [
                    'suitability_score' => $field->getProductivityScore(),
                    'soil_fertility' => $field->getSoilFertilityStatus(),
                    'is_suitable_for_rice' => $field->isSuitableForRice(),
                    'recommendations' => $field->getPreparationRecommendations(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get field recommendations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get field analysis and recommendations
     */
    public function getFieldAnalysis($fieldId)
    {
        try {
            $field = Field::with(['user', 'farm', 'plantings.riceVariety'])->findOrFail($fieldId);
            
            // Check if user owns this field
            if ($field->user_id !== auth()->id()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            
            return response()->json([
                'field' => $field,
                'analysis' => [
                    'productivity_score' => $field->getProductivityScore(),
                    'soil_fertility' => $field->getSoilFertilityStatus(),
                    'is_suitable_for_rice' => $field->isSuitableForRice(),
                    'preparation_recommendations' => $field->getPreparationRecommendations(),
                    'recommended_varieties' => $field->getRecommendedRiceVarieties(),
                    'current_planting' => $field->getCurrentRicePlanting(),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get field analysis',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get the current farmer's profile
     */
    public function getProfile()
    {
        try {
            $user = auth()->user();

            if (!$user->isFarmer()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            $farm = Farm::where('user_id', $user->id)->first();
            $fields = Field::where('user_id', $user->id)->with(['farm', 'plantings.riceVariety'])->get();

            $profileData = [
                'user' => $user,
                'farm' => $farm,
                'fields' => $fields,
                'user_profile' => $user->address,
            ];

            // If farm exists, add analysis for the first field
            if ($farm && $fields->count() > 0) {
                $primaryField = $fields->first();
                $profileData['analysis'] = [
                    'suitability_score' => $primaryField->getProductivityScore(),
                    'soil_fertility' => $primaryField->getSoilFertilityStatus(),
                    'recommendations' => $primaryField->getPreparationRecommendations(),
                    'recommended_varieties' => $primaryField->getRecommendedRiceVarieties(),
                    'is_suitable_for_rice' => $primaryField->isSuitableForRice(),
                ];
            }

            return response()->json([
                'message' => 'Profile retrieved successfully',
                'farmProfile' => $profileData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the farmer's profile
     */
    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            // Basic Information
            'farm_name' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'total_area' => 'nullable|numeric|min:0',
            'rice_area' => 'nullable|numeric|min:0',
            'farming_experience' => 'nullable|integer|min:0',
            'farm_description' => 'nullable|string|max:1000',
            
            // Soil Information
            'soil_type' => 'nullable|string|in:clay,loam,sandy,silt,clay_loam,sandy_loam,silty_clay,silty_loam',
            'soil_ph' => 'nullable|numeric|between:3.0,10.0',
            'organic_matter_content' => 'nullable|numeric|between:0,20',
            'nitrogen_level' => 'nullable|numeric|min:0',
            'phosphorus_level' => 'nullable|numeric|min:0',
            'potassium_level' => 'nullable|numeric|min:0',
            'elevation' => 'nullable|numeric|min:0',
            
            // Water Management
            'water_source' => 'nullable|string|in:irrigation_canal,river,well,shallow_well,pond,rainfall,spring',
            'irrigation_type' => 'nullable|string|in:flood,furrow,sprinkler,drip,manual,none',
            'water_access' => 'nullable|string|in:excellent,good,moderate,poor,very_poor',
            'drainage_quality' => 'nullable|string|in:excellent,good,moderate,poor',
            
            // Rice Varieties and Practices
            'preferred_varieties' => 'nullable|array',
            'preferred_varieties.*' => 'string',
            'planting_method' => 'nullable|string|in:direct_seeding,transplanting,broadcasting,drilling',
            'previous_yield' => 'nullable|numeric|min:0',
            'target_yield' => 'nullable|numeric|min:0',
            'cropping_seasons' => 'nullable|string|in:1,2,3',
            'farming_challenges' => 'nullable|array',
            'farming_challenges.*' => 'string',
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

            if (!$user->isFarmer()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }

            // Update farm if farm_name or location provided
            if ($request->has('farm_name') || $request->has('location')) {
                $farm = Farm::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'name' => $request->farm_name ?? null,
                        'location' => $request->location ?? null,
                        'description' => $request->farm_description ?? null,
                    ]
                );
            } else {
                $farm = Farm::where('user_id', $user->id)->first();
            }

            // Update main field if field-related data provided
            if ($farm && ($request->has('soil_type') || $request->has('rice_area') || $request->has('location'))) {
                // Geocode location if provided
                $coordinates = null;
                $locationData = null;
                
                if ($request->has('location') && $request->location) {
                    $coordinates = $this->geocodeLocation($request->location);
                    $locationData = ['address' => $request->location];
                    if ($coordinates) {
                        $locationData['lat'] = $coordinates['latitude'];
                        $locationData['lon'] = $coordinates['longitude'];
                    }
                }
                
                $field = Field::updateOrCreate(
                    [
                        'user_id' => $user->id,
                        'farm_id' => $farm->id,
                        'name' => 'Main Rice Field'
                    ],
                    array_filter([
                        'location' => $locationData,
                        'field_coordinates' => $coordinates ? [
                            'lat' => $coordinates['latitude'],
                            'lon' => $coordinates['longitude']
                        ] : null,
                        'soil_type' => $request->soil_type ?? null,
                        'soil_ph' => $request->soil_ph ?? null,
                        'organic_matter_content' => $request->organic_matter_content ?? null,
                        'nitrogen_level' => $request->nitrogen_level ?? null,
                        'phosphorus_level' => $request->phosphorus_level ?? null,
                        'potassium_level' => $request->potassium_level ?? null,
                        'size' => $request->rice_area ?? null,
                        'water_access' => $request->water_access ?? null,
                        'water_source' => $request->water_source ?? null,
                        'irrigation_type' => $request->irrigation_type ?? null,
                        'drainage_quality' => $request->drainage_quality ?? null,
                        'elevation' => $request->elevation ?? null,
                        'notes' => $request->hasAny(['farming_experience', 'previous_yield', 'target_yield', 'preferred_varieties', 'planting_method', 'cropping_seasons', 'farming_challenges']) 
                            ? $this->generateFieldNotes($request) 
                            : null,
                    ], function($value) {
                        return $value !== null;
                    })
                );
            }

            // Update user profile
            $addressData = $user->address ?? [];
            if ($request->has('location')) {
                $addressData['farm_location'] = $request->location;
            }
            if ($request->has('total_area')) {
                $addressData['total_area'] = $request->total_area;
            }
            if ($request->has('rice_area')) {
                $addressData['rice_area'] = $request->rice_area;
            }
            if ($request->has('farming_experience')) {
                $addressData['farming_experience'] = $request->farming_experience;
            }
            if ($request->has('preferred_varieties')) {
                $addressData['preferred_varieties'] = $request->preferred_varieties;
            }
            if ($request->has('planting_method')) {
                $addressData['planting_method'] = $request->planting_method;
            }
            if ($request->has('previous_yield')) {
                $addressData['previous_yield'] = $request->previous_yield;
            }
            if ($request->has('target_yield')) {
                $addressData['target_yield'] = $request->target_yield;
            }
            if ($request->has('cropping_seasons')) {
                $addressData['cropping_seasons'] = $request->cropping_seasons;
            }
            if ($request->has('farming_challenges')) {
                $addressData['farming_challenges'] = $request->farming_challenges;
            }

            $user->update(['address' => $addressData]);

            DB::commit();

            // Get updated data
            $farm = Farm::where('user_id', $user->id)->first();
            $fields = Field::where('user_id', $user->id)->get();

            return response()->json([
                'message' => 'Profile updated successfully',
                'farmProfile' => [
                    'farm' => $farm,
                    'fields' => $fields,
                    'user_profile' => $user->address,
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Geocode a location string to get coordinates
     */
    private function geocodeLocation(string $location): ?array
    {
        try {
            $weatherService = app(OpenWeatherAPIService::class);
            $coordinates = $weatherService->getCoordinates($location);
            
            if ($coordinates) {
                Log::info('Geocoded location', [
                    'location' => $location,
                    'coordinates' => $coordinates
                ]);
                return $coordinates;
            }
            
            Log::warning('Failed to geocode location', ['location' => $location]);
            return null;
        } catch (\Exception $e) {
            Log::error('Geocoding error', [
                'location' => $location,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}