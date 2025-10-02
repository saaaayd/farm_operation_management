<template>
  <div class="p-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Rice Farming Dashboard</h1>
      <p class="text-gray-600 mt-2">Welcome back, {{ authStore.user?.name }}! Here's your farm overview.</p>
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
          </div>
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
                  <div class="w-2 h-2 bg-green-600 rounded-full mt-2"></div>
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
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/stores/auth';
import {
  GlobeAltIcon,
  SparklesIcon,
  ExclamationTriangleIcon,
  CloudIcon,
  PlusIcon,
  MapIcon,
  ClipboardDocumentListIcon,
  ShoppingBagIcon,
} from '@heroicons/vue/24/outline';

const authStore = useAuthStore();

// Reactive data
const stats = ref({});
const plantings = ref([]);
const weatherData = ref([]);
const upcomingTasks = ref([]);
const recentActivity = ref([]);
const loading = ref(true);

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

const loadDashboardData = async () => {
  try {
    loading.value = true;
    
    // Load dashboard data (you'll need to implement these API calls)
    // const [statsResponse, plantingsResponse, weatherResponse, tasksResponse] = await Promise.all([
    //   riceFarmingAPI.getLifecycleOverview(),
    //   plantingsAPI.getAll(),
    //   weatherAPI.getRiceDashboard(),
    //   tasksAPI.getUpcoming()
    // ]);
    
    // Mock data for now
    stats.value = {
      total_fields: 3,
      active_plantings: 5,
      critical_plantings: 2,
      weather_alerts: 1,
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
    ];
    
    recentActivity.value = [
      {
        id: 1,
        description: 'Completed tillering stage for South Field',
        created_at: '2024-10-01',
      },
      {
        id: 2,
        description: 'Added new rice variety: Premium Basmati',
        created_at: '2024-09-30',
      },
    ];
    
  } catch (error) {
    console.error('Error loading dashboard data:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadDashboardData();
});
</script>