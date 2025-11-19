<template>
  <div class="planting-detail-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
        <p class="mt-4 text-gray-600">Loading planting data...</p>
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
            <h3 class="text-sm font-medium text-red-800">Error Loading Planting</h3>
            <p class="mt-1 text-sm text-red-700">{{ error }}</p>
            <button
              @click="loadPlantingData(route.params.id)"
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
            <router-link to="/plantings" class="hover:text-gray-700">Plantings</router-link>
            <span class="mx-2">/</span>
            <span class="text-gray-900">{{ planting.crop_type }} - {{ planting.variety }}</span>
          </nav>
          <h1 class="text-3xl font-bold text-gray-900">{{ planting.crop_type }} - {{ planting.variety }}</h1>
          <p class="text-gray-600 mt-2">Field: {{ planting.field_name }}</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="editPlanting"
            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Edit Planting
          </button>
          <button
            @click="addTask"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Add Task
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Planting Overview -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Planting Overview</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ planting.growth_progress }}%</div>
                <div class="text-sm text-gray-600">Growth Progress</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ daysToHarvest }}</div>
                <div class="text-sm text-gray-600">Days to Harvest</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ planting.planted_date ? formatDate(planting.planted_date) : 'Not set' }}</div>
                <div class="text-sm text-gray-600">Planted Date</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">{{ planting.expected_harvest ? formatDate(planting.expected_harvest) : 'Not set' }}</div>
                <div class="text-sm text-gray-600">Expected Harvest</div>
              </div>
            </div>
          </div>

          <!-- Growth Progress -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Growth Progress</h2>
            <div class="space-y-4">
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Overall Progress</span>
                <span class="font-medium">{{ planting.growth_progress }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div
                  class="bg-green-600 h-3 rounded-full transition-all duration-300"
                  :style="{ width: `${planting.growth_progress}%` }"
                ></div>
              </div>
              
              <div class="grid grid-cols-2 gap-4 mt-6">
                <div>
                  <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Germination</span>
                    <span class="font-medium">{{ planting.germination_rate }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-blue-600 h-2 rounded-full"
                      :style="{ width: `${planting.germination_rate}%` }"
                    ></div>
                  </div>
                </div>
                <div>
                  <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Flowering</span>
                    <span class="font-medium">{{ planting.flowering_rate }}%</span>
                  </div>
                  <div class="w-full bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-yellow-600 h-2 rounded-full"
                      :style="{ width: `${planting.flowering_rate}%` }"
                    ></div>
                  </div>
                </div>
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
                  <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                    <span class="text-green-600 text-sm">{{ getActivityIcon(activity.type) }}</span>
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

          <!-- Tasks -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Related Tasks</h2>
            <div class="space-y-3">
              <div
                v-for="task in relatedTasks"
                :key="task.id"
                class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
              >
                <div class="flex items-center space-x-3">
                  <input
                    type="checkbox"
                    :checked="task.completed"
                    @change="toggleTask(task.id)"
                    class="w-4 h-4 text-green-600 border-gray-300 rounded focus:ring-green-500"
                  />
                  <div>
                    <div class="font-medium text-gray-900">{{ task.title }}</div>
                    <div class="text-sm text-gray-600">{{ task.description }}</div>
                  </div>
                </div>
                <div class="flex items-center space-x-2">
                  <span
                    :class="getTaskStatusBadgeClass(task.status)"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ task.status }}
                  </span>
                  <span class="text-sm text-gray-500">{{ formatDate(task.due_date) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Planting Details -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Planting Details</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Crop Type:</span>
                <span class="font-medium">{{ planting.crop_type }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Variety:</span>
                <span class="font-medium">{{ planting.variety }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Field:</span>
                <span class="font-medium">{{ planting.field_name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Status:</span>
                <span
                  :class="getStatusBadgeClass(planting.status)"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ planting.status }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Seed Rate:</span>
                <span class="font-medium">{{ planting.seed_rate }} lbs/acre</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Spacing:</span>
                <span class="font-medium">{{ planting.row_spacing }}" rows</span>
              </div>
            </div>
          </div>

          <!-- Weather Impact -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Weather Impact</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Temperature:</span>
                <span class="font-medium">72°F</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Rainfall (7d):</span>
                <span class="font-medium">1.2 in</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Growing Degree Days:</span>
                <span class="font-medium">1,245</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Stress Level:</span>
                <span class="font-medium text-green-600">Low</span>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
            <div class="space-y-3">
              <button
                @click="updateProgress"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                📊 Update Progress
              </button>
              <button
                @click="addNote"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                📝 Add Note
              </button>
              <button
                @click="viewField"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                🌾 View Field
              </button>
              <button
                @click="generateReport"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                📈 Generate Report
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
import { plantingsAPI, tasksAPI } from '@/services/api'

const route = useRoute()
const router = useRouter()

const planting = ref({
  id: null,
  field_id: null,
  field_name: '',
  crop_type: '',
  variety: '',
  planted_date: '',
  expected_harvest: '',
  status: 'growing',
  growth_progress: 0,
  germination_rate: 0,
  flowering_rate: 0,
  seed_rate: 0,
  row_spacing: 0,
  field: null,
  rice_variety: null,
  planting_stages: [],
})

const recentActivities = ref([])
const relatedTasks = ref([])
const loading = ref(true)
const error = ref(null)

const daysToHarvest = computed(() => {
  if (!planting.value.expected_harvest) return 'N/A'
  const today = new Date()
  const harvestDate = new Date(planting.value.expected_harvest)
  const diffTime = harvestDate - today
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays > 0 ? diffDays : 0
})

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
    planned: 'bg-blue-100 text-blue-800',
    planted: 'bg-yellow-100 text-yellow-800',
    growing: 'bg-green-100 text-green-800',
    harvested: 'bg-purple-100 text-purple-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getTaskStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
  if (!date) return 'Not set'
  return new Date(date).toLocaleDateString()
}

const editPlanting = () => {
  router.push(`/plantings/${planting.value.id}/edit`)
}

const addTask = () => {
  // Navigate to add task page
  router.push('/tasks/create')
}

const updateProgress = async () => {
  const newStatus = prompt('Update planting status:\n(planted, growing, ready, harvested, failed)', planting.value.status || 'growing')
  if (!newStatus) return
  
  try {
    await plantingsAPI.update(planting.value.id, { status: newStatus })
    await loadPlantingData(planting.value.id)
    alert('Planting status updated successfully')
  } catch (error) {
    console.error('Failed to update progress:', error)
    alert('Failed to update progress: ' + (error.response?.data?.message || 'Unknown error'))
  }
}

const addNote = async () => {
  const currentNotes = planting.value.notes || ''
  const newNote = prompt('Add a note to this planting:', currentNotes)
  if (newNote === null) return
  
  try {
    await plantingsAPI.update(planting.value.id, { notes: newNote })
    await loadPlantingData(planting.value.id)
    alert('Note added successfully')
  } catch (error) {
    console.error('Failed to add note:', error)
    alert('Failed to add note: ' + (error.response?.data?.message || 'Unknown error'))
  }
}

const viewField = () => {
  // Navigate to field detail page
  router.push(`/fields/${planting.value.field_id}`)
}

const generateReport = () => {
  // Navigate to reports page
  router.push('/reports/crop-yield')
}

const toggleTask = (taskId) => {
  const task = relatedTasks.value.find(t => t.id === taskId)
  if (task) {
    task.completed = !task.completed
    task.status = task.completed ? 'completed' : 'pending'
  }
}

onMounted(() => {
  const plantingId = route.params.id
  // Load planting data from API
  loadPlantingData(plantingId)
})

const loadPlantingData = async (id) => {
  try {
    loading.value = true
    error.value = null
    
    // Load planting data from API
    const response = await plantingsAPI.getById(id)
    const data = response.data.data || response.data
    
    // Map API response to component data
    planting.value = {
      id: data.id,
      field_id: data.field_id,
      field_name: data.field?.name || '',
      field: data.field,
      crop_type: data.crop_type || 'rice',
      variety: data.rice_variety?.name || data.variety || '',
      rice_variety: data.rice_variety,
      planted_date: data.planting_date || data.planted_date,
      expected_harvest: data.expected_harvest_date || data.expected_harvest,
      status: data.status || 'growing',
      growth_progress: calculateGrowthProgress(data),
      germination_rate: data.germination_rate || 0,
      flowering_rate: data.flowering_rate || 0,
      seed_rate: data.seed_rate || 0,
      row_spacing: data.row_spacing || 0,
      planting_stages: data.planting_stages || [],
      area_planted: data.area_planted,
      planting_method: data.planting_method,
      notes: data.notes,
    }
    
    // Load related tasks for this planting
    try {
      const tasksResponse = await tasksAPI.getAll()
      const allTasks = tasksResponse.data.data || tasksResponse.data.tasks || tasksResponse.data || []
      relatedTasks.value = allTasks
        .filter(task => task.planting_id === id)
        .map(task => ({
          id: task.id,
          title: task.description || task.title,
          description: task.description,
          status: task.status,
          due_date: task.due_date,
          completed: task.status === 'completed',
        }))
    } catch (taskError) {
      console.error('Error loading tasks:', taskError)
      relatedTasks.value = []
    }
    
    // Generate recent activities from planting stages and tasks
    recentActivities.value = generateActivitiesFromData(data, relatedTasks.value)
    
  } catch (err) {
    console.error('Error loading planting data:', err)
    error.value = err.response?.data?.message || 'Failed to load planting data'
  } finally {
    loading.value = false
  }
}

const calculateGrowthProgress = (data) => {
  if (data.planting_stages && data.planting_stages.length > 0) {
    const completedStages = data.planting_stages.filter(stage => stage.status === 'completed').length
    return Math.round((completedStages / data.planting_stages.length) * 100)
  }
  // Fallback calculation based on status
  const statusProgress = {
    'planned': 0,
    'planted': 20,
    'growing': 50,
    'ready': 80,
    'harvested': 100,
  }
  return statusProgress[data.status] || 0
}

const generateActivitiesFromData = (plantingData, tasks) => {
  const activities = []
  
  // Add planting creation
  if (plantingData.created_at) {
    activities.push({
      id: 'planting-created',
      type: 'planting',
      title: 'Planting created',
      description: `Started ${plantingData.crop_type} planting`,
      date: plantingData.created_at,
    })
  }
  
  // Add completed stages
  if (plantingData.planting_stages) {
    plantingData.planting_stages
      .filter(stage => stage.status === 'completed')
      .forEach(stage => {
        activities.push({
          id: `stage-${stage.id}`,
          type: 'planting',
          title: `Stage completed: ${stage.rice_growth_stage?.name || 'Stage'}`,
          description: stage.notes || 'Growth stage completed',
          date: stage.completed_at || stage.updated_at,
        })
      })
  }
  
  // Add completed tasks
  tasks
    .filter(task => task.completed)
    .slice(0, 5)
    .forEach(task => {
      activities.push({
        id: `task-${task.id}`,
        type: 'maintenance',
        title: task.title,
        description: task.description,
        date: task.due_date,
      })
    })
  
  // Sort by date and return most recent
  return activities
    .sort((a, b) => new Date(b.date) - new Date(a.date))
    .slice(0, 10)
}
</script>

<style scoped>
.planting-detail-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>