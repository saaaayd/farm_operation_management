<template>
  <div class="task-detail-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
        <p class="mt-4 text-gray-600">Loading task data...</p>
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
            <h3 class="text-sm font-medium text-red-800">Error Loading Task</h3>
            <p class="mt-1 text-sm text-red-700">{{ error }}</p>
            <button
              @click="loadTaskData(route.params.id)"
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
            <router-link to="/tasks" class="hover:text-gray-700">Tasks</router-link>
            <span class="mx-2">/</span>
            <span class="text-gray-900">{{ task.title }}</span>
          </nav>
          <h1 class="text-3xl font-bold text-gray-900">{{ task.title }}</h1>
          <p class="text-gray-600 mt-2">{{ task.description }}</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="toggleTaskCompletion"
            :class="task.completed ? 'bg-green-600 hover:bg-green-700' : 'bg-blue-600 hover:bg-blue-700'"
            class="text-white px-4 py-2 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            {{ task.completed ? 'Mark Incomplete' : 'Mark Complete' }}
          </button>
          <button
            @click="editTask"
            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Edit Task
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Task Overview -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Task Overview</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ task.priority }}</div>
                <div class="text-sm text-gray-600">Priority</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ task.status }}</div>
                <div class="text-sm text-gray-600">Status</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ task.estimated_hours || 'N/A' }}</div>
                <div class="text-sm text-gray-600">Est. Hours</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">{{ daysUntilDue }}</div>
                <div class="text-sm text-gray-600">Days Until Due</div>
              </div>
            </div>
          </div>

          <!-- Task Details -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Task Details</h2>
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <p class="text-gray-900">{{ task.description || 'No description provided' }}</p>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Field</label>
                  <p class="text-gray-900">{{ task.field_name || 'No field assigned' }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
                  <p class="text-gray-900">{{ task.assigned_to || 'Unassigned' }}</p>
                </div>
              </div>
              
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Created Date</label>
                  <p class="text-gray-900">{{ formatDate(task.created_at) }}</p>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
                  <p class="text-gray-900">{{ formatDate(task.due_date) }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Time Tracking -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Time Tracking</h2>
            <div class="space-y-4">
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Estimated Hours:</span>
                <span class="font-medium">{{ task.estimated_hours || 'Not set' }} hours</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Actual Hours:</span>
                <span class="font-medium">{{ task.actual_hours || 0 }} hours</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Progress:</span>
                <span class="font-medium">{{ timeProgress }}%</span>
              </div>
              
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-blue-600 h-2 rounded-full transition-all duration-300"
                  :style="{ width: `${timeProgress}%` }"
                ></div>
              </div>
              
              <div class="flex space-x-3">
                <button
                  @click="startTimer"
                  :disabled="timerRunning"
                  class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50"
                >
                  {{ timerRunning ? 'Timer Running...' : 'Start Timer' }}
                </button>
                <button
                  @click="stopTimer"
                  :disabled="!timerRunning"
                  class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 disabled:opacity-50"
                >
                  Stop Timer
                </button>
                <button
                  @click="addTime"
                  class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
                >
                  Add Time
                </button>
              </div>
            </div>
          </div>

          <!-- Comments -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Comments</h2>
            <div class="space-y-4">
              <div
                v-for="comment in comments"
                :key="comment.id"
                class="border-l-4 border-blue-500 pl-4 py-2"
              >
                <div class="flex justify-between items-start mb-2">
                  <span class="font-medium text-gray-900">{{ comment.author }}</span>
                  <span class="text-sm text-gray-500">{{ formatDate(comment.created_at) }}</span>
                </div>
                <p class="text-gray-700">{{ comment.content }}</p>
              </div>
              
              <div class="mt-4">
                <textarea
                  v-model="newComment"
                  rows="3"
                  placeholder="Add a comment..."
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                ></textarea>
                <button
                  @click="addComment"
                  :disabled="!newComment.trim()"
                  class="mt-2 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                >
                  Add Comment
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Task Actions -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
            <div class="space-y-3">
              <button
                @click="assignTask"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                👤 Assign Task
              </button>
              <button
                @click="duplicateTask"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                📋 Duplicate Task
              </button>
              <button
                @click="viewField"
                v-if="task.field_id"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                🌾 View Field
              </button>
              <button
                @click="createRelatedTask"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                ➕ Create Related Task
              </button>
            </div>
          </div>

          <!-- Task History -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Task History</h3>
            <div class="space-y-3">
              <div
                v-for="history in taskHistory"
                :key="history.id"
                class="flex items-start space-x-3"
              >
                <div class="flex-shrink-0">
                  <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 text-xs">{{ getHistoryIcon(history.type) }}</span>
                  </div>
                </div>
                <div class="flex-1">
                  <div class="text-sm text-gray-900">{{ history.description }}</div>
                  <div class="text-xs text-gray-500">{{ formatDate(history.created_at) }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Related Tasks -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Related Tasks</h3>
            <div class="space-y-3">
              <div
                v-for="relatedTask in relatedTasks"
                :key="relatedTask.id"
                class="p-3 border border-gray-200 rounded-lg"
              >
                <div class="font-medium text-gray-900">{{ relatedTask.title }}</div>
                <div class="text-sm text-gray-600">{{ relatedTask.status }}</div>
                <div class="text-xs text-gray-500">{{ formatDate(relatedTask.due_date) }}</div>
              </div>
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
import { tasksAPI, fieldsAPI, laborAPI } from '@/services/api'

const route = useRoute()
const router = useRouter()

const task = ref({
  id: null,
  title: '',
  description: '',
  field_id: null,
  field_name: '',
  priority: '',
  status: '',
  due_date: '',
  assigned_to: '',
  estimated_hours: 0,
  actual_hours: 0,
  completed: false,
  created_at: '',
  planting: null,
  laborer: null,
})

const timerRunning = ref(false)
const newComment = ref('')
const comments = ref([])
const taskHistory = ref([])
const relatedTasks = ref([])
const loading = ref(true)
const error = ref(null)

const daysUntilDue = computed(() => {
  if (!task.value.due_date) return 'N/A'
  const today = new Date()
  const dueDate = new Date(task.value.due_date)
  const diffTime = dueDate - today
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24))
  return diffDays > 0 ? diffDays : 0
})

const timeProgress = computed(() => {
  if (!task.value.estimated_hours || task.value.estimated_hours === 0) return 0
  return Math.min((task.value.actual_hours / task.value.estimated_hours) * 100, 100)
})

const getHistoryIcon = (type) => {
  const icons = {
    created: '➕',
    assigned: '👤',
    started: '▶️',
    completed: '✅',
    updated: '✏️'
  }
  return icons[type] || '📝'
}

const formatDate = (date) => {
  if (!date) return 'Not set'
  return new Date(date).toLocaleDateString()
}

const toggleTaskCompletion = () => {
  task.value.completed = !task.value.completed
  task.value.status = task.value.completed ? 'completed' : 'pending'
  // API call to update task
}

const editTask = () => {
  router.push(`/tasks/${task.value.id}/edit`)
}

const startTimer = () => {
  timerRunning.value = true
  // Start timer logic
}

const stopTimer = () => {
  timerRunning.value = false
  // Stop timer and add time to actual_hours
}

const addTime = async () => {
  const hours = prompt('Enter hours worked:', '0')
  if (hours === null) return
  
  const hoursNum = Number(hours)
  if (isNaN(hoursNum) || hoursNum < 0) {
    alert('Please enter a valid number of hours')
    return
  }
  
  try {
    // Update task with hours worked
    await tasksAPI.update(task.value.id, { hours_worked: hoursNum })
    await loadTaskData(task.value.id)
    alert('Time logged successfully')
  } catch (error) {
    console.error('Failed to add time:', error)
    alert('Failed to log time: ' + (error.response?.data?.message || 'Unknown error'))
  }
}

const addComment = () => {
  if (!newComment.value.trim()) return
  
  const comment = {
    id: Date.now(),
    author: 'Current User',
    content: newComment.value,
    created_at: new Date().toISOString()
  }
  comments.value.push(comment)
  newComment.value = ''
}

const assignTask = async () => {
  // Load laborers for assignment
  try {
    const laborersResponse = await laborAPI.getLaborers()
    const laborers = laborersResponse.data.laborers || laborersResponse.data || []
    
    if (laborers.length === 0) {
      alert('No laborers available. Please create a laborer first.')
      return
    }
    
    const laborerNames = laborers.map(l => `${l.id}: ${l.name}`).join('\n')
    const laborerId = prompt(`Select laborer ID:\n\n${laborerNames}`, task.value.assigned_to?.toString() || '')
    
    if (laborerId === null) return
    
    const id = Number(laborerId)
    if (isNaN(id) || id <= 0) {
      alert('Please enter a valid laborer ID')
      return
    }
    
    await tasksAPI.update(task.value.id, { assigned_to: id })
    await loadTaskData(task.value.id)
    alert('Task assigned successfully')
  } catch (error) {
    console.error('Failed to assign task:', error)
    alert('Failed to assign task: ' + (error.response?.data?.message || 'Unknown error'))
  }
}

const duplicateTask = async () => {
  if (!confirm('Create a duplicate of this task?')) return
  
  try {
    const taskData = {
      planting_id: task.value.planting_id,
      task_type: task.value.task_type,
      description: task.value.description + ' (Copy)',
      due_date: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0], // 7 days from now
      status: 'pending',
      assigned_to: task.value.assigned_to || null,
    }
    
    await tasksAPI.create(taskData)
    alert('Task duplicated successfully')
    router.push('/tasks')
  } catch (error) {
    console.error('Failed to duplicate task:', error)
    alert('Failed to duplicate task: ' + (error.response?.data?.message || 'Unknown error'))
  }
}

const viewField = () => {
  if (task.value.field_id) {
    router.push(`/fields/${task.value.field_id}`)
  }
}

const createRelatedTask = () => {
  // Navigate to create task with pre-filled field
  router.push('/tasks/create')
}

onMounted(() => {
  const taskId = route.params.id
  // Load task data from API
  loadTaskData(taskId)
})

const loadTaskData = async (id) => {
  try {
    loading.value = true
    error.value = null
    
    // Load task data from API
    const response = await tasksAPI.getById(id)
    const data = response.data.data || response.data
    
    // Map API response to component data
    task.value = {
      id: data.id,
      title: data.description || data.title || 'Task',
      description: data.description || '',
      field_id: data.planting?.field_id || data.field_id,
      field_name: data.planting?.field?.name || data.field?.name || '',
      priority: data.priority || 'medium',
      status: data.status || 'pending',
      due_date: data.due_date,
      assigned_to: data.assigned_to,
      estimated_hours: data.estimated_hours || 0,
      actual_hours: data.actual_hours || 0,
      completed: data.status === 'completed',
      created_at: data.created_at,
      planting: data.planting,
      laborer: data.laborer,
    }
    
    // Generate task history from task data
    taskHistory.value = []
    if (data.created_at) {
      taskHistory.value.push({
        id: 'created',
        type: 'created',
        description: 'Task created',
        created_at: data.created_at,
      })
    }
    if (data.assigned_to && data.laborer) {
      taskHistory.value.push({
        id: 'assigned',
        type: 'assigned',
        description: `Assigned to ${data.laborer.name || 'laborer'}`,
        created_at: data.updated_at || data.created_at,
      })
    }
    if (data.status === 'in_progress') {
      taskHistory.value.push({
        id: 'started',
        type: 'started',
        description: 'Task started',
        created_at: data.updated_at || data.created_at,
      })
    }
    if (data.status === 'completed') {
      taskHistory.value.push({
        id: 'completed',
        type: 'completed',
        description: 'Task completed',
        created_at: data.updated_at || data.created_at,
      })
    }
    
    // Load related tasks (tasks for the same planting)
    if (data.planting_id) {
      try {
        const tasksResponse = await tasksAPI.getAll()
        const allTasks = tasksResponse.data.data || tasksResponse.data.tasks || tasksResponse.data || []
        relatedTasks.value = allTasks
          .filter(t => t.planting_id === data.planting_id && t.id !== id)
          .slice(0, 5)
          .map(t => ({
            id: t.id,
            title: t.description || t.title,
            status: t.status,
            due_date: t.due_date,
          }))
      } catch (taskError) {
        console.error('Error loading related tasks:', taskError)
        relatedTasks.value = []
      }
    }
    
  } catch (err) {
    console.error('Error loading task data:', err)
    error.value = err.response?.data?.message || 'Failed to load task data'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.task-detail-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>