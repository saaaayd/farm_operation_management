<template>
  <div class="min-h-screen bg-gray-100">
    <div class="max-w-5xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-semibold text-gray-900">Edit Planting</h1>
          <p class="text-sm text-gray-600">Update planting details and timelines.</p>
        </div>
        <button
          @click="goBack"
          class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
        >
          Cancel
        </button>
      </div>

      <div v-if="loading" class="bg-white shadow rounded-lg p-6 flex items-center justify-center">
        <svg class="animate-spin h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <div v-else class="bg-white shadow rounded-lg">
        <form @submit.prevent="submitForm" class="space-y-8 p-6">
          <div v-if="error" class="rounded-md bg-red-50 p-4">
            <div class="flex">
              <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M18 10A8 8 0 11.001 10 8 8 0 0118 10zm-8-3a1 1 0 00-.894.553L8 9.118V13a1 1 0 001 1h2a1 1 0 001-1V9.118l-.106-.565A1 1 0 0010 7z" clip-rule="evenodd" />
                </svg>
              </div>
              <div class="ml-3">
                <h3 class="text-sm font-medium text-red-800">Unable to save changes</h3>
                <p class="mt-2 text-sm text-red-700">{{ error }}</p>
              </div>
            </div>
          </div>

          <!-- Field & Variety -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label for="field_id" class="block text-sm font-medium text-gray-700 mb-2">
                Field *
              </label>
              <select
                id="field_id"
                v-model="form.field_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                required
              >
                <option value="">Select field</option>
                <option v-for="field in fields" :key="field.id" :value="field.id">
                  {{ field.name }} ({{ field.size }} ha)
                </option>
              </select>
            </div>

            <div>
              <label for="rice_variety_id" class="block text-sm font-medium text-gray-700 mb-2">
                Rice Variety *
              </label>
              <select
                id="rice_variety_id"
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
                  <span v-if="variety.maturity_days"> — {{ variety.maturity_days }} days</span>
                </option>
              </select>
            </div>
          </div>

          <!-- Crop Information -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
              <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                Status
              </label>
              <select
                id="status"
                v-model="form.status"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
              >
                <option value="planned">Planned</option>
                <option value="planted">Planted</option>
                <option value="growing">Growing</option>
                <option value="ready">Ready</option>
                <option value="harvested">Harvested</option>
                <option value="failed">Failed</option>
              </select>
            </div>
          </div>

          <!-- Planting Schedule -->
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
                Adjusted automatically when growth duration changes.
              </p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
                <option value="transplanting">Transplanting</option>
                <option value="direct_seeding">Direct Seeding</option>
                <option value="broadcasting">Broadcasting</option>
              </select>
            </div>
          </div>

          <!-- Metrics -->
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <label for="seed_rate" class="block text-sm font-medium text-gray-700 mb-2">
                Seed Rate (kg/ha)
              </label>
              <input
                id="seed_rate"
                v-model="form.seed_rate"
                type="number"
                min="0"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                placeholder="60"
              />
            </div>

            <div>
              <label for="area_planted" class="block text-sm font-medium text-gray-700 mb-2">
                Area Planted (ha) *
              </label>
              <input
                id="area_planted"
                v-model="form.area_planted"
                type="number"
                min="0.1"
                step="0.01"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
                placeholder="1.5"
              />
            </div>

            <div>
              <label for="season" class="block text-sm font-medium text-gray-700 mb-2">
                Season
              </label>
              <select
                id="season"
                v-model="form.season"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
              >
                <option value="wet">Wet Season</option>
                <option value="dry">Dry Season</option>
              </select>
            </div>
          </div>

          <!-- Notes -->
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
              Notes
            </label>
            <textarea
              id="notes"
              v-model="form.notes"
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
              placeholder="Additional information about this planting..."
            ></textarea>
          </div>

          <div class="flex justify-end">
            <button
              type="submit"
              :disabled="saving"
              class="inline-flex items-center px-6 py-3 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-60"
            >
              <svg
                v-if="saving"
                class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                ></circle>
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
              </svg>
              {{ saving ? 'Saving...' : 'Save Changes' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, watch, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useFarmStore } from '@/stores/farm';
import { riceVarietiesAPI, plantingsAPI } from '@/services/api';

const route = useRoute();
const router = useRouter();
const farmStore = useFarmStore();

const plantingId = route.params.id;

const loading = ref(true);
const saving = ref(false);
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
    if (!form.growth_duration && variety.maturity_days) {
      form.growth_duration = variety.maturity_days;
    }
  }
});

const goBack = () => {
  router.push('/plantings');
};

const loadPlanting = async () => {
  try {
    const response = await plantingsAPI.getById(plantingId);
    const planting = response.data?.planting || response.data;

    if (!planting) {
      throw new Error('Planting not found');
    }

    form.field_id = planting.field_id ?? '';
    form.rice_variety_id = planting.rice_variety_id ?? '';
    form.crop_type = planting.crop_type || '';
    form.planting_method = planting.planting_method || 'transplanting';
    form.status = planting.status || 'planted';
    form.notes = planting.notes || '';

    form.planting_date = planting.planting_date
      ? new Date(planting.planting_date).toISOString().split('T')[0]
      : '';
    form.expected_harvest_date = planting.expected_harvest_date
      ? new Date(planting.expected_harvest_date).toISOString().split('T')[0]
      : '';

    if (form.planting_date && form.expected_harvest_date) {
      const start = new Date(form.planting_date);
      const end = new Date(form.expected_harvest_date);
      const diff = Math.round((end - start) / (1000 * 60 * 60 * 24));
      form.growth_duration = diff > 0 ? diff : '';
    } else {
      form.growth_duration = '';
    }

    form.seed_rate = planting.seed_rate ?? '';
    form.area_planted = planting.area_planted ?? '';
    form.season = planting.season || (form.planting_date ? determineSeason(form.planting_date) : 'wet');
  } catch (err) {
    console.error('Failed to load planting:', err);
    throw err;
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

const submitForm = async () => {
  saving.value = true;
  error.value = '';

  try {
    const payload = {
      field_id: form.field_id ? Number(form.field_id) : undefined,
      rice_variety_id: form.rice_variety_id ? Number(form.rice_variety_id) : undefined,
      crop_type: form.crop_type || 'Rice',
      planting_date: form.planting_date || null,
      expected_harvest_date: form.expected_harvest_date || null,
      growth_duration: form.growth_duration ? Number(form.growth_duration) : null,
      planting_method: form.planting_method || 'transplanting',
      seed_rate: form.seed_rate ? Number(form.seed_rate) : null,
      area_planted: form.area_planted ? Number(form.area_planted) : null,
      season: form.season || (form.planting_date ? determineSeason(form.planting_date) : null),
      status: form.status || 'planned',
      notes: form.notes || null,
    };

    if (!payload.expected_harvest_date && payload.growth_duration && payload.planting_date) {
      const planting = new Date(payload.planting_date);
      const harvest = new Date(planting);
      harvest.setDate(harvest.getDate() + payload.growth_duration);
      payload.expected_harvest_date = harvest.toISOString().split('T')[0];
    }

    await farmStore.updatePlanting(plantingId, payload);

    router.push(`/plantings/${plantingId}`);
  } catch (err) {
    console.error('Failed to update planting:', err);
    error.value = err.response?.data?.message || err.message || 'Failed to update planting. Please try again.';
  } finally {
    saving.value = false;
  }
};

onMounted(async () => {
  try {
    await Promise.all([
      farmStore.fetchFields(),
      fetchRiceVarieties(),
    ]);

    fields.value = farmStore.fields;

    await loadPlanting();
  } catch (err) {
    error.value = err.response?.data?.message || err.message || 'Unable to load planting details.';
  } finally {
    loading.value = false;
  }
});
</script>

