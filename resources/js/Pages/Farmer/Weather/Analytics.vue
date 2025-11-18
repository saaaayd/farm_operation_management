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
              <h1 class="text-xl font-semibold text-gray-900">Weather Analytics</h1>
              <p class="text-sm text-gray-500">Analyze weather patterns and their impact on rice farming</p>
            </div>
          </div>
          
          <div class="flex space-x-3">
            <select 
              v-model="selectedField"
              class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
            >
              <option value="">All Fields</option>
              <option v-for="field in fields" :key="field.id" :value="field.id">
                {{ field.name }}
              </option>
            </select>
            <button 
              @click="refreshWeatherData"
              :disabled="loading"
              class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 flex items-center"
            >
              <svg 
                :class="['h-5 w-5 mr-2', { 'animate-spin': loading }]" 
                fill="none" 
                stroke="currentColor" 
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Refresh
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Current Weather Overview -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
        <!-- Current Weather -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Weather</h3>
            
            <div v-if="currentWeather" class="space-y-4">
              <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                  <div class="text-4xl">
                    {{ getWeatherIcon(currentWeather.conditions) }}
                  </div>
                  <div>
                    <div class="text-3xl font-bold text-gray-900">
                      {{ Math.round(currentWeather.temperature) }}°C
                    </div>
                    <div class="text-sm text-gray-600 capitalize">
                      {{ currentWeather.conditions }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div class="text-center">
                  <div class="text-2xl font-semibold text-blue-600">
                    {{ currentWeather.humidity }}%
                  </div>
                  <div class="text-xs text-gray-500">Humidity</div>
                </div>
                <div class="text-center">
                  <div class="text-2xl font-semibold text-gray-600">
                    {{ currentWeather.wind_speed }} km/h
                  </div>
                  <div class="text-xs text-gray-500">Wind Speed</div>
                </div>
              </div>

              <div class="bg-gray-50 rounded-lg p-4">
                <div class="flex items-center space-x-2">
                  <div 
                    :class="[
                      'w-3 h-3 rounded-full',
                      isFavorableForFarming ? 'bg-green-500' : 'bg-red-500'
                    ]"
                  ></div>
                  <span class="text-sm text-gray-700">
                    {{ isFavorableForFarming ? 'Favorable for rice farming' : 'Unfavorable conditions' }}
                  </span>
                </div>
              </div>
            </div>
            
            <div v-else class="text-center py-8 text-gray-500">
              <svg class="h-12 w-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
              </svg>
              <p class="text-sm">No weather data available</p>
            </div>
          </div>
        </div>

        <!-- 5-Day Forecast -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">5-Day Forecast</h3>
            
            <div v-if="forecast.length > 0" class="space-y-4">
              <div 
                v-for="day in forecast.slice(0, 5)" 
                :key="day.date"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex items-center space-x-4">
                  <div class="text-2xl">
                    {{ getWeatherIcon(day.conditions) }}
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ formatDate(day.date) }}</div>
                    <div class="text-sm text-gray-600 capitalize">{{ day.conditions }}</div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-lg font-semibold text-gray-900">
                    {{ Math.round(day.temperature) }}°C
                  </div>
                  <div class="text-sm text-gray-600">
                    {{ day.humidity }}% humidity
                  </div>
                </div>
              </div>
            </div>
            
            <div v-else class="text-center py-8 text-gray-500">
              <svg class="h-12 w-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
              </svg>
              <p class="text-sm">No forecast data available</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Weather Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Temperature Chart -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Temperature Trends</h3>
          <div class="h-64">
            <LineChart 
              v-if="weatherHistory.length > 0"
              :data="temperatureChartData"
              :options="chartOptions"
            />
            <div v-else class="flex items-center justify-center h-full text-gray-500">
              <p>No temperature data available</p>
            </div>
          </div>
        </div>

        <!-- Rainfall Chart -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Rainfall Patterns</h3>
          <div class="h-64">
            <BarChart 
              v-if="weatherHistory.length > 0"
              :data="rainfallChartData"
              :options="chartOptions"
            />
            <div v-else class="flex items-center justify-center h-full text-gray-500">
              <p>No rainfall data available</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Weather Alerts -->
      <div class="mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Weather Alerts</h3>
          
          <div v-if="alerts.length > 0" class="space-y-3">
            <div 
              v-for="alert in alerts" 
              :key="alert.id"
              :class="[
                'p-4 rounded-lg border-l-4',
                getAlertClass(alert.severity)
              ]"
            >
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  {{ getAlertIcon(alert.type) }}
                </div>
                <div class="ml-3">
                  <h4 class="text-sm font-medium">{{ alert.title }}</h4>
                  <p class="text-sm">{{ alert.message }}</p>
                  <p class="text-xs text-gray-500 mt-1">
                    {{ formatDate(alert.created_at) }}
                  </p>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="text-center py-8 text-gray-500">
            <svg class="h-12 w-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm">No weather alerts</p>
          </div>
        </div>
      </div>

      <!-- Historical Weather Data -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Historical Weather Data</h3>
        
        <div v-if="weatherHistory.length > 0" class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Date
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Temperature
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Humidity
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Wind Speed
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Conditions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="record in weatherHistory.slice(0, 10)" :key="record.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatDate(record.recorded_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ record.temperature }}°C
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ record.humidity }}%
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ record.wind_speed }} km/h
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">
                  {{ record.conditions }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        
        <div v-else class="text-center py-8 text-gray-500">
          <svg class="h-12 w-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
          </svg>
          <p class="text-sm">No historical weather data available</p>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useWeatherStore } from '@/stores/weather';
import { useFarmStore } from '@/stores/farm';
import LineChart from '@/Components/Charts/LineChart.vue';
import BarChart from '@/Components/Charts/BarChart.vue';

const route = useRoute();
const router = useRouter();
const weatherStore = useWeatherStore();
const farmStore = useFarmStore();

const loading = ref(false);
const normalizeFieldId = (value) => (value === null || value === undefined ? '' : String(value));
const selectedField = ref(normalizeFieldId(route.query.field || route.query.fieldId || ''));

const fields = computed(() => Array.isArray(farmStore.fields) ? farmStore.fields : []);
const currentWeather = computed(() => weatherStore.currentWeather || null);
const forecast = computed(() => Array.isArray(weatherStore.forecast) ? weatherStore.forecast : []);
const weatherHistory = computed(() => Array.isArray(weatherStore.weatherHistory) ? weatherStore.weatherHistory : []);
const alerts = computed(() => Array.isArray(weatherStore.alerts) ? weatherStore.alerts : []);

const isFavorableForFarming = computed(() => {
  if (!currentWeather.value) return false;
  
  return currentWeather.value.temperature >= 10 && 
         currentWeather.value.temperature <= 35 &&
         currentWeather.value.humidity >= 30 &&
         currentWeather.value.humidity <= 80 &&
         currentWeather.value.wind_speed < 20;
});

const temperatureChartData = computed(() => {
  const data = weatherHistory.value.slice(-30); // Last 30 days
  return {
    labels: data.map(record => formatDate(record.recorded_at)),
    datasets: [{
      label: 'Temperature (°C)',
      data: data.map(record => record.temperature),
      borderColor: 'rgb(34, 197, 94)',
      backgroundColor: 'rgba(34, 197, 94, 0.1)',
      tension: 0.1
    }]
  };
});

const rainfallChartData = computed(() => {
  const data = weatherHistory.value.slice(-30); // Last 30 days
  return {
    labels: data.map(record => formatDate(record.recorded_at)),
    datasets: [{
      label: 'Rainfall (mm)',
      data: data.map(record => record.rainfall || 0),
      backgroundColor: 'rgba(59, 130, 246, 0.5)',
      borderColor: 'rgb(59, 130, 246)',
      borderWidth: 1
    }]
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false
    }
  },
  scales: {
    y: {
      beginAtZero: true
    }
  }
};

const getWeatherIcon = (conditions) => {
  const icons = {
    clear: '☀️',
    cloudy: '☁️',
    rainy: '🌧️',
    stormy: '⛈️',
    snowy: '❄️',
    foggy: '🌫️'
  };
  return icons[conditions] || '🌤️';
};

const getAlertIcon = (type) => {
  const icons = {
    heavy_rain: '🌧️',
    drought: '🌵',
    typhoon: '🌀',
    extreme_temperature: '🌡️'
  };
  return icons[type] || '⚠️';
};

const getAlertClass = (severity) => {
  const classes = {
    low: 'bg-yellow-50 border-yellow-400 text-yellow-800',
    medium: 'bg-orange-50 border-orange-400 text-orange-800',
    high: 'bg-red-50 border-red-400 text-red-800',
    critical: 'bg-red-100 border-red-600 text-red-900'
  };
  return classes[severity] || classes.medium;
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const refreshWeatherData = async () => {
  loading.value = true;
  try {
    let fieldId = selectedField.value;

    if (!fieldId && fields.value.length > 0) {
      fieldId = normalizeFieldId(fields.value[0].id);
      selectedField.value = fieldId;
    }

    if (fieldId) {
      const numericFieldId = Number(fieldId);
      if (Number.isNaN(numericFieldId)) {
        return;
      }
      await Promise.all([
        weatherStore.fetchCurrentWeather(numericFieldId),
        weatherStore.fetchForecast(numericFieldId),
        weatherStore.fetchWeatherHistory(numericFieldId),
        weatherStore.fetchWeatherAlerts(numericFieldId)
      ]);
    }
  } catch (error) {
    console.error('Failed to refresh weather data:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  try {
    await farmStore.fetchFields();
    if (fields.value.length > 0) {
      const availableIds = fields.value.map(field => normalizeFieldId(field.id));
      const routeFieldId = normalizeFieldId(route.query.field || route.query.fieldId || '');
      if (routeFieldId && availableIds.includes(routeFieldId)) {
        selectedField.value = routeFieldId;
      } else {
        selectedField.value = availableIds[0];
      }
      await refreshWeatherData();
    }
  } catch (error) {
    console.error('Failed to load weather analytics:', error);
  }
});

const syncRouteQueryWithSelection = (fieldValue) => {
  const nextQuery = { ...route.query };
  if (fieldValue) {
    nextQuery.field = fieldValue;
  } else {
    delete nextQuery.field;
  }
  router.replace({ query: nextQuery }).catch(() => {});
};

watch(
  () => ({ field: route.query.field, fieldId: route.query.fieldId }),
  (newQuery) => {
    const incomingField = normalizeFieldId(newQuery.field || newQuery.fieldId || '');
    if (incomingField !== selectedField.value) {
      selectedField.value = incomingField;
    }
  }
);

watch(
  selectedField,
  (newValue, oldValue) => {
    if (newValue === oldValue) return;
    const normalized = normalizeFieldId(newValue);
    const currentRouteValue = normalizeFieldId(route.query.field || route.query.fieldId || '');
    if (normalized !== currentRouteValue) {
      syncRouteQueryWithSelection(normalized);
    }
  }
);
</script>
