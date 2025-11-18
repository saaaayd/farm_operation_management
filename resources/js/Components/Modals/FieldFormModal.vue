<template>
  <Modal :show="show" @close="closeModal">
    <div class="p-6">
      <h2 class="text-2xl font-semibold mb-4 text-gray-900">
        {{ isEditMode ? 'Edit Field' : 'Add New Field' }}
      </h2>

      <form @submit.prevent="submitForm">
        <div v-if="form.errors.general" class="mb-4 p-3 bg-red-100 border border-red-300 text-red-800 rounded-md">
          {{ form.errors.general }}
        </div>

        <div class="space-y-4">
          <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Field Name</label>
            <input
              type="text"
              id="name"
              v-model="form.data.name"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
              placeholder="e.g., North Paddock"
            />
            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
          </div>

          <div>
            <label for="address" class="block text-sm font-medium text-gray-700">Address / Location</label>
            <input
              type="text"
              id="address"
              v-model="form.data.location.address"
              required
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
              placeholder="Brgy. Lagao, General Santos City"
            />
            <p v-if="form.errors['location.address']" class="mt-1 text-xs text-red-600">{{ form.errors['location.address'] }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="lat" class="block text-sm font-medium text-gray-700">Latitude</label>
              <input
                type="number"
                step="any"
                id="lat"
                v-model.number="form.data.location.lat"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                placeholder="e.g., 6.1167"
              />
              <p v-if="form.errors['location.lat']" class="mt-1 text-xs text-red-600">{{ form.errors['location.lat'] }}</p>
            </div>
            <div>
              <label for="lon" class="block text-sm font-medium text-gray-700">Longitude</label>
              <input
                type="number"
                step="any"
                id="lon"
                v-model.number="form.data.location.lon"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                placeholder="e.g., 125.1718"
              />
              <p v-if="form.errors['location.lon']" class="mt-1 text-xs text-red-600">{{ form.errors['location.lon'] }}</p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="size" class="block text-sm font-medium text-gray-700">Size (hectares)</label>
              <input
                type="number"
                step="0.1"
                min="0"
                id="size"
                v-model.number="form.data.size"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                placeholder="e.g., 5.5"
              />
              <p v-if="form.errors.size" class="mt-1 text-xs text-red-600">{{ form.errors.size }}</p>
            </div>
            <div>
              <label for="soil_type" class="block text-sm font-medium text-gray-700">Soil Type</label>
              <input
                type="text"
                id="soil_type"
                v-model="form.data.soil_type"
                required
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                placeholder="e.g., Loam"
              />
              <p v-if="form.errors.soil_type" class="mt-1 text-xs text-red-600">{{ form.errors.soil_type }}</p>
            </div>
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
            {{ isEditMode ? 'Save Changes' : 'Create Field' }}
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useFarmStore } from '@/stores/farm'
import Modal from '@/Components/UI/Modal.vue' // Assuming you have a base modal
import LoadingSpinner from '@/Components/UI/LoadingSpinner.vue' // Assuming you have a spinner

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  field: { // Pass the field object when editing
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close'])

const farmStore = useFarmStore()

const isEditMode = computed(() => !!props.field)

const getInitialFormData = () => ({
  name: props.field?.name || '',
  location: {
    lat: props.field?.location?.lat || '',
    lon: props.field?.location?.lon || '',
    address: props.field?.location?.address || '',
  },
  size: props.field?.size || '',
  soil_type: props.field?.soil_type || '',
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
  }
})

const submitForm = async () => {
  form.value.processing = true
  form.value.errors = {}

  try {
    if (isEditMode.value) {
      // Update existing field
      await farmStore.updateField(props.field.id, form.value.data)
    } else {
      // Create new field
      await farmStore.createField(form.value.data)
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
</script>