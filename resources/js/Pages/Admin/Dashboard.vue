<template>
  <div class="admin-dashboard-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
          <p class="text-gray-600 mt-2">System overview and management</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="refreshData"
            :disabled="loading"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
          >
            {{ loading ? 'Refreshing...' : 'Refresh' }}
          </button>
          <button
            @click="exportData"
            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            Export Data
          </button>
          <button
            @click="handleLogout"
            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
          >
            Logout
          </button>
        </div>
      </div>

      <!-- Key Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-blue-600 text-2xl">👥</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ metrics.totalUsers }}</div>
              <div class="text-sm text-gray-600">Total Users</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <span class="text-green-600 text-2xl">🌾</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ metrics.totalFields }}</div>
              <div class="text-sm text-gray-600">Total Fields</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <span class="text-yellow-600 text-2xl">🛒</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ metrics.totalOrders }}</div>
              <div class="text-sm text-gray-600">Total Orders</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <span class="text-purple-600 text-2xl">💰</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(metrics.totalRevenue) }}</div>
              <div class="text-sm text-gray-600">Total Revenue</div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Recent Activity -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Recent Activity</h2>
            <div class="space-y-4">
              <div
                v-for="activity in recentActivity"
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
                  <div class="text-xs text-gray-500 mt-1">{{ formatDateTime(activity.date) }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- System Health -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">System Health</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h3 class="font-medium text-gray-900 mb-3">Server Status</h3>
                <div class="space-y-3">
                  <div class="flex items-center justify-between">
                    <span class="text-gray-600">API Server</span>
                    <span class="flex items-center">
                      <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                      <span class="text-sm text-green-600">Online</span>
                    </span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-gray-600">Database</span>
                    <span class="flex items-center">
                      <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                      <span class="text-sm text-green-600">Online</span>
                    </span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-gray-600">Weather API</span>
                    <span class="flex items-center">
                      <div class="w-2 h-2 bg-green-500 rounded-full mr-2"></div>
                      <span class="text-sm text-green-600">Online</span>
                    </span>
                  </div>
                </div>
              </div>
              <div>
                <h3 class="font-medium text-gray-900 mb-3">Performance</h3>
                <div class="space-y-3">
                  <div class="flex items-center justify-between">
                    <span class="text-gray-600">Response Time</span>
                    <span class="text-sm font-medium">245ms</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-gray-600">Uptime</span>
                    <span class="text-sm font-medium">99.9%</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-gray-600">Active Users</span>
                    <span class="text-sm font-medium">{{ metrics.activeUsers }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- User Growth Chart -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">User Growth (Last 30 Days)</h2>
            <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
              <span class="text-gray-500">User growth chart placeholder</span>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Quick Actions -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
            <div class="space-y-3">
              <button
                @click="manageUsers"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                👥 Manage Users
              </button>
              <button
                @click="viewSystemStats"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                📊 View System Stats
              </button>
              <button
                @click="manageSettings"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                ⚙️ System Settings
              </button>
              <button
                @click="viewLogs"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                📋 View Logs
              </button>
              <button
                @click="backupData"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                💾 Backup Data
              </button>
            </div>
          </div>

          <!-- Recent Users -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Recent Users</h3>
            <div class="space-y-3">
              <div
                v-for="user in recentUsers"
                :key="user.id"
                class="flex items-center space-x-3"
              >
                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                  <span class="text-gray-600 text-sm">{{ user.name.charAt(0) }}</span>
                </div>
                <div class="flex-1">
                  <div class="font-medium text-gray-900 text-sm">{{ user.name }}</div>
                  <div class="text-xs text-gray-600">{{ user.role }}</div>
                </div>
                <div class="text-xs text-gray-500">{{ formatDate(user.joined) }}</div>
              </div>
            </div>
          </div>

          <!-- System Alerts -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">System Alerts</h3>
            <div class="space-y-3">
              <div
                v-for="alert in systemAlerts"
                :key="alert.id"
                :class="getAlertClass(alert.severity)"
                class="p-3 rounded-lg border-l-4"
              >
                <div class="font-medium text-sm">{{ alert.title }}</div>
                <div class="text-xs text-gray-600 mt-1">{{ alert.description }}</div>
              </div>
            </div>
          </div>

          <!-- Storage Usage -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Storage Usage</h3>
            <div class="space-y-3">
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-gray-600">Database</span>
                  <span class="font-medium">2.4 GB / 10 GB</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div class="bg-blue-600 h-2 rounded-full" style="width: 24%"></div>
                </div>
              </div>
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-gray-600">Files</span>
                  <span class="font-medium">1.8 GB / 50 GB</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div class="bg-green-600 h-2 rounded-full" style="width: 3.6%"></div>
                </div>
              </div>
              <div>
                <div class="flex justify-between text-sm mb-1">
                  <span class="text-gray-600">Backups</span>
                  <span class="font-medium">5.2 GB / 100 GB</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div class="bg-yellow-600 h-2 rounded-full" style="width: 5.2%"></div>
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
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth';
import { formatCurrency } from '@/utils/format'

const auth = useAuthStore();
const router = useRouter()
const loading = ref(false)

const metrics = ref({
  totalUsers: 1247,
  totalFields: 3421,
  totalOrders: 856,
  totalRevenue: 125000,
  activeUsers: 89
})

const recentActivity = ref([
  {
    id: 1,
    type: 'user',
    title: 'New user registered',
    description: 'John Smith joined the platform',
    date: '2024-03-25T10:00:00Z'
  },
  {
    id: 2,
    type: 'order',
    title: 'New order placed',
    description: 'Order #ORD-001 for ₱450.00',
    date: '2024-03-25T09:30:00Z'
  },
  {
    id: 3,
    type: 'field',
    title: 'New field added',
    description: 'North Field added by Mike Johnson',
    date: '2024-03-25T08:45:00Z'
  },
  {
    id: 4,
    type: 'system',
    title: 'System backup completed',
    description: 'Daily backup completed successfully',
    date: '2024-03-25T06:00:00Z'
  }
])

const recentUsers = ref([
  {
    id: 1,
    name: 'John Smith',
    role: 'Farmer',
    joined: '2024-03-25T10:00:00Z'
  },
  {
    id: 2,
    name: 'Sarah Wilson',
    role: 'Buyer',
    joined: '2024-03-24T14:30:00Z'
  },
  {
    id: 3,
    name: 'Mike Johnson',
    role: 'Farmer',
    joined: '2024-03-24T09:15:00Z'
  }
])

const systemAlerts = ref([
  {
    id: 1,
    title: 'High CPU Usage',
    description: 'Server CPU usage is at 85%',
    severity: 'warning'
  },
  {
    id: 2,
    title: 'Database Backup',
    description: 'Scheduled backup completed',
    severity: 'info'
  }
])

const getActivityIcon = (type) => {
  const icons = {
    user: '👤',
    order: '🛒',
    field: '🌾',
    system: '⚙️'
  }
  return icons[type] || '📝'
}

const getAlertClass = (severity) => {
  const classes = {
    warning: 'bg-yellow-50 border-yellow-400',
    info: 'bg-blue-50 border-blue-400',
    danger: 'bg-red-50 border-red-400'
  }
  return classes[severity] || 'bg-gray-50 border-gray-400'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const formatDateTime = (date) => {
  return new Date(date).toLocaleString()
}

const refreshData = async () => {
  loading.value = true
  try {
    // API call to refresh data
    await new Promise(resolve => setTimeout(resolve, 1000)) // Simulate API call
  } catch (error) {
    console.error('Error refreshing data:', error)
  } finally {
    loading.value = false
  }
}

const exportData = () => {
  try {
    // Create sample CSV data
    const csvData = [
      ['Metric', 'Value'],
      ['Total Users', metrics.value.totalUsers],
      ['Total Fields', metrics.value.totalFields],
      ['Total Orders', metrics.value.totalOrders],
      ['Total Revenue', formatCurrency(metrics.value.totalRevenue)],
      ['Active Users', metrics.value.activeUsers]
    ]
    
    const csvContent = csvData.map(row => row.join(',')).join('\n')
    const blob = new Blob([csvContent], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    
    const a = document.createElement('a')
    a.href = url
    a.download = `admin-dashboard-export-${new Date().toISOString().split('T')[0]}.csv`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)
    
    alert('Dashboard data exported successfully!')
  } catch (error) {
    console.error('Export error:', error)
    alert('Export failed. Please try again.')
  }
}

const manageUsers = () => {
  router.push('/admin/users')
}

const viewSystemStats = () => {
  router.push('/admin/stats')
}

const manageSettings = () => {
  router.push('/admin/system')
}

const viewLogs = () => {
  router.push('/admin/logs')
}

const backupData = async () => {
  try {
    // Show loading state
    loading.value = true
    
    // Simulate backup process
    await new Promise(resolve => setTimeout(resolve, 2000))
    
    // In a real app, this would call an API endpoint
    alert('System backup completed successfully!')
  } catch (error) {
    console.error('Backup error:', error)
    alert('Backup failed. Please try again.')
  } finally {
    loading.value = false
  }
}

const handleLogout = async () => {
  await auth.logout();
  router.push('/login');
};

onMounted(() => {
  // Load admin dashboard data from API
})
</script>

<style scoped>
.admin-dashboard-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>