<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <div class="mx-auto h-16 w-16 bg-green-600 rounded-full flex items-center justify-center">
          <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
        </div>
        <h2 class="mt-6 text-3xl font-bold text-gray-900">
          Welcome to RiceFARM Management System
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          Let's set up your rice farming profile to get started with comprehensive farm management
        </p>
      </div>

      <!-- Progress Steps -->
      <div class="mb-8">
        <div class="flex items-center justify-center">
          <div class="flex items-center space-x-4">
            <div v-for="(step, index) in steps" :key="index" class="flex items-center">
              <div class="flex items-center justify-center w-8 h-8 rounded-full border-2" 
                   :class="currentStep > index ? 'bg-green-600 border-green-600 text-white' : 
                           currentStep === index ? 'border-green-600 text-green-600' : 
                           'border-gray-300 text-gray-300'">
                <span class="text-sm font-medium">{{ index + 1 }}</span>
              </div>
              <div v-if="index < steps.length - 1" class="w-16 h-0.5 ml-4" 
                   :class="currentStep > index ? 'bg-green-600' : 'bg-gray-300'"></div>
            </div>
          </div>
        </div>
        <div class="flex justify-center mt-2">
          <div class="text-sm text-gray-600 text-center max-w-md">
            {{ steps[currentStep].description }}
          </div>
        </div>
      </div>

      <!-- Form Container -->
      <div class="bg-white shadow-lg rounded-lg p-8">
        <form @submit.prevent="handleSubmit">
          <!-- Step 1: Basic Farm Information -->
          <div v-if="currentStep === 0" class="space-y-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Basic Farm Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="farm_name" class="block text-sm font-medium text-gray-700">Farm Name *</label>
                <input
                  id="farm_name"
                  v-model="form.farm_name"
                  type="text"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  placeholder="Enter your farm name"
                />
              </div>

              <div>
                <label for="farm_location" class="block text-sm font-medium text-gray-700">Farm Location *</label>
                <input
                  id="farm_location"
                  v-model="form.farm_location"
                  type="text"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  placeholder="City, Province, Country"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label for="total_area" class="block text-sm font-medium text-gray-700">Total Farm Area (hectares) *</label>
                <input
                  id="total_area"
                  v-model="form.total_area"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  placeholder="0.00"
                />
              </div>

              <div>
                <label for="rice_area" class="block text-sm font-medium text-gray-700">Rice Cultivation Area (hectares) *</label>
                <input
                  id="rice_area"
                  v-model="form.rice_area"
                  type="number"
                  step="0.01"
                  min="0"
                  :max="form.total_area"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  placeholder="0.00"
                />
              </div>

              <div>
                <label for="farming_experience" class="block text-sm font-medium text-gray-700">Years of Rice Farming Experience</label>
                <input
                  id="farming_experience"
                  v-model="form.farming_experience"
                  type="number"
                  min="0"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  placeholder="0"
                />
              </div>
            </div>

            <div>
              <label for="farm_description" class="block text-sm font-medium text-gray-700">Farm Description</label>
              <textarea
                id="farm_description"
                v-model="form.farm_description"
                rows="3"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                placeholder="Brief description of your farm..."
              ></textarea>
            </div>
          </div>

          <!-- Step 2: Field and Soil Information -->
          <div v-if="currentStep === 1" class="space-y-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Field and Soil Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="soil_type" class="block text-sm font-medium text-gray-700">Primary Soil Type *</label>
                <select
                  id="soil_type"
                  v-model="form.soil_type"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                >
                  <option value="">Select soil type</option>
                  <option value="clay">Clay</option>
                  <option value="loam">Loam</option>
                  <option value="sandy">Sandy</option>
                  <option value="silt">Silt</option>
                  <option value="clay_loam">Clay Loam</option>
                  <option value="sandy_loam">Sandy Loam</option>
                  <option value="silty_clay">Silty Clay</option>
                  <option value="silty_loam">Silty Loam</option>
                </select>
              </div>

              <div>
                <label for="soil_ph" class="block text-sm font-medium text-gray-700">Soil pH Level</label>
                <input
                  id="soil_ph"
                  v-model="form.soil_ph"
                  type="number"
                  step="0.1"
                  min="3.0"
                  max="10.0"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  placeholder="6.5"
                />
                <p class="mt-1 text-xs text-gray-500">Optimal pH for rice: 5.5 - 7.0</p>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label for="organic_matter" class="block text-sm font-medium text-gray-700">Organic Matter Content (%)</label>
                <input
                  id="organic_matter"
                  v-model="form.organic_matter_content"
                  type="number"
                  step="0.1"
                  min="0"
                  max="20"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  placeholder="2.5"
                />
              </div>

              <div>
                <label for="nitrogen_level" class="block text-sm font-medium text-gray-700">Nitrogen Level (ppm)</label>
                <input
                  id="nitrogen_level"
                  v-model="form.nitrogen_level"
                  type="number"
                  step="0.1"
                  min="0"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  placeholder="20"
                />
              </div>

              <div>
                <label for="phosphorus_level" class="block text-sm font-medium text-gray-700">Phosphorus Level (ppm)</label>
                <input
                  id="phosphorus_level"
                  v-model="form.phosphorus_level"
                  type="number"
                  step="0.1"
                  min="0"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  placeholder="15"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="potassium_level" class="block text-sm font-medium text-gray-700">Potassium Level (ppm)</label>
                <input
                  id="potassium_level"
                  v-model="form.potassium_level"
                  type="number"
                  step="0.1"
                  min="0"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  placeholder="25"
                />
              </div>

              <div>
                <label for="elevation" class="block text-sm font-medium text-gray-700">Field Elevation (meters above sea level)</label>
                <input
                  id="elevation"
                  v-model="form.elevation"
                  type="number"
                  step="0.1"
                  min="0"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  placeholder="100"
                />
              </div>
            </div>
          </div>

          <!-- Step 3: Water Management -->
          <div v-if="currentStep === 2" class="space-y-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Water Management</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="water_source" class="block text-sm font-medium text-gray-700">Primary Water Source *</label>
                <select
                  id="water_source"
                  v-model="form.water_source"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                >
                  <option value="">Select water source</option>
                  <option value="irrigation_canal">Irrigation Canal</option>
                  <option value="river">River</option>
                  <option value="well">Deep Well</option>
                  <option value="shallow_well">Shallow Well</option>
                  <option value="pond">Farm Pond</option>
                  <option value="rainfall">Rainfall Dependent</option>
                  <option value="spring">Natural Spring</option>
                </select>
              </div>

              <div>
                <label for="irrigation_type" class="block text-sm font-medium text-gray-700">Irrigation System *</label>
                <select
                  id="irrigation_type"
                  v-model="form.irrigation_type"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                >
                  <option value="">Select irrigation type</option>
                  <option value="flood">Flood Irrigation</option>
                  <option value="furrow">Furrow Irrigation</option>
                  <option value="sprinkler">Sprinkler System</option>
                  <option value="drip">Drip Irrigation</option>
                  <option value="manual">Manual Watering</option>
                  <option value="none">No Irrigation System</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="water_access" class="block text-sm font-medium text-gray-700">Water Access Quality *</label>
                <select
                  id="water_access"
                  v-model="form.water_access"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                >
                  <option value="">Select water access quality</option>
                  <option value="excellent">Excellent - Always available</option>
                  <option value="good">Good - Usually available</option>
                  <option value="moderate">Moderate - Sometimes limited</option>
                  <option value="poor">Poor - Often limited</option>
                  <option value="very_poor">Very Poor - Rarely available</option>
                </select>
              </div>

              <div>
                <label for="drainage_quality" class="block text-sm font-medium text-gray-700">Field Drainage Quality *</label>
                <select
                  id="drainage_quality"
                  v-model="form.drainage_quality"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                >
                  <option value="">Select drainage quality</option>
                  <option value="excellent">Excellent - Quick drainage</option>
                  <option value="good">Good - Adequate drainage</option>
                  <option value="moderate">Moderate - Slow drainage</option>
                  <option value="poor">Poor - Water logging issues</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Step 4: Rice Varieties and Farming Practices -->
          <div v-if="currentStep === 3" class="space-y-6">
            <h3 class="text-lg font-medium text-gray-900 mb-6">Rice Varieties and Farming Practices</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="preferred_varieties" class="block text-sm font-medium text-gray-700">Preferred Rice Varieties</label>
                <div class="mt-2 space-y-2">
                  <div v-for="variety in riceVarieties" :key="variety.value" class="flex items-center">
                    <input
                      :id="variety.value"
                      v-model="form.preferred_varieties"
                      :value="variety.value"
                      type="checkbox"
                      class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                    />
                    <label :for="variety.value" class="ml-2 text-sm text-gray-700">
                      {{ variety.label }}
                    </label>
                  </div>
                </div>
              </div>

              <div>
                <label for="planting_method" class="block text-sm font-medium text-gray-700">Preferred Planting Method</label>
                <select
                  id="planting_method"
                  v-model="form.planting_method"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                >
                  <option value="">Select planting method</option>
                  <option value="direct_seeding">Direct Seeding</option>
                  <option value="transplanting">Transplanting</option>
                  <option value="broadcasting">Broadcasting</option>
                  <option value="drilling">Drilling</option>
                </select>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label for="previous_yield" class="block text-sm font-medium text-gray-700">Previous Average Yield (tons/ha)</label>
                <input
                  id="previous_yield"
                  v-model="form.previous_yield"
                  type="number"
                  step="0.1"
                  min="0"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  placeholder="3.5"
                />
              </div>

              <div>
                <label for="target_yield" class="block text-sm font-medium text-gray-700">Target Yield (tons/ha)</label>
                <input
                  id="target_yield"
                  v-model="form.target_yield"
                  type="number"
                  step="0.1"
                  min="0"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  placeholder="4.0"
                />
              </div>

              <div>
                <label for="cropping_seasons" class="block text-sm font-medium text-gray-700">Cropping Seasons per Year</label>
                <select
                  id="cropping_seasons"
                  v-model="form.cropping_seasons"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                >
                  <option value="">Select seasons</option>
                  <option value="1">1 Season (Wet or Dry)</option>
                  <option value="2">2 Seasons (Wet & Dry)</option>
                  <option value="3">3 Seasons (Continuous)</option>
                </select>
              </div>
            </div>

            <div>
              <label for="farming_challenges" class="block text-sm font-medium text-gray-700">Main Farming Challenges (select all that apply)</label>
              <div class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-2">
                <div v-for="challenge in farmingChallenges" :key="challenge.value" class="flex items-center">
                  <input
                    :id="challenge.value"
                    v-model="form.farming_challenges"
                    :value="challenge.value"
                    type="checkbox"
                    class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                  />
                  <label :for="challenge.value" class="ml-2 text-sm text-gray-700">
                    {{ challenge.label }}
                  </label>
                </div>
              </div>
            </div>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="mt-6 bg-red-50 border border-red-200 rounded-md p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
              </div>
              <div class="ml-3">
                <p class="text-sm text-red-800">{{ error }}</p>
              </div>
            </div>
          </div>

          <!-- Navigation Buttons -->
          <div class="mt-8 flex justify-between">
            <button
              v-if="currentStep > 0"
              type="button"
              @click="previousStep"
              class="px-6 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            >
              Previous
            </button>
            <div v-else></div>

            <button
              v-if="currentStep < steps.length - 1"
              type="button"
              @click="nextStep"
              class="px-6 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
            >
              Next
            </button>
            <button
              v-else
              type="submit"
              :disabled="loading"
              class="px-6 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="loading" class="flex items-center">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                Setting up...
              </span>
              <span v-else>Complete Setup</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useRouter } from 'vue-router';
import { useFarmStore } from '@/stores/farm';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const farmStore = useFarmStore();
const authStore = useAuthStore();

const loading = ref(false);
const error = ref('');
const currentStep = ref(0);

const steps = [
  { title: 'Basic Information', description: 'Tell us about your farm basics' },
  { title: 'Soil & Field', description: 'Provide soil and field characteristics' },
  { title: 'Water Management', description: 'Water source and irrigation details' },
  { title: 'Rice Varieties', description: 'Rice varieties and farming practices' }
];

const form = reactive({
  // Basic Information
  farm_name: '',
  farm_location: '',
  total_area: '',
  rice_area: '',
  farming_experience: '',
  farm_description: '',
  
  // Soil Information
  soil_type: '',
  soil_ph: '',
  organic_matter_content: '',
  nitrogen_level: '',
  phosphorus_level: '',
  potassium_level: '',
  elevation: '',
  
  // Water Management
  water_source: '',
  irrigation_type: '',
  water_access: '',
  drainage_quality: '',
  
  // Rice Varieties and Practices
  preferred_varieties: [],
  planting_method: '',
  previous_yield: '',
  target_yield: '',
  cropping_seasons: '',
  farming_challenges: []
});

const riceVarieties = [
  { value: 'ir64', label: 'IR64 - High yielding variety' },
  { value: 'jasmine', label: 'Jasmine Rice - Aromatic' },
  { value: 'basmati', label: 'Basmati - Premium aromatic' },
  { value: 'brown_rice', label: 'Brown Rice - Nutritious' },
  { value: 'glutinous', label: 'Glutinous Rice - Sticky' },
  { value: 'red_rice', label: 'Red Rice - Antioxidant rich' },
  { value: 'black_rice', label: 'Black Rice - Superfood' },
  { value: 'local_variety', label: 'Local Traditional Variety' }
];

const farmingChallenges = [
  { value: 'pests', label: 'Pest Management' },
  { value: 'diseases', label: 'Disease Control' },
  { value: 'water_shortage', label: 'Water Shortage' },
  { value: 'flooding', label: 'Flooding Issues' },
  { value: 'soil_fertility', label: 'Soil Fertility' },
  { value: 'weather', label: 'Weather Variability' },
  { value: 'labor', label: 'Labor Shortage' },
  { value: 'market_access', label: 'Market Access' },
  { value: 'input_costs', label: 'High Input Costs' },
  { value: 'storage', label: 'Storage Facilities' }
];

const nextStep = () => {
  if (validateCurrentStep()) {
    currentStep.value++;
  }
};

const previousStep = () => {
  currentStep.value--;
  error.value = '';
};

const validateCurrentStep = () => {
  error.value = '';
  
  switch (currentStep.value) {
    case 0:
      if (!form.farm_name || !form.farm_location || !form.total_area || !form.rice_area) {
        error.value = 'Please fill in all required fields.';
        return false;
      }
      if (parseFloat(form.rice_area) > parseFloat(form.total_area)) {
        error.value = 'Rice cultivation area cannot exceed total farm area.';
        return false;
      }
      break;
    case 1:
      if (!form.soil_type) {
        error.value = 'Please select your soil type.';
        return false;
      }
      break;
    case 2:
      if (!form.water_source || !form.irrigation_type || !form.water_access || !form.drainage_quality) {
        error.value = 'Please fill in all water management fields.';
        return false;
      }
      break;
  }
  return true;
};

const handleSubmit = async () => {
  if (!validateCurrentStep()) {
    return;
  }

  loading.value = true;
  error.value = '';

  try {
    await farmStore.createRiceFarmProfile(form);
    
    // Update user data to reflect farm profile completion
    await authStore.fetchUser();
    
    // Redirect to dashboard
    router.push('/dashboard');
  } catch (err) {
    error.value = err.message || 'Failed to create farm profile. Please try again.';
  } finally {
    loading.value = false;
  }
};
</script>