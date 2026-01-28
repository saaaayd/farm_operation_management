<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full w-full mx-auto space-y-8">
      <div class="text-center">
        <div class="mx-auto h-20 w-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-105 transition-transform duration-200">
          <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
        </div>
        <h2 class="mt-6 text-4xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
          Welcome to RiceFARM!
        </h2>
        <p class="mt-3 text-base text-gray-600 max-w-md mx-auto">
          Let's set up your farm profile to get started with comprehensive rice farming management
        </p>
      </div>
      
      <form @submit.prevent="submitProfile" class="mt-8 space-y-6">
        <div class="bg-white shadow-xl rounded-2xl p-8 md:p-10 space-y-8 border border-gray-100">
          <!-- Farm Information -->
          <div class="border-b border-gray-200 pb-8">
            <div class="flex items-center mb-6">
              <div class="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-gray-900">Farm Information</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="farm_name" class="block text-sm font-semibold text-gray-700 mb-2">Farm Name *</label>
                <input
                id="farm_name"
                v-model="form.farm_name"
                type="text"
                required
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                placeholder="Enter your farm name"
                />
              </div>
              
              <div>
                <label for="total_area" class="block text-sm font-semibold text-gray-700 mb-2">Total Farm Area (hectares) *</label>
                <input
                id="total_area"
                v-model="form.total_area"
                type="number"
                step="0.01"
                min="0"
                required
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                placeholder="0.00"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
              <div>
                <label for="rice_area" class="block text-sm font-semibold text-gray-700 mb-2">Rice Cultivation Area (hectares) *</label>
                <input
                id="rice_area"
                v-model="form.rice_area"
                type="number"
                step="0.01"
                min="0"
                :max="form.total_area"
                required
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                placeholder="0.00"
                />
                <p v-if="form.rice_area && parseFloat(form.rice_area) > parseFloat(form.total_area || 0)" class="mt-1 text-xs text-red-600">
                  Rice area cannot exceed total farm area
                </p>
              </div>

              <div>
                <label for="farming_experience" class="block text-sm font-semibold text-gray-700 mb-2">Years of Rice Farming Experience</label>
                <input
                id="farming_experience"
                v-model="form.farming_experience"
                type="number"
                min="0"
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                placeholder="0"
                />
              </div>
            </div>

            <div class="mt-6">
              <label for="farm_description" class="block text-sm font-semibold text-gray-700 mb-2">Farm Description</label>
              <textarea
              id="farm_description"
              v-model="form.farm_description"
              rows="3"
              class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 resize-none"
              placeholder="Brief description of your farm..."
              ></textarea>
            </div>
          </div>
          
          <!-- Farm Location -->
          <div class="border-b border-gray-200 pb-8">
            <div class="flex items-center mb-6">
              <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-gray-900">Farm Location</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="province" class="block text-sm font-semibold text-gray-700 mb-2">Province</label>
                <div class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                  Bukidnon
                </div>
              </div>
            
              <div>
                <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">City / Municipality</label>
                <div class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                  City of Malaybalay
                </div>
              </div>
          
              <div class="md:col-span-2">
                <label for="barangay" class="block text-sm font-semibold text-gray-700 mb-2">Barangay</label>
                <div class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg bg-gray-50 text-gray-600">
                  Managok
                </div>
              </div>
            </div>
          </div>
      
          <div v-if="form.address" class="mt-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl shadow-sm">
            <div class="flex items-start">
              <svg class="h-5 w-5 text-green-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div>
                <p class="text-sm font-semibold text-gray-800 mb-1">Selected Address:</p>
                <p class="text-sm text-gray-700 mb-2">{{ form.address }}</p>
                <p class="text-xs text-gray-600 flex items-center">
                  <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Location will be automatically converted to coordinates for weather data
                </p>
              </div>
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
        <div class="flex justify-end pt-6">
          <button
          type="submit"
          :disabled="loading"
          class="group relative w-full md:w-auto min-w-[200px] flex justify-center items-center py-4 px-8 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
          >
          <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-4">
            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
          </span>
          <span v-if="!loading" class="mr-2">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
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
import { ref, reactive, onMounted, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useFarmStore } from '@/stores/farm';
import { useAuthStore } from '@/stores/auth';
import { useMarketplaceStore } from '@/stores/marketplace'; // Import Marketplace Store
import axios from 'axios';

const router = useRouter();
const farmStore = useFarmStore();
const authStore = useAuthStore();
const marketplaceStore = useMarketplaceStore(); // Initialize Marketplace Store

const loading = ref(false);
const error = ref('');

const form = reactive({
  // Basic Information
  farm_name: '',
  total_area: '',
  rice_area: '',
  farming_experience: '',
  farm_description: '',
  
  // Farm Location - Static
  address: 'Managok, City of Malaybalay, Bukidnon',
});

// Load provinces on mount
onMounted(async () => {
  // Static location assumed
  await marketplaceStore.fetchRiceVarieties(); // Fetch varieties for dropdowns
});

const riceVarieties = computed(() => marketplaceStore.riceVarieties || []);

const submitProfile = async () => {
  // Clear previous errors
  error.value = '';
  
  // Validate all required fields
  const requiredFields = {
    'Farm Name': form.farm_name,
    'Total Farm Area': form.total_area,
    'Rice Cultivation Area': form.rice_area,
    'Farm Location': form.address,
  };
  
  const missingFields = Object.entries(requiredFields)
    .filter(([_, value]) => !value || (typeof value === 'string' && value.trim() === ''))
    .map(([name]) => name);
  
  if (missingFields.length > 0) {
    error.value = `Please fill in all required fields: ${missingFields.join(', ')}`;
    return;
  }
  
  if (parseFloat(form.rice_area) > parseFloat(form.total_area)) {
    error.value = 'Rice cultivation area cannot exceed total farm area.';
    return;
  }
  
  loading.value = true;
  error.value = '';
  
  try {
    // Map form data to match backend expectations
    const profileData = {
      farm_name: form.farm_name,
      farm_location: form.address, // Use Philippine address as location string for geocoding
      total_area: form.total_area,
      rice_area: form.rice_area,
      farming_experience: form.farming_experience || null,
      farm_description: form.farm_description || null,
    };
    
    await farmStore.createRiceFarmProfile(profileData);
    
    // Update user data to reflect farm profile completion
    await authStore.fetchUser();
    
    // Redirect to dashboard
    router.push('/dashboard');
  } catch (err) {
    console.error('Profile creation error:', err);
    
    // Show backend validation errors if available
    if (err.response?.data?.errors) {
      const validationErrors = Object.entries(err.response.data.errors)
        .map(([field, messages]) => `${field}: ${Array.isArray(messages) ? messages.join(', ') : messages}`)
        .join('; ');
      error.value = `Validation errors: ${validationErrors}`;
    } else {
      error.value = err.response?.data?.message || err.message || 'Failed to create farm profile. Please try again.';
    }
  } finally {
    loading.value = false;
  }
};
</script>