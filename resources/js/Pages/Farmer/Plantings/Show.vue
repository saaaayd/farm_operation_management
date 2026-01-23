<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="w-full mx-auto space-y-8">
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
          
          <!-- Lifecycle / Stage Management Card -->
          <div class="bg-white shadow sm:rounded-lg border-l-4 border-green-500">
            <div class="px-4 py-5 sm:p-6">
              <div class="sm:flex sm:items-center sm:justify-between">
                <div>
                  <h3 class="text-lg font-medium leading-6 text-gray-900">Current Growth Stage</h3>
                  <div class="mt-2 max-w-xl text-sm text-gray-500">
                    <p v-if="currentStage">
                      Currently in <span class="font-bold text-green-700">{{ currentStage.rice_growth_stage?.name }}</span> stage.
                    </p>
                    <p v-else class="text-amber-600">No active stage found. Planting may be completed or planned.</p>
                  </div>
                </div>
                <div class="mt-5 sm:mt-0 sm:ml-6 sm:flex-shrink-0">
                  <button
                    v-if="canAdvanceStage"
                    @click="openAdvanceModal"
                    type="button"
                    class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:text-sm"
                  >
                    Advance to Next Stage
                  </button>
                  <span v-else-if="planting.status === 'ready'" class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-teal-100 text-teal-800">
                    Ready for Harvest
                  </span>
                </div>
              </div>

              <!-- Stage Metrics -->
              <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-3" v-if="currentStage">
                <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-100">
                  <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Started Date</dt>
                  <dd class="mt-1 text-lg font-semibold text-gray-900">{{ formatDate(currentStage.started_at) }}</dd>
                </div>
                <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-100">
                  <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Days in Stage</dt>
                  <dd class="mt-1 text-lg font-semibold text-gray-900">{{ daysInStage }} days</dd>
                </div>
                <div class="px-4 py-3 bg-gray-50 rounded-lg border border-gray-100">
                  <dt class="text-xs font-medium text-gray-500 uppercase tracking-wider">Est. Completion</dt>
                  <dd class="mt-1 text-lg font-semibold text-gray-900">
                    {{ lifecycleStatus?.next_stage ? 'Next: ' + lifecycleStatus.next_stage.name : 'Final Stage' }}
                  </dd>
                </div>
              </div>
              
              <!-- Timeline / Progress Bar -->
              <div class="mt-6" v-if="stageTimeline.length > 0">
                <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">Lifecycle Progress</h4>
                <div class="relative">
                  <div class="overflow-hidden h-2 mb-4 text-xs flex rounded bg-gray-200">
                    <div 
                      :style="{ width: `${lifecycleStatus?.progress_percentage || 0}%` }" 
                      class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500 transition-all duration-500"
                    ></div>
                  </div>
                  <div class="flex justify-between text-xs text-gray-400">
                    <span>Planted</span>
                    <span>Harvest</span>
                  </div>
                </div>
              </div>

            </div>
          </div>

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

    <!-- Advance Stage Modal -->
    <div v-if="showAdvanceModal" class="fixed z-50 inset-0 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true" @click="showAdvanceModal = false"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="relative z-10 inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
            <div class="sm:flex sm:items-start">
              <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-green-100 sm:mx-0 sm:h-10 sm:w-10">
                <svg class="h-6 w-6 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                </svg>
              </div>
              <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                  Advance to Next Stage
                </h3>
                <div class="mt-2">
                  <p class="text-sm text-gray-500">
                    Are you sure you want to complete the <strong>{{ currentStage?.rice_growth_stage?.name }}</strong> stage?
                  </p>
                  <p class="text-sm text-gray-500 mt-1" v-if="lifecycleStatus?.next_stage">
                    The next stage will be <strong>{{ lifecycleStatus.next_stage.name }}</strong>.
                  </p>
                  
                  <div class="mt-4">
                    <label for="stage-notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
                    <textarea
                      id="stage-notes"
                      v-model="advanceNotes"
                      rows="3"
                      class="shadow-sm focus:ring-green-500 focus:border-green-500 mt-1 block w-full sm:text-sm border-gray-300 rounded-md"
                      placeholder="Any observations about this stage..."
                    ></textarea>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
            <button
              type="button"
              class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-green-600 text-base font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 sm:ml-3 sm:w-auto sm:text-sm"
              @click="confirmAdvanceStage"
              :disabled="advanceLoading"
            >
              <span v-if="advanceLoading">Processing...</span>
              <span v-else>Confirm & Advance</span>
            </button>
            <button
              type="button"
              class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm"
              @click="showAdvanceModal = false"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>

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
const lifecycleStatus = computed(() => farmStore.lifecycleStatus)
const stageTimeline = computed(() => farmStore.stageTimeline)

// Computed helpers for stage display
const currentStage = computed(() => {
  if (!stageTimeline.value) return null
  return stageTimeline.value.find(s => s.status === 'in_progress')
})

const daysInStage = computed(() => {
  if (!currentStage.value || !currentStage.value.started_at) return 0
  const start = new Date(currentStage.value.started_at)
  const now = new Date()
  const diffTime = Math.abs(now - start)
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24)) 
})

const canAdvanceStage = computed(() => {
  return planting.value && 
         currentStage.value && 
         planting.value.status !== 'ready' && 
         planting.value.status !== 'harvested' &&
         planting.value.status !== 'failed'
})

// Confirmation State
const showConfirmModal = ref(false)
const plantingToDelete = ref(null)

// Advance Stage State
const showAdvanceModal = ref(false)
const advanceNotes = ref('')
const advanceLoading = ref(false)

const fetchPlantingData = async () => {
  // Clear any previous errors
  farmStore.error = null 
  try {
    // Call the lifecycle action instead of just getById
    await farmStore.fetchPlantingLifecycle(plantingId)
  } catch (err) {
    console.error('Failed to fetch planting:', err)
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

// --- Stage Actions ---
const openAdvanceModal = () => {
  advanceNotes.value = ''
  showAdvanceModal.value = true
}

const confirmAdvanceStage = async () => {
  advanceLoading.value = true
  try {
    await farmStore.advanceStage(planting.value.id, {
      notes: advanceNotes.value
    })
    showAdvanceModal.value = false
    // Notifications are handled by api.js interceptor
  } catch (err) {
    console.error('Failed to advance stage:', err)
  } finally {
    advanceLoading.value = false
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