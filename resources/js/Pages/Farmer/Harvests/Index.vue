<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-6 gap-4">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">Harvests</h1>
            <p class="text-sm text-gray-500">
              Monitor yields, quality metrics, and storage plans for each harvest.
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
              @click="goToCreate"
              class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md bg-green-600 text-white hover:bg-green-700"
            >
              Record Harvest
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx_auto px-4 sm:px-6 lg:px-8 py-8">
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

      <div v-else-if="loading" class="space-y-4">
        <div
          v-for="n in 5"
          :key="n"
          class="bg-white rounded-lg shadow p-6 animate-pulse space-y-3"
        >
          <div class="h-4 bg-gray-200 rounded w-1/3"></div>
          <div class="h-3 bg-gray-200 rounded"></div>
          <div class="h-3 bg-gray-200 rounded w-2/3"></div>
        </div>
      </div>

      <div v-else>
        <div v-if="harvests.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
          <div class="text-5xl mb-4">🌾</div>
          <h2 class="text-lg font-semibold text-gray-900 mb-2">No harvests recorded yet</h2>
          <p class="text-sm text-gray-600 mb-6">
            Record your first harvest to track yield, moisture, and quality metrics.
          </p>
          <button
            @click="goToCreate"
            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md bg-green-600 text-white hover:bg-green-700"
          >
            Record Harvest
          </button>
        </div>

        <div v-else class="bg-white rounded-lg shadow overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harvest Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Field</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Crop</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Yield (kg)</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Moisture (%)</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price / kg</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="harvest in harvests"
                  :key="harvest.id"
                  class="hover:bg-gray-50 transition-colors"
                >
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(harvest.harvest_date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    {{ harvest.planting?.field?.name || '—' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    {{ harvest.planting?.crop_type || harvest.crop_type || '—' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                    {{ formatNumber(harvest.yield) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">
                    {{ formatNumber(harvest.moisture_content) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700 text-right">
                    {{ formatCurrency(harvest.price_per_kg) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">
                    {{ formatCurrency(harvest.total_value) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFarmStore } from '@/stores/farm'

const router = useRouter()
const farmStore = useFarmStore()

const loading = ref(true)
const error = ref('')

const harvests = computed(() => farmStore.harvests || [])

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

const goToCreate = () => {
  router.push('/harvests/create')
}

const formatDate = (value) => {
  if (!value) return '—'
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? '—' : date.toLocaleDateString()
}

const formatNumber = (value) => {
  const num = Number(value)
  if (Number.isNaN(num)) return value ?? '—'
  return num.toLocaleString(undefined, { minimumFractionDigits: 0, maximumFractionDigits: 2 })
}

const formatCurrency = (value) => {
  const num = Number(value)
  if (Number.isNaN(num)) return value ? `₱${value}` : '—'
  return `₱${num.toFixed(2)}`
}

onMounted(() => {
  if (!harvests.value.length) {
    refreshHarvests()
  } else {
    loading.value = false
  }
})
</script>


