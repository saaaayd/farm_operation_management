<template>
  <div class="bg-white rounded-lg shadow-lg p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900">Current Weather</h3>
      <button
        @click="refreshWeather"
        :disabled="loading"
        class="p-2 text-gray-400 hover:text-gray-600 transition-colors"
      >
        <svg 
          :class="['h-5 w-5', { 'animate-spin': loading }]" 
          fill="none" 
          stroke="currentColor" 
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
        </svg>
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="loading && !weather" class="flex items-center justify-center py-8">
      <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
    </div>

    <!-- Weather Data -->
    <div v-else-if="weather" class="space-y-4">
      <!-- Main Weather Info -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="text-4xl">
            {{ getWeatherIcon(weather.conditions) }}
          </div>
          <div>
            <div class="text-3xl font-bold text-gray-900">
              {{ Math.round(weather.temperature) }}°C
            </div>
            <div class="text-sm text-gray-600 capitalize">
              {{ weather.conditions }}
            </div>
          </div>
        </div>
        <div class="text-right">
          <div class="text-sm text-gray-500">Last updated</div>
          <div class="text-xs text-gray-400">
            {{ formatTime(weather.recorded_at) }}
          </div>
        </div>
      </div>

      <!-- Weather Metrics -->
      <div class="grid grid-cols-3 gap-4">
        <div class="text-center">
          <div class="text-2xl font-semibold text-blue-600">
            {{ weather.humidity }}%
          </div>
          <div class="text-xs text-gray-500">Humidity</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-semibold text-gray-600">
            {{ weather.wind_speed }} km/h
          </div>
          <div class="text-xs text-gray-500">Wind Speed</div>
        </div>
        <div class="text-center">
          <div class="text-2xl font-semibold text-orange-600">
            {{ Math.round(weather.temperature * 9/5 + 32) }}°F
          </div>
          <div class="text-xs text-gray-500">Temperature</div>
        </div>
      </div>

      <!-- Weather Alerts -->
      <div v-if="alerts.length > 0" class="space-y-2">
        <h4 class="text-sm font-medium text-gray-900">Weather Alerts</h4>
        <div 
          v-for="alert in alerts" 
          :key="alert.id"
          :class="[
            'p-3 rounded-md border-l-4',
            getAlertClass(alert.severity)
          ]"
        >
          <div class="flex items-center">
            <div class="flex-shrink-0">
              {{ getAlertIcon(alert.type) }}
            </div>
            <div class="ml-3">
              <p class="text-sm font-medium">{{ alert.title }}</p>
              <p class="text-xs">{{ alert.message }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Farming Conditions -->
      <div class="bg-gray-50 rounded-lg p-4">
        <h4 class="text-sm font-medium text-gray-900 mb-2">Farming Conditions</h4>
        <div class="flex items-center space-x-2">
          <div 
            :class="[
              'w-3 h-3 rounded-full',
              isFavorableForFarming ? 'bg-green-500' : 'bg-red-500'
            ]"
          ></div>
          <span class="text-sm text-gray-700">
            {{ isFavorableForFarming ? 'Favorable for rice farming' : 'Unfavorable conditions detected' }}
          </span>
        </div>
        <div class="mt-2 text-xs text-gray-500">
          {{ getFarmingAdvice() }}
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-8">
      <div class="text-red-500 mb-2">
        <svg class="h-8 w-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
      <p class="text-sm text-gray-600">{{ error }}</p>
      <button 
        @click="refreshWeather"
        class="mt-2 text-sm text-green-600 hover:text-green-700"
      >
        Try again
      </button>
    </div>

    <!-- No Data State -->
    <div v-else class="text-center py-8">
      <div class="text-gray-400 mb-2">
        <svg class="h-8 w-8 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
        </svg>
      </div>
      <p class="text-sm text-gray-600">No weather data available</p>
      <button 
        @click="refreshWeather"
        class="mt-2 text-sm text-green-600 hover:text-green-700"
      >
        Load weather data
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useWeatherStore } from '@/stores/weather';
import { useFarmStore } from '@/stores/farm';

const props = defineProps({
  fieldId: {
    type: [String, Number],
    required: true
  }
});

const weatherStore = useWeatherStore();
const farmStore = useFarmStore();

const loading = ref(false);
const error = ref('');

const weather = computed(() => weatherStore.currentWeather);
const alerts = computed(() => weatherStore.weatherWarnings);

const isFavorableForFarming = computed(() => {
  if (!weather.value) return false;
  
  return weather.value.temperature >= 10 && 
         weather.value.temperature <= 35 &&
         weather.value.humidity >= 30 &&
         weather.value.humidity <= 80 &&
         weather.value.wind_speed < 20;
});

const refreshWeather = async () => {
  if (!props.fieldId) {
    error.value = 'No field ID provided';
    return;
  }
  
  loading.value = true;
  error.value = '';
  
  try {
    // Use Promise.allSettled to prevent one failure from breaking everything
    const results = await Promise.allSettled([
      weatherStore.fetchCurrentWeather(props.fieldId),
      weatherStore.fetchWeatherAlerts(props.fieldId)
    ]);
    
    // Check if any promises were rejected
    const failures = results.filter(result => result.status === 'rejected');
    if (failures.length > 0) {
      console.warn('Some weather data failed to load:', failures);
      if (failures.length === results.length) {
        error.value = 'Failed to fetch weather data';
      }
    }
  } catch (err) {
    error.value = err.message || 'Failed to fetch weather data';
  } finally {
    loading.value = false;
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

const getFarmingAdvice = () => {
  if (!weather.value) return '';
  
  const temp = weather.value.temperature;
  const humidity = weather.value.humidity;
  const wind = weather.value.wind_speed;
  
  if (temp < 10) return 'Low temperature may affect rice growth. Consider protective measures.';
  if (temp > 35) return 'High temperature stress. Ensure adequate irrigation.';
  if (humidity < 30) return 'Low humidity increases water requirements.';
  if (humidity > 80) return 'High humidity increases disease risk. Monitor for fungal infections.';
  if (wind > 20) return 'Strong winds may damage crops. Check for lodging.';
  
  return 'Optimal conditions for rice farming activities.';
};

const formatTime = (timestamp) => {
  return new Date(timestamp).toLocaleTimeString();
};

onMounted(() => {
  refreshWeather();
});
</script>
