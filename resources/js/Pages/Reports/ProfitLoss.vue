<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Profit & Loss Report</h1>
          <p class="text-gray-500 mt-1">Track your farm's financial performance and ROI by crop cycle.</p>
        </div>
        <div class="flex items-center gap-3">
          <select v-model="period" @change="loadData" class="px-4 py-2 border rounded-lg">
            <option value="30">Last 30 days</option>
            <option value="90">Last 90 days</option>
            <option value="180">Last 6 months</option>
            <option value="365">Last year</option>
            <option value="all">All time</option>
          </select>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
      </div>

      <div v-else>
        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500 mb-1">Total Revenue</p>
            <p class="text-2xl font-bold text-green-600">₱{{ formatNumber(summary.total_revenue) }}</p>
          </div>
          <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500 mb-1">Total Expenses</p>
            <p class="text-2xl font-bold text-red-600">₱{{ formatNumber(summary.total_expenses) }}</p>
          </div>
          <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500 mb-1">Net Profit</p>
            <p :class="['text-2xl font-bold', summary.net_profit >= 0 ? 'text-green-600' : 'text-red-600']">
              ₱{{ formatNumber(summary.net_profit) }}
            </p>
          </div>
          <div class="bg-white rounded-xl shadow p-6">
            <p class="text-sm text-gray-500 mb-1">Profit Margin</p>
            <p :class="['text-2xl font-bold', summary.profit_margin >= 0 ? 'text-green-600' : 'text-red-600']">
              {{ summary.profit_margin }}%
            </p>
          </div>
        </div>

        <!-- Expense Breakdown -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
          <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Expenses by Category</h2>
            <div class="space-y-3">
              <div v-for="(amount, category) in expensesByCategory" :key="category" class="flex items-center justify-between">
                <span class="text-gray-700 capitalize">{{ category.replace(/_/g, ' ') }}</span>
                <span class="font-semibold">₱{{ formatNumber(amount) }}</span>
              </div>
              <div v-if="Object.keys(expensesByCategory).length === 0" class="text-gray-500 text-center py-4">
                No expenses recorded
              </div>
            </div>
          </div>

          <!-- Monthly Trend -->
          <div class="bg-white rounded-xl shadow p-6">
            <h2 class="text-lg font-semibold mb-4">Monthly Trend</h2>
            <div class="space-y-2 max-h-64 overflow-y-auto">
              <div v-for="month in monthlyTrend" :key="month.month" class="flex items-center justify-between py-2 border-b border-gray-100">
                <span class="text-gray-700">{{ month.month }}</span>
                <div class="text-right">
                  <span class="text-green-600 mr-4">+₱{{ formatNumber(month.revenue) }}</span>
                  <span class="text-red-600 mr-4">-₱{{ formatNumber(month.expenses) }}</span>
                  <span :class="['font-semibold', month.profit >= 0 ? 'text-green-600' : 'text-red-600']">
                    ₱{{ formatNumber(month.profit) }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- ROI by Planting -->
        <div class="bg-white rounded-xl shadow p-6">
          <h2 class="text-lg font-semibold mb-4">ROI by Crop Cycle</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Field</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Variety</th>
                  <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Revenue</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Expenses</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Profit</th>
                  <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">ROI</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="planting in plantings" :key="planting.id" class="hover:bg-gray-50">
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">{{ planting.field_name }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-700">{{ planting.variety }}</td>
                  <td class="px-4 py-3 whitespace-nowrap">
                    <span :class="getStatusClass(planting.status)" class="px-2 py-1 rounded-full text-xs font-medium capitalize">
                      {{ planting.status?.replace(/_/g, ' ') }}
                    </span>
                  </td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-green-600">₱{{ formatNumber(planting.revenue) }}</td>
                  <td class="px-4 py-3 whitespace-nowrap text-sm text-right text-red-600">₱{{ formatNumber(planting.expenses) }}</td>
                  <td :class="['px-4 py-3 whitespace-nowrap text-sm text-right font-medium', planting.net_profit >= 0 ? 'text-green-600' : 'text-red-600']">
                    ₱{{ formatNumber(planting.net_profit) }}
                  </td>
                  <td :class="['px-4 py-3 whitespace-nowrap text-sm text-right font-bold', planting.roi >= 0 ? 'text-green-600' : 'text-red-600']">
                    {{ planting.roi }}%
                  </td>
                </tr>
                <tr v-if="plantings.length === 0">
                  <td colspan="7" class="px-4 py-8 text-center text-gray-500">No planting data available</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const loading = ref(true)
const period = ref('365')
const summary = ref({ total_revenue: 0, total_expenses: 0, net_profit: 0, profit_margin: 0 })
const expensesByCategory = ref({})
const monthlyTrend = ref([])
const plantings = ref([])

const loadData = async () => {
  loading.value = true
  try {
    const days = period.value === 'all' ? 3650 : parseInt(period.value)
    const startDate = new Date()
    startDate.setDate(startDate.getDate() - days)

    const [summaryRes, plantingsRes] = await Promise.all([
      axios.get('/api/reports/profit-loss', {
        params: { start_date: startDate.toISOString().split('T')[0] }
      }),
      axios.get('/api/reports/profit-loss/by-planting')
    ])

    summary.value = summaryRes.data.summary
    expensesByCategory.value = summaryRes.data.expenses_by_category || {}
    monthlyTrend.value = summaryRes.data.monthly_trend || []
    plantings.value = plantingsRes.data.plantings || []
  } catch (error) {
    console.error('Failed to load profit/loss data:', error)
  } finally {
    loading.value = false
  }
}

const formatNumber = (value) => {
  return Number(value || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    growing: 'bg-blue-100 text-blue-800',
    harvested: 'bg-purple-100 text-purple-800',
    completed: 'bg-gray-100 text-gray-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

onMounted(() => {
  loadData()
})
</script>
