<template>
  <div class="system-stats-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">System Statistics</h1>
          <p class="text-gray-600 mt-2">Detailed system performance and usage metrics</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="refreshStats"
            :disabled="loading"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
          >
            {{ loading ? 'Refreshing...' : 'Refresh' }}
          </button>
          <button
            @click="exportStats"
            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            Export Stats
          </button>
        </div>
      </div>

      <!-- Key Performance Indicators -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-blue-600 text-2xl">⚡</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ stats.responseTime }}ms</div>
              <div class="text-sm text-gray-600">Avg Response Time</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <span class="text-green-600 text-2xl">📈</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ stats.uptime }}%</div>
              <div class="text-sm text-gray-600">System Uptime</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <span class="text-yellow-600 text-2xl">💾</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ stats.cpuUsage }}%</div>
              <div class="text-sm text-gray-600">CPU Usage</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <span class="text-purple-600 text-2xl">🧠</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ stats.memoryUsage }}%</div>
              <div class="text-sm text-gray-600">Memory Usage</div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Performance Charts -->
        <div class="space-y-6">
          <!-- Response Time Chart -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Response Time (Last 24h)</h2>
            <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
              <LineChart v-if="responseTimeChartData.labels.length > 0" :data="responseTimeChartData" />
              <div v-else class="h-full flex items-center justify-center text-gray-500">
                No response time data available
              </div>
            </div>
          </div>

          <!-- CPU & Memory Usage -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">System Resources</h2>
            <div class="space-y-4">
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-gray-600">CPU Usage</span>
                  <span class="font-medium">{{ stats.cpuUsage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    :class="getUsageBarClass(stats.cpuUsage)"
                    class="h-2 rounded-full transition-all duration-300"
                    :style="{ width: `${stats.cpuUsage}%` }"
                  ></div>
                </div>
              </div>
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-gray-600">Memory Usage</span>
                  <span class="font-medium">{{ stats.memoryUsage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    :class="getUsageBarClass(stats.memoryUsage)"
                    class="h-2 rounded-full transition-all duration-300"
                    :style="{ width: `${stats.memoryUsage}%` }"
                  ></div>
                </div>
              </div>
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-gray-600">Disk Usage</span>
                  <span class="font-medium">{{ stats.diskUsage }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    :class="getUsageBarClass(stats.diskUsage)"
                    class="h-2 rounded-full transition-all duration-300"
                    :style="{ width: `${stats.diskUsage}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Usage Statistics -->
        <div class="space-y-6">
          <!-- User Activity -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">User Activity (Last 7 Days)</h2>
            <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
              <BarChart v-if="userActivityChartData.labels.length > 0" :data="userActivityChartData" />
              <div v-else class="h-full flex items-center justify-center text-gray-500">
                No user activity data available
              </div>
            </div>
          </div>

          <!-- Database Performance -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Database Performance</h2>
            <div class="space-y-4">
              <div class="flex justify-between">
                <span class="text-gray-600">Query Response Time:</span>
                <span class="font-medium">{{ stats.dbQueryTime }}ms</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Active Connections:</span>
                <span class="font-medium">{{ stats.dbConnections }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Database Size:</span>
                <span class="font-medium">{{ stats.dbSize }} GB</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Cache Hit Rate:</span>
                <span class="font-medium">{{ stats.cacheHitRate }}%</span>
              </div>
            </div>
          </div>

          <!-- API Usage -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">API Usage (Last 24h)</h2>
            <div class="space-y-3">
              <div
                v-for="endpoint in apiUsage"
                :key="endpoint.name"
                class="flex justify-between items-center"
              >
                <span class="text-gray-600">{{ endpoint.name }}</span>
                <div class="flex items-center space-x-2">
                  <div class="w-16 bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-blue-600 h-2 rounded-full"
                      :style="{ width: `${(endpoint.requests / 1000) * 100}%` }"
                    ></div>
                  </div>
                  <span class="text-sm font-medium">{{ endpoint.requests }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Error Logs -->
      <div class="mt-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold mb-4">Recent Errors</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="error in recentErrors" :key="error.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDateTime(error.timestamp) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="getErrorLevelClass(error.level)"
                      class="px-2 py-1 text-xs font-medium rounded-full"
                    >
                      {{ error.level }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ error.message }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ error.source }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { adminAPI } from '@/services/api'
import LineChart from '@/Components/Charts/LineChart.vue'
import BarChart from '@/Components/Charts/BarChart.vue'

const loading = ref(false)

const stats = ref({
  responseTime: 245,
  uptime: 99.9,
  cpuUsage: 65,
  memoryUsage: 78,
  diskUsage: 45,
  dbQueryTime: 12,
  dbConnections: 24,
  dbSize: 2.4,
  cacheHitRate: 94
})

const apiUsage = ref([
  { name: '/api/users', requests: 1250 },
  { name: '/api/fields', requests: 890 },
  { name: '/api/weather', requests: 2100 },
  { name: '/api/marketplace', requests: 650 },
  { name: '/api/inventory', requests: 420 }
])

const recentErrors = ref([
  {
    id: 1,
    timestamp: '2024-03-25T10:30:00Z',
    level: 'ERROR',
    message: 'Database connection timeout',
    source: 'Database'
  },
  {
    id: 2,
    timestamp: '2024-03-25T09:15:00Z',
    level: 'WARNING',
    message: 'High memory usage detected',
    source: 'System'
  },
  {
    id: 3,
    timestamp: '2024-03-25T08:45:00Z',
    level: 'ERROR',
    message: 'API rate limit exceeded',
    source: 'Weather API'
  },
  {
    id: 4,
    timestamp: '2024-03-25T07:20:00Z',
    level: 'INFO',
    message: 'Scheduled backup completed',
    source: 'Backup'
  }
])

const getUsageBarClass = (usage) => {
  if (usage < 50) return 'bg-green-600'
  if (usage < 80) return 'bg-yellow-600'
  return 'bg-red-600'
}

const getErrorLevelClass = (level) => {
  const classes = {
    ERROR: 'bg-red-100 text-red-800',
    WARNING: 'bg-yellow-100 text-yellow-800',
    INFO: 'bg-blue-100 text-blue-800'
  }
  return classes[level] || 'bg-gray-100 text-gray-800'
}

const formatDateTime = (date) => {
  return new Date(date).toLocaleString()
}

const refreshStats = async () => {
  loading.value = true
  try {
    // API call to refresh stats
    await new Promise(resolve => setTimeout(resolve, 1000)) // Simulate API call
  } catch (error) {
    console.error('Error refreshing stats:', error)
  } finally {
    loading.value = false
  }
}

const exportStats = () => {
  // Export stats logic
  console.log('Export stats')
}

// Chart data computed properties
const responseTimeChartData = computed(() => {
  // Placeholder - would be populated from actual system stats
  return {
    labels: [],
    datasets: []
  }
})

const userActivityChartData = computed(() => {
  // Placeholder - would be populated from actual system stats
  return {
    labels: [],
    datasets: []
  }
})

onMounted(() => {
  // Load system stats from API
  loadSystemStats()
})

const loadSystemStats = async () => {
  try {
    loading.value = true
    const response = await adminAPI.getSystemStats()
    const data = response.data.data || response.data
    // Update stats from API response
    // Chart data would be populated from response
  } catch (error) {
    console.error('Error loading system stats:', error)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.system-stats-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>