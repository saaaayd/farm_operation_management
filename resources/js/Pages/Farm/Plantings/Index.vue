<template>
  <div class="plantings-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Plantings</h1>
          <p class="text-gray-600 mt-2">Track and manage your crop plantings</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
        >
          Add New Planting
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
              placeholder="Search plantings..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="statusFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
            >
              <option value="">All Status</option>
              <option value="planned">Planned</option>
              <option value="planted">Planted</option>
              <option value="growing">Growing</option>
              <option value="harvested">Harvested</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Crop Type</label>
            <select
              v-model="cropFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
            >
              <option value="">All Crops</option>
              <option value="corn">Corn</option>
              <option value="wheat">Wheat</option>
              <option value="soybeans">Soybeans</option>
              <option value="rice">Rice</option>
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

      <!-- Plantings Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="planting in filteredPlantings"
          :key="planting.id"
          class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
        >
          <div class="p-6">
            <div class="flex justify-between items-start mb-4">
              <h3 class="text-xl font-semibold text-gray-900">{{ planting.crop_type }}</h3>
              <span
                :class="getStatusBadgeClass(planting.status)"
                class="px-2 py-1 text-xs font-medium rounded-full"
              >
                {{ planting.status }}
              </span>
            </div>
            
            <div class="space-y-2 mb-4">
              <div class="flex justify-between">
                <span class="text-gray-600">Field:</span>
                <span class="font-medium">{{ planting.field_name }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Variety:</span>
                <span class="font-medium">{{ planting.variety }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Planted:</span>
                <span class="font-medium">{{ formatDate(planting.planted_date) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Expected Harvest:</span>
                <span class="font-medium">{{ formatDate(planting.expected_harvest) }}</span>
              </div>
            </div>

            <!-- Progress Bar -->
            <div class="mb-4">
              <div class="flex justify-between text-sm text-gray-600 mb-1">
                <span>Growth Progress</span>
                <span>{{ planting.growth_progress }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-green-600 h-2 rounded-full transition-all duration-300"
                  :style="{ width: `${planting.growth_progress}%` }"
                ></div>
              </div>
            </div>

            <div class="flex space-x-2">
              <button
                @click="viewPlanting(planting.id)"
                class="flex-1 bg-green-600 text-white px-3 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
              >
                View Details
              </button>
              <button
                @click="editPlanting(planting.id)"
                class="flex-1 bg-gray-600 text-white px-3 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 text-sm"
              >
                Edit
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredPlantings.length === 0" class="text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">🌱</div>
        <h3 class="text-xl font-medium text-gray-900 mb-2">No plantings found</h3>
        <p class="text-gray-600 mb-6">Get started by adding your first planting</p>
        <button
          @click="showCreateModal = true"
          class="bg-green-600 text-white px-6 py-3 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
        >
          Add Your First Planting
        </button>
      </div>

      <!-- Create Planting Modal -->
      <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
          <h2 class="text-xl font-semibold mb-4">Add New Planting</h2>
          
          <form @submit.prevent="createPlanting" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Field</label>
              <select
                v-model="newPlanting.field_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              >
                <option value="">Select field</option>
                <option v-for="field in fields" :key="field.id" :value="field.id">
                  {{ field.name }}
                </option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Crop Type</label>
              <select
                v-model="newPlanting.crop_type"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              >
                <option value="">Select crop</option>
                <option value="corn">Corn</option>
                <option value="wheat">Wheat</option>
                <option value="soybeans">Soybeans</option>
                <option value="rice">Rice</option>
              </select>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Variety</label>
              <input
                v-model="newPlanting.variety"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Planted Date</label>
              <input
                v-model="newPlanting.planted_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                required
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Expected Harvest Date</label>
              <input
                v-model="newPlanting.expected_harvest"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                required
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
                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50"
              >
                {{ loading ? 'Creating...' : 'Create Planting' }}
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
const cropFilter = ref('')

const fields = ref([
  { id: 1, name: 'North Field' },
  { id: 2, name: 'South Field' },
  { id: 3, name: 'East Field' }
])

const plantings = ref([
  {
    id: 1,
    field_id: 1,
    field_name: 'North Field',
    crop_type: 'corn',
    variety: 'Pioneer 1234',
    planted_date: '2024-03-15',
    expected_harvest: '2024-08-15',
    status: 'growing',
    growth_progress: 45
  },
  {
    id: 2,
    field_id: 2,
    field_name: 'South Field',
    crop_type: 'wheat',
    variety: 'Winter Wheat 2024',
    planted_date: '2024-02-20',
    expected_harvest: '2024-06-15',
    status: 'growing',
    growth_progress: 75
  },
  {
    id: 3,
    field_id: 3,
    field_name: 'East Field',
    crop_type: 'soybeans',
    variety: 'Roundup Ready',
    planted_date: '2024-04-01',
    expected_harvest: '2024-09-15',
    status: 'planted',
    growth_progress: 15
  }
])

const newPlanting = ref({
  field_id: '',
  crop_type: '',
  variety: '',
  planted_date: '',
  expected_harvest: ''
})

const filteredPlantings = computed(() => {
  return plantings.value.filter(planting => {
    const matchesSearch = planting.crop_type.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         planting.variety.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         planting.field_name.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesStatus = !statusFilter.value || planting.status === statusFilter.value
    const matchesCrop = !cropFilter.value || planting.crop_type === cropFilter.value
    
    return matchesSearch && matchesStatus && matchesCrop
  })
})

const getStatusBadgeClass = (status) => {
  const classes = {
    planned: 'bg-blue-100 text-blue-800',
    planted: 'bg-yellow-100 text-yellow-800',
    growing: 'bg-green-100 text-green-800',
    harvested: 'bg-purple-100 text-purple-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
  if (!date) return 'Not set'
  return new Date(date).toLocaleDateString()
}

const viewPlanting = (id) => {
  router.push(`/plantings/${id}`)
}

const editPlanting = (id) => {
  // Navigate to edit page or show edit modal
  console.log('Edit planting:', id)
}

const createPlanting = async () => {
  loading.value = true
  try {
    const field = fields.value.find(f => f.id == newPlanting.value.field_id)
    const planting = {
      id: Date.now(),
      ...newPlanting.value,
      field_name: field?.name || '',
      status: 'planted',
      growth_progress: 0
    }
    plantings.value.push(planting)
    
    // Reset form
    newPlanting.value = {
      field_id: '',
      crop_type: '',
      variety: '',
      planted_date: '',
      expected_harvest: ''
    }
    showCreateModal.value = false
  } catch (error) {
    console.error('Error creating planting:', error)
  } finally {
    loading.value = false
  }
}

const clearFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  cropFilter.value = ''
}

onMounted(() => {
  // Load plantings from API
})
</script>

<style scoped>
.plantings-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>