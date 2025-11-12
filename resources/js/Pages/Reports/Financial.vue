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
            <span class="text-gray-500">Revenue chart placeholder</span>
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
import { ref, onMounted } from 'vue'
import { formatCurrency } from '@/utils/format'

const startDate = ref('2024-01-01')
const endDate = ref('2024-12-31')
const reportType = ref('income')

const financialSummary = ref({
  totalRevenue: 125000,
  totalExpenses: 85000,
  netProfit: 40000,
  profitMargin: 32
})

const expenseBreakdown = ref([
  { category: 'Seeds & Fertilizer', amount: 25000, percentage: 29, color: '#3B82F6' },
  { category: 'Equipment', amount: 20000, percentage: 24, color: '#10B981' },
  { category: 'Labor', amount: 15000, percentage: 18, color: '#F59E0B' },
  { category: 'Fuel & Utilities', amount: 10000, percentage: 12, color: '#EF4444' },
  { category: 'Insurance', amount: 8000, percentage: 9, color: '#8B5CF6' },
  { category: 'Other', amount: 7000, percentage: 8, color: '#6B7280' }
])

const transactions = ref([
  {
    id: 1,
    date: '2024-03-25',
    description: 'Corn harvest sale',
    category: 'Crop Sales',
    type: 'income',
    amount: 15000
  },
  {
    id: 2,
    date: '2024-03-24',
    description: 'Fertilizer purchase',
    category: 'Seeds & Fertilizer',
    type: 'expense',
    amount: 2500
  },
  {
    id: 3,
    date: '2024-03-23',
    description: 'Equipment maintenance',
    category: 'Equipment',
    type: 'expense',
    amount: 800
  },
  {
    id: 4,
    date: '2024-03-22',
    description: 'Wheat sale',
    category: 'Crop Sales',
    type: 'income',
    amount: 8500
  },
  {
    id: 5,
    date: '2024-03-21',
    description: 'Fuel purchase',
    category: 'Fuel & Utilities',
    type: 'expense',
    amount: 450
  }
])

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const updateReport = () => {
  // Update report based on selected parameters
  console.log('Update report:', { startDate: startDate.value, endDate: endDate.value, reportType: reportType.value })
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
  // Load financial data from API
})
</script>

<style scoped>
.financial-reports-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>