<template>
  <Modal :modelValue="show" @update:modelValue="handleModelValueUpdate" @close="closeModal" :withHeader="false">
    <div class="max-w-md mx-auto">
      <!-- Header -->
      <div class="bg-gray-50 border-b border-gray-200 px-6 py-4 flex items-center justify-between rounded-t-lg">
        <h3 class="text-lg font-semibold text-gray-900">Adjust Stock</h3>
        <button @click="closeModal" class="text-gray-400 hover:text-gray-500">
          <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Content -->
      <form @submit.prevent="handleSubmit" class="p-6">
        <div class="mb-6">
          <p class="text-sm text-gray-600 mb-2">Current Stock: <span class="font-semibold text-gray-900">{{ item?.quantity || 0 }} {{ item?.unit || 'units' }}</span></p>
          
          <div class="flex gap-4 mb-4">
            <button
              type="button"
              @click="type = 'add'"
              class="flex-1 py-2 px-4 rounded-lg border-2 text-sm font-medium transition-colors"
              :class="type === 'add' ? 'border-green-500 bg-green-50 text-green-700' : 'border-gray-200 hover:border-gray-300 text-gray-600'"
            >
              Add Stock
            </button>
            <button
              type="button"
              @click="type = 'remove'"
              class="flex-1 py-2 px-4 rounded-lg border-2 text-sm font-medium transition-colors"
              :class="type === 'remove' ? 'border-red-500 bg-red-50 text-red-700' : 'border-gray-200 hover:border-gray-300 text-gray-600'"
            >
              Remove Stock
            </button>
          </div>

          <div class="relative">
            <label for="quantity" class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
            <input
              type="number"
              id="quantity"
              v-model.number="quantity"
              min="0.01"
              step="any"
              required
              class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
              placeholder="0.00"
            />
          </div>

          <div v-if="error" class="mt-4 p-3 bg-red-50 text-red-700 text-sm rounded-lg">
            {{ error }}
          </div>
        </div>

        <div class="flex justify-end gap-3">
          <button
            type="button"
            @click="closeModal"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 from-gray-50 to-white"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="processing || !quantity"
            class="px-4 py-2 text-sm font-medium text-white rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed"
            :class="type === 'add' ? 'bg-green-600 hover:bg-green-700 focus:ring-green-500' : 'bg-red-600 hover:bg-red-700 focus:ring-red-500'"
          >
            <span v-if="processing">Processing...</span>
            <span v-else>{{ type === 'add' ? 'Add Stock' : 'Remove Stock' }}</span>
          </button>
        </div>
      </form>
    </div>
  </Modal>
</template>

<script setup>
import { ref, watch } from 'vue'
import Modal from '@/Components/UI/Modal.vue'
import { useInventoryStore } from '@/stores/inventory'

const props = defineProps({
  show: Boolean,
  item: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'updated'])
const inventoryStore = useInventoryStore()

const type = ref('add')
const quantity = ref('')
const processing = ref(false)
const error = ref(null)

const handleModelValueUpdate = (val) => {
  if (!val) closeModal()
}

const closeModal = () => {
  type.value = 'add'
  quantity.value = ''
  error.value = null
  emit('close')
}

const handleSubmit = async () => {
  if (!quantity.value || quantity.value <= 0) return

  processing.value = true
  error.value = null

  try {
    if (type.value === 'add') {
      await inventoryStore.addStock(props.item.id, quantity.value)
    } else {
      await inventoryStore.removeStock(props.item.id, quantity.value)
    }
    emit('updated')
    closeModal()
  } catch (err) {
    console.error('Stock adjustment failed:', err)
    error.value = err.userMessage || err.response?.data?.message || 'Failed to adjust stock'
  } finally {
    processing.value = false
  }
}

// Reset form when modal opens
watch(() => props.show, (val) => {
  if (val) {
    type.value = 'add'
    quantity.value = ''
    error.value = null
  }
})
</script>
