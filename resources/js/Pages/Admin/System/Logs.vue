<template>
  <div class="admin-system-logs-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">System Logs</h1>
          <p class="text-gray-600 mt-2">Monitor system activity and troubleshoot issues</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="refreshLogs"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Refresh
          </button>
          <router-link
            to="/admin"
            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Back to Dashboard
          </router-link>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Log Level</label>
            <select
              v-model="filters.level"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Levels</option>
              <option value="error">Error</option>
              <option value="warning">Warning</option>
              <option value="info">Info</option>
              <option value="debug">Debug</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
            <input
              v-model="filters.dateFrom"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
            <input
              v-model="filters.dateTo"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
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

      <!-- Logs Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Timestamp
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Level
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Message
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Source
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="log in filteredLogs" :key="log.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDateTime(log.timestamp) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="getLogLevelClass(log.level)"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ log.level.toUpperCase() }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  <div class="max-w-xs truncate" :title="log.message">
                    {{ log.message }}
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ log.source }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredLogs.length === 0" class="text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">📋</div>
        <h3 class="text-xl font-medium text-gray-900 mb-2">No logs found</h3>
        <p class="text-gray-600 mb-6">Try adjusting your filters or check back later</p>
      </div>

      <!-- Pagination -->
      <div v-if="filteredLogs.length > 0" class="mt-6 flex justify-center">
        <nav class="flex items-center space-x-2">
          <button
            @click="previousPage"
            :disabled="currentPage === 1"
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50"
          >
            Previous
          </button>
          <span class="px-3 py-2 text-sm text-gray-700">
            Page {{ currentPage }} of {{ totalPages }}
          </span>
          <button
            @click="nextPage"
            :disabled="currentPage === totalPages"
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50"
          >
            Next
          </button>
        </nav>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

const currentPage = ref(1)
const itemsPerPage = 20

const filters = ref({
  level: '',
  dateFrom: '',
  dateTo: ''
})

const logs = ref([
  {
    id: 1,
    timestamp: '2024-10-10T10:30:00Z',
    level: 'info',
    message: 'User login successful: admin@ricefarm.com',
    source: 'AuthController'
  },
  {
    id: 2,
    timestamp: '2024-10-10T10:25:00Z',
    level: 'warning',
    message: 'High CPU usage detected: 85%',
    source: 'SystemMonitor'
  },
  {
    id: 3,
    timestamp: '2024-10-10T10:20:00Z',
    level: 'error',
    message: 'Database connection timeout',
    source: 'DatabaseService'
  },
  {
    id: 4,
    timestamp: '2024-10-10T10:15:00Z',
    level: 'info',
    message: 'New user registered: john@example.com',
    source: 'UserController'
  },
  {
    id: 5,
    timestamp: '2024-10-10T10:10:00Z',
    level: 'debug',
    message: 'Cache cleared successfully',
    source: 'CacheService'
  }
])

const filteredLogs = computed(() => {
  let filtered = logs.value

  if (filters.value.level) {
    filtered = filtered.filter(log => log.level === filters.value.level)
  }

  if (filters.value.dateFrom) {
    filtered = filtered.filter(log => 
      new Date(log.timestamp) >= new Date(filters.value.dateFrom)
    )
  }

  if (filters.value.dateTo) {
    filtered = filtered.filter(log => 
      new Date(log.timestamp) <= new Date(filters.value.dateTo)
    )
  }

  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filtered.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(logs.value.length / itemsPerPage)
})

const getLogLevelClass = (level) => {
  const classes = {
    error: 'bg-red-100 text-red-800',
    warning: 'bg-yellow-100 text-yellow-800',
    info: 'bg-blue-100 text-blue-800',
    debug: 'bg-gray-100 text-gray-800'
  }
  return classes[level] || 'bg-gray-100 text-gray-800'
}

const formatDateTime = (date) => {
  return new Date(date).toLocaleString()
}

const refreshLogs = () => {
  alert('Logs refreshed!')
}

const clearFilters = () => {
  filters.value = {
    level: '',
    dateFrom: '',
    dateTo: ''
  }
  currentPage.value = 1
}

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}
</script>

<style scoped>
.admin-system-logs-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>