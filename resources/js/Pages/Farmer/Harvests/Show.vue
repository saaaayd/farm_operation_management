<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <nav class="flex items-center text-sm text-gray-500 mb-2">
            <router-link to="/harvests" class="hover:text-gray-700">Harvests</router-link>
            <span class="mx-2">/</span>
            <span class="text-gray-900">Harvest Details</span>
          </nav>
          <h1 class="text-3xl font-bold text-gray-800">Harvest Details</h1>
        </div>
        <div class="flex gap-3">
          <button
            @click="goBack"
            class="px-4 py-2 text-gray-600 bg-white border border-gray-300 rounded-lg hover:bg-gray-50"
          >
            Back to List
          </button>
          <button
            @click="editHarvest"
            class="px-4 py-2 text-white bg-green-600 rounded-lg hover:bg-green-700"
          >
            Edit Harvest
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-6 text-center">
        <p class="text-red-600">{{ error }}</p>
        <button @click="loadHarvest" class="mt-4 text-red-700 hover:underline">Try again</button>
      </div>

      <!-- Main Content -->
      <div v-else-if="harvest" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Yield Summary -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4 flex items-center">
              <span class="text-2xl mr-2">🌾</span> Yield Summary
            </h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
              <div class="text-center p-4 bg-green-50 rounded-lg">
                <p class="text-3xl font-bold text-green-600">{{ harvest.quantity || harvest.yield || 0 }}</p>
                <p class="text-sm text-gray-600 mt-1">{{ harvest.unit || 'kg' }} Harvested</p>
              </div>
              <div class="text-center p-4 bg-blue-50 rounded-lg">
                <p class="text-3xl font-bold text-blue-600">{{ harvest.quality_grade || 'N/A' }}</p>
                <p class="text-sm text-gray-600 mt-1">Quality Grade</p>
              </div>
              <div class="text-center p-4 bg-purple-50 rounded-lg">
                <p class="text-3xl font-bold text-purple-600">{{ formatCurrency(harvest.price_per_unit || 0) }}</p>
                <p class="text-sm text-gray-600 mt-1">Price per {{ harvest.unit || 'kg' }}</p>
              </div>
              <div class="text-center p-4 bg-emerald-50 rounded-lg">
                <p class="text-3xl font-bold text-emerald-600">{{ formatCurrency(harvest.total_value || calculateTotalValue()) }}</p>
                <p class="text-sm text-gray-600 mt-1">Total Value</p>
              </div>
            </div>
          </div>

          <!-- Harvest Details -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Harvest Information</h2>
            <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div class="flex justify-between border-b border-gray-100 pb-3">
                <dt class="text-gray-600">Harvest Date</dt>
                <dd class="font-medium text-gray-900">{{ formatDate(harvest.harvest_date) }}</dd>
              </div>
              <div class="flex justify-between border-b border-gray-100 pb-3">
                <dt class="text-gray-600">Field</dt>
                <dd class="font-medium text-gray-900">{{ harvest.planting?.field?.name || 'N/A' }}</dd>
              </div>
              <div class="flex justify-between border-b border-gray-100 pb-3">
                <dt class="text-gray-600">Rice Variety</dt>
                <dd class="font-medium text-gray-900">{{ harvest.planting?.riceVariety?.name || harvest.planting?.crop_type || 'Rice' }}</dd>
              </div>
              <div class="flex justify-between border-b border-gray-100 pb-3">
                <dt class="text-gray-600">Moisture Content</dt>
                <dd class="font-medium text-gray-900">{{ harvest.moisture_content ? harvest.moisture_content + '%' : 'N/A' }}</dd>
              </div>
              <div class="flex justify-between border-b border-gray-100 pb-3">
                <dt class="text-gray-600">Storage Location</dt>
                <dd class="font-medium text-gray-900">{{ harvest.storage_location || 'Not specified' }}</dd>
              </div>
              <div class="flex justify-between border-b border-gray-100 pb-3">
                <dt class="text-gray-600">Planting Date</dt>
                <dd class="font-medium text-gray-900">{{ harvest.planting?.planting_date ? formatDate(harvest.planting.planting_date) : 'N/A' }}</dd>
              </div>
            </dl>
          </div>

          <!-- Notes -->
          <div v-if="harvest.notes" class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Notes</h2>
            <p class="text-gray-700 leading-relaxed">{{ harvest.notes }}</p>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Planting Link -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Source Planting</h3>
            <div v-if="harvest.planting" class="space-y-3">
              <div class="p-4 bg-green-50 rounded-lg">
                <p class="font-medium text-green-800">{{ harvest.planting.riceVariety?.name || 'Rice Planting' }}</p>
                <p class="text-sm text-green-600 mt-1">{{ harvest.planting.field?.name || 'Field' }}</p>
                <p class="text-xs text-gray-500 mt-2">Planted: {{ formatDate(harvest.planting.planting_date) }}</p>
              </div>
              <router-link
                :to="`/plantings/${harvest.planting.id}`"
                class="block text-center text-green-600 hover:text-green-700 text-sm font-medium"
              >
                View Planting Details →
              </router-link>
            </div>
            <p v-else class="text-gray-500 text-sm">No linked planting record</p>
          </div>

          <!-- Quick Stats -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Yield Analysis</h3>
            <div class="space-y-4">
              <div v-if="harvest.planting?.area_planted">
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-gray-600">Yield per Hectare</span>
                  <span class="font-medium">{{ calculateYieldPerHectare() }} {{ harvest.unit || 'kg' }}/ha</span>
                </div>
              </div>
              <div v-if="harvest.planting?.planting_date">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Growth Period</span>
                  <span class="font-medium">{{ calculateGrowthPeriod() }} days</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Actions -->
          <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Actions</h3>
            <div class="space-y-2">
              <button
                @click="editHarvest"
                class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg text-sm font-medium text-gray-700"
              >
                ✏️ Edit Harvest Record
              </button>
              <button
                @click="createProduct"
                class="w-full text-left px-4 py-3 bg-gray-50 hover:bg-gray-100 rounded-lg text-sm font-medium text-gray-700"
              >
                🛒 Create Marketplace Product
              </button>
              <button
                @click="deleteHarvest"
                class="w-full text-left px-4 py-3 bg-red-50 hover:bg-red-100 rounded-lg text-sm font-medium text-red-600"
              >
                🗑️ Delete Harvest
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useFarmStore } from '@/stores/farm'

const route = useRoute()
const router = useRouter()
const farmStore = useFarmStore()

const loading = ref(true)
const error = ref(null)
const harvest = ref(null)

const loadHarvest = async () => {
  loading.value = true
  error.value = null
  try {
    // First try to find in store
    if (farmStore.harvests.length === 0) {
      await farmStore.fetchHarvests()
    }
    const found = farmStore.harvests.find(h => Number(h.id) === Number(route.params.id))
    if (found) {
      harvest.value = found
    } else {
      error.value = 'Harvest not found'
    }
  } catch (err) {
    console.error('Error loading harvest:', err)
    error.value = err.message || 'Failed to load harvest'
  } finally {
    loading.value = false
  }
}

const goBack = () => {
  router.push('/harvests')
}

const editHarvest = () => {
  // The Harvests Index uses a modal for editing, so go back to list
  router.push('/harvests')
}

const createProduct = () => {
  router.push('/marketplace/product/create')
}

const deleteHarvest = async () => {
  if (!confirm('Are you sure you want to delete this harvest? This cannot be undone.')) return
  try {
    await farmStore.deleteHarvest(harvest.value.id)
    router.push('/harvests')
  } catch (err) {
    console.error('Failed to delete harvest:', err)
    alert('Failed to delete harvest')
  }
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatCurrency = (value) => {
  if (!value && value !== 0) return '₱0.00'
  return Number(value).toLocaleString('en-PH', { style: 'currency', currency: 'PHP' })
}

const calculateTotalValue = () => {
  const qty = harvest.value?.quantity || harvest.value?.yield || 0
  const price = harvest.value?.price_per_unit || 0
  return qty * price
}

const calculateYieldPerHectare = () => {
  const area = harvest.value?.planting?.area_planted || 0
  const qty = harvest.value?.quantity || harvest.value?.yield || 0
  if (area === 0) return 'N/A'
  return (qty / area).toFixed(2)
}

const calculateGrowthPeriod = () => {
  const plantDate = harvest.value?.planting?.planting_date
  const harvestDate = harvest.value?.harvest_date
  if (!plantDate || !harvestDate) return 'N/A'
  const days = Math.floor((new Date(harvestDate) - new Date(plantDate)) / (1000 * 60 * 60 * 24))
  return days > 0 ? days : 'N/A'
}

onMounted(() => {
  loadHarvest()
})
</script>
