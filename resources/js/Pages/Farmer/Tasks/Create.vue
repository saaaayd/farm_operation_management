<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="w-full mx-auto space-y-8">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <button
            type="button"
            @click="router.push('/tasks')"
            class="inline-flex items-center text-sm font-medium text-emerald-700 hover:text-emerald-900 transition-colors"
          >
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Tasks
          </button>
          <h1 class="mt-4 text-3xl font-bold text-gray-900">New Task</h1>
          <p class="mt-2 text-base text-gray-600 max-w-2xl">
            Schedule operations and assign laborers.
          </p>
        </div>
      </div>

      <form @submit.prevent="submitTask" class="space-y-6">
        
        <!-- Top Grid: General Info & Timing -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Card 1: Core Details -->
          <div class="lg:col-span-2 space-y-6">
            <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
               <div class="p-6 border-b border-gray-100 bg-gray-50/30 flex items-center gap-3">
                 <div class="h-8 w-8 rounded-lg bg-green-100 flex items-center justify-center text-green-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                 </div>
                 <h2 class="font-semibold text-gray-900">Task Information</h2>
               </div>
               
               <div class="p-6 space-y-6">
                  <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="space-y-2">
                       <label class="form-label">Operation Type</label>
                       <select v-model="form.task_type" class="form-input" required>
                         <option value="" disabled>Select operation...</option>
                         <option v-for="option in taskTypeOptions" :key="option.value" :value="option.value">
                           {{ option.label }}
                         </option>
                       </select>
                       <p v-if="errors.task_type" class="form-error">{{ errors.task_type[0] }}</p>
                    </div>

                    <div class="space-y-2">
                       <label class="form-label">Due Date</label>
                       <input v-model="form.due_date" type="date" class="form-input" required />
                       <p v-if="errors.due_date" class="form-error">{{ errors.due_date[0] }}</p>
                    </div>
                  </div>

                  <div class="space-y-2">
                    <label class="form-label">Target Planting</label>
                    <div class="relative">
                      <select v-model="form.planting_id" class="form-input appearance-none" required>
                        <option value="" disabled>Select active planting...</option>
                        <option v-for="planting in plantings" :key="planting.id" :value="planting.id">
                           {{ formatPlantingOption(planting) }}
                        </option>
                      </select>
                      <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-gray-400">
                        <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
                      </div>
                    </div>
                     <p v-if="errors.planting_id" class="form-error">{{ errors.planting_id[0] }}</p>
                     <div v-if="!plantings.length" class="mt-2 text-sm text-amber-600 bg-amber-50 p-2 rounded-md">
                        No active plantings. <router-link to="/plantings/create" class="underline font-medium">Create one now</router-link>.
                     </div>
                  </div>

                  <div class="space-y-2">
                    <label class="form-label">Description & Instructions</label>
                    <textarea 
                      v-model="form.description" 
                      rows="4" 
                      class="form-input resize-none" 
                      placeholder="Describe the task details, safety precautions, and specific requirements..."
                      required
                    ></textarea>
                    <p v-if="errors.description" class="form-error">{{ errors.description[0] }}</p>
                  </div>
               </div>
            </section>
          </div>

          <!-- Card 2: Assignment & Payment (Side Panel) -->
          <div class="space-y-6">
             <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-gray-50/30 flex items-center gap-3">
                   <div class="h-8 w-8 rounded-lg bg-blue-100 flex items-center justify-center text-blue-600">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                   </div>
                   <h2 class="font-semibold text-gray-900">Assignment</h2>
                </div>

                <div class="p-5 space-y-5">
                   <!-- Assignment Type Tabs -->
                   <div class="flex p-1 bg-gray-100 rounded-lg">
                      <button 
                        type="button"
                        @click="form.assignment_type = 'individual'"
                        class="flex-1 py-1.5 text-sm font-medium rounded-md transition-all shadow-sm"
                        :class="form.assignment_type === 'individual' ? 'bg-white text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                      >
                        Individual
                      </button>
                      <button 
                        type="button"
                        @click="form.assignment_type = 'group'"
                        class="flex-1 py-1.5 text-sm font-medium rounded-md transition-all shadow-sm"
                        :class="form.assignment_type === 'group' ? 'bg-white text-gray-900' : 'text-gray-500 hover:text-gray-700'"
                      >
                        Group
                      </button>
                   </div>

                   <div v-if="form.assignment_type === 'individual'">
                      <label class="form-label mb-2">Assign Laborer</label>
                      <select v-model="form.assigned_to" class="form-input" :disabled="loadingLaborers">
                        <option value="">Unassigned</option>
                        <option v-for="laborer in laborers" :key="laborer.id" :value="laborer.id">
                           {{ laborer.name }}
                        </option>
                      </select>
                   </div>
                   
                   <div v-if="form.assignment_type === 'group'">
                      <label class="form-label mb-2">Assign Group</label>
                      <select v-model="form.laborer_group_id" class="form-input" :disabled="loadingLaborers">
                        <option value="">Unassigned</option>
                        <option v-for="group in groups" :key="group.id" :value="group.id">
                           {{ group.name }} ({{ group.laborers_count }})
                        </option>
                      </select>
                   </div>
                   
                   <p v-if="loadingLaborers" class="text-xs text-center text-gray-400">Updating roster...</p>
                </div>
             </section>

             <!-- Payment Structure Card -->
             <section class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-gray-50/30 flex items-center gap-3">
                   <div class="h-8 w-8 rounded-lg bg-orange-100 flex items-center justify-center text-orange-600">
                      <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                   </div>
                   <h2 class="font-semibold text-gray-900">Payment</h2>
                </div>

                 <div class="p-5 space-y-4">
                  <!-- Payment Type Radio -->
                  <div class="space-y-3">
                     <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all hover:bg-gray-50" :class="form.payment_type === 'wage' ? 'border-green-500 bg-green-50/30 ring-1 ring-green-500' : 'border-gray-200'">
                        <input type="radio" v-model="form.payment_type" value="wage" class="form-radio text-green-600 h-4 w-4">
                        <div class="ml-3">
                           <span class="block text-sm font-medium text-gray-900">Standard Wage</span>
                           <span class="block text-xs text-gray-500">Pay by day or task rate</span>
                        </div>
                     </label>

                     <!-- Wage Amount Input -->
                     <transition enter-active-class="transition ease-out duration-200" leave-active-class="transition ease-in duration-150" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                        <div v-if="form.payment_type === 'wage'" class="pt-2">
                           <label class="form-label mb-2">Wage Amount</label>
                           <div class="relative">
                              <span class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-500 font-medium">₱</span>
                              <input
                               v-model="form.wage_amount"
                               type="number"
                               min="0"
                               step="0.01"
                               class="form-input pl-7"
                               placeholder="0.00"
                              />
                           </div>
                           <p class="text-xs text-gray-500 mt-2">Defaults to laborer's standard rate.</p>
                        </div>
                     </transition>
                     
                     <label class="flex items-center p-3 border rounded-lg cursor-pointer transition-all hover:bg-gray-50" :class="form.payment_type === 'share' ? 'border-green-500 bg-green-50/30 ring-1 ring-green-500' : 'border-gray-200'">
                        <input type="radio" v-model="form.payment_type" value="share" class="form-radio text-green-600 h-4 w-4">
                        <div class="ml-3">
                           <span class="block text-sm font-medium text-gray-900">Produce Share</span>
                           <span class="block text-xs text-gray-500">Percentage of harvest yield</span>
                        </div>
                     </label>
                  </div>

                  <!-- Share Percentage Input -->
                  <transition enter-active-class="transition ease-out duration-200" leave-active-class="transition ease-in duration-150" enter-from-class="opacity-0 -translate-y-2" enter-to-class="opacity-100 translate-y-0" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-2">
                     <div v-if="form.payment_type === 'share'" class="pt-2">
                        <label class="form-label mb-2">Harvester's Cut (%)</label>
                        <div class="relative">
                           <input
                            v-model="form.revenue_share_percentage"
                            type="number"
                            min="0"
                            max="100"
                            step="0.01"
                            class="form-input pr-8"
                            placeholder="e.g. 10"
                           />
                           <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none text-gray-500 font-medium">
                              %
                           </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">Will be deducted from harvest quantity.</p>
                     </div>
                  </transition>
                 </div>
             </section>

          </div>
        </div>

        <div class="flex flex-col sm:flex-row gap-4 justify-end pt-4">
          <button
            type="button"
            @click="router.push('/tasks')"
            class="inline-flex items-center justify-center px-6 py-3 rounded-xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="submitting"
            class="inline-flex items-center justify-center px-8 py-3 rounded-xl font-semibold text-white bg-gradient-to-r from-emerald-600 to-green-600 shadow-lg hover:shadow-xl hover:from-emerald-700 hover:to-green-700 disabled:opacity-60 disabled:cursor-not-allowed"
          >
             <svg
                v-if="submitting"
                class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
              >
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            {{ submitting ? 'Scheduling...' : 'Schedule Task' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, reactive, ref, onMounted, watch } from 'vue'
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
const groups = ref([])

const form = reactive({
  task_type: '',
  planting_id: '',
  due_date: formatDateForInput(new Date()),
  description: '',
  assigned_to: '',
  laborer_group_id: '',
  assignment_type: 'individual', // 'individual' or 'group'
  payment_type: 'wage', // 'wage' or 'share'
  revenue_share_percentage: '',
  wage_amount: ''
})

// Watch for laborer selection to autofill rates
watch([() => form.assigned_to, () => form.payment_type], ([newLaborerId, newPaymentType]) => {
    if (!newLaborerId || form.assignment_type !== 'individual') return

    const laborer = laborers.value.find(l => l.id == newLaborerId)
    if (!laborer) return

    const rate = parseFloat(laborer.rate) || 0

    if (newPaymentType === 'wage') {
        form.wage_amount = rate // Autofill wage
    } else if (newPaymentType === 'share') {
        form.revenue_share_percentage = rate // Autofill share if rate is treated as percentage
    }
})



const plantings = computed(() => {
  return (farmStore.plantings || []).filter(p => {
    return p.status !== 'harvested' && p.status !== 'failed' && p.status !== 'cancelled'
  })
})
const taskTypeOptions = computed(() =>
  buildTaskTypeOptions(farmStore.tasks || [], { includeBase: true })
)

const formatPlantingOption = (planting) => {
  const crop = planting.crop_type || 'Planting'
  const fieldName = planting.field?.name || `Field #${planting.field_id}`
  const variety = planting.rice_variety?.name ? `(${planting.rice_variety.name})` : ''
  
  return `${crop} ${variety} • ${fieldName}`
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
      payment_type: form.payment_type,
      revenue_share_percentage: form.payment_type === 'share' ? form.revenue_share_percentage : null,
      wage_amount: form.payment_type === 'wage' ? form.wage_amount : null,
    }

    // Handle Assignment
    if (form.assignment_type === 'individual' && form.assigned_to) {
        const assignedToNum = Number(form.assigned_to)
        if (!isNaN(assignedToNum) && assignedToNum > 0) {
            payload.assigned_to = assignedToNum
        }
    } else if (form.assignment_type === 'group' && form.laborer_group_id) {
        const groupIdNum = Number(form.laborer_group_id)
        if (!isNaN(groupIdNum) && groupIdNum > 0) {
            payload.laborer_group_id = groupIdNum
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

const loadLaborersAndGroups = async () => {
  try {
    loadingLaborers.value = true
    const [laborerRes, groupRes] = await Promise.all([
        laborAPI.getLaborers(),
        laborAPI.getGroups()
    ])
    laborers.value = laborerRes.data.laborers || laborerRes.data || []
    groups.value = groupRes.data.groups || []
  } catch (error) {
    console.error('Failed to load laborers or groups:', error)
    laborers.value = []
    groups.value = []
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

  // Load laborers and groups for assignment
  loadLaborersAndGroups()

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
  margin-bottom: 0.25rem;
}

.form-input {
  width: 100%;
  padding: 0.625rem 0.875rem;
  background-color: #ffffff;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
  font-size: 0.875rem;
  transition: all 0.2s;
}

.form-input:focus {
  outline: none;
  border-color: #16a34a;
  box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.1);
}

.form-error {
  margin-top: 0.375rem;
  font-size: 0.75rem;
  color: #dc2626;
  font-weight: 500;
}
</style>
