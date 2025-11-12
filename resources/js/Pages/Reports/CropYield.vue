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
              <div class="text-sm text-gray-600">Total Yield (bushels)</div>
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
              <div class="text-2xl font-bold text-gray-900">{{ yieldSummary.avgYieldPerAcre }}</div>
              <div class="text-sm text-gray-600">Avg Yield/Acre</div>
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
            <span class="text-gray-500">Yield trends chart placeholder</span>
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
                <span class="text-sm font-medium text-green-600">{{ field.yieldPerAcre }} bu/acre</span>
              </div>
              <div class="flex justify-between text-sm text-gray-600">
                <span>Total Yield: {{ field.totalYield.toLocaleString() }} bushels</span>
                <span>Area: {{ field.area }} acres</span>
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
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Area (acres)</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Yield</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Yield/Acre</th>
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
                    {{ crop.totalYield.toLocaleString() }} bushels
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ crop.yieldPerAcre }} bu/acre
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatCurrency(crop.marketPrice) }}/bushel
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
import { ref, onMounted } from 'vue'
import { formatCurrency } from '@/utils/format'

const selectedSeason = ref('2024')
const selectedCrop = ref('')
const selectedField = ref('')

const yieldSummary = ref({
  totalYield: 12500,
  avgYieldPerAcre: 165,
  yieldIncrease: 12,
  bestField: 'North Field'
})

const fieldPerformance = ref([
  {
    name: 'North Field',
    totalYield: 4500,
    area: 25.5,
    yieldPerAcre: 176
  },
  {
    name: 'South Field',
    totalYield: 3800,
    area: 22.0,
    yieldPerAcre: 173
  },
  {
    name: 'East Field',
    totalYield: 4200,
    area: 28.0,
    yieldPerAcre: 150
  }
])

const cropComparison = ref([
  {
    name: 'Corn',
    area: 45.5,
    totalYield: 8300,
    yieldPerAcre: 182,
    marketPrice: 5.25,
    totalValue: 43575
  },
  {
    name: 'Wheat',
    area: 22.0,
    totalYield: 2200,
    yieldPerAcre: 100,
    marketPrice: 6.80,
    totalValue: 14960
  },
  {
    name: 'Soybeans',
    area: 8.0,
    totalYield: 2000,
    yieldPerAcre: 250,
    marketPrice: 12.50,
    totalValue: 25000
  }
])

const updateReport = () => {
  // Update report based on selected filters
  console.log('Update report:', { 
    season: selectedSeason.value, 
    crop: selectedCrop.value, 
    field: selectedField.value 
  })
}

const generateReport = () => {
  // Generate new report
  console.log('Generate report')
}

const exportReport = () => {
  // Export report logic
  console.log('Export report')
}

onMounted(() => {
  // Load crop yield data from API
})
</script>

<style scoped>
.crop-yield-reports-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>