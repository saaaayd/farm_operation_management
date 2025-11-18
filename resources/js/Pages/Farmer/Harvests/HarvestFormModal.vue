<template>
  <Modal :show="show" @close="closeModal">
    <div class="p-6">
      <h2 class="text-2xl font-semibold mb-4 text-gray-900">
        {{ isEditMode ? 'Edit Harvest Log' : 'Add New Harvest' }}
      </h2>

      <form @submit.prevent="submitForm">
        <div v-if="form.errors.general" class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded-md">
          {{ form.errors.general }}
        </div>

        <div class="space-y-4">
          <div>
            <label for="planting_id" class="block text-sm font-medium text-gray-700">Planting</label>
            <select
              id="planting_id"
              v-model.number="form.data.planting_id"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
              :class="{ 'border-red-500': form.errors.planting_id }"
            >
              <option value="" disabled>Select the planting to harvest</option>
              <option v-for="planting in harvestablePlantings" :key="planting.id" :value="planting.id">
                {{ planting.crop_type }} on {{ planting.field?.name }} (Planted: {{ formatDate(planting.planting_date) }})
              </option>
            </select>
            <p v-if="form.errors.planting_id" class="mt-1 text-xs text-red-600">{{ form.errors.planting_id }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="harvest_date" class="block text-sm font-medium text-gray-700">Harvest Date</label>
              <input
                type="date"
                id="harvest_date"
                v-model="form.data.harvest_date"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                :class="{ 'border-red-500': form.errors.harvest_date }"
              />
              <p v-if="form.errors.harvest_date" class="mt-1 text-xs text-red-600">{{ form.errors.harvest_date }}</p>
            </div>
            <div>
              <label for="quality_grade" class="block text-sm font-medium text-gray-700">Quality Grade</label>
              <select
                id="quality_grade"
                v-model="form.data.quality_grade"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                :class="{ 'border-red-500': form.errors.quality_grade }"
              >
                <option value="">N/A</option>
                <option value="A">Grade A (Premium)</option>
                <option value="B">Grade B (Good)</option>
                <option value="C">Grade C (Standard)</option>
                <option value="D">Grade D (Sub-standard)</option>
              </select>
              <p v-if="form.errors.quality_grade" class="mt-1 text-xs text-red-600">{{ form.errors.quality_grade }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="quantity" class="block text-sm font-medium text-gray-700">Quantity</label>
              <input
                type="number"
                step="0.01"
                min="0"
                id="quantity"
                v-model.number="form.data.quantity"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                :class="{ 'border-red-500': form.errors.quantity }"
              />
              <p v-if="form.errors.quantity" class="mt-1 text-xs text-red-600">{{ form.errors.quantity }}</p>
            </div>
            <div>
              <label for="unit" class="block text-sm font-medium text-gray-700">Unit</label>
              <select
                id="unit"
                v-model="form.data.unit"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                :class="{ 'border-red-500': form.errors.unit }"
              >
                <option value="kg">kg</option>
                <option value="tons">tons</option>
                <option value="bushels">bushels</option>
                <option value="pounds">pounds</option>
                <option value="grams">grams</option>
              </select>
              <p v-if="form.errors.unit" class="mt-1 text-xs text-red-600">{{ form.errors.unit }}</p>
            </div>
          </div>
          
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="price_per_unit" class="block text-sm font-medium text-gray-700">Price per Unit (Optional)</label>
              <input
                type="number"
                step="0.01"
                min="0"
                id="price_per_unit"
                v-model.number="form.data.price_per_unit"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                :class="{ 'border-red-500': form.errors.price_per_unit }"
                placeholder="e.g., 20.50"
              />
              <p v-if="form.errors.price_per_unit" class="mt-1 text-xs text-red-600">{{ form.errors.price_per_unit }}</p>
            </div>
            <div>
              <label for="total_value" class="block text-sm font-medium text-gray-700">Total Value (Optional)</label>
              <input
                type="number"
                step="0.01"
                min="0"
                id="total_value"
                v-model.number="form.data.total_value"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                :class="{ 'border-red-500': form.errors.total_value }"
                placeholder="Auto-calculated if price given"
              />
              <p v-if="form.errors.total_value" class="mt-1 text-xs text-red-600">{{ form.errors.total_value }}</p>
            </div>
          </div>
          
          <div>
            <label for="notes" class="block text-sm font-medium text-gray-700">Notes (Optional)</label>
            <textarea
              id="notes"
              v-model="form.data.notes"
              rows="3"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
              :class="{ 'border-red-500': form.errors.notes }"
              placeholder="e.g., Harvested from the north section."
            ></textarea>
            <p v-if="form.errors.notes" class="mt-1 text-xs text-red-600">{{ form.errors.notes }}</p>
          </div>
        </div>

        <div class="mt-6 flex justify-end gap-3">
          <button
            type="button"
            @click="closeModal"
            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="form.processing"
            class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md bg-green-600 text-white hover:bg-green-700 disabled:opacity-50"
          >
            <LoadingSpinner v-if="form.processing" class="mr-2" />
            {{ isEditMode ? 'Save Changes' : 'Create Harvest' }}
          </button>
        </div>
      </form>
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
const harvestablePlantings = computed(() => farmStore.harvestablePlantings)

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
watch(() => props.show, (newVal) => {
  if (newVal) {
    form.value.data = getInitialFormData()
    form.value.errors = {}
    form.value.processing = false
    // Also fetch plantings if they aren't loaded, as they are needed for the dropdown
    if (farmStore.plantings.length === 0) {
      farmStore.fetchPlantings().catch(err => {
        form.value.errors.general = "Could not load plantings list."
      })
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

  try {
    if (isEditMode.value) {
      await farmStore.updateHarvest(props.harvest.id, form.value.data)
    } else {
      await farmStore.createHarvest(form.value.data)
    }
    closeModal() // Close modal on success
  } catch (err) {
    if (err.response && err.response.status === 422) {
      form.value.errors = err.response.data.errors || {}
      form.value.errors.general = err.response.data.message || 'Validation failed.'
    } else {
      form.value.errors.general = 'An unexpected error occurred. Please try again.'
    }
    console.error('Form submission error:', err)
  } finally {
    form.value.processing = false
  }
}

const closeModal = () => {
  emit('close')
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
</script>