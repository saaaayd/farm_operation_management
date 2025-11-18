<template>
  <div class="order-detail-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <nav class="text-sm text-gray-500 mb-2">
            <router-link to="/orders" class="hover:text-gray-700">Orders</router-link>
            <span class="mx-2">/</span>
            <span class="text-gray-900">Order #{{ order.id }}</span>
          </nav>
          <h1 class="text-3xl font-bold text-gray-900">Order #{{ order.id }}</h1>
          <p class="text-gray-600 mt-2">Placed on {{ formatDate(order.date) }}</p>
        </div>
        <div class="flex space-x-3">
          <button
            v-if="order.status === 'delivered'"
            @click="leaveReview"
            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            Leave Review
          </button>
          <button
            v-if="order.status === 'pending'"
            @click="cancelOrder"
            class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
          >
            Cancel Order
          </button>
          <button
            @click="printOrder"
            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Print Order
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Order Status -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Order Status</h2>
            <div class="flex items-center justify-between mb-4">
              <span
                :class="getStatusBadgeClass(order.status)"
                class="px-3 py-1 text-sm font-medium rounded-full"
              >
                {{ order.status }}
              </span>
              <span class="text-sm text-gray-600">Last updated: {{ formatDate(order.updated_at) }}</span>
            </div>
            
            <!-- Progress Steps -->
            <div class="flex items-center justify-between">
              <div
                v-for="(step, index) in orderSteps"
                :key="step"
                class="flex flex-col items-center"
              >
                <div
                  :class="getStepClass(index)"
                  class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-medium mb-2"
                >
                  {{ index + 1 }}
                </div>
                <span class="text-xs text-gray-600 text-center">{{ step }}</span>
              </div>
            </div>
          </div>

          <!-- Order Items -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Order Items</h2>
            <div class="space-y-4">
              <div
                v-for="item in order.items"
                :key="item.id"
                class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg"
              >
                <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                  <span class="text-gray-500 text-2xl">{{ item.icon }}</span>
                </div>
                <div class="flex-1">
                  <h3 class="font-medium text-gray-900">{{ item.name }}</h3>
                  <p class="text-sm text-gray-600">{{ item.description }}</p>
                  <div class="flex items-center space-x-4 mt-2 text-sm text-gray-600">
                    <span>Sold by: {{ item.seller_name }}</span>
                    <span>•</span>
                    <span>{{ item.location }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <div class="font-medium">{{ formatCurrency(item.price) }}</div>
                  <div class="text-sm text-gray-600">Qty: {{ item.quantity }}</div>
                  <div class="font-medium text-green-600">{{ formatCurrency(item.price * item.quantity) }}</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Shipping Information -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Shipping Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h3 class="font-medium text-gray-900 mb-2">Shipping Address</h3>
                <div class="text-gray-600">
                  <div>{{ order.shipping_address.name }}</div>
                  <div>{{ order.shipping_address.street }}</div>
                  <div>{{ order.shipping_address.city }}, {{ order.shipping_address.state }} {{ order.shipping_address.zip }}</div>
                  <div>{{ order.shipping_address.country }}</div>
                </div>
              </div>
              <div>
                <h3 class="font-medium text-gray-900 mb-2">Shipping Details</h3>
                <div class="space-y-2 text-gray-600">
                  <div class="flex justify-between">
                    <span>Method:</span>
                    <span>{{ order.shipping_method }}</span>
                  </div>
                  <div class="flex justify-between">
                    <span>Cost:</span>
                    <span>{{ formatCurrency(order.shipping_cost) }}</span>
                  </div>
                  <div v-if="order.tracking_number" class="flex justify-between">
                    <span>Tracking:</span>
                    <span class="font-mono">{{ order.tracking_number }}</span>
                  </div>
                  <div v-if="order.estimated_delivery" class="flex justify-between">
                    <span>Est. Delivery:</span>
                    <span>{{ formatDate(order.estimated_delivery) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Order Timeline -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Order Timeline</h2>
            <div class="space-y-4">
              <div
                v-for="event in order.timeline"
                :key="event.id"
                class="flex items-start space-x-3"
              >
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 text-sm">{{ getTimelineIcon(event.type) }}</span>
                  </div>
                </div>
                <div class="flex-1">
                  <div class="font-medium text-gray-900">{{ event.title }}</div>
                  <div class="text-sm text-gray-600">{{ event.description }}</div>
                  <div class="text-xs text-gray-500 mt-1">{{ formatDateTime(event.date) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Order Summary -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Order Summary</h3>
              <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Subtotal:</span>
                <span class="font-medium">{{ formatCurrency(order.subtotal) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Shipping:</span>
                <span class="font-medium">{{ formatCurrency(order.shipping_cost) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Tax:</span>
                <span class="font-medium">{{ formatCurrency(order.tax) }}</span>
              </div>
              <div class="border-t border-gray-200 pt-3">
                <div class="flex justify-between text-lg font-bold">
                  <span>Total:</span>
                  <span>{{ formatCurrency(order.total) }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Payment Information -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Payment Information</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Payment Method:</span>
                <span class="font-medium">{{ order.payment_method }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Payment Status:</span>
                <span
                  :class="order.payment_status === 'paid' ? 'text-green-600' : 'text-red-600'"
                  class="font-medium"
                >
                  {{ order.payment_status }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Transaction ID:</span>
                <span class="font-mono text-sm">{{ order.transaction_id }}</span>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
            <div class="space-y-3">
              <button
                @click="trackPackage"
                v-if="order.tracking_number"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                📦 Track Package
              </button>
              <button
                @click="contactSeller"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                💬 Contact Seller
              </button>
              <button
                @click="reorderItems"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                🔄 Reorder Items
              </button>
              <button
                @click="downloadInvoice"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                📄 Download Invoice
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { formatCurrency } from '@/utils/format'

const route = useRoute()
const router = useRouter()

const order = ref({
  id: null,
  date: '',
  status: '',
  updated_at: '',
  total: 0,
  subtotal: 0,
  shipping_cost: 0,
  tax: 0,
  shipping_address: {},
  shipping_method: '',
  tracking_number: '',
  estimated_delivery: '',
  payment_method: '',
  payment_status: '',
  transaction_id: '',
  items: [],
  timeline: []
})

const orderSteps = ['Ordered', 'Processing', 'Shipped', 'Delivered']

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

const getStepClass = (index) => {
  const currentStep = getCurrentStepIndex()
  if (index < currentStep) return 'bg-green-600 text-white'
  if (index === currentStep) return 'bg-blue-600 text-white'
  return 'bg-gray-200 text-gray-600'
}

const getCurrentStepIndex = () => {
  const statusMap = {
    pending: 0,
    processing: 1,
    shipped: 2,
    delivered: 3,
    cancelled: -1
  }
  return statusMap[order.value.status] || 0
}

const getTimelineIcon = (type) => {
  const icons = {
    ordered: '📝',
    processing: '⚙️',
    shipped: '🚚',
    delivered: '✅',
    cancelled: '❌'
  }
  return icons[type] || '📅'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const formatDateTime = (date) => {
  return new Date(date).toLocaleString()
}

const leaveReview = () => {
  // Navigate to review page
  console.log('Leave review')
}

const cancelOrder = () => {
  // Cancel order logic
  console.log('Cancel order')
}

const printOrder = () => {
  // Print order logic
  window.print()
}

const trackPackage = () => {
  // Open tracking page
  console.log('Track package')
}

const contactSeller = () => {
  // Contact seller logic
  console.log('Contact seller')
}

const reorderItems = () => {
  // Reorder items logic
  console.log('Reorder items')
}

const downloadInvoice = () => {
  // Download invoice logic
  console.log('Download invoice')
}

onMounted(() => {
  const orderId = route.params.id
  // Load order data from API
  loadOrderData(orderId)
})

const loadOrderData = async (id) => {
  try {
    // API call to load order data
    // For now, using mock data
    order.value = {
      id: id,
      date: '2024-03-25T10:00:00Z',
      status: 'delivered',
      updated_at: '2024-03-27T14:30:00Z',
      total: 450.00,
      subtotal: 420.00,
      shipping_cost: 15.00,
      tax: 15.00,
      shipping_address: {
        name: 'John Smith',
        street: '123 Farm Road',
        city: 'Springfield',
        state: 'IL',
        zip: '62701',
        country: 'United States'
      },
      shipping_method: 'Standard Shipping',
      tracking_number: 'TRK123456789',
      estimated_delivery: '2024-03-27T18:00:00Z',
      payment_method: 'Credit Card ending in 1234',
      payment_status: 'paid',
      transaction_id: 'TXN789012345',
      items: [
        {
          id: 1,
          name: 'Corn Seeds - Pioneer 1234',
          description: 'High-yield corn seeds perfect for spring planting',
          price: 180.00,
          quantity: 2,
          seller_name: 'AgriSupply Co.',
          location: 'Springfield, IL',
          icon: '🌱'
        },
        {
          id: 2,
          name: 'Nitrogen Fertilizer',
          description: 'High-grade nitrogen fertilizer for optimal crop growth',
          price: 450.00,
          quantity: 1,
          seller_name: 'Farm Depot',
          location: 'Des Moines, IA',
          icon: '🌿'
        }
      ],
      timeline: [
        {
          id: 1,
          type: 'ordered',
          title: 'Order Placed',
          description: 'Your order has been placed successfully',
          date: '2024-03-25T10:00:00Z'
        },
        {
          id: 2,
          type: 'processing',
          title: 'Order Processing',
          description: 'Your order is being prepared for shipment',
          date: '2024-03-25T14:30:00Z'
        },
        {
          id: 3,
          type: 'shipped',
          title: 'Order Shipped',
          description: 'Your order has been shipped',
          date: '2024-03-26T09:15:00Z'
        },
        {
          id: 4,
          type: 'delivered',
          title: 'Order Delivered',
          description: 'Your order has been delivered',
          date: '2024-03-27T14:30:00Z'
        }
      ]
    }
  } catch (error) {
    console.error('Error loading order data:', error)
  }
}
</script>

<style scoped>
.order-detail-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>