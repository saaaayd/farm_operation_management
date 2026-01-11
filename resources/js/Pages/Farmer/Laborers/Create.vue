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
          <h1 class="mt-4 text-3xl font-bold text-gray-900">Add New Laborer</h1>
          <p class="mt-2 text-base text-gray-600 max-w-2xl">
            Input laborer details to assign them to tasks and manage your workforce.
          </p>
        </div>
      </div>

      <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-800">
        {{ error }}
      </div>

      <form @submit.prevent="submitLaborer" class="space-y-8">
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

          <!-- Profile Picture Upload -->
          <div class="flex items-center gap-6 mb-6 pb-6 border-b border-gray-200">
            <div class="relative">
              <div v-if="photoPreview" class="h-24 w-24 rounded-full shadow-lg overflow-hidden">
                <img :src="photoPreview" alt="Profile Picture Preview" class="h-full w-full object-cover" />
              </div>
              <div v-else class="h-24 w-24 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-full flex items-center justify-center shadow-lg text-white font-bold text-2xl">
                {{ getInitials(form.name) }}
              </div>
              <label class="absolute bottom-0 right-0 h-8 w-8 bg-emerald-600 rounded-full flex items-center justify-center cursor-pointer hover:bg-emerald-700 transition-colors shadow-md">
                <svg class="h-4 w-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <input type="file" accept="image/jpeg,image/png,image/webp" class="hidden" @change="handlePhotoSelect" />
              </label>
            </div>
            <div class="flex-1">
              <h3 class="text-sm font-semibold text-gray-700 mb-1">Profile Picture</h3>
              <p class="text-xs text-gray-500 mb-3">Upload a photo to easily identify this laborer. JPG, PNG or WebP, max 2MB.</p>
              <div class="flex gap-2">
                <button
                  v-if="photoPreview"
                  type="button"
                  @click="cancelPhotoSelect"
                  class="inline-flex items-center px-3 py-1.5 text-xs font-medium rounded-lg border border-gray-300 text-gray-700 hover:bg-gray-50"
                >
                  Remove
                </button>
                <span v-if="photoPreview" class="text-xs text-emerald-600 flex items-center">
                  <svg class="h-3 w-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                  </svg>
                  Photo will be uploaded when you save
                </span>
              </div>
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
                <option value="daily">Daily</option>
                <option value="per_job">Per Job</option>
                <option value="share">Share</option>
              </select>
            </div>
            <div v-if="form.rate_type !== 'per_job'">
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                {{ form.rate_type === 'share' ? 'Share Percentage (%) *' : 'Rate (₱) *' }}
              </label>
              <input
                v-model.number="form.rate"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                :placeholder="form.rate_type === 'share' ? 'e.g., 10' : '0.00'"
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
            {{ loading ? 'Saving...' : 'Add Laborer' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const loading = ref(false)
const error = ref('')
const availableGroups = ref([])
const loadingGroups = ref(false)
const photoPreview = ref(null)
const selectedPhoto = ref(null)

const form = reactive({
  name: '',
  phone: '',
  email: '',
  address: '',
  skill_level: '',
  specialization: '',
  rate: '',
  rate_type: 'daily',
  status: 'active',
  hire_date: new Date().toISOString().split('T')[0],
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

const submitLaborer = async () => {
  loading.value = true
  error.value = ''

  try {
    const { data } = await axios.post('/api/laborers', form)
    const laborerId = data.laborer.id
    
    // Upload photo if selected
    if (selectedPhoto.value && laborerId) {
      try {
        const formData = new FormData()
        formData.append('photo', selectedPhoto.value)
        await axios.post(`/api/laborers/${laborerId}/photo`, formData, {
          headers: { 'Content-Type': 'multipart/form-data' }
        })
      } catch (photoErr) {
        console.error('Failed to upload photo:', photoErr)
        // Continue anyway - laborer was created
      }
    }
    
    router.push('/laborers')
  } catch (err) {
    console.error('Failed to create laborer:', err)
    error.value = err.response?.data?.message || 'Failed to create laborer. Please try again.'
  } finally {
    loading.value = false
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

const handlePhotoSelect = (event) => {
    const file = event.target.files[0]
    if (file) {
        // Revoke old preview URL to prevent memory leak
        if (photoPreview.value) {
            URL.revokeObjectURL(photoPreview.value)
        }
        selectedPhoto.value = file
        photoPreview.value = URL.createObjectURL(file)
    }
}

const cancelPhotoSelect = () => {
    // Revoke preview URL to prevent memory leak
    if (photoPreview.value) {
        URL.revokeObjectURL(photoPreview.value)
    }
    selectedPhoto.value = null
    photoPreview.value = null
}

onMounted(() => {
    fetchGroups()
})
</script>
