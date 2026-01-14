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
          <h1 class="mt-4 text-3xl font-bold text-gray-900">Edit Planting</h1>
          <p class="mt-2 text-base text-gray-600 max-w-2xl">
            Update the details for this planting cycle.
          </p>
        </div>
      </div>


        <div v-if="loading" class="bg-white shadow-lg rounded-xl p-12 text-center">
          <LoadingSpinner text="Loading planting data..." />
        </div>
        
        <div v-else-if="error" class="bg-red-50 border-l-4 border-red-500 rounded-lg p-6">
          <h3 class="text-lg font-medium text-red-800">Failed to load planting</h3>
          <p class="mt-2 text-sm text-red-700">{{ error }}</p>
          <button
            @click="fetchPlantingData"
            class="mt-4 inline-flex items-center px-4 py-2 text-sm font-medium rounded-md bg-red-600 text-white hover:bg-red-700 transition-colors"
          >
            Try Again
          </button>
        </div>

        <PlantingForm
          v-else-if="planting"
          :planting="planting"
        />
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useFarmStore } from '@/stores/farm'
import PlantingForm from './PlantingForm.vue'
import LoadingSpinner from '@/Components/UI/LoadingSpinner.vue'

const router = useRouter()
const route = useRoute()
const farmStore = useFarmStore()

const loading = ref(true)
const error = ref('')

// Get the planting ID from the URL
const plantingId = route.params.id

// Use a computed prop to get the planting data from the store
const planting = computed(() => farmStore.currentPlanting)

const fetchPlantingData = async () => {
  loading.value = true
  error.value = ''
  try {
    // Call the action we added to the store
    await farmStore.fetchPlantingById(plantingId)
  } catch (err) {
    console.error('Failed to fetch planting:', err)
    error.value = err.response?.data?.message || 'Could not load planting data.'
  } finally {
    loading.value = false
  }
}

const goBack = () => {
  router.back()
}

// Fetch the data when the component is first loaded
onMounted(() => {
  fetchPlantingData()
})
</script>