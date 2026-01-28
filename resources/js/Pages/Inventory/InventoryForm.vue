<template>
  <form @submit.prevent="submitForm" class="space-y-8">
    <div v-if="formError" class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl flex items-start shadow-sm">
       <svg class="w-5 h-5 mr-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
       <div>
         <p class="font-medium">Please correct the following errors:</p>
         <p class="text-sm mt-1">{{ formError }}</p>
       </div>
    </div>

    <!-- Section 1: Item Identity -->
    <section class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100 space-y-6">
      <div class="flex items-center mb-4">
        <div class="h-12 w-12 rounded-xl bg-emerald-100 flex items-center justify-center mr-4">
          <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-semibold text-gray-900">Item Identity</h2>
          <p class="text-sm text-gray-600">Core details about this product.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="col-span-1 md:col-span-2">
          <label class="block text-sm font-semibold text-gray-700 mb-2">Item Name <span class="text-red-500">*</span></label>
          <input 
            v-model="form.name" 
            required 
            type="text" 
            :class="[
              'w-full rounded-lg border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition',
              formFieldErrors.name ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
            ]"
            placeholder="e.g. Urea 46-0-0"
          >
          <p v-if="formFieldErrors.name" class="mt-1 text-xs text-red-600">
            {{ Array.isArray(formFieldErrors.name) ? formFieldErrors.name[0] : formFieldErrors.name }}
          </p>
        </div>

        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Category <span class="text-red-500">*</span></label>
          <select 
            v-model="form.category" 
            required 
            :class="[
              'w-full rounded-lg border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition',
              formFieldErrors.category ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
            ]"
          >
            <option value="" disabled>Select category</option>
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
          <label class="block text-sm font-semibold text-gray-700 mb-2">Unit of Measure <span class="text-red-500">*</span></label>
          <select 
            v-model="form.unit" 
            required 
            :class="[
              'w-full rounded-lg border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition',
              formFieldErrors.unit ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
            ]"
          >
            <option v-for="opt in availableUnits" :key="opt.value" :value="opt.value">
              {{ opt.label }}
            </option>
          </select>
          <p v-if="formFieldErrors.unit" class="mt-1 text-xs text-red-600">
            {{ Array.isArray(formFieldErrors.unit) ? formFieldErrors.unit[0] : formFieldErrors.unit }}
          </p>
        </div>
      </div>
    </section>

    <!-- Section 2: Stock Management -->
    <section class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100 space-y-6">
      <div class="flex items-center mb-4">
        <div class="h-12 w-12 rounded-xl bg-blue-100 flex items-center justify-center mr-4">
          <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8v8m-4-5v5m-4-2v2m-2 4h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-semibold text-gray-900">Stock Management</h2>
          <p class="text-sm text-gray-600">Track quantities and set reorder alerts.</p>
        </div>
      </div>

      <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-6 rounded-xl border border-blue-100 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label class="block text-xs font-bold text-blue-800 uppercase mb-1">Current Stock</label>
          <input 
            v-model.number="form.current_stock" 
            type="number" 
            step="0.01" 
            min="0" 
            :class="[
              'w-full rounded-lg border-blue-200 focus:border-blue-500 focus:ring-blue-500 shadow-sm px-4 py-2',
              formFieldErrors.current_stock ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
            ]"
          >
          <p v-if="formFieldErrors.current_stock" class="mt-1 text-xs text-red-600">
            {{ Array.isArray(formFieldErrors.current_stock) ? formFieldErrors.current_stock[0] : formFieldErrors.current_stock }}
          </p>
        </div>
        <div>
          <label class="block text-xs font-bold text-blue-800 uppercase mb-1">Review Alert Level (Min)</label>
          <input 
            v-model.number="form.minimum_stock" 
            type="number" 
            step="0.01" 
            min="0" 
            :class="[
              'w-full rounded-lg border-blue-200 focus:border-blue-500 focus:ring-blue-500 shadow-sm px-4 py-2',
              formFieldErrors.minimum_stock ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
            ]"
          >
          <p v-if="formFieldErrors.minimum_stock" class="mt-1 text-xs text-red-600">
            {{ Array.isArray(formFieldErrors.minimum_stock) ? formFieldErrors.minimum_stock[0] : formFieldErrors.minimum_stock }}
          </p>
        </div>
        <div>
          <label class="block text-xs font-bold text-blue-800 uppercase mb-1">Unit Price</label>
          <div class="relative rounded-lg shadow-sm">
            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
              <span class="text-blue-500 sm:text-sm">₱</span>
            </div>
            <input 
              v-model.number="form.unit_price" 
              type="number" 
              step="0.01" 
              min="0" 
              :class="[
                'w-full rounded-lg border-blue-200 focus:border-blue-500 focus:ring-blue-500 pl-7 px-4 py-2 shadow-sm',
                formFieldErrors.unit_price ? 'border-red-300 focus:border-red-500 focus:ring-red-500' : ''
              ]"
            >
          </div>
          <p v-if="formFieldErrors.unit_price" class="mt-1 text-xs text-red-600">
            {{ Array.isArray(formFieldErrors.unit_price) ? formFieldErrors.unit_price[0] : formFieldErrors.unit_price }}
          </p>
        </div>
      </div>
    </section>

    <!-- Section 3: Logistics -->
    <section class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100 space-y-6">
      <div class="flex items-center mb-4">
        <div class="h-12 w-12 rounded-xl bg-amber-100 flex items-center justify-center mr-4">
          <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
          </svg>
        </div>
        <div>
          <h2 class="text-xl font-semibold text-gray-900">Logistics & Notes</h2>
          <p class="text-sm text-gray-600">Supplier details and storage information.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Supplier</label>
          <input 
            v-model="form.supplier" 
            type="text" 
            class="w-full rounded-lg border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition" 
            placeholder="e.g. ABC Suppliers"
          >
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Storage Location</label>
          <input 
            v-model="form.location" 
            type="text" 
            class="w-full rounded-lg border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition" 
            placeholder="e.g. Warehouse A, Shelf 3"
          >
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-2">Expiry Date</label>
          <input 
            v-model="form.expiry_date" 
            type="date" 
            class="w-full rounded-lg border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
          >
        </div>
        <div class="col-span-1 md:col-span-3">
          <label class="block text-sm font-semibold text-gray-700 mb-2">Description / Notes</label>
          <textarea 
            v-model="form.description" 
            rows="3" 
            class="w-full rounded-lg border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition resize-none"
            placeholder="Enter any additional details about this item..."
          ></textarea>
        </div>
      </div>
    </section>

    <!-- Actions -->
    <div class="flex flex-col sm:flex-row gap-4 justify-end pt-4">
      <button 
        type="button" 
        @click="$emit('cancel')" 
        class="inline-flex items-center justify-center px-6 py-3 rounded-xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-colors"
      >
        Cancel
      </button>
      <button 
        type="submit" 
        :disabled="submitting" 
        class="inline-flex items-center justify-center px-8 py-3 rounded-xl font-semibold text-white bg-gradient-to-r from-emerald-600 to-green-600 shadow-lg hover:shadow-xl hover:from-emerald-700 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-60 disabled:cursor-not-allowed transition-all"
      >
        <svg v-if="submitting" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        {{ submitting ? (isEditing ? 'Updating Item...' : 'Saving Item...') : (isEditing ? 'Update Product' : 'Create Product') }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { reactive, ref, onMounted, watch, computed } from 'vue'

const props = defineProps({
  item: {
    type: Object,
    default: null
  },
  isEditing: {
    type: Boolean,
    default: false
  },
  submitting: {
    type: Boolean,
    default: false
  },
  errors: {
    type: Object,
    default: () => ({})
  },
  errorMessage: {
    type: String,
    default: ''
  }
})

const emit = defineEmits(['submit', 'cancel'])

// Local error state to handle prop updates
const formError = ref('')
const formFieldErrors = ref({})

// Watch for error props
watch(() => props.errorMessage, (newVal) => formError.value = newVal)
watch(() => props.errors, (newVal) => formFieldErrors.value = newVal || {})

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

onMounted(() => {
  if (props.isEditing && props.item) {
    Object.assign(form, {
      id: props.item.id,
      name: props.item.name || '',
      category: props.item.category || '',
      unit: props.item.unit || 'kg',
      current_stock: Number(props.item.current_stock ?? props.item.quantity ?? 0),
      minimum_stock: Number(props.item.minimum_stock ?? props.item.min_stock ?? 0),
      unit_price: Number(props.item.unit_price ?? props.item.price ?? 0),
      supplier: props.item.supplier || '',
      location: props.item.location || '',
      expiry_date: props.item.expiry_date || '',
      description: props.item.description || ''
    })
  }
})

const submitForm = () => {
  // Clear local errors
  formError.value = ''
  formFieldErrors.value = {}
  
  // Clean payload
  const payload = { ...form }
  
  if (!props.isEditing) {
    delete payload.id
  }
  
  // Convert empty strings to null for optional fields
  if (!payload.location) payload.location = null
  if (!payload.expiry_date) payload.expiry_date = null
  if (!payload.description) payload.description = null
  if (!payload.supplier) payload.supplier = null
  
  // Ensure numerics
  payload.current_stock = Number(payload.current_stock) || 0
  payload.minimum_stock = Number(payload.minimum_stock) || 0
  payload.unit_price = Number(payload.unit_price) || 0
  
  emit('submit', payload)
}

// Dynamic Unit Logic
const unitConfig = {
  seeds: [
    { value: 'kg', label: 'Kilograms (kg)' },
    { value: 'bags', label: 'Bags' },
    { value: 'sacks', label: 'Sacks' },
    { value: 'packets', label: 'Packets' },
    { value: 'pounds', label: 'Pounds' }
  ],
  fertilizer: [
    { value: 'kg', label: 'Kilograms (kg)' },
    { value: 'bags', label: 'Bags' },
    { value: 'sacks', label: 'Sacks' },
    { value: 'liters', label: 'Liters (liquid)' }
  ],
  pesticide: [
    { value: 'liters', label: 'Liters' },
    { value: 'bottles', label: 'Bottles' },
    { value: 'gallons', label: 'Gallons' },
    { value: 'cans', label: 'Cans' }
  ],
  equipment: [
    { value: 'pieces', label: 'Pieces' },
    { value: 'sets', label: 'Sets' }
  ],
  tools: [
    { value: 'pieces', label: 'Pieces' },
    { value: 'sets', label: 'Sets' }
  ],
  default: [
    { value: 'pieces', label: 'Pieces' },
    { value: 'kg', label: 'Kilograms (kg)' },
    { value: 'liters', label: 'Liters' },
    { value: 'bags', label: 'Bags' }
  ]
}

const availableUnits = computed(() => {
  if (!form.category) return unitConfig.default
  return unitConfig[form.category.toLowerCase()] || unitConfig.default
})

// Auto-select first valid unit when category changes
watch(() => form.category, (newCategory) => {
  if (!newCategory) return
  
  const validUnits = availableUnits.value.map(u => u.value)
  // If current unit is not valid for new category, select the first valid one
  if (!validUnits.includes(form.unit)) {
    form.unit = validUnits[0]
  }
})
</script>
