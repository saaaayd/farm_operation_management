<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <button
            type="button"
            @click="goBack"
            class="inline-flex items-center text-sm font-medium text-emerald-700 hover:text-emerald-900 transition-colors"
          >
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Plantings
          </button>
          <h1 class="mt-4 text-3xl font-bold text-gray-900">
             {{ planting ? planting.crop_type : 'Planting Details' }}
          </h1>
          <p class="mt-2 text-base text-gray-600 max-w-2xl">
             {{ planting ? `On Field: ${planting.field?.name}` : 'Loading...' }}
          </p>
        </div>
        <div class="flex items-center gap-3" v-if="planting">
            <button
              @click="goToEdit(planting.id)"
              class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-50"
            >
              Edit
            </button>
            <button
              @click="confirmDelete(planting)"
              class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md bg-red-600 text-white hover:bg-red-700"
            >
              Delete
            </button>
          </div>
      </div>


      <div v-if="loading" class="bg-white shadow sm:rounded-lg p-12 text-center">
        <LoadingSpinner text="Loading planting details..." />
      </div>
      
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-6">
        <h3 class="text-lg font-medium text-red-800">Failed to load planting</h3>
        <p class="mt-2 text-sm text-red-700">{{ error }}</p>
        <button
          @click="fetchPlantingData"
          class="mt-4 inline-flex items-center px-4 py-2 text-sm font-medium rounded-md bg-red-600 text-white hover:bg-red-700"
        >
          Try Again
        </button>
      </div>

      <div v-else-if="planting" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <div class="flex justify-between items-start">
                <h3 class="text-lg font-medium leading-6 text-gray-900">Planting Details</h3>
                <span
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                  :class="statusClass(planting.status)"
                >
                  {{ formatStatus(planting.status) }}
                </span>
              </div>
              
              <dl class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-6">
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-gray-500">Field</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ planting.field?.name || 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-gray-500">Crop</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ planting.crop_type || 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-gray-500">Variety</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ planting.rice_variety?.name || 'N/A' }}</dd>
                </div>
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-gray-500">Season</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatStatus(planting.season) }}</dd>
                </div>
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-gray-500">Planting Date</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(planting.planting_date) }}</dd>
                </div>
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-gray-500">Expected Harvest</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatDate(planting.expected_harvest_date) }}</dd>
                </div>
                 <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-gray-500">Planting Method</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatStatus(planting.planting_method) }}</dd>
                </div>
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-gray-500">Area Planted</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ planting.area_planted || 'N/A' }} ha</dd>
                </div>
                <div class="sm:col-span-1">
                  <dt class="text-sm font-medium text-gray-500">Seed Quantity</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ planting.seed_rate || 'N/A' }} kg</dd>
                </div>
              </dl>
              
              <div class="mt-6" v-if="planting.notes">
                <dt class="text-sm font-medium text-gray-500">Notes</dt>
                <dd class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">{{ planting.notes }}</dd>
              </div>
            </div>
          </div>
        </div>

        <div class="lg:col-span-1 space-y-6">
          <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg font-medium leading-6 text-gray-900">Tasks</h3>
              <ul v-if="planting.tasks && planting.tasks.length > 0" class="mt-4 space-y-3">
                <li v-for="task in planting.tasks" :key="task.id" class="p-3 bg-gray-50 rounded-md flex justify-between items-center">
                  <div>
                    <p class="text-sm font-medium text-gray-900">{{ task.name }}</p>
                    <p class="text-xs text-gray-500">Due: {{ formatDate(task.due_date) }}</p>
                  </div>
                  <span :class="statusClass(task.status)" class="text-xs font-medium px-2 py-0.5 rounded-full">
                    {{ formatStatus(task.status) }}
                  </span>
                </li>
              </ul>
              <p v-else class="mt-4 text-sm text-gray-500">No tasks associated with this planting.</p>
              </div>
          </div>

          <div class="bg-white shadow sm:rounded-lg">
            <div class="px-4 py-5 sm:p-6">
              <h3 class="text-lg font-medium leading-6 text-gray-900">Harvests</h3>
              <ul v-if="planting.harvests && planting.harvests.length > 0" class="mt-4 space-y-3">
                <li v-for="harvest in planting.harvests" :key="harvest.id" class="p-3 bg-gray-50 rounded-md">
                  <p class="text-sm font-medium text-gray-900">{{ harvest.quantity }} {{ harvest.unit }}</p>
                  <p class="text-xs text-gray-500">Harvested on: {{ formatDate(harvest.harvest_date) }}</p>
                </li>
              </ul>
              <p v-else class="mt-4 text-sm text-gray-500">No harvests recorded for this planting yet.</p>
              </div>
          </div>
        </div>
      </div>

    </div>

    <!-- Confirmation Modal -->
    <ConfirmationModal
      :show="showConfirmModal"
      title="Delete Planting"
      :message="`Are you sure you want to delete ${plantingToDelete?.crop_type || 'this planting'}? This action cannot be undone.`"
      confirm-text="Delete"
      type="danger"
      @close="showConfirmModal = false"
      @confirm="deletePlanting"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useFarmStore } from '@/stores/farm'
import LoadingSpinner from '@/Components/UI/LoadingSpinner.vue'
import ConfirmationModal from '@/Components/UI/ConfirmationModal.vue'

const router = useRouter()
const route = useRoute()
const farmStore = useFarmStore()

// Get the planting ID from the URL
const plantingId = route.params.id

// Use computed props to reactively get data from the store
const loading = computed(() => farmStore.loadingPlanting)
const error = computed(() => farmStore.error)
const planting = computed(() => farmStore.currentPlanting)

// Confirmation State
const showConfirmModal = ref(false)
const plantingToDelete = ref(null)

const fetchPlantingData = async () => {
  // Clear any previous errors
  farmStore.error = null 
  try {
    // Call the action we added to the store
    await farmStore.fetchPlantingById(plantingId)
  } catch (err) {
    console.error('Failed to fetch planting:', err)
    // The store action already sets the error, so we just log it
  }
}

// --- Navigation ---
const goBack = () => {
  router.push('/plantings') // Go back to the index page
}

const goToEdit = (id) => {
  router.push(`/plantings/${id}/edit`)
}

// --- CRUD Actions ---
const confirmDelete = (planting) => {
  plantingToDelete.value = planting
  showConfirmModal.value = true
}

const deletePlanting = async () => {
  if (!plantingToDelete.value) return
  showConfirmModal.value = false
  
  try {
    await farmStore.deletePlanting(plantingToDelete.value.id)
    // After deleting, go back to the index page
    router.push('/plantings')
  } catch (err) {
    console.error('Failed to delete planting:', err)
    // The store action will set the error, which our computed prop will catch
  }
}

// --- Formatters (consistent with Index page) ---
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

const statusClass = (status) => {
  const classes = {
    // Planting Status
    planned: 'bg-gray-100 text-gray-800',
    planted: 'bg-blue-100 text-blue-800',
    growing: 'bg-yellow-100 text-yellow-800',
    ready: 'bg-teal-100 text-teal-800',
    harvested: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    // Season
    wet: 'bg-blue-100 text-blue-800',
    dry: 'bg-orange-100 text-orange-800',
    // Method
    transplanting: 'bg-indigo-100 text-indigo-800',
    direct_seeding: 'bg-purple-100 text-purple-800',
    broadcasting: 'bg-pink-100 text-pink-800',
    // Task Status
    pending: 'bg-gray-100 text-gray-800',
    in_progress: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-green-100 text-green-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

// Fetch the data when the component is first loaded
onMounted(() => {
  fetchPlantingData()
})
</script>