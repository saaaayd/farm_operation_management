<template>
  <div class="inventory-page min-h-screen bg-gray-50 font-sans">
    <div class="container mx-auto px-4 py-8">
      
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Inventory</h1>
          <p class="text-gray-500 mt-1">Track your farm supplies, seeds, and equipment.</p>
        </div>
        <button
          @click="openCreateModal"
          class="flex items-center gap-2 bg-emerald-600 text-white px-5 py-2.5 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm font-medium"
        >
          <span class="text-xl leading-none">+</span> Add Product
        </button>
      </div>

      <FormAlert :visible="!!error" :message="error" class="mb-6" />

      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
          <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Items</div>
          <div class="text-2xl font-bold text-gray-800 mt-1">{{ totalItems }}</div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
          <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Total Value</div>
          <div class="text-2xl font-bold text-emerald-600 mt-1">{{ formatCurrency(totalValue) }}</div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
          <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Low Stock</div>
          <div class="text-2xl font-bold text-yellow-600 mt-1">{{ lowStockItems }}</div>
        </div>
        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-sm">
          <div class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Out of Stock</div>
          <div class="text-2xl font-bold text-red-600 mt-1">{{ outOfStockItems }}</div>
        </div>
      </div>

      <div class="bg-white p-4 rounded-xl border border-gray-200 shadow-sm mb-6 flex flex-col md:flex-row gap-4">
        <div class="flex-1 relative">
          <span class="absolute left-3 top-2.5 text-gray-400">🔍</span>
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search by name, supplier, or location..."
            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none"
          />
        </div>
        <select v-model="categoryFilter" class="w-full md:w-48 px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-500 outline-none bg-white">
          <option value="">All Categories</option>
          <option value="seeds">Seeds</option>
          <option value="fertilizer">Fertilizer</option>
          <option value="pesticide">Pesticides</option>
          <option value="equipment">Equipment</option>
          <option value="tools">Tools</option>
        </select>
      </div>

      <div v-if="loading" class="text-center py-20">
        <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-emerald-600 mx-auto"></div>
        <p class="text-gray-500 mt-4">Loading inventory...</p>
      </div>

      <div v-else-if="filteredItems.length === 0" class="text-center py-16 bg-white rounded-xl border border-dashed border-gray-300">
        <div class="text-5xl mb-4">📦</div>
        <h3 class="text-lg font-medium text-gray-900">No items found</h3>
        <p class="text-gray-500">Try adjusting your filters or add a new item.</p>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="item in filteredItems"
          :key="item.id"
          class="bg-white rounded-xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200 flex flex-col"
        >
          <div class="p-5 flex justify-between items-start">
            <div>
              <span class="inline-block px-2 py-0.5 text-xs font-semibold rounded bg-gray-100 text-gray-600 mb-2 uppercase tracking-wide">
                {{ item.category }}
              </span>
              <h3 class="text-lg font-bold text-gray-800 leading-tight mb-1">{{ item.name }}</h3>
              <p class="text-sm text-gray-500 line-clamp-1">{{ item.description || 'No description' }}</p>
            </div>
            <div class="text-right pl-2">
              <div class="text-lg font-bold text-emerald-700">{{ formatCurrency(item.unit_price) }}</div>
              <div class="text-xs text-gray-400">/ {{ item.unit }}</div>
            </div>
          </div>

          <div class="px-5 pb-4">
            <div class="flex justify-between items-end mb-1">
              <span class="text-sm font-medium" :class="getStockColor(item, 'text')">
                {{ item.current_stock || 0 }} {{ item.unit }}
              </span>
              <span class="text-xs text-gray-400">Min: {{ item.minimum_stock || 0 }}</span>
            </div>
            <div class="w-full bg-gray-100 rounded-full h-2">
              <div
                class="h-2 rounded-full transition-all duration-500"
                :class="getStockColor(item, 'bg')"
                :style="{ width: getStockWidth(item) }"
              ></div>
            </div>
          </div>

          <div class="mt-auto border-t border-gray-50 px-5 py-3 bg-gray-50/50 rounded-b-xl flex justify-between items-center gap-2 text-sm">
            <span class="text-gray-500 flex items-center gap-1 truncate max-w-[60%]">
              <span v-if="item.location">📍 {{ item.location }}</span>
            </span>
            <div class="flex gap-3">
              <button
                @click="viewDetails(item)"
                class="text-blue-600 hover:text-blue-700 font-medium hover:underline"
              >
                View Details
              </button>
              <button
                @click="editItem(item)"
                class="text-emerald-600 hover:text-emerald-700 font-medium hover:underline"
              >
                Edit Details
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div v-if="showModal" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-gray-900 bg-opacity-60 transition-opacity" @click="closeModal"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full max-h-[90vh] flex flex-col">
          <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100 flex-shrink-0">
            <h3 class="text-xl leading-6 font-bold text-gray-900">
              {{ isEditing ? 'Edit Product' : 'Add New Product' }}
            </h3>
            <p class="text-sm text-gray-500 mt-1">Fill in the details below to manage your inventory.</p>
          </div>

          <form @submit.prevent="submitForm" class="p-6 space-y-5 flex-1 overflow-y-auto min-h-0">
            <FormAlert :visible="!!formError" :message="formError" :fieldErrors="formFieldErrors" />

            <div class="grid grid-cols-2 gap-4">
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Item Name <span class="text-red-500">*</span></label>
                <input 
                  v-model="form.name" 
                  required 
                  type="text" 
                  :class="[
                    'w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm',
                    formFieldErrors.name ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
                  ]"
                  placeholder="e.g. Urea 46-0-0"
                >
                <p v-if="formFieldErrors.name" class="mt-1 text-xs text-red-600">
                  {{ Array.isArray(formFieldErrors.name) ? formFieldErrors.name[0] : formFieldErrors.name }}
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Category <span class="text-red-500">*</span></label>
                <select 
                  v-model="form.category" 
                  required 
                  :class="[
                    'w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm',
                    formFieldErrors.category ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
                  ]"
                >
                  <option value="" disabled>Select...</option>
                  <option value="seeds">Seeds</option>
                  <option value="fertilizer">Fertilizer</option>
                  <option value="pesticide">Pesticide</option>
                  <option value="equipment">Equipment</option>
                  <option value="tools">Tools</option>
                </select>
                <p v-if="formFieldErrors.category" class="mt-1 text-xs text-red-600">
                  {{ Array.isArray(formFieldErrors.category) ? formFieldErrors.category[0] : formFieldErrors.category }}
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unit <span class="text-red-500">*</span></label>
                <select 
                  v-model="form.unit" 
                  required 
                  :class="[
                    'w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm',
                    formFieldErrors.unit ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
                  ]"
                >
                  <option value="kg">Kilograms (kg)</option>
                  <option value="liters">Liters</option>
                  <option value="packets">Packets / Bags</option>
                  <option value="pieces">Pieces</option>
                  <option value="pounds">Pounds</option>
                </select>
                <p v-if="formFieldErrors.unit" class="mt-1 text-xs text-red-600">
                  {{ Array.isArray(formFieldErrors.unit) ? formFieldErrors.unit[0] : formFieldErrors.unit }}
                </p>
              </div>
            </div>

            <div class="bg-emerald-50 p-4 rounded-lg border border-emerald-100 grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-bold text-emerald-800 uppercase mb-1">Current Stock</label>
                <input 
                  v-model.number="form.current_stock" 
                  type="number" 
                  step="0.01" 
                  min="0" 
                  :class="[
                    'w-full rounded-md border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500',
                    formFieldErrors.current_stock ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
                  ]"
                >
                <p v-if="formFieldErrors.current_stock" class="mt-1 text-xs text-red-600">
                  {{ Array.isArray(formFieldErrors.current_stock) ? formFieldErrors.current_stock[0] : formFieldErrors.current_stock }}
                </p>
              </div>
              <div>
                <label class="block text-xs font-bold text-emerald-800 uppercase mb-1">Alert Level (Min)</label>
                <input 
                  v-model.number="form.minimum_stock" 
                  type="number" 
                  step="0.01" 
                  min="0" 
                  :class="[
                    'w-full rounded-md border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500',
                    formFieldErrors.minimum_stock ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
                  ]"
                >
                <p v-if="formFieldErrors.minimum_stock" class="mt-1 text-xs text-red-600">
                  {{ Array.isArray(formFieldErrors.minimum_stock) ? formFieldErrors.minimum_stock[0] : formFieldErrors.minimum_stock }}
                </p>
              </div>
              <div class="col-span-2">
                <label class="block text-xs font-bold text-emerald-800 uppercase mb-1">Unit Price ($)</label>
                <input 
                  v-model.number="form.unit_price" 
                  type="number" 
                  step="0.01" 
                  min="0" 
                  :class="[
                    'w-full rounded-md border-emerald-200 focus:border-emerald-500 focus:ring-emerald-500',
                    formFieldErrors.unit_price ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
                  ]"
                >
                <p v-if="formFieldErrors.unit_price" class="mt-1 text-xs text-red-600">
                  {{ Array.isArray(formFieldErrors.unit_price) ? formFieldErrors.unit_price[0] : formFieldErrors.unit_price }}
                </p>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Supplier</label>
                <input 
                  v-model="form.supplier" 
                  type="text" 
                  class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" 
                  placeholder="e.g. ABC Suppliers"
                >
                <p v-if="formFieldErrors.supplier" class="mt-1 text-xs text-red-600">
                  {{ Array.isArray(formFieldErrors.supplier) ? formFieldErrors.supplier[0] : formFieldErrors.supplier }}
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <input 
                  v-model="form.location" 
                  type="text" 
                  class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm" 
                  placeholder="e.g. Warehouse A"
                >
                <p v-if="formFieldErrors.location" class="mt-1 text-xs text-red-600">
                  {{ Array.isArray(formFieldErrors.location) ? formFieldErrors.location[0] : formFieldErrors.location }}
                </p>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Expiry Date</label>
                <input 
                  v-model="form.expiry_date" 
                  type="date" 
                  class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm"
                >
                <p v-if="formFieldErrors.expiry_date" class="mt-1 text-xs text-red-600">
                  {{ Array.isArray(formFieldErrors.expiry_date) ? formFieldErrors.expiry_date[0] : formFieldErrors.expiry_date }}
                </p>
              </div>
              <div class="col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Description / Notes</label>
                <textarea 
                  v-model="form.description" 
                  rows="2" 
                  class="w-full rounded-lg border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 shadow-sm"
                ></textarea>
                <p v-if="formFieldErrors.description" class="mt-1 text-xs text-red-600">
                  {{ Array.isArray(formFieldErrors.description) ? formFieldErrors.description[0] : formFieldErrors.description }}
                </p>
              </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100 flex-shrink-0">
              <button type="button" @click="closeModal" class="px-4 py-2 bg-white text-gray-700 border border-gray-300 rounded-lg hover:bg-gray-50 font-medium">
                Cancel
              </button>
              <button type="submit" :disabled="submitting || loading" class="px-6 py-2 bg-emerald-600 text-white rounded-lg hover:bg-emerald-700 shadow-sm font-medium disabled:opacity-50 disabled:cursor-not-allowed flex items-center">
                <svg v-if="submitting || loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ submitting || loading ? (isEditing ? 'Updating...' : 'Saving...') : (isEditing ? 'Update Item' : 'Save Item') }}
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
import { formatCurrency } from '@/utils/format'

const router = useRouter()

const store = useInventoryStore()
const items = computed(() => store.items || [])
const error = computed(() => store.error)
const loading = computed(() => store.loading)

const searchQuery = ref('')
const categoryFilter = ref('')
const showModal = ref(false)
const isEditing = ref(false)
const formError = ref('')
const formFieldErrors = ref({})
const submitting = ref(false)

const form = reactive({
  id: null,
  name: '',
  category: '',
  unit: 'kg',
  current_stock: 0,
  minimum_stock: 0,
  unit_price: 0,
  supplier: '',
  location: '',
  expiry_date: '',
  description: ''
})

// Statistics
const totalItems = computed(() => items.value.length)
const totalValue = computed(() => items.value.reduce((sum, i) => sum + (Number(i.current_stock || 0) * Number(i.unit_price || 0)), 0))
const lowStockItems = computed(() => items.value.filter(i => {
  const stock = Number(i.current_stock || 0)
  const min = Number(i.minimum_stock || 0)
  return stock > 0 && stock <= min
}).length)
const outOfStockItems = computed(() => items.value.filter(i => Number(i.current_stock || 0) <= 0).length)

// Filter Logic
const filteredItems = computed(() => {
  return items.value.filter(item => {
    const search = searchQuery.value.toLowerCase()
    const matchesSearch = !search || 
      item.name?.toLowerCase().includes(search) || 
      item.description?.toLowerCase().includes(search) ||
      item.location?.toLowerCase().includes(search)
      
    const matchesCategory = !categoryFilter.value || item.category === categoryFilter.value
    return matchesSearch && matchesCategory
  })
})

// Modal Actions
const openCreateModal = () => {
  isEditing.value = false
  formError.value = ''
  formFieldErrors.value = {}
  Object.assign(form, {
    id: null, 
    name: '', 
    category: '', 
    unit: 'kg', 
    current_stock: 0, 
    minimum_stock: 0, 
    unit_price: 0,
    supplier: '',
    location: '', 
    expiry_date: '', 
    description: ''
  })
  showModal.value = true
}

const viewDetails = (item) => {
  router.push(`/inventory/${item.id}`)
}

const editItem = (item) => {
  isEditing.value = true
  formError.value = ''
  formFieldErrors.value = {}
  // Normalize data from backend (handling legacy fields if any exist in old records)
  Object.assign(form, {
    id: item.id,
    name: item.name || '',
    category: item.category || '',
    unit: item.unit || 'kg',
    current_stock: Number(item.current_stock ?? item.quantity ?? 0),
    minimum_stock: Number(item.minimum_stock ?? item.min_stock ?? 0),
    unit_price: Number(item.unit_price ?? item.price ?? 0),
    supplier: item.supplier || '',
    location: item.location || '',
    expiry_date: item.expiry_date || '',
    description: item.description || ''
  })
  showModal.value = true
}

const closeModal = () => {
  showModal.value = false
  // Reset form errors when closing
  formError.value = ''
  formFieldErrors.value = {}
}

const submitForm = async () => {
  if (submitting.value) return
  
  formError.value = ''
  formFieldErrors.value = {}
  submitting.value = true
  
  try {
    // Clean up the payload - remove id for new items, convert empty strings to null
    const payload = { ...form }
    
    // Remove id when creating new item
    if (!isEditing.value) {
      delete payload.id
    }
    
    // Convert empty strings to null for optional fields
    if (payload.location === '') payload.location = null
    if (payload.expiry_date === '') payload.expiry_date = null
    if (payload.description === '') payload.description = null
    if (payload.supplier === '') payload.supplier = null
    if (payload.notes === '') payload.notes = null
    
    // Ensure numeric fields are numbers, not strings
    payload.current_stock = Number(payload.current_stock) || 0
    payload.minimum_stock = Number(payload.minimum_stock) || 0
    payload.unit_price = Number(payload.unit_price) || 0
    
    // Ensure we send clean data to backend
    if (isEditing.value) {
      await store.updateItem(form.id, payload)
    } else {
      await store.createItem(payload)
    }
    
    // Success - close modal and refresh
    closeModal()
    await store.fetchItems() // Refresh list
  } catch (e) {
    console.error('Form submission error:', e)
    
    // Extract validation errors if present
    if (e.response?.data?.errors && typeof e.response.data.errors === 'object') {
      formFieldErrors.value = e.response.data.errors
      formError.value = e.response?.data?.message || 'Please fix the errors below'
    } else {
      formError.value = e.response?.data?.message || 
                       e.message ||
                       "Failed to save item. Please check inputs."
      formFieldErrors.value = {}
    }
  } finally {
    submitting.value = false
  }
}

// Visual Helpers
const getStockWidth = (item) => {
  const stock = Number(item.current_stock || 0)
  const min = Number(item.minimum_stock || 0)
  // Logic: Bar is full if stock is > 3x minimum. If 0 stock, width is 0.
  const maxScale = Math.max(min * 3, stock * 1.2, 10)
  return `${Math.min((stock / maxScale) * 100, 100)}%`
}

const getStockColor = (item, type) => {
  const stock = Number(item.current_stock || 0)
  const min = Number(item.minimum_stock || 0)
  
  if (stock <= 0) return type === 'bg' ? 'bg-red-500' : 'text-red-600'
  if (stock <= min) return type === 'bg' ? 'bg-yellow-500' : 'text-yellow-600'
  return type === 'bg' ? 'bg-emerald-500' : 'text-emerald-700'
}

onMounted(() => {
  store.fetchItems()
})
</script>