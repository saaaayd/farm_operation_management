<template>
  <div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto space-y-8">
      <!-- Header -->
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <button
            type="button"
            @click="router.push('/laborers')"
            class="inline-flex items-center text-sm font-medium text-emerald-700 hover:text-emerald-900 mb-2"
          >
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Laborers
          </button>
          <h1 class="text-3xl font-bold text-gray-900">Laborer Details</h1>
        </div>
        <div class="flex gap-3">
             <button
            @click="deleteLaborer"
            class="inline-flex items-center px-4 py-2 border border-red-300 rounded-md shadow-sm text-sm font-medium text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
          >
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
            </svg>
            Delete
          </button>
          <button
            @click="router.push(`/laborers/${laborer.id}/edit`)"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500"
          >
            <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            Edit Laborer
          </button>
        </div>
      </div>

      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-600"></div>
      </div>

      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-800">
        {{ error }}
      </div>

      <div v-else class="space-y-6">
        <!-- Main Info Card -->
        <div class="bg-white shadow rounded-2xl overflow-hidden">
          <div class="px-6 py-8 sm:p-10">
            <div class="flex items-center gap-6 mb-8">
              <div class="h-20 w-20 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full flex items-center justify-center shadow-lg text-white font-bold text-3xl">
                {{ getInitials(laborer.name) }}
              </div>
              <div>
                <h2 class="text-2xl font-bold text-gray-900">{{ laborer.name }}</h2>
                <div class="flex items-center gap-2 mt-1">
                  <span
                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium capitalize"
                    :class="statusClass(laborer.status)"
                  >
                    {{ formatStatus(laborer.status) }}
                  </span>
                   <span class="text-gray-400">|</span>
                   <span class="text-gray-500 text-sm">Hired {{ formatDate(laborer.hire_date) }}</span>
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-6">
              <div class="border-t border-gray-100 pt-4">
                <dt class="text-sm font-medium text-gray-500 mb-1">Phone Number</dt>
                <dd class="text-base text-gray-900">{{ laborer.phone }}</dd>
              </div>
              <div class="border-t border-gray-100 pt-4">
                <dt class="text-sm font-medium text-gray-500 mb-1">Email Address</dt>
                <dd class="text-base text-gray-900">{{ laborer.email || 'N/A' }}</dd>
              </div>
              <div class="border-t border-gray-100 pt-4">
                <dt class="text-sm font-medium text-gray-500 mb-1">Address</dt>
                <dd class="text-base text-gray-900">{{ laborer.address || 'N/A' }}</dd>
              </div>
              <div class="border-t border-gray-100 pt-4">
                  <dt class="text-sm font-medium text-gray-500 mb-1">Groups</dt>
                  <dd class="flex flex-wrap gap-2">
                       <span
                          v-for="group in laborer.groups"
                          :key="group.id"
                          class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium"
                          :style="{ backgroundColor: group.color + '20', color: group.color }"
                        >
                          {{ group.name }}
                        </span>
                        <span v-if="!laborer.groups || laborer.groups.length === 0" class="text-gray-500 text-sm italic">
                            No groups assigned
                        </span>
                  </dd>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Employment Details -->
            <div class="bg-white shadow rounded-2xl overflow-hidden p-6">
                 <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    Employment Details
                 </h3>
                 <div class="space-y-4">
                     <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Skill Level</dt>
                        <dd class="text-sm font-semibold text-gray-700 capitalize">{{ laborer.skill_level || 'N/A' }}</dd>
                    </div>
                     <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Specialization</dt>
                        <dd class="text-sm font-semibold text-gray-700">{{ laborer.specialization || 'N/A' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Rate Type</dt>
                        <dd class="text-sm font-semibold text-gray-700 capitalize">{{ laborer.rate_type ? laborer.rate_type.replace('_', ' ') : 'N/A' }}</dd>
                    </div>
                    <div v-if="laborer.rate_type !== 'per_job'">
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Rate</dt>
                        <dd class="text-sm font-semibold text-gray-700">₱{{ laborer.rate ? Number(laborer.rate).toFixed(2) : '0.00' }}</dd>
                    </div>
                 </div>
            </div>

            <!-- Emergency Contact -->
            <div class="bg-white shadow rounded-2xl overflow-hidden p-6">
                 <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                    <svg class="h-5 w-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M12 12h.01M12 6h.01M12 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Emergency Contact
                 </h3>
                  <div class="space-y-4">
                     <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Name</dt>
                        <dd class="text-sm font-semibold text-gray-700">{{ laborer.emergency_contact_name || 'N/A' }}</dd>
                    </div>
                     <div>
                        <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Phone</dt>
                        <dd class="text-sm font-semibold text-gray-700">{{ laborer.emergency_contact_phone || 'N/A' }}</dd>
                    </div>
                 </div>
            </div>
        </div>

        <!-- Notes -->
        <div class="bg-white shadow rounded-2xl overflow-hidden p-6" v-if="laborer.notes">
             <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center gap-2">
                <svg class="h-5 w-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Additional Notes
             </h3>
             <p class="text-gray-700 whitespace-pre-line">{{ laborer.notes }}</p>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const route = useRoute()

const loading = ref(true)
const error = ref('')
const laborer = ref({})

const fetchLaborer = async () => {
    loading.value = true
    error.value = ''
    try {
        const { data } = await axios.get(`/api/laborers/${route.params.id}`)
        laborer.value = data.laborer
    } catch (err) {
        console.error('Failed to fetch laborer:', err)
        error.value = 'Failed to load laborer details.'
    } finally {
        loading.value = false
    }
}

const deleteLaborer = async () => {
    if (confirm(`Are you sure you want to delete ${laborer.value.name}?`)) {
        try {
            await axios.delete(`/api/laborers/${laborer.value.id}`)
            router.push('/laborers')
        } catch (err) {
            console.error('Failed to delete laborer:', err)
            alert('Failed to delete laborer.')
        }
    }
}

const getInitials = (name) => {
    if (!name) return ''
    return name
        .split(' ')
        .map(n => n[0])
        .join('')
        .toUpperCase()
        .substring(0, 2)
}

const formatDate = (value) => {
  if (!value) return ''
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? value : date.toLocaleDateString()
}

const formatStatus = (status) => {
    if (!status) return ''
    return status.replace(/_/g, ' ')
}

const statusClass = (status) => {
  const classes = {
    active: 'bg-green-100 text-green-800',
    inactive: 'bg-gray-100 text-gray-800',
    on_leave: 'bg-yellow-100 text-yellow-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

onMounted(() => {
    fetchLaborer()
})
</script>
