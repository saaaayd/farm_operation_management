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
          <!-- Weather Map -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-semibold">Weather Map</h2>
              <div class="flex items-center space-x-2">
                <select
                  v-model="selectedWeatherLayer"
                  @change="updateWeatherLayer"
                  class="text-sm px-3 py-1 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="windy">Windy.com (Full Featured)</option>
                  <option value="temperature">Temperature</option>
                  <option value="precipitation">Precipitation</option>
                  <option value="clouds">Clouds</option>
                  <option value="pressure">Pressure</option>
                  <option value="wind">Wind</option>
                  <option value="base">Base Map Only</option>
                </select>
                <button
                  @click="refreshWeather"
                  :disabled="loading"
                  class="text-sm px-3 py-1 bg-green-100 text-green-700 rounded-md hover:bg-green-200 disabled:opacity-50"
                >
                  {{ loading ? 'Refreshing...' : 'Refresh' }}
                </button>
              </div>
            </div>
            <!-- Windy.com Embed (Full Featured Weather Map) -->
            <div v-if="selectedWeatherLayer === 'windy'" class="w-full rounded-lg overflow-hidden" style="height: 500px;">
              <iframe
                ref="windyIframe"
                src="https://embed.windy.com/embed2.html?lat=14.5995&lon=120.9842&zoom=6&level=surface&overlay=wind&menu=&message=&marker=&calendar=now&pressure=&type=map&location=coordinates&detail=&detailLat=14.5995&detailLon=120.9842&metricWind=default&metricTemp=default&radarRange=-1"
                width="100%"
                height="100%"
                frameborder="0"
                style="border: none;"
                @error="handleIframeError"
                sandbox="allow-scripts allow-same-origin allow-popups allow-forms allow-popups-to-escape-sandbox"
                @load="handleIframeLoad"
              ></iframe>
            </div>
            <!-- Leaflet Map with Weather Overlays -->
            <div v-else ref="mapContainer" class="w-full rounded-lg" style="height: 500px; z-index: 0;"></div>
          </div>

          <!-- 7-Day Forecast -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">7-Day Forecast</h2>
            <div v-if="loading && forecast.length === 0" class="text-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
              <p class="text-gray-500 mt-4">Loading forecast...</p>
            </div>
            <div v-else-if="forecast.length === 0" class="text-center py-8">
              <p class="text-gray-500">No forecast data available</p>
              <button
                @click="refreshWeather"
                class="mt-4 text-blue-600 hover:text-blue-700 text-sm font-medium"
              >
                Try refreshing weather data
              </button>
            </div>
            <div v-else class="space-y-4">
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
                    <div class="font-medium text-gray-900">
                      {{ (day.high !== null && !isNaN(day.high)) ? Math.round(day.high) + '°C' : '--' }}
                    </div>
                    <div class="text-sm text-gray-600">
                      {{ (day.low !== null && !isNaN(day.low)) ? Math.round(day.low) + '°C' : '--' }}
                    </div>
                  </div>
                  <div class="text-right text-sm text-gray-600">
                    <div>
                      {{ (day.rain_chance !== null && !isNaN(day.rain_chance)) ? Math.round(day.rain_chance) + '%' : '--' }} rain
                    </div>
                    <div>
                      {{ (day.wind_speed !== null && !isNaN(day.wind_speed)) ? Math.round(day.wind_speed * 3.6) + ' km/h' : '--' }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Weather Alerts -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Weather Alerts</h2>
            <div v-if="weatherAlerts.length > 0" class="space-y-4">
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
            <div v-else class="text-center py-8 text-gray-500">
              <svg class="h-12 w-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <p class="text-sm">No alerts — weather looks good for farming!</p>
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
                  <span class="text-sm text-gray-600">{{ Math.round(field.temperature) }}°C</span>
                </div>
                <div class="text-sm text-gray-600">
                  <div>Humidity: {{ Math.round(field.humidity) }}%</div>
                  <div>Rainfall: {{ Number(field.rainfall || 0) > 0 ? Number(field.rainfall).toFixed(1) : '0.0' }} mm</div>
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
            <div v-if="forecast.length === 0" class="text-center py-4 text-gray-500">
              <p class="text-sm">Loading weather data...</p>
            </div>
            <div v-else class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Today:</span>
                <span class="font-medium">{{ gdd.today }}°</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">This Week:</span>
                <span class="font-medium">{{ gdd.week }}°</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">This Month:</span>
                <span class="font-medium text-gray-500">~{{ gdd.month }}°</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">This Season:</span>
                <span class="font-medium text-gray-500">~{{ gdd.season }}°</span>
              </div>
              <div class="mt-4 pt-3 border-t border-gray-100">
                <div class="flex items-start space-x-2">
                  <span class="text-blue-500 text-lg">ℹ️</span>
                  <div>
                    <h4 class="text-xs font-semibold text-gray-700 uppercase tracking-wide mb-1">About GDD</h4>
                    <p class="text-xs text-gray-600 leading-relaxed mb-2">
                      Growing Degree Days (GDD) measure heat accumulation to estimate crop maturity. Higher values indicate faster growth.
                    </p>
                    <div class="bg-gray-50 p-2 rounded text-xs font-mono text-gray-600 border border-gray-100 mb-2">
                      ((Max Temp + Min Temp) / 2) - 10°C = Daily GDD
                    </div>
                    <p class="text-xs text-gray-500 italic">
                      Rice typically requires ~2200 cumulative GDD to reach maturity.
                    </p>
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
import { ref, onMounted, onBeforeUnmount, watch, computed } from 'vue'
import { useRouter } from 'vue-router'
import { useFarmStore } from '@/stores/farm'
import { useWeatherStore } from '@/stores/weather'

const router = useRouter()
const farmStore = useFarmStore()
const weatherStore = useWeatherStore()

const loading = ref(false)
const mapContainer = ref(null)
const map = ref(null)
const markers = ref([])
const fieldWeatherData = ref({}) // Store fetched weather data for each field
const selectedWeatherLayer = ref('windy') // Default to Windy.com embed
const currentWeatherLayer = ref(null) // Current active weather overlay
const windyIframe = ref(null)

// Get fields from store
const fields = computed(() => farmStore.fields || [])

// Get fields with valid coordinates
const fieldsWithCoordinates = computed(() => {
  return fields.value.filter(field => {
    const hasLocation = field.location && field.location.lat && field.location.lon
    const hasCoordinates = field.field_coordinates && field.field_coordinates.lat && field.field_coordinates.lon
    return hasLocation || hasCoordinates
  })
})

// Suppress iframe security errors (harmless browser extension conflicts)
const handleIframeLoad = () => {
  // Note: The SecurityError about '_falkon_external' is a harmless browser extension issue
  // It occurs when browser extensions (like Falkon) try to access iframe content
  // This doesn't affect the Windy.com iframe functionality - the map still works correctly
  // The error is logged by the browser's security system but can be safely ignored
}

const handleIframeError = () => {
  // These errors are harmless - they occur when browser extensions try to access iframe
  // They don't affect functionality, so we can safely ignore them
  if (window.console && console.warn) {
    // Optionally log a user-friendly message instead of the error
  }
}

// Suppress cross-origin frame access errors globally (they're harmless)
if (typeof window !== 'undefined') {
  window.addEventListener('error', (event) => {
    // Suppress SecurityError related to iframe cross-origin access
    if (event.message && event.message.includes('Blocked a frame with origin')) {
      event.preventDefault()
      return false
    }
  }, true)
}

// Fallback weather data (for when store doesn't have data)
const fallbackWeather = ref({
  temperature: 72,
  humidity: 65,
  rainfall: 0.2,
  wind_speed: 5,
  condition: 'Partly Cloudy',
  icon: '⛅'
})

// Current weather from store or computed from primary field
const currentWeather = computed(() => {
  if (weatherStore.currentWeather) {
    return {
      temperature: weatherStore.currentWeather.temperature || 22,
      humidity: weatherStore.currentWeather.humidity || 65,
      rainfall: weatherStore.currentWeather.rainfall || weatherStore.currentWeather.precipitation || 0.2,
      wind_speed: weatherStore.currentWeather.wind_speed || 10,
      condition: weatherStore.currentWeather.condition || 'Partly Cloudy',
      icon: getWeatherIcon(weatherStore.currentWeather.weather_code)
    }
  }
  
  // Fallback to first field's weather or fallback data
  const firstFieldWeather = fieldWeather.value[0]
  if (firstFieldWeather) {
    return {
      temperature: firstFieldWeather.temperature,
      humidity: firstFieldWeather.humidity,
      rainfall: firstFieldWeather.rainfall,
      wind_speed: 5,
      condition: 'Partly Cloudy',
      icon: '⛅'
    }
  }
  
  // Use fallback weather
  return fallbackWeather.value
})

// Forecast from weather store
const forecast = computed(() => {
  if (weatherStore.forecast && weatherStore.forecast.length > 0) {
    return weatherStore.forecast.map(day => ({
      date: day.date || day.time,
      condition: day.condition || day.weather || 'Clear',
      description: day.description || day.weather_description || 'Clear skies',
      high: day.high || day.temperature_max || day.max_temp || 75,
      low: day.low || day.temperature_min || day.min_temp || 55,
      rain_chance: day.rain_chance || day.precipitation_probability || day.precipitation_chance || 0,
      wind_speed: day.wind_speed || day.wind || 5,
      icon: getWeatherIcon(day.weather_code || day.code)
    }))
  }
  
  // Return empty array if no forecast data
  return []
})

// Weather alerts from store
const weatherAlerts = computed(() => {
  if (weatherStore.alerts && weatherStore.alerts.length > 0) {
    return weatherStore.alerts.map((alert, index) => ({
      id: alert.id || index + 1,
      title: alert.title || alert.type || 'Weather Alert',
      description: alert.description || alert.message || '',
      severity: alert.severity || alert.level || 'info',
      issued_at: alert.issued_at || alert.created_at || new Date().toISOString()
    }))
  }
  
  return []
})

// Field weather computed from actual fields and weather data
const fieldWeather = computed(() => {
  return fieldsWithCoordinates.value.map(field => {
    const weatherData = fieldWeatherData.value[field.id] || {}
    const location = field.location || field.field_coordinates || {}
    
    // Handle different data formats from API
    // Weather data might have temperature in Celsius or Fahrenheit
    let temp = weatherData.temperature || weatherData.temp || 22
    // If temperature seems like Fahrenheit (> 100), convert to Celsius
    if (temp > 100) {
      temp = (temp - 32) * 5/9
    }
    
    // Handle rainfall/precipitation - could be in different units
    let rainfall = weatherData.rainfall || weatherData.precipitation || weatherData.rain?.intensity || 0
    // If rainfall seems like inches (> 1), convert to mm
    if (rainfall > 1 && rainfall < 10) {
      rainfall = rainfall * 25.4 // Convert inches to mm
    }
    
    return {
      id: field.id,
      name: field.name,
      temperature: temp,
      humidity: weatherData.humidity || 65,
      rainfall: rainfall,
      location: location,
      field_coordinates: field.field_coordinates
    }
  })
})

// Growing Degree Days calculation (base temperature 10°C for rice)
const GDD_BASE_TEMP = 10

// Calculate GDD for a single day: ((high + low) / 2) - base, minimum 0
const calculateDailyGDD = (high, low) => {
  if (high === null || low === null || isNaN(high) || isNaN(low)) return 0
  const avgTemp = (high + low) / 2
  return Math.max(0, avgTemp - GDD_BASE_TEMP)
}

// Growing Degree Days computed from forecast data
const gdd = computed(() => {
  const forecastData = forecast.value || []
  
  // Calculate today's GDD
  const todayGDD = forecastData.length > 0 
    ? Math.round(calculateDailyGDD(forecastData[0].high, forecastData[0].low))
    : 0
  
  // Calculate this week's GDD (sum of available forecast days, up to 7)
  const weekDays = forecastData.slice(0, 7)
  const weekGDD = Math.round(weekDays.reduce((sum, day) => 
    sum + calculateDailyGDD(day.high, day.low), 0))
  
  // Estimate month GDD (weekly average * 4.3 weeks)
  const avgWeeklyGDD = weekGDD / Math.max(1, weekDays.length) * 7
  const monthGDD = Math.round(avgWeeklyGDD * 4.3)
  
  // Estimate season GDD (roughly 4 months for rice growing season)
  const seasonGDD = Math.round(monthGDD * 4)
  
  return {
    today: todayGDD,
    week: weekGDD,
    month: monthGDD,
    season: seasonGDD
  }
})

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
    // Fetch fields if not loaded
    if (fields.value.length === 0) {
      await farmStore.fetchFields()
    }
    
    // Fetch weather for all fields with coordinates
    const weatherPromises = fieldsWithCoordinates.value.map(async (field) => {
      try {
        const weather = await weatherStore.fetchCurrentWeather(field.id)
        if (weather && weather.weather) {
          fieldWeatherData.value[field.id] = weather.weather
        }
      } catch (error) {
        console.warn(`Failed to fetch weather for field ${field.id}:`, error)
      }
    })
    
    await Promise.all(weatherPromises)
    
    // Fetch forecast for primary field (7 days)
    if (fieldsWithCoordinates.value.length > 0) {
      const primaryField = fieldsWithCoordinates.value[0]
      try {
        await weatherStore.fetchForecast(primaryField.id, 7)
        console.log('Forecast loaded:', weatherStore.forecast?.length || 0, 'days')
      } catch (error) {
        console.warn('Failed to fetch forecast:', error)
      }
    }
    
    // Refresh map markers
    if (map.value) {
      await updateFieldMarkers()
    }
    
    // Update current weather display
    await fetchCurrentWeather()
  } catch (error) {
    console.error('Error refreshing weather:', error)
  } finally {
    loading.value = false
  }
}

const viewForecast = () => {
  const defaultField = fieldsWithCoordinates.value?.[0]?.id
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
  // Navigate to historical data page (Analytics)
  router.push('/weather/analytics')
}

const exportWeatherData = () => {
  // Convert field weather data to CSV
  if (!fieldWeather.value || fieldWeather.value.length === 0) {
    alert('No weather data available to export.')
    return
  }

  const headers = ['Field Name', 'Temperature (°C)', 'Humidity (%)', 'Rainfall (mm)', 'Location']
  const rows = fieldWeather.value.map(field => [
    `"${field.name || 'Unknown Field'}"`,
    field.temperature || '',
    field.humidity || '',
    field.rainfall || '',
    `"${field.location?.lat || ''}, ${field.location?.lon || ''}"`
  ])

  const csvContent = [
    headers.join(','),
    ...rows.map(row => row.join(','))
  ].join('\n')

  // Create download link
  const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' })
  const url = URL.createObjectURL(blob)
  const link = document.createElement('a')
  link.setAttribute('href', url)
  link.setAttribute('download', `weather_data_${new Date().toISOString().split('T')[0]}.csv`)
  link.style.visibility = 'hidden'
  document.body.appendChild(link)
  link.click()
  document.body.removeChild(link)
}

const viewWeatherReports = () => {
  router.push('/reports/weather')
}

// Weather map functions
const initMap = () => {
  if (!mapContainer.value) return

  // Default center (Philippines - common location for farms)
  const defaultLat = 14.5995
  const defaultLon = 120.9842

  // Load Leaflet dynamically
  if (typeof L === 'undefined') {
    // Load Leaflet CSS
    const link = document.createElement('link')
    link.rel = 'stylesheet'
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
    link.integrity = 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY='
    link.crossOrigin = ''
    document.head.appendChild(link)

    // Load Leaflet JS
    const script = document.createElement('script')
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
    script.integrity = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo='
    script.crossOrigin = ''
    script.onload = () => {
      createMap()
    }
    document.head.appendChild(script)
  } else {
    createMap()
  }

  function createMap() {
    if (!mapContainer.value) return
    
    // Check if map already exists and remove it
    if (map.value) {
      map.value.remove()
      map.value = null
    }
    
    // Small delay to ensure container is ready
    setTimeout(() => {
      if (!mapContainer.value) return
      
      // Initialize map
      map.value = L.map(mapContainer.value, {
        zoomControl: true,
        attributionControl: true
      }).setView([defaultLat, defaultLon], 6)

      // Add OpenStreetMap tiles as base layer
      const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors | Weather data © Open-Meteo',
        maxZoom: 19,
      }).addTo(map.value)

      // Store base layer
      map.value.osmLayer = osmLayer
      
      // Store overlay groups (we'll use markers/circles instead of tile layers)
      map.value.weatherOverlays = {
        temperature: null,
        precipitation: null,
        clouds: null,
        pressure: null,
        wind: null,
      }

      // Add field markers (these show weather data)
    updateFieldMarkers()
    
    // Add weather station markers
    updateStationMarkers()
    
    // Set initial layer visualization
    updateWeatherLayer()
    }, 100)
  }
  
  // Update weather layer visualization based on selection
  const updateWeatherLayer = () => {
    if (!map.value || !L) return
    
    // Remove existing weather overlay circles
    if (map.value.weatherOverlayGroup) {
      map.value.removeLayer(map.value.weatherOverlayGroup)
      map.value.weatherOverlayGroup = null
    }
    
    // Create new overlay group
    const overlayGroup = L.layerGroup()
    
    // Update field markers to show selected weather data
    if (selectedWeatherLayer.value !== 'base' && fieldWeather.value.length > 0) {
      const defaultCoords = [
        { lat: 14.6095, lon: 120.9842 },
        { lat: 14.5895, lon: 120.9842 },
        { lat: 14.5995, lon: 120.9942 },
      ]
      
      fieldWeather.value.forEach(async (field, index) => {
        let lat, lon
        
        if (field.location && field.location.lat && field.location.lon) {
          lat = field.location.lat
          lon = field.location.lon
        } else if (field.field_coordinates && field.field_coordinates.lat && field.field_coordinates.lon) {
          lat = field.field_coordinates.lat
          lon = field.field_coordinates.lon
        } else if (defaultCoords[index]) {
          lat = defaultCoords[index].lat
          lon = defaultCoords[index].lon
        } else {
          return
        }
        
        if (!lat || !lon) return
        
        // Fetch weather data
        const weatherData = await fetchOpenMeteoWeather(lat, lon)
        if (!weatherData) return
        
        let value, color, radius, label
        
        switch (selectedWeatherLayer.value) {
          case 'temperature':
            value = weatherData.temperature || field.temperature || 22
            // Color gradient: blue (cold) -> green (moderate) -> red (hot) - Celsius
            if (value < 10) color = '#3B82F6'
            else if (value < 20) color = '#10B981'
            else if (value < 30) color = '#F59E0B'
            else color = '#EF4444'
            radius = Math.max(20, Math.min(60, (value - 5) * 2))
            label = `${Math.round(value)}°C`
            break
          case 'precipitation':
            value = weatherData.precipitation || 0
            color = value > 0 ? '#3B82F6' : '#E5E7EB'
            radius = Math.max(15, Math.min(50, value * 20))
            label = value > 0 ? `${value.toFixed(2)} mm/h` : 'No rain'
            break
          case 'clouds':
            value = weatherData.cloudrate || 0
            color = `rgba(148, 163, 184, ${value / 100})` // Gray with opacity based on cloud cover
            radius = 30 + (value / 100) * 20
            label = `${value}%`
            break
          case 'pressure':
            value = weatherData.pressure || 1013
            // Pressure gradient: low (red) -> normal (green) -> high (blue)
            if (value < 1000) color = '#EF4444'
            else if (value < 1020) color = '#10B981'
            else color = '#3B82F6'
            radius = 25
            label = `${Math.round(value)} hPa`
            break
          case 'wind':
            value = weatherData.wind_speed || 0
            // Wind speed gradient: calm (green) -> moderate (yellow) -> strong (red) - km/h
            if (value < 15) color = '#10B981'
            else if (value < 30) color = '#F59E0B'
            else color = '#EF4444'
            radius = Math.max(15, Math.min(40, value * 0.8))
            label = `${Math.round(value)} km/h`
            break
          default:
            return
        }
        
        // Create circle overlay
        const circle = L.circle([lat, lon], {
          color: color,
          fillColor: color,
          fillOpacity: 0.4,
          radius: radius * 1000, // Convert to meters
          weight: 2
        }).bindPopup(`
          <div style="min-width: 150px;">
            <strong>${field.name}</strong><br/>
            ${selectedWeatherLayer.value.charAt(0).toUpperCase() + selectedWeatherLayer.value.slice(1)}: ${label}
          </div>
        `)
        
        overlayGroup.addLayer(circle)
      })
    }
    
    // Add overlay group to map
    if (overlayGroup.getLayers().length > 0) {
      overlayGroup.addTo(map.value)
      map.value.weatherOverlayGroup = overlayGroup
      currentWeatherLayer.value = overlayGroup
    } else {
      currentWeatherLayer.value = null
    }
  }
}

// Fetch weather data from Open-Meteo API
const fetchOpenMeteoWeather = async (lat, lon) => {
  try {
    // Open-Meteo API - no API key required, good CORS support
    const url = `https://api.open-meteo.com/v1/forecast?latitude=${lat}&longitude=${lon}&current=temperature_2m,relative_humidity_2m,wind_speed_10m,wind_direction_10m,precipitation,weather_code,cloud_cover,pressure_msl&temperature_unit=celsius&wind_speed_unit=kmh&precipitation_unit=mm`
    
    const response = await fetch(url)
    
    if (!response.ok) {
      throw new Error(`HTTP error! status: ${response.status}`)
    }
    
    const data = await response.json()
    
    if (data.current) {
      const current = data.current
      return {
        temperature: Math.round(current.temperature_2m || 0),
        humidity: Math.round(current.relative_humidity_2m || 0),
        wind_speed: Math.round(current.wind_speed_10m || 0),
        wind_direction: Math.round(current.wind_direction_10m || 0),
        pressure: Math.round((current.pressure_msl || 0) / 100), // Convert Pa to hPa
        conditions: getWeatherCodeDescription(current.weather_code),
        description: getWeatherCodeDescription(current.weather_code),
        precipitation: current.precipitation || 0,
        cloudrate: Math.round(current.cloud_cover || 0),
        weather_code: current.weather_code || 0,
      }
    }
    
    return null
  } catch (error) {
    console.error('Error fetching Open-Meteo weather:', error)
    return null
  }
}

// Get weather description from WMO weather code
const getWeatherCodeDescription = (code) => {
  // WMO Weather interpretation codes (WW)
  const descriptions = {
    0: 'Clear sky',
    1: 'Mainly clear',
    2: 'Partly cloudy',
    3: 'Overcast',
    45: 'Foggy',
    48: 'Depositing rime fog',
    51: 'Light drizzle',
    53: 'Moderate drizzle',
    55: 'Dense drizzle',
    56: 'Light freezing drizzle',
    57: 'Dense freezing drizzle',
    61: 'Slight rain',
    63: 'Moderate rain',
    65: 'Heavy rain',
    66: 'Light freezing rain',
    67: 'Heavy freezing rain',
    71: 'Slight snow fall',
    73: 'Moderate snow fall',
    75: 'Heavy snow fall',
    77: 'Snow grains',
    80: 'Slight rain showers',
    81: 'Moderate rain showers',
    82: 'Violent rain showers',
    85: 'Slight snow showers',
    86: 'Heavy snow showers',
    95: 'Thunderstorm',
    96: 'Thunderstorm with slight hail',
    99: 'Thunderstorm with heavy hail',
  }
  return descriptions[code] || 'Unknown'
}

const updateFieldMarkers = async () => {
  if (!map.value || !L) return

  // Clear existing field markers
  markers.value.forEach(marker => {
    if (marker.isField) {
      map.value.removeLayer(marker)
    }
  })
  markers.value = markers.value.filter(m => !m.isField)

  // Use actual fields from store
  const fieldsToProcess = fieldsWithCoordinates.value

  // Fetch weather data for all fields in parallel
  const weatherPromises = fieldsToProcess.map(async (field) => {
    // Get coordinates from field data
    let lat, lon
    
    if (field.location && field.location.lat && field.location.lon) {
      lat = field.location.lat
      lon = field.location.lon
    } else if (field.field_coordinates && field.field_coordinates.lat && field.field_coordinates.lon) {
      lat = field.field_coordinates.lat
      lon = field.field_coordinates.lon
    } else {
      return null
    }

    if (!lat || !lon) return null

    // Try to get weather from store first, then fetch from API
    let weatherData = fieldWeatherData.value[field.id]
    
    if (!weatherData) {
      // Try to fetch from weather store API first
      try {
        const apiWeather = await weatherStore.fetchCurrentWeather(field.id)
        if (apiWeather && apiWeather.weather) {
          weatherData = apiWeather.weather
          fieldWeatherData.value[field.id] = weatherData
        }
      } catch (error) {
        console.warn(`Failed to fetch weather from API for field ${field.id}, using Open-Meteo fallback`)
      }
      
      // Fallback to Open-Meteo if API doesn't work
      if (!weatherData) {
        weatherData = await fetchOpenMeteoWeather(lat, lon)
        if (weatherData) {
          fieldWeatherData.value[field.id] = weatherData
        }
      }
    }
    
    return {
      field,
      lat,
      lon,
      weatherData: weatherData || {
        temperature: 70,
        humidity: 65,
        wind_speed: 5,
        description: 'Weather data unavailable',
        precipitation: 0,
      }
    }
  })

  const fieldDataWithWeather = await Promise.all(weatherPromises)

  // Add markers for each field with real weather data
  fieldDataWithWeather.forEach(({ field, lat, lon, weatherData }) => {
    if (!field || !lat || !lon || !map.value || !L) return

    // Use real weather data or fallback to field data (Celsius)
    const temp = weatherData?.temperature || field.temperature || 22
    const humidity = weatherData?.humidity || field.humidity || 65
    const rainfall = weatherData?.precipitation || field.rainfall || 0
    // Wind speed is in km/h from API, ensure it's displayed correctly
    const windSpeed = weatherData?.wind_speed || 0
    const description = weatherData?.description || 'Weather data unavailable'
    
    // Create custom icon based on temperature (Celsius thresholds)
    const iconColor = temp < 15 ? '#3B82F6' : temp > 30 ? '#EF4444' : '#10B981'
    
    const customIcon = L.divIcon({
      className: 'weather-marker',
      html: `
        <div style="
          background: ${iconColor};
          width: 40px;
          height: 40px;
          border-radius: 50%;
          border: 3px solid white;
          display: flex;
          align-items: center;
          justify-content: center;
          color: white;
          font-weight: bold;
          font-size: 12px;
          box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        ">
          ${Math.round(temp)}°C
        </div>
      `,
      iconSize: [40, 40],
      iconAnchor: [20, 20],
    })

    const marker = L.marker([lat, lon], { icon: customIcon })
      .addTo(map.value)
      .bindPopup(`
        <div style="min-width: 200px;">
          <h3 style="margin: 0 0 8px 0; font-weight: bold;">${field.name}</h3>
          <div style="font-size: 14px; margin-bottom: 8px;">
            <div><strong>Temperature:</strong> ${Math.round(temp)}°C</div>
            <div><strong>Humidity:</strong> ${humidity}%</div>
            <div><strong>Wind Speed:</strong> ${Math.round(windSpeed)} km/h</div>
            <div><strong>Conditions:</strong> ${description}</div>
            ${rainfall > 0 ? `<div><strong>Precipitation:</strong> ${rainfall.toFixed(2)} mm/h</div>` : ''}
          </div>
          <button 
            onclick="window.location.href='/weather/fields/${field.id}'"
            style="margin-top: 8px; padding: 4px 12px; background: #3B82F6; color: white; border: none; border-radius: 4px; cursor: pointer; font-size: 12px;"
          >
            View Details
          </button>
        </div>
      `)

    marker.isField = true
    markers.value.push(marker)
    
    // Store weather data for this field
    fieldWeatherData.value[field.id] = weatherData
  })

  // Fit map to show all markers
  if (markers.value.length > 0) {
    const group = new L.featureGroup(markers.value.filter(m => m.isField))
    map.value.fitBounds(group.getBounds().pad(0.1))
  }
}

const updateStationMarkers = () => {
  if (!map.value || !L) return

  // Clear existing station markers
  markers.value.forEach(marker => {
    if (marker.isStation) {
      map.value.removeLayer(marker)
    }
  })
  markers.value = markers.value.filter(m => !m.isStation)

  // Add markers for weather stations
  // Using sample coordinates - in production, these would come from weatherStations data
  const stationCoordinates = [
    { id: 1, name: 'Main Station', lat: 14.5995, lon: 120.9842, status: 'online' },
    { id: 2, name: 'North Station', lat: 14.6095, lon: 120.9842, status: 'online' },
    { id: 3, name: 'South Station', lat: 14.5895, lon: 120.9842, status: 'offline' },
  ]

  stationCoordinates.forEach(station => {
    if (!station.lat || !station.lon) return

    const statusColor = station.status === 'online' ? '#10B981' : '#EF4444'
    const statusIcon = station.status === 'online' ? '📡' : '⚠️'

    const customIcon = L.divIcon({
      className: 'station-marker',
      html: `
        <div style="
          background: ${statusColor};
          width: 36px;
          height: 36px;
          border-radius: 50%;
          border: 3px solid white;
          display: flex;
          align-items: center;
          justify-content: center;
          font-size: 18px;
          box-shadow: 0 2px 4px rgba(0,0,0,0.3);
        ">
          ${statusIcon}
        </div>
      `,
      iconSize: [36, 36],
      iconAnchor: [18, 18],
    })

    const marker = L.marker([station.lat, station.lon], { icon: customIcon })
      .addTo(map.value)
      .bindPopup(`
        <div style="min-width: 150px;">
          <h3 style="margin: 0 0 8px 0; font-weight: bold;">${station.name}</h3>
          <div style="font-size: 14px;">
            <div><strong>Status:</strong> 
              <span style="color: ${statusColor};">${station.status}</span>
            </div>
          </div>
        </div>
      `)

    marker.isStation = true
    markers.value.push(marker)
  })
}

// Removed toggleWeatherLayer - ColorfulClouds provides data via API, not map tiles

// Watch for fieldWeather changes to update markers
watch(fieldWeather, () => {
  if (map.value) {
    updateFieldMarkers()
  }
}, { deep: true })

// Fetch current weather for dashboard overview
const fetchCurrentWeather = async () => {
  try {
    // Try to fetch from weather store for primary field first
    if (fieldsWithCoordinates.value.length > 0) {
      const primaryField = fieldsWithCoordinates.value[0]
      try {
        await weatherStore.fetchCurrentWeather(primaryField.id)
        // Weather store will be updated, computed property will react
        return
      } catch (error) {
        console.warn('Failed to fetch weather from store, using Open-Meteo fallback')
      }
    }
    
    // Fallback: Use default coordinates (Philippines) for main weather display
    const defaultLat = 14.5995
    const defaultLon = 120.9842
    
    const weatherData = await fetchOpenMeteoWeather(defaultLat, defaultLon)
    
    if (weatherData) {
      // Update fallback weather instead of computed property
      fallbackWeather.value = {
        temperature: weatherData.temperature,
        humidity: weatherData.humidity,
        rainfall: Number(weatherData.precipitation || 0).toFixed(2),
        wind_speed: weatherData.wind_speed,
        condition: weatherData.description,
        icon: getWeatherIcon(weatherData.weather_code)
      }
    }
  } catch (error) {
    console.error('Error fetching current weather:', error)
  }
}

// Get weather icon based on weather code
const getWeatherIcon = (code) => {
  // Map WMO weather codes to emojis
  if (code === 0 || code === 1) return '☀️' // Clear/Mainly clear
  if (code === 2 || code === 3) return '☁️' // Partly cloudy/Overcast
  if (code === 45 || code === 48) return '🌫️' // Fog
  if (code >= 51 && code <= 57) return '🌦️' // Drizzle
  if (code >= 61 && code <= 67) return '🌧️' // Rain
  if (code >= 71 && code <= 77) return '❄️' // Snow
  if (code >= 80 && code <= 82) return '🌧️' // Rain showers
  if (code >= 85 && code <= 86) return '🌨️' // Snow showers
  if (code >= 95 && code <= 99) return '⛈️' // Thunderstorm
  return '🌤️' // Default
}

onMounted(async () => {
  // Fetch fields first
  if (fields.value.length === 0) {
    await farmStore.fetchFields()
  }
  
  // Fetch weather data for fields
  await refreshWeather()
  
  // Initialize map only if not using Windy.com embed
  if (selectedWeatherLayer.value !== 'windy') {
    setTimeout(() => {
      initMap()
    }, 100)
  }
})

// Watch for layer changes and initialize/destroy map accordingly
watch(selectedWeatherLayer, (newValue) => {
  if (newValue === 'windy') {
    // Destroy Leaflet map if switching to Windy
    if (map.value) {
      map.value.remove()
      map.value = null
    }
  } else {
    // Initialize Leaflet map if switching from Windy
    if (!map.value && mapContainer.value) {
      setTimeout(() => {
        initMap()
      }, 200)
    } else if (map.value) {
      // Update layer visualization if map already exists
      updateWeatherLayer()
    }
  }
})

onBeforeUnmount(() => {
  if (map.value) {
    map.value.remove()
  }
})
</script>

<style scoped>
.weather-dashboard-page {
  min-height: 100vh;
  background-color: #f8fafc;
}

/* Leaflet map container styles */
:deep(.leaflet-container) {
  border-radius: 0.5rem;
  z-index: 0;
}

:deep(.leaflet-popup-content-wrapper) {
  border-radius: 0.5rem;
}

:deep(.weather-marker),
:deep(.station-marker) {
  background: transparent !important;
  border: none !important;
}
</style>