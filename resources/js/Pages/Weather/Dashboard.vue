<template>
  <div class="weather-dashboard-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Weather Dashboard</h1>
          <p class="text-gray-600 mt-2">Monitor weather conditions across your farm</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="refreshWeather"
            :disabled="loading"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
          >
            {{ loading ? 'Refreshing...' : 'Refresh' }}
          </button>
          <button
            @click="viewForecast"
            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            View Forecast
          </button>
        </div>
      </div>

      <!-- Current Weather Overview -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-blue-600 text-2xl">🌡️</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ currentWeather.temperature }}°F</div>
              <div class="text-sm text-gray-600">Temperature</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-blue-600 text-2xl">💧</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ currentWeather.humidity }}%</div>
              <div class="text-sm text-gray-600">Humidity</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-blue-600 text-2xl">🌧️</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ currentWeather.rainfall }} in</div>
              <div class="text-sm text-gray-600">Rainfall (24h)</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-blue-600 text-2xl">💨</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ currentWeather.wind_speed }} mph</div>
              <div class="text-sm text-gray-600">Wind Speed</div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Weather Map -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Weather Map</h2>
            <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
              <span class="text-gray-500">Interactive weather map placeholder</span>
            </div>
          </div>

          <!-- 7-Day Forecast -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">7-Day Forecast</h2>
            <div class="space-y-4">
              <div
                v-for="day in forecast"
                :key="day.date"
                class="flex items-center justify-between p-4 border border-gray-200 rounded-lg"
              >
                <div class="flex items-center space-x-4">
                  <div class="text-sm font-medium text-gray-900 w-20">
                    {{ formatDate(day.date) }}
                  </div>
                  <div class="text-2xl">{{ day.icon }}</div>
                  <div>
                    <div class="font-medium text-gray-900">{{ day.condition }}</div>
                    <div class="text-sm text-gray-600">{{ day.description }}</div>
                  </div>
                </div>
                <div class="flex items-center space-x-4">
                  <div class="text-right">
                    <div class="font-medium text-gray-900">{{ day.high }}°F</div>
                    <div class="text-sm text-gray-600">{{ day.low }}°F</div>
                  </div>
                  <div class="text-right text-sm text-gray-600">
                    <div>{{ day.rain_chance }}% rain</div>
                    <div>{{ day.wind_speed }} mph</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Weather Alerts -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Weather Alerts</h2>
            <div class="space-y-4">
              <div
                v-for="alert in weatherAlerts"
                :key="alert.id"
                :class="getAlertClass(alert.severity)"
                class="p-4 rounded-lg border-l-4"
              >
                <div class="flex items-start">
                  <div class="flex-shrink-0">
                    <span class="text-lg">{{ getAlertIcon(alert.severity) }}</span>
                  </div>
                  <div class="ml-3">
                    <h3 class="font-medium">{{ alert.title }}</h3>
                    <p class="text-sm mt-1">{{ alert.description }}</p>
                    <p class="text-xs mt-2 text-gray-500">{{ formatDate(alert.issued_at) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Field Weather Summary -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Field Weather Summary</h3>
            <div class="space-y-4">
              <div
                v-for="field in fieldWeather"
                :key="field.id"
                class="p-3 border border-gray-200 rounded-lg"
              >
                <div class="flex justify-between items-start mb-2">
                  <h4 class="font-medium text-gray-900">{{ field.name }}</h4>
                  <span class="text-sm text-gray-600">{{ field.temperature }}°F</span>
                </div>
                <div class="text-sm text-gray-600">
                  <div>Humidity: {{ field.humidity }}%</div>
                  <div>Rainfall: {{ field.rainfall }} in</div>
                </div>
                <button
                  @click="viewFieldWeather(field.id)"
                  class="mt-2 text-blue-600 hover:text-blue-800 text-sm"
                >
                  View Details →
                </button>
              </div>
            </div>
          </div>

          <!-- Growing Degree Days -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Growing Degree Days</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Today:</span>
                <span class="font-medium">{{ gdd.today }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">This Week:</span>
                <span class="font-medium">{{ gdd.week }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">This Month:</span>
                <span class="font-medium">{{ gdd.month }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">This Season:</span>
                <span class="font-medium">{{ gdd.season }}</span>
              </div>
            </div>
          </div>

          <!-- Weather Station Status -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Weather Station Status</h3>
            <div class="space-y-3">
              <div
                v-for="station in weatherStations"
                :key="station.id"
                class="flex items-center justify-between"
              >
                <div>
                  <div class="font-medium text-gray-900">{{ station.name }}</div>
                  <div class="text-sm text-gray-600">{{ station.location }}</div>
                </div>
                <div class="flex items-center">
                  <div
                    :class="station.status === 'online' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ station.status }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
            <div class="space-y-3">
              <button
                @click="viewHistoricalData"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                📊 View Historical Data
              </button>
              <button
                @click="setWeatherAlerts"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                🔔 Set Weather Alerts
              </button>
              <button
                @click="exportWeatherData"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                📤 Export Weather Data
              </button>
              <button
                @click="viewWeatherReports"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                📈 View Weather Reports
              </button>
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

const router = useRouter()
const loading = ref(false)

const currentWeather = ref({
  temperature: 72,
  humidity: 65,
  rainfall: 0.2,
  wind_speed: 5,
  condition: 'Partly Cloudy',
  icon: '⛅'
})

const forecast = ref([
  {
    date: '2024-03-26',
    condition: 'Sunny',
    description: 'Clear skies',
    high: 75,
    low: 55,
    rain_chance: 10,
    wind_speed: 8,
    icon: '☀️'
  },
  {
    date: '2024-03-27',
    condition: 'Partly Cloudy',
    description: 'Some clouds',
    high: 73,
    low: 52,
    rain_chance: 20,
    wind_speed: 6,
    icon: '⛅'
  },
  {
    date: '2024-03-28',
    condition: 'Rain',
    description: 'Light rain expected',
    high: 68,
    low: 48,
    rain_chance: 80,
    wind_speed: 12,
    icon: '🌧️'
  },
  {
    date: '2024-03-29',
    condition: 'Cloudy',
    description: 'Overcast',
    high: 65,
    low: 45,
    rain_chance: 30,
    wind_speed: 10,
    icon: '☁️'
  },
  {
    date: '2024-03-30',
    condition: 'Sunny',
    description: 'Clear skies',
    high: 70,
    low: 50,
    rain_chance: 5,
    wind_speed: 7,
    icon: '☀️'
  }
])

const weatherAlerts = ref([
  {
    id: 1,
    title: 'Frost Warning',
    description: 'Temperatures expected to drop below freezing tonight. Protect sensitive crops.',
    severity: 'warning',
    issued_at: '2024-03-25T18:00:00Z'
  },
  {
    id: 2,
    title: 'Heavy Rain Expected',
    description: 'Heavy rainfall expected in 2 days. Consider delaying field work.',
    severity: 'info',
    issued_at: '2024-03-25T12:00:00Z'
  }
])

const fieldWeather = ref([
  {
    id: 1,
    name: 'North Field',
    temperature: 71,
    humidity: 63,
    rainfall: 0.1
  },
  {
    id: 2,
    name: 'South Field',
    temperature: 73,
    humidity: 67,
    rainfall: 0.3
  },
  {
    id: 3,
    name: 'East Field',
    temperature: 70,
    humidity: 65,
    rainfall: 0.2
  }
])

const gdd = ref({
  today: 15,
  week: 95,
  month: 420,
  season: 1250
})

const weatherStations = ref([
  {
    id: 1,
    name: 'Main Station',
    location: 'Farm Center',
    status: 'online'
  },
  {
    id: 2,
    name: 'North Station',
    location: 'North Field',
    status: 'online'
  },
  {
    id: 3,
    name: 'South Station',
    location: 'South Field',
    status: 'offline'
  }
])

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', { 
    weekday: 'short', 
    month: 'short', 
    day: 'numeric' 
  })
}

const getAlertClass = (severity) => {
  const classes = {
    warning: 'bg-yellow-50 border-yellow-400',
    info: 'bg-blue-50 border-blue-400',
    danger: 'bg-red-50 border-red-400'
  }
  return classes[severity] || 'bg-gray-50 border-gray-400'
}

const getAlertIcon = (severity) => {
  const icons = {
    warning: '⚠️',
    info: 'ℹ️',
    danger: '🚨'
  }
  return icons[severity] || '📢'
}

const refreshWeather = async () => {
  loading.value = true
  try {
    // API call to refresh weather data
    await new Promise(resolve => setTimeout(resolve, 1000)) // Simulate API call
  } catch (error) {
    console.error('Error refreshing weather:', error)
  } finally {
    loading.value = false
  }
}

const viewForecast = () => {
  const defaultField = fieldWeather.value?.[0]?.id
  if (defaultField) {
    router.push({
      path: '/weather/analytics',
      query: { field: defaultField }
    })
  } else {
    router.push('/weather/analytics')
  }
}

const viewFieldWeather = (fieldId) => {
  router.push(`/weather/fields/${fieldId}`)
}

const viewHistoricalData = () => {
  // Navigate to historical data page
  console.log('View historical data')
}

const setWeatherAlerts = () => {
  // Show weather alerts settings modal
  console.log('Set weather alerts')
}

const exportWeatherData = () => {
  // Export weather data
  console.log('Export weather data')
}

const viewWeatherReports = () => {
  router.push('/reports/weather')
}

onMounted(() => {
  // Load weather data from API
})
</script>

<style scoped>
.weather-dashboard-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>