<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-6xl mx-auto py-8 px-4">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">My Orders</h1>
        <p class="text-gray-600 mt-1">Track your rice product orders</p>
      </div>

      <!-- Status Tabs -->
      <div class="flex space-x-2 mb-6 overflow-x-auto pb-2">
        <button v-for="tab in tabs" :key="tab.value"
          @click="activeTab = tab.value"
          :class="[
            'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
            activeTab === tab.value 
              ? 'bg-green-600 text-white' 
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          ]"
        >
          {{ tab.label }}
          <span v-if="getOrderCount(tab.value) > 0" class="ml-1 px-2 py-0.5 rounded-full text-xs"
            :class="activeTab === tab.value ? 'bg-white/20' : 'bg-gray-300'"
          >{{ getOrderCount(tab.value) }}</span>
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
        <p class="text-gray-500 mt-4">Loading orders...</p>
      </div>

      <!-- Orders List -->
      <div v-else-if="filteredOrders.length > 0" class="space-y-4">
        <div v-for="order in filteredOrders" :key="order.id"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
        >
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="font-semibold text-gray-900">{{ order.rice_product?.name || 'Rice Product' }}</h3>
                <span :class="getStatusClass(order.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                  {{ formatStatus(order.status) }}
                </span>
              </div>
              <div class="text-sm text-gray-600 space-y-1">
                <p>Quantity: {{ order.quantity }} kg • ₱{{ Number(order.total_amount).toLocaleString() }}</p>
                <p>Seller: {{ order.rice_product?.farmer?.name || 'N/A' }}</p>
                <p>Ordered: {{ formatDate(order.order_date) }}</p>
              </div>
            </div>
            <div class="flex flex-col gap-2">
              <router-link :to="`/buyer/orders/${order.id}`"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 text-center"
              >View Details</router-link>
              <button v-if="order.status === 'shipped'"
                @click="confirmDelivery(order)"
                class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700"
              >Confirm Delivery</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12 bg-gray-50 rounded-xl">
        <div class="text-5xl mb-4">📦</div>
        <h3 class="text-lg font-medium text-gray-900">No orders found</h3>
        <p class="text-gray-500 mt-1">Start shopping to see your orders here</p>
        <router-link to="/buyer/products" class="inline-flex items-center px-4 py-2 mt-4 bg-green-600 text-white rounded-lg hover:bg-green-700">
          Browse Products
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useMarketplaceStore } from '@/stores/marketplace'

const marketplaceStore = useMarketplaceStore()
const loading = ref(true)
const orders = ref([])
const activeTab = ref('all')

const tabs = [
  { value: 'all', label: 'All Orders' },
  { value: 'pending', label: 'Pending' },
  { value: 'confirmed', label: 'Confirmed' },
  { value: 'shipped', label: 'Shipped' },
  { value: 'delivered', label: 'Delivered' },
]

const filteredOrders = computed(() => {
  if (activeTab.value === 'all') return orders.value
  return orders.value.filter(o => o.status === activeTab.value)
})

const getOrderCount = (status) => {
  if (status === 'all') return orders.value.length
  return orders.value.filter(o => o.status === status).length
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    shipped: 'bg-purple-100 text-purple-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    disputed: 'bg-orange-100 text-orange-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatStatus = (status) => status?.charAt(0).toUpperCase() + status?.slice(1)

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

const confirmDelivery = async (order) => {
  if (!confirm('Confirm that you have received this order?')) return
  try {
    await marketplaceStore.confirmOrderDelivery(order.id)
    order.status = 'delivered'
  } catch (err) {
    alert(err.message || 'Failed to confirm delivery')
  }
}

onMounted(async () => {
  try {
    const response = await marketplaceStore.fetchBuyerOrders()
    orders.value = response.orders || []
  } catch (err) {
    console.error('Failed to load orders', err)
  } finally {
    loading.value = false
  }
})
</script>
