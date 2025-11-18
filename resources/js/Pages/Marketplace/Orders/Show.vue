<template>
  <div class="order-detail-page">
    <div class="container mx-auto px-4 py-8">
      <div v-if="loading" class="py-24 text-center text-gray-500">
        <div class="mx-auto mb-4 h-10 w-10 animate-spin rounded-full border-b-2 border-green-600"></div>
        Loading order details...
      </div>

      <div v-else-if="error" class="mx-auto max-w-2xl rounded-lg bg-white p-6 text-center shadow">
        <h1 class="text-xl font-semibold text-gray-900 mb-2">Unable to load order</h1>
        <p class="text-gray-600 mb-6">{{ error }}</p>
        <button
          @click="router.push(ordersListRoute)"
          class="rounded-md bg-green-600 px-4 py-2 text-white hover:bg-green-700"
        >
          Back to Orders
        </button>
      </div>

      <div v-else-if="order" class="space-y-8">
        <!-- Header -->
        <div class="flex justify-between items-center">
        <div>
          <nav class="text-sm text-gray-500 mb-2">
              <router-link :to="ordersListRoute" class="hover:text-gray-700">Orders</router-link>
            <span class="mx-2">/</span>
            <span class="text-gray-900">Order #{{ order.id }}</span>
          </nav>
          <h1 class="text-3xl font-bold text-gray-900">Order #{{ order.id }}</h1>
            <p class="text-gray-600 mt-2">
              Placed on {{ formatDate(order.order_date || order.created_at) }}
            </p>
        </div>
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
              <span class="text-sm text-gray-600">
                Last updated: {{ formatDate(order.updated_at) }}
              </span>
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
                <span class="text-xs text-gray-600 text-center">
                  {{ step.charAt(0).toUpperCase() + step.slice(1) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Order Items -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Order Items</h2>
            <div class="space-y-4">
              <div
                v-for="item in lineItems"
                :key="item.id"
                class="flex items-center space-x-4 p-4 border border-gray-200 rounded-lg"
              >
                <div class="w-16 h-16 bg-gray-200 rounded-lg flex items-center justify-center">
                  <span class="text-gray-500 text-2xl">🌾</span>
                </div>
                <div class="flex-1">
                  <h3 class="font-medium text-gray-900">{{ item.name }}</h3>
                  <p class="text-sm text-gray-600">{{ item.description }}</p>
                  <div class="flex items-center space-x-4 mt-2 text-sm text-gray-600">
                    <span>Farmer: {{ item.farmer || 'Pending assignment' }}</span>
                    <span v-if="item.location">•</span>
                    <span v-if="item.location">{{ item.location }}</span>
                  </div>
                </div>
                <div class="text-right">
                  <div class="font-medium">{{ formatCurrency(item.price) }}</div>
                  <div class="text-sm text-gray-600">Qty: {{ item.quantity }}</div>
                  <div class="font-medium text-green-600">
                    {{ formatCurrency(item.price * item.quantity) }}
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Shipping Information -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Shipping Information</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <h3 class="font-medium text-gray-900 mb-2">Delivery Address</h3>
                <div class="text-gray-600 space-y-1">
                  <div>{{ deliveryAddress.street || 'Provided during checkout' }}</div>
                  <div v-if="deliveryAddress.city">
                    {{ deliveryAddress.city }} {{ deliveryAddress.state ? ', ' + deliveryAddress.state : '' }}
                    {{ deliveryAddress.postal_code }}
                  </div>
                  <div v-if="deliveryAddress.country">{{ deliveryAddress.country }}</div>
                </div>
              </div>
              <div>
                <h3 class="font-medium text-gray-900 mb-2">Shipping Details</h3>
                <div class="space-y-2 text-gray-600">
                  <div class="flex justify-between">
                    <span>Method:</span>
                    <span>{{ order.delivery_method || 'Pickup' }}</span>
                  </div>
                  <div v-if="order.tracking_number" class="flex justify-between">
                    <span>Tracking:</span>
                    <span class="font-mono">{{ order.tracking_number }}</span>
                  </div>
                  <div v-if="order.available_date" class="flex justify-between">
                    <span>Available:</span>
                    <span>{{ formatDate(order.available_date) }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Order Timeline -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Order Timeline</h2>
            <div v-if="orderTimeline.length" class="space-y-4">
              <div
                v-for="event in orderTimeline"
                :key="event.id"
                class="flex items-start space-x-3"
              >
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 text-sm">{{ event.type === 'cancelled' ? '❌' : '🗓️' }}</span>
                  </div>
                </div>
                <div class="flex-1">
                  <div class="font-medium text-gray-900">{{ event.title }}</div>
                  <div class="text-sm text-gray-600">{{ event.description }}</div>
                  <div class="text-xs text-gray-500 mt-1">{{ formatDateTime(event.date) }}</div>
                </div>
              </div>
            </div>
            <p v-else class="text-sm text-gray-500">Status updates will appear here.</p>
          </div>

          <!-- Order Messages -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-semibold">Order Messages</h2>
              <button
                @click="loadMessages"
                class="text-sm text-green-600 hover:text-green-700"
                type="button"
              >
                Refresh
              </button>
            </div>

            <div v-if="messagesLoading" class="text-sm text-gray-500">Loading messages…</div>
            <div
              v-else
              class="mb-4 max-h-64 space-y-3 overflow-y-auto rounded border border-gray-100 bg-gray-50 p-3"
            >
              <div v-if="!messages.length" class="text-sm text-gray-500">
                No messages yet. Start the conversation to coordinate pickup or delivery.
              </div>
              <div
                v-for="message in messages"
                :key="message.id"
                class="max-w-md rounded-lg px-4 py-3 text-sm"
                :class="message.sender_id === currentUserId ? 'ml-auto bg-green-100 text-right' : 'mr-auto bg-white text-left border border-gray-200'"
              >
                <div class="text-xs text-gray-500">
                  {{ message.sender?.name || 'Participant' }} • {{ formatDateTime(message.created_at) }}
                </div>
                <div class="mt-2 text-gray-800 whitespace-pre-line">{{ message.message }}</div>
              </div>
            </div>

            <form @submit.prevent="sendMessage" class="space-y-3">
              <textarea
                id="order-message-input"
                v-model="messageInput"
                rows="3"
                class="w-full rounded-md border border-gray-300 px-3 py-2 focus:border-green-500 focus:outline-none focus:ring-1 focus:ring-green-500"
                placeholder="Share updates or ask a question"
              ></textarea>
              <div class="flex items-center justify-between">
                <span v-if="messageError" class="text-sm text-red-600">{{ messageError }}</span>
                <button
                  type="submit"
                  :disabled="sendingMessage || !messageInput.trim()"
                  class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white disabled:cursor-not-allowed disabled:opacity-50"
                >
                  {{ sendingMessage ? 'Sending…' : 'Send Message' }}
                </button>
              </div>
            </form>
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
                <span class="font-medium">{{ formatCurrency(orderSubtotal) }}</span>
              </div>
              <div class="border-t border-gray-200 pt-3">
                <div class="flex justify-between text-lg font-bold">
                  <span>Total:</span>
                  <span>{{ formatCurrency(order.total_amount || orderSubtotal) }}</span>
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
                <span class="font-medium">{{ order.payment_method || 'To be arranged' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Payment Status:</span>
                <span
                  :class="order.payment_status === 'paid' ? 'text-green-600' : 'text-yellow-600'"
                  class="font-medium"
                >
                  {{ order.payment_status || 'pending' }}
                </span>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
            <div class="space-y-3">
              <button
                @click="contactSeller"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                💬 Contact Seller
              </button>
            </div>
          </div>
        </div>
      </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { formatCurrency } from '@/utils/format'
import { riceMarketplaceAPI } from '@/services/api'
import { useAuthStore } from '@/stores/auth'

const route = useRoute()
const router = useRouter()
const authStore = useAuthStore()

const order = ref(null)
const loading = ref(true)
const error = ref('')

const isFarmer = computed(() => authStore.user?.role === 'farmer')
const ordersListRoute = computed(() => (isFarmer.value ? '/marketplace/orders' : '/orders'))

const orderSteps = ['pending', 'confirmed', 'processing', 'shipped', 'delivered']

const lineItems = computed(() => {
  if (!order.value?.rice_product) return []
  return [
    {
      id: order.value.rice_product.id,
      name: order.value.rice_product.name,
      description: order.value.rice_product.description,
      quantity: order.value.quantity,
      price: order.value.unit_price,
      farmer: order.value.rice_product.farmer?.name,
      location: order.value.rice_product.farmer?.address?.city,
    },
  ]
})

const orderSubtotal = computed(() => {
  if (!order.value) return 0
  return (order.value.unit_price || 0) * (order.value.quantity || 0)
})

const deliveryAddress = computed(() => order.value?.delivery_address || {})
const currentUserId = computed(() => authStore.user?.id)

const messages = ref([])
const messagesLoading = ref(false)
const sendingMessage = ref(false)
const messageInput = ref('')
const messageError = ref('')

const getStatusBadgeClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    processing: 'bg-purple-100 text-purple-800',
    shipped: 'bg-indigo-100 text-indigo-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getCurrentStepIndex = () => {
  if (!order.value?.status) return 0
  const statusOrder = ['pending', 'confirmed', 'processing', 'shipped', 'delivered']
  const idx = statusOrder.indexOf(order.value.status)
  return idx >= 0 ? idx : 0
}

const getStepClass = (index) => {
  const currentStep = getCurrentStepIndex()
  if (index < currentStep) return 'bg-green-600 text-white'
  if (index === currentStep) return 'bg-blue-600 text-white'
  return 'bg-gray-200 text-gray-600'
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString()
}

const formatDateTime = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleString()
}

const orderTimeline = computed(() => {
  if (!order.value) return []
  const events = [
    {
      id: 'placed',
      type: 'ordered',
      title: 'Order Placed',
      description: 'Order created by buyer',
      date: order.value.order_date || order.value.created_at,
    },
  ]

  if (['confirmed', 'processing', 'shipped', 'delivered'].includes(order.value.status)) {
    events.push({
      id: 'confirmed',
      type: 'processing',
      title: 'Order Confirmed',
      description: 'Farmer confirmed the order',
      date: order.value.updated_at,
    })
  }

  if (['shipped', 'delivered'].includes(order.value.status)) {
    events.push({
      id: 'shipped',
      type: 'shipped',
      title: 'Order Shipped',
      description: 'Order is on the way',
      date: order.value.actual_delivery_date || order.value.updated_at,
    })
  }

  if (order.value.status === 'delivered') {
    events.push({
      id: 'delivered',
      type: 'delivered',
      title: 'Order Delivered',
      description: 'Buyer received the order',
      date: order.value.actual_delivery_date || order.value.updated_at,
    })
  }

  if (order.value.status === 'cancelled') {
    events.push({
      id: 'cancelled',
      type: 'cancelled',
      title: 'Order Cancelled',
      description: 'Order was cancelled',
      date: order.value.updated_at,
    })
  }

  return events
})

const contactSeller = () => {
  const input = document.getElementById('order-message-input')
  if (input) {
    input.focus()
  }
}

const printOrder = () => {
  window.print()
}

const loadOrderData = async (id) => {
  loading.value = true
  error.value = ''
  try {
    const response = await riceMarketplaceAPI.getOrderById(id)
    order.value = response.data.order
    await loadMessages()
  } catch (err) {
    error.value = err.userMessage || err.response?.data?.message || 'Failed to load order'
  } finally {
    loading.value = false
  }
}

const loadMessages = async () => {
  if (!order.value) return
  messagesLoading.value = true
  messageError.value = ''
  try {
    const response = await riceMarketplaceAPI.getOrderMessages(order.value.id)
    messages.value = response.data.messages || []
  } catch (err) {
    messageError.value = err.userMessage || err.response?.data?.message || 'Failed to load messages'
  } finally {
    messagesLoading.value = false
  }
}

const sendMessage = async () => {
  if (!order.value || !messageInput.value.trim()) {
    return
  }

  sendingMessage.value = true
  messageError.value = ''
  try {
    const response = await riceMarketplaceAPI.sendOrderMessage(order.value.id, {
      message: messageInput.value.trim(),
    })
    messages.value.push(response.data.data)
    messageInput.value = ''
  } catch (err) {
    messageError.value = err.userMessage || err.response?.data?.message || 'Failed to send message'
  } finally {
    sendingMessage.value = false
  }
}

onMounted(() => {
  loadOrderData(route.params.id)
})
</script>

<style scoped>
.order-detail-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>