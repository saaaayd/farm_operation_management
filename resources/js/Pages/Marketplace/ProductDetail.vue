<template>
  <div class="product-detail-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Loading State -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
        <p class="mt-4 text-gray-600">Loading product data...</p>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error Loading Product</h3>
            <p class="mt-1 text-sm text-red-700">{{ error }}</p>
            <button
              @click="loadProductData(route.params.id)"
              class="mt-3 text-sm font-medium text-red-800 hover:text-red-900 underline"
            >
              Try again
            </button>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div v-else>
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <nav class="text-sm text-gray-500 mb-2">
            <router-link to="/marketplace" class="hover:text-gray-700">Marketplace</router-link>
            <span class="mx-2">/</span>
            <span class="text-gray-900">{{ product.name }}</span>
          </nav>
          <h1 class="text-3xl font-bold text-gray-900">{{ product.name }}</h1>
          <p class="text-gray-600 mt-2">Sold by {{ product.seller_name }}</p>
        </div>
        <div class="flex space-x-3">
          <button
            type="button"
            @click="addToCart"
            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            Add to Cart
          </button>
          <button
            type="button"
            @click="contactSeller"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Contact Seller
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Product Images -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <div class="aspect-w-16 aspect-h-9 bg-gray-200 rounded-lg mb-4">
              <div class="w-full h-96 bg-gray-200 flex items-center justify-center">
                <span class="text-gray-500 text-8xl">{{ product.icon }}</span>
              </div>
            </div>
            <div class="grid grid-cols-4 gap-2">
              <div class="aspect-w-1 aspect-h-1 bg-gray-200 rounded cursor-pointer">
                <div class="w-full h-20 bg-gray-200 flex items-center justify-center">
                  <span class="text-gray-500 text-2xl">{{ product.icon }}</span>
                </div>
              </div>
              <div class="aspect-w-1 aspect-h-1 bg-gray-200 rounded cursor-pointer">
                <div class="w-full h-20 bg-gray-200 flex items-center justify-center">
                  <span class="text-gray-500 text-2xl">{{ product.icon }}</span>
                </div>
              </div>
              <div class="aspect-w-1 aspect-h-1 bg-gray-200 rounded cursor-pointer">
                <div class="w-full h-20 bg-gray-200 flex items-center justify-center">
                  <span class="text-gray-500 text-2xl">{{ product.icon }}</span>
                </div>
              </div>
              <div class="aspect-w-1 aspect-h-1 bg-gray-200 rounded cursor-pointer">
                <div class="w-full h-20 bg-gray-200 flex items-center justify-center">
                  <span class="text-gray-500 text-2xl">{{ product.icon }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Product Description -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Description</h2>
            <p class="text-gray-700 leading-relaxed">{{ product.description }}</p>
          </div>

          <!-- Product Specifications -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Specifications</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="spec in product.specifications" :key="spec.name" class="flex justify-between">
                <span class="text-gray-600">{{ spec.name }}:</span>
                <span class="font-medium">{{ spec.value }}</span>
              </div>
            </div>
          </div>

          <!-- Reviews -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Reviews</h2>
            <div class="space-y-4">
              <div
                v-for="review in reviews"
                :key="review.id"
                class="border-b border-gray-200 pb-4 last:border-b-0"
              >
                <div class="flex items-start justify-between mb-2">
                  <div>
                    <div class="font-medium text-gray-900">{{ review.author }}</div>
                    <div class="flex items-center">
                      <div class="flex text-yellow-400">
                        <span v-for="i in 5" :key="i" class="text-sm">★</span>
                      </div>
                      <span class="text-sm text-gray-600 ml-2">{{ review.rating }}/5</span>
                    </div>
                  </div>
                  <span class="text-sm text-gray-500">{{ formatDate(review.date) }}</span>
                </div>
                <p class="text-gray-700">{{ review.comment }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Price and Purchase -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <div class="text-3xl font-bold text-green-600 mb-2">{{ formatCurrency(product.price) }}</div>
            <div class="text-gray-600 mb-4">{{ product.unit }}</div>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                <div class="flex items-center space-x-2">
                  <button
                    type="button"
                    @click="decreaseQuantity"
                    class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300"
                  >
                    -
                  </button>
                  <input
                    v-model="quantity"
                    type="number"
                    min="1"
                    :max="product.stock"
                    class="w-16 text-center border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                  <button
                    type="button"
                    @click="increaseQuantity"
                    class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300"
                  >
                    +
                  </button>
                </div>
              </div>
              
              <div class="text-sm text-gray-600">
                Stock: {{ product.stock }} available
              </div>
              
              <div class="text-lg font-semibold">
                Total: {{ formatCurrency(product.price * quantity) }}
              </div>
              
              <button
                type="button"
                @click="addToCart"
                class="w-full bg-green-600 text-white py-3 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
              >
                Add to Cart
              </button>
            </div>
          </div>

          <!-- Seller Information -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Seller Information</h3>
            <div class="space-y-3">
              <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                  <span class="text-gray-600 text-lg">👤</span>
                </div>
                <div>
                  <div class="font-medium text-gray-900">{{ product.seller_name }}</div>
                  <div class="text-sm text-gray-600">{{ product.location }}</div>
                </div>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-gray-600">Rating:</span>
                <div class="flex items-center">
                  <div class="flex text-yellow-400">
                    <span v-for="i in 5" :key="i" class="text-sm">★</span>
                  </div>
                  <span class="text-sm text-gray-600 ml-2">{{ product.rating }}/5</span>
                </div>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-gray-600">Member since:</span>
                <span class="text-sm">{{ product.member_since }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-gray-600">Products sold:</span>
                <span class="text-sm">{{ product.products_sold }}</span>
              </div>
            </div>
          </div>

          <!-- Shipping Information -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Shipping & Delivery</h3>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-gray-600">Shipping:</span>
                <span class="text-sm font-medium">{{ product.shipping_cost === 0 ? 'Free' : formatCurrency(product.shipping_cost) }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-600">Delivery:</span>
                <span class="text-sm">{{ product.delivery_time }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-600">Pickup available:</span>
                <span class="text-sm">{{ product.pickup_available ? 'Yes' : 'No' }}</span>
              </div>
            </div>
          </div>

          <!-- Related Products -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Related Products</h3>
            <div class="space-y-3">
              <div
                v-for="related in relatedProducts"
                :key="related.id"
                class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded cursor-pointer"
                @click="viewProduct(related.id)"
              >
                <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                  <span class="text-gray-500">{{ related.icon }}</span>
                </div>
                <div class="flex-1">
                  <div class="font-medium text-gray-900 text-sm">{{ related.name }}</div>
                  <div class="text-green-600 font-medium text-sm">{{ formatCurrency(related.price) }}</div>
                </div>
              </div>
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
import { riceMarketplaceAPI } from '@/services/api'

const route = useRoute()
const router = useRouter()
const quantity = ref(1)
const loading = ref(true)
const error = ref(null)

const product = ref({
  id: null,
  name: '',
  description: '',
  price: 0,
  unit: '',
  category: '',
  seller_name: '',
  location: '',
  rating: 0,
  icon: '',
  stock: 0,
  specifications: [],
  shipping_cost: 0,
  delivery_time: '',
  pickup_available: false,
  member_since: '',
  products_sold: 0
})

const reviews = ref([])
const relatedProducts = ref([])

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const increaseQuantity = () => {
  if (quantity.value < product.value.stock) {
    quantity.value++
  }
}

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const addToCart = () => {
  // Add to cart logic
  console.log('Add to cart:', product.value.id, quantity.value)
}

const contactSeller = () => {
  // Contact seller logic
  console.log('Contact seller')
}

const viewProduct = (id) => {
  router.push(`/marketplace/product/${id}`)
}

onMounted(() => {
  const productId = route.params.id
  // Load product data from API
  loadProductData(productId)
})

const loadProductData = async (id) => {
  try {
    loading.value = true
    error.value = null
    
    // Load product data from API
    const response = await riceMarketplaceAPI.getProductById(id)
    const data = response.data.data || response.data
    
    // Map API response to component data
    product.value = {
      id: data.id,
      name: data.name || '',
      description: data.description || '',
      price: data.price_per_unit || data.price || 0,
      unit: data.unit || 'kg',
      category: 'rice', // Rice marketplace products
      seller_name: data.farmer?.name || 'Farmer',
      location: data.farmer?.location || '',
      rating: data.average_rating || data.rating || 0,
      icon: '🌾',
      stock: data.quantity_available || data.quantity || 0,
      specifications: [
        { name: 'Variety', value: data.rice_variety?.name || 'N/A' },
        { name: 'Quality Grade', value: data.quality_grade || 'N/A' },
        { name: 'Organic', value: data.is_organic ? 'Yes' : 'No' },
        { name: 'Harvest Date', value: data.harvest_date ? formatDate(data.harvest_date) : 'N/A' },
        { name: 'Available From', value: data.available_from ? formatDate(data.available_from) : 'Now' },
      ],
      shipping_cost: 0, // Not in API
      delivery_time: 'Contact seller',
      pickup_available: true,
      member_since: data.farmer?.created_at ? new Date(data.farmer.created_at).getFullYear().toString() : 'N/A',
      products_sold: 0, // Not in API
      rice_variety: data.rice_variety,
      farmer: data.farmer,
      quality_grade: data.quality_grade,
      is_organic: data.is_organic,
      production_status: data.production_status,
      minimum_order_quantity: data.minimum_order_quantity,
    }
    
  } catch (err) {
    console.error('Error loading product data:', err)
    error.value = err.response?.data?.message || 'Failed to load product data'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
.product-detail-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>