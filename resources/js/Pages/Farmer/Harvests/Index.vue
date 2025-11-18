<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-6 gap-4">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">Harvests</h1>
            <p class="text-sm text-gray-500">
              Log and manage all your crop yields.
            </p>
          </div>
          <div class="flex items-center gap-3">
            <button
              @click="refreshHarvests"
              :disabled="loading"
              class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              <svg
                :class="['h-4 w-4 mr-2', { 'animate-spin': loading }]"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"
                />
              </svg>
              Refresh
            </button>
            <button
              @click="openCreateModal"
              class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md bg-green-600 text-white hover:bg-green-700"
            >
              Add Harvest
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
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
              @click="refreshHarvests"
              class="mt-2 text-sm font-medium text-red-700 hover:text-red-800"
            >
              Try again
            </button>
          </div>
        </div>
      </div>

      <div v-else-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
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
        <div v-if="harvests.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
          <div class="text-5xl mb-4">🌾</div>
          <h2 class="text-lg font-semibold text-gray-900 mb-2">No harvests logged</h2>
          <p class="text-sm text-gray-600 mb-6">
            Log your first harvest to track yield and performance.
          </p>
          <button
            @click="openCreateModal"
            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md bg-green-600 text-white hover:bg-green-700"
          >
            Add Harvest
          </button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <article
            v-for="harvest in harvests"
            :key="harvest.id"
            class="bg-white rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <div class="h-full flex flex-col">
              <div class="flex items-start justify-between mb-4 pt-6 px-6">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">
                    {{ harvest.quantity }} {{ harvest.unit }}
                  </h3>
                  <p class="text-xs text-gray-500">
                    From: {{ harvest.planting?.field?.name || 'N/A' }}
                  </p>
                </div>
                <span
                  v-if="harvest.quality_grade"
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="qualityClass(harvest.quality_grade)"
                >
                  Grade {{ harvest.quality_grade }}
                </span>
              </div>

              <dl class="grid grid-cols-2 gap-y-2 text-sm text-gray-600 mb-4 px-6">
                <div>
                  <dt class="font-medium text-gray-500">Harvest Date</dt>
                  <dd class="text-gray-900 font-semibold">
                    {{ formatDate(harvest.harvest_date) }}
                  </dd>
                </div>
                <div>
                  <dt class="font-medium text-gray-500">Crop</dt>
                  <dd>{{ harvest.planting?.crop_type || 'N/A' }}</dd>
                </div>
                <div>
                  <dt class="font-medium text-gray-500">Total Value</dt>
                  <dd>{{ formatCurrency(harvest.total_value) }}</dd>
                </div>
                <div>
                  <dt class="font-medium text-gray-500">Price/Unit</dt>
                  <dd>{{ formatCurrency(harvest.price_per_unit) }}</dd>
                </div>
              </dl>

              <div class="mt-auto border-t border-gray-200">
                <div class="flex divide-x divide-gray-200">
                  <button
                    @click="openEditModal(harvest)"
                    class="flex-1 inline-flex items-center justify-center py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-bl-lg"
                  >
                    <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L13.196 5.232z" />
                    </svg>
                    <span class="ml-2">Edit</span>
                  </button>
                  <button
                    @click="confirmDelete(harvest)"
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
    </main>

    <HarvestFormModal
      :show="isModalOpen"
      :harvest="selectedHarvest"
      @close="closeModal"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFarmStore } from '@/stores/farm'
import HarvestFormModal from './HarvestFormModal.vue' // Import the new modal

const router = useRouter()
const farmStore = useFarmStore()

const loading = ref(true)
const error = ref('')
const isModalOpen = ref(false)
const selectedHarvest = ref(null)

const harvests = computed(() => farmStore.harvests || [])

// --- Modal Controls ---
const openCreateModal = () => {
  selectedHarvest.value = null
  isModalOpen.value = true
}

const openEditModal = (harvest) => {
  selectedHarvest.value = { ...harvest } // Pass a copy
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  selectedHarvest.value = null
}

// --- Data Fetching ---
const refreshHarvests = async () => {
  loading.value = true
  error.value = ''
  try {
    await farmStore.fetchHarvests()
  } catch (err) {
    console.error('Failed to load harvests:', err)
    error.value = err.userMessage || err.response?.data?.message || 'Unable to load harvests.'
  } finally {
    loading.value = false
  }
}

// --- CRUD Actions ---
const confirmDelete = async (harvest) => {
  const harvestName = `${harvest.quantity} ${harvest.unit}`
  if (window.confirm(`Are you sure you want to delete this harvest of "${harvestName}"? This cannot be undone.`)) {
    try {
      await farmStore.deleteHarvest(harvest.id)
      // Store action will optimistically remove it from the list
    } catch (err) {
      console.error('Failed to delete harvest:', err)
      error.value = err.userMessage || err.response?.data?.message || 'Unable to delete harvest.'
    }
  }
}

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

const formatCurrency = (value) => {
  if (value === null || value === undefined || value === '') return 'N/A'
  const num = Number(value)
  if (Number.isNaN(num)) return 'Invalid'
  return num.toLocaleString('en-US', { style: 'currency', currency: 'PHP' }) // Assuming PHP
}

const qualityClass = (grade) => {
  const classes = {
    A: 'bg-green-100 text-green-800',
    B: 'bg-blue-100 text-blue-800',
    C: 'bg-yellow-100 text-yellow-800',
    D: 'bg-red-100 text-red-800',
  }
  return classes[grade] || 'bg-gray-100 text-gray-800'
}

// --- Lifecycle ---
onMounted(() => {
  // Fetch harvests
  if (farmStore.harvests.length === 0) {
    refreshHarvests()
  } else {
    loading.value = false
  }
  
  // Also fetch plantings in the background if they aren't loaded
  // as they are needed for the create/edit modal dropdown
  if (farmStore.plantings.length === 0) {
    farmStore.fetchPlantings().catch(err => console.warn('BG fetch plantings failed', err))
  }
})
</script>