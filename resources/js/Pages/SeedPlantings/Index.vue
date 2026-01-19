<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Standard Header -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Nursery</h1>
          <p class="text-gray-500 mt-1">Manage all your seed sowings, from sowing to transplanting.</p>
        </div>
        <div class="flex items-center gap-3">
          <button
            @click="refreshPlantings"
            :disabled="loading"
            class="flex items-center gap-2 bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm font-medium disabled:opacity-50"
          >
            <svg
              :class="['h-5 w-5', { 'animate-spin': loading }]"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
          </button>
          <button
            @click="goToCreate"
            class="flex items-center gap-2 bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 transition-colors shadow-sm font-medium"
          >
            <span class="text-xl leading-none">+</span> New Sowing
          </button>
        </div>
      </div>

      <div>
      <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-700">{{ error }}</p>
            <button
              @click="refreshPlantings"
              class="mt-2 text-sm font-medium text-red-700 hover:text-red-800"
            >
              Try Again
            </button>
          </div>
        </div>
      </div>

      <div class="bg-white p-4 rounded-lg shadow mb-6 flex flex-col md:flex-row gap-4 items-end" v-if="!loading && (seedPlantings.length > 0 || filters.status || filters.variety)">
          <div class="flex-1 w-full md:w-auto">
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select 
              v-model="filters.status" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
            >
              <option value="">All Statuses</option>
              <option value="sown">Sown</option>
              <option value="germinating">Germinating</option>
              <option value="ready">Ready to Transplant</option>
              <option value="transplanted">Transplanted</option>
              <option value="failed">Failed</option>
            </select>
          </div>
          
          <div class="flex-1 w-full md:w-auto">
            <label class="block text-sm font-medium text-gray-700 mb-2">Variety</label>
            <select 
              v-model="filters.variety" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
            >
              <option :value="null">All Varieties</option>
              <option 
                v-for="option in varietyOptions" 
                :key="option.key" 
                :value="option"
              >
                {{ option.label }}
              </option>
            </select>
          </div>
          
          <div class="flex items-end">
            <button 
              @click="clearFilters"
              class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors"
            >
              Clear Filters
            </button>
          </div>
        </div>

      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="n in 6"
          :key="n"
          class="bg-white rounded-lg shadow p-6 animate-pulse space-y-4"
        >
          <div class="h-6 bg-gray-200 rounded"></div>
          <div class="space-y-2">
            <div class="h-3 bg-gray-200 rounded"></div>
            <div class="h-3 bg-gray-200 rounded w-3/4"></div>
            <div class="h-3 bg-gray-200 rounded w-2/4"></div>
          </div>
          <div class="h-10 bg-gray-200 rounded"></div>
        </div>
      </div>

      <div v-else>
        <div v-if="filteredPlantings.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
          <div class="text-5xl mb-4">🌱</div>
          <h2 class="text-lg font-semibold text-gray-900 mb-2">No sowings found</h2>
          <p class="text-sm text-gray-600 mb-6">
            Get started by recording your first seed sowing.
          </p>
          <button
            @click="goToCreate"
            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md bg-green-600 text-white hover:bg-green-700"
          >
            New Sowing
          </button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <article
            v-for="planting in filteredPlantings"
            :key="planting.id"
            class="bg-white rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <div class="h-full flex flex-col">
              <div class="flex items-start justify-between mb-4 pt-6 px-6">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">
                     {{ planting.rice_variety?.name || 'Unknown Variety' }}
                  </h3>
                  <p class="text-xs text-gray-500" v-if="planting.batch_id">
                    Batch: {{ planting.batch_id }}
                  </p>
                </div>
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="statusClass(planting.status)"
                >
                  {{ formatStatus(planting.status) }}
                </span>
              </div>

              <dl class="grid grid-cols-2 gap-y-2 text-sm text-gray-600 mb-4 px-6">
                 <div>
                  <dt class="font-medium text-gray-500">Quantity</dt>
                  <dd class="text-gray-900 font-semibold">
                    {{ planting.quantity }} {{ planting.unit || 'kg' }}
                  </dd>
                </div>
                <div>
                  <dt class="font-medium text-gray-500">Sown On</dt>
                  <dd>{{ formatDate(planting.planting_date) }}</dd>
                </div>
                <div class="col-span-2">
                  <dt class="font-medium text-gray-500">Est. Transplant</dt>
                  <dd>{{ formatDate(planting.expected_transplant_date) }}</dd>
                </div>
              </dl>

              <div class="mt-auto border-t border-gray-200">
                <div class="flex divide-x divide-gray-200">
                  <button
                    @click="goToDetails(planting.id)"
                    class="flex-1 inline-flex items-center justify-center py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-bl-lg"
                  >
                    <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" >
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span class="ml-2">Details</span>
                  </button>
                  <button
                    @click="goToEdit(planting.id)"
                    class="flex-1 inline-flex items-center justify-center py-3 text-sm font-medium text-gray-700 hover:bg-gray-50"
                  >
                    <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L13.196 5.232z" />
                    </svg>
                    <span class="ml-2">Edit</span>
                  </button>
                  <button
                    @click="confirmDelete(planting)"
                    class="flex-1 inline-flex items-center justify-center py-3 text-sm font-medium text-red-600 hover:bg-red-50 rounded-br-lg"
                  >
                    <svg class="h-4 w-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span class="ml-2">Delete</span>
                  </button>
                </div>
              </div>
            </div>
          </article>
        </div>
      </div>
      </div>
      <!-- Confirmation Modal -->
      <ConfirmationModal
        :show="showConfirmModal"
        title="Delete Sowing"
        :message="`Are you sure you want to delete this sowing of ${plantingToDelete?.rice_variety?.name || 'unknown variety'}? This action cannot be undone.`"
        confirm-text="Delete"
        type="danger"
        @close="showConfirmModal = false"
        @confirm="deletePlanting"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFarmStore } from '@/stores/farm'
import ConfirmationModal from '@/Components/UI/ConfirmationModal.vue'

const router = useRouter()
const farmStore = useFarmStore()

const loading = ref(false);
const filters = ref({
  status: '',
  variety: null,
});
const error = ref(null);

// Confirmation State
const showConfirmModal = ref(false)
const plantingToDelete = ref(null)

const seedPlantings = computed(() => farmStore.seedPlantings);
const varietyOptions = computed(() => {
  const options = [];
  const seen = new Set();

  seedPlantings.value.forEach((planting) => {
    if (planting?.rice_variety) {
      const varietyId = planting.rice_variety.id;
      if (varietyId) {
        const key = `variety-${varietyId}`;
        if (!seen.has(key)) {
          options.push({
            key,
            label: planting.rice_variety.name,
            type: 'variety',
            id: varietyId,
          });
          seen.add(key);
        }
      }
    }
  });

  return options.sort((a, b) => a.label.localeCompare(b.label));
});

const filteredPlantings = computed(() => {
  let filtered = seedPlantings.value;

  if (filters.value.status) {
    filtered = filtered.filter(p => p.status === filters.value.status);
  }

  if (filters.value.variety) {
    const { id } = filters.value.variety;
    filtered = filtered.filter((planting) => {
        const plantingVarietyId = planting.rice_variety?.id;
        return plantingVarietyId && Number(plantingVarietyId) === Number(id);
    });
  }
  return filtered;
});

const statusClass = (status) => {
  const classes = {
    sown: 'bg-indigo-100 text-indigo-800',
    germinating: 'bg-yellow-100 text-yellow-800',
    ready: 'bg-green-100 text-green-800',
    transplanted: 'bg-blue-100 text-blue-800',
    failed: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}


const clearFilters = () => {
  filters.value = {
    status: '',
    variety: null,
  };
};

const refreshPlantings = async () => {
  loading.value = true;
  error.value = null;
  try {
    await farmStore.fetchSeedPlantings();
  } catch (err) {
    console.error('Failed to load seed plantings:', err);
    error.value = err.userMessage || 'Unable to load plantings.';
  } finally {
    loading.value = false;
  }
};

// --- Navigation ---
const goToCreate = () => {
  router.push('/seed-plantings/create');
};

const goToDetails = (id) => {
  router.push(`/seed-plantings/${id}`)
}

const goToEdit = (id) => {
  // Assuming edit route exists, otherwise functionality needs to be added or button hidden/disabled
  router.push(`/seed-plantings/${id}/edit`)
}

// --- CRUD Actions ---
const confirmDelete = (planting) => {
  plantingToDelete.value = planting;
  showConfirmModal.value = true;
};

const deletePlanting = async () => {
  if (!plantingToDelete.value) return;
  showConfirmModal.value = false;
  
  try {
    await farmStore.deleteSeedPlanting(plantingToDelete.value.id);
    plantingToDelete.value = null;
  } catch (err) { 
    console.error('Failed to delete planting:', err);
    error.value = err.userMessage || 'Unable to delete planting.';
  }
};

// --- Formatters ---
const formatDate = (value) => {
  if (!value) return 'N/A'
  try {
    const date = new Date(value)
    if (Number.isNaN(date.getTime())) return 'Invalid Date'
    return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })
  } catch (e) {
    return value
  }
}

const formatStatus = (status) => {
  if (!status) return 'Unknown'
  return status.charAt(0).toUpperCase() + status.slice(1).replace(/_/g, ' ')
}

// --- Lifecycle ---
onMounted(() => {
  // Always refresh to get latest status (or check if length is 0)
  refreshPlantings();
})
</script>
