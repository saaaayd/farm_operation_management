<template>
  <div class="admin-reports-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">System Reports</h1>
          <p class="text-gray-600 mt-2">Generate and view comprehensive system reports</p>
        </div>
        <router-link
          to="/admin"
          class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700"
        >
          Back to Dashboard
        </router-link>
      </div>

      <!-- Report Categories -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition" @click="viewReport('sales-trends')">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
              <span class="text-green-600 text-2xl">📈</span>
            </div>
            <h3 class="ml-4 text-lg font-semibold text-gray-900">Sales Trends</h3>
          </div>
          <p class="text-gray-600 text-sm">View sales trends and revenue analytics</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition" @click="viewReport('expense-summary')">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
              <span class="text-red-600 text-2xl">💰</span>
            </div>
            <h3 class="ml-4 text-lg font-semibold text-gray-900">Expense Summary</h3>
          </div>
          <p class="text-gray-600 text-sm">Review expense breakdowns and categories</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition" @click="viewReport('inventory-usage')">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
              <span class="text-blue-600 text-2xl">📦</span>
            </div>
            <h3 class="ml-4 text-lg font-semibold text-gray-900">Inventory Usage</h3>
          </div>
          <p class="text-gray-600 text-sm">System-wide inventory analysis</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6 cursor-pointer hover:shadow-lg transition" @click="viewReport('financial-audit')">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
              <span class="text-purple-600 text-2xl">🔍</span>
            </div>
            <h3 class="ml-4 text-lg font-semibold text-gray-900">Financial Audit</h3>
          </div>
          <p class="text-gray-600 text-sm">Complete financial transaction audit</p>
        </div>
      </div>

      <!-- Report Viewer -->
      <div v-if="selectedReport" class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
          <h2 class="text-2xl font-semibold text-gray-900">{{ getReportTitle(selectedReport) }}</h2>
          <div class="flex space-x-3">
            <button
              @click="exportReport"
              class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700"
            >
              Export CSV
            </button>
            <button
              @click="selectedReport = null"
              class="bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400"
            >
              Close
            </button>
          </div>
        </div>

        <!-- Date Range Filter -->
        <div v-if="selectedReport !== 'inventory-usage'" class="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
            <input
              v-model="dateRange.start_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
            <input
              v-model="dateRange.end_date"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div class="flex items-end">
            <button
              @click="loadReport"
              :disabled="loading"
              class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
            >
              {{ loading ? 'Loading...' : 'Load Report' }}
            </button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="text-center py-12 text-gray-500">
          <div class="mx-auto mb-4 h-10 w-10 animate-spin rounded-full border-b-2 border-blue-600"></div>
          Loading report data...
        </div>

        <!-- Report Content -->
        <div v-else-if="reportData">
          <!-- Sales Trends -->
          <div v-if="selectedReport === 'sales-trends' && reportData.summary">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
              <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Total Sales</div>
                <div class="text-2xl font-bold text-blue-600">{{ reportData.summary.total_sales || 0 }}</div>
              </div>
              <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Total Amount</div>
                <div class="text-2xl font-bold text-green-600">{{ formatCurrency(reportData.summary.total_amount || 0) }}</div>
              </div>
              <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Average Amount</div>
                <div class="text-2xl font-bold text-purple-600">{{ formatCurrency(reportData.summary.average_amount || 0) }}</div>
              </div>
            </div>
            <div class="mb-6">
              <h3 class="text-lg font-semibold mb-4">Monthly Breakdown</h3>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Month</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Count</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Average</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="(data, month) in reportData.monthly_breakdown" :key="month">
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ month }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ data.count }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatCurrency(data.total_amount) }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatCurrency(data.average_amount) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Expense Summary -->
          <div v-if="selectedReport === 'expense-summary' && reportData.summary">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
              <div class="bg-red-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Total Expenses</div>
                <div class="text-2xl font-bold text-red-600">{{ reportData.summary.total_expenses || 0 }}</div>
              </div>
              <div class="bg-orange-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Total Amount</div>
                <div class="text-2xl font-bold text-orange-600">{{ formatCurrency(reportData.summary.total_amount || 0) }}</div>
              </div>
              <div class="bg-yellow-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Average Amount</div>
                <div class="text-2xl font-bold text-yellow-600">{{ formatCurrency(reportData.summary.average_amount || 0) }}</div>
              </div>
            </div>
            <div class="mb-6">
              <h3 class="text-lg font-semibold mb-4">Category Breakdown</h3>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Count</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Amount</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Average</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="category in reportData.category_breakdown" :key="category.category">
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">{{ category.category }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ category.count }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatCurrency(category.total_amount) }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatCurrency(category.average_amount) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Inventory Usage -->
          <div v-if="selectedReport === 'inventory-usage' && reportData.summary">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
              <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Total Items</div>
                <div class="text-2xl font-bold text-blue-600">{{ reportData.summary.total_items || 0 }}</div>
              </div>
              <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Total Quantity</div>
                <div class="text-2xl font-bold text-green-600">{{ reportData.summary.total_quantity || 0 }}</div>
              </div>
              <div class="bg-red-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Low Stock Items</div>
                <div class="text-2xl font-bold text-red-600">{{ reportData.summary.low_stock_count || 0 }}</div>
              </div>
            </div>
            <div class="mb-6">
              <h3 class="text-lg font-semibold mb-4">Category Breakdown</h3>
              <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                  <thead class="bg-gray-50">
                    <tr>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Items</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Quantity</th>
                      <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Value</th>
                    </tr>
                  </thead>
                  <tbody class="bg-white divide-y divide-gray-200">
                    <tr v-for="category in reportData.category_breakdown" :key="category.category">
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 capitalize">{{ category.category }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ category.total_items }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ category.total_quantity }}</td>
                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{{ formatCurrency(category.total_value) }}</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Financial Audit -->
          <div v-if="selectedReport === 'financial-audit' && reportData">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
              <div class="bg-green-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Sales Transactions</div>
                <div class="text-2xl font-bold text-green-600">{{ reportData.sales?.total_transactions || 0 }}</div>
                <div class="text-sm text-gray-500 mt-1">{{ formatCurrency(reportData.sales?.total_amount || 0) }}</div>
              </div>
              <div class="bg-red-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Expense Transactions</div>
                <div class="text-2xl font-bold text-red-600">{{ reportData.expenses?.total_transactions || 0 }}</div>
                <div class="text-sm text-gray-500 mt-1">{{ formatCurrency(reportData.expenses?.total_amount || 0) }}</div>
              </div>
              <div class="bg-blue-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Order Transactions</div>
                <div class="text-2xl font-bold text-blue-600">{{ reportData.orders?.total_transactions || 0 }}</div>
                <div class="text-sm text-gray-500 mt-1">{{ formatCurrency(reportData.orders?.total_amount || 0) }}</div>
              </div>
              <div class="bg-purple-50 p-4 rounded-lg">
                <div class="text-sm text-gray-600">Net Profit</div>
                <div class="text-2xl font-bold text-purple-600">{{ formatCurrency(reportData.net_profit || 0) }}</div>
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
import { adminAPI } from '@/services/api'

const loading = ref(false)
const selectedReport = ref(null)
const reportData = ref(null)
const dateRange = ref({
  start_date: new Date(new Date().setMonth(new Date().getMonth() - 12)).toISOString().split('T')[0],
  end_date: new Date().toISOString().split('T')[0],
})

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount || 0)
}

const getReportTitle = (report) => {
  const titles = {
    'sales-trends': 'Sales Trends Report',
    'expense-summary': 'Expense Summary Report',
    'inventory-usage': 'Inventory Usage Report',
    'financial-audit': 'Financial Audit Report',
  }
  return titles[report] || 'Report'
}

const viewReport = (reportType) => {
  selectedReport.value = reportType
  reportData.value = null
  if (reportType === 'inventory-usage') {
    loadReport()
  }
}

const loadReport = async () => {
  loading.value = true
  try {
    let response
    const params = selectedReport.value !== 'inventory-usage' ? dateRange.value : {}
    
    switch (selectedReport.value) {
      case 'sales-trends':
        response = await adminAPI.getSalesTrends(params)
        break
      case 'expense-summary':
        response = await adminAPI.getExpenseSummary(params)
        break
      case 'inventory-usage':
        response = await adminAPI.getInventoryUsage()
        break
      case 'financial-audit':
        response = await adminAPI.getFinancialAudit(params)
        break
    }
    reportData.value = response.data
  } catch (error) {
    console.error('Error loading report:', error)
    alert('Failed to load report')
  } finally {
    loading.value = false
  }
}

const exportReport = () => {
  if (!reportData.value) return
  
  // Simple CSV export
  let csv = ''
  if (selectedReport.value === 'sales-trends' && reportData.value.monthly_breakdown) {
    csv = 'Month,Count,Total Amount,Average Amount\n'
    Object.entries(reportData.value.monthly_breakdown).forEach(([month, data]) => {
      csv += `${month},${data.count},${data.total_amount},${data.average_amount}\n`
    })
  } else if (selectedReport.value === 'expense-summary' && reportData.value.category_breakdown) {
    csv = 'Category,Count,Total Amount,Average Amount\n'
    reportData.value.category_breakdown.forEach(cat => {
      csv += `${cat.category},${cat.count},${cat.total_amount},${cat.average_amount}\n`
    })
  }
  
  if (csv) {
    const blob = new Blob([csv], { type: 'text/csv' })
    const url = window.URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `${selectedReport.value}-${new Date().toISOString().split('T')[0]}.csv`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    window.URL.revokeObjectURL(url)
  }
}
</script>

<style scoped>
.admin-reports-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>
