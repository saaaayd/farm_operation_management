<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Standard Header -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Expenses</h1>
          <p class="text-gray-500 mt-1">Manage and track your farm's operating costs.</p>
        </div>
        <div class="flex items-center gap-3">
          <button
            @click="refreshExpenses"
            :disabled="loading"
            class="flex items-center gap-2 bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm font-medium disabled:opacity-50"
          >
            <svg
              :class="['h-5 w-5', { 'animate-spin': loading }]"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
          </button>
          <button
            @click="exportCsv"
            class="flex items-center gap-2 bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-sm font-medium"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export CSV
          </button>
          <button
            @click="goToReports"
            class="flex items-center gap-2 bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 transition-colors shadow-sm font-medium"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            View Financial Report
          </button>
        </div>
      </div>

       <!-- Tab Navigation -->
       <div class="mb-8">
        <nav class="flex space-x-4">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'px-4 py-2 rounded-full text-sm font-medium transition-colors duration-200 shadow-sm border',
              activeTab === tab.id
                ? 'bg-green-100 text-green-800 border-green-200'
                : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50 hover:text-gray-800'
            ]"
          >
            {{ tab.name }} <span class="ml-1 text-xs opacity-75">({{ tab.count }})</span>
          </button>
        </nav>
      </div>

      <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-700">{{ error }}</p>
            <button
              @click="refreshExpenses"
              class="mt-2 text-sm font-medium text-red-700 hover:text-red-800"
            >
              Try again
            </button>
          </div>
        </div>
      </div>

      <div v-else-if="loading" class="space-y-4">
        <div
          v-for="n in 3"
          :key="n"
          class="bg-white rounded-lg shadow p-6 animate-pulse space-y-3"
        >
          <div class="h-4 bg-gray-200 rounded w-2/3"></div>
          <div class="h-3 bg-gray-200 rounded w-1/2"></div>
          <div class="h-3 bg-gray-200 rounded"></div>
        </div>
      </div>

      <!-- Capital Expenses View -->
      <div v-else-if="activeTab === 'capital'" class="space-y-6">
        <!-- Analytics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-500">
            <p class="text-sm font-medium text-gray-500">Total Capital Expenses</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900">
              {{ formatCurrency(capitalTotal) }}
            </p>
          </div>
          <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-400">
            <p class="text-sm font-medium text-gray-500">Average Expense</p>
             <p class="mt-2 text-3xl font-semibold text-gray-900">
              {{ formatCurrency(capitalAverage) }}
            </p>
          </div>
          <div class="bg-white rounded-lg shadow p-6 border-l-4 border-blue-300">
            <p class="text-sm font-medium text-gray-500">Highest Spending Category</p>
            <p class="mt-2 text-lg font-semibold text-gray-900 truncate">
               {{ capitalTopCategory?.category ? formatCategory(capitalTopCategory.category) : '—' }}
            </p>
             <p v-if="capitalTopCategory" class="text-sm text-gray-500 mt-1">
              {{ formatCurrency(capitalTopCategory.total) }}
            </p>
          </div>
        </div>

        <!-- List -->
        <div v-if="capitalExpenses.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
            <div class="text-5xl mb-4">🚜</div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">No capital expenses</h2>
            <p class="text-sm text-gray-600">
              No equipment, infrastructure, or other non-labor expenses recorded.
            </p>
        </div>
        <div v-else class="bg-white rounded-lg shadow overflow-hidden">
             <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Capital Expense Records</h3>
                <span class="text-sm text-gray-500">{{ capitalExpenses.length }} records</span>
            </div>
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="expense in capitalExpenses" :key="expense.id" class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(expense.date) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                      <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                        {{ formatCategory(expense.category) }}
                      </span>
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600">{{ expense.description || '—' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">{{ formatCurrency(expense.amount) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatPaymentMethod(expense.payment_method) }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Labor Expenses View -->
      <div v-else-if="activeTab === 'labor'" class="space-y-6">
         <!-- Analytics Cards -->
         <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-500">
            <p class="text-sm font-medium text-gray-500">Total Labor Costs</p>
            <p class="mt-2 text-3xl font-semibold text-gray-900">
              {{ formatCurrency(laborTotal) }}
            </p>
          </div>
          <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-400">
            <p class="text-sm font-medium text-gray-500">Average Wage/Payment</p>
             <p class="mt-2 text-3xl font-semibold text-gray-900">
              {{ formatCurrency(laborAverage) }}
            </p>
          </div>
           <!-- Placeholder for "Top Laborer" or similar if data available, using top category for now -->
          <div class="bg-white rounded-lg shadow p-6 border-l-4 border-orange-300">
            <p class="text-sm font-medium text-gray-500">Processing Status</p>
            <div class="mt-2 flex items-center space-x-2">
                 <span class="text-lg font-semibold text-gray-900">Active</span>
                 <span class="px-2 py-1 text-xs rounded bg-green-100 text-green-800">Payroll Active</span>
            </div>
            <p class="text-sm text-gray-500 mt-1">
             {{ laborExpenses.length }} payments recorded
            </p>
          </div>
        </div>

        <!-- List -->
        <div v-if="laborExpenses.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
            <div class="text-5xl mb-4">👷</div>
            <h2 class="text-lg font-semibold text-gray-900 mb-2">No labor expenses</h2>
            <p class="text-sm text-gray-600">
              No worker wages or labor-related costs recorded yet.
            </p>
        </div>
        <div v-else class="bg-white rounded-lg shadow overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex justify-between items-center">
                <h3 class="text-lg font-medium text-gray-900">Labor Expense Records</h3>
                 <span class="text-sm text-gray-500">{{ laborExpenses.length }} records</span>
            </div>
            <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Description/Worker</th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                   <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment Method</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="expense in laborExpenses" :key="expense.id" class="hover:bg-gray-50 transition-colors">
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatDate(expense.date) }}</td>
                   <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                       <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                        Labor
                      </span>
                   </td>
                  <td class="px-6 py-4 text-sm text-gray-600">{{ expense.description || '—' }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">{{ formatCurrency(expense.amount) }}</td>
                   <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">{{ formatPaymentMethod(expense.payment_method) }}</td>
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
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useFarmStore } from '@/stores/farm'
import axios from 'axios'

const router = useRouter()
const farmStore = useFarmStore()

const loading = ref(true)
const error = ref('')
const activeTab = ref('capital') // Default to Capital to avoid "All" confusion

const expenses = computed(() => farmStore.expenses || [])

// Split expenses into two main buckets
const laborExpenses = computed(() => {
    return expenses.value.filter(exp => exp.category === 'labor' || exp.category === 'labor_wage')
})

// Capital here roughly means "Everything else" as per common small farm app usage
// unless strictly Equipment. Given the user context "Capital vs Labor", we group inputs into Capital/Operating.
const capitalExpenses = computed(() => {
    // Exclude labor
    return expenses.value.filter(exp => exp.category !== 'labor' && exp.category !== 'labor_wage')
})

const tabs = computed(() => [
  { id: 'capital', name: 'Capital Expenses', count: capitalExpenses.value.length },
  { id: 'labor', name: 'Labor Expenses', count: laborExpenses.value.length }
])

// --- Analytics Helpers ---
const calculateTotal = (items) => items.reduce((sum, item) => sum + (Number(item.amount) || 0), 0)
const calculateAverage = (items) => items.length ? calculateTotal(items) / items.length : 0

const calculateTopCategory = (items) => {
    if (!items.length) return null
    const summary = items.reduce((acc, expense) => {
        const category = expense.category || 'other'
        const amount = Number(expense.amount) || 0
        if (!acc[category]) {
            acc[category] = { category, total: 0, count: 0 }
        }
        acc[category].total += amount
        acc[category].count += 1
        return acc
    }, {})
     const sorted = Object.values(summary).sort((a, b) => b.total - a.total)
    return sorted.length > 0 ? sorted[0] : null
}

// Capital Analytics
const capitalTotal = computed(() => calculateTotal(capitalExpenses.value))
const capitalAverage = computed(() => calculateAverage(capitalExpenses.value))
const capitalTopCategory = computed(() => calculateTopCategory(capitalExpenses.value))

// Labor Analytics
const laborTotal = computed(() => calculateTotal(laborExpenses.value))
const laborAverage = computed(() => calculateAverage(laborExpenses.value))


const refreshExpenses = async () => {
  loading.value = true
  error.value = ''

  try {
    await farmStore.fetchExpenses({})
  } catch (err) {
    console.error('Failed to load expenses:', err)
    error.value = err.userMessage || err.response?.data?.message || 'Unable to load expenses.'
  } finally {
    loading.value = false
  }
}

const goToReports = () => {
  router.push('/reports')
}

const exportCsv = async () => {
  try {
    const response = await axios.get('/api/reports/export/expenses', {
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `expenses_${new Date().toISOString().split('T')[0]}.csv`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (err) {
    console.error('Failed to export:', err)
    alert('Failed to export CSV')
  }
}

const formatCurrency = (value) => {
  const num = Number(value)
  if (Number.isNaN(num)) return value ? `\u20B1${value}` : '\u20B10.00'
  return `\u20B1${num.toFixed(2)}`
}

const formatDate = (value) => {
  if (!value) return '—'
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString()
}

const formatCategory = (category) => {
  if (!category) return 'Other'
  const formatted = category.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase())
  
  if (category === 'inventory_purchase') return 'Inventory / Supplies'
  
  return formatted
}

const formatPaymentMethod = (method) => {
  if (!method) return 'Not specified'
  return method.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase())
}

onMounted(() => {
  if (!expenses.value.length) {
    refreshExpenses()
  } else {
    loading.value = false
  }
})
</script>

<!-- Forced HMR Update -->
