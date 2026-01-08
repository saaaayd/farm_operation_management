<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Standard Header -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Rice Marketplace</h1>
          <p class="text-gray-500 mt-1">Browse and purchase premium rice products</p>
        </div>
        <div class="flex items-center space-x-4">
          <router-link 
            to="/cart"
            class="relative p-2 text-gray-500 hover:text-gray-700 transition-colors bg-white rounded-lg border border-gray-300"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 11-4 0v-6m4 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01" />
            </svg>
            <span 
              v-if="marketplaceStore.cartItemsCount > 0"
              class="absolute -top-1 -right-1 h-5 w-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center"
            >
              {{ marketplaceStore.cartItemsCount }}
            </span>
          </router-link>
        </div>
      </div>

      <!-- Main Content -->
      <div>
      <!-- Search and Filters -->
      <div class="bg-white rounded-lg shadow p-6 mb-8">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-2">Search Products</label>
            <div class="relative">
              <input
                v-model="searchQuery"
                type="text"
                placeholder="Search for rice varieties..."
                class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
              />
              <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Rice Variety</label>
            <select 
              v-model="filters.variety" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
            >
              <option value="">All Varieties</option>
              <option value="IR64">IR64</option>
              <option value="Jasmine">Jasmine Rice</option>
              <option value="Basmati">Basmati Rice</option>
              <option value="Arborio">Arborio Rice</option>
              <option value="Brown Rice">Brown Rice</option>
              <option value="Sticky Rice">Sticky Rice</option>
              <option value="Wild Rice">Wild Rice</option>
            </select>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">Sort By</label>
            <select 
              v-model="filters.sortBy" 
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500"
            >
              <option value="name">Name</option>
              <option value="price_low">Price: Low to High</option>
              <option value="price_high">Price: High to Low</option>
              <option value="newest">Newest First</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Products Grid -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-for="n in 8" :key="n" class="bg-white rounded-lg shadow p-6 animate-pulse">
          <div class="h-48 bg-gray-200 rounded mb-4"></div>
          <div class="h-4 bg-gray-200 rounded mb-2"></div>
          <div class="h-4 bg-gray-200 rounded w-3/4"></div>
        </div>
      </div>

      <div v-else-if="filteredProducts.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div 
          v-for="product in filteredProducts" 
          :key="product.id"
          class="bg-white rounded-lg shadow hover:shadow-md transition-shadow cursor-pointer"
          @click="viewProduct(product)"
        >
          <div class="p-6">
            <!-- Product Image -->
            <div class="h-48 bg-gradient-to-br from-green-100 to-emerald-100 rounded-lg mb-4 flex items-center justify-center">
              <svg class="h-20 w-20 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>

            <!-- Product Info -->
            <div class="mb-4">
              <h3 class="text-lg font-semibold text-gray-900 mb-2">{{ product.name }}</h3>
              <p class="text-sm text-gray-600 mb-2">{{ product.description || 'Premium rice variety' }}</p>
              
              <!-- Farmer Info -->
              <div class="flex items-center text-sm text-gray-500 mb-2">
                <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                {{ product.farmer?.name || 'Local Farmer' }}
              </div>

              <!-- Quality Grade -->
              <div class="flex items-center mb-2">
                <span class="text-xs font-medium px-2 py-1 rounded-full bg-green-100 text-green-800">
                  Grade {{ product.quality_grade || 'A' }}
                </span>
              </div>
            </div>

            <!-- Price and Availability -->
            <div class="flex justify-between items-center mb-4">
              <div>
                <span class="text-xl font-bold text-green-600">
                  {{ formatCurrency(product.price_per_unit) }}/{{ product.unit || 'kg' }}
                </span>
              </div>
              <div class="text-sm text-gray-500">
                {{ product.quantity_available || 0 }} {{ product.unit || 'kg' }} available
              </div>
            </div>

            <!-- Actions -->
            <div class="space-y-2">
              <button 
                type="button"
                @click.stop="addToCart(product)"
                :disabled="!product.quantity_available || product.quantity_available <= 0"
                class="w-full bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
              >
                {{ !product.quantity_available || product.quantity_available <= 0 ? 'Out of Stock' : 'Add to Cart' }}
              </button>
              <button 
                type="button"
                @click.stop="viewProduct(product)"
                class="w-full bg-gray-200 text-gray-700 py-2 px-4 rounded-lg hover:bg-gray-300 transition-colors"
              >
                View Details
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12">
        <svg class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No products found</h3>
        <p class="text-gray-600 mb-4">Try adjusting your search or filters</p>
        <button 
          type="button"
          @click="clearFilters"
          class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors"
        >
          Clear Filters
        </button>
      </div>

      <!-- Pagination -->
      <div v-if="filteredProducts.length > 0" class="mt-8 flex justify-center">
        <nav class="flex items-center space-x-2">
          <button 
            type="button"
            @click="currentPage--"
            :disabled="currentPage === 1"
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Previous
          </button>
          
          <span class="px-3 py-2 text-sm font-medium text-gray-700">
            Page {{ currentPage }} of {{ totalPages }}
          </span>
          
          <button 
            type="button"
            @click="currentPage++"
            :disabled="currentPage === totalPages"
            class="px-3 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            Next
          </button>
        </nav>
      </div>
      </div>
    </div>

    <!-- Toast Notification -->
    <Transition name="toast">
      <div 
        v-if="toast.show" 
        class="fixed bottom-6 right-6 z-50 flex items-center gap-3 px-5 py-4 rounded-xl shadow-lg border"
        :class="{
          'bg-green-50 border-green-200 text-green-800': toast.type === 'success',
          'bg-red-50 border-red-200 text-red-800': toast.type === 'error'
        }"
      >
        <svg v-if="toast.type === 'success'" class="h-6 w-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <svg v-else class="h-6 w-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span class="font-medium">{{ toast.message }}</span>
      </div>
    </Transition>

    <!-- Quantity Selection Modal -->
    <div v-if="showQuantityModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl max-w-md w-full p-6 shadow-2xl">
        <h3 class="text-xl font-bold text-gray-900 mb-4">Add to Cart</h3>
        
        <!-- Product Info -->
        <div class="flex gap-4 mb-6">
          <div class="h-16 w-16 bg-gradient-to-br from-green-100 to-emerald-100 rounded-lg flex items-center justify-center flex-shrink-0">
            <svg class="h-8 w-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
            </svg>
          </div>
          <div>
            <h4 class="font-semibold text-gray-900">{{ selectedProduct?.name }}</h4>
            <p class="text-green-600 font-medium">{{ formatCurrency(selectedProduct?.price_per_unit) }}/{{ selectedProduct?.unit || 'kg' }}</p>
            <p class="text-sm text-gray-500">{{ selectedProduct?.quantity_available || 0 }} {{ selectedProduct?.unit || 'kg' }} available</p>
          </div>
        </div>

        <!-- Quantity Selector -->
        <div class="mb-6">
          <label class="block text-sm font-medium text-gray-700 mb-2">Quantity ({{ selectedProduct?.unit || 'kg' }})</label>
          <div class="flex items-center gap-4">
            <button 
              @click="selectedQuantity = Math.max(1, selectedQuantity - 1)"
              class="h-10 w-10 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors text-xl font-medium"
            >−</button>
            <input 
              v-model.number="selectedQuantity" 
              type="number" 
              min="1" 
              :max="selectedProduct?.quantity_available || 100"
              class="w-24 text-center text-lg font-semibold border-2 border-gray-300 rounded-lg py-2 focus:border-green-500 focus:outline-none"
            />
            <button 
              @click="selectedQuantity = Math.min(selectedProduct?.quantity_available || 100, selectedQuantity + 1)"
              class="h-10 w-10 rounded-full border-2 border-gray-300 flex items-center justify-center hover:bg-gray-50 transition-colors text-xl font-medium"
            >+</button>
          </div>
          <p class="text-sm text-gray-500 mt-2">
            Subtotal: <span class="font-semibold text-gray-900">{{ formatCurrency((selectedProduct?.price_per_unit || 0) * selectedQuantity) }}</span>
          </p>
        </div>

        <!-- Actions -->
        <div class="flex gap-3">
          <button 
            @click="showQuantityModal = false"
            class="flex-1 px-4 py-3 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors"
          >
            Cancel
          </button>
          <button 
            @click="confirmAddToCart"
            class="flex-1 px-4 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 font-medium transition-colors"
          >
            Add to Cart
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import { useRouter } from 'vue-router';
import { useMarketplaceStore } from '@/stores/marketplace';
import { formatCurrency } from '@/utils/format';

const router = useRouter();
const marketplaceStore = useMarketplaceStore();

const loading = ref(false);
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = 12;

const filters = ref({
  variety: '',
  sortBy: 'name'
});

const products = computed(() => marketplaceStore.riceProducts);
const filteredProducts = computed(() => {
  let filtered = products.value;

  // Search filter
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    filtered = filtered.filter(product => 
      product.name.toLowerCase().includes(query) ||
      product.description?.toLowerCase().includes(query) ||
      product.farmer?.name?.toLowerCase().includes(query)
    );
  }

  // Variety filter
  if (filters.value.variety) {
    filtered = filtered.filter(product => 
      product.name.toLowerCase().includes(filters.value.variety.toLowerCase())
    );
  }

  // Sort
  switch (filters.value.sortBy) {
    case 'price_low':
      filtered.sort((a, b) => a.price - b.price);
      break;
    case 'price_high':
      filtered.sort((a, b) => b.price - a.price);
      break;
    case 'newest':
      filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
      break;
    default:
      filtered.sort((a, b) => a.name.localeCompare(b.name));
  }

  // Pagination
  const start = (currentPage.value - 1) * itemsPerPage;
  const end = start + itemsPerPage;
  return filtered.slice(start, end);
});

const totalPages = computed(() => {
  return Math.ceil(products.value.length / itemsPerPage);
});

// Toast notification state
const toast = ref({
  show: false,
  message: '',
  type: 'success'
});

// Quantity modal state
const showQuantityModal = ref(false);
const selectedProduct = ref(null);
const selectedQuantity = ref(1);

const showToast = (message, type = 'success') => {
  toast.value = { show: true, message, type };
  setTimeout(() => {
    toast.value.show = false;
  }, 3000);
};

const addToCart = (product) => {
  selectedProduct.value = product;
  selectedQuantity.value = 1;
  showQuantityModal.value = true;
};

const confirmAddToCart = () => {
  if (selectedProduct.value && selectedQuantity.value > 0) {
    marketplaceStore.addToCart(selectedProduct.value, selectedQuantity.value);
    showQuantityModal.value = false;
    showToast(`Added ${selectedQuantity.value} ${selectedProduct.value.unit || 'kg'} of ${selectedProduct.value.name} to cart!`, 'success');
  }
};

const viewProduct = (product) => {
  router.push(`/marketplace/products/${product.id}`);
};

const clearFilters = () => {
  searchQuery.value = '';
  filters.value = {
    variety: '',
    sortBy: 'name'
  };
  currentPage.value = 1;
};

// Reset pagination when filters change
watch([searchQuery, filters], () => {
  currentPage.value = 1;
});

onMounted(async () => {
  loading.value = true;
  try {
    await marketplaceStore.fetchProducts();
  } catch (error) {
    console.error('Failed to load products:', error);
  } finally {
    loading.value = false;
  }
});
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.3s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(100px);
}
</style>