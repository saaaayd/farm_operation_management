<template>
  <div class="inventory-page min-h-screen bg-gray-50 font-sans">
    <div class="container mx-auto px-4 py-8">
      
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Inventory</h1>
          <p class="text-gray-500 mt-1">Track your farm supplies, seeds, and equipment.</p>
        </div>
        <div class="flex items-center gap-3">
          <button
            @click="exportCsv"
            class="flex items-center gap-2 bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-blue-700 transition-colors shadow-sm font-medium"
          >
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
            </svg>
            Export CSV
          </button>
          <button
            @click="router.push('/inventory/create')"
            class="flex items-center gap-2 bg-emerald-600 text-white px-5 py-2.5 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm font-medium"
          >
            <span class="text-xl leading-none">+</span> Add Product
          </button>
        </div>
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useInventoryStore } from '@/stores/inventory'
import FormAlert from '@/Components/UI/FormAlert.vue'
import { formatCurrency } from '@/utils/format'
import axios from 'axios'

const router = useRouter()
const store = useInventoryStore()

const items = computed(() => store.items || [])
const error = computed(() => store.error)
const loading = computed(() => store.loading)

const searchQuery = ref('')
const categoryFilter = ref('')

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

const viewDetails = (item) => {
  router.push(`/inventory/${item.id}`)
}

const editItem = (item) => {
  router.push(`/inventory/${item.id}/edit`)
}

const exportCsv = async () => {
  try {
    const response = await axios.get('/api/reports/export/inventory', {
      responseType: 'blob'
    })
    const url = window.URL.createObjectURL(new Blob([response.data]))
    const link = document.createElement('a')
    link.href = url
    link.setAttribute('download', `inventory_${new Date().toISOString().split('T')[0]}.csv`)
    document.body.appendChild(link)
    link.click()
    link.remove()
    window.URL.revokeObjectURL(url)
  } catch (err) {
    console.error('Failed to export:', err)
    alert('Failed to export CSV')
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