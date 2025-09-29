<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 to-emerald-100 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full space-y-8">
      <div class="text-center">
        <div class="mx-auto h-16 w-16 bg-green-600 rounded-full flex items-center justify-center">
          <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
        </div>
        <h2 class="mt-6 text-3xl font-bold text-gray-900">
          Welcome to RiceFARM!
        </h2>
        <p class="mt-2 text-sm text-gray-600">
          Let's set up your farm profile to get started with rice farming management
        </p>
      </div>

      <form @submit.prevent="submitProfile" class="mt-8 space-y-6">
        <div class="bg-white shadow-lg rounded-lg p-8 space-y-6">
          <!-- Farm Information -->
          <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Farm Information</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="farm_name" class="block text-sm font-medium text-gray-700">Farm Name</label>
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
                <label for="farm_size" class="block text-sm font-medium text-gray-700">Total Farm Size (hectares)</label>
                <input
                  id="farm_size"
                  v-model="form.farm_size"
                  type="number"
                  step="0.01"
                  min="0"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  placeholder="0.00"
                />
              </div>
            </div>

            <div class="mt-6">
              <label for="farm_location" class="block text-sm font-medium text-gray-700">Farm Location</label>
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

          <!-- Field Details -->
          <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Field Details</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="soil_type" class="block text-sm font-medium text-gray-700">Primary Soil Type</label>
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
                </select>
              </div>

              <div>
                <label for="water_source" class="block text-sm font-medium text-gray-700">Water Source</label>
                <select
                  id="water_source"
                  v-model="form.water_source"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                >
                  <option value="">Select water source</option>
                  <option value="irrigation">Irrigation System</option>
                  <option value="rainfall">Rainfall Dependent</option>
                  <option value="well">Well Water</option>
                  <option value="river">River Water</option>
                  <option value="pond">Pond/Lake</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Rice Varietal Preferences -->
          <div class="border-b border-gray-200 pb-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Rice Varietal Preferences</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="preferred_variety" class="block text-sm font-medium text-gray-700">Preferred Rice Variety</label>
                <select
                  id="preferred_variety"
                  v-model="form.preferred_variety"
                  required
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                >
                  <option value="">Select rice variety</option>
                  <option value="IR64">IR64</option>
                  <option value="Jasmine">Jasmine Rice</option>
                  <option value="Basmati">Basmati Rice</option>
                  <option value="Arborio">Arborio Rice</option>
                  <option value="Brown Rice">Brown Rice</option>
                  <option value="Sticky Rice">Sticky Rice</option>
                  <option value="Wild Rice">Wild Rice</option>
                </select>
              </div>

              <div>
                <label for="previous_yield" class="block text-sm font-medium text-gray-700">Previous Average Yield (kg/ha)</label>
                <input
                  id="previous_yield"
                  v-model="form.previous_yield"
                  type="number"
                  step="0.1"
                  min="0"
                  class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                  placeholder="0.0"
                />
              </div>
            </div>
          </div>

          <!-- Additional Information -->
          <div>
            <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
            
            <div>
              <label for="farming_experience" class="block text-sm font-medium text-gray-700">Years of Farming Experience</label>
              <input
                id="farming_experience"
                v-model="form.farming_experience"
                type="number"
                min="0"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                placeholder="0"
              />
            </div>

            <div class="mt-6">
              <label for="notes" class="block text-sm font-medium text-gray-700">Additional Notes</label>
              <textarea
                id="notes"
                v-model="form.notes"
                rows="3"
                class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
                placeholder="Any additional information about your farm..."
              ></textarea>
            </div>
          </div>
        </div>

        <!-- Error Message -->
        <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
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

        <!-- Submit Button -->
        <div class="flex justify-end">
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            {{ loading ? 'Setting up your farm...' : 'Complete Setup' }}
          </button>
        </div>
      </form>
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

const form = reactive({
  farm_name: '',
  farm_size: '',
  farm_location: '',
  soil_type: '',
  water_source: '',
  preferred_variety: '',
  previous_yield: '',
  farming_experience: '',
  notes: '',
});

const submitProfile = async () => {
  loading.value = true;
  error.value = '';

  try {
    await farmStore.createFarmProfile(form);
    
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
