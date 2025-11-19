<template>
  <div class="p-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
      <p class="text-gray-600 mt-2">System overview and management tools</p>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
      <p class="mt-4 text-gray-600">Loading dashboard data...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <ExclamationTriangleIcon class="h-5 w-5 text-red-400" />
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Error Loading Dashboard</h3>
          <p class="mt-1 text-sm text-red-700">{{ error }}</p>
          <button
            @click="loadDashboardData"
            class="mt-3 text-sm font-medium text-red-800 hover:text-red-900 underline"
          >
            Try again
          </button>
        </div>
      </div>
    </div>

    <!-- Dashboard Content -->
    <div v-else>

    <!-- System Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <UsersIcon class="h-8 w-8 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Users</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.total_users || 0 }}</p>
            <p class="text-xs text-gray-500">{{ stats.active_users || 0 }} active</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <UserGroupIcon class="h-8 w-8 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Farmers</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.total_farmers || 0 }}</p>
            <p class="text-xs text-gray-500">{{ stats.active_farmers || 0 }} with active farms</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <GlobeAltIcon class="h-8 w-8 text-purple-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Rice Fields</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.total_fields || 0 }}</p>
            <p class="text-xs text-gray-500">{{ stats.total_area || 0 }} hectares</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ShoppingCartIcon class="h-8 w-8 text-orange-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Marketplace Orders</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.total_orders || 0 }}</p>
            <p class="text-xs text-gray-500">{{ stats.pending_orders || 0 }} pending</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Column -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Recent Users -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-900">Recent Users</h2>
            <router-link
              to="/admin/users"
              class="text-sm text-green-600 hover:text-green-700 font-medium"
            >
              View all
            </router-link>
          </div>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Role</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="user in recentUsers" :key="user.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="flex items-center">
                      <div class="flex-shrink-0 h-10 w-10">
                        <div class="h-10 w-10 rounded-full bg-gray-300 flex items-center justify-center">
                          <UserIcon class="h-6 w-6 text-gray-600" />
                        </div>
                      </div>
                      <div class="ml-4">
                        <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                        <div class="text-sm text-gray-500">{{ user.email }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                          :class="getRoleClass(user.role)">
                      {{ user.role }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(user.created_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                          :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                      {{ user.is_active ? 'Active' : 'Inactive' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- User Growth Chart -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">User Growth (Last 30 Days)</h2>
          </div>
          <div class="p-6" style="height: 300px;">
            <LineChart v-if="userGrowthChartData.labels.length > 0" :data="userGrowthChartData" />
            <div v-else class="text-center text-gray-500 py-8">
              <p>No user growth data available</p>
            </div>
          </div>
        </div>

        <!-- Sales Trends Chart -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Sales Trends (Last 12 Months)</h2>
          </div>
          <div class="p-6" style="height: 300px;">
            <LineChart v-if="salesTrendsChartData.labels.length > 0" :data="salesTrendsChartData" />
            <div v-else class="text-center text-gray-500 py-8">
              <p>No sales data available</p>
            </div>
          </div>
        </div>

        <!-- Expense Trends Chart -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Expense Trends (Last 12 Months)</h2>
          </div>
          <div class="p-6" style="height: 300px;">
            <LineChart v-if="expenseTrendsChartData.labels.length > 0" :data="expenseTrendsChartData" />
            <div v-else class="text-center text-gray-500 py-8">
              <p>No expense data available</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="space-y-8">
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
          </div>
          <div class="p-6 space-y-3">
            <router-link
              to="/admin/users/create"
              class="flex items-center p-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <UserPlusIcon class="w-5 h-5 mr-3 text-blue-600" />
              Add New User
            </router-link>
            
            <router-link
              to="/admin/system/settings"
              class="flex items-center p-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <CogIcon class="w-5 h-5 mr-3 text-gray-600" />
              System Settings
            </router-link>
            
            <router-link
              to="/admin/reports"
              class="flex items-center p-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <ChartBarIcon class="w-5 h-5 mr-3 text-green-600" />
              Generate Reports
            </router-link>
            
            <button
              @click="exportData"
              class="flex items-center w-full p-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <ArrowDownTrayIcon class="w-5 h-5 mr-3 text-purple-600" />
              Export Data
            </button>
          </div>
        </div>

        <!-- System Health -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">System Health</h2>
          </div>
          <div class="p-6 space-y-4">
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Database</span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <div class="w-2 h-2 bg-green-600 rounded-full mr-1"></div>
                Healthy
              </span>
            </div>
            
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">API Services</span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <div class="w-2 h-2 bg-green-600 rounded-full mr-1"></div>
                Online
              </span>
            </div>
            
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Weather Service</span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                <div class="w-2 h-2 bg-yellow-600 rounded-full mr-1"></div>
                Limited
              </span>
            </div>
            
            <div class="flex items-center justify-between">
              <span class="text-sm text-gray-600">Storage</span>
              <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                <div class="w-2 h-2 bg-green-600 rounded-full mr-1"></div>
                85% Available
              </span>
            </div>
          </div>
        </div>

        <!-- Recent Alerts -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">System Alerts</h2>
          </div>
          <div class="p-6">
            <div v-if="systemAlerts.length === 0" class="text-center py-4">
              <CheckCircleIcon class="mx-auto h-8 w-8 text-green-500" />
              <p class="mt-2 text-sm text-gray-500">No active alerts</p>
            </div>
            
            <div v-else class="space-y-3">
              <div
                v-for="alert in systemAlerts"
                :key="alert.id"
                class="flex items-start space-x-3 p-3 rounded-lg"
                :class="getAlertClass(alert.level)"
              >
                <ExclamationTriangleIcon class="w-5 h-5 flex-shrink-0 mt-0.5" />
                <div>
                  <p class="text-sm font-medium">{{ alert.title }}</p>
                  <p class="text-xs opacity-75">{{ alert.message }}</p>
                </div>
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
import { ref, onMounted, computed } from 'vue';
import {
  UsersIcon,
  UserGroupIcon,
  GlobeAltIcon,
  ShoppingCartIcon,
  UserIcon,
  UserPlusIcon,
  CogIcon,
  ChartBarIcon,
  ArrowDownTrayIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';
import { adminAPI } from '@/services/api';
import LineChart from '@/Components/Charts/LineChart.vue';

// Reactive data
const stats = ref({});
const recentUsers = ref([]);
const recentOrders = ref([]);
const recentProducts = ref([]);
const userGrowth = ref(null);
const salesTrends = ref(null);
const expenseTrends = ref(null);
const systemAlerts = ref([]);
const loading = ref(true);
const error = ref(null);

// Methods
const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString();
};

const getRoleClass = (role) => {
  const roleClasses = {
    'admin': 'bg-red-100 text-red-800',
    'farmer': 'bg-green-100 text-green-800',
    'user': 'bg-blue-100 text-blue-800',
  };
  return roleClasses[role] || 'bg-gray-100 text-gray-800';
};

const getActivityTypeClass = (type) => {
  const typeClasses = {
    'user_registration': 'bg-blue-100 text-blue-800',
    'farm_creation': 'bg-green-100 text-green-800',
    'order_placed': 'bg-orange-100 text-orange-800',
    'system_update': 'bg-purple-100 text-purple-800',
  };
  return typeClasses[type] || 'bg-gray-100 text-gray-800';
};

const getAlertClass = (level) => {
  const alertClasses = {
    'critical': 'bg-red-50 text-red-700',
    'warning': 'bg-yellow-50 text-yellow-700',
    'info': 'bg-blue-50 text-blue-700',
  };
  return alertClasses[level] || 'bg-gray-50 text-gray-700';
};

const exportData = () => {
  try {
    // Create comprehensive system data export
    const exportData = {
      stats: stats.value,
      recentUsers: recentUsers.value,
      systemActivity: systemActivity.value,
      systemAlerts: systemAlerts.value,
      exportDate: new Date().toISOString()
    }
    
    const jsonContent = JSON.stringify(exportData, null, 2)
    const blob = new Blob([jsonContent], { type: 'application/json' })
    const url = window.URL.createObjectURL(blob)
    
    const a = document.createElement('a')
    a.href = url
    a.download = `system-data-export-${new Date().toISOString().split('T')[0]}.json`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)
    
    console.log('System data exported successfully');
  } catch (error) {
    console.error('Export error:', error)
    alert('Export failed. Please try again.')
  }
};

const loadDashboardData = async () => {
  try {
    loading.value = true;
    error.value = null;
    
    const response = await adminAPI.getDashboard();
    const data = response.data;
    
    // Map API response to component data
    stats.value = {
      total_users: data.stats?.total_users || 0,
      active_users: data.stats?.active_users || 0,
      total_farmers: data.stats?.total_farmers || 0,
      active_farmers: data.stats?.total_farmers || 0, // Use total_farmers as active
      total_buyers: data.stats?.total_buyers || 0,
      total_fields: data.stats?.total_fields || 0,
      active_plantings: data.stats?.active_plantings || 0,
      total_products: data.stats?.total_products || 0,
      approved_products: data.stats?.approved_products || 0,
      pending_products: data.stats?.pending_products || 0,
      total_orders: data.stats?.total_orders || 0,
      pending_orders: data.stats?.pending_orders || 0,
      completed_orders: data.stats?.completed_orders || 0,
      total_revenue: data.stats?.total_revenue || 0,
      total_sales_volume: data.stats?.total_sales_volume || 0,
      total_expenses: data.stats?.total_expenses || 0,
      monthly_sales: data.stats?.monthly_sales || 0,
      monthly_expenses: data.stats?.monthly_expenses || 0,
      total_inventory_items: data.stats?.total_inventory_items || 0,
      low_stock_items: data.stats?.low_stock_items || 0,
    };
    
    recentUsers.value = (data.recent_users || []).map(user => ({
      id: user.id,
      name: user.name,
      email: user.email,
      role: user.role,
      created_at: user.created_at,
      is_active: user.approval_status === 'approved',
      approval_status: user.approval_status,
    }));
    
    recentOrders.value = data.recent_orders || [];
    recentProducts.value = data.recent_products || [];
    userGrowth.value = data.user_growth || null;
    salesTrends.value = data.sales_trends || null;
    expenseTrends.value = data.expense_trends || null;
    
    // Generate system alerts based on data
    systemAlerts.value = [];
    if (data.stats?.pending_users > 10) {
      systemAlerts.value.push({
        id: 1,
        title: 'High Pending Users',
        message: `${data.stats.pending_users} users awaiting approval`,
        level: 'warning',
      });
    }
    if (data.stats?.low_stock_items > 20) {
      systemAlerts.value.push({
        id: 2,
        title: 'Low Stock Alert',
        message: `${data.stats.low_stock_items} inventory items are low on stock`,
        level: 'warning',
      });
    }
    
  } catch (err) {
    console.error('Error loading admin dashboard data:', err);
    error.value = err.response?.data?.message || 'Failed to load dashboard data';
    // Set empty defaults on error
    stats.value = {};
    recentUsers.value = [];
    recentOrders.value = [];
    recentProducts.value = [];
  } finally {
    loading.value = false;
  }
};

// Chart data for user growth
const userGrowthChartData = computed(() => {
  if (!userGrowth.value || !userGrowth.value.labels) {
    return {
      labels: [],
      datasets: []
    };
  }
  
  return {
    labels: userGrowth.value.labels,
    datasets: [
      {
        label: 'Total Users',
        data: userGrowth.value.cumulative_users,
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        fill: true,
        tension: 0.4,
      }
    ]
  };
});

// Chart data for sales trends
const salesTrendsChartData = computed(() => {
  if (!salesTrends.value || !salesTrends.value.labels) {
    return {
      labels: [],
      datasets: []
    };
  }
  
  return {
    labels: salesTrends.value.labels,
    datasets: [
      {
        label: 'Sales (₱)',
        data: salesTrends.value.data,
        borderColor: 'rgb(34, 197, 94)',
        backgroundColor: 'rgba(34, 197, 94, 0.1)',
        fill: true,
        tension: 0.4,
      }
    ]
  };
});

// Chart data for expense trends
const expenseTrendsChartData = computed(() => {
  if (!expenseTrends.value || !expenseTrends.value.labels) {
    return {
      labels: [],
      datasets: []
    };
  }
  
  return {
    labels: expenseTrends.value.labels,
    datasets: [
      {
        label: 'Expenses (₱)',
        data: expenseTrends.value.data,
        borderColor: 'rgb(239, 68, 68)',
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
        fill: true,
        tension: 0.4,
      }
    ]
  };
});

onMounted(() => {
  loadDashboardData();
});
</script>