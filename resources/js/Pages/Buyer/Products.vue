<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Rice Products</h1>
            <p class="text-sm text-gray-600 mt-1">Browse available rice products and pre-order options</p>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Filters -->
      <div class="mb-6 bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search products..."
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
              @input="debounceSearch"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
            <select
              v-model="filters.production_status"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
              @change="loadProducts"
            >
              <option value="">All</option>
              <option value="available">Available</option>
              <option value="in_production">In Production (Pre-order)</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
            <select
              v-model="filters.sort_by"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
              @change="loadProducts"
            >
              <option value="created_at">Newest</option>
              <option value="price">Price</option>
              <option value="rating">Rating</option>
              <option value="available_from">Available Date</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Quality Grade</label>
            <select
              v-model="filters.quality_grade"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
              @change="loadProducts"
            >
              <option value="">All Grades</option>
              <option value="grade_a">Grade A (Premium)</option>
              <option value="grade_b">Grade B</option>
              <option value="commercial">Commercial</option>
            </select>
          </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Min Price (₱)</label>
            <input
              v-model.number="filters.min_price"
              type="number"
              min="0"
              placeholder="Min"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
              @change="loadProducts"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Max Price (₱)</label>
            <input
              v-model.number="filters.max_price"
              type="number"
              min="0"
              placeholder="Max"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
              @change="loadProducts"
            />
          </div>
          <div class="flex items-end">
            <label class="flex items-center gap-2 cursor-pointer">
              <input
                type="checkbox"
                v-model="filters.is_organic"
                @change="loadProducts"
                class="w-4 h-4 text-green-600 rounded"
              />
              <span class="text-sm font-medium text-gray-700">Organic Only</span>
            </label>
          </div>
          <div class="flex items-end">
            <button
              @click="resetFilters"
              class="w-full px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors"
            >
              Reset Filters
            </button>
          </div>
        </div>
      </div>


      <!-- Loading State -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div v-for="n in 8" :key="n" class="bg-white rounded-lg shadow p-6 animate-pulse">
          <div class="h-48 bg-gray-200 rounded mb-4"></div>
          <div class="h-4 bg-gray-200 rounded mb-2"></div>
          <div class="h-4 bg-gray-200 rounded w-3/4"></div>
        </div>
      </div>

      <!-- Products Grid -->
      <div v-else-if="products.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div
          v-for="product in products"
          :key="product.id"
          class="bg-white rounded-lg shadow hover:shadow-md transition-shadow cursor-pointer"
          @click="viewProduct(product.id)"
        >
          <div class="p-6">
            <!-- Product Image -->
            <div class="h-48 bg-gradient-to-br from-green-100 to-emerald-100 rounded-lg mb-4 overflow-hidden">
              <img
                v-if="product.images && product.images.length > 0"
                :src="product.images[0]"
                :alt="product.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <span class="text-6xl">🌾</span>
              </div>
            </div>

            <!-- Product Info -->
            <h3 class="font-semibold text-gray-900 mb-2 line-clamp-2">{{ product.name }}</h3>
            <p class="text-sm text-gray-600 mb-3 line-clamp-2">{{ product.description }}</p>

            <!-- Production Status Badge -->
            <div class="mb-3">
              <span
                v-if="product.production_status === 'in_production'"
                class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800"
              >
                Pre-order Available
              </span>
              <span
                v-else-if="product.production_status === 'available'"
                class="inline-flex px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800"
              >
                Available Now
              </span>
            </div>

            <!-- Available From (for pre-orders) -->
            <div v-if="product.production_status === 'in_production' && product.available_from" class="mb-3">
              <p class="text-xs text-gray-500">
                Available from: {{ formatDate(product.available_from) }}
              </p>
            </div>

            <!-- Stock Info -->
            <div class="mb-3">
              <p class="text-sm text-gray-600">
                <span v-if="product.production_status === 'available'">
                  Stock: {{ product.quantity_available }} {{ product.unit }}
                </span>
                <span v-else>
                  Pre-order now
                </span>
              </p>
            </div>

            <!-- Price -->
            <div class="flex justify-between items-center mb-4">
              <span class="text-lg font-bold text-green-600">
                {{ formatCurrency(product.price_per_unit) }}/{{ product.unit }}
              </span>
              <div v-if="product.average_rating > 0" class="flex items-center">
                <span class="text-yellow-400 text-sm">★</span>
                <span class="text-sm text-gray-600 ml-1">{{ product.average_rating.toFixed(1) }}</span>
              </div>
            </div>

            <!-- Action Button -->
            <button
              @click.stop="viewProduct(product.id)"
              class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors"
            >
              {{ product.production_status === 'in_production' ? 'Pre-order Now' : 'View Details' }}
            </button>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="bg-white rounded-lg shadow p-12 text-center">
        <svg class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
        <p class="text-gray-600">Try adjusting your filters or search terms</p>
      </div>

      <!-- Pagination -->
      <div v-if="pagination && pagination.total > pagination.per_page" class="mt-8 flex justify-center">
        <nav class="flex space-x-2">
          <button
            @click="loadProducts(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-4 py-2 border border-gray-300 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
          >
            Previous
          </button>
          <span class="px-4 py-2 text-gray-700">
            Page {{ pagination.current_page }} of {{ pagination.last_page }}
          </span>
          <button
            @click="loadProducts(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-4 py-2 border border-gray-300 rounded-md disabled:opacity-50 disabled:cursor-not-allowed hover:bg-gray-50"
          >
            Next
          </button>
        </nav>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import { formatCurrency } from '@/utils/format'

const router = useRouter()

const loading = ref(false)
const products = ref([])
const pagination = ref(null)

const filters = ref({
  search: '',
  production_status: '',
  quality_grade: '',
  min_price: null,
  max_price: null,
  is_organic: false,
  sort_by: 'created_at',
  sort_order: 'desc',
  page: 1
})

let searchTimeout = null

const debounceSearch = () => {
  clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    filters.value.page = 1
    loadProducts()
  }, 500)
}

const loadProducts = async (page = 1) => {
  loading.value = true
  filters.value.page = page

  try {
    const params = {
      page: filters.value.page,
      sort_by: filters.value.sort_by,
      sort_order: filters.value.sort_order,
      per_page: 12
    }

    if (filters.value.search) {
      params.search = filters.value.search
    }

    if (filters.value.production_status) {
      params.production_status = filters.value.production_status
    }

    if (filters.value.quality_grade) {
      params.quality_grade = filters.value.quality_grade
    }

    if (filters.value.min_price) {
      params.min_price = filters.value.min_price
    }

    if (filters.value.max_price) {
      params.max_price = filters.value.max_price
    }

    if (filters.value.is_organic) {
      params.is_organic = 1
    }

    const response = await axios.get('/api/rice-marketplace/products', { params })
    
    products.value = response.data.products.data || response.data.products
    pagination.value = response.data.products
    
    // Handle both paginated and non-paginated responses
    if (response.data.products.current_page) {
      pagination.value = {
        current_page: response.data.products.current_page,
        last_page: response.data.products.last_page,
        per_page: response.data.products.per_page,
        total: response.data.products.total
      }
    } else {
      pagination.value = null
    }
  } catch (error) {
    console.error('Error loading products:', error)
    products.value = []
  } finally {
    loading.value = false
  }
}

const resetFilters = () => {
  filters.value = {
    search: '',
    production_status: '',
    quality_grade: '',
    min_price: null,
    max_price: null,
    is_organic: false,
    sort_by: 'created_at',
    sort_order: 'desc',
    page: 1
  }
  loadProducts()
}


const viewProduct = (productId) => {
  router.push(`/buyer/products/${productId}`)
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

onMounted(() => {
  loadProducts()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>






