<template>
  <div class="admin-user-detail-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">User Details</h1>
          <p class="text-gray-600 mt-2">View and manage user information</p>
        </div>
        <div class="flex space-x-3">
          <router-link
            :to="`/admin/users/${userId}/edit`"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Edit User
          </router-link>
          <router-link
            to="/admin/users"
            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Back to Users
          </router-link>
        </div>
      </div>

      <!-- User Information Card -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="flex items-center mb-6">
          <div class="w-20 h-20 bg-gray-200 rounded-full flex items-center justify-center">
            <span class="text-gray-600 text-2xl font-bold">{{ user.name?.charAt(0) }}</span>
          </div>
          <div class="ml-6">
            <h2 class="text-2xl font-bold text-gray-900">{{ user.name }}</h2>
            <p class="text-gray-600">{{ user.email }}</p>
            <div class="mt-2">
              <span
                :class="getRoleBadgeClass(user.role)"
                class="px-3 py-1 text-sm font-medium rounded-full"
              >
                {{ user.role }}
              </span>
              <span
                :class="getStatusBadgeClass(user.status)"
                class="ml-2 px-3 py-1 text-sm font-medium rounded-full"
              >
                {{ user.status }}
              </span>
            </div>
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-500">Email</label>
                <p class="text-gray-900">{{ user.email }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Phone</label>
                <p class="text-gray-900">{{ user.phone || 'Not provided' }}</p>
              </div>
            </div>
          </div>
          
          <div>
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Account Information</h3>
            <div class="space-y-3">
              <div>
                <label class="block text-sm font-medium text-gray-500">Role</label>
                <p class="text-gray-900 capitalize">{{ user.role }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Status</label>
                <p class="text-gray-900 capitalize">{{ user.status }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Joined</label>
                <p class="text-gray-900">{{ formatDate(user.joined) }}</p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-500">Last Login</label>
                <p class="text-gray-900">{{ formatDate(user.last_login) }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Actions -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Actions</h3>
        <div class="flex space-x-3">
          <button
            @click="toggleUserStatus"
            :class="user.status === 'active' ? 'bg-red-600 hover:bg-red-700' : 'bg-green-600 hover:bg-green-700'"
            class="text-white px-4 py-2 rounded-md focus:outline-none focus:ring-2"
          >
            {{ user.status === 'active' ? 'Suspend User' : 'Activate User' }}
          </button>
          <button
            @click="resetPassword"
            class="bg-yellow-600 text-white px-4 py-2 rounded-md hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-yellow-500"
          >
            Reset Password
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const userId = route.params.id

const user = ref({
  id: userId,
  name: 'John Smith',
  email: 'john.smith@example.com',
  role: 'farmer',
  status: 'active',
  phone: '+1 (555) 123-4567',
  last_login: '2024-03-25T10:00:00Z',
  joined: '2024-01-15T09:00:00Z'
})

const getRoleBadgeClass = (role) => {
  const classes = {
    admin: 'bg-red-100 text-red-800',
    farmer: 'bg-green-100 text-green-800',
    buyer: 'bg-blue-100 text-blue-800'
  }
  return classes[role] || 'bg-gray-100 text-gray-800'
}

const getStatusBadgeClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    suspended: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const toggleUserStatus = () => {
  user.value.status = user.value.status === 'active' ? 'suspended' : 'active'
  alert(`User ${user.value.status === 'active' ? 'activated' : 'suspended'} successfully!`)
}

const resetPassword = () => {
  alert('Password reset email sent to user!')
}

onMounted(() => {
  // In a real app, fetch user data from API
  console.log('Loading user details for ID:', userId)
})
</script>

<style scoped>
.admin-user-detail-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>