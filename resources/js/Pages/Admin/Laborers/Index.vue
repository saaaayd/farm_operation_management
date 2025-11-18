<template>
  <div class="laborers-management-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Laborer Management</h1>
          <p class="text-gray-600 mt-2">Manage laborer database for farmer allocation</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="showCreateModal = true"
            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700"
          >
            + Add Laborer
          </button>
          <button
            @click="loadLaborers"
            :disabled="loading"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
          >
            {{ loading ? 'Loading...' : 'Refresh' }}
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
              placeholder="Search by name, email, phone..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @input="applyFilters"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="filters.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @change="applyFilters"
            >
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Farmer</label>
            <input
              v-model="filters.user_id"
              type="number"
              placeholder="Farmer ID"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              @input="applyFilters"
            />
          </div>
        </div>
      </div>

      <!-- Laborers Table -->
      <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div v-if="loading && !laborers.length" class="p-12 text-center text-gray-500">
          <div class="mx-auto mb-4 h-10 w-10 animate-spin rounded-full border-b-2 border-blue-600"></div>
          Loading laborers...
        </div>
        <div v-else-if="!loading && !laborers.length" class="p-12 text-center text-gray-500">
          <p class="text-lg">No laborers found</p>
        </div>
        <div v-else class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Contact</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Skill Level</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Hourly Rate</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="laborer in laborers" :key="laborer.id" class="hover:bg-gray-50">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ laborer.name }}</div>
                  <div v-if="laborer.specialization" class="text-xs text-gray-500">{{ laborer.specialization }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  <div>{{ laborer.phone || '-' }}</div>
                  <div v-if="laborer.email" class="text-xs text-gray-500">{{ laborer.email }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ laborer.skill_level || '-' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                  {{ formatCurrency(laborer.hourly_rate) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="{
                      'bg-green-100 text-green-800': laborer.status === 'active',
                      'bg-gray-100 text-gray-800': laborer.status !== 'active'
                    }"
                    class="px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ laborer.status || 'inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                  <button
                    @click="editLaborer(laborer)"
                    class="text-blue-600 hover:text-blue-900 mr-3"
                  >
                    Edit
                  </button>
                  <button
                    @click="deleteLaborer(laborer.id)"
                    class="text-red-600 hover:text-red-900"
                  >
                    Delete
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Create/Edit Modal -->
      <div
        v-if="showCreateModal || editingLaborer"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="closeModal"
      >
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
          <h3 class="text-xl font-semibold mb-4">
            {{ editingLaborer ? 'Edit Laborer' : 'Add New Laborer' }}
          </h3>
          <form @submit.prevent="saveLaborer" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Name *</label>
                <input
                  v-model="laborerForm.name"
                  type="text"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                <input
                  v-model="laborerForm.phone"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                <input
                  v-model="laborerForm.email"
                  type="email"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Skill Level</label>
                <input
                  v-model="laborerForm.skill_level"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Specialization</label>
                <input
                  v-model="laborerForm.specialization"
                  type="text"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hourly Rate</label>
                <input
                  v-model="laborerForm.hourly_rate"
                  type="number"
                  step="0.01"
                  min="0"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                <select
                  v-model="laborerForm.status"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Hire Date</label>
                <input
                  v-model="laborerForm.hire_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
              <input
                v-model="laborerForm.address"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Emergency Contact</label>
              <input
                v-model="laborerForm.emergency_contact"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Notes</label>
              <textarea
                v-model="laborerForm.notes"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
            </div>
            <div class="flex space-x-3 pt-4">
              <button
                type="submit"
                :disabled="saving"
                class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50"
              >
                {{ saving ? 'Saving...' : 'Save' }}
              </button>
              <button
                type="button"
                @click="closeModal"
                class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400"
              >
                Cancel
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { adminAPI } from '@/services/api'

const loading = ref(false)
const saving = ref(false)
const laborers = ref([])
const showCreateModal = ref(false)
const editingLaborer = ref(null)
const filters = ref({
  search: '',
  status: '',
  user_id: '',
})

const laborerForm = ref({
  name: '',
  phone: '',
  email: '',
  address: '',
  skill_level: '',
  specialization: '',
  hourly_rate: '',
  status: 'active',
  hire_date: '',
  emergency_contact: '',
  notes: '',
  user_id: null,
})

const formatCurrency = (amount) => {
  if (!amount) return '-'
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount)
}

const loadLaborers = async () => {
  loading.value = true
  try {
    const params = Object.fromEntries(
      Object.entries(filters.value).filter(([_, v]) => v !== '')
    )
    const response = await adminAPI.getLaborers(params)
    laborers.value = response.data.data || []
  } catch (error) {
    console.error('Error loading laborers:', error)
    alert('Failed to load laborers')
  } finally {
    loading.value = false
  }
}

const applyFilters = () => {
  loadLaborers()
}

const editLaborer = (laborer) => {
  editingLaborer.value = laborer
  laborerForm.value = { ...laborer }
  showCreateModal.value = true
}

const saveLaborer = async () => {
  saving.value = true
  try {
    const data = { ...laborerForm.value }
    if (editingLaborer.value) {
      await adminAPI.updateLaborer(editingLaborer.value.id, data)
      alert('Laborer updated successfully')
    } else {
      await adminAPI.createLaborer(data)
      alert('Laborer created successfully')
    }
    closeModal()
    loadLaborers()
  } catch (error) {
    console.error('Error saving laborer:', error)
    alert(error.response?.data?.message || 'Failed to save laborer')
  } finally {
    saving.value = false
  }
}

const deleteLaborer = async (id) => {
  if (!confirm('Are you sure you want to delete this laborer?')) return
  
  try {
    await adminAPI.deleteLaborer(id)
    alert('Laborer deleted successfully')
    loadLaborers()
  } catch (error) {
    console.error('Error deleting laborer:', error)
    alert(error.response?.data?.message || 'Failed to delete laborer')
  }
}

const closeModal = () => {
  showCreateModal.value = false
  editingLaborer.value = null
  laborerForm.value = {
    name: '',
    phone: '',
    email: '',
    address: '',
    skill_level: '',
    specialization: '',
    hourly_rate: '',
    status: 'active',
    hire_date: '',
    emergency_contact: '',
    notes: '',
    user_id: null,
  }
}

onMounted(() => {
  loadLaborers()
})
</script>

<style scoped>
.laborers-management-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>

