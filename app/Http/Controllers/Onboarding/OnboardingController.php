<?php

namespace App\Http\Controllers\Onboarding;

use App\Http\Controllers\Controller;
use App\Services\OnboardingService;
use App\Models\User;
use App\Models\Farm;
use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OnboardingController extends Controller
{
    protected $onboardingService;

    public function __construct(OnboardingService $onboardingService)
    {
        $this->onboardingService = $onboardingService;
    }

    /**
     * Get onboarding status for current user
     */
    public function getOnboardingStatus()
    {
        $user = Auth::user();
        $status = $this->onboardingService->getOnboardingStatus($user->id);
        
        return response()->json([
            'status' => $status,
            'user' => $user,
        ]);
    }

    /**
     * Start the onboarding process
     */
    public function startOnboarding()
    {
        $user = Auth::user();
        
        // Initialize onboarding progress
        $this->onboardingService->initializeOnboarding($user->id);
        
        return response()->json([
            'message' => 'Onboarding process started',
            'next_step' => 'profile_setup',
        ]);
    }

    /**
     * Complete profile setup step
     */
    public function completeProfileSetup(Request $request)
    {
        $request->validate([
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:255',
            'farming_experience_years' => 'nullable|integer|min:0|max:100',
            'farm_size_preference' => 'nullable|in:small,medium,large',
            'primary_crops' => 'nullable|array',
            'primary_crops.*' => 'string|max:50',
            'farming_goals' => 'nullable|array',
            'farming_goals.*' => 'string|max:100',
        ]);

        $user = Auth::user();
        
        // Update user profile
        $profileData = $request->only([
            'phone', 'address', 'farming_experience_years', 
            'farm_size_preference', 'primary_crops', 'farming_goals'
        ]);
        
        $this->onboardingService->updateUserProfile($user->id, $profileData);
        
        // Mark profile setup as complete
        $this->onboardingService->markStepComplete($user->id, 'profile_setup');
        
        return response()->json([
            'message' => 'Profile setup completed',
            'next_step' => 'farm_setup',
        ]);
    }

    /**
     * Complete farm setup step
     */
    public function completeFarmSetup(Request $request)
    {
        $request->validate([
            'farm_name' => 'required|string|max:100',
            'farm_address' => 'required|string|max:255',
            'total_area' => 'required|numeric|min:0.1|max:10000',
            'farm_type' => 'required|in:rice,mixed,organic,conventional',
            'ownership_type' => 'required|in:owned,leased,partnership',
            'coordinates' => 'nullable|array',
            'coordinates.lat' => 'nullable|numeric|between:-90,90',
            'coordinates.lng' => 'nullable|numeric|between:-180,180',
        ]);

        $user = Auth::user();
        
        DB::beginTransaction();
        try {
            // Create farm
            $farmData = [
                'user_id' => $user->id,
                'name' => $request->farm_name,
                'address' => $request->farm_address,
                'total_area' => $request->total_area,
                'farm_type' => $request->farm_type,
                'ownership_type' => $request->ownership_type,
            ];
            
            if ($request->coordinates) {
                $farmData['coordinates'] = $request->coordinates;
            }
            
            $farm = $this->onboardingService->createFarm($farmData);
            
            // Mark farm setup as complete
            $this->onboardingService->markStepComplete($user->id, 'farm_setup');
            
            DB::commit();
            
            return response()->json([
                'message' => 'Farm setup completed',
                'farm' => $farm,
                'next_step' => 'field_setup',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Farm setup failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete field setup step
     */
    public function completeFieldSetup(Request $request)
    {
        $request->validate([
            'fields' => 'required|array|min:1',
            'fields.*.name' => 'required|string|max:100',
            'fields.*.size_hectares' => 'required|numeric|min:0.01|max:1000',
            'fields.*.soil_type' => 'required|in:clay,loam,sandy,silt,peat',
            'fields.*.irrigation_type' => 'required|in:rain_fed,irrigated,drip,sprinkler',
            'fields.*.location' => 'nullable|array',
            'fields.*.location.lat' => 'nullable|numeric|between:-90,90',
            'fields.*.location.lon' => 'nullable|numeric|between:-180,180',
        ]);

        $user = Auth::user();
        $farm = Farm::where('user_id', $user->id)->latest()->first();
        
        if (!$farm) {
            return response()->json([
                'message' => 'No farm found. Please complete farm setup first.',
            ], 400);
        }

        DB::beginTransaction();
        try {
            $fields = [];
            
            foreach ($request->fields as $fieldData) {
                $fieldData['farm_id'] = $farm->id;
                $field = $this->onboardingService->createField($fieldData);
                $fields[] = $field;
            }
            
            // Mark field setup as complete
            $this->onboardingService->markStepComplete($user->id, 'field_setup');
            
            DB::commit();
            
            return response()->json([
                'message' => 'Field setup completed',
                'fields' => $fields,
                'next_step' => 'initial_inventory',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Field setup failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete initial inventory setup
     */
    public function completeInitialInventory(Request $request)
    {
        $request->validate([
            'inventory_items' => 'nullable|array',
            'inventory_items.*.name' => 'required|string|max:100',
            'inventory_items.*.category' => 'required|in:seeds,fertilizers,pesticides,equipment,tools,materials',
            'inventory_items.*.quantity' => 'required|numeric|min:0',
            'inventory_items.*.unit' => 'required|string|max:20',
            'inventory_items.*.price' => 'nullable|numeric|min:0',
            'inventory_items.*.min_stock' => 'nullable|numeric|min:0',
            'skip_inventory' => 'boolean',
        ]);

        $user = Auth::user();
        
        if ($request->skip_inventory) {
            // Mark inventory setup as complete without adding items
            $this->onboardingService->markStepComplete($user->id, 'initial_inventory');
            
            return response()->json([
                'message' => 'Initial inventory setup skipped',
                'next_step' => 'system_preferences',
            ]);
        }

        DB::beginTransaction();
        try {
            $inventoryItems = [];
            
            if ($request->inventory_items) {
                foreach ($request->inventory_items as $itemData) {
                    $itemData['user_id'] = $user->id;
                    $item = $this->onboardingService->createInventoryItem($itemData);
                    $inventoryItems[] = $item;
                }
            }
            
            // Mark inventory setup as complete
            $this->onboardingService->markStepComplete($user->id, 'initial_inventory');
            
            DB::commit();
            
            return response()->json([
                'message' => 'Initial inventory setup completed',
                'inventory_items' => $inventoryItems,
                'next_step' => 'system_preferences',
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Initial inventory setup failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Complete system preferences setup
     */
    public function completeSystemPreferences(Request $request)
    {
        $request->validate([
            'currency' => 'required|string|size:3',
            'unit_system' => 'required|in:metric,imperial',
            'language' => 'required|string|size:2',
            'timezone' => 'required|string|max:50',
            'notifications' => 'required|array',
            'notifications.email' => 'boolean',
            'notifications.sms' => 'boolean',
            'notifications.weather_alerts' => 'boolean',
            'notifications.inventory_alerts' => 'boolean',
            'notifications.financial_reports' => 'boolean',
        ]);

        $user = Auth::user();
        
        // Update system preferences
        $preferences = $request->only([
            'currency', 'unit_system', 'language', 'timezone', 'notifications'
        ]);
        
        $this->onboardingService->updateSystemPreferences($user->id, $preferences);
        
        // Mark preferences setup as complete
        $this->onboardingService->markStepComplete($user->id, 'system_preferences');
        
        return response()->json([
            'message' => 'System preferences setup completed',
            'next_step' => 'tutorial_completion',
        ]);
    }

    /**
     * Complete tutorial
     */
    public function completeTutorial(Request $request)
    {
        $request->validate([
            'completed_sections' => 'required|array',
            'completed_sections.*' => 'string|in:dashboard,farms,inventory,financial,reports,weather',
            'skip_tutorial' => 'boolean',
        ]);

        $user = Auth::user();
        
        if ($request->skip_tutorial) {
            // Mark tutorial as complete without tracking sections
            $this->onboardingService->markStepComplete($user->id, 'tutorial_completion');
            $this->onboardingService->completeOnboarding($user->id);
            
            return response()->json([
                'message' => 'Tutorial skipped, onboarding completed',
                'onboarding_complete' => true,
            ]);
        }

        // Track completed tutorial sections
        $this->onboardingService->updateTutorialProgress($user->id, $request->completed_sections);
        
        // Mark tutorial as complete
        $this->onboardingService->markStepComplete($user->id, 'tutorial_completion');
        
        // Complete entire onboarding process
        $this->onboardingService->completeOnboarding($user->id);
        
        return response()->json([
            'message' => 'Onboarding process completed successfully',
            'onboarding_complete' => true,
        ]);
    }

    /**
     * Skip onboarding step
     */
    public function skipStep(Request $request)
    {
        $request->validate([
            'step' => 'required|in:profile_setup,farm_setup,field_setup,initial_inventory,system_preferences,tutorial_completion',
        ]);

        $user = Auth::user();
        $step = $request->step;
        
        // Mark step as skipped
        $this->onboardingService->skipStep($user->id, $step);
        
        // Get next step
        $nextStep = $this->onboardingService->getNextStep($user->id);
        
        return response()->json([
            'message' => "Step '{$step}' skipped",
            'next_step' => $nextStep,
        ]);
    }

    /**
     * Get onboarding progress
     */
    public function getProgress()
    {
        $user = Auth::user();
        $progress = $this->onboardingService->getOnboardingProgress($user->id);
        
        return response()->json([
            'progress' => $progress,
        ]);
    }

    /**
     * Reset onboarding process
     */
    public function resetOnboarding()
    {
        $user = Auth::user();
        
        // This would typically require elevated support permissions or special circumstances
        $this->onboardingService->resetOnboarding($user->id);
        
        return response()->json([
            'message' => 'Onboarding process has been reset',
        ]);
    }

    /**
     * Get onboarding recommendations
     */
    public function getRecommendations()
    {
        $user = Auth::user();
        $recommendations = $this->onboardingService->getPersonalizedRecommendations($user->id);
        
        return response()->json([
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Complete quick setup (simplified onboarding)
     */
    public function completeQuickSetup(Request $request)
    {
        $request->validate([
            'farm_name' => 'required|string|max:100',
            'farm_address' => 'required|string|max:255',
            'total_area' => 'required|numeric|min:0.1|max:10000',
            'primary_crop' => 'required|string|max:50',
            'farming_experience' => 'required|in:beginner,intermediate,advanced',
        ]);

        $user = Auth::user();
        
        DB::beginTransaction();
        try {
            // Quick setup with minimal required information
            $setupData = $request->only([
                'farm_name', 'farm_address', 'total_area', 'primary_crop', 'farming_experience'
            ]);
            
            $result = $this->onboardingService->performQuickSetup($user->id, $setupData);
            
            DB::commit();
            
            return response()->json([
                'message' => 'Quick setup completed successfully',
                'farm' => $result['farm'],
                'field' => $result['field'],
                'onboarding_complete' => true,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Quick setup failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get setup templates based on user type
     */
    public function getSetupTemplates()
    {
        $templates = $this->onboardingService->getSetupTemplates();
        
        return response()->json([
            'templates' => $templates,
        ]);
    }

    /**
     * Apply setup template
     */
    public function applyTemplate(Request $request)
    {
        $request->validate([
            'template_id' => 'required|string|in:small_rice_farm,large_rice_farm,mixed_crop_farm,organic_farm',
            'customizations' => 'nullable|array',
        ]);

        $user = Auth::user();
        
        DB::beginTransaction();
        try {
            $result = $this->onboardingService->applySetupTemplate(
                $user->id, 
                $request->template_id, 
                $request->customizations ?? []
            );
            
            DB::commit();
            
            return response()->json([
                'message' => 'Setup template applied successfully',
                'setup_result' => $result,
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Template application failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}