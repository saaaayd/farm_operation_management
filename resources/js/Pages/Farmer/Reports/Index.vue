<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Standard Header -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
          <div class="flex items-center gap-3">
            <h1 class="text-3xl font-bold text-gray-800">Farm Reports & Analytics</h1>
          </div>
          <p class="text-gray-500 mt-1">Analyze your rice farming performance and financial data</p>
        </div>
        
        <div class="flex items-center space-x-3">
          <select 
            v-model="selectedPeriod"
            class="px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-green-500 focus:border-green-500 bg-white shadow-sm"
          >
            <option value="30">Last 30 Days</option>
            <option value="90">Last 3 Months</option>
            <option value="365">Last Year</option>
            <option value="all">All Time</option>
          </select>
          <button 
            @click="exportReport"
            :disabled="loading || !!loadError"
            class="flex items-center gap-2 bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors shadow-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export Report
          </button>
        </div>
      </div>

      <div>
      <div v-if="loadError" class="bg-red-50 border border-red-200 rounded-md p-6 mb-8">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-6 w-6 text-red-400" viewBox="0 0 24 24" fill="none" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Failed to load reports</h3>
            <p class="mt-2 text-sm text-red-700">{{ loadError }}</p>
            <button
              @click="loadReportData"
              class="mt-4 inline-flex items-center px-3 py-2 bg-red-600 text-white text-sm font-medium rounded-md hover:bg-red-700"
            >
              Retry loading data
            </button>
          </div>
        </div>
      </div>

      <div
        v-else-if="loading"
        class="flex flex-col items-center justify-center py-24 text-gray-600"
      >
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mb-4"></div>
        <p>Loading farmer reports...</p>
      </div>

      <div v-else>
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
                <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(totalRevenue) }}</p>
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
                <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(totalExpenses) }}</p>
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
                <p class="text-2xl font-semibold text-gray-900">{{ formatCurrency(netProfit) }}</p>
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
              :options="weatherChartOptions"
            />
            <div v-else class="flex items-center justify-center h-full text-gray-500">
              <p>No weather correlation data available</p>
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
import { ref, computed, onMounted, watch } from 'vue';
import { useFarmStore } from '@/stores/farm';
import { useWeatherStore } from '@/stores/weather';
import LineChart from '@/Components/Charts/LineChart.vue';
import BarChart from '@/Components/Charts/BarChart.vue';
import PieChart from '@/Components/Charts/PieChart.vue';
import { formatCurrency } from '@/utils/format';

const farmStore = useFarmStore();
const weatherStore = useWeatherStore();

const activeTab = ref('yield');
const selectedPeriod = ref('365');
const loading = ref(false);
const loadError = ref('');

const tabs = [
  { id: 'yield', name: 'Yield Report' },
  { id: 'financial', name: 'Financial Report' },
  { id: 'weather', name: 'Weather Correlation' }
];

const chartColors = [
  'rgba(34, 197, 94, 0.5)',
  'rgba(59, 130, 246, 0.5)',
  'rgba(168, 85, 247, 0.5)',
  'rgba(245, 158, 11, 0.5)',
  'rgba(239, 68, 68, 0.5)',
  'rgba(14, 165, 233, 0.5)',
  'rgba(251, 191, 36, 0.5)'
];

const ensureArray = (value) => (Array.isArray(value) ? value : []);
const getColor = (index) => chartColors[index % chartColors.length];

const formatDateForApi = (date) => {
  if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
    return '';
  }
  return date.toISOString().split('T')[0];
};

const formatLabelDate = (date) => {
  const parsed = date ? new Date(date) : null;
  if (!parsed || Number.isNaN(parsed.getTime())) {
    return '';
  }
  return parsed.toLocaleDateString(undefined, { month: 'short', day: 'numeric' });
};

const monthKey = (date) => {
  const parsed = date ? new Date(date) : null;
  if (!parsed || Number.isNaN(parsed.getTime())) {
    return null;
  }
  return `${parsed.getFullYear()}-${String(parsed.getMonth() + 1).padStart(2, '0')}`;
};

const monthLabelFromKey = (key) => {
  if (!key) return '';
  const [year, month] = key.split('-').map(Number);
  if (!year || !month) return '';
  const date = new Date(year, month - 1, 1);
  return date.toLocaleDateString(undefined, { month: 'short', year: 'numeric' });
};

const computePeriodFilters = (period) => {
  if (period === 'all') {
    return { filters: {}, weatherDays: 365 };
  }

  const days = parseInt(period, 10);
  if (!Number.isFinite(days) || days <= 0) {
    return { filters: {}, weatherDays: 30 };
  }

  const endDate = new Date();
  const startDate = new Date();
  startDate.setDate(endDate.getDate() - (days - 1));

  return {
    filters: {
      date_from: formatDateForApi(startDate),
      date_to: formatDateForApi(endDate),
    },
    weatherDays: Math.min(days, 365),
  };
};

const aggregateByMonth = (records, dateKey, valueKey) => {
  const result = new Map();
  ensureArray(records).forEach((record) => {
    const dateValue = record?.[dateKey];
    const value = Number(record?.[valueKey]);
    if (!dateValue || Number.isNaN(value)) {
      return;
    }

    const key = monthKey(dateValue);
    if (!key) return;

    result.set(key, (result.get(key) || 0) + value);
  });
  return result;
};

const harvests = computed(() => ensureArray(farmStore.harvests));
const fields = computed(() => ensureArray(farmStore.fields));
const sales = computed(() => ensureArray(farmStore.sales));
const expensesList = computed(() => ensureArray(farmStore.expenses));
const weatherHistoryRecords = computed(() => ensureArray(weatherStore.weatherHistory));

const loadReportData = async () => {
  if (loading.value) {
    // Allow data refresh even if already loading to keep data current
    console.warn('Reloading farmer reports data...');
  }

  loading.value = true;
  loadError.value = '';

  try {
    await Promise.all([
      farmStore.fetchFields(),
      farmStore.fetchHarvests()
    ]);

    const { filters, weatherDays } = computePeriodFilters(selectedPeriod.value);

    await Promise.all([
      farmStore.fetchSales(filters),
      farmStore.fetchExpenses(filters)
    ]);

    const primaryFieldId = fields.value[0]?.id;

    if (primaryFieldId) {
      await weatherStore.fetchWeatherHistory(primaryFieldId, weatherDays);
    } else {
      console.warn('No fields found for weather analytics');
    }
  } catch (error) {
    console.error('Failed to load report data:', error);
    loadError.value = error.userMessage || error.response?.data?.message || 'Unable to load report data. Please try again.';
  } finally {
    loading.value = false;
  }
};

// Yield Report Data
const totalYield = computed(() => {
  const total = harvests.value.reduce((sum, harvest) => sum + (Number(harvest?.yield) || 0), 0);
  return total.toFixed(0);
});

const averageYieldPerHectare = computed(() => {
  if (!harvests.value.length || !fields.value.length) {
    return '0';
  }

  const totalYieldKg = harvests.value.reduce((sum, harvest) => sum + (Number(harvest?.yield) || 0), 0);
  const totalArea = fields.value.reduce((sum, field) => sum + (Number(field?.size) || 0), 0);

  if (totalArea <= 0) {
    return '0';
  }

  return (totalYieldKg / totalArea).toFixed(0);
});

const bestVariety = computed(() => {
  if (!harvests.value.length) {
    return 'N/A';
  }

  const varietyTotals = harvests.value.reduce((acc, harvest) => {
    const variety = harvest?.planting?.crop_type || 'Unknown Variety';
    const yieldValue = Number(harvest?.yield) || 0;
    acc[variety] = (acc[variety] || 0) + yieldValue;
    return acc;
  }, {});

  const entries = Object.entries(varietyTotals);
  if (!entries.length) {
    return 'N/A';
  }

  const [topVariety] = entries.reduce((best, current) => (current[1] > best[1] ? current : best));
  return topVariety;
});

const totalHarvests = computed(() => harvests.value.length);

const yieldChartData = computed(() => {
  const ordered = harvests.value
    .filter(harvest => harvest?.harvest_date && !Number.isNaN(new Date(harvest.harvest_date).getTime()))
    .sort((a, b) => new Date(a.harvest_date) - new Date(b.harvest_date))
    .slice(-12);

  if (!ordered.length) {
    return { labels: [], datasets: [] };
  }

  return {
    labels: ordered.map((harvest) => formatLabelDate(harvest.harvest_date)),
    datasets: [{
      label: 'Yield (kg)',
      data: ordered.map((harvest) => Number(harvest?.yield) || 0),
      borderColor: 'rgb(34, 197, 94)',
      backgroundColor: 'rgba(34, 197, 94, 0.1)',
      tension: 0.1
    }]
  };
});

const varietyChartData = computed(() => {
  const varietyTotals = harvests.value.reduce((acc, harvest) => {
    const variety = harvest?.planting?.crop_type || 'Unknown Variety';
    const yieldValue = Number(harvest?.yield) || 0;
    acc[variety] = (acc[variety] || 0) + yieldValue;
    return acc;
  }, {});

  const entries = Object.entries(varietyTotals).sort((a, b) => b[1] - a[1]);

  if (!entries.length) {
    return { labels: [], datasets: [] };
  }

  const labels = entries.map(([variety]) => variety);
  const data = entries.map(([, total]) => total);

  return {
    labels,
    datasets: [{
      label: 'Yield (kg)',
      data,
      backgroundColor: labels.map((_, index) => getColor(index))
    }]
  };
});

// Financial Report Data
const totalRevenue = computed(() => {
  const total = sales.value.reduce((sum, sale) => sum + (Number(sale?.total_amount) || 0), 0);
  return Number(total.toFixed(2));
});

const totalExpenses = computed(() => {
  const total = expensesList.value.reduce((sum, expense) => sum + (Number(expense?.amount) || 0), 0);
  return Number(total.toFixed(2));
});

const netProfit = computed(() => {
  const profit = Number(totalRevenue.value) - Number(totalExpenses.value);
  return Number(profit.toFixed(2));
});

const profitMargin = computed(() => {
  const revenue = Number(totalRevenue.value);
  if (revenue <= 0) {
    return '0.0';
  }
  const profit = Number(netProfit.value);
  return ((profit / revenue) * 100).toFixed(1);
});

const revenueByMonth = computed(() => aggregateByMonth(sales.value, 'sale_date', 'total_amount'));
const expensesByMonth = computed(() => aggregateByMonth(expensesList.value, 'date', 'amount'));

const monthLabels = computed(() => {
  const keys = new Set([
    ...revenueByMonth.value.keys(),
    ...expensesByMonth.value.keys()
  ]);
  return Array.from(keys).sort();
});

const financialChartData = computed(() => {
  if (!monthLabels.value.length) {
    return { labels: [], datasets: [] };
  }

  const labels = monthLabels.value.map(monthLabelFromKey);

  const revenueData = monthLabels.value.map((key) => parseFloat((revenueByMonth.value.get(key) || 0).toFixed(2)));
  const expensesData = monthLabels.value.map((key) => parseFloat((expensesByMonth.value.get(key) || 0).toFixed(2)));

  return {
    labels,
    datasets: [
      {
        label: 'Revenue',
        data: revenueData,
        borderColor: 'rgb(34, 197, 94)',
        backgroundColor: 'rgba(34, 197, 94, 0.15)',
        tension: 0.2,
        fill: true
      },
      {
        label: 'Expenses',
        data: expensesData,
        borderColor: 'rgb(239, 68, 68)',
        backgroundColor: 'rgba(239, 68, 68, 0.15)',
        tension: 0.2,
        fill: true
      }
    ]
  };
});

const expenseChartData = computed(() => {
  if (!expensesList.value.length) {
    return { labels: [], datasets: [] };
  }

  const categoryTotals = expensesList.value.reduce((acc, expense) => {
    const category = expense?.category || 'Uncategorized';
    const amount = Number(expense?.amount) || 0;
    acc[category] = (acc[category] || 0) + amount;
    return acc;
  }, {});

  const entries = Object.entries(categoryTotals).sort((a, b) => b[1] - a[1]);
  const labels = entries.map(([category]) => category);
  const data = entries.map(([, amount]) => Number(amount.toFixed(2)));

  return {
    labels,
    datasets: [{
      data,
      backgroundColor: labels.map((_, index) => getColor(index)),
      borderWidth: 1
    }]
  };
});

// Weather Correlation Data
const weatherByMonth = computed(() => {
  const map = new Map();
  weatherHistoryRecords.value.forEach((record) => {
    const key = monthKey(record?.recorded_at);
    if (!key) return;

    const rainfall = Number(record?.rainfall) || 0;
    const temperature = Number(record?.temperature) || 0;
    const entry = map.get(key) || { rainfall: 0, temperature: 0, count: 0 };

    entry.rainfall += rainfall;
    entry.temperature += temperature;
    entry.count += 1;

    map.set(key, entry);
  });
  return map;
});

const averageRainfall = computed(() => {
  if (!weatherHistoryRecords.value.length) {
    return '0.0';
  }
  const total = weatherHistoryRecords.value.reduce((sum, record) => sum + (Number(record?.rainfall) || 0), 0);
  return (total / weatherHistoryRecords.value.length).toFixed(1);
});

const averageTemperature = computed(() => {
  if (!weatherHistoryRecords.value.length) {
    return '0.0';
  }
  const total = weatherHistoryRecords.value.reduce((sum, record) => sum + (Number(record?.temperature) || 0), 0);
  return (total / weatherHistoryRecords.value.length).toFixed(1);
});

const weatherImpact = computed(() => {
  if (!weatherHistoryRecords.value.length) {
    return '0';
  }
  const favorable = weatherHistoryRecords.value.filter((record) => {
    const rainfall = Number(record?.rainfall);
    const temperature = Number(record?.temperature);
    if (Number.isNaN(rainfall) || Number.isNaN(temperature)) {
      return false;
    }
    return rainfall >= 60 && rainfall <= 140 && temperature >= 20 && temperature <= 35;
  }).length;

  return ((favorable / weatherHistoryRecords.value.length) * 100).toFixed(0);
});

const yieldByMonth = computed(() => aggregateByMonth(harvests.value, 'harvest_date', 'yield'));

const weatherCorrelationData = computed(() => {
  const keys = new Set([
    ...weatherByMonth.value.keys(),
    ...yieldByMonth.value.keys()
  ]);

  const orderedKeys = Array.from(keys).sort();

  if (!orderedKeys.length) {
    return { labels: [], datasets: [] };
  }

  const labels = orderedKeys.map(monthLabelFromKey);
  const rainfallData = orderedKeys.map((key) => {
    const entry = weatherByMonth.value.get(key);
    if (!entry || entry.count === 0) return 0;
    return Number((entry.rainfall / entry.count).toFixed(1));
  });
  const yieldData = orderedKeys.map((key) => {
    const totalYield = yieldByMonth.value.get(key) || 0;
    return Number(totalYield.toFixed(1));
  });

  return {
    labels,
    datasets: [
      {
        label: 'Avg Rainfall (mm)',
        data: rainfallData,
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.2,
        yAxisID: 'y'
      },
      {
        label: 'Yield (kg)',
        data: yieldData,
        borderColor: 'rgb(34, 197, 94)',
        backgroundColor: 'rgba(34, 197, 94, 0.1)',
        tension: 0.2,
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

const weatherChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'bottom'
    }
  },
  scales: {
    y: {
      beginAtZero: true,
      position: 'left'
    },
    y1: {
      beginAtZero: true,
      position: 'right',
      grid: {
        drawOnChartArea: false
      }
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
  if (loading.value || loadError.value) {
    return;
  }

  const payload = {
    generated_at: new Date().toISOString(),
    period: selectedPeriod.value,
    totals: {
      yield_kg: Number(totalYield.value) || 0,
      average_yield_per_hectare: Number(averageYieldPerHectare.value) || 0,
      best_variety: bestVariety.value,
      harvest_count: totalHarvests.value,
      revenue: Number(totalRevenue.value) || 0,
      expenses: Number(totalExpenses.value) || 0,
      net_profit: Number(netProfit.value) || 0,
      profit_margin: Number(profitMargin.value) || 0,
    },
    weather: {
      average_rainfall_mm: Number(averageRainfall.value) || 0,
      average_temperature_c: Number(averageTemperature.value) || 0,
      favorable_conditions_percent: Number(weatherImpact.value) || 0,
    },
    generated_from: 'FarmerReportsIndex',
  };

  const blob = new Blob([JSON.stringify(payload, null, 2)], { type: 'application/json' });
  const url = URL.createObjectURL(blob);
  const anchor = document.createElement('a');
  anchor.href = url;
  anchor.download = `farmer-report-${selectedPeriod.value}-${Date.now()}.json`;
  document.body.appendChild(anchor);
  anchor.click();
  document.body.removeChild(anchor);
  URL.revokeObjectURL(url);
};

watch(selectedPeriod, () => {
  loadReportData();
});

onMounted(() => {
  loadReportData();
});
</script>
