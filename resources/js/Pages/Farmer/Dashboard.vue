<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="h-8 w-8 bg-green-600 rounded-full flex items-center justify-center">
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
            </div>
            <div class="ml-3">
              <h1 class="text-xl font-semibold text-gray-900">RiceFARM Dashboard</h1>
              <p class="text-sm text-gray-500">Welcome back, {{ authStore.user?.name }}</p>
            </div>
          </div>
          
          <div class="flex items-center space-x-4">
            <button
              @click="logout"
              class="text-gray-500 hover:text-gray-700 transition-colors"
            >
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Planted Area</p>
              <p class="text-2xl font-semibold text-gray-900">{{ totalPlantedArea }} ha</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Tasks Due Today</p>
              <p class="text-2xl font-semibold text-gray-900">{{ tasksDueToday }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="h-8 w-8 bg-yellow-100 rounded-full flex items-center justify-center">
                <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Low Stock Alerts</p>
              <p class="text-2xl font-semibold text-gray-900">{{ lowStockCount }}</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="h-8 w-8 bg-purple-100 rounded-full flex items-center justify-center">
                <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Pending Orders</p>
              <p class="text-2xl font-semibold text-gray-900">{{ pendingOrders }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Main Dashboard Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Weather Widget -->
        <div class="lg:col-span-1">
          <CurrentWeather 
            v-if="primaryField" 
            :field-id="primaryField.id" 
          />
          <div v-else class="bg-white rounded-lg shadow-lg p-6">
            <div class="text-center text-gray-500">
              <svg class="h-12 w-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
              </svg>
              <p class="text-sm">No fields available for weather data</p>
              <router-link 
                to="/fields" 
                class="text-green-600 hover:text-green-700 text-sm font-medium"
              >
                Add your first field
              </router-link>
            </div>
          </div>
        </div>

        <!-- Upcoming Tasks -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-gray-900">Upcoming Tasks</h3>
              <router-link 
                to="/tasks" 
                class="text-green-600 hover:text-green-700 text-sm font-medium"
              >
                View all tasks
              </router-link>
            </div>
            
            <div v-if="upcomingTasks.length > 0" class="space-y-3">
              <div 
                v-for="task in upcomingTasks.slice(0, 5)" 
                :key="task.id"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex items-center space-x-3">
                  <div 
                    :class="[
                      'w-3 h-3 rounded-full',
                      getTaskPriorityColor(task.priority)
                    ]"
                  ></div>
                  <div>
                    <p class="text-sm font-medium text-gray-900">{{ task.task_type }}</p>
                    <p class="text-xs text-gray-500">{{ task.description }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-xs text-gray-500">{{ formatDate(task.due_date) }}</p>
                  <span 
                    :class="[
                      'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
                      getTaskStatusClass(task.status)
                    ]"
                  >
                    {{ task.status }}
                  </span>
                </div>
              </div>
            </div>
            
            <div v-else class="text-center py-8 text-gray-500">
              <svg class="h-12 w-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
              <p class="text-sm">No upcoming tasks</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="mt-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <router-link 
            to="/tasks/create"
            class="bg-white rounded-lg shadow p-4 text-center hover:shadow-md transition-shadow"
          >
            <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
              </svg>
            </div>
            <p class="text-sm font-medium text-gray-900">Create Task</p>
          </router-link>

          <router-link 
            to="/harvests/create"
            class="bg-white rounded-lg shadow p-4 text-center hover:shadow-md transition-shadow"
          >
            <div class="h-8 w-8 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
              </svg>
            </div>
            <p class="text-sm font-medium text-gray-900">Record Harvest</p>
          </router-link>

          <router-link 
            to="/inventory"
            class="bg-white rounded-lg shadow p-4 text-center hover:shadow-md transition-shadow"
          >
            <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <p class="text-sm font-medium text-gray-900">Check Inventory</p>
          </router-link>

          <router-link 
            to="/marketplace"
            class="bg-white rounded-lg shadow p-4 text-center hover:shadow-md transition-shadow"
          >
            <div class="h-8 w-8 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
              </svg>
            </div>
            <p class="text-sm font-medium text-gray-900">Marketplace</p>
          </router-link>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useFarmStore } from '@/stores/farm';
import { useInventoryStore } from '@/stores/inventory';
import { useMarketplaceStore } from '@/stores/marketplace';
import CurrentWeather from '@/Components/Weather/CurrentWeather.vue';

const router = useRouter();
const authStore = useAuthStore();
const farmStore = useFarmStore();
const inventoryStore = useInventoryStore();
const marketplaceStore = useMarketplaceStore();

const totalPlantedArea = computed(() => {
  return farmStore.plantings
    .filter(p => p.status !== 'harvested')
    .reduce((total, planting) => {
      return total + (planting.field?.size || 0);
    }, 0).toFixed(2);
});

const tasksDueToday = computed(() => {
  const today = new Date();
  today.setHours(23, 59, 59, 999);
  return farmStore.tasks.filter(task => {
    const dueDate = new Date(task.due_date);
    return dueDate <= today && ['pending', 'in_progress'].includes(task.status);
  }).length;
});

const upcomingTasks = computed(() => farmStore.upcomingTasks);
const lowStockCount = computed(() => inventoryStore.lowStockItems.length);
const pendingOrders = computed(() => marketplaceStore.orders.filter(order => order.status === 'pending').length);

const primaryField = computed(() => farmStore.fields[0]);

const getTaskPriorityColor = (priority) => {
  const colors = {
    low: 'bg-green-500',
    medium: 'bg-yellow-500',
    high: 'bg-red-500'
  };
  return colors[priority] || 'bg-gray-500';
};

const getTaskStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const logout = async () => {
  await authStore.logout();
  router.push('/login');
};

onMounted(async () => {
  try {
    await Promise.all([
      farmStore.fetchFields(),
      farmStore.fetchPlantings(),
      farmStore.fetchTasks(),
      inventoryStore.fetchItems(),
      marketplaceStore.fetchOrders()
    ]);
  } catch (error) {
    console.error('Failed to load dashboard data:', error);
  }
});
</script>
