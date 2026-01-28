<template>
  <div class="field-weather-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <button
            type="button"
            @click="router.push('/weather')"
            class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-800 transition-colors mb-4"
          >
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Weather
          </button>
          <h1 class="text-3xl font-bold text-gray-900">{{ field.name }} Weather</h1>
          <p class="text-gray-600 mt-2">Detailed weather conditions for this field</p>
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
            @click="viewFieldDetails"
            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            View Field
          </button>
        </div>
      </div>

      <!-- Current Weather -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-blue-600 text-2xl">🌡️</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ Math.round(currentWeather.temperature) }}°C</div>
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
              <div class="text-2xl font-bold text-gray-900">{{ Math.round(currentWeather.humidity) }}%</div>
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
              <div class="text-2xl font-bold text-gray-900">{{ Number(currentWeather.rainfall || 0).toFixed(1) }} mm</div>
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
              <div class="text-2xl font-bold text-gray-900">{{ Math.round(currentWeather.wind_speed) }} km/h</div>
              <div class="text-sm text-gray-600">Wind Speed</div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Weather Chart -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Temperature & Humidity (7 Days)</h2>
            <div class="w-full" style="height: 300px;">
              <LineChart v-if="temperatureHumidityChartData.labels.length > 0" :data="temperatureHumidityChartData" />
              <div v-else class="h-full flex items-center justify-center text-gray-500">
                No temperature/humidity data available
              </div>
            </div>
          </div>

          <!-- Rainfall Chart -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Rainfall (7 days)</h2>
            <div class="w-full" style="height: 300px;">
              <BarChart v-if="rainfallChartData.labels.length > 0" :data="rainfallChartData" />
              <div v-else class="h-full flex items-center justify-center text-gray-500">
                No rainfall data available
              </div>
            </div>
          </div>

          <!-- Weather History -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Weather History</h2>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">High</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Low</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rainfall</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Humidity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Wind</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="day in processedWeatherHistory" :key="day.date || day.recorded_at">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDate(day.date || day.recorded_at) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ Math.round(day.high || day.temperature || day.max_temperature || 0) }}°C
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ Math.round(day.low || day.min_temperature || day.temperature || 0) }}°C
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ Number(day.rainfall || day.precipitation || 0).toFixed(1) }} mm
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ Math.round(day.humidity || 0) }}%
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ Math.round((day.wind_speed || 0) * 3.6) }} km/h
                    </td>
                  </tr>
                  <tr v-if="processedWeatherHistory.length === 0">
                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                      No weather history data available
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Field Details -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Field Details</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Size:</span>
                <span class="font-medium">{{ field.size > 0 ? field.size.toFixed(2) : 'Not set' }} {{ field.size > 0 ? 'hectares' : '' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Soil Type:</span>
                <span class="font-medium">{{ field.soil_type }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Current Crop:</span>
                <span class="font-medium">{{ field.current_crop || 'None' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Location:</span>
                <span class="font-medium">{{ fieldLocation }}</span>
              </div>
            </div>
          </div>

          <!-- Growing Degree Days -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Growing Degree Days</h3>
            <p class="text-xs text-gray-500 mb-3">
              GDD measures heat accumulation for crop growth. Calculated as daily average temperature minus base temperature (10°C for rice). Higher GDD indicates faster growth.
            </p>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Today:</span>
                <span class="font-medium">{{ gdd.today }} GDD</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">This Week:</span>
                <span class="font-medium">{{ gdd.week }} GDD</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">This Month:</span>
                <span class="font-medium">{{ gdd.month }} GDD</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">This Season:</span>
                <span class="font-medium">{{ gdd.season }} GDD</span>
              </div>
            </div>
          </div>

          <!-- Soil Conditions -->
          <div v-if="soilConditions.available" class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Soil Conditions</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Soil Temperature:</span>
                <span class="font-medium">{{ Math.round(soilConditions.temperature) }}°C</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Soil Moisture:</span>
                <span class="font-medium">{{ Math.round(soilConditions.moisture) }}%</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">pH Level:</span>
                <span class="font-medium">{{ soilConditions.ph }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Last Updated:</span>
                <span class="font-medium">{{ formatDate(soilConditions.last_updated) }}</span>
              </div>
            </div>
          </div>

          <!-- Weather Alerts -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Field-Specific Alerts</h3>
            <div class="space-y-3">
              <div
                v-for="alert in fieldAlerts"
                :key="alert.id"
                :class="getAlertClass(alert.severity)"
                class="p-3 rounded-lg border-l-4"
              >
                <div class="font-medium text-sm">{{ alert.title }}</div>
                <div class="text-xs text-gray-600 mt-1">{{ alert.description }}</div>
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
                @click="viewWeatherAnalytics"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                📈 View Weather Analytics
              </button>
              <button
                @click="exportFieldData"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                📤 Export Field Data
              </button>
              <button
                @click="viewWeatherDashboard"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                🏠 View Weather Dashboard
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { weatherAPI, fieldsAPI } from '@/services/api'
import LineChart from '@/Components/Charts/LineChart.vue'
import BarChart from '@/Components/Charts/BarChart.vue'

const route = useRoute()
const router = useRouter()
const loading = ref(false)

const field = ref({
  id: null,
  name: '',
  size: 0,
  soil_type: '',
  current_crop: '',
  weather_station: ''
})

const currentWeather = ref({
  temperature: 0,
  humidity: 0,
  rainfall: 0,
  wind_speed: 0
})

const weatherHistory = ref([])
const weatherData24h = ref([])
const weatherData7d = ref([])

const gdd = ref({
  today: 0,
  week: 0,
  month: 0,
  season: 0
})

const soilConditions = ref({
  available: false,
  temperature: 0,
  moisture: 0,
  ph: 0,
  last_updated: null
})

const fieldAlerts = ref([])

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const getAlertClass = (severity) => {
  const classes = {
    warning: 'bg-yellow-50 border-yellow-400',
    info: 'bg-blue-50 border-blue-400',
    danger: 'bg-red-50 border-red-400'
  }
  return classes[severity] || 'bg-gray-50 border-gray-400'
}

const refreshWeather = async () => {
  if (!field.value.id) return
  await loadFieldWeatherData(field.value.id)
}

const viewFieldDetails = () => {
  router.push(`/fields/${field.value.id}`)
}

const viewHistoricalData = () => {
  // Navigate to weather analytics page with field filter (consistent with dashboard)
  router.push({
    path: '/weather/analytics',
    query: { field: field.value.id }
  })
}

const viewWeatherAnalytics = () => {
  // Navigate to weather analytics page with field filter
  router.push({
    path: '/weather/analytics',
    query: { field: field.value.id }
  })
}

const exportFieldData = async () => {
  try {
    // Export weather data as CSV
    const response = await weatherAPI.getHistory(field.value.id, 30)
    let historyData = []
    
    if (response.data?.weather_logs?.data) {
      historyData = response.data.weather_logs.data
    } else if (Array.isArray(response.data?.data)) {
      historyData = response.data.data
    } else if (Array.isArray(response.data)) {
      historyData = response.data
    }
    
    if (historyData.length === 0) {
      alert('No weather data available to export')
      return
    }
    
    // Create CSV content
    const headers = ['Date', 'Temperature (°C)', 'Humidity (%)', 'Rainfall (mm)', 'Wind Speed (km/h)', 'Conditions']
    const rows = historyData.map(item => {
      const date = new Date(item.recorded_at || item.date).toLocaleString()
      const temp = item.temperature || 0
      const humidity = item.humidity || 0
      const rainfall = item.rainfall || item.precipitation || 0
      const windSpeed = (item.wind_speed || 0) * 3.6 // Convert m/s to km/h
      const conditions = item.conditions || item.weather_condition || 'N/A'
      
      return [date, temp, humidity, rainfall, windSpeed.toFixed(1), conditions].join(',')
    })
    
    const csvContent = [
      headers.join(','),
      ...rows
    ].join('\n')
    
    // Create and download file
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    link.setAttribute('href', url)
    link.setAttribute('download', `field-${field.value.id}-weather-${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
  } catch (error) {
    console.error('Error exporting field data:', error)
    alert('Failed to export weather data. Please try again.')
  }
}

const viewWeatherDashboard = () => {
  // Navigate to weather dashboard
  router.push('/weather')
}

onMounted(() => {
  const fieldId = route.params.id
  // Load field weather data from API
  loadFieldWeatherData(fieldId)
})

const loadFieldWeatherData = async (id) => {
  try {
    loading.value = true
    
    // Load field data
    const fieldResponse = await fieldsAPI.getById(id)
    const fieldData = fieldResponse.data.field || fieldResponse.data.data || fieldResponse.data
    
    // Get current crop from plantings if available
    let currentCrop = 'None'
    if (fieldData.plantings && Array.isArray(fieldData.plantings) && fieldData.plantings.length > 0) {
      // Find active planting (planted or growing status)
      const activePlanting = fieldData.plantings
        .filter(p => p.status === 'planted' || p.status === 'growing')
        .sort((a, b) => new Date(b.planting_date || 0) - new Date(a.planting_date || 0))[0]
      
      if (activePlanting) {
        // Try to get rice variety name
        if (activePlanting.rice_variety && activePlanting.rice_variety.name) {
          currentCrop = activePlanting.rice_variety.name
        } else if (activePlanting.rice_variety_id) {
          // If we have variety ID but not loaded, just show crop type
          currentCrop = activePlanting.crop_type ? activePlanting.crop_type.charAt(0).toUpperCase() + activePlanting.crop_type.slice(1) : 'Rice'
        } else if (activePlanting.crop_type) {
          currentCrop = activePlanting.crop_type.charAt(0).toUpperCase() + activePlanting.crop_type.slice(1)
        }
      }
    }
    
    // Get size - convert to hectares if needed (assuming size is in hectares)
    const size = parseFloat(fieldData.size) || 0
    
    field.value = {
      id: fieldData.id,
      name: fieldData.name || '',
      size: size,
      soil_type: fieldData.soil_type || 'Not specified',
      current_crop: currentCrop,
      location: fieldData.location || fieldData.field_coordinates || {}
    }
    
    // Load current weather
    try {
      const currentResponse = await weatherAPI.getCurrentWeather(id)
      const responseData = currentResponse.data
      const weatherData = responseData.weather || responseData.data || responseData
      
      if (weatherData) {
        // Handle temperature - convert from Fahrenheit if needed, or use Celsius
        let temp = weatherData.temperature || 0
        if (temp > 100) {
          // Likely Fahrenheit, convert to Celsius
          temp = (temp - 32) * 5/9
        }
        
        // Handle wind speed - convert from m/s to km/h if needed
        let windSpeed = weatherData.wind_speed || 0
        if (windSpeed < 50) {
          // Likely m/s, convert to km/h
          windSpeed = windSpeed * 3.6
        }
        
        // Handle rainfall - convert from inches to mm if needed
        let rainfall = weatherData.rainfall || weatherData.precipitation || 0
        if (rainfall > 0 && rainfall < 1) {
          // Likely inches, convert to mm
          rainfall = rainfall * 25.4
        }
        
        currentWeather.value = {
          temperature: temp,
          humidity: weatherData.humidity || 0,
          rainfall: rainfall,
          wind_speed: windSpeed,
        }
        
        // If no history data, create a single data point from current weather for the chart
        if (weatherData7d.value.length === 0 && currentWeather.value.temperature > 0) {
          weatherData7d.value = [{
            recorded_at: new Date().toISOString(),
            date: new Date().toISOString(),
            temperature: currentWeather.value.temperature,
            humidity: currentWeather.value.humidity,
            rainfall: currentWeather.value.rainfall,
            wind_speed: currentWeather.value.wind_speed
          }]
          weatherHistory.value = weatherData7d.value
        }
      }
    } catch (weatherError) {
      console.error('Error loading current weather:', weatherError)
    }
    
    // Load weather history (last 7 days)
    try {
      const historyResponse = await weatherAPI.getHistory(id, 7)
      // Handle paginated response structure
      let historyData = []
      if (historyResponse.data) {
        if (historyResponse.data.weather_logs) {
          // Paginated response
          historyData = historyResponse.data.weather_logs.data || historyResponse.data.weather_logs || []
        } else if (Array.isArray(historyResponse.data.data)) {
          // Direct array in data
          historyData = historyResponse.data.data
        } else if (Array.isArray(historyResponse.data)) {
          // Direct array
          historyData = historyResponse.data
        }
      }
      // Ensure it's an array
      weatherHistory.value = Array.isArray(historyData) ? historyData : []
      weatherData7d.value = Array.isArray(historyData) ? historyData : []
      
      console.log('Loaded weather history:', weatherData7d.value.length, 'records')
      if (weatherData7d.value.length > 0) {
        // Log actual data structure for debugging
        const sample = weatherData7d.value[0]
        console.log('Sample weather data structure:', {
          recorded_at: sample.recorded_at,
          date: sample.date,
          temperature: sample.temperature,
          humidity: sample.humidity,
          rainfall: sample.rainfall,
          wind_speed: sample.wind_speed,
          conditions: sample.conditions
        })
      } else {
        console.warn('No weather history data found. Will use current weather if available.')
      }
    } catch (error) {
      console.error('Error loading weather history:', error)
      weatherHistory.value = []
      weatherData7d.value = []
    }
    
    // Load 24h weather data (last 24 hours)
    try {
      const history24hResponse = await weatherAPI.getHistory(id, 1)
      let history24hData = []
      if (history24hResponse.data) {
        if (history24hResponse.data.weather_logs) {
          history24hData = history24hResponse.data.weather_logs.data || history24hResponse.data.weather_logs || []
        } else if (Array.isArray(history24hResponse.data.data)) {
          history24hData = history24hResponse.data.data
        } else if (Array.isArray(history24hResponse.data)) {
          history24hData = history24hResponse.data
        }
      }
      weatherData24h.value = Array.isArray(history24hData) ? history24hData : []
    } catch (error) {
      console.warn('Error loading 24h weather data:', error)
      weatherData24h.value = []
    }
    
    // Load weather alerts
    try {
      const alertsResponse = await weatherAPI.getAlerts(id)
      const alertsData = alertsResponse.data.alerts || alertsResponse.data || []
      fieldAlerts.value = Array.isArray(alertsData) ? alertsData.map((alert, index) => ({
        id: alert.id || index + 1,
        title: alert.title || alert.type || alert.message || 'Weather Alert',
        description: alert.description || alert.message || '',
        severity: alert.severity || alert.level || 'info'
      })) : []
    } catch (error) {
      console.warn('Error loading weather alerts:', error)
      fieldAlerts.value = []
    }
    
    // Calculate GDD from weather data
    calculateGDD()
    
  } catch (error) {
    console.error('Error loading field weather data:', error)
  } finally {
    loading.value = false
  }
}

// Calculate Growing Degree Days from weather history
const calculateGDD = () => {
  if (!weatherData7d.value || weatherData7d.value.length === 0) {
    gdd.value = { today: 0, week: 0, month: 0, season: 0 }
    return
  }
  
  // Base temperature for rice (10°C)
  const baseTemp = 10
  const today = new Date()
  const todayStr = today.toISOString().split('T')[0]
  
  // Filter today's data
  const todayData = weatherData7d.value.filter(item => {
    const date = new Date(item.recorded_at || item.date)
    return date.toISOString().split('T')[0] === todayStr
  })
  
  // Calculate today's GDD
  let todayGDD = 0
  if (todayData.length > 0) {
    const avgTemp = todayData.reduce((sum, item) => sum + (item.temperature || 0), 0) / todayData.length
    todayGDD = Math.max(0, avgTemp - baseTemp)
  }
  
  // Calculate week's GDD (last 7 days)
  let weekGDD = 0
  const weekData = weatherData7d.value.slice(-7)
  if (weekData.length > 0) {
    weekData.forEach(item => {
      const temp = item.temperature || 0
      weekGDD += Math.max(0, temp - baseTemp)
    })
  }
  
  // For month and season, we'd need more data, so estimate based on week
  const monthGDD = weekGDD * 4 // Rough estimate
  const seasonGDD = weekGDD * 12 // Rough estimate
  
  gdd.value = {
    today: Math.round(todayGDD),
    week: Math.round(weekGDD),
    month: Math.round(monthGDD),
    season: Math.round(seasonGDD)
  }
}

// Process weather history for table display
const processedWeatherHistory = computed(() => {
  if (!weatherHistory.value || weatherHistory.value.length === 0) {
    return []
  }
  
  // Group by date and calculate daily stats
  const dailyStats = {}
  
  weatherHistory.value.forEach(item => {
    const date = new Date(item.recorded_at || item.date).toISOString().split('T')[0]
    
    if (!dailyStats[date]) {
      dailyStats[date] = {
        date: date,
        temperatures: [],
        humidities: [],
        rainfalls: [],
        windSpeeds: []
      }
    }
    
    const temp = parseFloat(item.temperature || 0)
    dailyStats[date].temperatures.push(temp)
    dailyStats[date].humidities.push(parseFloat(item.humidity || 0))
    dailyStats[date].rainfalls.push(parseFloat(item.rainfall || item.precipitation || 0))
    dailyStats[date].windSpeeds.push(parseFloat(item.wind_speed || 0))
  })
  
  // Convert to array with min/max/avg
  return Object.values(dailyStats).map(day => ({
    date: day.date,
    high: Math.max(...day.temperatures),
    low: Math.min(...day.temperatures),
    humidity: day.humidities.length > 0 ? day.humidities.reduce((a, b) => a + b, 0) / day.humidities.length : 0,
    rainfall: day.rainfalls.reduce((a, b) => a + b, 0),
    wind_speed: day.windSpeeds.length > 0 ? day.windSpeeds.reduce((a, b) => a + b, 0) / day.windSpeeds.length : 0
  })).sort((a, b) => new Date(b.date) - new Date(a.date))
})

// Field location display
const fieldLocation = computed(() => {
  if (!field.value.location) return 'Not set'
  const loc = field.value.location
  if (loc.lat && loc.lon) {
    return `${loc.lat.toFixed(4)}, ${loc.lon.toFixed(4)}`
  }
  return 'Not set'
})

// Chart data computed properties - 7 days data
const temperatureHumidityChartData = computed(() => {
  // Use 7-day data, grouped by day for better visualization
  let data = []
  
  if (weatherData7d.value && Array.isArray(weatherData7d.value) && weatherData7d.value.length > 0) {
    data = weatherData7d.value
  } else if (currentWeather.value && currentWeather.value.temperature > 0) {
    // Fallback: use current weather as a single data point
    data = [{
      recorded_at: new Date().toISOString(),
      date: new Date().toISOString().split('T')[0],
      temperature: currentWeather.value.temperature,
      humidity: currentWeather.value.humidity
    }]
  }
  
  if (data.length === 0) {
    return { labels: [], datasets: [] }
  }
  
  // Group by date and calculate daily averages for 7-day view
  const dailyData = {}
  data.forEach(item => {
    // Handle different date formats
    let dateStr = null
    if (item.date) {
      dateStr = typeof item.date === 'string' ? item.date.split('T')[0] : new Date(item.date).toISOString().split('T')[0]
    } else if (item.recorded_at) {
      dateStr = typeof item.recorded_at === 'string' ? item.recorded_at.split('T')[0] : new Date(item.recorded_at).toISOString().split('T')[0]
    } else {
      dateStr = new Date().toISOString().split('T')[0]
    }
    
    if (!dailyData[dateStr]) {
      dailyData[dateStr] = {
        date: dateStr,
        temperatures: [],
        humidities: []
      }
    }
    
    // Handle different temperature field names and formats
    let temp = item.temperature || item.temp || item.avg_temperature || 0
    // Convert to number if string
    temp = typeof temp === 'string' ? parseFloat(temp) : temp
    // If temperature seems like Fahrenheit (> 100), convert to Celsius
    if (temp > 100) {
      temp = (temp - 32) * 5/9
    }
    
    // Handle different humidity field names
    let humidity = item.humidity || item.avg_humidity || 0
    humidity = typeof humidity === 'string' ? parseFloat(humidity) : humidity
    
    if (temp > -50 && temp < 60) dailyData[dateStr].temperatures.push(temp) // Valid temperature range
    if (humidity >= 0 && humidity <= 100) dailyData[dateStr].humidities.push(humidity) // Valid humidity range
  })
  
  // Convert to array and calculate averages
  const processedData = Object.values(dailyData)
    .map(day => ({
      date: day.date,
      temp: day.temperatures.length > 0 ? day.temperatures.reduce((a, b) => a + b, 0) / day.temperatures.length : null,
      humidity: day.humidities.length > 0 ? day.humidities.reduce((a, b) => a + b, 0) / day.humidities.length : null
    }))
    .filter(item => (item.temp !== null && item.temp > -50 && item.temp < 60) || (item.humidity !== null && item.humidity >= 0 && item.humidity <= 100))
    .sort((a, b) => new Date(a.date) - new Date(b.date))
    .slice(-7) // Last 7 days
  
  if (processedData.length === 0) {
    return { labels: [], datasets: [] }
  }
  
  return {
    labels: processedData.map(item => {
      try {
        const date = new Date(item.date)
        return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })
      } catch (e) {
        return item.date
      }
    }),
    datasets: [
      {
        label: 'Temperature (°C)',
        data: processedData.map(item => Math.round(item.temp * 10) / 10),
        borderColor: 'rgb(239, 68, 68)',
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
        yAxisID: 'y',
      },
      {
        label: 'Humidity (%)',
        data: processedData.map(item => Math.round(item.humidity)),
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        yAxisID: 'y1',
      }
    ]
  }
})

const rainfallChartData = computed(() => {
  if (!weatherData7d.value || !Array.isArray(weatherData7d.value) || weatherData7d.value.length === 0) {
    return { labels: [], datasets: [] }
  }
  
  // Group by date and sum rainfall
  const dailyRainfall = {}
  weatherData7d.value.forEach(item => {
    const date = new Date(item.recorded_at || item.date).toLocaleDateString()
    if (!dailyRainfall[date]) {
      dailyRainfall[date] = 0
    }
    dailyRainfall[date] += parseFloat(item.rainfall || item.precipitation || 0)
  })
  
  return {
    labels: Object.keys(dailyRainfall),
    datasets: [
      {
        label: 'Rainfall (mm)',
        data: Object.values(dailyRainfall),
        backgroundColor: 'rgba(59, 130, 246, 0.8)',
      }
    ]
  }
})
</script>

<style scoped>
.field-weather-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>