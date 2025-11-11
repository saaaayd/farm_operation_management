<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <router-link to="/dashboard" class="text-gray-500 hover:text-gray-700 mr-4">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </router-link>
            <div>
              <h1 class="text-xl font-semibold text-gray-900">Rice Planting Management</h1>
              <p class="text-sm text-gray-500">Manage your rice plantings and track growth</p>
            </div>
          </div>
          
          <router-link 
            to="/plantings/create"
            class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center"
          >
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            New Planting
          </router-link>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select 
              v-model="filters.status" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
            >
              <option value="">All Status</option>
              <option value="planned">Planned</option>
              <option value="planted">Planted</option>
              <option value="growing">Growing</option>
              <option value="ready">Ready</option>
              <option value="harvested">Harvested</option>
              <option value="failed">Failed</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Rice Variety</label>
            <select 
              v-model="filters.variety" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
            >
              <option value="">All Varieties</option>
              <option value="IR64">IR64</option>
              <option value="Jasmine">Jasmine Rice</option>
              <option value="Basmati">Basmati Rice</option>
              <option value="Arborio">Arborio Rice</option>
              <option value="Brown Rice">Brown Rice</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Field</label>
            <select 
              v-model="filters.field" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
            >
              <option value="">All Fields</option>
              <option v-for="field in fields" :key="field.id" :value="field.id">
                {{ field.name }}
              </option>
            </select>
          </div>
          
          <div class="flex items-end">
            <button 
              @click="clearFilters"
              class="w-full bg-gray-200 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-300 transition-colors"
            >
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Plantings Grid -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div v-for="n in 6" :key="n" class="bg-white rounded-lg shadow p-6 animate-pulse">
          <div class="h-4 bg-gray-200 rounded mb-4"></div>
          <div class="h-3 bg-gray-200 rounded mb-2"></div>
          <div class="h-3 bg-gray-200 rounded w-3/4"></div>
        </div>
      </div>

      <div v-else-if="filteredPlantings.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div 
          v-for="planting in filteredPlantings" 
          :key="planting.id"
          class="bg-white rounded-lg shadow hover:shadow-md transition-shadow cursor-pointer"
          @click="viewPlanting(planting)"
        >
          <div class="p-6">
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-semibold text-gray-900">{{ planting.crop_type }}</h3>
              <span 
                :class="getStatusClass(planting.status)"
                class="px-2 py-1 text-xs font-medium rounded-full"
              >
                {{ formatLabel(planting.status) }}
              </span>
            </div>

            <!-- Field Info -->
            <div class="mb-4">
              <div class="flex items-center text-sm text-gray-600 mb-1">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
                {{ planting.field?.name || 'Unknown Field' }}
              </div>
              <div class="flex items-center text-sm text-gray-600">
                <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                </svg>
                {{ planting.field?.size || 0 }} hectares
              </div>
            </div>

            <!-- Dates -->
            <div class="space-y-2 mb-4">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Planted:</span>
                <span class="text-gray-900">{{ formatDate(planting.planting_date) }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Expected Harvest:</span>
                <span class="text-gray-900">{{ formatDate(planting.expected_harvest_date) }}</span>
              </div>
            </div>

            <!-- Progress -->
            <div class="mb-4">
              <div class="flex justify-between text-sm text-gray-600 mb-1">
                <span>Growth Progress</span>
                <span>{{ getProgressPercentage(planting) }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div 
                  :class="getProgressColor(planting.status)"
                  class="h-2 rounded-full transition-all duration-300"
                  :style="{ width: getProgressPercentage(planting) + '%' }"
                ></div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex space-x-2">
              <button 
                @click.stop="viewPlanting(planting)"
                class="flex-1 bg-green-600 text-white py-2 px-3 rounded-md text-sm hover:bg-green-700 transition-colors"
              >
                View Details
              </button>
              <button 
                @click.stop="editPlanting(planting)"
                class="bg-gray-200 text-gray-700 py-2 px-3 rounded-md text-sm hover:bg-gray-300 transition-colors"
              >
                Edit
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <svg class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No plantings found</h3>
        <p class="text-gray-600 mb-4">Get started by creating your first rice planting</p>
        <router-link 
          to="/plantings/create"
          class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors inline-flex items-center"
        >
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
          Create Planting
        </router-link>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useFarmStore } from '@/stores/farm';

const router = useRouter();
const farmStore = useFarmStore();

const loading = ref(false);
const filters = ref({
  status: '',
  variety: '',
  field: ''
});

const plantings = computed(() => farmStore.plantings);
const fields = computed(() => farmStore.fields);

const filteredPlantings = computed(() => {
  let filtered = plantings.value;

  if (filters.value.status) {
    filtered = filtered.filter(p => p.status === filters.value.status);
  }

  if (filters.value.variety) {
    filtered = filtered.filter(p => p.crop_type === filters.value.variety);
  }

  if (filters.value.field) {
    filtered = filtered.filter(p => p.field_id === parseInt(filters.value.field));
  }

  return filtered;
});

const getStatusClass = (status) => {
  const classes = {
    planned: 'bg-indigo-100 text-indigo-800',
    planted: 'bg-blue-100 text-blue-800',
    growing: 'bg-green-100 text-green-800',
    ready: 'bg-yellow-100 text-yellow-800',
    harvested: 'bg-gray-100 text-gray-800',
    failed: 'bg-red-100 text-red-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const getProgressPercentage = (planting) => {
  const statusProgress = {
    planned: 0,
    planted: 20,
    growing: 60,
    ready: 90,
    harvested: 100,
    failed: 0
  };
  return statusProgress[planting.status] || 0;
};

const getProgressColor = (status) => {
  const colors = {
    planned: 'bg-indigo-500',
    planted: 'bg-blue-500',
    growing: 'bg-green-500',
    ready: 'bg-yellow-500',
    harvested: 'bg-gray-500',
    failed: 'bg-red-500'
  };
  return colors[status] || 'bg-gray-500';
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const formatLabel = (value) => {
  if (!value) return 'Not set';
  return value
    .toString()
    .split('_')
    .map(part => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ');
};

const clearFilters = () => {
  filters.value = {
    status: '',
    variety: '',
    field: ''
  };
};

const viewPlanting = (planting) => {
  router.push(`/plantings/${planting.id}`);
};

const editPlanting = (planting) => {
  router.push(`/plantings/${planting.id}/edit`);
};

onMounted(async () => {
  loading.value = true;
  try {
    await Promise.all([
      farmStore.fetchPlantings(),
      farmStore.fetchFields()
    ]);
  } catch (error) {
    console.error('Failed to load plantings:', error);
  } finally {
    loading.value = false;
  }
});
</script>
