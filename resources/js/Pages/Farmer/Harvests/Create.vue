<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <router-link to="/dashboard" class="text-gray-500 hover:text-gray-700 mr-4">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </router-link>
            <div>
              <h1 class="text-xl font-semibold text-gray-900">Record Rice Harvest</h1>
              <p class="text-sm text-gray-500">Track your rice harvest yield and quality</p>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <form @submit.prevent="submitHarvest" class="space-y-8">
        <!-- Planting Selection -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Select Planting</h3>
          
          <div>
            <label for="planting_id" class="block text-sm font-medium text-gray-700 mb-2">
              Choose Planting *
            </label>
            <select
              id="planting_id"
              v-model="form.planting_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
            >
              <option value="">Select a planting</option>
              <option v-for="planting in availablePlantings" :key="planting.id" :value="planting.id">
                {{ planting.crop_type }} - {{ planting.field?.name }} ({{ planting.field?.size }} ha)
              </option>
            </select>
            <p class="mt-1 text-sm text-gray-500">
              Select the planting you want to harvest
            </p>
          </div>
        </div>

        <!-- Harvest Details -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Harvest Details</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="harvest_date" class="block text-sm font-medium text-gray-700 mb-2">
                Harvest Date *
              </label>
              <input
                id="harvest_date"
                v-model="form.harvest_date"
                type="date"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
              />
            </div>

            <div>
              <label for="yield" class="block text-sm font-medium text-gray-700 mb-2">
                Total Yield (kg) *
              </label>
              <input
                id="yield"
                v-model="form.yield"
                type="number"
                step="0.1"
                min="0"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                placeholder="0.0"
              />
            </div>
          </div>

          <div class="mt-6">
            <label for="yield_per_hectare" class="block text-sm font-medium text-gray-700 mb-2">
              Yield per Hectare (kg/ha)
            </label>
            <input
              id="yield_per_hectare"
              v-model="form.yield_per_hectare"
              type="number"
              step="0.1"
              min="0"
              readonly
              class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50"
              placeholder="Calculated automatically"
            />
            <p class="mt-1 text-sm text-gray-500">
              Calculated based on total yield and field size
            </p>
          </div>
        </div>

        <!-- Quality Assessment -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Quality Assessment</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="quality_grade" class="block text-sm font-medium text-gray-700 mb-2">
                Quality Grade *
              </label>
              <select
                id="quality_grade"
                v-model="form.quality_grade"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
              >
                <option value="">Select quality grade</option>
                <option value="A">Grade A - Excellent (95-100% whole grains)</option>
                <option value="B">Grade B - Good (85-94% whole grains)</option>
                <option value="C">Grade C - Average (75-84% whole grains)</option>
                <option value="D">Grade D - Poor (Below 75% whole grains)</option>
              </select>
            </div>

            <div>
              <label for="moisture_content" class="block text-sm font-medium text-gray-700 mb-2">
                Moisture Content (%)
              </label>
              <input
                id="moisture_content"
                v-model="form.moisture_content"
                type="number"
                step="0.1"
                min="0"
                max="100"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                placeholder="14.0"
              />
              <p class="mt-1 text-sm text-gray-500">
                Ideal moisture content: 12-14%
              </p>
            </div>
          </div>

          <div class="mt-6">
            <label for="broken_grains_percentage" class="block text-sm font-medium text-gray-700 mb-2">
              Broken Grains Percentage (%)
            </label>
            <input
              id="broken_grains_percentage"
              v-model="form.broken_grains_percentage"
              type="number"
              step="0.1"
              min="0"
              max="100"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
              placeholder="5.0"
            />
            <p class="mt-1 text-sm text-gray-500">
              Lower percentage indicates better quality
            </p>
          </div>
        </div>

        <!-- Pricing and Storage -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Pricing & Storage</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="price_per_kg" class="block text-sm font-medium text-gray-700 mb-2">
                Price per kg ($)
              </label>
              <input
                id="price_per_kg"
                v-model="form.price_per_kg"
                type="number"
                step="0.01"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                placeholder="0.00"
              />
            </div>

            <div>
              <label for="storage_location" class="block text-sm font-medium text-gray-700 mb-2">
                Storage Location
              </label>
              <input
                id="storage_location"
                v-model="form.storage_location"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                placeholder="Warehouse A, Silo B, etc."
              />
            </div>
          </div>

          <div class="mt-6">
            <label for="total_value" class="block text-sm font-medium text-gray-700 mb-2">
              Total Value ($)
            </label>
            <input
              id="total_value"
              v-model="form.total_value"
              type="number"
              step="0.01"
              min="0"
              readonly
              class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-50"
              placeholder="Calculated automatically"
            />
            <p class="mt-1 text-sm text-gray-500">
              Calculated based on yield and price per kg
            </p>
          </div>
        </div>

        <!-- Additional Information -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Additional Information</h3>
          
          <div>
            <label for="harvest_method" class="block text-sm font-medium text-gray-700 mb-2">
              Harvest Method
            </label>
            <select
              id="harvest_method"
              v-model="form.harvest_method"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
            >
              <option value="">Select method</option>
              <option value="manual">Manual Harvesting</option>
              <option value="mechanical">Mechanical Harvesting</option>
              <option value="combine_harvester">Combine Harvester</option>
              <option value="sickle">Sickle</option>
            </select>
          </div>

          <div class="mt-6">
            <label for="weather_conditions" class="block text-sm font-medium text-gray-700 mb-2">
              Weather During Harvest
            </label>
            <select
              id="weather_conditions"
              v-model="form.weather_conditions"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
            >
              <option value="">Select conditions</option>
              <option value="clear">Clear/Sunny</option>
              <option value="cloudy">Cloudy</option>
              <option value="rainy">Rainy</option>
              <option value="windy">Windy</option>
            </select>
          </div>

          <div class="mt-6">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
              Harvest Notes
            </label>
            <textarea
              id="notes"
              v-model="form.notes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
              placeholder="Any additional notes about the harvest..."
            ></textarea>
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
        <div class="flex justify-end space-x-4">
          <router-link 
            to="/harvests"
            class="bg-gray-200 text-gray-700 px-6 py-2 rounded-lg hover:bg-gray-300 transition-colors"
          >
            Cancel
          </router-link>
          <button
            type="submit"
            :disabled="loading"
            class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center"
          >
            <svg v-if="loading" class="animate-spin h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ loading ? 'Recording Harvest...' : 'Record Harvest' }}
          </button>
        </div>
      </form>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useFarmStore } from '@/stores/farm';

const router = useRouter();
const farmStore = useFarmStore();

const loading = ref(false);
const error = ref('');
const plantings = ref([]);

const form = reactive({
  planting_id: '',
  harvest_date: '',
  yield: '',
  yield_per_hectare: '',
  quality_grade: '',
  moisture_content: '',
  broken_grains_percentage: '',
  price_per_kg: '',
  storage_location: '',
  total_value: '',
  harvest_method: '',
  weather_conditions: '',
  notes: ''
});

const availablePlantings = computed(() => {
  return plantings.value.filter(p => 
    ['growing', 'ready'].includes(p.status) && 
    new Date(p.expected_harvest_date) <= new Date(Date.now() + 30 * 24 * 60 * 60 * 1000) // Within next 30 days
  );
});

// Auto-calculate yield per hectare
watch([() => form.yield, () => form.planting_id], ([yield, plantingId]) => {
  if (yield && plantingId) {
    const planting = plantings.value.find(p => p.id === parseInt(plantingId));
    if (planting && planting.field?.size) {
      form.yield_per_hectare = (parseFloat(yield) / planting.field.size).toFixed(2);
    }
  }
});

// Auto-calculate total value
watch([() => form.yield, () => form.price_per_kg], ([yield, price]) => {
  if (yield && price) {
    form.total_value = (parseFloat(yield) * parseFloat(price)).toFixed(2);
  }
});

const submitHarvest = async () => {
  loading.value = true;
  error.value = '';

  try {
    await farmStore.createHarvest(form);
    router.push('/harvests');
  } catch (err) {
    error.value = err.message || 'Failed to record harvest. Please try again.';
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  try {
    await farmStore.fetchPlantings();
    plantings.value = farmStore.plantings;
    
    // Set default harvest date to today
    form.harvest_date = new Date().toISOString().split('T')[0];
  } catch (error) {
    console.error('Failed to load plantings:', error);
  }
});
</script>
