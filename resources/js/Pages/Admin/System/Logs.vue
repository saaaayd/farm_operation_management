<template>
  <div class="admin-system-logs-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Activity Logs</h1>
          <p class="text-gray-600 mt-2">Monitor system activity and audit trails</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="loadLogs"
            :disabled="loading"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
          >
            {{ loading ? 'Loading...' : 'Refresh' }}
          </button>
          <router-link
            to="/admin"
            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700"
          >
            Back to Dashboard
          </router-link>
        </div>
      </div>

      <!-- Stats -->
      <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="text-2xl font-bold text-blue-600">{{ stats.total_logs || 0 }}</div>
          <div class="text-sm text-gray-600">Total Logs</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="text-2xl font-bold text-green-600">{{ stats.today_logs || 0 }}</div>
          <div class="text-sm text-gray-600">Today</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="text-2xl font-bold text-purple-600">{{ stats.this_week_logs || 0 }}</div>
          <div class="text-sm text-gray-600">This Week</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="text-2xl font-bold text-orange-600">{{ stats.this_month_logs || 0 }}</div>
          <div class="text-sm text-gray-600">This Month</div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Action</label>
            <input
              v-model="filters.action"
              type="text"
              placeholder="Filter by action..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">User</label>
            <input
              v-model="filters.user_id"
              type="number"
              placeholder="User ID"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Model Type</label>
            <input
              v-model="filters.model_type"
              type="text"
              placeholder="e.g., App\\Models\\User"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
            <input
              v-model="filters.start_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
            <input
              v-model="filters.end_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
        </div>
        <div class="mt-4 flex space-x-3">
          <button
            @click="applyFilters"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
          >
            Apply Filters
          </button>
          <button
            @click="clearFilters"
            class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400"
          >
            Clear
          </button>
        </div>
      </div>

      <!-- Logs Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div v-if="loading && !logs.length" class="p-12 text-center text-gray-500">
          <div class="mx-auto mb-4 h-10 w-10 animate-spin rounded-full border-b-2 border-blue-600"></div>
          Loading activity logs...
        </div>
        <div v-else-if="!loading && !logs.length" class="p-12 text-center text-gray-500">
          <p class="text-lg">No activity logs found</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Timestamp</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Model</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">IP Address</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="log in logs" :key="log.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDateTime(log.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ log.user?.name || 'System' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                    {{ log.action }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ log.model_type ? log.model_type.split('\\').pop() : '-' }}
                  <span v-if="log.model_id" class="text-gray-400">#{{ log.model_id }}</span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                  {{ log.description || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ log.ip_address || '-' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <!-- Pagination -->
        <div v-if="pagination" class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <div class="text-sm text-gray-600">
            Showing {{ pagination.from || 0 }} to {{ pagination.to || 0 }} of {{ pagination.total || 0 }} results
          </div>
          <div class="flex space-x-2">
            <button
              @click="loadLogs(pagination.current_page - 1)"
              :disabled="!pagination.prev_page_url"
              class="px-4 py-2 border border-gray-300 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
            >
              Previous
            </button>
            <button
              @click="loadLogs(pagination.current_page + 1)"
              :disabled="!pagination.next_page_url"
              class="px-4 py-2 border border-gray-300 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminAPI } from '@/services/api'

const loading = ref(false)
const logs = ref([])
const stats = ref(null)
const pagination = ref(null)
const filters = ref({
  action: '',
  user_id: '',
  model_type: '',
  start_date: '',
  end_date: '',
  search: '',
})

const formatDateTime = (date) => {
  return new Date(date).toLocaleString()
}

const loadLogs = async (page = 1) => {
  loading.value = true
  try {
    const params = {
      page,
      per_page: 50,
      ...Object.fromEntries(
        Object.entries(filters.value).filter(([_, v]) => v !== '')
      )
    }
    const response = await adminAPI.getActivityLogs(params)
    logs.value = response.data.data || []
    pagination.value = {
      current_page: response.data.current_page,
      from: response.data.from,
      to: response.data.to,
      total: response.data.total,
      prev_page_url: response.data.prev_page_url,
      next_page_url: response.data.next_page_url,
    }
  } catch (error) {
    console.error('Error loading logs:', error)
    alert('Failed to load activity logs')
  } finally {
    loading.value = false
  }
}

const loadStats = async () => {
  try {
    const response = await adminAPI.getActivityLogStats()
    stats.value = response.data
  } catch (error) {
    console.error('Error loading stats:', error)
  }
}

const applyFilters = () => {
  loadLogs(1)
}

const clearFilters = () => {
  filters.value = {
    action: '',
    user_id: '',
    model_type: '',
    start_date: '',
    end_date: '',
    search: '',
  }
  loadLogs(1)
}

onMounted(() => {
  loadLogs()
  loadStats()
})
</script>

<style scoped>
.admin-system-logs-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>
