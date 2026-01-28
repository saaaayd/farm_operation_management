<?php

namespace App\Services;

use App\Models\User;
use App\Models\Farm;
use App\Models\Field;
use App\Models\InventoryItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class OnboardingService
{
    /**
     * Onboarding steps configuration
     */
    private $onboardingSteps = [
        'profile_setup' => [
            'name' => 'Profile Setup',
            'description' => 'Complete your farming profile and preferences',
            'required' => true,
            'order' => 1,
        ],
        'farm_setup' => [
            'name' => 'Farm Setup',
            'description' => 'Add your farm information and location',
            'required' => true,
            'order' => 2,
        ],
        'field_setup' => [
            'name' => 'Field Setup',
            'description' => 'Define your fields and their characteristics',
            'required' => true,
            'order' => 3,
        ],
        'initial_inventory' => [
            'name' => 'Initial Inventory',
            'description' => 'Set up your initial inventory items',
            'required' => false,
            'order' => 4,
        ],
        'system_preferences' => [
            'name' => 'System Preferences',
            'description' => 'Configure system settings and notifications',
            'required' => false,
            'order' => 5,
        ],
        'tutorial_completion' => [
            'name' => 'Tutorial',
            'description' => 'Learn how to use the system features',
            'required' => false,
            'order' => 6,
        ],
    ];

    /**
     * Initialize onboarding for a user
     */
    public function initializeOnboarding($userId)
    {
        $user = User::findOrFail($userId);

        // Initialize onboarding progress
        $onboardingData = [
            'started_at' => now(),
            'current_step' => 'profile_setup',
            'completed_steps' => [],
            'skipped_steps' => [],
            'is_complete' => false,
        ];

        $user->update([
            'onboarding_data' => $onboardingData,
            'onboarding_completed_at' => null,
        ]);

        return $onboardingData;
    }

    /**
     * Get onboarding status for a user
     */
    public function getOnboardingStatus($userId)
    {
        $user = User::findOrFail($userId);

        if (!$user->onboarding_data) {
            return [
                'is_started' => false,
                'is_complete' => false,
                'current_step' => null,
                'progress_percentage' => 0,
            ];
        }

        $onboardingData = $user->onboarding_data;
        $completedSteps = $onboardingData['completed_steps'] ?? [];
        $totalSteps = count($this->onboardingSteps);
        $progressPercentage = ($totalSteps > 0) ? (count($completedSteps) / $totalSteps) * 100 : 0;

        return [
            'is_started' => true,
            'is_complete' => $onboardingData['is_complete'] ?? false,
            'current_step' => $onboardingData['current_step'] ?? null,
            'completed_steps' => $completedSteps,
            'skipped_steps' => $onboardingData['skipped_steps'] ?? [],
            'progress_percentage' => round($progressPercentage, 1),
            'started_at' => $onboardingData['started_at'] ?? null,
            'completed_at' => $user->onboarding_completed_at,
        ];
    }

    /**
     * Mark a step as complete
     */
    public function markStepComplete($userId, $step)
    {
        $user = User::findOrFail($userId);
        $onboardingData = $user->onboarding_data ?? [];

        $completedSteps = $onboardingData['completed_steps'] ?? [];

        if (!in_array($step, $completedSteps)) {
            $completedSteps[] = $step;
        }

        $onboardingData['completed_steps'] = $completedSteps;
        $onboardingData['current_step'] = $this->getNextStep($userId, $completedSteps);

        $user->update(['onboarding_data' => $onboardingData]);

        return $onboardingData;
    }

    /**
     * Skip a step
     */
    public function skipStep($userId, $step)
    {
        $user = User::findOrFail($userId);
        $onboardingData = $user->onboarding_data ?? [];

        $skippedSteps = $onboardingData['skipped_steps'] ?? [];

        if (!in_array($step, $skippedSteps)) {
            $skippedSteps[] = $step;
        }

        $onboardingData['skipped_steps'] = $skippedSteps;
        $onboardingData['current_step'] = $this->getNextStep($userId, $onboardingData['completed_steps'] ?? [], $skippedSteps);

        $user->update(['onboarding_data' => $onboardingData]);

        return $onboardingData;
    }

    /**
     * Get next step in onboarding
     */
    public function getNextStep($userId, $completedSteps = null, $skippedSteps = null)
    {
        if ($completedSteps === null || $skippedSteps === null) {
            $user = User::findOrFail($userId);
            $onboardingData = $user->onboarding_data ?? [];
            $completedSteps = $onboardingData['completed_steps'] ?? [];
            $skippedSteps = $onboardingData['skipped_steps'] ?? [];
        }

        $processedSteps = array_merge($completedSteps, $skippedSteps);

        // Find the next unprocessed step
        foreach ($this->onboardingSteps as $stepKey => $stepConfig) {
            if (!in_array($stepKey, $processedSteps)) {
                return $stepKey;
            }
        }

        return null; // All steps completed or skipped
    }

    /**
     * Complete entire onboarding process
     */
    public function completeOnboarding($userId)
    {
        $user = User::findOrFail($userId);
        $onboardingData = $user->onboarding_data ?? [];

        $onboardingData['is_complete'] = true;
        $onboardingData['completed_at'] = now();
        $onboardingData['current_step'] = null;

        $user->update([
            'onboarding_data' => $onboardingData,
            'onboarding_completed_at' => now(),
        ]);

        // Trigger post-onboarding actions
        $this->triggerPostOnboardingActions($userId);

        return $onboardingData;
    }

    /**
     * Update user profile during onboarding
     */
    public function updateUserProfile($userId, $profileData)
    {
        $user = User::findOrFail($userId);

        // Update user fields
        $userFields = array_intersect_key($profileData, array_flip([
            'phone',
            'address'
        ]));

        if (!empty($userFields)) {
            $user->update($userFields);
        }

        // Update or create user metadata
        $metadata = $user->metadata ?? [];
        $metadataFields = array_intersect_key($profileData, array_flip([
            'farming_experience_years',
            'farm_size_preference',
            'primary_crops',
            'farming_goals'
        ]));

        $metadata = array_merge($metadata, $metadataFields);
        $user->update(['metadata' => $metadata]);

        return $user;
    }

    /**
     * Create farm during onboarding
     */
    public function createFarm($farmData)
    {
        try {
            $farm = Farm::create([
                'user_id' => $farmData['user_id'],
                'name' => $farmData['name'],
                'address' => $farmData['address'],
                'total_area' => $farmData['total_area'],
                'farm_type' => $farmData['farm_type'] ?? 'rice',
                'ownership_type' => $farmData['ownership_type'] ?? 'owned',
                'coordinates' => $farmData['coordinates'] ?? null,
                'is_active' => true,
            ]);

            return $farm;
        } catch (\Exception $e) {
            Log::error('Failed to create farm during onboarding: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create field during onboarding
     */
    public function createField($fieldData)
    {
        try {
            $field = Field::create([
                'farm_id' => $fieldData['farm_id'],
                'name' => $fieldData['name'],
                'size_hectares' => $fieldData['size_hectares'],
                'soil_type' => $fieldData['soil_type'],
                'irrigation_type' => $fieldData['irrigation_type'],
                'location' => $fieldData['location'] ?? null,
                'is_active' => true,
            ]);

            return $field;
        } catch (\Exception $e) {
            Log::error('Failed to create field during onboarding: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create inventory item during onboarding
     */
    public function createInventoryItem($itemData)
    {
        try {
            $item = InventoryItem::create([
                'user_id' => $itemData['user_id'],
                'name' => $itemData['name'],
                'category' => $itemData['category'],
                'current_stock' => $itemData['current_stock'] ?? $itemData['quantity'] ?? 0,
                'unit' => $itemData['unit'],
                'unit_price' => $itemData['unit_price'] ?? $itemData['price'] ?? 0,
                'minimum_stock' => $itemData['minimum_stock'] ?? $itemData['min_stock'] ?? 0,
                'description' => $itemData['description'] ?? '',
                'supplier' => $itemData['supplier'] ?? '',
            ]);

            return $item;
        } catch (\Exception $e) {
            Log::error('Failed to create inventory item during onboarding: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update system preferences
     */
    public function updateSystemPreferences($userId, $preferences)
    {
        $user = User::findOrFail($userId);

        $settings = $user->settings ?? [];
        $settings = array_merge($settings, $preferences);

        $user->update(['settings' => $settings]);

        return $user;
    }

    /**
     * Update tutorial progress
     */
    public function updateTutorialProgress($userId, $completedSections)
    {
        $user = User::findOrFail($userId);
        $onboardingData = $user->onboarding_data ?? [];

        $onboardingData['tutorial_progress'] = [
            'completed_sections' => $completedSections,
            'completed_at' => now(),
        ];

        $user->update(['onboarding_data' => $onboardingData]);

        return $onboardingData;
    }

    /**
     * Get onboarding progress details
     */
    public function getOnboardingProgress($userId)
    {
        $status = $this->getOnboardingStatus($userId);

        $progress = [];
        foreach ($this->onboardingSteps as $stepKey => $stepConfig) {
            $isCompleted = in_array($stepKey, $status['completed_steps'] ?? []);
            $isSkipped = in_array($stepKey, $status['skipped_steps'] ?? []);
            $isCurrent = $status['current_step'] === $stepKey;

            $progress[] = [
                'step' => $stepKey,
                'name' => $stepConfig['name'],
                'description' => $stepConfig['description'],
                'required' => $stepConfig['required'],
                'order' => $stepConfig['order'],
                'is_completed' => $isCompleted,
                'is_skipped' => $isSkipped,
                'is_current' => $isCurrent,
                'status' => $isCompleted ? 'completed' : ($isSkipped ? 'skipped' : ($isCurrent ? 'current' : 'pending')),
            ];
        }

        // Sort by order
        usort($progress, function ($a, $b) {
            return $a['order'] <=> $b['order'];
        });

        return [
            'steps' => $progress,
            'overall_status' => $status,
        ];
    }

    /**
     * Reset onboarding process
     */
    public function resetOnboarding($userId)
    {
        $user = User::findOrFail($userId);

        $user->update([
            'onboarding_data' => null,
            'onboarding_completed_at' => null,
        ]);

        return true;
    }

    /**
     * Get personalized recommendations
     */
    public function getPersonalizedRecommendations($userId)
    {
        $user = User::findOrFail($userId);
        $metadata = $user->metadata ?? [];
        $farms = Farm::where('user_id', $userId)->get();

        $recommendations = [];

        // Experience-based recommendations
        $experience = $metadata['farming_experience_years'] ?? 0;
        if ($experience < 2) {
            $recommendations[] = [
                'type' => 'learning',
                'title' => 'New Farmer Resources',
                'description' => 'Access beginner-friendly guides and tutorials to get started with rice farming.',
                'action' => 'View Learning Materials',
                'priority' => 'high',
            ];
        }

        // Farm size recommendations
        $farmSizePreference = $metadata['farm_size_preference'] ?? null;
        if ($farmSizePreference === 'small') {
            $recommendations[] = [
                'type' => 'optimization',
                'title' => 'Small Farm Optimization',
                'description' => 'Learn techniques to maximize yield on smaller plots.',
                'action' => 'View Small Farm Tips',
                'priority' => 'medium',
            ];
        }

        // Crop-specific recommendations
        $primaryCrops = $metadata['primary_crops'] ?? [];
        if (in_array('rice', $primaryCrops)) {
            $recommendations[] = [
                'type' => 'crop_management',
                'title' => 'Rice Growth Stage Tracking',
                'description' => 'Set up growth stage monitoring for better rice management.',
                'action' => 'Setup Growth Tracking',
                'priority' => 'high',
            ];
        }

        // System setup recommendations
        if ($farms->isEmpty()) {
            $recommendations[] = [
                'type' => 'setup',
                'title' => 'Complete Farm Setup',
                'description' => 'Add your farm information to start tracking your operations.',
                'action' => 'Add Farm',
                'priority' => 'high',
            ];
        }

        return $recommendations;
    }

    /**
     * Perform quick setup
     */
    public function performQuickSetup($userId, $setupData)
    {
        DB::beginTransaction();

        try {
            // Create farm
            $farm = $this->createFarm([
                'user_id' => $userId,
                'name' => $setupData['farm_name'],
                'address' => $setupData['farm_address'],
                'total_area' => $setupData['total_area'],
                'farm_type' => 'rice',
                'ownership_type' => 'owned',
            ]);

            // Create a default field
            $field = $this->createField([
                'farm_id' => $farm->id,
                'name' => 'Main Field',
                'size_hectares' => $setupData['total_area'],
                'soil_type' => 'loam',
                'irrigation_type' => 'irrigated',
            ]);

            // Update user profile
            $this->updateUserProfile($userId, [
                'farming_experience_years' => $this->mapExperienceToYears($setupData['farming_experience']),
                'primary_crops' => [$setupData['primary_crop']],
            ]);

            // Mark all steps as completed
            foreach (array_keys($this->onboardingSteps) as $step) {
                $this->markStepComplete($userId, $step);
            }

            // Complete onboarding
            $this->completeOnboarding($userId);

            DB::commit();

            return [
                'farm' => $farm,
                'field' => $field,
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Get setup templates
     */
    public function getSetupTemplates()
    {
        return [
            'small_rice_farm' => [
                'name' => 'Small Rice Farm',
                'description' => 'Perfect for farms under 5 hectares focusing on rice production',
                'recommended_for' => 'Small-scale rice farmers',
                'features' => [
                    'Basic inventory management',
                    'Simple financial tracking',
                    'Weather monitoring',
                    'Growth stage tracking',
                ],
                'default_settings' => [
                    'farm_type' => 'rice',
                    'field_count' => 2,
                    'default_field_size' => 2.5,
                    'inventory_categories' => ['seeds', 'fertilizers', 'pesticides'],
                ],
            ],
            'large_rice_farm' => [
                'name' => 'Large Rice Farm',
                'description' => 'Comprehensive setup for farms over 20 hectares',
                'recommended_for' => 'Commercial rice farmers',
                'features' => [
                    'Advanced inventory management',
                    'Detailed financial analysis',
                    'Multi-field weather monitoring',
                    'Labor management',
                    'Automated reporting',
                ],
                'default_settings' => [
                    'farm_type' => 'rice',
                    'field_count' => 5,
                    'default_field_size' => 10,
                    'inventory_categories' => ['seeds', 'fertilizers', 'pesticides', 'equipment', 'tools'],
                ],
            ],
            'mixed_crop_farm' => [
                'name' => 'Mixed Crop Farm',
                'description' => 'For farms growing rice alongside other crops',
                'recommended_for' => 'Diversified farmers',
                'features' => [
                    'Multi-crop tracking',
                    'Crop rotation planning',
                    'Diversified inventory',
                    'Comparative analytics',
                ],
                'default_settings' => [
                    'farm_type' => 'mixed',
                    'field_count' => 4,
                    'default_field_size' => 5,
                    'inventory_categories' => ['seeds', 'fertilizers', 'pesticides', 'equipment'],
                ],
            ],
            'organic_farm' => [
                'name' => 'Organic Farm',
                'description' => 'Specialized setup for organic farming practices',
                'recommended_for' => 'Organic certified farmers',
                'features' => [
                    'Organic compliance tracking',
                    'Natural pest management',
                    'Soil health monitoring',
                    'Certification reporting',
                ],
                'default_settings' => [
                    'farm_type' => 'organic',
                    'field_count' => 3,
                    'default_field_size' => 4,
                    'inventory_categories' => ['organic_seeds', 'organic_fertilizers', 'bio_pesticides'],
                ],
            ],
        ];
    }

    /**
     * Apply setup template
     */
    public function applySetupTemplate($userId, $templateId, $customizations = [])
    {
        $templates = $this->getSetupTemplates();

        if (!isset($templates[$templateId])) {
            throw new \Exception('Invalid template ID');
        }

        $template = $templates[$templateId];
        $settings = array_merge($template['default_settings'], $customizations);

        DB::beginTransaction();

        try {
            // Apply template-specific setup logic
            $result = $this->executeTemplateSetup($userId, $templateId, $settings);

            // Mark relevant onboarding steps as completed
            $this->markStepComplete($userId, 'profile_setup');
            $this->markStepComplete($userId, 'system_preferences');

            DB::commit();

            return $result;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Execute template-specific setup
     */
    private function executeTemplateSetup($userId, $templateId, $settings)
    {
        // This would contain template-specific logic
        // For now, returning a basic structure

        return [
            'template_applied' => $templateId,
            'settings_applied' => $settings,
            'message' => "Template '{$templateId}' has been applied successfully",
        ];
    }

    /**
     * Map experience level to years
     */
    private function mapExperienceToYears($experienceLevel)
    {
        return match ($experienceLevel) {
            'beginner' => 1,
            'intermediate' => 5,
            'advanced' => 10,
            default => 0,
        };
    }

    /**
     * Trigger post-onboarding actions
     */
    private function triggerPostOnboardingActions($userId)
    {
        // Send welcome email
        // Create initial recommendations
        // Set up default notifications
        // Schedule first weather update

        Log::info("Onboarding completed for user {$userId}");
    }
}