<template>
  <div class="min-h-screen bg-gray-50/50 font-sans text-gray-900">
    <div class="container mx-auto px-4 py-8 max-w-7xl">

      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">
            My Products
          </h1>
          <p class="text-gray-500 mt-1">Manage your inventory and marketplace listings</p>
        </div>

        <div class="flex items-center gap-3 w-full md:w-auto">
          <button
            type="button"
            @click="refresh"
            :disabled="marketplaceStore.loading"
            class="flex items-center justify-center gap-2 px-4 py-2 bg-white text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-gray-300 transition-all shadow-sm font-medium text-sm disabled:opacity-50 disabled:cursor-not-allowed w-full md:w-auto"
          >
            <svg
              :class="['h-4 w-4', { 'animate-spin': marketplaceStore.loading }]"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            <span class="hidden sm:inline">Refresh Data</span>
          </button>

          <button
            type="button"
            @click="router.push('/marketplace/product/create')"
            class="flex items-center justify-center gap-2 px-5 py-2 bg-emerald-600 text-white border border-transparent rounded-lg hover:bg-emerald-700 active:bg-emerald-800 transition-all shadow-sm font-medium text-sm w-full md:w-auto"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Add Product
          </button>
        </div>
      </div>

      <div class="space-y-6">

        <div v-if="marketplaceStore.error" class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-start gap-3">
          <svg class="h-5 w-5 text-red-500 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div class="flex-1">
            <h3 class="text-sm font-medium text-red-800">Unable to load products</h3>
            <p class="text-sm text-red-600 mt-1">{{ marketplaceStore.error }}</p>
            <button
              type="button"
              @click="refresh"
              class="mt-2 text-sm font-medium text-red-700 hover:text-red-900 underline"
            >
              Try again
            </button>
          </div>
        </div>

        <div v-else-if="marketplaceStore.loading && products.length === 0" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="border-b border-gray-100 bg-gray-50/50 px-6 py-4">
             <div class="flex gap-4">
               <div class="h-4 bg-gray-200 rounded w-24 animate-pulse"></div>
               <div class="h-4 bg-gray-200 rounded w-24 animate-pulse"></div>
             </div>
          </div>
          <div class="divide-y divide-gray-100">
             <div v-for="n in 5" :key="n" class="px-6 py-4 flex items-center justify-between">
                <div class="flex flex-col gap-2 w-1/3">
                  <div class="h-4 bg-gray-100 rounded w-3/4 animate-pulse"></div>
                  <div class="h-3 bg-gray-100 rounded w-1/2 animate-pulse"></div>
                </div>
                <div class="h-4 bg-gray-100 rounded w-24 animate-pulse"></div>
                <div class="h-4 bg-gray-100 rounded w-24 animate-pulse"></div>
                <div class="h-8 bg-gray-100 rounded w-8 animate-pulse"></div>
             </div>
          </div>
        </div>

        <div v-else-if="products.length === 0" class="bg-white rounded-xl shadow-sm border border-gray-200 p-12 text-center">
          <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="text-3xl">🌾</span>
          </div>
          <h2 class="text-lg font-semibold text-gray-900 mb-1">No products listed yet</h2>
          <p class="text-gray-500 mb-6 max-w-sm mx-auto">
            Start building your marketplace presence by adding your first rice variety.
          </p>
          <button
            type="button"
            @click="router.push('/marketplace/product/create')"
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-colors shadow-sm"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
            Add First Product
          </button>
        </div>

        <div v-else class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-100">
              <thead class="bg-gray-50/50">
                <tr>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Product Info</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Inventory</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Price</th>
                  <th scope="col" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Quality</th>
                  <th scope="col" class="px-6 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-100 bg-white">
                <tr
                  v-for="product in products"
                  :key="product.id"
                  class="group hover:bg-gray-50/80 transition-colors"
                >
                  <td class="px-6 py-4">
                    <div class="flex items-center">
                      <div>
                        <div class="text-sm font-semibold text-gray-900">{{ product.name }}</div>
                        <div class="text-xs text-gray-500 mt-0.5">
                          {{ product.rice_variety?.name || product.riceVariety?.name || 'Unknown Variety' }}
                          <span class="text-gray-300 mx-1">•</span>
                          Updated {{ formatDate(product.updated_at) }}
                        </div>
                      </div>
                    </div>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border',
                        product.production_status === 'available' ? 'bg-emerald-50 text-emerald-700 border-emerald-100' :
                        product.production_status === 'in_production' ? 'bg-blue-50 text-blue-700 border-blue-100' :
                        'bg-gray-50 text-gray-600 border-gray-200'
                      ]"
                    >
                      <span :class="[
                        'h-1.5 w-1.5 rounded-full',
                         product.production_status === 'available' ? 'bg-emerald-500' :
                         product.production_status === 'in_production' ? 'bg-blue-500' :
                         'bg-gray-400'
                      ]"></span>
                      {{ formatStatus(product.production_status) }}
                    </span>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-right">
                    <div class="text-sm font-medium text-gray-900">{{ formatQuantity(product.quantity_available) }}</div>
                    <div class="text-xs text-gray-500 uppercase">{{ product.unit || 'units' }}</div>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-right">
                     <div class="text-sm font-bold text-gray-900">₱{{ formatPrice(product.price_per_unit) }}</div>
                     <div class="text-xs text-gray-500">per {{ product.unit || 'unit' }}</div>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm text-gray-700 flex items-center gap-1.5">
                       <svg v-if="product.quality_grade === 'premium' || product.quality_grade === 'grade_a'" class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                       {{ qualityMap[product.quality_grade] || product.quality_grade }}
                    </div>
                  </td>

                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex items-center justify-end gap-2 opacity-100 sm:opacity-0 sm:group-hover:opacity-100 transition-opacity">
                      <button
                        type="button"
                        @click="router.push(`/marketplace/product/${product.id}/edit`)"
                        class="p-1.5 text-gray-500 hover:text-emerald-600 hover:bg-emerald-50 rounded-lg transition-colors"
                        title="Edit Product"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                      </button>
                      <button
                        type="button"
                        @click="removeProduct(product)"
                        class="p-1.5 text-gray-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                        title="Delete Product"
                      >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

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

const removeProduct = (product) => {
  confirmDeleteProduct(product)
}

const formatQuantity = (value) => {
  const num = Number(value)
  return Number.isNaN(num) ? value : num.toLocaleString('en-US')
}

const formatPrice = (value) => {
  const num = Number(value)
  return Number.isNaN(num) ? '0.00' : num.toFixed(2)
}

const formatStatus = (status) => {
  if (!status) return 'Available'
  return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const formatDate = (value) => {
  if (!value) return 'Recently'
  const date = new Date(value)
  return Number.isNaN(date.getTime()) ? 'Recently' : date.toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  })
}

onMounted(async () => {
  if (!authStore.user) {
    return
  }

  try {
    await refresh()
  } catch (error) {
    console.error('Error loading products:', error)
  }
})
</script>
