<template>
  <div class="field-detail-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
        <p class="mt-4 text-gray-600">Loading field data...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error Loading Field</h3>
            <p class="mt-1 text-sm text-red-700">{{ error }}</p>
            <button
              @click="loadFieldData(route.params.id)"
              class="mt-3 text-sm font-medium text-red-800 hover:text-red-900 underline"
            >
              Try again
            </button>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div v-else>
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <nav class="text-sm text-gray-500 mb-2">
            <router-link to="/fields" class="hover:text-gray-700">Fields</router-link>
            <span class="mx-2">/</span>
            <span class="text-gray-900">{{ field.name }}</span>
          </nav>
          <h1 class="text-3xl font-bold text-gray-900">{{ field.name }}</h1>
          <p class="text-gray-600 mt-2">{{ field.description }}</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="editField"
            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Edit Field
          </button>
          <button
            @click="addPlanting"
            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            Add Planting
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Field Overview -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Field Overview</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ field.size }}</div>
                <div class="text-sm text-gray-600">Acres</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ field.current_crop || 'None' }}</div>
                <div class="text-sm text-gray-600">Current Crop</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ field.soil_type }}</div>
                <div class="text-sm text-gray-600">Soil Type</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">{{ field.status }}</div>
                <div class="text-sm text-gray-600">Status</div>
              </div>
            </div>
          </div>

          <!-- Current Planting -->
          <div v-if="currentPlanting" class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Current Planting</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h3 class="font-medium text-gray-900 mb-2">{{ currentPlanting.crop_type }}</h3>
                <div class="space-y-2">
                  <div class="flex justify-between">
                    <span class="text-gray-600">Planted:</span>
                    <span class="font-medium">{{ formatDate(currentPlanting.planted_date) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Expected Harvest:</span>
                    <span class="font-medium">{{ formatDate(currentPlanting.expected_harvest) }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-gray-600">Variety:</span>
                    <span class="font-medium">{{ currentPlanting.variety }}</span>
                  </div>
                </div>
              </div>
              <div>
                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                  <div
                    class="bg-green-600 h-2 rounded-full"
                    :style="{ width: `${currentPlanting.growth_progress}%` }"
                  ></div>
                </div>
                <div class="text-sm text-gray-600">{{ currentPlanting.growth_progress }}% Complete</div>
              </div>
            </div>
          </div>

          <!-- Recent Activities -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Recent Activities</h2>
            <div class="space-y-4">
              <div
                v-for="activity in recentActivities"
                :key="activity.id"
                class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 text-sm">{{ getActivityIcon(activity.type) }}</span>
                  </div>
                </div>
                <div class="flex-1">
                  <div class="font-medium text-gray-900">{{ activity.title }}</div>
                  <div class="text-sm text-gray-600">{{ activity.description }}</div>
                  <div class="text-xs text-gray-500 mt-1">{{ formatDate(activity.date) }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Planting History -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Planting History</h2>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Crop</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Planted</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harvested</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Yield</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="planting in plantingHistory" :key="planting.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ planting.crop_type }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ formatDate(planting.planted_date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ planting.harvested_date ? formatDate(planting.harvested_date) : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ planting.yield ? `${planting.yield} bushels` : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="getStatusBadgeClass(planting.status)"
                        class="px-2 py-1 text-xs font-medium rounded-full"
                      >
                        {{ planting.status }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Field Map -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Field Map</h3>
            <div class="w-full h-48 bg-gray-200 rounded-lg flex items-center justify-center">
              <span class="text-gray-500">Map placeholder</span>
            </div>
          </div>

          <!-- Weather -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Current Weather</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Temperature:</span>
                <span class="font-medium">72°F</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Humidity:</span>
                <span class="font-medium">65%</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Rainfall:</span>
                <span class="font-medium">0.2 in</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Wind:</span>
                <span class="font-medium">5 mph</span>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
            <div class="space-y-3">
              <button
                @click="addTask"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                📋 Add Task
              </button>
              <button
                @click="viewWeather"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                🌤️ View Weather
              </button>
              <button
                @click="addNote"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                📝 Add Note
              </button>
              <button
                @click="viewReports"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                📊 View Reports
              </button>
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { fieldsAPI, plantingsAPI } from '@/services/api'

const route = useRoute()
const router = useRouter()

const field = ref({
  id: null,
  name: '',
  description: '',
  size: 0,
  soil_type: '',
  current_crop: null,
  status: 'active',
  location: null,
  area: 0,
})

const currentPlanting = ref(null)

const recentActivities = ref([])
const plantingHistory = ref([])
const loading = ref(true)
const error = ref(null)

const getActivityIcon = (type) => {
  const icons = {
    planting: '🌱',
    fertilizer: '🌿',
    irrigation: '💧',
    harvest: '🌾',
    maintenance: '🔧'
  }
  return icons[type] || '📝'
}

const getStatusBadgeClass = (status) => {
  const classes = {
    growing: 'bg-green-100 text-green-800',
    completed: 'bg-blue-100 text-blue-800',
    failed: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString()
}

const editField = () => {
  router.push(`/fields/${field.value.id}/edit`)
}

const addPlanting = () => {
  // Navigate to add planting page
  router.push('/plantings/create')
}

const addTask = () => {
  // Navigate to add task page
  router.push('/tasks/create')
}

const viewWeather = () => {
  // Navigate to weather page
  router.push(`/weather/fields/${field.value.id}`)
}

const addNote = async () => {
  const newNote = prompt('Add a note to this field:')
  if (newNote === null) return
  
  try {
    await fieldsAPI.update(field.value.id, { notes: newNote })
    await loadFieldData(field.value.id)
    alert('Note added successfully')
  } catch (error) {
    console.error('Failed to add note:', error)
    alert('Failed to add note: ' + (error.response?.data?.message || 'Unknown error'))
  }
}

const viewReports = () => {
  // Navigate to reports page
  router.push('/reports/crop-yield')
}

onMounted(() => {
  const fieldId = route.params.id
  // Load field data from API
  loadFieldData(fieldId)
})

const loadFieldData = async (id) => {
  try {
    loading.value = true
    error.value = null
    
    // Load field data from API
    const response = await fieldsAPI.getById(id)
    const data = response.data.data || response.data
    
    // Map API response to component data
    field.value = {
      id: data.id,
      name: data.name || '',
      description: data.description || '',
      size: data.area || data.size || 0,
      soil_type: data.soil_type || '',
      current_crop: null, // Will be set from plantings
      status: 'active',
      location: data.location,
      area: data.area || data.size || 0,
    }
    
    // Load plantings for this field
    try {
      const plantingsResponse = await plantingsAPI.getAll()
      const allPlantings = plantingsResponse.data.data || plantingsResponse.data.plantings || plantingsResponse.data || []
      const fieldPlantings = allPlantings.filter(p => p.field_id === id)
      
      // Set current planting (most recent active one)
      const activePlanting = fieldPlantings.find(p => ['planted', 'growing', 'ready'].includes(p.status))
      if (activePlanting) {
        currentPlanting.value = {
          id: activePlanting.id,
          crop_type: activePlanting.crop_type || 'rice',
          variety: activePlanting.rice_variety?.name || activePlanting.variety || '',
          planted_date: activePlanting.planting_date || activePlanting.planted_date,
          expected_harvest: activePlanting.expected_harvest_date || activePlanting.expected_harvest,
          growth_progress: calculateGrowthProgress(activePlanting),
        }
        field.value.current_crop = activePlanting.crop_type || 'rice'
      }
      
      // Set planting history
      plantingHistory.value = fieldPlantings.map(p => ({
        id: p.id,
        crop_type: p.crop_type || 'rice',
        planted_date: p.planting_date || p.planted_date,
        harvested_date: p.actual_harvest_date || p.harvested_date,
        yield: p.harvests?.reduce((sum, h) => sum + (h.yield || 0), 0) || null,
        status: p.status,
      }))
      
      // Generate recent activities from plantings and tasks
      recentActivities.value = generateActivitiesFromPlantings(fieldPlantings)
      
    } catch (plantingError) {
      console.error('Error loading plantings:', plantingError)
      plantingHistory.value = []
      recentActivities.value = []
    }
    
  } catch (err) {
    console.error('Error loading field data:', err)
    error.value = err.response?.data?.message || 'Failed to load field data'
  } finally {
    loading.value = false
  }
}

const calculateGrowthProgress = (planting) => {
  if (planting.planting_stages && planting.planting_stages.length > 0) {
    const completedStages = planting.planting_stages.filter(s => s.status === 'completed').length
    return Math.round((completedStages / planting.planting_stages.length) * 100)
  }
  const statusProgress = {
    'planned': 0,
    'planted': 20,
    'growing': 50,
    'ready': 80,
    'harvested': 100,
  }
  return statusProgress[planting.status] || 0
}

const generateActivitiesFromPlantings = (plantings) => {
  const activities = []
  
  plantings.forEach(planting => {
    if (planting.created_at) {
      activities.push({
        id: `planting-${planting.id}`,
        type: 'planting',
        title: `${planting.crop_type} planted`,
        description: `Planted ${planting.rice_variety?.name || planting.variety || 'crop'} variety`,
        date: planting.planting_date || planting.created_at,
      })
    }
    
    if (planting.planting_stages) {
      planting.planting_stages
        .filter(stage => stage.status === 'completed')
        .forEach(stage => {
          activities.push({
            id: `stage-${stage.id}`,
            type: 'planting',
            title: `Growth stage: ${stage.rice_growth_stage?.name || 'Stage'}`,
            description: stage.notes || 'Growth stage completed',
            date: stage.completed_at || stage.updated_at,
          })
        })
    }
  })
  
  return activities
    .sort((a, b) => new Date(b.date) - new Date(a.date))
    .slice(0, 10)
}
</script>

<style scoped>
.field-detail-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>