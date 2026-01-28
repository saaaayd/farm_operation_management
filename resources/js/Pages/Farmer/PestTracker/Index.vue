<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Pest & Disease Tracker</h1>
          <p class="text-gray-500 mt-1">Monitor and manage pest incidents across your fields.</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="flex items-center gap-2 bg-red-600 text-white px-5 py-2.5 rounded-lg hover:bg-red-700 transition-colors shadow-sm font-medium"
        >
          <span class="text-xl leading-none">+</span> Report Incident
        </button>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl shadow p-5">
          <p class="text-sm text-gray-500">Total Incidents</p>
          <p class="text-2xl font-bold text-gray-800">{{ stats.total }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-red-500">
          <p class="text-sm text-gray-500">Active</p>
          <p class="text-2xl font-bold text-red-600">{{ stats.active }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-yellow-500">
          <p class="text-sm text-gray-500">Treated</p>
          <p class="text-2xl font-bold text-yellow-600">{{ stats.treated }}</p>
        </div>
        <div class="bg-white rounded-xl shadow p-5 border-l-4 border-green-500">
          <p class="text-sm text-gray-500">Resolved</p>
          <p class="text-2xl font-bold text-green-600">{{ stats.resolved }}</p>
        </div>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-xl shadow p-4 mb-6 flex flex-wrap gap-4">
        <select v-model="filter.status" @change="loadData" class="px-3 py-2 border rounded-lg">
          <option value="">All Status</option>
          <option value="active">Active</option>
          <option value="treated">Treated</option>
          <option value="resolved">Resolved</option>
        </select>
        <select v-model="filter.severity" @change="loadData" class="px-3 py-2 border rounded-lg">
          <option value="">All Severity</option>
          <option value="low">Low</option>
          <option value="medium">Medium</option>
          <option value="high">High</option>
          <option value="critical">Critical</option>
        </select>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-red-600 mx-auto"></div>
      </div>

      <!-- Incidents List -->
      <div v-else-if="incidents.length > 0" class="space-y-4">
        <div v-for="incident in incidents" :key="incident.id"
          class="bg-white rounded-xl shadow p-6 hover:shadow-lg transition-shadow"
        >
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <span class="text-2xl">{{ getTypeIcon(incident.pest_type) }}</span>
                <h3 class="font-semibold text-gray-900">{{ incident.pest_name }}</h3>
                <span :class="getSeverityClass(incident.severity)" class="px-2 py-1 rounded-full text-xs font-medium">
                  {{ incident.severity }}
                </span>
                <span :class="getStatusClass(incident.status)" class="px-2 py-1 rounded-full text-xs font-medium capitalize">
                  {{ incident.status }}
                </span>
              </div>
              <div class="text-sm text-gray-600 space-y-1">
                <p><strong>Field:</strong> {{ incident.planting?.field?.name || 'N/A' }}</p>
                <p><strong>Detected:</strong> {{ formatDate(incident.detected_date) }}</p>
                <p v-if="incident.symptoms"><strong>Symptoms:</strong> {{ incident.symptoms }}</p>
                <p v-if="incident.treatment_applied" class="text-green-600">
                  <strong>Treatment:</strong> {{ incident.treatment_applied }}
                </p>
              </div>
            </div>
            <div class="flex flex-col gap-2">
              <button v-if="incident.status === 'active'"
                @click="markTreated(incident)"
                class="px-4 py-2 bg-yellow-500 text-white rounded-lg text-sm hover:bg-yellow-600"
              >Mark Treated</button>
              <button v-if="incident.status === 'treated'"
                @click="markResolved(incident)"
                class="px-4 py-2 bg-green-600 text-white rounded-lg text-sm hover:bg-green-700"
              >Mark Resolved</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12 bg-white rounded-xl">
        <div class="text-5xl mb-4">🐛</div>
        <h3 class="text-lg font-medium text-gray-900">No incidents recorded</h3>
        <p class="text-gray-500 mt-1">Click "Report Incident" to log a pest or disease issue</p>
      </div>

      <!-- Create Modal -->
      <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
          <h2 class="text-xl font-bold text-gray-900 mb-4">Report Pest Incident</h2>
          
          <form @submit.prevent="submitIncident">
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Pest Type</label>
                <select v-model="form.pest_type" required class="w-full px-3 py-2 border rounded-lg">
                  <option value="">Select type</option>
                  <option value="insect">Insect</option>
                  <option value="disease">Disease</option>
                  <option value="weed">Weed</option>
                  <option value="rodent">Rodent</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Severity</label>
                <select v-model="form.severity" required class="w-full px-3 py-2 border rounded-lg">
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                  <option value="critical">Critical</option>
                </select>
              </div>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Pest/Disease Name</label>
              <input v-model="form.pest_name" type="text" required
                class="w-full px-3 py-2 border rounded-lg" placeholder="e.g., Rice Stem Borer" />
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Planting</label>
              <select v-model="form.planting_id" required class="w-full px-3 py-2 border rounded-lg">
                <option value="">Select planting</option>
                <option v-for="p in plantings" :key="p.id" :value="p.id">
                  {{ p.field?.name }} - {{ p.rice_variety?.name }}
                </option>
              </select>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Detected Date</label>
                <input v-model="form.detected_date" type="date" required class="w-full px-3 py-2 border rounded-lg" />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Affected Area (ha)</label>
                <input v-model="form.affected_area" type="number" step="0.01" class="w-full px-3 py-2 border rounded-lg" />
              </div>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Symptoms</label>
              <textarea v-model="form.symptoms" rows="2" class="w-full px-3 py-2 border rounded-lg"
                placeholder="Describe observed symptoms..."></textarea>
            </div>

            <div class="flex gap-3">
              <button type="button" @click="showCreateModal = false"
                class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
              >Cancel</button>
              <button type="submit" :disabled="submitting"
                class="flex-1 px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50"
              >{{ submitting ? 'Saving...' : 'Save Incident' }}</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import axios from 'axios'

const loading = ref(true)
const incidents = ref([])
const stats = ref({ total: 0, active: 0, treated: 0, resolved: 0 })
const plantings = ref([])
const showCreateModal = ref(false)
const submitting = ref(false)
const filter = ref({ status: '', severity: '' })

const form = ref({
  pest_type: '',
  pest_name: '',
  severity: 'medium',
  planting_id: '',
  detected_date: new Date().toISOString().split('T')[0],
  affected_area: null,
  symptoms: '',
})

const loadData = async () => {
  loading.value = true
  try {
    const params = {}
    if (filter.value.status) params.status = filter.value.status
    if (filter.value.severity) params.severity = filter.value.severity

    const response = await axios.get('/api/pest-incidents', { params })
    incidents.value = response.data.incidents?.data || []
    stats.value = response.data.stats || { total: 0, active: 0, treated: 0, resolved: 0 }
  } catch (error) {
    console.error('Failed to load incidents:', error)
  } finally {
    loading.value = false
  }
}

const loadPlantings = async () => {
  try {
    const response = await axios.get('/api/plantings')
    plantings.value = response.data.plantings || response.data || []
  } catch (error) {
    console.error('Failed to load plantings:', error)
  }
}

const submitIncident = async () => {
  submitting.value = true
  try {
    await axios.post('/api/pest-incidents', form.value)
    showCreateModal.value = false
    resetForm()
    loadData()
  } catch (error) {
    alert(error.response?.data?.message || 'Failed to save incident')
  } finally {
    submitting.value = false
  }
}

const markTreated = async (incident) => {
  const treatment = prompt('What treatment was applied?')
  if (!treatment) return
  try {
    await axios.put(`/api/pest-incidents/${incident.id}`, {
      status: 'treated',
      treatment_applied: treatment,
      treatment_date: new Date().toISOString().split('T')[0]
    })
    loadData()
  } catch (error) {
    alert('Failed to update')
  }
}

const markResolved = async (incident) => {
  try {
    await axios.put(`/api/pest-incidents/${incident.id}`, { status: 'resolved' })
    loadData()
  } catch (error) {
    alert('Failed to update')
  }
}

const resetForm = () => {
  form.value = {
    pest_type: '',
    pest_name: '',
    severity: 'medium',
    planting_id: '',
    detected_date: new Date().toISOString().split('T')[0],
    affected_area: null,
    symptoms: '',
  }
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}

const getTypeIcon = (type) => {
  const icons = { insect: '🐛', disease: '🦠', weed: '🌿', rodent: '🐀', other: '⚠️' }
  return icons[type] || '❓'
}

const getSeverityClass = (severity) => {
  const classes = {
    low: 'bg-blue-100 text-blue-800',
    medium: 'bg-yellow-100 text-yellow-800',
    high: 'bg-orange-100 text-orange-800',
    critical: 'bg-red-100 text-red-800',
  }
  return classes[severity] || 'bg-gray-100 text-gray-800'
}

const getStatusClass = (status) => {
  const classes = {
    active: 'bg-red-100 text-red-800',
    treated: 'bg-yellow-100 text-yellow-800',
    resolved: 'bg-green-100 text-green-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

onMounted(() => {
  loadData()
  loadPlantings()
})
</script>
