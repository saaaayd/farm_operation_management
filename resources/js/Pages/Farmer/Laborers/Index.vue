<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Standard Header -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Laborer Management</h1>
          <p class="text-gray-500 mt-1">Manage your farm laborers, track their skills, and assign them to tasks.</p>
        </div>
        <div class="flex items-center gap-3">
          <button
            @click="fetchLaborers"
            :disabled="loading"
            class="flex items-center gap-2 bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm font-medium disabled:opacity-50"
          >
            <svg
              :class="['h-5 w-5', { 'animate-spin': loading }]"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
          </button>
          <button
            @click="goToGroups"
            class="flex items-center gap-2 bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm font-medium"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
            Manage Groups
          </button>
          <button
            @click="goToCreate"
            class="flex items-center gap-2 bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-sm font-medium"
          >
            <span class="text-xl leading-none">+</span> Add Laborer
          </button>
        </div>
      </div>

      <div>
      <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-700">{{ error }}</p>
            <button
              @click="fetchLaborers"
              class="mt-2 text-sm font-medium text-red-700 hover:text-red-800"
            >
              Try again
            </button>
          </div>
        </div>
      </div>

      <div v-else-if="loading && laborers.length === 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="n in 6"
          :key="n"
          class="bg-white rounded-lg shadow p-6 animate-pulse space-y-4"
        >
          <div class="h-6 bg-gray-200 rounded"></div>
          <div class="space-y-2">
            <div class="h-3 bg-gray-200 rounded"></div>
            <div class="h-3 bg-gray-200 rounded w-3/4"></div>
            <div class="h-3 bg-gray-200 rounded w-2/4"></div>
          </div>
          <div class="h-10 bg-gray-200 rounded"></div>
        </div>
      </div>

      <div v-else>
        <div v-if="laborers.length === 0" class="bg-white/90 backdrop-blur-sm rounded-2xl shadow-xl p-12 text-center border border-gray-100">
          <div class="inline-flex items-center justify-center h-20 w-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-2xl mb-6">
            <svg class="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">No laborers yet</h2>
          <p class="text-sm text-gray-600 mb-8 max-w-md mx-auto">
            Add your first laborer to start managing your workforce and assigning tasks.
          </p>
          <button
            @click="goToCreate"
            class="inline-flex items-center px-6 py-3 text-sm font-semibold rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
          >
            <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Add Your First Laborer
          </button>
        </div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <article
            v-for="laborer in laborers"
            :key="laborer.id"
            class="group bg-white/90 backdrop-blur-sm rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 border border-gray-100 overflow-hidden relative"
          >
            <!-- Actions -->
            <div class="absolute top-4 right-4 flex space-x-2">
                <button
                    @click.stop="goToEdit(laborer)"
                    class="p-2 bg-blue-50 text-blue-600 rounded-full hover:bg-blue-100 transition-colors"
                    title="Edit"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </button>
                <button
                    @click.stop="confirmDelete(laborer)"
                    class="p-2 bg-red-50 text-red-600 rounded-full hover:bg-red-100 transition-colors"
                    title="Delete"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                </button>
            </div>

            <div class="p-6 h-full flex flex-col cursor-pointer" @click="goToEdit(laborer)">
              <!-- Header -->
              <div class="flex items-start justify-between mb-5">
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-2 pr-16">
                    <div class="h-12 w-12 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center shadow-md text-white font-bold text-xl">
                      {{ getInitials(laborer.name) }}
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 group-hover:text-blue-700 transition-colors">{{ laborer.name }}</h3>
                        <p class="text-sm text-gray-500">{{ laborer.phone }}</p>
                    </div>
                  </div>
                </div>
              </div>

              <div class="mb-4">
                  <span
                  class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold shadow-sm mr-2"
                  :class="statusClass(laborer.status)"
                >
                  {{ formatStatus(laborer.status) }}
                </span>
                <span
                  v-for="group in laborer.groups"
                  :key="group.id"
                  class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mr-1"
                  :style="{ backgroundColor: group.color + '20', color: group.color }"
                >
                  {{ group.name }}
                </span>
              </div>

              <!-- Details Grid -->
              <dl class="grid grid-cols-2 gap-4 mb-5">
                <div class="flex items-start gap-2">
                  <div class="mt-0.5">
                    <svg class="h-4 w-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Skill Level</dt>
                    <dd class="text-sm font-semibold text-gray-700 mt-0.5 capitalize">
                      {{ laborer.skill_level?.replace(/_/g, ' ') || 'Not set' }}
                    </dd>
                  </div>
                </div>
                <div class="flex items-start gap-2">
                  <div class="mt-0.5">
                    <svg class="h-4 w-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                         <!-- Peso Symbol (approximate or generic money icon) -->
                         <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Hourly Rate</dt>
                    <dd class="text-sm font-semibold text-gray-700 mt-0.5 truncate">
                      ₱{{ laborer.hourly_rate ? Number(laborer.hourly_rate).toFixed(2) : '0.00' }}
                    </dd>
                  </div>
                </div>
                 <div class="flex items-start gap-2 col-span-2" v-if="laborer.specialization">
                  <div class="mt-0.5">
                    <svg class="h-4 w-4 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                  </div>
                  <div class="flex-1 min-w-0">
                    <dt class="text-xs font-medium text-gray-500 uppercase tracking-wide">Specialization</dt>
                    <dd class="text-sm font-semibold text-gray-700 mt-0.5">
                      {{ laborer.specialization }}
                    </dd>
                  </div>
                </div>
              </dl>

              <!-- Footer -->
              <div class="mt-auto pt-4 border-t border-gray-200">
                <div class="text-xs text-gray-400 flex items-center gap-1">
                  <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                  </svg>
                  Hired {{ formatDate(laborer.hire_date) }}
                </div>
              </div>
            </div>
          </article>
        </div>
      </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const loading = ref(true)
const error = ref('')
const laborers = ref([])

const fetchLaborers = async () => {
  loading.value = true
  error.value = ''

  try {
    const { data } = await axios.get('/api/laborers')
    laborers.value = data.laborers || []
  } catch (err) {
    console.error('Failed to load laborers:', err)
    error.value = 'Unable to load laborers. Please try again.'
  } finally {
    loading.value = false
  }
}

const goToCreate = () => {
  router.push('/laborers/create')
}

const goToGroups = () => {
    router.push('/laborers/groups')
}

const goToEdit = (laborer) => {
    router.push(`/laborers/${laborer.id}/edit`)
}

const confirmDelete = async (laborer) => {
    if (confirm(`Are you sure you want to delete ${laborer.name}?`)) {
        try {
            await axios.delete(`/api/laborers/${laborer.id}`)
            fetchLaborers()
        } catch (err) {
            console.error('Failed to delete laborer:', err)
            alert('Failed to delete laborer.')
        }
    }
}

const getInitials = (name) => {
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
    return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
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
  fetchLaborers()
})
</script>
