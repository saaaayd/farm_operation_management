<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50">
    <header class="bg-white/80 backdrop-blur-sm shadow-sm border-b border-gray-200 sticky top-0 z-10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-6 gap-4">
          <div>
            <div class="flex items-center gap-3">
              <div class="h-10 w-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-lg">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                </svg>
              </div>
              <div>
                <h1 class="text-2xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">Harvests</h1>
                <p class="text-sm text-gray-600 mt-0.5">
                  Log and manage all your crop yields and performance.
                </p>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <button
              @click="refreshHarvests"
              :disabled="loading"
              class="inline-flex items-center px-4 py-2.5 text-sm font-medium rounded-lg border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50 transition-all duration-200 shadow-sm hover:shadow"
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
              class="inline-flex items-center px-5 py-2.5 text-sm font-semibold rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 text-white hover:from-green-700 hover:to-emerald-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
            >
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
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
        <div v-if="harvests.length === 0" class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-12 text-center border border-gray-100">
          <div class="inline-flex items-center justify-center h-20 w-20 bg-gradient-to-br from-green-100 to-emerald-100 rounded-2xl mb-6">
            <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">No harvests logged</h2>
          <p class="text-sm text-gray-600 mb-8 max-w-md mx-auto">
            Log your first harvest to track yield, quality, and financial performance.
          </p>
          <button
            @click="openCreateModal"
            class="inline-flex items-center px-6 py-3 text-sm font-semibold rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 text-white hover:from-green-700 hover:to-emerald-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
          >
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Log Your First Harvest
          </button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <article
            v-for="harvest in harvests"
            :key="harvest.id"
            class="group bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100 overflow-hidden"
          >
            <div class="h-full flex flex-col">
              <!-- Header -->
              <div class="p-6 pb-4">
                <div class="flex items-start justify-between mb-4">
                  <div class="flex-1">
                    <div class="flex items-center gap-2 mb-2">
                      <div class="h-10 w-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-md">
                        <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                        </svg>
                      </div>
                      <div>
                        <h3 class="text-xl font-bold text-gray-900 group-hover:text-green-700 transition-colors">
                          {{ harvest.quantity || harvest.yield || 0 }} {{ harvest.unit || 'kg' }}
                        </h3>
                        <div class="flex items-center text-xs text-gray-500 mt-1">
                          <svg class="h-3 w-3 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                          </svg>
                          {{ harvest.planting?.field?.name || 'Unknown Field' }}
                        </div>
                      </div>
                    </div>
                  </div>
                  <span
                    v-if="harvest.quality_grade"
                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold shadow-sm"
                    :class="qualityClass(harvest.quality_grade)"
                  >
                    Grade {{ harvest.quality_grade }}
                  </span>
                </div>

                <!-- Crop Variety -->
                <div v-if="harvest.planting?.riceVariety" class="mb-4 p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200">
                  <div class="flex items-center gap-2">
                    <div class="h-6 w-6 bg-green-500 rounded-lg flex items-center justify-center">
                      <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                      </svg>
                    </div>
                    <div class="flex-1">
                      <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Variety</p>
                      <p class="text-sm font-bold text-green-700">{{ harvest.planting.riceVariety.name }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Details Grid -->
              <dl class="grid grid-cols-2 gap-4 px-6 mb-4">
                <div class="flex items-start gap-2">
                  <div class="mt-0.5">
                    <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Date</dt>
                    <dd class="text-sm font-semibold text-gray-900 mt-0.5">
                      {{ formatDate(harvest.harvest_date) }}
                    </dd>
                  </div>
                </div>
                <div class="flex items-start gap-2">
                  <div class="mt-0.5">
                    <svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Crop</dt>
                    <dd class="text-sm font-semibold text-gray-700 mt-0.5 truncate">
                      {{ harvest.planting?.crop_type || harvest.planting?.riceVariety?.name || 'Rice' }}
                    </dd>
                  </div>
                </div>
                <div v-if="harvest.total_value" class="flex items-start gap-2 col-span-2">
                  <div class="mt-0.5">
                    <svg class="h-4 w-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Total Value</dt>
                    <dd class="text-lg font-bold text-emerald-700 mt-0.5">{{ formatCurrency(harvest.total_value) }}</dd>
                  </div>
                </div>
                <div v-if="harvest.price_per_unit" class="flex items-start gap-2">
                  <div class="mt-0.5">
                    <svg class="h-4 w-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Price/Unit</dt>
                    <dd class="text-sm font-semibold text-gray-700 mt-0.5">{{ formatCurrency(harvest.price_per_unit) }}</dd>
                  </div>
                </div>
              </dl>

              <!-- Footer Actions -->
              <div class="mt-auto pt-4 border-t border-gray-200">
                <div class="flex divide-x divide-gray-200">
                  <button
                    @click="openEditModal(harvest)"
                    class="flex-1 inline-flex items-center justify-center py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-bl-2xl transition-colors"
                  >
                    <svg class="h-4 w-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L13.196 5.232z" />
                    </svg>
                    Edit
                  </button>
                  <button
                    @click="confirmDelete(harvest)"
                    class="flex-1 inline-flex items-center justify-center py-3 text-sm font-medium text-red-600 hover:bg-red-50 rounded-br-2xl transition-colors"
                  >
                    <svg class="h-4 w-4 mr-2 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Delete
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
  // Always refresh harvests to get latest data
  refreshHarvests()
  
  // Also fetch plantings in the background if they aren't loaded
  // as they are needed for the create/edit modal dropdown
  if (farmStore.plantings.length === 0) {
    farmStore.fetchPlantings().catch(err => console.warn('BG fetch plantings failed', err))
  }
})
</script>