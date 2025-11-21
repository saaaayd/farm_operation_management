<template>
  <Modal :modelValue="show" @update:modelValue="handleModelValueUpdate" @close="closeModal" :withHeader="false">
    <div class="max-w-3xl mx-auto -mx-6 -my-6">
      <!-- Header with close button -->
      <div class="sticky top-0 z-10 bg-white border-b border-gray-200 px-6 py-5 flex items-center justify-between shadow-sm">
        <div class="flex items-center">
          <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-green-500 to-emerald-600 flex items-center justify-center mr-3 shadow-lg">
            <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div>
            <h2 class="text-xl font-bold text-gray-900">
              {{ isEditMode ? 'Edit Harvest Log' : 'Add New Harvest' }}
            </h2>
            <p class="text-xs text-gray-600 mt-0.5">
              {{ isEditMode ? 'Update harvest details and records' : 'Record your harvest details and track yield' }}
            </p>
          </div>
        </div>
        <button
          type="button"
          @click="closeModal"
          class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 transition-all hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500"
        >
          <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
          </svg>
        </button>
      </div>

      <!-- Scrollable content -->
      <div class="px-6 py-10 max-h-[calc(100vh-14rem)] overflow-y-auto">
      <form @submit.prevent="submitForm" class="space-y-6 pb-6">
        <!-- Error Message -->
        <div v-if="form.errors.general" class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-800">{{ form.errors.general }}</p>
            </div>
          </div>
        </div>

        <!-- Planting Selection -->
        <section class="bg-gradient-to-br from-white to-gray-50 rounded-2xl p-6 border border-gray-200 shadow-sm">
          <div class="flex items-center mb-4">
            <div class="h-10 w-10 rounded-lg bg-green-100 flex items-center justify-center mr-3">
              <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Select Planting</h3>
              <p class="text-xs text-gray-600">Choose which planting to record harvest for</p>
            </div>
          </div>
          <div>
            <label for="planting_id" class="block text-sm font-semibold text-gray-700 mb-2">
              Planting *
            </label>
            <select
              id="planting_id"
              v-model.number="form.data.planting_id"
              required
              class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500 transition"
              :class="{ 'border-red-500 ring-2 ring-red-200': form.errors.planting_id }"
            >
              <option value="" disabled>
                {{ harvestablePlantings.length === 0 ? 'No plantings available - Please create a planting first' : 'Select the planting to harvest' }}
              </option>
              <option v-for="planting in harvestablePlantings" :key="planting.id" :value="planting.id">
                {{ getPlantingDisplayName(planting) }}
              </option>
            </select>
            <p v-if="harvestablePlantings.length === 0" class="mt-2 text-xs text-amber-600 bg-amber-50 px-3 py-2 rounded-lg">
              <span class="font-medium">Note:</span> Total plantings in store: {{ farmStore.plantings?.length || 0 }}
            </p>
            <p v-if="form.errors.planting_id" class="mt-2 text-xs text-red-600">{{ form.errors.planting_id }}</p>
          </div>
        </section>

        <!-- Harvest Details -->
        <section class="bg-gradient-to-br from-white to-gray-50 rounded-2xl p-6 border border-gray-200 shadow-sm">
          <div class="flex items-center mb-4">
            <div class="h-10 w-10 rounded-lg bg-blue-100 flex items-center justify-center mr-3">
              <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Harvest Details</h3>
              <p class="text-xs text-gray-600">Date and quality information</p>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="harvest_date" class="block text-sm font-semibold text-gray-700 mb-2">
                Harvest Date *
              </label>
              <input
                type="date"
                id="harvest_date"
                v-model="form.data.harvest_date"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 transition"
                :class="{ 'border-red-500 ring-2 ring-red-200': form.errors.harvest_date }"
              />
              <p v-if="form.errors.harvest_date" class="mt-1 text-xs text-red-600">{{ form.errors.harvest_date }}</p>
            </div>
            <div>
              <label for="quality_grade" class="block text-sm font-semibold text-gray-700 mb-2">
                Quality Grade
              </label>
              <select
                id="quality_grade"
                v-model="form.data.quality_grade"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500 transition"
                :class="{ 'border-red-500 ring-2 ring-red-200': form.errors.quality_grade }"
              >
                <option value="">Select grade (optional)</option>
                <option value="A" class="font-semibold">Grade A (Premium)</option>
                <option value="B">Grade B (Good)</option>
                <option value="C">Grade C (Standard)</option>
                <option value="D">Grade D (Sub-standard)</option>
              </select>
              <p v-if="form.errors.quality_grade" class="mt-1 text-xs text-red-600">{{ form.errors.quality_grade }}</p>
            </div>
          </div>
        </section>

        <!-- Quantity & Unit -->
        <section class="bg-gradient-to-br from-white to-gray-50 rounded-2xl p-6 border border-gray-200 shadow-sm">
          <div class="flex items-center mb-4">
            <div class="h-10 w-10 rounded-lg bg-purple-100 flex items-center justify-center mr-3">
              <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Quantity & Unit</h3>
              <p class="text-xs text-gray-600">Amount harvested and measurement unit</p>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="quantity" class="block text-sm font-semibold text-gray-700 mb-2">
                Quantity *
              </label>
              <input
                type="number"
                step="0.01"
                min="0"
                id="quantity"
                v-model.number="form.data.quantity"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 transition"
                :class="{ 'border-red-500 ring-2 ring-red-200': form.errors.quantity }"
                placeholder="0.00"
              />
              <p v-if="form.errors.quantity" class="mt-1 text-xs text-red-600">{{ form.errors.quantity }}</p>
            </div>
            <div>
              <label for="unit" class="block text-sm font-semibold text-gray-700 mb-2">
                Unit *
              </label>
              <select
                id="unit"
                v-model="form.data.unit"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-green-500 focus:ring-2 focus:ring-green-500 transition"
                :class="{ 'border-red-500 ring-2 ring-red-200': form.errors.unit }"
              >
                <option value="kg">Kilograms (kg)</option>
                <option value="tons">Tons</option>
                <option value="bushels">Bushels</option>
                <option value="pounds">Pounds (lbs)</option>
                <option value="grams">Grams (g)</option>
              </select>
              <p v-if="form.errors.unit" class="mt-1 text-xs text-red-600">{{ form.errors.unit }}</p>
            </div>
          </div>
        </section>
        
        <!-- Pricing -->
        <section class="bg-gradient-to-br from-white to-gray-50 rounded-2xl p-6 border border-gray-200 shadow-sm">
          <div class="flex items-center mb-4">
            <div class="h-10 w-10 rounded-lg bg-yellow-100 flex items-center justify-center mr-3">
              <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Pricing (Optional)</h3>
              <p class="text-xs text-gray-600">Track value and calculate total revenue</p>
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label for="price_per_unit" class="block text-sm font-semibold text-gray-700 mb-2">
                Price per Unit
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-500 text-sm">₱</span>
                </div>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  id="price_per_unit"
                  v-model.number="form.data.price_per_unit"
                  class="w-full rounded-lg border border-gray-300 pl-8 pr-4 py-3 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 transition"
                  :class="{ 'border-red-500 ring-2 ring-red-200': form.errors.price_per_unit }"
                  placeholder="20.50"
                />
              </div>
              <p v-if="form.errors.price_per_unit" class="mt-1 text-xs text-red-600">{{ form.errors.price_per_unit }}</p>
            </div>
            <div>
              <label for="total_value" class="block text-sm font-semibold text-gray-700 mb-2">
                Total Value
                <span v-if="form.data.quantity && form.data.price_per_unit" class="text-xs font-normal text-green-600 ml-1">
                  (Auto-calculated)
                </span>
              </label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                  <span class="text-gray-500 text-sm">₱</span>
                </div>
                <input
                  type="number"
                  step="0.01"
                  min="0"
                  id="total_value"
                  v-model.number="form.data.total_value"
                  :readonly="form.data.quantity && form.data.price_per_unit"
                  class="w-full rounded-lg border border-gray-300 pl-8 pr-4 py-3 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 transition"
                  :class="{ 
                    'border-red-500 ring-2 ring-red-200': form.errors.total_value,
                    'bg-gray-50 cursor-not-allowed': form.data.quantity && form.data.price_per_unit
                  }"
                  placeholder="Auto-calculated if price given"
                />
              </div>
              <p v-if="form.errors.total_value" class="mt-1 text-xs text-red-600">{{ form.errors.total_value }}</p>
            </div>
          </div>
        </section>
        
        <!-- Notes -->
        <section class="bg-gradient-to-br from-white to-gray-50 rounded-2xl p-6 border border-gray-200 shadow-sm">
          <div class="flex items-center mb-4">
            <div class="h-10 w-10 rounded-lg bg-indigo-100 flex items-center justify-center mr-3">
              <svg class="h-5 w-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
              </svg>
            </div>
            <div>
              <h3 class="text-lg font-semibold text-gray-900">Additional Notes</h3>
              <p class="text-xs text-gray-600">Any additional information about this harvest</p>
            </div>
          </div>
          <div>
            <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">
              Notes
            </label>
            <textarea
              id="notes"
              v-model="form.data.notes"
              rows="4"
              class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-green-500 focus:ring-2 focus:ring-green-500 transition resize-none"
              :class="{ 'border-red-500 ring-2 ring-red-200': form.errors.notes }"
              placeholder="e.g., Harvested from the north section. Excellent quality yield."
            ></textarea>
            <p v-if="form.errors.notes" class="mt-1 text-xs text-red-600">{{ form.errors.notes }}</p>
          </div>
        </section>

        <!-- Actions -->
        <div class="flex justify-end gap-3 pt-4 border-t border-gray-200">
          <button
            type="button"
            @click="closeModal"
            class="inline-flex items-center px-6 py-3 text-sm font-semibold rounded-lg border-2 border-gray-300 text-gray-700 bg-white hover:bg-gray-50 transition-colors"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center px-6 py-3 text-sm font-semibold rounded-lg bg-gradient-to-r from-green-600 to-emerald-600 text-white hover:from-green-700 hover:to-emerald-700 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transition-all"
          >
            <LoadingSpinner v-if="form.processing" class="mr-2" />
            <svg v-if="!form.processing" class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ isEditMode ? 'Save Changes' : 'Create Harvest' }}
          </button>
        </div>
      </form>
      </div>
    </div>
  </Modal>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import { useFarmStore } from '@/stores/farm'
import Modal from '@/Components/UI/Modal.vue'
import LoadingSpinner from '@/Components/UI/LoadingSpinner.vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  harvest: { // Pass the harvest object when editing
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close'])

const farmStore = useFarmStore()

const isEditMode = computed(() => !!props.harvest)

// Get harvestable plantings from the store
const harvestablePlantings = computed(() => {
  const allPlantings = farmStore.plantings || []
  // Use the store getter which should handle the filtering
  let plantings = farmStore.harvestablePlantings || []
  
  // Debug logging when modal is open
  if (props.show) {
    console.log('=== Harvest Modal Debug ===')
    console.log('All plantings in store:', allPlantings.length)
    console.log('Harvestable plantings (from getter):', plantings.length)
    if (allPlantings.length > 0) {
      console.log('Sample planting structure:', JSON.stringify(allPlantings[0], null, 2))
      console.log('Planting statuses:', allPlantings.map(p => ({ 
        id: p?.id, 
        status: p?.status,
        statusType: typeof p?.status,
        statusValue: String(p?.status),
        hasField: !!p?.field,
        fieldName: p?.field?.name,
        fieldId: p?.field_id
      })))
    }
    
    // If getter returns empty but we have plantings, do manual filter as fallback
    if (plantings.length === 0 && allPlantings.length > 0) {
      console.warn('Getter returned 0 but we have plantings! Doing manual filter...')
      plantings = allPlantings.filter(p => {
        if (!p || !p.id) return false
        // Only exclude explicitly failed (case-insensitive)
        const status = String(p.status || '').toLowerCase()
        return status !== 'failed'
      })
      console.log('Manual filter result:', plantings.length, plantings)
    }
  }
  
  return plantings
})

// Helper to format dates for <input type="date">
const formatDateForInput = (dateString) => {
  if (!dateString) return ''
  try {
    const date = new Date(dateString)
    return date.toISOString().split('T')[0]
  } catch (e) {
    return ''
  }
}

const getInitialFormData = () => ({
  planting_id: props.harvest?.planting_id || '',
  harvest_date: formatDateForInput(props.harvest?.harvest_date),
  quantity: props.harvest?.quantity || '',
  unit: props.harvest?.unit || 'kg',
  quality_grade: props.harvest?.quality_grade || '',
  price_per_unit: props.harvest?.price_per_unit || '',
  total_value: props.harvest?.total_value || '',
  notes: props.harvest?.notes || '',
})

const form = ref({
  data: getInitialFormData(),
  errors: {},
  processing: false,
})

// Watch for the modal to open and reset the form
watch(() => props.show, async (newVal) => {
  if (newVal) {
    form.value.data = getInitialFormData()
    form.value.errors = {}
    form.value.processing = false
    // Always fetch plantings and fields when modal opens to ensure we have latest data
    try {
      console.log('Modal opened, fetching plantings and fields...')
      console.log('Current plantings count:', farmStore.plantings.length)
      
      // Always fetch to get latest data
      await farmStore.fetchPlantings()
      console.log('After fetch, plantings count:', farmStore.plantings.length)
      console.log('Plantings data:', farmStore.plantings)
      
      // Also fetch fields if not loaded (needed for field name display)
      if (farmStore.fields.length === 0) {
        await farmStore.fetchFields()
      }
    } catch (err) {
      console.error('Failed to load plantings/fields:', err)
      form.value.errors.general = "Could not load plantings list. Please try again."
    }
  }
})

// Auto-calculate total_value
watch(() => [form.value.data.quantity, form.value.data.price_per_unit], ([qty, price]) => {
  if (qty && price) {
    form.value.data.total_value = (parseFloat(qty) * parseFloat(price)).toFixed(2)
  }
})

const submitForm = async () => {
  form.value.processing = true
  form.value.errors = {}

  // Clean the form data - convert empty strings to null and ensure proper types
  const payload = { ...form.value.data }
  
  // Convert empty strings to null for optional fields
  if (payload.quality_grade === '') payload.quality_grade = null
  if (payload.price_per_unit === '') payload.price_per_unit = null
  if (payload.total_value === '') payload.total_value = null
  if (payload.notes === '') payload.notes = null
  
  // Ensure numeric fields are numbers
  if (payload.quantity) payload.quantity = parseFloat(payload.quantity)
  if (payload.price_per_unit) payload.price_per_unit = parseFloat(payload.price_per_unit)
  if (payload.total_value) payload.total_value = parseFloat(payload.total_value)
  
  // Ensure planting_id is a number
  if (payload.planting_id) payload.planting_id = Number(payload.planting_id)

  try {
    if (isEditMode.value) {
      await farmStore.updateHarvest(props.harvest.id, payload)
    } else {
      await farmStore.createHarvest(payload)
    }
    // Refresh harvests list after create/update
    await farmStore.fetchHarvests()
    closeModal() // Close modal on success
  } catch (err) {
    if (err.response && err.response.status === 422) {
      form.value.errors = err.response.data.errors || {}
      form.value.errors.general = err.response.data.message || 'Validation failed.'
    } else {
      form.value.errors.general = err.response?.data?.message || 'An unexpected error occurred. Please try again.'
    }
    console.error('Form submission error:', err)
  } finally {
    form.value.processing = false
  }
}

const closeModal = () => {
  emit('close')
}

const handleModelValueUpdate = (value) => {
  if (!value) {
    closeModal()
  }
}

// Helper for planting dropdown
const formatDate = (value) => {
  if (!value) return 'N/A'
  try {
    const date = new Date(value)
    return date.toLocaleDateString(undefined, { month: 'short', day: 'numeric' })
  } catch (e) {
    return value
  }
}

// Helper to get display name for planting in dropdown
const getPlantingDisplayName = (planting) => {
  if (!planting) return 'Unknown Planting'
  
  const parts = []
  
  // Add crop type or rice variety name
  if (planting.rice_variety?.name) {
    parts.push(planting.rice_variety.name)
  } else if (planting.crop_type) {
    parts.push(planting.crop_type)
  } else {
    parts.push('Rice')
  }
  
  // Add field name
  if (planting.field?.name) {
    parts.push(`on ${planting.field.name}`)
  } else if (planting.field_id) {
    parts.push(`on Field #${planting.field_id}`)
  }
  
  // Add planting date
  if (planting.planting_date) {
    parts.push(`(Planted: ${formatDate(planting.planting_date)})`)
  }
  
  // Add status if not standard
  if (planting.status && !['growing', 'ready'].includes(planting.status)) {
    parts.push(`[${planting.status}]`)
  }
  
  return parts.join(' ') || `Planting #${planting.id}`
}
</script>