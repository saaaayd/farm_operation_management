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
              @click="activeTab = 'purchases'"
              :class="activeTab === 'purchases' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
              class="py-4 px-1 border-b-2 font-medium text-sm"
            >
              My Purchases ({{ purchases.length }})
            </button>
            <button
              @click="activeTab = 'sales'"
              :class="activeTab === 'sales' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700'"
              class="py-4 px-1 border-b-2 font-medium text-sm"
            >
              My Sales ({{ sales.length }})
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

      <!-- Orders List -->
      <div class="space-y-6">
        <div
          v-for="order in filteredOrders"
          :key="order.id"
          class="bg-white rounded-lg shadow-md p-6"
        >
          <div class="flex items-start justify-between mb-4">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Order #{{ order.id }}</h3>
              <p class="text-gray-600 text-sm">{{ formatDate(order.date) }}</p>
            </div>
            <div class="text-right">
              <div class="text-lg font-bold text-gray-900">{{ formatCurrency(order.total) }}</div>
              <span
                :class="getStatusBadgeClass(order.status)"
                class="px-2 py-1 text-xs font-medium rounded-full"
              >
                {{ order.status }}
              </span>
            </div>
          </div>
          
          <div class="space-y-3">
            <div
              v-for="item in order.items"
              :key="item.id"
              class="flex items-center space-x-4 p-3 bg-gray-50 rounded-lg"
            >
              <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                <span class="text-gray-500">{{ item.icon }}</span>
              </div>
              <div class="flex-1">
                <h4 class="font-medium text-gray-900">{{ item.name }}</h4>
                <p class="text-sm text-gray-600">{{ item.seller_name }}</p>
              </div>
              <div class="text-right">
                <div class="font-medium">{{ formatCurrency(item.price) }}</div>
                <div class="text-sm text-gray-600">Qty: {{ item.quantity }}</div>
              </div>
            </div>
          </div>
          
          <div class="flex justify-between items-center mt-4 pt-4 border-t border-gray-200">
            <div class="text-sm text-gray-600">
              <div v-if="order.shipping_address">
                <span class="font-medium">Shipping to:</span> {{ order.shipping_address }}
              </div>
              <div v-if="order.tracking_number">
                <span class="font-medium">Tracking:</span> {{ order.tracking_number }}
              </div>
            </div>
            <div class="flex space-x-2">
              <button
                @click="viewOrder(order.id)"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
              >
                View Details
              </button>
              <button
                v-if="order.status === 'delivered'"
                @click="leaveReview(order.id)"
                class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
              >
                Leave Review
              </button>
              <button
                v-if="order.status === 'pending'"
                @click="cancelOrder(order.id)"
                class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 text-sm"
              >
                Cancel
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredOrders.length === 0" class="text-center py-12">
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
import { ref, computed } from 'vue'
import { useRouter } from 'vue-router'
import { formatCurrency } from '@/utils/format'

const router = useRouter()
const activeTab = ref('purchases')
const searchQuery = ref('')
const statusFilter = ref('')
const dateFilter = ref('')

const purchases = ref([
  {
    id: 'ORD-001',
    date: '2024-03-25T10:00:00Z',
    status: 'delivered',
    total: 450.00,
    shipping_address: '123 Farm Road, Springfield, IL 62701',
    tracking_number: 'TRK123456789',
    items: [
      {
        id: 1,
        name: 'Corn Seeds - Pioneer 1234',
        price: 180.00,
        quantity: 2,
        seller_name: 'AgriSupply Co.',
        icon: '🌱'
      },
      {
        id: 2,
        name: 'Nitrogen Fertilizer',
        price: 450.00,
        quantity: 1,
        seller_name: 'Farm Depot',
        icon: '🌿'
      }
    ]
  },
  {
    id: 'ORD-002',
    date: '2024-03-20T14:30:00Z',
    status: 'shipped',
    total: 89.99,
    shipping_address: '123 Farm Road, Springfield, IL 62701',
    tracking_number: 'TRK987654321',
    items: [
      {
        id: 3,
        name: 'Garden Tools Set',
        price: 89.99,
        quantity: 1,
        seller_name: 'Tool Master',
        icon: '🔧'
      }
    ]
  }
])

const sales = ref([
  {
    id: 'SALE-001',
    date: '2024-03-24T09:15:00Z',
    status: 'delivered',
    total: 360.00,
    shipping_address: '456 Farm Lane, Des Moines, IA 50301',
    tracking_number: 'TRK456789123',
    items: [
      {
        id: 4,
        name: 'Wheat Seeds - Premium Grade',
        price: 180.00,
        quantity: 2,
        seller_name: 'Your Farm',
        icon: '🌾'
      }
    ]
  }
])

const filteredOrders = computed(() => {
  const orders = activeTab.value === 'purchases' ? purchases.value : sales.value
  
  return orders.filter(order => {
    const matchesSearch = order.id.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         order.items.some(item => item.name.toLowerCase().includes(searchQuery.value.toLowerCase()))
    const matchesStatus = !statusFilter.value || order.status === statusFilter.value
    const matchesDate = !dateFilter.value || isWithinDateRange(order.date, dateFilter.value)
    
    return matchesSearch && matchesStatus && matchesDate
  })
})

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    shipped: 'bg-purple-100 text-purple-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const isWithinDateRange = (date, range) => {
  const orderDate = new Date(date)
  const now = new Date()
  
  switch (range) {
    case 'last7days':
      return orderDate >= new Date(now.getTime() - 7 * 24 * 60 * 60 * 1000)
    case 'last30days':
      return orderDate >= new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000)
    case 'last3months':
      return orderDate >= new Date(now.getTime() - 90 * 24 * 60 * 60 * 1000)
    default:
      return true
  }
}

const viewOrder = (orderId) => {
  router.push(`/orders/${orderId}`)
}

const leaveReview = (orderId) => {
  // Navigate to review page
  console.log('Leave review for order:', orderId)
}

const cancelOrder = (orderId) => {
  // Cancel order logic
  console.log('Cancel order:', orderId)
}

const viewMarketplace = () => {
  router.push('/marketplace')
}

const sellProduct = () => {
  // Navigate to sell product page
  console.log('Sell product')
}

const clearFilters = () => {
  searchQuery.value = ''
  statusFilter.value = ''
  dateFilter.value = ''
}
</script>

<style scoped>
.orders-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>