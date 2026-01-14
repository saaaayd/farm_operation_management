<template>
  <div class="min-h-screen bg-gray-50">
    <div class="w-full mx-auto py-8 px-4">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-2xl font-bold text-gray-900">Incoming Orders</h1>
        <p class="text-gray-600 mt-1">Manage orders from buyers</p>
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
      </div>

      <!-- Orders List -->
      <div v-else-if="filteredOrders.length > 0" class="space-y-4">
        <div v-for="order in filteredOrders" :key="order.id"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6"
        >
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="font-semibold text-gray-900">{{ order.rice_product?.name || 'Rice Product' }}</h3>
                <span :class="getStatusClass(order.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                  {{ formatStatus(order.status) }}
                </span>
                <span v-if="order.payment_status === 'paid'" class="px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800 ml-2">
                  Paid
                </span>
                <span v-else class="px-2 py-1 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 ml-2">
                  Unpaid
                </span>
              </div>
              <div class="text-sm text-gray-600 space-y-1">
                <p>{{ order.quantity }} kg • ₱{{ Number(order.total_amount).toLocaleString() }}</p>
                <p>Buyer: {{ order.buyer?.name || 'N/A' }}</p>
                <p>Ordered: {{ formatDate(order.order_date) }}</p>
              </div>
            </div>
            <div class="flex flex-wrap gap-2">
              <!-- Pending Actions -->
              <template v-if="order.status === 'pending'">
                <button @click="acceptOrder(order)" class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700">
                  Accept
                </button>
                <button @click="rejectOrder(order)" class="px-4 py-2 bg-red-100 text-red-700 rounded-lg text-sm font-medium hover:bg-red-200">
                  Reject
                </button>
              </template>
              <!-- Confirmed Actions -->
              <button v-if="order.status === 'confirmed'"
                @click="shipOrder(order)"
                class="px-4 py-2 bg-purple-600 text-white rounded-lg text-sm font-medium hover:bg-purple-700"
              >Mark as Shipped</button>
              
              <!-- Payment Action -->
              <button v-if="order.payment_status !== 'paid' && order.status !== 'cancelled'"
                @click="markAsPaid(order)"
                class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm font-medium hover:bg-green-700"
              >Mark as Paid</button>

              <!-- View Details -->
              <router-link :to="`/farmer/orders/${order.id}`"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200"
              >Details</router-link>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12 bg-gray-50 rounded-xl">
        <div class="text-5xl mb-4">📋</div>
        <h3 class="text-lg font-medium text-gray-900">No orders yet</h3>
        <p class="text-gray-500 mt-1">Orders from buyers will appear here</p>
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
const activeTab = ref('pending')

const tabs = [
  { value: 'all', label: 'All' },
  { value: 'pending', label: 'Pending' },
  { value: 'confirmed', label: 'Confirmed' },
  { value: 'shipped', label: 'Shipped' },
  { value: 'delivered', label: 'Completed' },
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
const formatDate = (date) => date ? new Date(date).toLocaleDateString('en-PH', { month: 'short', day: 'numeric' }) : 'N/A'

const acceptOrder = async (order) => {
  try {
    await marketplaceStore.acceptOrder(order.id)
    order.status = 'confirmed'
  } catch (err) {
    alert(err.message || 'Failed to accept order')
  }
}

const rejectOrder = async (order) => {
  const reason = prompt('Reason for rejection (optional):')
  try {
    await marketplaceStore.rejectOrder(order.id, reason)
    order.status = 'cancelled'
  } catch (err) {
    alert(err.message || 'Failed to reject order')
  }
}

const shipOrder = async (order) => {
  const tracking = prompt('Tracking number (optional):')
  try {
    await marketplaceStore.shipOrder(order.id, tracking)
    order.status = 'shipped'
  } catch (err) {
    alert(err.message || 'Failed to ship order')
  }
}

const markAsPaid = async (order) => {
  if (!confirm('Are you sure you want to mark this order as paid?')) return
  
  try {
    const response = await marketplaceStore.markAsPaid(order.id)
    order.payment_status = 'paid'
    // Optional: Update local order if response returns updated model
    if (response && response.order) {
        Object.assign(order, response.order)
    }
  } catch (err) {
    alert(err.message || 'Failed to mark as paid')
  }
}

onMounted(async () => {
  try {
    const response = await marketplaceStore.fetchFarmerOrders()
    orders.value = response.orders || []
  } catch (err) {
    console.error('Failed to load orders', err)
  } finally {
    loading.value = false
  }
})
</script>
