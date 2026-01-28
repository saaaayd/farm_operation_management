<template>
  <div class="tasks-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Tasks</h1>
          <p class="text-gray-600 mt-2">Manage your farm tasks and activities</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          Add New Task
        </button>
      </div>

      <!-- Filters and Search -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search tasks..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="statusFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
              <option value="overdue">Overdue</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
            <select
              v-model="priorityFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Priorities</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>
          <div class="flex items-end">
            <button
              @click="clearFilters"
              class="w-full bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500"
            >
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Tasks List -->
      <div class="space-y-4">
        <div
          v-for="task in filteredTasks"
          :key="task.id"
          class="bg-white rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow"
        >
          <div class="flex items-start justify-between">
            <div class="flex-1">
              <div class="flex items-center space-x-3 mb-2">
                <input
                  type="checkbox"
                  :checked="task.completed"
                  @change="toggleTask(task.id)"
                  class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                />
                <h3 class="text-lg font-semibold text-gray-900">{{ task.title }}</h3>
                <span
                  :class="getPriorityBadgeClass(task.priority)"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ task.priority }}
                </span>
                <span
                  :class="getStatusBadgeClass(task.status)"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ task.status }}
                </span>
              </div>
              
              <p class="text-gray-600 mb-3">{{ task.description }}</p>
              
              <div class="flex items-center space-x-6 text-sm text-gray-500">
                <div class="flex items-center space-x-1">
                  <span>📅</span>
                  <span>Due: {{ formatDate(task.due_date) }}</span>
                </div>
                <div class="flex items-center space-x-1">
                  <span>🌾</span>
                  <span>{{ task.field_name || 'No field' }}</span>
                </div>
                <div class="flex items-center space-x-1">
                  <span>👤</span>
                  <span>{{ task.assigned_to || 'Unassigned' }}</span>
                </div>
                <div v-if="task.estimated_hours" class="flex items-center space-x-1">
                  <span>⏱️</span>
                  <span>{{ task.estimated_hours }}h</span>
                </div>
              </div>
            </div>
            
            <div class="flex space-x-2 ml-4">
              <button
                @click="viewTask(task.id)"
                class="bg-blue-600 text-white px-3 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
              >
                View
              </button>
              <button
                @click="editTask(task.id)"
                class="bg-gray-600 text-white px-3 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 text-sm"
              >
                Edit
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredTasks.length === 0" class="text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">📋</div>
        <h3 class="text-xl font-medium text-gray-900 mb-2">No tasks found</h3>
        <p class="text-gray-600 mb-6">Get started by adding your first task</p>
        <button
          @click="showCreateModal = true"
          class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          Add Your First Task
        </button>
      </div>

      <!-- Create Task Modal -->
      <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
          <h2 class="text-xl font-semibold mb-4">Add New Task</h2>
          
          <form @submit.prevent="createTask" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Task Title</label>
              <input
                v-model="newTask.title"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
              <textarea
                v-model="newTask.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Field</label>
              <select
                v-model="newTask.field_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                <option value="">Select field (optional)</option>
                <option v-for="field in fields" :key="field.id" :value="field.id">
                  {{ field.name }}
                </option>
              </select>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Priority</label>
                <select
                  v-model="newTask.priority"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                >
                  <option value="">Select priority</option>
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>
              
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Due Date</label>
                <input
                  v-model="newTask.due_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                />
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Estimated Hours</label>
              <input
                v-model="newTask.estimated_hours"
                type="number"
                step="0.5"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>

            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="showCreateModal = false"
                class="px-4 py-2 text-gray-600 hover:text-gray-800"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
              >
                {{ loading ? 'Creating...' : 'Create Task' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const loading = ref(false)
const showCreateModal = ref(false)
const searchQuery = ref('')
const statusFilter = ref('')
const priorityFilter = ref('')

const fields = ref([
  { id: 1, name: 'North Field' },
  { id: 2, name: 'South Field' },
  { id: 3, name: 'East Field' }
])

const tasks = ref([
  {
    id: 1,
    title: 'Apply herbicide to North Field',
    description: 'Apply pre-emergent herbicide to prevent weed growth',
    field_id: 1,
    field_name: 'North Field',
    priority: 'high',
    status: 'pending',
    due_date: '2024-04-01',
    assigned_to: 'John Smith',
    estimated_hours: 4,
    completed: false
  },
  {
    id: 2,
    title: 'Check irrigation system',
    description: 'Inspect and test irrigation system in all fields',
    field_id: null,
    field_name: null,
    priority: 'medium',
    status: 'in_progress',
    due_date: '2024-03-30',
    assigned_to: 'Mike Johnson',
    estimated_hours: 6,
    completed: false
  },
  {
    id: 3,
    title: 'Fertilizer application',
    description: 'Apply side-dress fertilizer to corn fields',
    field_id: 1,
    field_name: 'North Field',
    priority: 'high',
    status: 'completed',
    due_date: '2024-03-20',
    assigned_to: 'John Smith',
    estimated_hours: 3,
    completed: true
  }
])

const newTask = ref({
  title: '',
  description: '',
  field_id: '',
  priority: '',
  due_date: '',
  estimated_hours: ''
})

const filteredTasks = computed(() => {
  return tasks.value.filter(task => {
    const matchesSearch = task.title.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         task.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesStatus = !statusFilter.value || task.status === statusFilter.value
    const matchesPriority = !priorityFilter.value || task.priority === priorityFilter.value
    
    return matchesSearch && matchesStatus && matchesPriority
  })
})

const getPriorityBadgeClass = (priority) => {
  const classes = {
    low: 'bg-gray-100 text-gray-800',
    medium: 'bg-yellow-100 text-yellow-800',
    high: 'bg-orange-100 text-orange-800',
    urgent: 'bg-red-100 text-red-800'
  }
  return classes[priority] || 'bg-gray-100 text-gray-800'
}

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    overdue: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
  if (!date) return 'Not set'
  return new Date(date).toLocaleDateString()
}

const viewTask = (id) => {
  router.push(`/tasks/${id}`)
}

const editTask = (id) => {
  // Navigate to edit page or show edit modal
  console.log('Edit task:', id)
}

const toggleTask = (id) => {
  const task = tasks.value.find(t => t.id === id)
  if (task) {
    task.completed = !task.completed
    task.status = task.completed ? 'completed' : 'pending'
  }
}

const createTask = async () => {
  loading.value = true
  try {
    const field = fields.value.find(f => f.id == newTask.value.field_id)
    const task = {
      id: Date.now(),
      ...newTask.value,
      field_name: field?.name || null,
      status: 'pending',
      assigned_to: null,
      completed: false
    }
    tasks.value.push(task)
    
    // Reset form
    newTask.value = {
      title: '',
      description: '',
      field_id: '',
      priority: '',
      due_date: '',
      estimated_hours: ''
    }
    showCreateModal.value = false
  } catch (error) {
    console.error('Error creating task:', error)
  } finally {
    loading.value = false
  }
}

const clearFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  priorityFilter.value = ''
}

onMounted(() => {
  // Load tasks from API
})
</script>

<style scoped>
.tasks-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>