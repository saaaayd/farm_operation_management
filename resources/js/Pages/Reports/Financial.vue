<template>
  <div class="financial-reports-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Financial Reports</h1>
          <p class="text-gray-600 mt-2">Track your farm's financial performance</p>
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

      <!-- Date Range Selector -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Report Period</h2>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <input
              v-model="startDate"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
            <input
              v-model="endDate"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Report Type</label>
            <select
              v-model="reportType"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="income">Income Statement</option>
              <option value="expenses">Expense Report</option>
              <option value="profit">Profit & Loss</option>
              <option value="cashflow">Cash Flow</option>
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

      <!-- Financial Summary -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                <span class="text-green-600 text-2xl">💰</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(financialSummary.totalRevenue) }}</div>
              <div class="text-sm text-gray-600">Total Revenue</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                <span class="text-red-600 text-2xl">💸</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(financialSummary.totalExpenses) }}</div>
              <div class="text-sm text-gray-600">Total Expenses</div>
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
              <div class="text-2xl font-bold text-gray-900">{{ formatCurrency(financialSummary.netProfit) }}</div>
              <div class="text-sm text-gray-600">Net Profit</div>
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
              <div class="text-2xl font-bold text-gray-900">{{ financialSummary.profitMargin }}%</div>
              <div class="text-sm text-gray-600">Profit Margin</div>
            </div>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- Revenue Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold mb-4">Revenue Trends</h2>
          <div class="w-full h-64 bg-gray-200 rounded-lg flex items-center justify-center">
            <LineChart v-if="revenueChartData.labels.length > 0" :data="revenueChartData" />
            <div v-else class="h-full flex items-center justify-center text-gray-500">
              No revenue data available
            </div>
          </div>
        </div>

        <!-- Expense Breakdown -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold mb-4">Expense Breakdown</h2>
          <div class="space-y-4">
            <div
              v-for="expense in expenseBreakdown"
              :key="expense.category"
              class="flex items-center justify-between"
            >
              <div class="flex items-center space-x-3">
                <div class="w-4 h-4 rounded-full" :style="{ backgroundColor: expense.color }"></div>
                <span class="text-gray-700">{{ expense.category }}</span>
              </div>
              <div class="text-right">
                <div class="font-medium">{{ formatCurrency(expense.amount) }}</div>
                <div class="text-sm text-gray-600">{{ expense.percentage }}%</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Detailed Transactions -->
      <div class="mt-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold mb-4">Recent Transactions</h2>
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="transaction in transactions" :key="transaction.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(transaction.date) }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-900">
                    {{ transaction.description }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ transaction.category }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="transaction.type === 'income' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                      class="px-2 py-1 text-xs font-medium rounded-full"
                    >
                      {{ transaction.type }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium"
                      :class="transaction.type === 'income' ? 'text-green-600' : 'text-red-600'">
                    {{ transaction.type === 'income' ? '+' : '-' }}{{ formatCurrency(Math.abs(transaction.amount)) }}
                  </td>
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
import { ref, onMounted, computed } from 'vue'
import { formatCurrency } from '@/utils/format'
import { reportsAPI } from '@/services/api'
import LineChart from '@/Components/Charts/LineChart.vue'

const startDate = ref('2024-01-01')
const endDate = ref('2024-12-31')
const reportType = ref('income')

const financialSummary = ref({
  totalRevenue: 0,
  totalExpenses: 0,
  netProfit: 0,
  profitMargin: 0
})

const expenseBreakdown = ref([])

const transactions = ref([])

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const updateReport = async () => {
  // Reload report data with current filters
  try {
    await loadFinancialData()
    alert('Report updated successfully')
  } catch (error) {
    console.error('Failed to update report:', error)
    alert('Failed to update report')
  }
}

const generateReport = async () => {
  // Generate new report with current filters
  try {
    await loadFinancialData()
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
    link.setAttribute('download', `financial-report-${new Date().toISOString().split('T')[0]}.csv`)
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
  // Generate CSV content from financial data
  const headers = ['Date', 'Type', 'Description', 'Amount', 'Category']
  const rows = transactions.value.map(item => [
    item.date || 'N/A',
    item.type || 'N/A',
    item.description || 'N/A',
    item.amount || 0,
    item.category || 'N/A',
  ])
  
  return [headers, ...rows].map(row => row.join(',')).join('\n')
}

const revenueChartData = computed(() => {
  if (!revenueTrends.value || revenueTrends.value.length === 0) {
    return {
      labels: [],
      datasets: []
    }
  }
  
  return {
    labels: revenueTrends.value.map(item => item.month),
    datasets: [{
      label: 'Revenue (₱)',
      data: revenueTrends.value.map(item => item.revenue),
      borderColor: 'rgb(34, 197, 94)',
      backgroundColor: 'rgba(34, 197, 94, 0.1)',
      fill: true,
      tension: 0.4,
    }]
  }
})

const revenueTrends = ref([])

onMounted(() => {
  // Load financial data from API
  loadFinancialData()
})

const loadFinancialData = async () => {
  try {
    const start = new Date(startDate.value)
    const end = new Date(endDate.value)
    const period = Math.ceil((end - start) / (1000 * 60 * 60 * 24)) || 365
    const response = await reportsAPI.getFinancialReport(period)
    const data = response.data.data || response.data
    
    if (data.financial_summary) {
      financialSummary.value = {
        totalRevenue: data.financial_summary.total_revenue || 0,
        totalExpenses: data.financial_summary.total_expenses || 0,
        netProfit: data.financial_summary.net_profit || 0,
        profitMargin: data.financial_summary.profit_margin || 0
      }
    }
    
    if (data.expense_breakdown) {
      expenseBreakdown.value = data.expense_breakdown
    }
    
    if (data.revenue_trends) {
      revenueTrends.value = data.revenue_trends
    }
    
    if (data.transactions) {
      transactions.value = data.transactions
    }
  } catch (error) {
    console.error('Error loading financial data:', error)
    alert('Failed to load financial data')
  }
}
</script>

<style scoped>
.financial-reports-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>