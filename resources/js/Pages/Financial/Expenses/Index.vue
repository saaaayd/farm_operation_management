<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Standard Header -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Expenses</h1>
          <p class="text-gray-500 mt-1">Review operating costs across seeds, inputs, labor, and farm improvements.</p>
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

      <div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <p class="text-sm font-medium text-gray-500">Total Expenses</p>
          <p class="mt-2 text-3xl font-semibold text-gray-900">
            {{ formatCurrency(totalExpenses) }}
          </p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <p class="text-sm font-medium text-gray-500">Average Expense</p>
          <p class="mt-2 text-3xl font-semibold text-gray-900">
            {{ formatCurrency(averageExpense) }}
          </p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <p class="text-sm font-medium text-gray-500">Most Frequent Category</p>
          <p class="mt-2 text-lg font-semibold text-gray-900">
            {{ topCategory?.category || '—' }}
          </p>
          <p v-if="topCategory" class="text-sm text-gray-500 mt-1">
            {{ topCategory.count }} expenses · {{ formatCurrency(topCategory.total) }}
          </p>
        </div>
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
          v-for="n in 6"
          :key="n"
          class="bg-white rounded-lg shadow p-6 animate-pulse space-y-3"
        >
          <div class="h-4 bg-gray-200 rounded w-2/3"></div>
          <div class="h-3 bg-gray-200 rounded w-1/2"></div>
          <div class="h-3 bg-gray-200 rounded"></div>
        </div>
      </div>

      <div v-else>
        <div v-if="expenses.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
          <div class="text-5xl mb-4">📊</div>
          <h2 class="text-lg font-semibold text-gray-900 mb-2">No expenses recorded yet</h2>
          <p class="text-sm text-gray-600">
            Log farm operating costs to monitor profitability and cash flow.
          </p>
        </div>
        <div v-else class="bg-white rounded-lg shadow overflow-hidden">
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
                <tr
                  v-for="expense in expenses"
                  :key="expense.id"
                  class="hover:bg-gray-50 transition-colors"
                >
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatDate(expense.date) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    {{ formatCategory(expense.category) }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-600">
                    {{ expense.description || '—' }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-right font-medium text-gray-900">
                    {{ formatCurrency(expense.amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                    {{ formatPaymentMethod(expense.payment_method) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
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

const router = useRouter()
const farmStore = useFarmStore()

const loading = ref(true)
const error = ref('')

const expenses = computed(() => farmStore.expenses || [])

const totalExpenses = computed(() =>
  expenses.value.reduce((sum, expense) => sum + (Number(expense.amount) || 0), 0)
)

const averageExpense = computed(() => {
  if (!expenses.value.length) return 0
  return totalExpenses.value / expenses.value.length
})

const topCategory = computed(() => {
  if (!expenses.value.length) return null
  const summary = expenses.value.reduce((acc, expense) => {
    const category = expense.category || 'other'
    const amount = Number(expense.amount) || 0
    if (!acc[category]) {
      acc[category] = { category, total: 0, count: 0 }
    }
    acc[category].total += amount
    acc[category].count += 1
    return acc
  }, {})

  return Object.values(summary).sort((a, b) => b.total - a.total)[0]
})

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
  if (Number.isNaN(num)) return value ? `₱${value}` : '₱0.00'
  return `₱${num.toFixed(2)}`
}

const formatDate = (value) => {
  if (!value) return '—'
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString()
}

const formatCategory = (category) => {
  if (!category) return 'Other'
  return category.replace(/_/g, ' ').replace(/\b\w/g, (l) => l.toUpperCase())
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


