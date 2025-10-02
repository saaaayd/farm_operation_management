<template>
  <div class="p-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Rice Farming Analytics</h1>
          <p class="text-gray-600 mt-2">Comprehensive insights into your rice farming operations</p>
        </div>
        
        <div class="flex items-center space-x-4">
          <select
            v-model="selectedPeriod"
            @change="loadAnalytics"
            class="px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500"
          >
            <option value="3">Last 3 months</option>
            <option value="6">Last 6 months</option>
            <option value="12">Last 12 months</option>
            <option value="24">Last 24 months</option>
          </select>
          
          <button
            @click="exportReport"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
          >
            <ArrowDownTrayIcon class="w-4 h-4 mr-2" />
            Export Report
          </button>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
      <p class="mt-4 text-gray-600">Loading analytics...</p>
    </div>

    <!-- Analytics Content -->
    <div v-else-if="analytics" class="space-y-8">
      <!-- Key Metrics Overview -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <SparklesIcon class="h-8 w-8 text-green-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Total Yield</p>
              <p class="text-2xl font-bold text-gray-900">{{ analytics.production_analytics?.total_yield || 0 }} kg</p>
              <p class="text-xs text-gray-500">{{ analytics.production_analytics?.average_yield_per_hectare || 0 }} kg/ha avg</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CurrencyDollarIcon class="h-8 w-8 text-blue-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Net Profit</p>
              <p class="text-2xl font-bold text-gray-900">${{ analytics.financial_analytics?.net_profit || 0 }}</p>
              <p class="text-xs text-gray-500">{{ analytics.financial_analytics?.profit_margin || 0 }}% margin</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <GlobeAltIcon class="h-8 w-8 text-purple-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Field Performance</p>
              <p class="text-2xl font-bold text-gray-900">{{ analytics.field_performance?.average_productivity_score || 0 }}</p>
              <p class="text-xs text-gray-500">Productivity score</p>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <CloudIcon class="h-8 w-8 text-orange-600" />
            </div>
            <div class="ml-4">
              <p class="text-sm font-medium text-gray-500">Weather Suitability</p>
              <p class="text-2xl font-bold text-gray-900">{{ analytics.weather_impact?.average_weather_suitability || 0 }}%</p>
              <p class="text-xs text-gray-500">{{ analytics.weather_impact?.weather_risk_assessment || 'Unknown' }} risk</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Charts Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Production Trends -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Production Trends</h2>
          </div>
          <div class="p-6">
            <LineChart
              v-if="analytics.production_analytics?.monthly_production"
              :data="formatProductionChartData(analytics.production_analytics.monthly_production)"
              :options="chartOptions"
            />
            <div v-else class="text-center py-8 text-gray-500">
              No production data available
            </div>
          </div>
        </div>

        <!-- Financial Overview -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Financial Overview</h2>
          </div>
          <div class="p-6">
            <BarChart
              v-if="analytics.financial_analytics?.monthly_trends"
              :data="formatFinancialChartData(analytics.financial_analytics.monthly_trends)"
              :options="chartOptions"
            />
            <div v-else class="text-center py-8 text-gray-500">
              No financial data available
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Analytics Sections -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Yield by Variety -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Yield by Rice Variety</h2>
          </div>
          <div class="p-6">
            <div v-if="analytics.production_analytics?.yield_by_variety?.length > 0" class="space-y-4">
              <div
                v-for="variety in analytics.production_analytics.yield_by_variety"
                :key="variety.variety_name"
                class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
              >
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ variety.variety_name }}</p>
                  <p class="text-xs text-gray-500">{{ variety.total_area }} ha planted</p>
                </div>
                <div class="text-right">
                  <p class="text-sm font-bold text-green-600">{{ variety.yield_per_hectare }} kg/ha</p>
                  <p class="text-xs text-gray-500">{{ variety.total_yield }} kg total</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              No variety data available
            </div>
          </div>
        </div>

        <!-- Field Performance -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Field Performance</h2>
          </div>
          <div class="p-6">
            <div v-if="analytics.field_performance?.field_performance?.length > 0" class="space-y-4">
              <div
                v-for="field in analytics.field_performance.field_performance.slice(0, 5)"
                :key="field.field_id"
                class="flex items-center justify-between p-3 border border-gray-200 rounded-lg"
              >
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ field.field_name }}</p>
                  <p class="text-xs text-gray-500">{{ field.size }} ha</p>
                </div>
                <div class="text-right">
                  <div class="flex items-center">
                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                      <div
                        class="bg-green-600 h-2 rounded-full"
                        :style="{ width: `${field.productivity_score}%` }"
                      ></div>
                    </div>
                    <span class="text-xs text-gray-600">{{ field.productivity_score }}%</span>
                  </div>
                  <p class="text-xs text-gray-500">{{ field.yield_per_hectare }} kg/ha</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              No field data available
            </div>
          </div>
        </div>

        <!-- Market Performance -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Market Performance</h2>
          </div>
          <div class="p-6">
            <div v-if="analytics.market_performance?.product_performance?.length > 0" class="space-y-4">
              <div class="grid grid-cols-2 gap-4 text-center">
                <div class="p-3 bg-green-50 rounded-lg">
                  <p class="text-2xl font-bold text-green-600">{{ analytics.market_performance.total_products }}</p>
                  <p class="text-xs text-gray-600">Products Listed</p>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg">
                  <p class="text-2xl font-bold text-blue-600">{{ analytics.market_performance.average_rating }}</p>
                  <p class="text-xs text-gray-600">Avg Rating</p>
                </div>
              </div>
              
              <div v-if="analytics.market_performance.best_selling_product" class="p-3 border border-gray-200 rounded-lg">
                <p class="text-sm font-medium text-gray-900">Best Seller</p>
                <p class="text-xs text-gray-500">{{ analytics.market_performance.best_selling_product.product_name }}</p>
                <p class="text-xs text-green-600 font-medium">${{ analytics.market_performance.best_selling_product.total_revenue }} revenue</p>
              </div>
            </div>
            <div v-else class="text-center py-8 text-gray-500">
              No market data available
            </div>
          </div>
        </div>
      </div>

      <!-- Efficiency Metrics -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Efficiency Metrics</h2>
        </div>
        <div class="p-6">
          <div v-if="analytics.efficiency_metrics" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center">
              <div class="w-24 h-24 mx-auto mb-4">
                <PieChart
                  :data="formatEfficiencyData('Stage Completion', analytics.efficiency_metrics.stage_completion_efficiency)"
                  :options="{ responsive: true, maintainAspectRatio: false }"
                />
              </div>
              <p class="text-sm font-medium text-gray-900">Stage Completion</p>
              <p class="text-xs text-gray-500">{{ analytics.efficiency_metrics.stage_completion_efficiency }}% on time</p>
            </div>
            
            <div class="text-center">
              <div class="w-24 h-24 mx-auto mb-4">
                <PieChart
                  :data="formatEfficiencyData('Yield Efficiency', analytics.efficiency_metrics.yield_efficiency)"
                  :options="{ responsive: true, maintainAspectRatio: false }"
                />
              </div>
              <p class="text-sm font-medium text-gray-900">Yield Efficiency</p>
              <p class="text-xs text-gray-500">{{ analytics.efficiency_metrics.yield_efficiency }}% of expected</p>
            </div>
            
            <div class="text-center">
              <p class="text-lg font-bold text-gray-900">{{ analytics.efficiency_metrics.average_growth_cycle_days }} days</p>
              <p class="text-sm text-gray-600">Average Growth Cycle</p>
              <p class="text-xs text-gray-500">From planting to harvest</p>
            </div>
          </div>
          <div v-else class="text-center py-8 text-gray-500">
            No efficiency data available
          </div>
        </div>
      </div>

      <!-- Recommendations -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Recommendations</h2>
        </div>
        <div class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h3 class="text-sm font-medium text-gray-900 mb-3">Production Optimization</h3>
              <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-start">
                  <CheckCircleIcon class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                  Focus on high-performing rice varieties
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                  Improve soil conditions in underperforming fields
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                  Optimize planting schedules based on weather patterns
                </li>
              </ul>
            </div>
            
            <div>
              <h3 class="text-sm font-medium text-gray-900 mb-3">Financial Improvement</h3>
              <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-start">
                  <CheckCircleIcon class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                  Reduce input costs through efficient resource management
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                  Explore premium market opportunities
                </li>
                <li class="flex items-start">
                  <CheckCircleIcon class="w-4 h-4 text-green-500 mr-2 mt-0.5 flex-shrink-0" />
                  Consider value-added processing options
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="text-center py-12">
      <ExclamationTriangleIcon class="mx-auto h-12 w-12 text-red-500" />
      <h3 class="mt-2 text-sm font-medium text-gray-900">Error loading analytics</h3>
      <p class="mt-1 text-sm text-gray-500">{{ error }}</p>
      <div class="mt-6">
        <button
          @click="loadAnalytics"
          class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
        >
          Try Again
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { analyticsAPI } from '@/services/api';
import {
  SparklesIcon,
  CurrencyDollarIcon,
  GlobeAltIcon,
  CloudIcon,
  ArrowDownTrayIcon,
  CheckCircleIcon,
  ExclamationTriangleIcon,
} from '@heroicons/vue/24/outline';
import LineChart from '@/components/Charts/LineChart.vue';
import BarChart from '@/components/Charts/BarChart.vue';
import PieChart from '@/components/Charts/PieChart.vue';

// Reactive data
const analytics = ref(null);
const loading = ref(true);
const error = ref('');
const selectedPeriod = ref('12');

// Chart options
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'top',
    },
  },
  scales: {
    y: {
      beginAtZero: true,
    },
  },
};

// Methods
const loadAnalytics = async () => {
  try {
    loading.value = true;
    error.value = '';
    
    const response = await analyticsAPI.getRiceFarmingAnalytics(selectedPeriod.value);
    analytics.value = response.data.analytics;
    
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load analytics data';
    console.error('Error loading analytics:', err);
  } finally {
    loading.value = false;
  }
};

const formatProductionChartData = (monthlyProduction) => {
  return {
    labels: monthlyProduction.map(item => item.month),
    datasets: [
      {
        label: 'Total Yield (kg)',
        data: monthlyProduction.map(item => item.total_yield),
        borderColor: 'rgb(34, 197, 94)',
        backgroundColor: 'rgba(34, 197, 94, 0.1)',
        tension: 0.4,
      },
    ],
  };
};

const formatFinancialChartData = (monthlyTrends) => {
  return {
    labels: monthlyTrends.map(item => item.month),
    datasets: [
      {
        label: 'Revenue ($)',
        data: monthlyTrends.map(item => item.revenue),
        backgroundColor: 'rgba(34, 197, 94, 0.8)',
      },
      {
        label: 'Expenses ($)',
        data: monthlyTrends.map(item => item.expenses),
        backgroundColor: 'rgba(239, 68, 68, 0.8)',
      },
      {
        label: 'Profit ($)',
        data: monthlyTrends.map(item => item.profit),
        backgroundColor: 'rgba(59, 130, 246, 0.8)',
      },
    ],
  };
};

const formatEfficiencyData = (label, value) => {
  return {
    labels: [label, 'Remaining'],
    datasets: [
      {
        data: [value, 100 - value],
        backgroundColor: ['#22c55e', '#e5e7eb'],
        borderWidth: 0,
      },
    ],
  };
};

const exportReport = () => {
  // Implement report export functionality
  console.log('Exporting analytics report...');
  // You could generate a PDF or CSV file here
};

onMounted(() => {
  loadAnalytics();
});
</script>