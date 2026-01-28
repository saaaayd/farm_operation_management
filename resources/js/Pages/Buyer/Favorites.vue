<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Standard Header -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">My Favorites</h1>
          <p class="text-gray-500 mt-1">Products you've saved for later</p>
        </div>
      </div>

      <!-- Main Content -->
      <!-- Loading State -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="n in 4" :key="n" class="bg-white rounded-lg shadow p-6 animate-pulse">
          <div class="h-48 bg-gray-200 rounded mb-4"></div>
          <div class="h-4 bg-gray-200 rounded mb-2"></div>
          <div class="h-4 bg-gray-200 rounded w-3/4"></div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="text-center py-16">
        <div class="text-6xl mb-4">⚠️</div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Failed to load favorites</h3>
        <p class="text-gray-500 mb-6">{{ error }}</p>
        <button 
          @click="loadFavorites" 
          class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium"
        >
          Try Again
        </button>
      </div>

      <!-- Empty State -->
      <div v-else-if="favorites.length === 0" class="text-center py-16">
        <div class="text-6xl mb-4">❤️</div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No favorites yet</h3>
        <p class="text-gray-500 mb-6">Start adding rice products you love and they'll appear here</p>
        <router-link 
          to="/marketplace" 
          class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium"
        >
          Browse Products
        </router-link>
      </div>

      <!-- Favorites Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="item in favorites"
          :key="item.id"
          class="bg-white rounded-lg shadow hover:shadow-md transition-shadow"
        >
          <div class="p-6">
            <!-- Product Image -->
            <div class="h-48 bg-gradient-to-br from-green-100 to-emerald-100 rounded-lg mb-4 overflow-hidden relative">
              <img
                v-if="item.rice_product?.images?.length"
                :src="item.rice_product.images[0]"
                :alt="item.rice_product.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <span class="text-5xl">🌾</span>
              </div>
              <!-- Remove Button -->
              <button
                @click="removeFavorite(item.id)"
                :disabled="removingId === item.id"
                class="absolute top-2 right-2 w-8 h-8 bg-white rounded-full shadow-md flex items-center justify-center hover:bg-red-50 transition-colors disabled:opacity-50"
              >
                <svg v-if="removingId === item.id" class="w-5 h-5 text-gray-400 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <svg v-else class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>
              </button>
            </div>

            <!-- Product Info -->
            <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ item.rice_product?.name || 'Product' }}</h3>
            <p class="text-sm text-gray-500 mb-2">{{ item.rice_product?.rice_variety?.name || 'Rice' }}</p>
            
            <div class="flex items-center justify-between mb-4">
              <span class="text-lg font-bold text-green-600">
                ₱{{ Number(item.rice_product?.price_per_unit || 0).toLocaleString() }}
              </span>
              <span class="text-sm text-gray-500">per {{ item.rice_product?.unit || 'kg' }}</span>
            </div>

            <!-- Stock Status -->
            <div class="mb-4">
              <span 
                v-if="item.rice_product?.quantity_available > 0"
                class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800"
              >
                {{ item.rice_product.quantity_available }} {{ item.rice_product.unit }} available
              </span>
              <span 
                v-else
                class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800"
              >
                Out of stock
              </span>
            </div>

            <!-- Actions -->
            <div class="space-y-2">
              <router-link
                :to="`/marketplace/products/${item.rice_product_id}`"
                class="block w-full text-center bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors font-medium"
              >
                View Details
              </router-link>
              <button
                @click="addToCart(item.rice_product)"
                :disabled="!item.rice_product?.quantity_available"
                class="w-full text-center bg-gray-100 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-200 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
              >
                Add to Cart
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useMarketplaceStore } from '@/stores/marketplace'
import api from '@/services/api'

const router = useRouter()
const marketplaceStore = useMarketplaceStore()

const loading = ref(false)
const error = ref(null)
const favorites = ref([])
const removingId = ref(null)



const loadFavorites = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await api.get('/rice-marketplace/favorites')
    favorites.value = response.data.favorites || []
  } catch (err) {
    console.error('Failed to load favorites:', err)
    error.value = err.response?.data?.message || 'Failed to load favorites'
    favorites.value = []
  } finally {
    loading.value = false
  }
}

const removeFavorite = async (id) => {
  removingId.value = id
  try {
    await api.delete(`/rice-marketplace/favorites/${id}`)
    // Remove from local state
    favorites.value = favorites.value.filter(f => f.id !== id)
    // Global toast handles success
  } catch (err) {
    console.error('Failed to remove favorite:', err)
    // Global toast handles error via interceptor
  } finally {
    removingId.value = null
  }
}

const addToCart = async (product) => {
  if (!product) return
  try {
    await marketplaceStore.addToCart(product, 1)
    // Global toast handles success
  } catch (err) {
    console.error('Failed to add to cart:', err)
    // Global toast handles error
  }
}

onMounted(() => {
  loadFavorites()
})
</script>

<style scoped>
</style>
