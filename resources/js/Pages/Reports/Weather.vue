<template>
  <div class="weather-reports-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Weather Reports</h1>
          <p class="text-gray-600 mt-2">Analyze weather patterns and their impact on your farm</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="exportReport"
            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            Export Report
          </button>
          <button
            @click="generateReport"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Generate Report
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Report Filters</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
            <select
              v-model="dateRange"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="last7days">Last 7 Days</option>
              <option value="last30days">Last 30 Days</option>
              <option value="last3months">Last 3 Months</option>
              <option value="lastyear">Last Year</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Field</label>
            <select
              v-model="selectedField"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Fields</option>
              <option value="north">North Field</option>
              <option value="south">South Field</option>
              <option value="east">East Field</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Weather Station</label>
            <select
              v-model="selectedStation"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Stations</option>
              <option value="main">Main Station</option>
              <option value="north">North Station</option>
              <option value="south">South Station</option>
            </select>
          </div>
          <div class="flex items-end">
            <button
              @click="updateReport"
              class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              Update Report
            </button>
          </div>
        </div>
      </div>

      <!-- Weather Summary -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-blue-600 text-2xl">🌡️</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ weatherSummary.avgTemperature }}°F</div>
              <div class="text-sm text-gray-600">Avg Temperature</div>
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
              <div class="text-2xl font-bold text-gray-900">{{ weatherSummary.totalRainfall }}"</div>
              <div class="text-sm text-gray-600">Total Rainfall</div>
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
              <div class="text-2xl font-bold text-gray-900">{{ weatherSummary.avgWindSpeed }} mph</div>
              <div class="text-sm text-gray-600">Avg Wind Speed</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-blue-600 text-2xl">☀️</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ weatherSummary.sunshineHours }}h</div>
              <div class="text-sm text-gray-600">Sunshine Hours</div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Temperature Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold mb-4">Temperature Trends</h2>
          <div class="w-full" style="height: 300px;">
            <LineChart v-if="temperatureChartData.labels.length > 0" :data="temperatureChartData" />
            <div v-else class="h-full flex items-center justify-center text-gray-500">
              No temperature data available
            </div>
          </div>
        </div>

        <!-- Rainfall Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold mb-4">Rainfall Distribution</h2>
          <div class="w-full" style="height: 300px;">
            <BarChart v-if="rainfallChartData.labels.length > 0" :data="rainfallChartData" />
            <div v-else class="h-full flex items-center justify-center text-gray-500">
              No rainfall data available
            </div>
          </div>
        </div>
      </div>

      <!-- Growing Degree Days -->
      <div class="mt-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold mb-4">Growing Degree Days (GDD)</h2>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div class="text-center">
              <div class="text-3xl font-bold text-green-600">{{ gddData.today }}</div>
              <div class="text-sm text-gray-600">Today</div>
            </div>
            <div class="text-center">
              <div class="text-3xl font-bold text-blue-600">{{ gddData.week }}</div>
              <div class="text-sm text-gray-600">This Week</div>
            </div>
            <div class="text-center">
              <div class="text-3xl font-bold text-purple-600">{{ gddData.month }}</div>
              <div class="text-sm text-gray-600">This Month</div>
            </div>
            <div class="text-center">
              <div class="text-3xl font-bold text-yellow-600">{{ gddData.season }}</div>
              <div class="text-sm text-gray-600">This Season</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Weather Events -->
      <div class="mt-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold mb-4">Significant Weather Events</h2>
          <div class="space-y-4">
            <div
              v-for="event in weatherEvents"
              :key="event.id"
              class="flex items-start space-x-3 p-4 border border-gray-200 rounded-lg"
            >
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                  <span class="text-blue-600 text-sm">{{ getEventIcon(event.type) }}</span>
                </div>
              </div>
              <div class="flex-1">
                <div class="flex justify-between items-start">
                  <div>
                    <h3 class="font-medium text-gray-900">{{ event.title }}</h3>
                    <p class="text-sm text-gray-600 mt-1">{{ event.description }}</p>
                  </div>
                  <span class="text-sm text-gray-500">{{ formatDate(event.date) }}</span>
                </div>
                <div class="mt-2 flex items-center space-x-4 text-sm text-gray-600">
                  <span>Duration: {{ event.duration }}</span>
                  <span>Intensity: {{ event.intensity }}</span>
                  <span>Impact: {{ event.impact }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Weather Impact Analysis -->
      <div class="mt-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold mb-4">Weather Impact Analysis</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <h3 class="font-medium text-gray-900 mb-3">Crop Development</h3>
              <div class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Growth Stage</span>
                  <span class="font-medium">Vegetative</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Days to Maturity</span>
                  <span class="font-medium">45 days</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Stress Level</span>
                  <span class="font-medium text-green-600">Low</span>
                </div>
              </div>
            </div>
            <div>
              <h3 class="font-medium text-gray-900 mb-3">Field Conditions</h3>
              <div class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Soil Moisture</span>
                  <span class="font-medium">Optimal</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Field Workability</span>
                  <span class="font-medium">Good</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Disease Risk</span>
                  <span class="font-medium text-yellow-600">Moderate</span>
                </div>
              </div>
            </div>
            <div>
              <h3 class="font-medium text-gray-900 mb-3">Recommendations</h3>
              <div class="space-y-2">
                <div class="text-sm text-gray-600">
                  • Monitor soil moisture levels
                </div>
                <div class="text-sm text-gray-600">
                  • Consider fungicide application
                </div>
                <div class="text-sm text-gray-600">
                  • Plan irrigation schedule
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
import { ref, onMounted, computed } from 'vue'
import { weatherAPI } from '@/services/api'
import LineChart from '@/Components/Charts/LineChart.vue'
import BarChart from '@/Components/Charts/BarChart.vue'

const dateRange = ref('last30days')
const selectedField = ref('')
const selectedStation = ref('')
const weatherData = ref([])
const loading = ref(true)

const weatherSummary = ref({
  avgTemperature: 0,
  totalRainfall: 0,
  avgWindSpeed: 0,
  sunshineHours: 0
})

const gddData = ref({
  today: 15,
  week: 95,
  month: 420,
  season: 1250
})

const weatherEvents = ref([
  {
    id: 1,
    type: 'rain',
    title: 'Heavy Rainfall',
    description: 'Significant rainfall event with 1.2 inches in 6 hours',
    date: '2024-03-20T14:00:00Z',
    duration: '6 hours',
    intensity: 'Heavy',
    impact: 'Positive'
  },
  {
    id: 2,
    type: 'wind',
    title: 'Strong Winds',
    description: 'Wind speeds reached 25 mph with gusts up to 35 mph',
    date: '2024-03-18T10:30:00Z',
    duration: '4 hours',
    intensity: 'Strong',
    impact: 'Neutral'
  },
  {
    id: 3,
    type: 'frost',
    title: 'Frost Warning',
    description: 'Temperatures dropped to 32°F, potential frost damage',
    date: '2024-03-15T06:00:00Z',
    duration: '2 hours',
    intensity: 'Light',
    impact: 'Negative'
  }
])

const getEventIcon = (type) => {
  const icons = {
    rain: '🌧️',
    wind: '💨',
    frost: '❄️',
    heat: '🌡️',
    storm: '⛈️'
  }
  return icons[type] || '🌤️'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const updateReport = async () => {
  // Reload report data with current filters
  try {
    await loadWeatherData()
    alert('Report updated successfully')
  } catch (error) {
    console.error('Failed to update report:', error)
    alert('Failed to update report')
  }
}

const generateReport = async () => {
  // Generate new report with current filters
  try {
    await loadWeatherData()
    alert('Report generated successfully')
  } catch (error) {
    console.error('Failed to generate report:', error)
    alert('Failed to generate report')
  }
}

const exportReport = () => {
  // Export report as CSV
  try {
    const csvContent = generateCSV()
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    link.setAttribute('href', url)
    link.setAttribute('download', `weather-report-${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    alert('Report exported successfully')
  } catch (error) {
    console.error('Failed to export report:', error)
    alert('Failed to export report')
  }
}

const generateCSV = () => {
  // Generate CSV content from weather data
  const headers = ['Date', 'Temperature', 'Humidity', 'Rainfall', 'Wind Speed', 'Conditions']
  const rows = weatherData.value.map(item => [
    item.date || 'N/A',
    item.temperature || 0,
    item.humidity || 0,
    item.rainfall || 0,
    item.wind_speed || 0,
    item.conditions || 'N/A',
  ])
  
  return [headers, ...rows].map(row => row.join(',')).join('\n')
}

// Chart data computed properties
const temperatureChartData = computed(() => {
  if (!weatherData.value || weatherData.value.length === 0) {
    return { labels: [], datasets: [] }
  }
  
  return {
    labels: weatherData.value.map(item => {
      const date = new Date(item.recorded_at || item.date)
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
    }),
    datasets: [
      {
        label: 'Temperature (°C)',
        data: weatherData.value.map(item => item.temperature || 0),
        borderColor: 'rgb(239, 68, 68)',
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
        fill: true,
        tension: 0.4,
      }
    ]
  }
})

const rainfallChartData = computed(() => {
  if (!weatherData.value || weatherData.value.length === 0) {
    return { labels: [], datasets: [] }
  }
  
  // Group by date and sum rainfall
  const dailyRainfall = {}
  weatherData.value.forEach(item => {
    const date = new Date(item.recorded_at || item.date).toLocaleDateString()
    if (!dailyRainfall[date]) {
      dailyRainfall[date] = 0
    }
    dailyRainfall[date] += item.rainfall || 0
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

const loadWeatherData = async () => {
  try {
    loading.value = true
    // Load weather data for all fields or selected field
    // This is a placeholder - actual implementation would depend on the API structure
    weatherData.value = []
    
    // Calculate summary from data
    if (weatherData.value.length > 0) {
      weatherSummary.value = {
        avgTemperature: weatherData.value.reduce((sum, item) => sum + (item.temperature || 0), 0) / weatherData.value.length,
        totalRainfall: weatherData.value.reduce((sum, item) => sum + (item.rainfall || 0), 0),
        avgWindSpeed: weatherData.value.reduce((sum, item) => sum + (item.wind_speed || 0), 0) / weatherData.value.length,
        sunshineHours: 0, // Not available in weather data
      }
    }
  } catch (error) {
    console.error('Error loading weather data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadWeatherData()
})
</script>

<style scoped>
.weather-reports-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>