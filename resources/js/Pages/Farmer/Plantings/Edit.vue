<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
          <div class="flex items-center gap-3">
            <button @click="goBack" class="text-gray-500 hover:text-gray-700">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <div>
              <h1 class="text-2xl font-semibold text-gray-900">Edit Planting</h1>
              <p class="text-sm text-gray-500">
                Update the details for this planting cycle.
              </p>
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="max-w-3xl mx-auto">
        <div v-if="loading" class="bg-white shadow sm:rounded-lg p-12 text-center">
          <LoadingSpinner text="Loading planting data..." />
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

        <PlantingForm
          v-else-if="planting"
          :planting="planting"
        />
      </div>
    </main>
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