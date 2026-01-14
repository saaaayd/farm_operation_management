<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Standard Header -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Rice Fields</h1>
          <p class="text-gray-500 mt-1">Track field boundaries, soil data, and readiness for planting.</p>
        </div>
        <div class="flex items-center gap-3">
          <button
             @click="openEditFarmModal"
             class="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition-colors shadow-sm font-medium"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
            </svg>
            Edit Farm Details
          </button>
          <button
            @click="refreshFields"
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
            @click="goToFieldSetup"
            class="flex items-center gap-2 bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 transition-colors shadow-sm font-medium"
          >
            <span class="text-xl leading-none">+</span> Add Field
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
              @click="refreshFields"
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
        <div v-if="fields.length === 0" class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-12 text-center border border-gray-100">
          <div class="inline-flex items-center justify-center h-20 w-20 bg-gradient-to-br from-green-100 to-emerald-100 rounded-2xl mb-6">
            <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">No fields yet</h2>
          <p class="text-sm text-gray-600 mb-8 max-w-md mx-auto">
            Add your first field to start tracking planting schedules, weather insights, and crop management.
          </p>
          <button
            @click="goToFieldSetup"
            class="inline-flex items-center px-6 py-3 text-sm font-semibold rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 text-white hover:from-green-700 hover:to-emerald-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
          >
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Create Your First Field
          </button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <article
            v-for="field in fields"
            :key="field.id"
            class="group bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100 overflow-hidden"
          >
            <div class="p-6 h-full flex flex-col">
              <!-- Header -->
              <div class="flex items-start justify-between mb-5">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2">
                    <div class="h-10 w-10 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-md">
                      <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                      </svg>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 group-hover:text-green-700 transition-colors">{{ field.name }}</h3>
                  </div>
                  <div class="flex items-center text-xs text-gray-500 mb-3">
                    <svg class="h-4 w-4 mr-1.5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span class="truncate">{{ formatLocation(field.location || field.address) }}</span>
                  </div>
                </div>
                <span
                  class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold shadow-sm"
                  :class="statusClass(field.status)"
                >
                  {{ field.status ? statusLabel(field.status) : 'Active' }}
                </span>
              </div>

              <!-- Current Crop Highlight -->
              <div v-if="field.current_crop" class="mb-5 p-3 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200">
                <div class="flex items-center gap-2">
                  <div class="h-8 w-8 bg-green-500 rounded-lg flex items-center justify-center">
                    <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Current Crop</p>
                    <p class="text-sm font-bold text-green-700">{{ field.current_crop }}</p>
                  </div>
                </div>
              </div>

              <!-- Field Details Grid -->
              <dl class="grid grid-cols-2 gap-4 mb-5">
                <div class="flex items-start gap-2">
                  <div class="mt-0.5">
                    <svg class="h-4 w-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4" />
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Size</dt>
                    <dd class="text-sm font-bold text-gray-900 mt-0.5">
                      {{ formatArea(field.size || field.area || field.field_size) }}
                    </dd>
                  </div>
                </div>
                <div class="flex items-start gap-2">
                  <div class="mt-0.5">
                    <svg class="h-4 w-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Soil</dt>
                    <dd class="text-sm font-semibold text-gray-700 mt-0.5 truncate">
                      {{ field.soil_type || 'Not set' }}
                    </dd>
                  </div>
                </div>
                <div v-if="!field.current_crop" class="flex items-start gap-2 col-span-2">
                  <div class="mt-0.5">
                    <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                  </div>
                  <div class="flex-1">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Current Crop</dt>
                    <dd class="text-sm font-semibold text-gray-400 mt-0.5">None</dd>
                  </div>
                </div>
                <div class="flex items-start gap-2">
                  <div class="mt-0.5">
                    <svg class="h-4 w-4 text-cyan-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 002-2V6a1 1 0 011-1h2m6 0h2a1 1 0 011 1v3a2 2 0 002 2h1.945M15 21v-6a3 3 0 00-3-3 3 3 0 00-3 3v6m6 0H9" />
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Irrigation</dt>
                    <dd class="text-sm font-semibold text-gray-700 mt-0.5 truncate">
                      {{ field.irrigation_type || 'Not set' }}
                    </dd>
                  </div>
                </div>
              </dl>

              <!-- Footer -->
              <div class="mt-auto pt-4 border-t border-gray-200 flex justify-between items-center">
                <div class="text-xs text-gray-400 flex items-center gap-1">
                  <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Updated {{ formatDate(field.updated_at || field.created_at) }}
                </div>
                <button
                  @click.stop="editField(field.id)"
                  class="text-gray-400 hover:text-indigo-600 transition-colors p-1 rounded hover:bg-indigo-50"
                  title="Edit Field"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                  </svg>
                </button>
              </div>
            </div>
          </article>
        </div>
      </div>
      </div>
    </div>
    <EditFarmModal
      :show="showEditFarmModal"
      :farm="farmProfile"
      @close="showEditFarmModal = false"
      @updated="onFarmUpdated"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFarmStore } from '@/stores/farm'
import EditFarmModal from '@/Components/Farm/EditFarmModal.vue'

const router = useRouter()
const farmStore = useFarmStore()

const loading = ref(true)
const error = ref('')
const showEditFarmModal = ref(false)

const fields = computed(() => farmStore.fields || [])
const farmProfile = computed(() => farmStore.farmProfile)

const openEditFarmModal = () => {
  showEditFarmModal.value = true
}

const onFarmUpdated = async () => {
  await farmStore.fetchFarmProfile()
}

const refreshFields = async () => {
  loading.value = true
  error.value = ''

  try {
    await Promise.all([
      farmStore.fetchFields(),
      farmStore.fetchFarmProfile()
    ])
  } catch (err) {
    console.error('Failed to load fields:', err)
    error.value = err.userMessage || err.response?.data?.message || 'Unable to load fields.'
  } finally {
    loading.value = false
  }
}

const goToFieldSetup = () => {
  // Navigate directly to field creation page
  router.push('/fields/create')
}

const editField = (id) => {
  router.push(`/fields/${id}/edit`)
}

const formatArea = (value) => {
  if (!value) return '—'
  const num = Number(value)
  if (Number.isNaN(num)) return value
  return `${num.toFixed(1)} ha`
}

const formatLocation = (location) => {
  if (!location) return 'Location not set'
  if (typeof location === 'string') return location
  const parts = [location.barangay, location.city, location.province].filter(Boolean)
  return parts.join(', ') || 'Location not set'
}

const formatDate = (value) => {
  if (!value) return 'Recently'
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? 'Recently' : date.toLocaleDateString()
}

const statusLabel = (status) => {
  const labels = {
    active: 'Active',
    fallow: 'Fallow',
    maintenance: 'Maintenance'
  }
  return labels[status] || status
}

const statusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    fallow: 'bg-yellow-100 text-yellow-800',
    maintenance: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-blue-100 text-blue-800'
}

onMounted(() => {
  // Always refresh fields to get latest current_crop data
  refreshFields()
})
</script>


