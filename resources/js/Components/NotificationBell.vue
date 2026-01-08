<template>
  <div class="relative">
    <button
      @click="toggleDropdown"
      class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors"
    >
      <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
      <span
        v-if="unreadCount > 0"
        class="absolute top-0 right-0 inline-flex items-center justify-center px-1.5 py-0.5 text-xs font-bold leading-none text-white transform translate-x-1/2 -translate-y-1/2 bg-red-500 rounded-full"
      >
        {{ unreadCount > 9 ? '9+' : unreadCount }}
      </span>
    </button>

    <!-- Dropdown -->
    <div
      v-if="showDropdown"
      class="absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border border-gray-200 z-50 max-h-96 overflow-hidden"
    >
      <div class="flex items-center justify-between p-4 border-b border-gray-100">
        <h3 class="font-semibold text-gray-900">Notifications</h3>
        <button
          v-if="unreadCount > 0"
          @click="markAllAsRead"
          class="text-xs text-blue-600 hover:text-blue-800"
        >
          Mark all as read
        </button>
      </div>

      <div v-if="loading" class="p-4 text-center text-gray-500">Loading...</div>

      <div v-else-if="notifications.length === 0" class="p-8 text-center text-gray-500">
        <svg class="h-12 w-12 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <p>No notifications</p>
      </div>

      <div v-else class="max-h-64 overflow-y-auto">
        <div
          v-for="notification in notifications"
          :key="notification.id"
          @click="handleNotificationClick(notification)"
          :class="[
            'p-4 border-b border-gray-50 cursor-pointer hover:bg-gray-50 transition-colors',
            !notification.read_at ? 'bg-blue-50' : ''
          ]"
        >
          <div class="flex justify-between items-start mb-1">
            <span class="font-medium text-gray-900 text-sm">{{ notification.title }}</span>
            <span class="text-xs text-gray-400">{{ formatTime(notification.created_at) }}</span>
          </div>
          <p class="text-sm text-gray-600 line-clamp-2">{{ notification.message }}</p>
        </div>
      </div>

      <div class="p-3 border-t border-gray-100 text-center">
        <router-link
          to="/notifications"
          @click="showDropdown = false"
          class="text-sm text-blue-600 hover:text-blue-800"
        >
          View all notifications
        </router-link>
      </div>
    </div>

    <!-- Overlay to close dropdown -->
    <div
      v-if="showDropdown"
      class="fixed inset-0 z-40"
      @click="showDropdown = false"
    ></div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const showDropdown = ref(false)
const loading = ref(false)
const notifications = ref([])
const unreadCount = ref(0)

const toggleDropdown = () => {
  showDropdown.value = !showDropdown.value
  if (showDropdown.value) {
    fetchNotifications()
  }
}

const fetchNotifications = async () => {
  loading.value = true
  try {
    const response = await axios.get('/api/notifications')
    notifications.value = response.data.notifications?.data || response.data.notifications || []
    unreadCount.value = response.data.unread_count || 0
  } catch (error) {
    console.error('Failed to fetch notifications:', error)
  } finally {
    loading.value = false
  }
}

const fetchUnreadCount = async () => {
  try {
    const response = await axios.get('/api/notifications/unread-count')
    unreadCount.value = response.data.unread_count || 0
  } catch (error) {
    console.error('Failed to fetch unread count:', error)
  }
}

const markAllAsRead = async () => {
  try {
    await axios.post('/api/notifications/read-all')
    unreadCount.value = 0
    notifications.value = notifications.value.map(n => ({ ...n, read_at: new Date().toISOString() }))
  } catch (error) {
    console.error('Failed to mark all as read:', error)
  }
}

const handleNotificationClick = async (notification) => {
  // Mark as read
  if (!notification.read_at) {
    try {
      await axios.post(`/api/notifications/${notification.id}/read`)
      notification.read_at = new Date().toISOString()
      unreadCount.value = Math.max(0, unreadCount.value - 1)
    } catch (error) {
      console.error('Failed to mark as read:', error)
    }
  }

  // Navigate if link is provided
  if (notification.link) {
    showDropdown.value = false
    router.push(notification.link)
  }
}

const formatTime = (dateString) => {
  const date = new Date(dateString)
  const now = new Date()
  const diff = now.getTime() - date.getTime()
  const minutes = Math.floor(diff / 60000)
  const hours = Math.floor(minutes / 60)
  const days = Math.floor(hours / 24)

  if (minutes < 1) return 'Just now'
  if (minutes < 60) return `${minutes}m ago`
  if (hours < 24) return `${hours}h ago`
  if (days < 7) return `${days}d ago`
  return date.toLocaleDateString()
}

// Poll for new notifications every 30 seconds
let pollInterval = null

onMounted(() => {
  fetchUnreadCount()
  pollInterval = setInterval(fetchUnreadCount, 30000)
})

onUnmounted(() => {
  if (pollInterval) {
    clearInterval(pollInterval)
  }
})
</script>
