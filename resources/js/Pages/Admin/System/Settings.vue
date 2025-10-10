<template>
  <div class="admin-system-settings-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">System Settings</h1>
          <p class="text-gray-600 mt-2">Configure system-wide settings and preferences</p>
        </div>
        <router-link
          to="/admin"
          class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
        >
          Back to Dashboard
        </router-link>
      </div>

      <div class="space-y-6">
        <!-- General Settings -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">General Settings</h2>
          <form @submit.prevent="saveGeneralSettings" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">System Name</label>
                <input
                  v-model="generalSettings.systemName"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Admin Email</label>
                <input
                  v-model="generalSettings.adminEmail"
                  type="email"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">System Description</label>
              <textarea
                v-model="generalSettings.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
            </div>
            <div class="flex justify-end">
              <button
                type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                Save General Settings
              </button>
            </div>
          </form>
        </div>

        <!-- Security Settings -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">Security Settings</h2>
          <form @submit.prevent="saveSecuritySettings" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Session Timeout (minutes)</label>
                <input
                  v-model="securitySettings.sessionTimeout"
                  type="number"
                  min="15"
                  max="1440"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Max Login Attempts</label>
                <input
                  v-model="securitySettings.maxLoginAttempts"
                  type="number"
                  min="3"
                  max="10"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
            <div class="flex items-center">
              <input
                v-model="securitySettings.requireTwoFactor"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label class="ml-2 block text-sm text-gray-900">
                Require Two-Factor Authentication for Admin Users
              </label>
            </div>
            <div class="flex justify-end">
              <button
                type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                Save Security Settings
              </button>
            </div>
          </form>
        </div>

        <!-- Email Settings -->
        <div class="bg-white rounded-lg shadow-md p-6">
          <h2 class="text-xl font-semibold text-gray-900 mb-4">Email Settings</h2>
          <form @submit.prevent="saveEmailSettings" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Host</label>
                <input
                  v-model="emailSettings.smtpHost"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">SMTP Port</label>
                <input
                  v-model="emailSettings.smtpPort"
                  type="number"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">From Email</label>
                <input
                  v-model="emailSettings.fromEmail"
                  type="email"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">From Name</label>
                <input
                  v-model="emailSettings.fromName"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
            <div class="flex justify-end">
              <button
                type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
              >
                Save Email Settings
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const generalSettings = ref({
  systemName: 'RiceFARM Management System',
  adminEmail: 'admin@ricefarm.com',
  description: 'A comprehensive rice farming management system'
})

const securitySettings = ref({
  sessionTimeout: 120,
  maxLoginAttempts: 5,
  requireTwoFactor: false
})

const emailSettings = ref({
  smtpHost: 'smtp.gmail.com',
  smtpPort: 587,
  fromEmail: 'noreply@ricefarm.com',
  fromName: 'RiceFARM System'
})

const saveGeneralSettings = () => {
  alert('General settings saved successfully!')
}

const saveSecuritySettings = () => {
  alert('Security settings saved successfully!')
}

const saveEmailSettings = () => {
  alert('Email settings saved successfully!')
}
</script>

<style scoped>
.admin-system-settings-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>