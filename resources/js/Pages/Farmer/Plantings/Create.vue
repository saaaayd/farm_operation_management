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

        <!-- Crop Details -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Crop Details</h3>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Rice Variety *
              </label>
              <template v-if="riceVarieties.length">
                <select
                  v-model="form.rice_variety_id"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                  required
                >
                  <option value="">Select variety</option>
                  <option
                    v-for="variety in riceVarieties"
                    :key="variety.id"
                    :value="variety.id"
                  >
                    {{ variety.name }}
                    <span v-if="variety.average_maturity_days">
                      — {{ variety.average_maturity_days }} days
                    </span>
                  </option>
                </select>
              </template>
              <template v-else>
                <div class="rounded-md border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-sm text-gray-600">
                  No rice varieties available. Add varieties under Marketplace settings.
                </div>
              </template>
            </div>

            <div>
              <label for="crop_type" class="block text-sm font-medium text-gray-700 mb-2">
                Crop Name *
              </label>
              <input
                id="crop_type"
                v-model="form.crop_type"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                placeholder="e.g. Rice (IR64)"
              />
            </div>

            <div>
              <label for="growth_duration" class="block text-sm font-medium text-gray-700 mb-2">
                Growth Duration (days)
              </label>
              <input
                id="growth_duration"
                v-model="form.growth_duration"
                type="number"
                min="30"
                max="240"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                placeholder="120"
              />
              <p class="mt-1 text-sm text-gray-500">
                Used to estimate harvest date when not provided.
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
              <label for="seed_rate" class="block text-sm font-medium text-gray-700 mb-2">
                Seed Rate (kg/ha)
              </label>
              <input
                id="seed_rate"
                v-model="form.seed_rate"
                type="number"
                step="0.1"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                placeholder="e.g. 25"
              />
            </div>

            <div>
              <label for="area_planted" class="block text-sm font-medium text-gray-700 mb-2">
                Area Planted (hectares) *
              </label>
              <input
                id="area_planted"
                v-model="form.area_planted"
                type="number"
                step="0.1"
                min="0"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
              />
            </div>

            <div>
              <label for="season" class="block text-sm font-medium text-gray-700 mb-2">
                Season *
              </label>
              <select
                id="season"
                v-model="form.season"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
              >
                <option value="wet">Wet Season</option>
                <option value="dry">Dry Season</option>
              </select>
            </div>

            <div>
              <label for="planting_method" class="block text-sm font-medium text-gray-700 mb-2">
                Planting Method *
              </label>
              <select
                id="planting_method"
                v-model="form.planting_method"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
              >
                <option value="direct_seeding">Direct Seeding</option>
                <option value="transplanting">Transplanting</option>
                <option value="broadcasting">Broadcasting</option>
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
import { ref, reactive, onMounted, watch, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useFarmStore } from '@/stores/farm';
import { riceVarietiesAPI } from '@/services/api';

const router = useRouter();
const farmStore = useFarmStore();

const loading = ref(false);
const error = ref('');
const fields = ref([]);
const riceVarieties = ref([]);

const form = reactive({
  field_id: '',
  rice_variety_id: '',
  crop_type: '',
  growth_duration: '',
  planting_date: '',
  expected_harvest_date: '',
  seed_rate: '',
  area_planted: '',
  planting_method: 'transplanting',
  season: '',
  notes: '',
  status: 'planned'
});

const selectedField = computed(() =>
  fields.value.find(field => Number(field.id) === Number(form.field_id))
);

function determineSeason(dateString) {
  const date = new Date(dateString);
  if (Number.isNaN(date.getTime())) {
    return 'wet';
  }
  const month = date.getMonth() + 1;
  return month >= 5 && month <= 10 ? 'wet' : 'dry';
}

watch([() => form.planting_date, () => form.growth_duration], ([plantingDate, growthDuration]) => {
  if (plantingDate && growthDuration) {
    const planting = new Date(plantingDate);
    const harvest = new Date(planting);
    harvest.setDate(harvest.getDate() + parseInt(growthDuration, 10));
    form.expected_harvest_date = harvest.toISOString().split('T')[0];
  }
});

watch(() => form.planting_date, (plantingDate) => {
  if (plantingDate) {
    form.season = determineSeason(plantingDate);
    const today = new Date();
    const selected = new Date(plantingDate);
    if (!Number.isNaN(selected.getTime())) {
      if (selected > today && form.status === 'planted') {
        form.status = 'planned';
      } else if (selected <= today && form.status === 'planned') {
        form.status = 'planted';
      }
    }
  }
});

watch(() => form.field_id, () => {
  if (selectedField.value?.size) {
    form.area_planted = selectedField.value.size;
  }
});

watch(() => form.rice_variety_id, (id) => {
  const variety = riceVarieties.value.find(v => Number(v.id) === Number(id));
  if (variety) {
    if (!form.crop_type) {
      form.crop_type = variety.name;
    }
    if (!form.growth_duration && variety.average_maturity_days) {
      form.growth_duration = variety.average_maturity_days;
    }
  }
});

const submitPlanting = async () => {
  loading.value = true;
  error.value = '';

  try {
    const payload = {
      field_id: Number(form.field_id),
      rice_variety_id: form.rice_variety_id ? Number(form.rice_variety_id) : null,
      crop_type: form.crop_type || 'Rice',
      planting_date: form.planting_date,
      expected_harvest_date: form.expected_harvest_date || null,
      growth_duration: form.growth_duration ? Number(form.growth_duration) : null,
      planting_method: form.planting_method || 'transplanting',
      seed_rate: form.seed_rate ? Number(form.seed_rate) : null,
      area_planted: form.area_planted ? Number(form.area_planted) : (selectedField.value?.size ?? null),
      season: form.season || determineSeason(form.planting_date),
      status: form.status || 'planned',
      notes: form.notes || null,
    };

    if (!payload.expected_harvest_date && payload.growth_duration && payload.planting_date) {
      const planting = new Date(payload.planting_date);
      const harvest = new Date(planting);
      harvest.setDate(harvest.getDate() + payload.growth_duration);
      payload.expected_harvest_date = harvest.toISOString().split('T')[0];
    }

    if (!payload.area_planted || payload.area_planted <= 0) {
      payload.area_planted = selectedField.value?.size ?? 1;
    }

    if (!payload.rice_variety_id && riceVarieties.value.length) {
      payload.rice_variety_id = riceVarieties.value[0].id;
    }

    await farmStore.createPlanting(payload);
    router.push('/plantings');
  } catch (err) {
    error.value = err.message || 'Failed to create planting. Please try again.';
  } finally {
    loading.value = false;
  }
};

const fetchRiceVarieties = async () => {
  try {
    const response = await riceVarietiesAPI.getAll();
    const payload = response?.data;
    const varieties = payload?.data || payload?.varieties || payload || [];
    riceVarieties.value = Array.isArray(varieties) ? varieties : [];
  } catch (err) {
    console.error('Failed to load rice varieties:', err);
    riceVarieties.value = [];
  }
};

onMounted(async () => {
  try {
    await Promise.all([
      farmStore.fetchFields(),
      fetchRiceVarieties(),
    ]);

    fields.value = farmStore.fields;

    if (fields.value.length && !form.field_id) {
      form.field_id = fields.value[0].id;
      form.area_planted = fields.value[0].size ?? '';
    }

    if (riceVarieties.value.length && !form.rice_variety_id) {
      const variety = riceVarieties.value[0];
      form.rice_variety_id = variety.id;
      form.crop_type = variety.name;
      if (variety.average_maturity_days) {
        form.growth_duration = variety.average_maturity_days;
      }
    }

    if (form.planting_date) {
      form.season = determineSeason(form.planting_date);
    }
  } catch (error) {
    console.error('Failed to load planting prerequisites:', error);
  }
});
</script>
