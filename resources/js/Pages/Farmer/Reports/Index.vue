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
              <h1 class="text-xl font-semibold text-gray-900">Farm Reports & Analytics</h1>
              <p class="text-sm text-gray-500">Analyze your rice farming performance and financial data</p>
            </div>
          </div>
          
          <div class="flex space-x-3">
            <select 
              v-model="selectedPeriod"
              class="px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
            >
              <option value="30">Last 30 Days</option>
              <option value="90">Last 3 Months</option>
              <option value="365">Last Year</option>
              <option value="all">All Time</option>
            </select>
            <button 
              @click="exportReport"
              class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors flex items-center"
            >
              <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
              </svg>
              Export Report
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Report Tabs -->
      <div class="mb-8">
        <nav class="flex space-x-8">
          <button 
            v-for="tab in tabs" 
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'py-2 px-1 border-b-2 font-medium text-sm',
              activeTab === tab.id 
                ? 'border-green-500 text-green-600' 
                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>
      </div>

      <!-- Yield Report -->
      <div v-if="activeTab === 'yield'" class="space-y-8">
        <!-- Yield Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                  <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Yield</p>
                <p class="text-2xl font-semibold text-gray-900">{{ totalYield }} kg</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                  <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Average Yield/ha</p>
                <p class="text-2xl font-semibold text-gray-900">{{ averageYieldPerHectare }} kg/ha</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="h-8 w-8 bg-yellow-100 rounded-full flex items-center justify-center">
                  <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Best Variety</p>
                <p class="text-2xl font-semibold text-gray-900">{{ bestVariety }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="h-8 w-8 bg-purple-100 rounded-full flex items-center justify-center">
                  <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Harvests</p>
                <p class="text-2xl font-semibold text-gray-900">{{ totalHarvests }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Yield Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Yield Over Time</h3>
            <div class="h-64">
              <LineChart 
                v-if="yieldChartData.labels.length > 0"
                :data="yieldChartData"
                :options="chartOptions"
              />
              <div v-else class="flex items-center justify-center h-full text-gray-500">
                <p>No yield data available</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Yield by Variety</h3>
            <div class="h-64">
              <BarChart 
                v-if="varietyChartData.labels.length > 0"
                :data="varietyChartData"
                :options="chartOptions"
              />
              <div v-else class="flex items-center justify-center h-full text-gray-500">
                <p>No variety data available</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Financial Report -->
      <div v-if="activeTab === 'financial'" class="space-y-8">
        <!-- Financial Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                  <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Revenue</p>
                <p class="text-2xl font-semibold text-gray-900">${{ totalRevenue }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="h-8 w-8 bg-red-100 rounded-full flex items-center justify-center">
                  <svg class="h-5 w-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Total Expenses</p>
                <p class="text-2xl font-semibold text-gray-900">${{ totalExpenses }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                  <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Net Profit</p>
                <p class="text-2xl font-semibold text-gray-900">${{ netProfit }}</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="h-8 w-8 bg-purple-100 rounded-full flex items-center justify-center">
                  <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Profit Margin</p>
                <p class="text-2xl font-semibold text-gray-900">{{ profitMargin }}%</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Financial Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue vs Expenses</h3>
            <div class="h-64">
              <LineChart 
                v-if="financialChartData.labels.length > 0"
                :data="financialChartData"
                :options="chartOptions"
              />
              <div v-else class="flex items-center justify-center h-full text-gray-500">
                <p>No financial data available</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Expense Categories</h3>
            <div class="h-64">
              <PieChart 
                v-if="expenseChartData.labels.length > 0"
                :data="expenseChartData"
                :options="pieChartOptions"
              />
              <div v-else class="flex items-center justify-center h-full text-gray-500">
                <p>No expense data available</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Weather Correlation Report -->
      <div v-if="activeTab === 'weather'" class="space-y-8">
        <!-- Weather Impact Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center">
                  <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Avg Rainfall</p>
                <p class="text-2xl font-semibold text-gray-900">{{ averageRainfall }} mm</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="h-8 w-8 bg-orange-100 rounded-full flex items-center justify-center">
                  <svg class="h-5 w-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Avg Temperature</p>
                <p class="text-2xl font-semibold text-gray-900">{{ averageTemperature }}°C</p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center">
                  <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                  </svg>
                </div>
              </div>
              <div class="ml-4">
                <p class="text-sm font-medium text-gray-500">Weather Impact</p>
                <p class="text-2xl font-semibold text-gray-900">{{ weatherImpact }}%</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Weather Correlation Chart -->
        <div class="bg-white rounded-lg shadow p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-4">Weather vs Yield Correlation</h3>
          <div class="h-64">
            <LineChart 
              v-if="weatherCorrelationData.labels.length > 0"
              :data="weatherCorrelationData"
              :options="chartOptions"
            />
            <div v-else class="flex items-center justify-center h-full text-gray-500">
              <p>No weather correlation data available</p>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useFarmStore } from '@/stores/farm';
import { useWeatherStore } from '@/stores/weather';
import LineChart from '@/Components/Charts/LineChart.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import PieChart from '@/Components/Charts/PieChart.vue';

const farmStore = useFarmStore();
const weatherStore = useWeatherStore();

const activeTab = ref('yield');
const selectedPeriod = ref('365');

const tabs = [
  { id: 'yield', name: 'Yield Report' },
  { id: 'financial', name: 'Financial Report' },
  { id: 'weather', name: 'Weather Correlation' }
];

// Yield Report Data
const totalYield = computed(() => {
  return farmStore.harvests.reduce((total, harvest) => total + (harvest.yield || 0), 0).toFixed(0);
});

const averageYieldPerHectare = computed(() => {
  const totalYield = farmStore.harvests.reduce((total, harvest) => total + (harvest.yield || 0), 0);
  const totalArea = farmStore.fields.reduce((total, field) => total + (field.size || 0), 0);
  return totalArea > 0 ? (totalYield / totalArea).toFixed(0) : 0;
});

const bestVariety = computed(() => {
  const varietyYields = {};
  farmStore.harvests.forEach(harvest => {
    const variety = harvest.planting?.crop_type || 'Unknown';
    varietyYields[variety] = (varietyYields[variety] || 0) + (harvest.yield || 0);
  });
  
  return Object.keys(varietyYields).reduce((best, variety) => 
    varietyYields[variety] > varietyYields[best] ? variety : best, 'N/A'
  );
});

const totalHarvests = computed(() => farmStore.harvests.length);

const yieldChartData = computed(() => {
  const data = farmStore.harvests.slice(-12); // Last 12 harvests
  return {
    labels: data.map(harvest => new Date(harvest.harvest_date).toLocaleDateString()),
    datasets: [{
      label: 'Yield (kg)',
      data: data.map(harvest => harvest.yield || 0),
      borderColor: 'rgb(34, 197, 94)',
      backgroundColor: 'rgba(34, 197, 94, 0.1)',
      tension: 0.1
    }]
  };
});

const varietyChartData = computed(() => {
  const varietyYields = {};
  farmStore.harvests.forEach(harvest => {
    const variety = harvest.planting?.crop_type || 'Unknown';
    varietyYields[variety] = (varietyYields[variety] || 0) + (harvest.yield || 0);
  });
  
  return {
    labels: Object.keys(varietyYields),
    datasets: [{
      label: 'Yield (kg)',
      data: Object.values(varietyYields),
      backgroundColor: [
        'rgba(34, 197, 94, 0.5)',
        'rgba(59, 130, 246, 0.5)',
        'rgba(168, 85, 247, 0.5)',
        'rgba(245, 158, 11, 0.5)',
        'rgba(239, 68, 68, 0.5)'
      ]
    }]
  };
});

// Financial Report Data
const totalRevenue = computed(() => {
  return farmStore.sales?.reduce((total, sale) => total + (sale.total_amount || 0), 0).toFixed(2) || '0.00';
});

const totalExpenses = computed(() => {
  return farmStore.expenses?.reduce((total, expense) => total + (expense.amount || 0), 0).toFixed(2) || '0.00';
});

const netProfit = computed(() => {
  return (parseFloat(totalRevenue.value) - parseFloat(totalExpenses.value)).toFixed(2);
});

const profitMargin = computed(() => {
  const revenue = parseFloat(totalRevenue.value);
  const profit = parseFloat(netProfit.value);
  return revenue > 0 ? ((profit / revenue) * 100).toFixed(1) : '0.0';
});

const financialChartData = computed(() => {
  // Mock data - in real app, this would come from sales/expenses data
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
  return {
    labels: months,
    datasets: [
      {
        label: 'Revenue',
        data: [1200, 1500, 1800, 1600, 2000, 2200],
        borderColor: 'rgb(34, 197, 94)',
        backgroundColor: 'rgba(34, 197, 94, 0.1)',
        tension: 0.1
      },
      {
        label: 'Expenses',
        data: [800, 900, 1000, 950, 1100, 1200],
        borderColor: 'rgb(239, 68, 68)',
        backgroundColor: 'rgba(239, 68, 68, 0.1)',
        tension: 0.1
      }
    ]
  };
});

const expenseChartData = computed(() => {
  return {
    labels: ['Seeds', 'Fertilizer', 'Labor', 'Equipment', 'Other'],
    datasets: [{
      data: [25, 30, 20, 15, 10],
      backgroundColor: [
        'rgba(34, 197, 94, 0.5)',
        'rgba(59, 130, 246, 0.5)',
        'rgba(168, 85, 247, 0.5)',
        'rgba(245, 158, 11, 0.5)',
        'rgba(239, 68, 68, 0.5)'
      ]
    }]
  };
});

// Weather Correlation Data
const averageRainfall = computed(() => {
  const weatherData = weatherStore.weatherHistory;
  if (weatherData.length === 0) return '0';
  const totalRainfall = weatherData.reduce((total, record) => total + (record.rainfall || 0), 0);
  return (totalRainfall / weatherData.length).toFixed(1);
});

const averageTemperature = computed(() => {
  const weatherData = weatherStore.weatherHistory;
  if (weatherData.length === 0) return '0';
  const totalTemp = weatherData.reduce((total, record) => total + (record.temperature || 0), 0);
  return (totalTemp / weatherData.length).toFixed(1);
});

const weatherImpact = computed(() => {
  // Mock calculation - in real app, this would be calculated based on weather-yield correlation
  return '85';
});

const weatherCorrelationData = computed(() => {
  // Mock data - in real app, this would correlate weather data with yield data
  const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
  return {
    labels: months,
    datasets: [
      {
        label: 'Rainfall (mm)',
        data: [50, 60, 80, 120, 100, 70],
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        yAxisID: 'y'
      },
      {
        label: 'Yield (kg)',
        data: [800, 900, 1200, 1500, 1300, 1000],
        borderColor: 'rgb(34, 197, 94)',
        backgroundColor: 'rgba(34, 197, 94, 0.1)',
        yAxisID: 'y1'
      }
    ]
  };
});

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true
    }
  },
  scales: {
    y: {
      beginAtZero: true
    }
  }
};

const pieChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom'
    }
  }
};

const exportReport = () => {
  // In a real app, this would generate and download a PDF/Excel report
  console.log('Exporting report...');
};

onMounted(async () => {
  try {
    await Promise.all([
      farmStore.fetchHarvests(),
      farmStore.fetchFields(),
      farmStore.fetchSales(),
      farmStore.fetchExpenses(),
      weatherStore.fetchWeatherHistory(farmStore.fields[0]?.id)
    ]);
  } catch (error) {
    console.error('Failed to load report data:', error);
  }
});
</script>
