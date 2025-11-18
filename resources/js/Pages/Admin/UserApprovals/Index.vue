<template>
  <div class="user-approvals-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">User Approvals</h1>
          <p class="text-gray-600 mt-2">Review and approve pending user registrations</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="loadPendingUsers"
            :disabled="loading"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
          >
            {{ loading ? 'Loading...' : 'Refresh' }}
          </button>
          <router-link
            to="/admin/users"
            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700"
          >
            All Users
          </router-link>
        </div>
      </div>

      <!-- Stats -->
      <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="text-2xl font-bold text-yellow-600">{{ stats.pending || 0 }}</div>
          <div class="text-sm text-gray-600">Pending</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="text-2xl font-bold text-green-600">{{ stats.approved || 0 }}</div>
          <div class="text-sm text-gray-600">Approved</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="text-2xl font-bold text-red-600">{{ stats.rejected || 0 }}</div>
          <div class="text-sm text-gray-600">Rejected</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="text-2xl font-bold text-blue-600">{{ (stats.pending || 0) + (stats.approved || 0) + (stats.rejected || 0) }}</div>
          <div class="text-sm text-gray-600">Total</div>
        </div>
      </div>

      <!-- Pending Users List -->
      <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-xl font-semibold">Pending Approvals</h2>
        </div>
        <div v-if="loading && !pendingUsers.length" class="p-12 text-center text-gray-500">
          <div class="mx-auto mb-4 h-10 w-10 animate-spin rounded-full border-b-2 border-blue-600"></div>
          Loading pending users...
        </div>
        <div v-else-if="!loading && !pendingUsers.length" class="p-12 text-center text-gray-500">
          <p class="text-lg">No pending user approvals</p>
        </div>
        <div v-else class="divide-y divide-gray-200">
          <div
            v-for="user in pendingUsers"
            :key="user.id"
            class="p-6 hover:bg-gray-50"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center space-x-3">
                  <h3 class="text-lg font-semibold text-gray-900">{{ user.name }}</h3>
                  <span
                    :class="{
                      'bg-yellow-100 text-yellow-800': user.role === 'farmer',
                      'bg-blue-100 text-blue-800': user.role === 'buyer'
                    }"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ user.role }}
                  </span>
                </div>
                <p class="text-gray-600 mt-1">{{ user.email }}</p>
                <p v-if="user.phone" class="text-sm text-gray-500 mt-1">{{ user.phone }}</p>
                <p class="text-xs text-gray-400 mt-2">
                  Registered: {{ formatDate(user.created_at) }}
                </p>
              </div>
              <div class="flex space-x-2 ml-4">
                <button
                  @click="approveUser(user.id)"
                  :disabled="processing === user.id"
                  class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50 text-sm"
                >
                  {{ processing === user.id ? 'Processing...' : 'Approve' }}
                </button>
                <button
                  @click="showRejectModal(user)"
                  :disabled="processing === user.id"
                  class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 disabled:opacity-50 text-sm"
                >
                  Reject
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Reject Modal -->
      <div
        v-if="rejectModalUser"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="rejectModalUser = null"
      >
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
          <h3 class="text-xl font-semibold mb-4">Reject User Registration</h3>
          <p class="text-gray-600 mb-4">
            Are you sure you want to reject <strong>{{ rejectModalUser.name }}</strong>?
          </p>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Rejection Reason (required)
            </label>
            <textarea
              v-model="rejectionReason"
              rows="4"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
              placeholder="Enter reason for rejection..."
            ></textarea>
          </div>
          <div class="flex space-x-3">
            <button
              @click="confirmReject"
              :disabled="!rejectionReason.trim() || processing === rejectModalUser.id"
              class="flex-1 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 disabled:opacity-50"
            >
              {{ processing === rejectModalUser.id ? 'Processing...' : 'Confirm Reject' }}
            </button>
            <button
              @click="rejectModalUser = null; rejectionReason = ''"
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
const processing = ref(null)
const pendingUsers = ref([])
const stats = ref(null)
const rejectModalUser = ref(null)
const rejectionReason = ref('')

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const loadPendingUsers = async () => {
  loading.value = true
  try {
    const [usersResponse, statsResponse] = await Promise.all([
      adminAPI.getPendingUsers(),
      adminAPI.getUserApprovalStats()
    ])
    pendingUsers.value = usersResponse.data.data || []
    stats.value = statsResponse.data
  } catch (error) {
    console.error('Error loading pending users:', error)
    alert('Failed to load pending users')
  } finally {
    loading.value = false
  }
}

const approveUser = async (userId) => {
  if (!confirm('Are you sure you want to approve this user?')) return
  
  processing.value = userId
  try {
    await adminAPI.approveUser(userId)
    await loadPendingUsers()
    alert('User approved successfully')
  } catch (error) {
    console.error('Error approving user:', error)
    alert(error.response?.data?.message || 'Failed to approve user')
  } finally {
    processing.value = null
  }
}

const showRejectModal = (user) => {
  rejectModalUser.value = user
  rejectionReason.value = ''
}

const confirmReject = async () => {
  if (!rejectionReason.value.trim()) {
    alert('Please provide a rejection reason')
    return
  }

  processing.value = rejectModalUser.value.id
  try {
    await adminAPI.rejectUser(rejectModalUser.value.id, rejectionReason.value)
    await loadPendingUsers()
    rejectModalUser.value = null
    rejectionReason.value = ''
    alert('User rejected successfully')
  } catch (error) {
    console.error('Error rejecting user:', error)
    alert(error.response?.data?.message || 'Failed to reject user')
  } finally {
    processing.value = null
  }
}

onMounted(() => {
  loadPendingUsers()
})
</script>

<style scoped>
.user-approvals-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>

