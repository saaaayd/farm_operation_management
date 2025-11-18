<template>
  <form @submit.prevent="submitForm" class="space-y-6">
    <div v-if="form.errors.general" class="p-4 bg-red-50 border border-red-300 text-red-800 rounded-md">
      <h3 class="font-medium">An error occurred:</h3>
      <p>{{ form.errors.general }}</p>
    </div>

    <div class="bg-white shadow sm:rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900">Planting Details</h3>
        <p class="mt-1 text-sm text-gray-500">
          Provide the core details about this planting cycle.
        </p>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 gap-6">
          <div class="sm:col-span-1">
            <label for="field_id" class="block text-sm font-medium text-gray-700">Field</label>
            <select
              id="field_id"
              v-model="form.data.field_id"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
              :class="{ 'border-red-500': form.errors.field_id }"
            >
              <option value="" disabled>Select a field</option>
              <option v-for="field in farmStore.fields" :key="field.id" :value="field.id">
                {{ field.name }} ({{ field.size }} ha)
              </option>
            </select>
            <p v-if="form.errors.field_id" class="mt-1 text-xs text-red-600">{{ form.errors.field_id }}</p>
          </div>

          <div class="sm:col-span-1">
            <label for="crop_type" class="block text-sm font-medium text-gray-700">Crop Name</label>
            <input
              type="text"
              id="crop_type"
              v-model="form.data.crop_type"
              placeholder="e.g., Rice"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
              :class="{ 'border-red-500': form.errors.crop_type }"
            />
            <p v-if="form.errors.crop_type" class="mt-1 text-xs text-red-600">{{ form.errors.crop_type }}</p>
          </div>
          
          <div class="sm:col-span-1">
            <label for="rice_variety_id" class="block text-sm font-medium text-gray-700">Rice Variety</label>
            <input
              type="text"
              id="rice_variety_id"
              v-model="form.data.rice_variety_id"
              placeholder="e.g., RC 216 (testing, replace with dropdown)"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
              :class="{ 'border-red-500': form.errors.rice_variety_id }"
            />
             <p v-if="form.errors.rice_variety_id" class="mt-1 text-xs text-red-600">{{ form.errors.rice_variety_id }}</p>
          </div>

          <div class="sm:col-span-1">
            <label for="season" class="block text-sm font-medium text-gray-700">Season</label>
            <select
              id="season"
              v-model="form.data.season"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
              :class="{ 'border-red-500': form.errors.season }"
            >
              <option value="wet">Wet Season (May - Oct)</option>
              <option value="dry">Dry Season (Nov - Apr)</option>
            </select>
            <p v-if="form.errors.season" class="mt-1 text-xs text-red-600">{{ form.errors.season }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white shadow sm:rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900">Schedule & Status</h3>
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-6">
          <div>
            <label for="planting_date" class="block text-sm font-medium text-gray-700">Planting Date</label>
            <input
              type="date"
              id="planting_date"
              v-model="form.data.planting_date"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
              :class="{ 'border-red-500': form.errors.planting_date }"
            />
            <p v-if="form.errors.planting_date" class="mt-1 text-xs text-red-600">{{ form.errors.planting_date }}</p>
          </div>
          
          <div>
            <label for="expected_harvest_date" class="block text-sm font-medium text-gray-700">Expected Harvest Date</label>
            <input
              type="date"
              id="expected_harvest_date"
              v-model="form.data.expected_harvest_date"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
              :class="{ 'border-red-500': form.errors.expected_harvest_date }"
            />
            <p v-if="form.errors.expected_harvest_date" class="mt-1 text-xs text-red-600">{{ form.errors.expected_harvest_date }}</p>
          </div>

          <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select
              id="status"
              v-model="form.data.status"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
              :class="{ 'border-red-500': form.errors.status }"
            >
              <option value="planted">Planted</option>
              <option value="growing">Growing</option>
              <option value="ready">Ready for Harvest</option>
              <option value="harvested">Harvested</option>
              <option value="failed">Failed</option>
            </select>
            <p v-if="form.errors.status" class="mt-1 text-xs text-red-600">{{ form.errors.status }}</p>
          </div>
        </div>
      </div>
    </div>
    
    <div class="bg-white shadow sm:rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900">Method & Quantity</h3>
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-6">
          <div>
            <label for="planting_method" class="block text-sm font-medium text-gray-700">Planting Method</label>
            <select
              id="planting_method"
              v-model="form.data.planting_method"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
              :class="{ 'border-red-500': form.errors.planting_method }"
            >
              <option value="transplanting">Transplanting</option>
              <option value="direct_seeding">Direct Seeding</option>
              <option value="broadcasting">Broadcasting</option>
            </select>
            <p v-if="form.errors.planting_method" class="mt-1 text-xs text-red-600">{{ form.errors.planting_method }}</p>
          </div>
          
          <div>
            <label for="area_planted" class="block text-sm font-medium text-gray-700">Area Planted (ha)</label>
            <input
              type="number"
              step="0.1"
              min="0"
              id="area_planted"
              v-model.number="form.data.area_planted"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
              :class="{ 'border-red-500': form.errors.area_planted }"
            />
            <p v-if="form.errors.area_planted" class="mt-1 text-xs text-red-600">{{ form.errors.area_planted }}</p>
          </div>

          <div>
            <label for="seed_rate" class="block text-sm font-medium text-gray-700">Seed Quantity (kg)</label>
            <input
              type="number"
              step="1"
              min="0"
              id="seed_rate"
              v-model.number="form.data.seed_rate"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
              :class="{ 'border-red-500': form.errors.seed_rate }"
            />
            <p v-if="form.errors.seed_rate" class="mt-1 text-xs text-red-600">{{ form.errors.seed_rate }}</p>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white shadow sm:rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900">Notes</h3>
        <div class="mt-4">
          <textarea
            id="notes"
            v-model="form.data.notes"
            rows="4"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
            :class="{ 'border-red-500': form.errors.notes }"
            placeholder="Any additional notes about this planting..."
          ></textarea>
          <p v-if="form.errors.notes" class="mt-1 text-xs text-red-600">{{ form.errors.notes }}</p>
        </div>
      </div>
    </div>
    
    <div class="flex justify-end gap-3 pt-4">
      <button
        type="button"
        @click="cancelForm"
        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-50"
      >
        Cancel
      </button>
      <button
        type="submit"
        :disabled="form.processing"
        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md bg-green-600 text-white hover:bg-green-700 disabled:opacity-50"
      >
        <LoadingSpinner v-if="form.processing" class="mr-2" />
        {{ isEditMode ? 'Save Changes' : 'Create Planting' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFarmStore } from '@/stores/farm'
import LoadingSpinner from '@/Components/UI/LoadingSpinner.vue' // Assuming you have this

const props = defineProps({
  planting: {
    type: Object,
    default: null,
  },
})

const router = useRouter()
const farmStore = useFarmStore()

const isEditMode = computed(() => !!props.planting)

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
  field_id: props.planting?.field_id || '',
  rice_variety_id: props.planting?.rice_variety_id || null, // <-- Use null
  crop_type: props.planting?.crop_type || 'Rice',
  planting_date: formatDateForInput(props.planting?.planting_date),
  expected_harvest_date: formatDateForInput(props.planting?.expected_harvest_date) || null, // <-- Use null
  planting_method: props.planting?.planting_method || 'transplanting',
  seed_rate: props.planting?.seed_rate || null, // <-- Use null
  area_planted: props.planting?.area_planted || null, // <-- Use null
  season: props.planting?.season || 'wet',
  status: props.planting?.status || 'planted',
  notes: props.planting?.notes || null, // <-- Use null
})

const form = ref({
  data: getInitialFormData(),
  errors: {},
  processing: false,
})

// If the planting prop changes (e.g., in edit mode), reset the form
watch(() => props.planting, () => {
  form.value.data = getInitialFormData()
  form.value.errors = {}
})

// Fetch fields if not already in store
onMounted(() => {
  if (farmStore.fields.length === 0) {
    farmStore.fetchFields().catch(err => {
      form.value.errors.general = "Could not load fields. Please refresh."
    })
  }
})

const submitForm = async () => {
  form.value.processing = true
  form.value.errors = {}

  // --- DATA CLEANING STEP ---
  // Create a copy of the data to send
  const payload = { ...form.value.data };
  
  // Convert any remaining empty strings "" to null
  // This is crucial for 'nullable' rules in Laravel
  for (const key in payload) {
    if (payload[key] === '') {
      payload[key] = null;
    }
  }
  // --- END CLEANING STEP ---

  try {
    if (isEditMode.value) {
      // Send the cleaned payload
      await farmStore.updatePlanting(props.planting.id, payload)
    } else {
      // Send the cleaned payload
      await farmStore.createPlanting(payload)
    }
    // Success - navigate back to the index page
    router.push('/plantings')
  } catch (err) {
    if (err.response && err.response.status === 422) {
      form.value.errors = err.response.data.errors || {}
      form.value.errors.general = err.response.data.message || 'Validation failed. Please check the fields.'
    } else {
      form.value.errors.general = 'An unexpected error occurred. Please try again.'
    }
    console.error('Form submission error:', err)
  } finally {
    form.value.processing = false
  }
}

const cancelForm = () => {
  router.back() // Go back to the previous page
}
</script>