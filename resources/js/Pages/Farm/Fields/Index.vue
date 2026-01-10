<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-6 gap-4">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">Fields</h1>
            <p class="text-sm text-gray-500">
              Track field boundaries, soil data, and readiness for planting.
            </p>
          </div>
          <div class="flex items-center gap-3">
            <button
              @click="refreshFields"
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
              Add Field
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
        <div v-if="fields.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
          <div class="text-5xl mb-4">🌾</div>
          <h2 class="text-lg font-semibold text-gray-900 mb-2">No fields yet</h2>
          <p class="text-sm text-gray-600 mb-6">
            Add your first field to start tracking planting schedules and weather insights.
          </p>
          <button
            @click="openCreateModal"
            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md bg-green-600 text-white hover:bg-green-700"
          >
            Add Field
          </button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <article
            v-for="field in fields"
            :key="field.id"
            class="bg-white rounded-lg shadow hover:shadow-md transition-shadow"
          >
            <div class-="p-6 h-full flex flex-col">
              <div class="flex items-start justify-between mb-4 pt-6 px-6">
                <div>
                  <h3 class="text-lg font-semibold text-gray-900">{{ field.name || 'Unnamed Field' }}</h3>
                  <p class="text-xs text-gray-500">
                    {{ formatLocation(field.location || field.address) }}
                  </p>
                </div>
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="statusClass(field.status)"
                >
                  {{ field.status ? statusLabel(field.status) : 'Active' }}
                </span>
              </div>

              <dl class="grid grid-cols-2 gap-y-2 text-sm text-gray-600 mb-4 px-6">
                <div>
                  <dt class="font-medium text-gray-500">Size</dt>
                  <dd class="text-gray-900 font-semibold">
                    {{ formatArea(field.size || field.area || field.field_size) }}
                  </dd>
                </div>
                <div>
                  <dt class="font-medium text-gray-500">Soil Type</dt>
                  <dd>{{ field.soil_type || 'Not specified' }}</dd>
                </div>
                <div>
                  <dt class="font-medium text-gray-500">Current Crop</dt>
                  <dd>{{ field.current_crop || 'None' }}</dd>
                </div>
                <div>
                  <dt class="font-medium text-gray-500">Irrigation</dt>
                  <dd>{{ field.irrigation_type || 'Not specified' }}</dd>
                </div>
              </dl>

              <div class="mt-auto border-t border-gray-200">
                <div class="flex divide-x divide-gray-200">
                  <button
                    @click="openEditModal(field)"
                    class="flex-1 inline-flex items-center justify-center py-3 text-sm font-medium text-gray-700 hover:bg-gray-50 rounded-bl-lg"
                  >
                    <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.5L13.196 5.232z" />
                    </svg>
                    <span class="ml-2">Edit</span>
                  </button>
                  <button
                    @click="confirmDelete(field)"
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

    <FieldFormModal
      :show="isModalOpen"
      :field="selectedField"
      @close="closeModal"
    />
    
    <!-- Confirmation Modal -->
    <ConfirmationModal
      :show="showConfirmModal"
      title="Delete Field"
      :message="`Are you sure you want to delete ${fieldToDelete?.name || 'this field'}? This action cannot be undone.`"
      confirm-text="Delete"
      type="danger"
      @close="showConfirmModal = false"
      @confirm="deleteField"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFarmStore } from '@/stores/farm'
import FieldFormModal from '@/Components/Modals/FieldFormModal.vue'
import ConfirmationModal from '@/Components/UI/ConfirmationModal.vue'

const router = useRouter()
const farmStore = useFarmStore()

const loading = ref(true)
const error = ref('')
const isModalOpen = ref(false)
const selectedField = ref(null)

// Confirmation State
const showConfirmModal = ref(false)
const fieldToDelete = ref(null)

const fields = computed(() => farmStore.fields || [])

// --- Modal Controls ---
const openCreateModal = () => {
  selectedField.value = null
  isModalOpen.value = true
}

const openEditModal = (field) => {
  selectedField.value = { ...field } // Pass a copy to avoid reactive mutation
  isModalOpen.value = true
}

const closeModal = () => {
  isModalOpen.value = false
  selectedField.value = null
}

// --- Data Fetching ---
const refreshFields = async () => {
  loading.value = true
  error.value = ''

  try {
    await farmStore.fetchFields()
  } catch (err) {
    console.error('Failed to load fields:', err)
    error.value = err.userMessage || err.response?.data?.message || 'Unable to load fields.'
  } finally {
    loading.value = false
  }
}

// --- CRUD Actions ---
const confirmDelete = (field) => {
  fieldToDelete.value = field
  showConfirmModal.value = true
}

const deleteField = async () => {
  if (!fieldToDelete.value) return
  showConfirmModal.value = false
  
  try {
    await farmStore.deleteField(fieldToDelete.value.id)
    // No need to call refreshFields() if store optimistically updates
  } catch (err) {
    console.error('Failed to delete field:', err)
    error.value = err.userMessage || err.response?.data?.message || 'Unable to delete field.'
  }
}

// --- Formatters (Copied from original) ---
const formatArea = (value) => {
  if (!value) return '—'
  const num = Number(value)
  if (Number.isNaN(num)) return value
  return `${num.toFixed(1)} ha`
}

const formatLocation = (location) => {
  if (!location) return 'Location not set'
  if (typeof location === 'string') return location
  // Updated to handle new structure
  if (location.address) return location.address
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

// --- Lifecycle ---
onMounted(() => {
  if (!fields.value.length) {
    refreshFields()
  } else {
    loading.value = false
  }
})
</script>