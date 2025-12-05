<template>
  <div class="orders-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Orders</h1>
          <p class="text-gray-600 mt-2">Track your purchase and sales history</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="viewMarketplace"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Browse Products
          </button>
          <button
            v-if="isFarmer"
            @click="sellProduct"
            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            Sell Product
          </button>
        </div>
      </div>

      <!-- Tabs -->
      <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="border-b border-gray-200">
          <nav class="flex space-x-8 px-6">
            <button
              v-if="showPurchasesTab"
              @click="activeTab = 'purchases'"
              :class="activeTab === 'purchases' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
              class="py-4 px-1 border-b-2 font-medium text-sm"
            >
              My Purchases ({{ purchasesCount }})
            </button>
            <button
              v-if="showSalesTab"
              @click="activeTab = 'sales'"
              :class="activeTab === 'sales' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
              class="py-4 px-1 border-b-2 font-medium text-sm"
            >
              My Sales ({{ salesCount }})
            </button>
          </nav>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search orders..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="statusFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Status</option>
              <option value="pending">Pending</option>
              <option value="processing">Processing</option>
              <option value="shipped">Shipped</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Date Range</label>
            <select
              v-model="dateFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Time</option>
              <option value="last7days">Last 7 days</option>
              <option value="last30days">Last 30 days</option>
              <option value="last3months">Last 3 months</option>
            </select>
          </div>
          <div class="flex items-end">
            <button
              @click="clearFilters"
              class="w-full bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500"
            >
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Orders Feedback -->
      <div v-if="ordersError" class="mb-6 rounded-lg bg-red-50 p-4 text-sm text-red-700">
        {{ ordersError }}
      </div>

      <div v-if="ordersLoading" class="space-y-4">
        <div v-for="n in 3" :key="n" class="animate-pulse rounded-lg bg-white p-6 shadow">
          <div class="h-4 w-1/3 bg-gray-200 rounded mb-3"></div>
          <div class="h-3 w-1/4 bg-gray-200 rounded mb-6"></div>
          <div class="space-y-3">
            <div v-for="m in 2" :key="m" class="h-16 bg-gray-100 rounded"></div>
          </div>
        </div>
      </div>

      <!-- Orders List -->
      <div v-else class="space-y-6">
        <div
          v-for="order in filteredOrders"
          :key="order.id"
          class="bg-white rounded-lg shadow-md p-6"
        >
          <div class="flex items-start justify-between mb-4">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Order #{{ order.id }}</h3>
              <p class="text-gray-600 text-sm">
                {{ formatDate(order.order_date || order.created_at) }}
              </p>
              <p class="text-xs text-gray-500">
                {{ order.is_pre_order ? 'Pre-order' : 'Standard order' }}
              </p>
            </div>
            <div class="text-right">
              <div class="text-lg font-bold text-gray-900">
                {{ formatCurrency(order.total_amount || order.total) }}
              </div>
              <span
                :class="getStatusBadgeClass(order.status)"
                class="px-2 py-1 text-xs font-medium rounded-full capitalize"
              >
                {{ order.status }}
              </span>
            </div>
          </div>
          
          <div class="space-y-3">
            <div class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg">
              <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                <span class="text-2xl">🌾</span>
              </div>
              <div class="flex-1">
                <h4 class="font-medium text-gray-900">
                  {{ order.rice_product?.name || 'Rice product' }}
                </h4>
                <p class="text-sm text-gray-600">
                  Qty: {{ order.quantity }} {{ order.rice_product?.unit || 'sacks' }}
                </p>
              </div>
              <div class="text-right text-sm text-gray-600">
                <div class="font-medium">
                  {{ formatCurrency((order.unit_price || order.price_per_unit) * order.quantity) }}
                </div>
                <div>{{ order.delivery_method || 'Pickup' }}</div>
              </div>
            </div>

            <div v-if="order.is_pre_order" class="text-sm text-yellow-700">
              Available {{ order.available_date ? formatDate(order.available_date) : 'soon' }}
            </div>
            <div v-if="order.delivery_address" class="text-sm text-gray-600">
              <span class="font-medium">Deliver to:</span>
              {{ order.delivery_address.street || 'Customer provided address' }}
            </div>
          </div>
          
          <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-200">
            <div class="text-xs text-gray-500">
              <div v-if="order.tracking_number">
                <span class="font-medium">Tracking:</span> {{ order.tracking_number }}
              </div>
              <div v-if="order.progress_percentage">
                Progress: {{ order.progress_percentage }}%
              </div>
            </div>
            <div class="flex space-x-2">
              <button
                @click="viewOrder(order.id)"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
              >
                View Details
              </button>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="!ordersLoading && pagination"
        class="mt-6 flex items-center justify-between rounded-lg bg-white px-4 py-3 text-sm text-gray-600 shadow"
      >
        <button
          @click="handlePageChange('prev')"
          :disabled="pagination.current_page === 1"
          class="rounded-md border border-gray-300 px-3 py-1 disabled:cursor-not-allowed disabled:opacity-50"
        >
          Previous
        </button>
        <span>{{ paginationSummary }}</span>
        <button
          @click="handlePageChange('next')"
          :disabled="pagination.current_page === pagination.last_page"
          class="rounded-md border border-gray-300 px-3 py-1 disabled:cursor-not-allowed disabled:opacity-50"
        >
          Next
        </button>
      </div>

      <!-- Empty State -->
      <div v-if="!ordersLoading && filteredOrders.length === 0" class="text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">📦</div>
        <h3 class="text-xl font-medium text-gray-900 mb-2">No orders found</h3>
        <p class="text-gray-600 mb-6">You haven't made any {{ activeTab }} yet</p>
        <button
          @click="viewMarketplace"
          class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          Browse Products
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import { formatCurrency } from '@/utils/format'
import { useMarketplaceStore } from '@/stores/marketplace'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const marketplaceStore = useMarketplaceStore()
const authStore = useAuthStore()

const isBuyer = computed(() => authStore.user?.role === 'buyer')
const isFarmer = computed(() => authStore.user?.role === 'farmer')

const showPurchasesTab = computed(() => isBuyer.value)
const showSalesTab = computed(() => isFarmer.value)

const activeTab = ref(showPurchasesTab.value ? 'purchases' : 'sales')
const searchQuery = ref('')
const statusFilter = ref('')
const dateFilter = ref('')
const currentPage = ref(1)
const ordersError = ref('')

const ordersLoading = computed(() => marketplaceStore.loading)
const orders = computed(() => marketplaceStore.orders || [])
const pagination = computed(() => marketplaceStore.ordersPagination || null)

const purchasesCount = computed(() =>
  showPurchasesTab.value ? pagination.value?.total ?? orders.value.length : 0
)
const salesCount = computed(() =>
  showSalesTab.value ? pagination.value?.total ?? orders.value.length : 0
)

watch(
  () => [showPurchasesTab.value, showSalesTab.value],
  ([purchases, sales]) => {
    if (!purchases && activeTab.value === 'purchases' && sales) {
      activeTab.value = 'sales'
    } else if (!sales && activeTab.value === 'sales' && purchases) {
      activeTab.value = 'purchases'
    }
  }
)

const resolveDateRange = () => {
  if (!dateFilter.value) {
    return {}
  }

  const today = new Date()
  let fromDate = null

  switch (dateFilter.value) {
    case 'last7days':
      fromDate = new Date()
      fromDate.setDate(today.getDate() - 7)
      break
    case 'last30days':
      fromDate = new Date()
      fromDate.setDate(today.getDate() - 30)
      break
    case 'last3months':
      fromDate = new Date()
      fromDate.setMonth(today.getMonth() - 3)
      break
    default:
      return {}
  }

  const toDateString = today.toISOString().split('T')[0]
  const fromDateString = fromDate ? fromDate.toISOString().split('T')[0] : null

  return {
    from_date: fromDateString,
    to_date: fromDate ? toDateString : null,
  }
}

const loadOrders = async () => {
  ordersError.value = ''
  try {
    const params = {
      page: currentPage.value,
    }

    if (statusFilter.value) {
      params.status = statusFilter.value
    }

    const range = resolveDateRange()
    if (range.from_date) params.from_date = range.from_date
    if (range.to_date) params.to_date = range.to_date

    await marketplaceStore.fetchOrders(params)
  } catch (error) {
    ordersError.value = error.userMessage || error.response?.data?.message || 'Failed to load orders'
  }
}

const filteredOrders = computed(() => {
  if (
    (activeTab.value === 'purchases' && !showPurchasesTab.value) ||
    (activeTab.value === 'sales' && !showSalesTab.value)
  ) {
    return []
  }

  if (!searchQuery.value) {
    return orders.value
  }

  const search = searchQuery.value.toLowerCase()
  return orders.value.filter(order => {
    const orderIdMatch = order.id?.toString().toLowerCase().includes(search)
    const productMatch = order.rice_product?.name?.toLowerCase().includes(search)
    return orderIdMatch || productMatch
  })
})

const paginationSummary = computed(() => {
  if (!pagination.value) return ''
  return `Page ${pagination.value.current_page} of ${pagination.value.last_page}`
})

const handlePageChange = (direction) => {
  if (!pagination.value) return

  const targetPage =
    direction === 'next'
      ? pagination.value.current_page + 1
      : pagination.value.current_page - 1

  if (targetPage < 1 || targetPage > pagination.value.last_page) {
    return
  }

  currentPage.value = targetPage
  loadOrders()
}

watch([statusFilter, dateFilter], () => {
  currentPage.value = 1
  loadOrders()
})

const clearFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  dateFilter.value = ''
  currentPage.value = 1
  loadOrders()
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    processing: 'bg-purple-100 text-purple-800',
    shipped: 'bg-indigo-100 text-indigo-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const viewOrder = (orderId) => {
  const path = isFarmer.value ? `/marketplace/orders/${orderId}` : `/orders/${orderId}`
  router.push(path)
}

const viewMarketplace = () => {
  router.push('/marketplace')
}

const sellProduct = () => {
  router.push('/marketplace/product/create')
}

onMounted(() => {
  loadOrders()
})
</script>

<style scoped>
.orders-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>