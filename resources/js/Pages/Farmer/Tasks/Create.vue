<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">Schedule Farm Task</h1>
            <p class="text-sm text-gray-500">
              Plan upcoming farm work and keep your team aligned.
            </p>
          </div>
          <router-link
            to="/tasks"
            class="text-sm text-gray-600 hover:text-gray-800"
          >
            Back to tasks
          </router-link>
        </div>
      </div>
    </header>

    <main class="px-4 sm:px-6 lg:px-8 py-8">
      <div class="max-w-3xl mx-auto bg-white rounded-2xl shadow border border-gray-100">
        <div class="px-6 py-5 border-b border-gray-100">
          <h2 class="text-lg font-semibold text-gray-900">Task details</h2>
          <p class="text-sm text-gray-500 mt-1">
            Specify what needs to be done, when, and which planting it supports.
          </p>
        </div>

        <form @submit.prevent="submitTask" class="px-6 py-6 space-y-8">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
              <label class="form-label">Task Type *</label>
              <select
                v-model="form.task_type"
                class="form-input"
                required
              >
                <option value="">Select task type</option>
                <option
                  v-for="option in taskTypeOptions"
                  :key="option.value"
                  :value="option.value"
                >
                  {{ option.label }}
                </option>
              </select>
              <p v-if="errors.task_type" class="form-error">{{ errors.task_type[0] }}</p>
            </div>

            <div class="space-y-2">
              <label class="form-label">Linked Planting *</label>
              <template v-if="plantings.length">
                <select
                  v-model="form.planting_id"
                  class="form-input"
                  required
                >
                  <option value="">Select planting</option>
                  <option
                    v-for="planting in plantings"
                    :key="planting.id"
                    :value="planting.id"
                  >
                    {{ formatPlantingOption(planting) }}
                  </option>
                </select>
                <p v-if="errors.planting_id" class="form-error">{{ errors.planting_id[0] }}</p>
              </template>
              <template v-else>
                <div class="rounded-md border border-dashed border-gray-300 bg-gray-50 px-4 py-5 text-sm text-gray-600">
                  No active plantings found. Create one to schedule work:
                  <router-link
                    to="/plantings/create"
                    class="ml-2 inline-flex items-center text-green-600 hover:text-green-700 font-medium"
                  >
                    Create planting
                  </router-link>
                </div>
              </template>
            </div>

            <div class="space-y-2">
              <label class="form-label">Due Date *</label>
              <input
                v-model="form.due_date"
                type="date"
                class="form-input"
                required
              />
              <p v-if="errors.due_date" class="form-error">{{ errors.due_date[0] }}</p>
            </div>

            <div class="space-y-2">
              <label class="form-label">Assign to (optional)</label>
              <select
                v-model="form.assigned_to"
                class="form-input"
                :disabled="loadingLaborers"
              >
                <option value="">No assignment</option>
                <option
                  v-for="laborer in laborers"
                  :key="laborer.id"
                  :value="laborer.id"
                >
                  {{ laborer.name }}{{ laborer.phone ? ` (${laborer.phone})` : '' }}
                </option>
              </select>
              <p v-if="loadingLaborers" class="text-xs text-gray-400">
                Loading laborers...
              </p>
              <p v-else-if="laborers.length === 0" class="text-xs text-gray-400">
                No laborers available. You can assign a laborer later or create one in the Laborers section.
              </p>
              <p v-else class="text-xs text-gray-400">
                Select a laborer to assign this task to, or leave blank to assign later.
              </p>
            </div>
          </div>

          <div class="space-y-2">
            <label class="form-label">Description *</label>
            <textarea
              v-model="form.description"
              rows="5"
              class="form-input"
              placeholder="Example: Apply nitrogen fertilizer (50kg/ha) after irrigation. Ensure field is drained before entry."
              required
            ></textarea>
            <p v-if="errors.description" class="form-error">{{ errors.description[0] }}</p>
          </div>

          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-end gap-3 pt-4 border-t border-gray-100">
            <router-link
              to="/tasks"
              class="px-4 py-2 text-sm font-medium text-gray-600 hover:text-gray-800"
            >
              Cancel
            </router-link>
            <button
              type="submit"
              :disabled="submitting"
              class="inline-flex items-center justify-center px-4 py-2 text-sm font-semibold rounded-md bg-green-600 text-white hover:bg-green-700 disabled:opacity-50"
            >
              <svg
                v-if="submitting"
                class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              {{ submitting ? 'Saving...' : 'Create Task' }}
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed, reactive, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFarmStore } from '@/stores/farm'
import { buildTaskTypeOptions } from '@/utils/taskTypes'
import { laborAPI } from '@/services/api'

const router = useRouter()
const farmStore = useFarmStore()

const submitting = ref(false)
const errors = ref({})
const loadingLaborers = ref(false)
const laborers = ref([])

const form = reactive({
  task_type: '',
  planting_id: '',
  due_date: formatDateForInput(new Date()),
  description: '',
  assigned_to: ''
})

const plantings = computed(() => farmStore.plantings || [])
const taskTypeOptions = computed(() =>
  buildTaskTypeOptions(farmStore.tasks || [], { includeBase: true })
)

const formatPlantingOption = (planting) => {
  const crop = planting.crop_type || 'Planting'
  const field = planting.field?.name ? ` • ${planting.field.name}` : ''
  const due = planting.expected_harvest_date
    ? ` • Harvest ${new Date(planting.expected_harvest_date).toLocaleDateString()}`
    : ''
  return `${crop}${field}${due}`
}

function formatDateForInput(date) {
  if (!(date instanceof Date) || Number.isNaN(date.getTime())) return ''
  const year = date.getFullYear()
  const month = `${date.getMonth() + 1}`.padStart(2, '0')
  const day = `${date.getDate()}`.padStart(2, '0')
  return `${year}-${month}-${day}`
}

const submitTask = async () => {
  submitting.value = true
  errors.value = {}

  try {
    // Validate required fields before submitting
    if (!form.task_type || !form.planting_id || !form.due_date || !form.description.trim()) {
      errors.value = {
        ...(form.task_type ? {} : { task_type: ['Task type is required'] }),
        ...(form.planting_id ? {} : { planting_id: ['Linked planting is required'] }),
        ...(form.due_date ? {} : { due_date: ['Due date is required'] }),
        ...(form.description.trim() ? {} : { description: ['Description is required'] }),
      }
      submitting.value = false
      return
    }

    // Ensure planting_id is a valid positive integer
    const plantingId = Number(form.planting_id)
    if (isNaN(plantingId) || plantingId <= 0) {
      errors.value = { planting_id: ['Please select a valid planting'] }
      submitting.value = false
      return
    }

    const payload = {
      task_type: form.task_type,
      planting_id: plantingId,
      due_date: form.due_date,
      description: form.description.trim(),
    }

    // Only include assigned_to if a laborer is selected
    if (form.assigned_to && form.assigned_to.toString().trim()) {
      const assignedToNum = Number(form.assigned_to)
      if (!isNaN(assignedToNum) && assignedToNum > 0) {
        payload.assigned_to = assignedToNum
      }
    }

    await farmStore.createTask(payload)
    router.push('/tasks')
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }
  } finally {
    submitting.value = false
  }
}

const loadLaborers = async () => {
  try {
    loadingLaborers.value = true
    const response = await laborAPI.getLaborers()
    laborers.value = response.data.laborers || response.data || []
  } catch (error) {
    console.error('Failed to load laborers:', error)
    laborers.value = []
  } finally {
    loadingLaborers.value = false
  }
}

onMounted(async () => {
  const loaders = []

  if (!plantings.value.length) {
    loaders.push(farmStore.fetchPlantings())
  }

  if (!(farmStore.tasks && farmStore.tasks.length)) {
    loaders.push(farmStore.fetchTasks())
  }

  // Load laborers for assignment
  loadLaborers()

  if (!loaders.length) {
    return
  }

  try {
    await Promise.all(loaders)
  } catch (error) {
    console.error('Failed to load data for task creation:', error)
  }
})
</script>

<style scoped>
.form-label {
  display: block;
  font-size: 0.875rem;
  font-weight: 500;
  color: #374151;
}

.form-input {
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  font-size: 0.875rem;
}

.form-input:focus {
  outline: none;
  border-color: #10b981;
  box-shadow: 0 0 0 1px #10b981;
}

.form-error {
  margin-top: 0.25rem;
  font-size: 0.75rem;
  color: #dc2626;
}
</style>

