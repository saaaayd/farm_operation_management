<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <button
            type="button"
            @click="router.push('/laborers')"
            class="inline-flex items-center text-sm font-medium text-emerald-700 hover:text-emerald-900"
          >
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Laborers
          </button>
          <h1 class="mt-4 text-3xl font-bold text-gray-900">Edit Laborer</h1>
          <p class="mt-2 text-base text-gray-600 max-w-2xl">
            Update laborer details.
          </p>
        </div>
      </div>

      <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-800">
        {{ error }}
      </div>

        <div v-if="initialLoading" class="flex justify-center py-12">
             <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-600"></div>
        </div>

      <form v-else @submit.prevent="submitLaborer" class="space-y-8">
        <!-- Personal Information -->
        <section class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100 space-y-6">
          <div class="flex items-center mb-4">
            <div class="h-12 w-12 rounded-xl bg-emerald-100 flex items-center justify-center mr-4">
              <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <div>
              <h2 class="text-xl font-semibold text-gray-900">Personal Information</h2>
              <p class="text-sm text-gray-600">Basic details about the laborer.</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Full Name *</label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                placeholder="e.g., Juan Dela Cruz"
              />
            </div>
             <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Phone Number *</label>
              <input
                v-model="form.phone"
                type="text"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                placeholder="e.g., 09123456789"
              />
            </div>
             <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Email Address</label>
              <input
                v-model="form.email"
                type="email"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                placeholder="e.g., juan@example.com"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Address</label>
              <input
                 v-model="form.address"
                 type="text"
                 class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                 placeholder="Barangay, City, Province"
               />
            </div>
             <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Emergency Contact Name</label>
              <input
                 v-model="form.emergency_contact_name"
                 type="text"
                 class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                 placeholder="Name"
               />
            </div>
             <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Emergency Contact Phone</label>
              <input
                 v-model="form.emergency_contact_phone"
                 type="text"
                 class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                 placeholder="Phone Number"
               />
            </div>
          </div>
        </section>

        <!-- Employment Details -->
        <section class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100 space-y-6">
          <div class="flex items-center mb-4">
            <div class="h-12 w-12 rounded-xl bg-blue-100 flex items-center justify-center mr-4">
              <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
              </svg>
            </div>
            <div>
              <h2 class="text-xl font-semibold text-gray-900">Employment Details</h2>
              <p class="text-sm text-gray-600">Skills, rates, and status.</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Skill Level *</label>
              <select
                v-model="form.skill_level"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              >
                <option value="">Select Level</option>
                <option value="beginner">Beginner</option>
                <option value="intermediate">Intermediate</option>
                <option value="advanced">Advanced</option>
                <option value="expert">Expert</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Specialization</label>
              <input
                v-model="form.specialization"
                type="text"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                placeholder="e.g., Harvester, Equipment Operator"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Status *</label>
              <select
                v-model="form.status"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              >
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="on_leave">On Leave</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Rate Type *</label>
              <select
                v-model="form.rate_type"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              >
                <option value="hourly">Hourly</option>
                <option value="daily">Daily</option>
                <option value="per_job">Per Job</option>
              </select>
            </div>
            <div v-if="form.rate_type !== 'per_job'">
              <label class="block text-sm font-semibold text-gray-700 mb-2">Rate (₱) *</label>
              <input
                v-model.number="form.rate"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                placeholder="0.00"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Hire Date *</label>
               <input
                v-model="form.hire_date"
                type="date"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              />
            </div>
          </div>

          <div>
             <label class="block text-sm font-semibold text-gray-700 mb-2">Additional Notes</label>
            <textarea
              v-model="form.notes"
              rows="3"
               class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition resize-none"
              placeholder="Any other notes about the laborer."
            ></textarea>
          </div>
        </section>

        <!-- Group Assignment -->
        <section class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100 space-y-6">
          <div class="flex items-center mb-4">
            <div class="h-12 w-12 rounded-xl bg-purple-100 flex items-center justify-center mr-4">
              <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div>
              <h2 class="text-xl font-semibold text-gray-900">Group Assignment</h2>
              <p class="text-sm text-gray-600">Assign this laborer to teams.</p>
            </div>
          </div>

          <div v-if="loadingGroups" class="text-sm text-gray-500">Loading groups...</div>
          <div v-else-if="availableGroups.length === 0" class="text-sm text-gray-500 italic">No groups available. Create groups first.</div>
          <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
             <label
                v-for="group in availableGroups"
                :key="group.id"
                class="flex items-center space-x-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50 hover:border-emerald-300 transition-colors"
                :class="{ 'border-emerald-500 bg-emerald-50': form.groups.includes(group.id) }"
              >
                <input
                  type="checkbox"
                  :value="group.id"
                  v-model="form.groups"
                  class="rounded text-emerald-600 focus:ring-emerald-500 h-5 w-5 border-gray-300"
                >
                <div class="flex items-center space-x-2">
                    <span class="w-3 h-3 rounded-full" :style="{ backgroundColor: group.color }"></span>
                    <span class="text-sm font-medium text-gray-700">{{ group.name }}</span>
                </div>
              </label>
          </div>
        </section>


        <div class="flex flex-col sm:flex-row gap-4 justify-end pt-4">
          <button
            type="button"
            @click="router.push('/laborers')"
            class="inline-flex items-center justify-center px-6 py-3 rounded-xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="inline-flex items-center justify-center px-8 py-3 rounded-xl font-semibold text-white bg-gradient-to-r from-emerald-600 to-green-600 shadow-lg hover:shadow-xl hover:from-emerald-700 hover:to-green-700 disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ loading ? 'Saving...' : 'Update Laborer' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'

const router = useRouter()
const route = useRoute()

const loading = ref(false)
const initialLoading = ref(true)
const error = ref('')
const availableGroups = ref([])
const loadingGroups = ref(false)

const form = reactive({
  name: '',
  phone: '',
  email: '',
  address: '',
  skill_level: '',
  specialization: '',
  rate: '',
  rate_type: 'hourly',
  status: 'active',
  hire_date: '',
  emergency_contact_name: '',
  emergency_contact_phone: '',
  notes: '',
  groups: [],
})

const fetchGroups = async () => {
    loadingGroups.value = true
    try {
        const { data } = await axios.get('/api/laborers/groups')
        availableGroups.value = data.groups || []
    } catch (err) {
        console.error('Failed to load groups:', err)
    } finally {
        loadingGroups.value = false
    }
}

const fetchLaborer = async () => {
    initialLoading.value = true
    error.value = ''
    try {
        const { data } = await axios.get(`/api/laborers/${route.params.id}`)
        const laborer = data.laborer
        
        form.name = laborer.name || ''
        form.phone = laborer.phone || ''
        form.email = laborer.email || ''
        form.address = laborer.address || ''
        form.skill_level = laborer.skill_level || ''
        form.specialization = laborer.specialization || ''
        form.rate = laborer.rate || ''
        form.rate_type = laborer.rate_type || 'hourly'
        form.status = laborer.status || 'active'
        form.hire_date = laborer.hire_date || ''
        form.emergency_contact_name = laborer.emergency_contact_name || ''
        form.emergency_contact_phone = laborer.emergency_contact_phone || ''
        form.notes = laborer.notes || ''
        form.groups = laborer.groups ? laborer.groups.map(g => g.id) : []
        
    } catch (err) {
        console.error('Failed to fetch laborer:', err)
        error.value = 'Failed to load laborer details.'
    } finally {
        initialLoading.value = false
    }
}

const submitLaborer = async () => {
  loading.value = true
  error.value = ''

  try {
    await axios.put(`/api/laborers/${route.params.id}`, form)
    router.push('/laborers')
  } catch (err) {
    console.error('Failed to update laborer:', err)
    error.value = err.response?.data?.message || 'Failed to update laborer. Please try again.'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
    fetchGroups()
    fetchLaborer()
})
</script>
