<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Sales Records</h1>
          <p class="text-gray-600 mt-1">Track off-platform and direct sales of your harvest</p>
        </div>
        <button
          @click="openSaleModal"
          class="flex items-center gap-2 bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 transition-colors shadow-sm font-medium"
        >
          <span class="text-xl leading-none">+</span> Record Sale
        </button>
      </div>

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
              <span class="text-green-600 text-2xl">💰</span>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">₱{{ formatNumber(summary.total_sales) }}</div>
              <div class="text-sm text-gray-600">Total Revenue</div>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
              <span class="text-blue-600 text-2xl">📦</span>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ formatNumber(summary.total_quantity) }} kg</div>
              <div class="text-sm text-gray-600">Total Quantity Sold</div>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
              <span class="text-purple-600 text-2xl">📊</span>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ summary.sales_count }}</div>
              <div class="text-sm text-gray-600">Total Transactions</div>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center">
            <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
              <span class="text-yellow-600 text-2xl">📈</span>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">₱{{ formatNumber(summary.average_sale_amount) }}</div>
              <div class="text-sm text-gray-600">Average Sale</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
            <input
              v-model="filters.date_from"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
            <input
              v-model="filters.date_to"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status</label>
            <select v-model="filters.status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-green-500 focus:border-green-500">
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="paid">Paid</option>
              <option value="partial">Partial</option>
              <option value="overdue">Overdue</option>
            </select>
          </div>
          <div class="flex items-end">
            <button @click="fetchSales" class="w-full bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700">
              Apply Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="bg-white rounded-lg shadow p-12 text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto mb-4"></div>
        <p class="text-gray-600">Loading sales...</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="sales.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <span class="text-4xl">💵</span>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No sales recorded yet</h3>
        <p class="text-gray-600 mb-6">Start recording your harvest sales to track your revenue.</p>
        <button
          @click="openSaleModal"
          class="bg-green-600 text-white px-6 py-2 rounded-lg hover:bg-green-700"
        >
          Record Your First Sale
        </button>
      </div>

      <!-- Sales List -->
      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Source</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harvest / Crop</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Unit Price</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Payment</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="sale in sales" :key="sale.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatDate(sale.sale_date) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="sale.rice_order_id ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800'"
                >
                  {{ sale.rice_order_id ? '🛒 Marketplace' : '📝 Manual' }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-medium text-gray-900">
                  {{ sale.harvest?.planting?.crop_type || 'Rice' }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ sale.harvest?.planting?.field?.name || 'Unknown Field' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ formatNumber(sale.quantity) }} kg
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                ₱{{ formatNumber(sale.unit_price) }}/kg
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-green-600">
                ₱{{ formatNumber(sale.total_amount) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="px-2 py-1 text-xs font-medium rounded-full"
                  :class="paymentStatusClass(sale.payment_status)"
                >
                  {{ formatPaymentStatus(sale.payment_status) }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm">
                <button
                  @click="viewSale(sale)"
                  class="text-blue-600 hover:text-blue-800 mr-3"
                >
                  View
                </button>
                <button
                  v-if="!sale.rice_order_id"
                  @click="deleteSale(sale.id)"
                  class="text-red-600 hover:text-red-800"
                >
                  Delete
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Record Sale Modal -->
      <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">Record New Sale</h3>
          </div>
          <form @submit.prevent="submitSale" class="p-6 space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Harvest *</label>
              <select v-model="form.harvest_id" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                <option value="">Select a harvest</option>
                <option v-for="harvest in harvests" :key="harvest.id" :value="harvest.id">
                  {{ harvest.planting?.crop_type || 'Rice' }} - {{ harvest.quantity }} kg ({{ formatDate(harvest.harvest_date) }})
                </option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantity (kg) *</label>
                <input v-model.number="form.quantity" type="number" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-md" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit Price (₱/kg) *</label>
                <input v-model.number="form.unit_price" type="number" step="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-md" />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Total Amount</label>
              <div class="px-3 py-2 bg-gray-100 border border-gray-300 rounded-md">
                ₱{{ formatNumber(computedTotal) }}
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Sale Date *</label>
              <input v-model="form.sale_date" type="date" required class="w-full px-3 py-2 border border-gray-300 rounded-md" />
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method *</label>
                <select v-model="form.payment_method" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                  <option value="cash">Cash</option>
                  <option value="bank_transfer">Bank Transfer</option>
                  <option value="check">Check</option>
                  <option value="credit">Credit</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Payment Status *</label>
                <select v-model="form.payment_status" required class="w-full px-3 py-2 border border-gray-300 rounded-md">
                  <option value="paid">Paid</option>
                  <option value="pending">Pending</option>
                  <option value="partial">Partial</option>
                </select>
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Buyer Name (Optional)</label>
              <input v-model="form.buyer_name" type="text" placeholder="Enter buyer name" class="w-full px-3 py-2 border border-gray-300 rounded-md" />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
              <textarea v-model="form.notes" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-md"></textarea>
            </div>
            <div class="flex justify-end gap-3 pt-4 border-t">
              <button type="button" @click="showModal = false" class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50">
                Cancel
              </button>
              <button type="submit" :disabled="submitting" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50">
                {{ submitting ? 'Saving...' : 'Save Sale' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import axios from 'axios'

const loading = ref(true)
const submitting = ref(false)
const showModal = ref(false)
const sales = ref([])
const harvests = ref([])
const summary = ref({
  total_sales: 0,
  total_quantity: 0,
  sales_count: 0,
  average_sale_amount: 0
})

const filters = ref({
  date_from: '',
  date_to: '',
  status: ''
})

const form = ref({
  harvest_id: '',
  quantity: 0,
  unit_price: 0,
  sale_date: new Date().toISOString().split('T')[0],
  payment_method: 'cash',
  payment_status: 'paid',
  buyer_name: '',
  notes: ''
})

const computedTotal = computed(() => {
  return (form.value.quantity || 0) * (form.value.unit_price || 0)
})

const formatNumber = (value) => {
  if (!value) return '0'
  return Number(value).toLocaleString(undefined, { maximumFractionDigits: 2 })
}

const formatDate = (value) => {
  if (!value) return 'N/A'
  return new Date(value).toLocaleDateString()
}

const formatPaymentStatus = (status) => {
  const labels = {
    pending: 'Pending',
    paid: 'Paid',
    partial: 'Partial',
    overdue: 'Overdue'
  }
  return labels[status] || status
}

const paymentStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
    partial: 'bg-blue-100 text-blue-800',
    overdue: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const fetchSales = async () => {
  loading.value = true
  try {
    const params = {}
    if (filters.value.date_from) params.date_from = filters.value.date_from
    if (filters.value.date_to) params.date_to = filters.value.date_to
    if (filters.value.status) params.status = filters.value.status
    
    const [salesRes, summaryRes] = await Promise.all([
      axios.get('/api/sales', { params }),
      axios.get('/api/sales/summary', { params })
    ])
    
    sales.value = salesRes.data.sales || []
    summary.value = summaryRes.data.summary || summary.value
  } catch (error) {
    console.error('Failed to fetch sales:', error)
  } finally {
    loading.value = false
  }
}

const fetchHarvests = async () => {
  try {
    const response = await axios.get('/api/harvests')
    harvests.value = response.data.harvests || response.data || []
  } catch (error) {
    console.error('Failed to fetch harvests:', error)
  }
}

const openSaleModal = () => {
  form.value = {
    harvest_id: '',
    quantity: 0,
    unit_price: 0,
    sale_date: new Date().toISOString().split('T')[0],
    payment_method: 'cash',
    payment_status: 'paid',
    buyer_name: '',
    notes: ''
  }
  fetchHarvests()
  showModal.value = true
}

const submitSale = async () => {
  submitting.value = true
  try {
    await axios.post('/api/sales', {
      harvest_id: form.value.harvest_id,
      buyer_id: 1, // Placeholder - ideally would create/select buyer
      quantity: form.value.quantity,
      unit_price: form.value.unit_price,
      total_amount: computedTotal.value,
      sale_date: form.value.sale_date,
      payment_method: form.value.payment_method,
      payment_status: form.value.payment_status,
      notes: form.value.notes
    })
    showModal.value = false
    await fetchSales()
    alert('Sale recorded successfully!')
  } catch (error) {
    console.error('Failed to create sale:', error)
    alert(error.response?.data?.message || 'Failed to record sale')
  } finally {
    submitting.value = false
  }
}

const viewSale = (sale) => {
  alert(`Sale ID: ${sale.id}\nTotal: ₱${sale.total_amount}\nDate: ${formatDate(sale.sale_date)}`)
}

const deleteSale = async (id) => {
  if (!confirm('Are you sure you want to delete this sale?')) return
  try {
    await axios.delete(`/api/sales/${id}`)
    await fetchSales()
    alert('Sale deleted successfully')
  } catch (error) {
    console.error('Failed to delete sale:', error)
    alert('Failed to delete sale')
  }
}

onMounted(() => {
  fetchSales()
})
</script>
