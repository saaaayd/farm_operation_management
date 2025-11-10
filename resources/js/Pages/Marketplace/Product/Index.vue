<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">My Products</h1>
            <p class="text-sm text-gray-500">
              Manage rice products offered in the marketplace.
            </p>
          </div>
          <div class="flex items-center gap-3">
            <button
              @click="refresh"
              :disabled="marketplaceStore.loading"
              class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
            >
              <svg
                :class="['h-4 w-4 mr-2', { 'animate-spin': marketplaceStore.loading }]"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
              </svg>
              Refresh
            </button>
            <button
              @click="router.push('/marketplace/product/create')"
              class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md bg-green-600 text-white hover:bg-green-700"
            >
              Add Product
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="marketplaceStore.error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-700">{{ marketplaceStore.error }}</p>
            <button
              @click="refresh"
              class="mt-2 text-sm font-medium text-red-700 hover:text-red-800"
            >
              Try again
            </button>
          </div>
        </div>
      </div>

      <div v-else-if="marketplaceStore.loading && products.length === 0" class="space-y-4">
        <div
          v-for="n in 5"
          :key="n"
          class="bg-white rounded-lg shadow p-6 animate-pulse space-y-3"
        >
          <div class="h-4 bg-gray-200 rounded w-1/3"></div>
          <div class="h-3 bg-gray-200 rounded"></div>
          <div class="h-3 bg-gray-200 rounded w-2/3"></div>
        </div>
      </div>

      <div v-else-if="products.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <div class="text-5xl mb-4">🛒</div>
        <h2 class="text-lg font-semibold text-gray-900 mb-2">No products yet</h2>
        <p class="text-sm text-gray-600 mb-6">
          Publish your first rice product to reach marketplace buyers.
        </p>
        <button
          @click="router.push('/marketplace/product/create')"
          class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md bg-green-600 text-white hover:bg-green-700"
        >
          Add Product
        </button>
      </div>

      <div v-else class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Product</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Variety</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quality</th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="product in products"
                :key="product.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                  <div class="text-xs text-gray-500">Updated {{ formatDate(product.updated_at) }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                  {{ product.rice_variety?.name || product.riceVariety?.name || '—' }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                  {{ formatQuantity(product.quantity_available, product.unit) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                  {{ formatCurrency(product.price_per_unit, product.unit) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                  {{ qualityMap[product.quality_grade] || product.quality_grade }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end gap-2">
                    <button
                      @click="router.push(`/marketplace/product/${product.id}/edit`)"
                      class="text-green-600 hover:text-green-900"
                    >
                      Edit
                    </button>
                    <button
                      @click="removeProduct(product)"
                      class="text-red-600 hover:text-red-900"
                    >
                      Delete
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useMarketplaceStore } from '@/stores/marketplace'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const marketplaceStore = useMarketplaceStore()
const authStore = useAuthStore()

const qualityMap = {
  premium: 'Premium',
  grade_a: 'Grade A',
  grade_b: 'Grade B',
  commercial: 'Commercial'
}

const products = computed(() => marketplaceStore.farmerProducts || [])

const refresh = () => marketplaceStore.fetchFarmerProducts({ per_page: 100 })

const removeProduct = async (product) => {
  if (!confirm(`Delete ${product.name}? This action cannot be undone.`)) return

  try {
    await marketplaceStore.deleteRiceProduct(product.id)
  } catch (error) {
    console.error('Failed to delete product:', error)
  }
}

const formatQuantity = (value, unit) => {
  const num = Number(value)
  if (Number.isNaN(num)) return value
  return `${num.toLocaleString()} ${unit || ''}`
}

const formatCurrency = (value, unit) => {
  const num = Number(value)
  if (Number.isNaN(num)) return `₱${value}`
  return `₱${num.toFixed(2)} / ${unit || 'unit'}`
}

const formatDate = (value) => {
  if (!value) return 'Recently'
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? 'Recently' : date.toLocaleDateString()
}

onMounted(async () => {
  if (!authStore.user) return
  await refresh()
})
</script>

