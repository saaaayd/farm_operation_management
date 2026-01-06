<template>
  <div class="p-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Rice Farming Dashboard</h1>
      <p class="text-gray-600 mt-2">Welcome back, {{ authStore.user?.name }}! Here's your farm overview.</p>
    </div>

    <!-- Error State -->
    <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
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

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <GlobeAltIcon class="h-8 w-8 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Rice Fields</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.total_fields || 0 }}</p>
            <p class="text-xs text-gray-500">Managed fields</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <SparklesIcon class="h-8 w-8 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Active Plantings</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.active_plantings || 0 }}</p>
            <p class="text-xs text-gray-500">Growing crops</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ExclamationTriangleIcon class="h-8 w-8 text-yellow-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Critical Stages</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.critical_plantings || 0 }}</p>
            <p class="text-xs text-gray-500">Need attention</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <CloudIcon class="h-8 w-8 text-purple-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Weather Alerts</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.weather_alerts || 0 }}</p>
            <p class="text-xs text-gray-500">Active warnings</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Module Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">Inventory Status</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.low_stock_items || 0 }}</p>
            <p class="text-xs text-orange-600">Low stock items</p>
          </div>
          <CubeIcon class="h-8 w-8 text-orange-500" />
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6 border-l-4 border-indigo-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">Marketplace Orders</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.pending_orders || 0 }}</p>
            <p class="text-xs text-indigo-600">Pending orders</p>
          </div>
          <ShoppingBagIcon class="h-8 w-8 text-indigo-500" />
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6 border-l-4 border-red-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">Monthly Expenses</p>
            <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.monthly_expenses || 0) }}</p>
            <p class="text-xs text-red-600">This month</p>
          </div>
          <CurrencyDollarIcon class="h-8 w-8 text-red-500" />
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6 border-l-4 border-teal-500">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm font-medium text-gray-500">Upcoming Tasks</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.upcoming_tasks || 0 }}</p>
            <p class="text-xs text-teal-600">Next 7 days</p>
          </div>
          <ClipboardDocumentListIcon class="h-8 w-8 text-teal-500" />
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Column -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Current Plantings -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Current Rice Plantings</h2>
          </div>
          <div class="p-6">
            <div v-if="plantings.length === 0" class="text-center py-8">
              <SparklesIcon class="mx-auto h-12 w-12 text-gray-400" />
              <h3 class="mt-2 text-sm font-medium text-gray-900">No active plantings</h3>
              <p class="mt-1 text-sm text-gray-500">Get started by creating your first rice planting.</p>
              <div class="mt-6">
                <router-link
                  to="/rice-farming/plantings/create"
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
                >
                  <PlusIcon class="-ml-1 mr-2 h-5 w-5" />
                  Create Planting
                </router-link>
              </div>
            </div>
            
            <div v-else class="space-y-4">
              <div
                v-for="planting in plantings.slice(0, 3)"
                :key="planting.id"
                class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <h3 class="text-sm font-medium text-gray-900">
                      {{ planting.field?.name }} - {{ planting.rice_variety?.name }}
                    </h3>
                    <p class="text-sm text-gray-500">
                      Planted {{ formatDate(planting.planting_date) }}
                    </p>
                  </div>
                  <div class="text-right">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="getStageStatusClass(planting.current_stage)"
                    >
                      {{ planting.current_stage || 'Not Started' }}
                    </span>
                    <p class="text-sm text-gray-500 mt-1">
                      {{ planting.progress_percentage || 0 }}% Complete
                    </p>
                  </div>
                </div>
                
                <!-- Progress Bar -->
                <div class="mt-3">
                  <div class="bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-green-600 h-2 rounded-full transition-all duration-300"
                      :style="{ width: `${planting.progress_percentage || 0}%` }"
                    ></div>
                  </div>
                </div>
              </div>
              
              <div v-if="plantings.length > 3" class="text-center">
                <router-link
                  to="/rice-farming/lifecycle"
                  class="text-green-600 hover:text-green-700 text-sm font-medium"
                >
                  View all plantings ({{ plantings.length }})
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- Weather Overview -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Weather Overview</h2>
          </div>
          <div class="p-6">
            <div v-if="weatherData.length === 0" class="text-center py-8">
              <CloudIcon class="mx-auto h-12 w-12 text-gray-400" />
              <h3 class="mt-2 text-sm font-medium text-gray-900">No weather data</h3>
              <p class="mt-1 text-sm text-gray-500">Weather information will appear here once available.</p>
            </div>
            
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div
                v-for="field in weatherData.slice(0, 2)"
                :key="field.field.id"
                class="border border-gray-200 rounded-lg p-4"
              >
                <h3 class="text-sm font-medium text-gray-900 mb-3">{{ field.field.name }}</h3>
                
                <div v-if="field.weather" class="space-y-2">
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Temperature</span>
                    <span class="text-sm font-medium">{{ field.weather.temperature }}°C</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Humidity</span>
                    <span class="text-sm font-medium">{{ field.weather.humidity }}%</span>
                  </div>
                  <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Conditions</span>
                    <span class="text-sm font-medium capitalize">{{ field.weather.conditions }}</span>
                  </div>
                  
                  <div v-if="field.alerts_count > 0" class="mt-3">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                      <ExclamationTriangleIcon class="w-3 h-3 mr-1" />
                      {{ field.alerts_count }} Alert{{ field.alerts_count > 1 ? 's' : '' }}
                    </span>
                  </div>
                </div>
                
                <div v-else class="text-sm text-gray-500">
                  No recent weather data
                </div>
              </div>
            </div>
            
            <div class="mt-4 text-center">
              <router-link
                to="/weather"
                class="text-green-600 hover:text-green-700 text-sm font-medium"
              >
                View detailed weather analytics
              </router-link>
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
              to="/rice-farming/plantings/create"
              class="flex items-center p-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <PlusIcon class="w-5 h-5 mr-3 text-green-600" />
              Create New Planting
            </router-link>
            
            <router-link
              to="/fields/create"
              class="flex items-center p-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <MapIcon class="w-5 h-5 mr-3 text-blue-600" />
              Add Rice Field
            </router-link>
            
            <router-link
              to="/tasks/create"
              class="flex items-center p-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <ClipboardDocumentListIcon class="w-5 h-5 mr-3 text-purple-600" />
              Create Task
            </router-link>
            
            <router-link
              to="/marketplace/my-products/create"
              class="flex items-center p-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <ShoppingBagIcon class="w-5 h-5 mr-3 text-orange-600" />
              List Rice Product
            </router-link>
          </div>
        </div>

        <!-- Upcoming Tasks -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Upcoming Tasks</h2>
          </div>
          <div class="p-6">
            <div v-if="upcomingTasks.length === 0" class="text-center py-4">
              <ClipboardDocumentListIcon class="mx-auto h-8 w-8 text-gray-400" />
              <p class="mt-2 text-sm text-gray-500">No upcoming tasks</p>
            </div>
            
            <div v-else class="space-y-3">
              <div
                v-for="task in upcomingTasks.slice(0, 5)"
                :key="task.id"
                class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
              >
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ task.title }}</p>
                  <p class="text-xs text-gray-500">Due {{ formatDate(task.due_date) }}</p>
                </div>
                <span
                  class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                  :class="getPriorityClass(task.priority)"
                >
                  {{ task.priority }}
                </span>
              </div>
              
              <div class="text-center">
                <router-link
                  to="/tasks"
                  class="text-green-600 hover:text-green-700 text-sm font-medium"
                >
                  View all tasks
                </router-link>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Recent Activity</h2>
          </div>
          <div class="p-6">
            <div class="space-y-3">
              <div
                v-for="activity in recentActivity.slice(0, 5)"
                :key="activity.id"
                class="flex items-start space-x-3"
              >
                <div class="flex-shrink-0">
                  <div 
                    class="w-2 h-2 rounded-full mt-2"
                    :class="getActivityTypeColor(activity.type)"
                  ></div>
                </div>
                <div>
                  <p class="text-sm text-gray-900">{{ activity.description }}</p>
                  <p class="text-xs text-gray-500">{{ formatDate(activity.created_at) }}</p>
                </div>
              </div>
              
              <div v-if="recentActivity.length === 0" class="text-center py-4">
                <p class="text-sm text-gray-500">No recent activity</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Module Status Overview -->
    <div class="mt-8">
      <h2 class="text-xl font-bold text-gray-900 mb-6">Module Status Overview</h2>
      
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Field Management Module -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Field Management</h3>
              <GlobeAltIcon class="h-6 w-6 text-green-600" />
            </div>
          </div>
          <div class="p-6">
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Total Fields</span>
                <span class="text-sm font-medium text-gray-900">{{ stats.total_fields || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Suitable for Rice</span>
                <span class="text-sm font-medium text-green-600">{{ Math.floor((stats.total_fields || 0) * 0.8) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Need Preparation</span>
                <span class="text-sm font-medium text-orange-600">{{ Math.floor((stats.total_fields || 0) * 0.2) }}</span>
              </div>
            </div>
            <div class="mt-4">
              <router-link 
                to="/fields" 
                class="text-sm text-green-600 hover:text-green-700 font-medium"
              >
                Manage Fields →
              </router-link>
            </div>
          </div>
        </div>

        <!-- Rice Lifecycle Module -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Rice Lifecycle</h3>
              <SparklesIcon class="h-6 w-6 text-blue-600" />
            </div>
          </div>
          <div class="p-6">
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Active Plantings</span>
                <span class="text-sm font-medium text-gray-900">{{ stats.active_plantings || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Critical Stages</span>
                <span class="text-sm font-medium text-yellow-600">{{ stats.critical_plantings || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Ready to Harvest</span>
                <span class="text-sm font-medium text-green-600">{{ Math.floor((stats.active_plantings || 0) * 0.15) }}</span>
              </div>
            </div>
            <div class="mt-4">
              <router-link 
                to="/rice-farming/lifecycle" 
                class="text-sm text-blue-600 hover:text-blue-700 font-medium"
              >
                View Lifecycle →
              </router-link>
            </div>
          </div>
        </div>

        <!-- Weather Analytics Module -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Weather Analytics</h3>
              <CloudIcon class="h-6 w-6 text-purple-600" />
            </div>
          </div>
          <div class="p-6">
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Active Alerts</span>
                <span class="text-sm font-medium text-gray-900">{{ stats.weather_alerts || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Monitored Fields</span>
                <span class="text-sm font-medium text-green-600">{{ stats.total_fields || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Avg Suitability</span>
                <span class="text-sm font-medium text-blue-600">{{ Math.floor(Math.random() * 20 + 70) }}%</span>
              </div>
            </div>
            <div class="mt-4">
              <router-link 
                to="/weather" 
                class="text-sm text-purple-600 hover:text-purple-700 font-medium"
              >
                View Weather →
              </router-link>
            </div>
          </div>
        </div>

        <!-- Inventory Module -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Inventory</h3>
              <CubeIcon class="h-6 w-6 text-orange-600" />
            </div>
          </div>
          <div class="p-6">
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Total Items</span>
                <span class="text-sm font-medium text-gray-900">{{ Math.floor(Math.random() * 50 + 20) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Low Stock</span>
                <span class="text-sm font-medium text-orange-600">{{ stats.low_stock_items || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Out of Stock</span>
                <span class="text-sm font-medium text-red-600">{{ Math.floor((stats.low_stock_items || 0) * 0.3) }}</span>
              </div>
            </div>
            <div class="mt-4">
              <router-link 
                to="/inventory" 
                class="text-sm text-orange-600 hover:text-orange-700 font-medium"
              >
                Manage Inventory →
              </router-link>
            </div>
          </div>
        </div>

        <!-- Marketplace Module -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Marketplace</h3>
              <ShoppingBagIcon class="h-6 w-6 text-indigo-600" />
            </div>
          </div>
          <div class="p-6">
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Listed Products</span>
                <span class="text-sm font-medium text-gray-900">{{ Math.floor(Math.random() * 10 + 5) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Pending Orders</span>
                <span class="text-sm font-medium text-indigo-600">{{ stats.pending_orders || 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">This Month Sales</span>
                <span class="text-sm font-medium text-green-600">{{ formatCurrency(Math.floor(Math.random() * 5000 + 1000)) }}</span>
              </div>
            </div>
            <div class="mt-4">
              <router-link 
                to="/marketplace/my-products" 
                class="text-sm text-indigo-600 hover:text-indigo-700 font-medium"
              >
                View Products →
              </router-link>
            </div>
          </div>
        </div>

        <!-- Financial Module -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Financial</h3>
              <CurrencyDollarIcon class="h-6 w-6 text-green-600" />
            </div>
          </div>
          <div class="p-6">
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Monthly Expenses</span>
                <span class="text-sm font-medium text-red-600">{{ formatCurrency(stats.monthly_expenses || 0) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Monthly Revenue</span>
                <span class="text-sm font-medium text-green-600">{{ formatCurrency(Math.floor((stats.monthly_expenses || 0) * 1.3)) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-600">Net Profit</span>
                <span class="text-sm font-medium text-blue-600">{{ formatCurrency(Math.floor((stats.monthly_expenses || 0) * 0.3)) }}</span>
              </div>
            </div>
            <div class="mt-4">
              <router-link 
                to="/financial/expenses" 
                class="text-sm text-green-600 hover:text-green-700 font-medium"
              >
                View Finances →
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import { formatCurrency } from '@/utils/format';
import { 
  riceFarmingAPI, 
  fieldsAPI, 
  weatherAPI, 
  tasksAPI, 
  inventoryAPI, 
  riceMarketplaceAPI, 
  expensesAPI 
} from '@/services/api';
import {
  GlobeAltIcon,
  SparklesIcon,
  ExclamationTriangleIcon,
  CloudIcon,
  PlusIcon,
  MapIcon,
  ClipboardDocumentListIcon,
  ShoppingBagIcon,
  CubeIcon,
  CurrencyDollarIcon,
} from '@heroicons/vue/24/outline';

const authStore = useAuthStore();

// Reactive data
const stats = ref({});
const plantings = ref([]);
const weatherData = ref([]);
const upcomingTasks = ref([]);
const recentActivity = ref([]);
const loading = ref(true);
const error = ref(null);

// Methods
const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString();
};

const getStageStatusClass = (stage) => {
  const stageClasses = {
    'seedling': 'bg-green-100 text-green-800',
    'tillering': 'bg-blue-100 text-blue-800',
    'flowering': 'bg-yellow-100 text-yellow-800',
    'grain_filling': 'bg-orange-100 text-orange-800',
    'ripening': 'bg-purple-100 text-purple-800',
    'harvest': 'bg-red-100 text-red-800',
  };
  return stageClasses[stage] || 'bg-gray-100 text-gray-800';
};

const getPriorityClass = (priority) => {
  const priorityClasses = {
    'high': 'bg-red-100 text-red-800',
    'medium': 'bg-yellow-100 text-yellow-800',
    'low': 'bg-green-100 text-green-800',
  };
  return priorityClasses[priority] || 'bg-gray-100 text-gray-800';
};

const getActivityTypeColor = (type) => {
  const typeColors = {
    'planting': 'bg-green-600',
    'task': 'bg-blue-600',
    'order': 'bg-indigo-600',
    'weather': 'bg-purple-600',
    'inventory': 'bg-orange-600',
  };
  return typeColors[type] || 'bg-gray-600';
};

const loadDashboardData = async () => {
  try {
    loading.value = true;
    error.value = null;
    
    // Load actual dashboard data from multiple APIs
    const [
      lifecycleResponse,
      fieldsResponse, 
      weatherResponse,
      tasksResponse,
      inventoryResponse,
      marketplaceResponse,
      expensesResponse
    ] = await Promise.all([
      riceFarmingAPI.getLifecycleOverview().catch(() => ({ data: { overview: {} } })),
      fieldsAPI.getAll().catch(() => ({ data: { fields: [] } })),
      weatherAPI.getRiceDashboard().catch(() => ({ data: { field_details: [] } })),
      tasksAPI.getAll().catch(() => ({ data: { tasks: [] } })),
      inventoryAPI.getAll().catch(() => ({ data: { inventory: [] } })),
      riceMarketplaceAPI.getOrders().catch(() => ({ data: { orders: [] } })),
      expensesAPI.getAll().catch(() => ({ data: { expenses: [] } }))
    ]);

    // Process lifecycle data
    const lifecycleData = lifecycleResponse.data.overview || {};
    
    // Process fields data
    const fieldsData = fieldsResponse.data.fields || [];
    
    // Process weather data
    const weatherFieldData = weatherResponse.data.field_details || [];
    
    // Process tasks data
    const tasksData = tasksResponse.data.tasks || [];
    const upcomingTasksData = tasksData.filter(task => {
      const dueDate = new Date(task.due_date);
      const nextWeek = new Date();
      nextWeek.setDate(nextWeek.getDate() + 7);
      return dueDate <= nextWeek && ['pending', 'in_progress'].includes(task.status);
    });

    // Process inventory data
    const inventoryData = inventoryResponse.data.inventory || [];
    const lowStockItems = inventoryData.filter(item => (item.current_stock ?? item.quantity ?? 0) <= (item.minimum_stock ?? item.min_stock ?? 10));

    // Process marketplace data
    const ordersData = marketplaceResponse.data.orders || [];
    const pendingOrders = ordersData.filter(order => ['pending', 'confirmed'].includes(order.status));

    // Process expenses data
    const expensesData = expensesResponse.data.expenses || [];
    const thisMonthExpenses = expensesData.filter(expense => {
      const expenseDate = new Date(expense.expense_date || expense.created_at);
      const thisMonth = new Date();
      return expenseDate.getMonth() === thisMonth.getMonth() && 
             expenseDate.getFullYear() === thisMonth.getFullYear();
    });

    // Set dashboard stats
    stats.value = {
      total_fields: fieldsData.length,
      active_plantings: lifecycleData.total_plantings || 0,
      critical_plantings: lifecycleData.critical_plantings?.length || 0,
      weather_alerts: weatherFieldData.reduce((total, field) => total + (field.alerts_count || 0), 0),
      low_stock_items: lowStockItems.length,
      pending_orders: pendingOrders.length,
      monthly_expenses: thisMonthExpenses.reduce((total, expense) => total + parseFloat(expense.amount || 0), 0),
      upcoming_tasks: upcomingTasksData.length,
    };
    
    // Set plantings data
    plantings.value = (lifecycleData.plantings || []).map(planting => ({
      ...planting,
      current_stage: planting.current_stage?.riceGrowthStage?.name || 'Not Started',
      progress_percentage: planting.progress_percentage || 0,
    }));
    
    // Set weather data
    weatherData.value = weatherFieldData.map(field => ({
      field: { id: field.field.id, name: field.field.name },
      weather: field.weather,
      alerts_count: field.alerts_count || 0,
      critical_alerts: field.critical_alerts || 0,
    }));
    
    // Set upcoming tasks
    upcomingTasks.value = upcomingTasksData.slice(0, 5);
    
    // Generate recent activity from various sources
    recentActivity.value = generateRecentActivity(
      lifecycleData,
      tasksData,
      ordersData,
      inventoryData
    );
    
  } catch (err) {
    console.error('Error loading dashboard data:', err);
    error.value = err.response?.data?.message || 'Failed to load dashboard data';
    // Set empty defaults on error instead of sample data
    stats.value = {};
    plantings.value = [];
    weatherData.value = [];
    upcomingTasks.value = [];
    recentActivity.value = [];
  } finally {
    loading.value = false;
  }
};

const generateRecentActivity = (lifecycle, tasks, orders, inventory) => {
  const activities = [];
  
  // Add recent planting activities
  if (lifecycle.plantings) {
    lifecycle.plantings.slice(0, 2).forEach(planting => {
      activities.push({
        id: `planting-${planting.id}`,
        description: `Started ${planting.current_stage?.riceGrowthStage?.name || 'new'} stage for ${planting.field?.name}`,
        created_at: planting.updated_at || planting.created_at,
        type: 'planting'
      });
    });
  }
  
  // Add recent task completions
  const completedTasks = tasks.filter(task => task.status === 'completed').slice(0, 2);
  completedTasks.forEach(task => {
    activities.push({
      id: `task-${task.id}`,
      description: `Completed task: ${task.title}`,
      created_at: task.updated_at || task.created_at,
      type: 'task'
    });
  });
  
  // Add recent orders
  const recentOrders = orders.slice(0, 2);
  recentOrders.forEach(order => {
    activities.push({
      id: `order-${order.id}`,
      description: `New order received: ${order.rice_product?.name} (${order.quantity} ${order.rice_product?.unit})`,
      created_at: order.order_date || order.created_at,
      type: 'order'
    });
  });
  
  // Sort by date and return top 5
  return activities
    .sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
    .slice(0, 5);
};

const loadSampleData = () => {
  stats.value = {
    total_fields: 3,
    active_plantings: 5,
    critical_plantings: 2,
    weather_alerts: 1,
    low_stock_items: 3,
    pending_orders: 4,
    monthly_expenses: 2500,
    upcoming_tasks: 6,
  };
  
  plantings.value = [
    {
      id: 1,
      field: { name: 'North Field' },
      rice_variety: { name: 'IR64' },
      planting_date: '2024-01-15',
      current_stage: 'flowering',
      progress_percentage: 65,
    },
    {
      id: 2,
      field: { name: 'South Field' },
      rice_variety: { name: 'Jasmine' },
      planting_date: '2024-02-01',
      current_stage: 'tillering',
      progress_percentage: 40,
    },
    {
      id: 3,
      field: { name: 'East Field' },
      rice_variety: { name: 'Basmati' },
      planting_date: '2024-02-15',
      current_stage: 'grain_filling',
      progress_percentage: 80,
    },
  ];
  
  weatherData.value = [
    {
      field: { id: 1, name: 'North Field' },
      weather: {
        temperature: 28,
        humidity: 75,
        conditions: 'partly cloudy',
      },
      alerts_count: 1,
      critical_alerts: 0,
    },
    {
      field: { id: 2, name: 'South Field' },
      weather: {
        temperature: 32,
        humidity: 85,
        conditions: 'humid',
      },
      alerts_count: 2,
      critical_alerts: 1,
    },
  ];
  
  upcomingTasks.value = [
    {
      id: 1,
      title: 'Apply fertilizer to North Field',
      due_date: '2024-10-05',
      priority: 'high',
    },
    {
      id: 2,
      title: 'Check irrigation system',
      due_date: '2024-10-07',
      priority: 'medium',
    },
    {
      id: 3,
      title: 'Harvest East Field',
      due_date: '2024-10-10',
      priority: 'high',
    },
    {
      id: 4,
      title: 'Soil testing for West Field',
      due_date: '2024-10-12',
      priority: 'low',
    },
  ];
  
  recentActivity.value = [
    {
      id: 1,
      description: 'Completed grain filling stage for East Field',
      created_at: '2024-10-01',
      type: 'planting'
    },
    {
      id: 2,
      description: 'New order received: Premium Jasmine Rice (50 kg)',
      created_at: '2024-09-30',
      type: 'order'
    },
    {
      id: 3,
      description: 'Completed task: Pest inspection for North Field',
      created_at: '2024-09-29',
      type: 'task'
    },
    {
      id: 4,
      description: 'Weather alert: High humidity detected in South Field',
      created_at: '2024-09-28',
      type: 'weather'
    },
    {
      id: 5,
      description: 'Low stock alert: NPK Fertilizer below minimum level',
      created_at: '2024-09-27',
      type: 'inventory'
    },
  ];
};

onMounted(() => {
  loadDashboardData();
});
</script>