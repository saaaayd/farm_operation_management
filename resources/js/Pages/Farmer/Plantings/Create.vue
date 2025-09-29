<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <router-link to="/plantings" class="text-gray-500 hover:text-gray-700 mr-4">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </router-link>
            <div>
              <h1 class="text-xl font-semibold text-gray-900">Create New Rice Planting</h1>
              <p class="text-sm text-gray-500">Add a new rice planting to track growth and yield</p>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <form @submit.prevent="submitPlanting" class="space-y-8">
        <!-- Field Selection -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Field Selection</h3>
          
          <div>
            <label for="field_id" class="block text-sm font-medium text-gray-700 mb-2">
              Select Field *
            </label>
            <select
              id="field_id"
              v-model="form.field_id"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
            >
              <option value="">Choose a field</option>
              <option v-for="field in fields" :key="field.id" :value="field.id">
                {{ field.name }} - {{ field.size }} hectares ({{ field.soil_type }})
              </option>
            </select>
            <p class="mt-1 text-sm text-gray-500">
              Select the field where you want to plant rice
            </p>
          </div>
        </div>

        <!-- Rice Variety -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Rice Variety</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="crop_type" class="block text-sm font-medium text-gray-700 mb-2">
                Rice Variety *
              </label>
              <select
                id="crop_type"
                v-model="form.crop_type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
              >
                <option value="">Select rice variety</option>
                <option value="IR64">IR64 - High yielding, 120-130 days</option>
                <option value="Jasmine">Jasmine Rice - Aromatic, 110-120 days</option>
                <option value="Basmati">Basmati Rice - Long grain, 130-140 days</option>
                <option value="Arborio">Arborio Rice - Short grain, 100-110 days</option>
                <option value="Brown Rice">Brown Rice - Whole grain, 120-130 days</option>
                <option value="Sticky Rice">Sticky Rice - Glutinous, 100-110 days</option>
                <option value="Wild Rice">Wild Rice - Native variety, 140-150 days</option>
              </select>
            </div>

            <div>
              <label for="growth_duration" class="block text-sm font-medium text-gray-700 mb-2">
                Growth Duration (days)
              </label>
              <input
                id="growth_duration"
                v-model="form.growth_duration"
                type="number"
                min="80"
                max="200"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                placeholder="120"
              />
              <p class="mt-1 text-sm text-gray-500">
                Typical growth duration for this variety
              </p>
            </div>
          </div>
        </div>

        <!-- Planting Dates -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Planting Schedule</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="planting_date" class="block text-sm font-medium text-gray-700 mb-2">
                Planting Date *
              </label>
              <input
                id="planting_date"
                v-model="form.planting_date"
                type="date"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
              />
            </div>

            <div>
              <label for="transplanting_date" class="block text-sm font-medium text-gray-700 mb-2">
                Transplanting Date
              </label>
              <input
                id="transplanting_date"
                v-model="form.transplanting_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
              />
              <p class="mt-1 text-sm text-gray-500">
                Usually 15-30 days after planting
              </p>
            </div>
          </div>

          <div class="mt-6">
            <label for="expected_harvest_date" class="block text-sm font-medium text-gray-700 mb-2">
              Expected Harvest Date *
            </label>
            <input
              id="expected_harvest_date"
              v-model="form.expected_harvest_date"
              type="date"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
            />
            <p class="mt-1 text-sm text-gray-500">
              Calculated based on growth duration
            </p>
          </div>
        </div>

        <!-- Planting Details -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Planting Details</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="seed_quantity" class="block text-sm font-medium text-gray-700 mb-2">
                Seed Quantity (kg)
              </label>
              <input
                id="seed_quantity"
                v-model="form.seed_quantity"
                type="number"
                step="0.1"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                placeholder="25.0"
              />
            </div>

            <div>
              <label for="planting_method" class="block text-sm font-medium text-gray-700 mb-2">
                Planting Method
              </label>
              <select
                id="planting_method"
                v-model="form.planting_method"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
              >
                <option value="">Select method</option>
                <option value="direct_seeding">Direct Seeding</option>
                <option value="transplanting">Transplanting</option>
                <option value="broadcast">Broadcast Seeding</option>
                <option value="drill">Drill Seeding</option>
              </select>
            </div>
          </div>

          <div class="mt-6">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
              Additional Notes
            </label>
            <textarea
              id="notes"
              v-model="form.notes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
              placeholder="Any additional information about this planting..."
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
            to="/plantings"
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
            {{ loading ? 'Creating Planting...' : 'Create Planting' }}
          </button>
        </div>
      </form>
    </main>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useFarmStore } from '@/stores/farm';

const router = useRouter();
const farmStore = useFarmStore();

const loading = ref(false);
const error = ref('');
const fields = ref([]);

const form = reactive({
  field_id: '',
  crop_type: '',
  growth_duration: '',
  planting_date: '',
  transplanting_date: '',
  expected_harvest_date: '',
  seed_quantity: '',
  planting_method: '',
  notes: '',
  status: 'planted'
});

// Auto-calculate harvest date when planting date or growth duration changes
watch([() => form.planting_date, () => form.growth_duration], ([plantingDate, growthDuration]) => {
  if (plantingDate && growthDuration) {
    const planting = new Date(plantingDate);
    const harvest = new Date(planting);
    harvest.setDate(harvest.getDate() + parseInt(growthDuration));
    form.expected_harvest_date = harvest.toISOString().split('T')[0];
  }
});

// Auto-calculate transplanting date (15 days after planting)
watch(() => form.planting_date, (plantingDate) => {
  if (plantingDate) {
    const planting = new Date(plantingDate);
    const transplanting = new Date(planting);
    transplanting.setDate(transplanting.getDate() + 15);
    form.transplanting_date = transplanting.toISOString().split('T')[0];
  }
});

// Auto-set growth duration based on variety
watch(() => form.crop_type, (variety) => {
  const durations = {
    'IR64': 125,
    'Jasmine': 115,
    'Basmati': 135,
    'Arborio': 105,
    'Brown Rice': 125,
    'Sticky Rice': 105,
    'Wild Rice': 145
  };
  
  if (durations[variety]) {
    form.growth_duration = durations[variety];
  }
});

const submitPlanting = async () => {
  loading.value = true;
  error.value = '';

  try {
    await farmStore.createPlanting(form);
    router.push('/plantings');
  } catch (err) {
    error.value = err.message || 'Failed to create planting. Please try again.';
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  try {
    await farmStore.fetchFields();
    fields.value = farmStore.fields;
  } catch (error) {
    console.error('Failed to load fields:', error);
  }
});
</script>
