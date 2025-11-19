<template>
  <div class="inventory-detail-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <nav class="text-sm text-gray-500 mb-2">
            <router-link to="/inventory" class="hover:text-gray-700">Inventory</router-link>
            <span class="mx-2">/</span>
            <span class="text-gray-900">{{ item.name }}</span>
          </nav>
          <h1 class="text-3xl font-bold text-gray-900">{{ item.name }}</h1>
          <p class="text-gray-600 mt-2">{{ item.description }}</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="editItem"
            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500"
          >
            Edit Item
          </button>
          <button
            @click="adjustStock"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Adjust Stock
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Item Overview -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Item Overview</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="text-center">
                <div class="text-2xl font-bold text-blue-600">{{ item.quantity }} {{ item.unit }}</div>
                <div class="text-sm text-gray-600">Current Stock</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-green-600">{{ formatCurrency(item.unit_price) }}</div>
                <div class="text-sm text-gray-600">Unit Price</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-yellow-600">{{ formatCurrency(totalValue) }}</div>
                <div class="text-sm text-gray-600">Total Value</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-purple-600">{{ item.category }}</div>
                <div class="text-sm text-gray-600">Category</div>
              </div>
            </div>
          </div>

          <!-- Stock Level -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Stock Level</h2>
            <div class="space-y-4">
              <div class="flex justify-between items-center">
                <span class="text-gray-600">Current Stock</span>
                <span class="font-medium">{{ item.quantity }} {{ item.unit }}</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div
                  :class="getStockBarClass()"
                  class="h-3 rounded-full transition-all duration-300"
                  :style="{ width: `${stockPercentage}%` }"
                ></div>
              </div>
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Min Stock:</span>
                    <span class="font-medium">{{ item.min_stock }} {{ item.unit }}</span>
                  </div>
                </div>
                <div>
                  <div class="flex justify-between text-sm mb-1">
                    <span class="text-gray-600">Max Stock:</span>
                    <span class="font-medium">{{ item.max_stock }} {{ item.unit }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Recent Transactions -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Recent Transactions</h2>
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="transaction in transactions" :key="transaction.id">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ formatDate(transaction.date) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                      <span
                        :class="getTransactionBadgeClass(transaction.type)"
                        class="px-2 py-1 text-xs font-medium rounded-full"
                      >
                        {{ transaction.type }}
                      </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                      {{ transaction.quantity > 0 ? '+' : '' }}{{ transaction.quantity }} {{ item.unit }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ transaction.reason }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                      {{ transaction.user }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>

          <!-- Usage History -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Usage History</h2>
            <div class="space-y-4">
              <div
                v-for="usage in usageHistory"
                :key="usage.id"
                class="flex items-start space-x-3 p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex-shrink-0">
                  <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                    <span class="text-blue-600 text-sm">📊</span>
                  </div>
                </div>
                <div class="flex-1">
                  <div class="font-medium text-gray-900">{{ usage.activity }}</div>
                  <div class="text-sm text-gray-600">{{ usage.description }}</div>
                  <div class="text-xs text-gray-500 mt-1">{{ formatDate(usage.date) }}</div>
                </div>
                <div class="text-sm font-medium text-gray-900">
                  {{ usage.quantity }} {{ item.unit }}
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Item Details -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Item Details</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">SKU:</span>
                <span class="font-medium">{{ item.sku || 'N/A' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Category:</span>
                <span class="font-medium">{{ item.category }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Unit:</span>
                <span class="font-medium">{{ item.unit }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Status:</span>
                <span
                  :class="getStatusBadgeClass(item.status)"
                  class="px-2 py-1 text-xs font-medium rounded-full"
                >
                  {{ item.status }}
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Last Updated:</span>
                <span class="font-medium">{{ formatDate(item.updated_at) }}</span>
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Quick Actions</h3>
            <div class="space-y-3">
              <button
                @click="addStock"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                ➕ Add Stock
              </button>
              <button
                @click="removeStock"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                ➖ Remove Stock
              </button>
              <button
                @click="setReorderPoint"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                📋 Set Reorder Point
              </button>
              <button
                @click="viewSuppliers"
                class="w-full text-left px-3 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
              >
                🏪 View Suppliers
              </button>
            </div>
          </div>

          <!-- Suppliers -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Suppliers</h3>
            <div class="space-y-3">
              <div
                v-for="supplier in suppliers"
                :key="supplier.id"
                class="p-3 border border-gray-200 rounded-lg"
              >
                <div class="font-medium text-gray-900">{{ supplier.name }}</div>
                <div class="text-sm text-gray-600">{{ supplier.contact }}</div>
                <div class="text-sm text-gray-600">{{ formatCurrency(supplier.price) }} per {{ item.unit }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { formatCurrency } from '@/utils/format'
import { useInventoryStore } from '@/stores/inventory'
import api from '@/services/api'

const route = useRoute()
const router = useRouter()
const inventoryStore = useInventoryStore()
const loading = computed(() => inventoryStore.loading)
const error = ref('')

const item = ref({
  id: null,
  name: '',
  description: '',
  category: '',
  quantity: 0,
  unit: '',
  unit_price: 0,
  min_stock: 0,
  max_stock: 0,
  status: '',
  sku: '',
  updated_at: ''
})

const transactions = ref([
  {
    id: 1,
    type: 'in',
    quantity: 25,
    reason: 'Purchase order received',
    user: 'John Smith',
    date: '2024-03-25T10:00:00Z'
  },
  {
    id: 2,
    type: 'out',
    quantity: -5,
    reason: 'Used for field application',
    user: 'Mike Johnson',
    date: '2024-03-24T14:30:00Z'
  },
  {
    id: 3,
    type: 'in',
    quantity: 30,
    reason: 'Initial stock',
    user: 'Admin',
    date: '2024-03-20T09:00:00Z'
  }
])

const usageHistory = ref([
  {
    id: 1,
    activity: 'Field Application',
    description: 'Applied to North Field',
    quantity: 5,
    date: '2024-03-24T14:30:00Z'
  },
  {
    id: 2,
    activity: 'Field Application',
    description: 'Applied to South Field',
    quantity: 3,
    date: '2024-03-22T10:15:00Z'
  }
])

const suppliers = ref([
  {
    id: 1,
    name: 'AgriSupply Co.',
    contact: 'contact@agrisupply.com',
    price: 180.00
  },
  {
    id: 2,
    name: 'Farm Depot',
    contact: 'sales@farmdepot.com',
    price: 175.00
  }
])

const totalValue = computed(() => {
  return item.value.quantity * item.value.unit_price
})

const stockPercentage = computed(() => {
  if (item.value.max_stock === 0) return 0
  return Math.min((item.value.quantity / item.value.max_stock) * 100, 100)
})

const getStatusBadgeClass = (status) => {
  const classes = {
    in_stock: 'bg-green-100 text-green-800',
    low_stock: 'bg-yellow-100 text-yellow-800',
    out_of_stock: 'bg-red-100 text-red-800'
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const getTransactionBadgeClass = (type) => {
  const classes = {
    in: 'bg-green-100 text-green-800',
    out: 'bg-red-100 text-red-800',
    adjustment: 'bg-blue-100 text-blue-800'
  }
  return classes[type] || 'bg-gray-100 text-gray-800'
}

const getStockBarClass = () => {
  const percentage = stockPercentage.value
  if (percentage < 20) return 'bg-red-600'
  if (percentage < 50) return 'bg-yellow-600'
  return 'bg-green-600'
}

const formatDate = (date) => {
  if (!date) return 'Not set'
  return new Date(date).toLocaleDateString()
}

const editItem = () => {
  // Could navigate to a dedicated edit route; keeping inline for now
  console.log('Edit item')
}

const adjustStock = () => {
  // Show stock adjustment modal
  console.log('Adjust stock')
}

const addStock = async () => {
  const quantity = Number(prompt('Enter quantity to add:', '1'))
  if (!quantity || Number.isNaN(quantity) || quantity <= 0) return
  try {
    await inventoryStore.addStock(item.value.id, quantity)
    await reloadFromStore()
  } catch (e) {
    console.error('Failed to add stock', e)
    error.value = e.userMessage || e.response?.data?.message || 'Failed to add stock'
  }
}

const removeStock = async () => {
  const quantity = Number(prompt('Enter quantity to remove:', '1'))
  if (!quantity || Number.isNaN(quantity) || quantity <= 0) return
  try {
    await inventoryStore.removeStock(item.value.id, quantity)
    await reloadFromStore()
  } catch (e) {
    console.error('Failed to remove stock', e)
    error.value = e.userMessage || e.response?.data?.message || 'Failed to remove stock'
  }
}

const setReorderPoint = async () => {
  const currentReorderPoint = item.value.reorder_point || 0
  const newReorderPoint = prompt('Set reorder point (minimum stock level):', currentReorderPoint.toString())
  if (newReorderPoint === null) return
  
  const reorderPoint = Number(newReorderPoint)
  if (isNaN(reorderPoint) || reorderPoint < 0) {
    alert('Please enter a valid positive number')
    return
  }
  
  try {
    await inventoryStore.updateItem(item.value.id, { reorder_point: reorderPoint })
    await reloadFromStore()
    alert('Reorder point updated successfully')
  } catch (error) {
    console.error('Failed to set reorder point:', error)
    alert('Failed to set reorder point: ' + (error.response?.data?.message || 'Unknown error'))
  }
}

const viewSuppliers = () => {
  // Navigate to suppliers page if route exists
  router.push('/suppliers').catch(() => {
    alert('Suppliers page is not available yet')
  })
}

onMounted(async () => {
  const itemId = Number(route.params.id)
  await loadItemData(itemId)
})

const loadItemData = async (id) => {
  try {
    // Attempt to find in store first
    if (!inventoryStore.items.length) {
      await inventoryStore.fetchItems()
    }
    const found = inventoryStore.items.find(it => Number(it.id) === Number(id))
    if (found) {
      item.value = found
      return
    }
    // Fallback to direct API call
    const resp = await api.get(`/inventory/${id}`)
    const payload = resp?.data
    const entity = payload?.inventory_item || payload?.item || payload
    if (entity && entity.id) {
      item.value = entity
      return
    }
    console.warn('Item not found on server, going back to list')
    router.push('/inventory')
  } catch (e) {
    console.error('Error loading item data:', e)
    error.value = e.userMessage || e.response?.data?.message || 'Unable to load item'
  }
}
</script>

<style scoped>
.inventory-detail-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>