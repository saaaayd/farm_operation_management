<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Standard Header -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">My Products</h1>
          <p class="text-gray-500 mt-1">Manage rice products offered in the marketplace.</p>
        </div>
        <div class="flex items-center gap-3">
          <button
            type="button"
            @click="refresh"
            :disabled="marketplaceStore.loading"
            class="flex items-center gap-2 bg-white text-gray-700 border border-gray-300 px-4 py-2 rounded-lg hover:bg-gray-50 transition-colors shadow-sm font-medium disabled:opacity-50"
          >
            <svg
              :class="['h-5 w-5', { 'animate-spin': marketplaceStore.loading }]"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Refresh
          </button>
          <button
            type="button"
            @click="router.push('/marketplace/product/create')"
            class="flex items-center gap-2 bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 transition-colors shadow-sm font-medium"
          >
            <span class="text-xl leading-none">+</span> Add Product
          </button>
        </div>
      </div>

      <div>
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
              type="button"
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
          type="button"
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
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
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
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="{
                      'bg-green-100 text-green-800': product.production_status === 'available',
                      'bg-blue-100 text-blue-800': product.production_status === 'in_production',
                      'bg-red-100 text-red-800': product.production_status === 'out_of_stock'
                    }"
                    class="px-2 py-1 text-xs font-medium rounded-full capitalize"
                  >
                    {{ product.production_status || 'available' }}
                  </span>
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
                      type="button"
                      @click="router.push(`/marketplace/product/${product.id}/edit`)"
                      class="text-green-600 hover:text-green-900"
                    >
                      Edit
                    </button>
                    <button
                      type="button"
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
      </div>
      <!-- Confirmation Modal -->
      <ConfirmationModal
        :show="showConfirmModal"
        title="Delete Product"
        :message="`Are you sure you want to delete ${productToDelete?.name}? This action cannot be undone.`"
        confirm-text="Delete"
        type="danger"
        @close="showConfirmModal = false"
        @confirm="deleteProduct"
      />
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useMarketplaceStore } from '@/stores/marketplace'
import { useAuthStore } from '@/stores/auth'
import ConfirmationModal from '@/Components/UI/ConfirmationModal.vue'

const router = useRouter()
const marketplaceStore = useMarketplaceStore()
const authStore = useAuthStore()

// Confirmation State
const showConfirmModal = ref(false)
const productToDelete = ref(null)

const qualityMap = {
  premium: 'Premium',
  grade_a: 'Grade A',
  grade_b: 'Grade B',
  commercial: 'Commercial'
}

const products = computed(() => marketplaceStore.farmerProducts || [])

const refresh = () => marketplaceStore.fetchFarmerProducts({ per_page: 100 })

const confirmDeleteProduct = (product) => {
  productToDelete.value = product
  showConfirmModal.value = true
}

const deleteProduct = async () => {
  if (!productToDelete.value) return
  showConfirmModal.value = false
  
  try {
    await marketplaceStore.deleteRiceProduct(productToDelete.value.id)
    productToDelete.value = null
  } catch (error) {
    console.error('Failed to delete product:', error)
  }
}

// Kept removeProduct for backward compatibility if needed, but updated to use confirmDeleteProduct 
// or simply replace usages. The template used removeProduct.
const removeProduct = (product) => {
  confirmDeleteProduct(product)
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
  if (!authStore.user) {
    console.warn('No user found in auth store')
    return
  }
  
  console.log('My Products page mounted, fetching products...')
  console.log('Current farmerProducts in store:', marketplaceStore.farmerProducts?.length || 0)
  
  // Always refresh products when mounting the page
  // This ensures we get the latest data, especially after creating a product
  try {
    await refresh()
    console.log('Products fetched successfully:', marketplaceStore.farmerProducts?.length || 0)
  } catch (error) {
    console.error('Error loading products:', error)
    console.error('Error details:', error.response?.data)
  }
})
</script>

