<template>
  <div class="inventory-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Inventory</h1>
          <p class="text-gray-600 mt-2">Manage your farm supplies and equipment</p>
        </div>
        <button
          type="button"
          @click="showCreateModal = true"
          class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          Add New Item
        </button>
      </div>

      <FormAlert
        :visible="!!inventoryError"
        :message="inventoryError"
        class="mb-6"
      />

      <!-- Summary Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                <span class="text-blue-600 text-lg">📦</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ totalItems }}</div>
              <div class="text-sm text-gray-600">Total Items</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                <span class="text-green-600 text-lg">✅</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ inStockItems }}</div>
              <div class="text-sm text-gray-600">In Stock</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                <span class="text-yellow-600 text-lg">⚠️</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ lowStockItems }}</div>
              <div class="text-sm text-gray-600">Low Stock</div>
            </div>
          </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">
                <span class="text-red-600 text-lg">❌</span>
              </div>
            </div>
            <div class="ml-4">
              <div class="text-2xl font-bold text-gray-900">{{ outOfStockItems }}</div>
              <div class="text-sm text-gray-600">Out of Stock</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters and Search -->
      <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input
              v-model="searchQuery"
              type="text"
              placeholder="Search inventory..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
            <select
              v-model="categoryFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Categories</option>
              <option value="seeds">Seeds</option>
              <option value="fertilizer">Fertilizer</option>
              <option value="pesticides">Pesticides</option>
              <option value="equipment">Equipment</option>
              <option value="tools">Tools</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="statusFilter"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
              <option value="">All Status</option>
              <option value="in_stock">In Stock</option>
              <option value="low_stock">Low Stock</option>
              <option value="out_of_stock">Out of Stock</option>
            </select>
          </div>
          <div class="flex items-end">
            <button
              type="button"
              @click="clearFilters"
              class="w-full bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500"
            >
              Clear Filters
            </button>
          </div>
        </div>
      </div>

      <!-- Inventory Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="item in filteredItems"
          :key="item.id"
          class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow"
        >
          <div class="p-6">
            <div class="flex justify-between items-start mb-4">
              <h3 class="text-xl font-semibold text-gray-900">{{ item.name }}</h3>
              <span
                :class="getStatusBadgeClass(item.status)"
                class="px-2 py-1 text-xs font-medium rounded-full"
              >
                {{ item.status }}
              </span>
            </div>
            
            <div class="space-y-2 mb-4">
              <div class="flex justify-between">
                <span class="text-gray-600">Category:</span>
                <span class="font-medium">{{ item.category }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Quantity:</span>
                <span class="font-medium">{{ item.quantity }} {{ item.unit }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Unit Price:</span>
                <span class="font-medium">{{ formatCurrency(item.unit_price) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Total Value:</span>
                <span class="font-medium">{{ formatCurrency(item.quantity * item.unit_price) }}</span>
              </div>
            </div>

            <!-- Stock Level Indicator -->
            <div class="mb-4">
              <div class="flex justify-between text-sm text-gray-600 mb-1">
                <span>Stock Level</span>
                <span>{{ getStockPercentage(item) }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  :class="getStockBarClass(item)"
                  class="h-2 rounded-full transition-all duration-300"
                  :style="{ width: `${getStockPercentage(item)}%` }"
                ></div>
              </div>
            </div>

            <div class="flex space-x-2">
              <button
                type="button"
                @click="viewItem(item.id)"
                class="flex-1 bg-blue-600 text-white px-3 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
              >
                View Details
              </button>
              <button
                type="button"
                @click="editItem(item.id)"
                class="flex-1 bg-gray-600 text-white px-3 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 text-sm"
              >
                Edit
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="filteredItems.length === 0" class="text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">📦</div>
        <h3 class="text-xl font-medium text-gray-900 mb-2">No inventory items found</h3>
        <p class="text-gray-600 mb-6">Get started by adding your first inventory item</p>
        <button
          type="button"
          @click="showCreateModal = true"
          class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
        >
          Add Your First Item
        </button>
      </div>

      <!-- Create Item Modal -->
      <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
          <h2 class="text-xl font-semibold mb-4">Add New Inventory Item</h2>
          
          <form @submit.prevent="createItem" class="space-y-4">
            <FormAlert
              :visible="!!formError.message"
              :message="formError.message"
              :field-errors="formError.fieldErrors"
            />
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Item Name</label>
              <input
                v-model="newItem.name"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
              <p v-if="fieldError('name')" class="mt-1 text-xs text-red-600">{{ fieldError('name') }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
              <select
                v-model="newItem.category"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              >
                <option value="">Select category</option>
                <option value="seeds">Seeds</option>
                <option value="fertilizer">Fertilizer</option>
                <option value="pesticides">Pesticides</option>
                <option value="equipment">Equipment</option>
                <option value="tools">Tools</option>
              </select>
              <p v-if="fieldError('category')" class="mt-1 text-xs text-red-600">{{ fieldError('category') }}</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                <input
                  v-model="newItem.quantity"
                  type="number"
                  min="0"
                  step="0.1"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                />
                <p v-if="fieldError('quantity') || fieldError('current_stock')" class="mt-1 text-xs text-red-600">
                  {{ fieldError('quantity') || fieldError('current_stock') }}
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Unit</label>
                <select
                  v-model="newItem.unit"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  required
                >
                  <option value="">Select unit</option>
                  <option value="lbs">Pounds</option>
                  <option value="bags">Bags</option>
                  <option value="gallons">Gallons</option>
                  <option value="pieces">Pieces</option>
                  <option value="tons">Tons</option>
                </select>
                <p v-if="fieldError('unit')" class="mt-1 text-xs text-red-600">{{ fieldError('unit') }}</p>
              </div>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Unit Price</label>
              <input
                v-model="newItem.unit_price"
                type="number"
                min="0"
                step="0.01"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                required
              />
              <p v-if="fieldError('unit_price')" class="mt-1 text-xs text-red-600">{{ fieldError('unit_price') }}</p>
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Description</label>
              <textarea
                v-model="newItem.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
              ></textarea>
              <p v-if="fieldError('description')" class="mt-1 text-xs text-red-600">{{ fieldError('description') }}</p>
            </div>

            <div class="flex justify-end space-x-3">
              <button
                type="button"
                @click="showCreateModal = false"
                class="px-4 py-2 text-gray-600 hover:text-gray-800"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="loading"
                class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
              >
                {{ loading ? 'Creating...' : 'Create Item' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useInventoryStore } from '@/stores/inventory'
import FormAlert from '@/Components/UI/FormAlert.vue'
import { extractFormErrors, resetFormErrors } from '@/utils/form'

const router = useRouter()
const inventoryStore = useInventoryStore()
const loading = computed(() => inventoryStore.loading)
const showCreateModal = ref(false)
const searchQuery = ref('')
const categoryFilter = ref('')
const statusFilter = ref('')

const items = computed(() => inventoryStore.items || [])
const inventoryError = computed(() => inventoryStore.error)

const newItem = ref({
  name: '',
  category: '',
  quantity: '',
  unit: '',
  unit_price: '',
  description: ''
})

const formError = reactive({
  message: '',
  fieldErrors: {},
})

const fieldErrors = computed(() => formError.fieldErrors || {})

const fieldError = (field) => {
  const messages = fieldErrors.value?.[field]
  if (Array.isArray(messages)) {
    return messages[0]
  }
  return messages || ''
}

const filteredItems = computed(() => {
  return (items.value || []).filter(item => {
    const matchesSearch = item.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
                         item.description.toLowerCase().includes(searchQuery.value.toLowerCase())
    const matchesCategory = !categoryFilter.value || item.category === categoryFilter.value
    const matchesStatus = !statusFilter.value || item.status === statusFilter.value
    
    return matchesSearch && matchesCategory && matchesStatus
  })
})

const totalItems = computed(() => items.value.length)
const inStockItems = computed(() => items.value.filter(item => item.status === 'in_stock').length)
const lowStockItems = computed(() => items.value.filter(item => item.status === 'low_stock').length)
const outOfStockItems = computed(() => items.value.filter(item => item.status === 'out_of_stock').length)

const getStatusBadgeClass = (status) => {
  const classes = {
    in_stock: 'bg-green-100 text-green-800',
    low_stock: 'bg-yellow-100 text-yellow-800',
    out_of_stock: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getStockPercentage = (item) => {
  if (item.max_stock === 0) return 0
  return Math.min((item.quantity / item.max_stock) * 100, 100)
}

const getStockBarClass = (item) => {
  const percentage = getStockPercentage(item)
  if (percentage < 20) return 'bg-red-600'
  if (percentage < 50) return 'bg-yellow-600'
  return 'bg-green-600'
}

const viewItem = (id) => {
  router.push(`/inventory/${id}`)
}

const editItem = (id) => {
  // Navigate to edit page or show edit modal
  console.log('Edit item:', id)
}

const createItem = async () => {
  resetFormErrors(formError)
  try {
    const mapCategory = (c) => {
      const v = String(c || '').toLowerCase()
      if (v === 'fertilizer' || v === 'fertilizers') return 'fertilizer'
      if (v === 'pesticide' || v === 'pesticides') return 'pesticide'
      if (v === 'produce') return 'produce'
      return v
    }
    const mapUnit = (u) => {
      const v = String(u || '').toLowerCase()
      if (v === 'lbs' || v === 'pounds') return 'pounds'
      if (v === 'bag' || v === 'bags' || v === 'packet' || v === 'packets') return 'packets'
      if (v === 'liter' || v === 'liters') return 'liters'
      return v
    }
    const payload = {
      name: newItem.value.name,
      category: mapCategory(newItem.value.category),
      // Backend expects current_stock/minimum_stock; send both and legacy names for robustness
      current_stock: Number(newItem.value.quantity),
      quantity: Number(newItem.value.quantity),
      minimum_stock: 0,
      min_stock: 0,
      unit: mapUnit(newItem.value.unit),
      unit_price: Number(newItem.value.unit_price),
      description: newItem.value.description || null,
    }
    await inventoryStore.createItem(payload)
    // Reset form
    newItem.value = {
      name: '',
      category: '',
      quantity: '',
      unit: '',
      unit_price: '',
      description: ''
    }
    showCreateModal.value = false
  } catch (error) {
    const parsed = extractFormErrors(error)
    formError.message = parsed.message
    formError.fieldErrors = parsed.fieldErrors
  }
}

const clearFilters = () => {
  searchQuery.value = ''
  categoryFilter.value = ''
  statusFilter.value = ''
}

onMounted(async () => {
  if (!items.value.length) {
    try {
      await inventoryStore.fetchItems()
    } catch (e) {
      // handled by store
    }
  }
})
</script>

<style scoped>
.inventory-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>