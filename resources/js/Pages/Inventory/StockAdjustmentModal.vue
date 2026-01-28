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

          <!-- Unit Cost & Total Cost (Only for Adding Stock) -->
          <div v-if="type === 'add'" class="grid grid-cols-2 gap-4 mt-4">
            <div>
              <label for="unitCost" class="block text-sm font-medium text-gray-700 mb-1">Unit Cost</label>
              <div class="relative rounded-md shadow-sm">
                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                  <span class="text-gray-500 sm:text-sm">₱</span>
                </div>
                <input
                  type="number"
                  id="unitCost"
                  v-model.number="unitCost"
                  min="0"
                  step="any"
                  class="block w-full rounded-lg border-gray-300 pl-7 shadow-sm focus:border-green-500 focus:ring-green-500"
                  placeholder="0.00"
                />
              </div>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Total Cost</label>
              <div class="w-full px-3 py-2 bg-gray-50 border border-gray-200 rounded-lg text-gray-900 font-medium">
                {{ formatCurrency(totalCost) }}
              </div>
            </div>
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
import { ref, watch, computed } from 'vue'
import Modal from '@/Components/UI/Modal.vue'
import { useInventoryStore } from '@/stores/inventory'
import { formatCurrency } from '@/utils/format'

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
const unitCost = ref('')
const processing = ref(false)
const error = ref(null)

const totalCost = computed(() => {
  if (!quantity.value || !unitCost.value) return 0
  return quantity.value * unitCost.value
})

const handleModelValueUpdate = (val) => {
  if (!val) closeModal()
}

const closeModal = () => {
  type.value = 'add'
  quantity.value = ''
  unitCost.value = ''
  error.value = null
  emit('close')
}

const handleSubmit = async () => {
  if (!quantity.value || quantity.value <= 0) return

  processing.value = true
  error.value = null

  try {
    if (type.value === 'add') {
      await inventoryStore.addStock(props.item.id, quantity.value, unitCost.value)
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
    // Initialize unitCost with current unit_price
    unitCost.value = props.item.unit_price || 0
    error.value = null
  }
})
</script>
