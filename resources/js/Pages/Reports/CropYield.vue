<template>
  <div class="crop-yield-reports-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Crop Yield Reports</h1>
          <p class="text-gray-600 mt-2">Analyze your crop production and yield data</p>
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
            <label class="block text-sm font-medium text-gray-700 mb-2">Season</label>
            <select
              v-model="selectedSeason"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="2024">2024 Season</option>
              <option value="2023">2023 Season</option>
              <option value="2022">2022 Season</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Crop Type</label>
            <select
              v-model="selectedCrop"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Crops</option>
              <option value="corn">Corn</option>
              <option value="wheat">Wheat</option>
              <option value="soybeans">Soybeans</option>
              <option value="rice">Rice</option>
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

      <!-- Yield Summary -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <span class="text-green-600 text-2xl">🌾</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ yieldSummary.totalYield.toLocaleString() }}</div>
              <div class="text-sm text-gray-600">Total Yield (kg)</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-blue-600 text-2xl">📊</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ yieldSummary.avgYieldPerAcre.toFixed(1) }}</div>
              <div class="text-sm text-gray-600">Avg Yield/Hectare (kg)</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                <span class="text-yellow-600 text-2xl">📈</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ yieldSummary.yieldIncrease }}%</div>
              <div class="text-sm text-gray-600">vs Last Year</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                <span class="text-purple-600 text-2xl">🏆</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ yieldSummary.bestField }}</div>
              <div class="text-sm text-gray-600">Best Performing Field</div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Yield Trends Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold mb-4">Yield Trends (Last 5 Years)</h2>
          <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
            <LineChart v-if="yieldChartData.labels.length > 0" :data="yieldChartData" />
            <div v-else class="h-full flex items-center justify-center text-gray-500">
              No yield data available
            </div>
          </div>
        </div>

        <!-- Field Performance -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold mb-4">Field Performance</h2>
          <div class="space-y-4">
            <div
              v-for="field in fieldPerformance"
              :key="field.name"
              class="p-4 border border-gray-200 rounded-lg"
            >
              <div class="flex justify-between items-start mb-2">
                <h3 class="font-medium text-gray-900">{{ field.name }}</h3>
                <span class="text-sm font-medium text-green-600">{{ field.yieldPerAcre.toFixed(1) }} kg/ha</span>
              </div>
              <div class="flex justify-between text-sm text-gray-600">
                <span>Total Yield: {{ field.totalYield.toLocaleString() }} kg</span>
                <span>Area: {{ field.area }} ha</span>
              </div>
              <div class="mt-2">
                <div class="w-full bg-gray-200 rounded-full h-2">
                  <div
                    class="bg-green-600 h-2 rounded-full"
                    :style="{ width: `${(field.yieldPerAcre / 200) * 100}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Crop Comparison -->
      <div class="mt-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold mb-4">Crop Comparison</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Crop</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Area (ha)</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Yield (kg)</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Yield/Hectare</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Market Price</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Value</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="crop in cropComparison" :key="crop.name">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    {{ crop.name }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ crop.area }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ crop.totalYield.toLocaleString() }} kg
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ crop.yieldPerAcre.toFixed(1) }} kg/ha
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatCurrency(crop.marketPrice) }}/kg
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                    {{ formatCurrency(crop.totalValue) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Yield Factors -->
      <div class="mt-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold mb-4">Yield Factors Analysis</h2>
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
              <h3 class="font-medium text-gray-900 mb-3">Weather Impact</h3>
              <div class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Temperature</span>
                  <span class="font-medium">Optimal</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Rainfall</span>
                  <span class="font-medium">Adequate</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Growing Days</span>
                  <span class="font-medium">125 days</span>
                </div>
              </div>
            </div>
            <div>
              <h3 class="font-medium text-gray-900 mb-3">Soil Conditions</h3>
              <div class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">pH Level</span>
                  <span class="font-medium">6.8</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Organic Matter</span>
                  <span class="font-medium">3.2%</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Drainage</span>
                  <span class="font-medium">Good</span>
                </div>
              </div>
            </div>
            <div>
              <h3 class="font-medium text-gray-900 mb-3">Management</h3>
              <div class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Fertilizer Applied</span>
                  <span class="font-medium">Yes</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Pest Control</span>
                  <span class="font-medium">Effective</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Irrigation</span>
                  <span class="font-medium">As needed</span>
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
import { formatCurrency } from '@/utils/format'
import { reportsAPI } from '@/services/api'
import LineChart from '@/Components/Charts/LineChart.vue'

const selectedSeason = ref('2024')
const selectedCrop = ref('')
const selectedField = ref('')

const yieldSummary = ref({
  totalYield: 0,
  avgYieldPerAcre: 0,
  yieldIncrease: 0,
  bestField: 'N/A'
})

const fieldPerformance = ref([])

const cropComparison = ref([])

const updateReport = async () => {
  // Reload report data with current filters
  try {
    await loadYieldData()
    alert('Report updated successfully')
  } catch (error) {
    console.error('Failed to update report:', error)
    alert('Failed to update report')
  }
}

const generateReport = async () => {
  // Generate new report with current filters
  try {
    await loadYieldData()
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
    link.setAttribute('download', `crop-yield-report-${new Date().toISOString().split('T')[0]}.csv`)
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
  // Generate CSV content from yield data
  const headers = ['Crop', 'Area (hectares)', 'Total Yield (kg)', 'Yield per Hectare', 'Market Price', 'Total Value']
  const rows = cropComparison.value.map(item => [
    item.name || 'N/A',
    item.area || 0,
    item.totalYield || 0,
    item.yieldPerAcre || 0,
    item.marketPrice || 0,
    item.totalValue || 0,
  ])
  
  return [headers, ...rows].map(row => row.join(',')).join('\n')
}

const yieldTrends = ref([])

const yieldChartData = computed(() => {
  if (!yieldTrends.value || yieldTrends.value.length === 0) {
    return {
      labels: [],
      datasets: []
    }
  }
  
  return {
    labels: yieldTrends.value.map(item => item.month),
    datasets: [{
      label: 'Yield (kg)',
      data: yieldTrends.value.map(item => item.yield),
      borderColor: 'rgb(34, 197, 94)',
      backgroundColor: 'rgba(34, 197, 94, 0.1)',
      fill: true,
      tension: 0.4,
    }]
  }
})

onMounted(() => {
  // Load crop yield data from API
  loadYieldData()
})

const loadYieldData = async () => {
  try {
    const period = selectedSeason.value === '2024' ? 365 : selectedSeason.value === '2023' ? 730 : 1095
    const response = await reportsAPI.getCropYieldReport(period)
    const data = response.data.data || response.data
    
    if (data.yield_summary) {
      yieldSummary.value = {
        totalYield: data.yield_summary.total_yield || 0,
        avgYieldPerAcre: data.yield_summary.avg_yield_per_hectare || 0,
        yieldIncrease: data.yield_summary.yield_increase || 0,
        bestField: data.yield_summary.best_field || 'N/A'
      }
    }
    
    if (data.field_performance) {
      fieldPerformance.value = data.field_performance.map(field => ({
        name: field.field_name,
        totalYield: field.total_yield,
        area: field.area,
        yieldPerAcre: field.yield_per_hectare
      }))
    }
    
    if (data.crop_comparison) {
      cropComparison.value = data.crop_comparison.map(crop => ({
        name: crop.name,
        area: crop.area,
        totalYield: crop.total_yield,
        yieldPerAcre: crop.yield_per_hectare,
        marketPrice: crop.market_price,
        totalValue: crop.total_value
      }))
    }
    
    if (data.yield_trends) {
      yieldTrends.value = data.yield_trends
    }
  } catch (error) {
    console.error('Error loading yield data:', error)
    alert('Failed to load yield data')
  }
}
</script>

<style scoped>
.crop-yield-reports-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>