<template>
  <div class="message-moderation-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Message Moderation</h1>
          <p class="text-gray-600 mt-2">Monitor and moderate buyer-farmer communications</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="loadActiveConversations"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700"
          >
            Active Conversations
          </button>
          <button
            @click="loadAllMessages"
            :disabled="loading"
            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 disabled:opacity-50"
          >
            {{ loading ? 'Loading...' : 'All Messages' }}
          </button>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search in messages..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @input="applyFilters"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Order ID</label>
            <input
              v-model="filters.order_id"
              type="number"
              placeholder="Filter by order..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @input="applyFilters"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sender ID</label>
            <input
              v-model="filters.sender_id"
              type="number"
              placeholder="Filter by sender..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @input="applyFilters"
            />
          </div>
        </div>
      </div>

      <!-- Active Conversations -->
      <div v-if="viewMode === 'conversations'" class="bg-white rounded-lg shadow-md mb-6">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-xl font-semibold">Active Conversations (Last 7 Days)</h2>
        </div>
        <div v-if="loading && !conversations.length" class="p-12 text-center text-gray-500">
          <div class="mx-auto mb-4 h-10 w-10 animate-spin rounded-full border-b-2 border-blue-600"></div>
          Loading conversations...
        </div>
        <div v-else-if="!loading && !conversations.length" class="p-12 text-center text-gray-500">
          <p class="text-lg">No active conversations</p>
        </div>
        <div v-else class="divide-y divide-gray-200">
          <div
            v-for="order in conversations"
            :key="order.id"
            class="p-6 hover:bg-gray-50 cursor-pointer"
            @click="viewOrderMessages(order.id)"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center space-x-3 mb-2">
                  <h3 class="text-lg font-semibold text-gray-900">Order #{{ order.id }}</h3>
                  <span
                    :class="{
                      'bg-yellow-100 text-yellow-800': order.status === 'pending',
                      'bg-blue-100 text-blue-800': order.status === 'confirmed',
                      'bg-green-100 text-green-800': order.status === 'delivered'
                    }"
                    class="px-2 py-1 text-xs font-medium rounded-full capitalize"
                  >
                    {{ order.status }}
                  </span>
                </div>
                <p class="text-gray-600">
                  Buyer: {{ order.buyer?.name || 'Unknown' }} | 
                  Farmer: {{ order.rice_product?.farmer?.name || 'Unknown' }}
                </p>
                <p v-if="order.messages && order.messages.length" class="text-sm text-gray-500 mt-2">
                  Last message: {{ formatDateTime(order.messages[0]?.created_at) }}
                </p>
              </div>
              <button
                @click.stop="viewOrderMessages(order.id)"
                class="ml-4 text-blue-600 hover:text-blue-800"
              >
                View Messages →
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- All Messages -->
      <div v-else class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-xl font-semibold">All Messages</h2>
        </div>
        <div v-if="loading && !messages.length" class="p-12 text-center text-gray-500">
          <div class="mx-auto mb-4 h-10 w-10 animate-spin rounded-full border-b-2 border-blue-600"></div>
          Loading messages...
        </div>
        <div v-else-if="!loading && !messages.length" class="p-12 text-center text-gray-500">
          <p class="text-lg">No messages found</p>
        </div>
        <div v-else class="divide-y divide-gray-200">
          <div
            v-for="message in messages"
            :key="message.id"
            class="p-6 hover:bg-gray-50"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center space-x-3 mb-2">
                  <span class="font-semibold text-gray-900">{{ message.sender?.name || 'Unknown' }}</span>
                  <span class="text-sm text-gray-500">Order #{{ message.rice_order_id }}</span>
                </div>
                <p class="text-gray-700 mb-2">{{ message.message }}</p>
                <p class="text-xs text-gray-400">{{ formatDateTime(message.created_at) }}</p>
              </div>
              <button
                @click="deleteMessage(message.id)"
                class="ml-4 text-red-600 hover:text-red-800 text-sm"
              >
                Delete
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Order Messages Modal -->
      <div
        v-if="selectedOrder"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="selectedOrder = null"
      >
        <div class="bg-white rounded-lg p-6 max-w-3xl w-full mx-4 max-h-[90vh] overflow-y-auto">
          <div class="flex justify-between items-center mb-4">
            <h3 class="text-xl font-semibold">Order #{{ selectedOrder.id }} Messages</h3>
            <button
              @click="selectedOrder = null"
              class="text-gray-500 hover:text-gray-700"
            >
              ✕
            </button>
          </div>
          <div v-if="orderMessagesLoading" class="text-center py-8 text-gray-500">
            Loading messages...
          </div>
          <div v-else-if="orderMessages.length === 0" class="text-center py-8 text-gray-500">
            No messages for this order
          </div>
          <div v-else class="space-y-4 mb-4">
            <div
              v-for="msg in orderMessages"
              :key="msg.id"
              class="p-4 rounded-lg"
              :class="msg.sender_id === selectedOrder.buyer_id ? 'bg-blue-50' : 'bg-green-50'"
            >
              <div class="flex justify-between items-start mb-2">
                <span class="font-semibold text-gray-900">{{ msg.sender?.name || 'Unknown' }}</span>
                <span class="text-xs text-gray-500">{{ formatDateTime(msg.created_at) }}</span>
              </div>
              <p class="text-gray-700">{{ msg.message }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Delete Message Modal -->
      <div
        v-if="deleteModalMessage"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="deleteModalMessage = null"
      >
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
          <h3 class="text-xl font-semibold mb-4">Delete Message</h3>
          <p class="text-gray-600 mb-4">Are you sure you want to delete this message?</p>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Reason (required)</label>
            <textarea
              v-model="deleteReason"
              rows="3"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
              placeholder="Enter reason for deletion..."
            ></textarea>
          </div>
          <div class="flex space-x-3">
            <button
              @click="confirmDelete"
              :disabled="!deleteReason.trim()"
              class="flex-1 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 disabled:opacity-50"
            >
              Delete
            </button>
            <button
              @click="deleteModalMessage = null; deleteReason = ''"
              class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminAPI } from '@/services/api'

const loading = ref(false)
const orderMessagesLoading = ref(false)
const messages = ref([])
const conversations = ref([])
const viewMode = ref('messages')
const selectedOrder = ref(null)
const orderMessages = ref([])
const deleteModalMessage = ref(null)
const deleteReason = ref('')
const filters = ref({
  search: '',
  order_id: '',
  sender_id: '',
})

const formatDateTime = (date) => {
  return new Date(date).toLocaleString()
}

const loadAllMessages = async () => {
  loading.value = true
  viewMode.value = 'messages'
  try {
    const params = Object.fromEntries(
      Object.entries(filters.value).filter(([_, v]) => v !== '')
    )
    const response = await adminAPI.getAllMessages(params)
    messages.value = response.data.data || []
  } catch (error) {
    console.error('Error loading messages:', error)
    alert('Failed to load messages')
  } finally {
    loading.value = false
  }
}

const loadActiveConversations = async () => {
  loading.value = true
  viewMode.value = 'conversations'
  try {
    const response = await adminAPI.getActiveConversations()
    conversations.value = response.data.data || []
  } catch (error) {
    console.error('Error loading conversations:', error)
    alert('Failed to load conversations')
  } finally {
    loading.value = false
  }
}

const viewOrderMessages = async (orderId) => {
  selectedOrder.value = { id: orderId }
  orderMessagesLoading.value = true
  try {
    const response = await adminAPI.getOrderMessages(orderId)
    selectedOrder.value = response.data.order
    orderMessages.value = response.data.messages || []
  } catch (error) {
    console.error('Error loading order messages:', error)
    alert('Failed to load order messages')
  } finally {
    orderMessagesLoading.value = false
  }
}

const deleteMessage = (messageId) => {
  deleteModalMessage.value = messageId
  deleteReason.value = ''
}

const confirmDelete = async () => {
  if (!deleteReason.value.trim()) {
    alert('Please provide a reason')
    return
  }

  try {
    await adminAPI.deleteMessage(deleteModalMessage.value, deleteReason.value)
    alert('Message deleted successfully')
    deleteModalMessage.value = null
    deleteReason.value = ''
    if (viewMode.value === 'messages') {
      loadAllMessages()
    } else {
      loadActiveConversations()
    }
  } catch (error) {
    console.error('Error deleting message:', error)
    alert(error.response?.data?.message || 'Failed to delete message')
  }
}

const applyFilters = () => {
  if (viewMode.value === 'messages') {
    loadAllMessages()
  }
}

onMounted(() => {
  loadAllMessages()
})
</script>

<style scoped>
.message-moderation-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>

