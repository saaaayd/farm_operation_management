<template>
  <div class="admin-reports-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">System Reports</h1>
          <p class="text-gray-600 mt-2">Generate and view comprehensive system reports</p>
        </div>
        <router-link
          to="/admin"
          class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
        >
          Back to Dashboard
        </router-link>
      </div>

      <!-- Report Categories -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
              <span class="text-blue-600 text-2xl">👥</span>
            </div>
            <h3 class="ml-4 text-lg font-semibold text-gray-900">User Reports</h3>
          </div>
          <p class="text-gray-600 mb-4">Generate reports about user activity, registrations, and demographics</p>
          <button
            @click="generateUserReport"
            class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Generate User Report
          </button>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
              <span class="text-green-600 text-2xl">🌾</span>
            </div>
            <h3 class="ml-4 text-lg font-semibold text-gray-900">Farm Reports</h3>
          </div>
          <p class="text-gray-600 mb-4">Analyze farm productivity, field utilization, and crop yields</p>
          <button
            @click="generateFarmReport"
            class="w-full bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            Generate Farm Report
          </button>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
              <span class="text-purple-600 text-2xl">💰</span>
            </div>
            <h3 class="ml-4 text-lg font-semibold text-gray-900">Financial Reports</h3>
          </div>
          <p class="text-gray-600 mb-4">Review revenue, expenses, and financial performance metrics</p>
          <button
            @click="generateFinancialReport"
            class="w-full bg-purple-600 text-white px-4 py-2 rounded-md hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-purple-500"
          >
            Generate Financial Report
          </button>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
              <span class="text-orange-600 text-2xl">🛒</span>
            </div>
            <h3 class="ml-4 text-lg font-semibold text-gray-900">Order Reports</h3>
          </div>
          <p class="text-gray-600 mb-4">Track marketplace orders, sales trends, and customer behavior</p>
          <button
            @click="generateOrderReport"
            class="w-full bg-orange-600 text-white px-4 py-2 rounded-md hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500"
          >
            Generate Order Report
          </button>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
              <span class="text-red-600 text-2xl">⚙️</span>
            </div>
            <h3 class="ml-4 text-lg font-semibold text-gray-900">System Reports</h3>
          </div>
          <p class="text-gray-600 mb-4">Monitor system performance, errors, and usage statistics</p>
          <button
            @click="generateSystemReport"
            class="w-full bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500"
          >
            Generate System Report
          </button>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center mb-4">
            <div class="w-12 h-12 bg-indigo-100 rounded-full flex items-center justify-center">
              <span class="text-indigo-600 text-2xl">📊</span>
            </div>
            <h3 class="ml-4 text-lg font-semibold text-gray-900">Custom Reports</h3>
          </div>
          <p class="text-gray-600 mb-4">Create custom reports with specific parameters and filters</p>
          <button
            @click="createCustomReport"
            class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500"
          >
            Create Custom Report
          </button>
        </div>
      </div>

      <!-- Recent Reports -->
      <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Reports</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Report Name
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Type
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Generated
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="report in recentReports" :key="report.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                  {{ report.name }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="getReportTypeClass(report.type)"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ report.type }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(report.generated_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <div class="flex space-x-2">
                    <button
                      @click="downloadReport(report.id)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      Download
                    </button>
                    <button
                      @click="viewReport(report.id)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      View
                    </button>
                    <button
                      @click="deleteReport(report.id)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

const recentReports = ref([
  {
    id: 1,
    name: 'Monthly User Activity Report',
    type: 'User',
    generated_at: '2024-10-09T14:30:00Z'
  },
  {
    id: 2,
    name: 'Q3 Financial Summary',
    type: 'Financial',
    generated_at: '2024-10-08T10:15:00Z'
  },
  {
    id: 3,
    name: 'Farm Productivity Analysis',
    type: 'Farm',
    generated_at: '2024-10-07T16:45:00Z'
  }
])

const getReportTypeClass = (type) => {
  const classes = {
    'User': 'bg-blue-100 text-blue-800',
    'Farm': 'bg-green-100 text-green-800',
    'Financial': 'bg-purple-100 text-purple-800',
    'Order': 'bg-orange-100 text-orange-800',
    'System': 'bg-red-100 text-red-800',
    'Custom': 'bg-indigo-100 text-indigo-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const generateUserReport = () => {
  alert('Generating User Report... This will be available for download shortly.')
}

const generateFarmReport = () => {
  alert('Generating Farm Report... This will be available for download shortly.')
}

const generateFinancialReport = () => {
  alert('Generating Financial Report... This will be available for download shortly.')
}

const generateOrderReport = () => {
  alert('Generating Order Report... This will be available for download shortly.')
}

const generateSystemReport = () => {
  alert('Generating System Report... This will be available for download shortly.')
}

const createCustomReport = () => {
  alert('Custom Report Builder coming soon!')
}

const downloadReport = (id) => {
  alert(`Downloading report ${id}...`)
}

const viewReport = (id) => {
  alert(`Viewing report ${id}...`)
}

const deleteReport = (id) => {
  if (confirm('Are you sure you want to delete this report?')) {
    const index = recentReports.value.findIndex(r => r.id === id)
    if (index > -1) {
      recentReports.value.splice(index, 1)
      alert('Report deleted successfully!')
    }
  }
}
</script>

<style scoped>
.admin-reports-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>