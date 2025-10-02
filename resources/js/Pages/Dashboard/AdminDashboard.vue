<template>
  <div class="p-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Admin Dashboard</h1>
      <p class="text-gray-600 mt-2">System overview and management tools</p>
    </div>

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

        <!-- System Activity -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">System Activity</h2>
          </div>
          <div class="p-6">
            <div class="space-y-4">
              <div v-for="activity in systemActivity" :key="activity.id" class="flex items-start space-x-3">
                <div class="flex-shrink-0">
                  <div class="w-2 h-2 bg-blue-600 rounded-full mt-2"></div>
                </div>
                <div class="flex-1">
                  <p class="text-sm text-gray-900">{{ activity.description }}</p>
                  <p class="text-xs text-gray-500">{{ formatDate(activity.created_at) }}</p>
                </div>
                <div class="flex-shrink-0">
                  <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                        :class="getActivityTypeClass(activity.type)">
                    {{ activity.type }}
                  </span>
                </div>
              </div>
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
</template>

<script setup>
import { ref, onMounted } from 'vue';
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

// Reactive data
const stats = ref({});
const recentUsers = ref([]);
const systemActivity = ref([]);
const systemAlerts = ref([]);
const loading = ref(true);

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
  // Implement data export functionality
  console.log('Exporting system data...');
};

const loadDashboardData = async () => {
  try {
    loading.value = true;
    
    // Mock data for admin dashboard
    stats.value = {
      total_users: 156,
      active_users: 142,
      total_farmers: 89,
      active_farmers: 76,
      total_fields: 234,
      total_area: 1250.5,
      total_orders: 1834,
      pending_orders: 23,
    };
    
    recentUsers.value = [
      {
        id: 1,
        name: 'John Farmer',
        email: 'john@example.com',
        role: 'farmer',
        created_at: '2024-10-01',
        is_active: true,
      },
      {
        id: 2,
        name: 'Jane Buyer',
        email: 'jane@example.com',
        role: 'user',
        created_at: '2024-09-30',
        is_active: true,
      },
    ];
    
    systemActivity.value = [
      {
        id: 1,
        description: 'New farmer registered: John Farmer',
        type: 'user_registration',
        created_at: '2024-10-02',
      },
      {
        id: 2,
        description: 'Rice farm created in North Region',
        type: 'farm_creation',
        created_at: '2024-10-01',
      },
    ];
    
    systemAlerts.value = [
      {
        id: 1,
        title: 'Weather API Rate Limit',
        message: 'Approaching daily API limit for weather service',
        level: 'warning',
      },
    ];
    
  } catch (error) {
    console.error('Error loading admin dashboard data:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadDashboardData();
});
</script>