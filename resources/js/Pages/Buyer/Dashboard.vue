<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="h-8 w-8 bg-green-600 rounded-full flex items-center justify-center">
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
              </div>
            </div>
            <div class="ml-3">
              <h1 class="text-xl font-semibold text-gray-900">RiceFARM Marketplace</h1>
              <p class="text-sm text-gray-500">Welcome back, {{ authStore.user?.name }}</p>
            </div>
          </div>
          
          <div class="flex items-center space-x-4">
            <router-link 
              to="/cart"
              class="relative p-2 text-gray-500 hover:text-gray-700 transition-colors"
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
            
            <button
              @click="logout"
              class="text-gray-500 hover:text-gray-700 transition-colors"
            >
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Welcome Section -->
      <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">
          Fresh Rice Products
        </h2>
        <p class="text-gray-600">
          Discover premium rice varieties from local farmers
        </p>
      </div>

      <!-- Featured Products -->
      <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Featured Products</h3>
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="n in 4" :key="n" class="bg-white rounded-lg shadow p-6 animate-pulse">
            <div class="h-32 bg-gray-200 rounded mb-4"></div>
            <div class="h-4 bg-gray-200 rounded mb-2"></div>
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
          </div>
        </div>
        
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div 
            v-for="product in featuredProducts" 
            :key="product.id"
            class="bg-white rounded-lg shadow hover:shadow-md transition-shadow cursor-pointer"
            @click="viewProduct(product)"
          >
            <div class="p-6">
              <div class="h-32 bg-gradient-to-br from-green-100 to-emerald-100 rounded-lg mb-4 flex items-center justify-center">
                <svg class="h-16 w-16 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
              </div>
              <h4 class="font-semibold text-gray-900 mb-2">{{ product.name }}</h4>
              <p class="text-sm text-gray-600 mb-3">{{ product.description || 'Premium rice variety' }}</p>
              <div class="flex justify-between items-center">
                <span class="text-lg font-bold text-green-600">
                  ${{ product.price }}/{{ product.unit }}
                </span>
                <span class="text-sm text-gray-500">
                  {{ product.quantity }} available
                </span>
              </div>
              <button 
                @click.stop="addToCart(product)"
                class="w-full mt-4 bg-green-600 text-white py-2 px-4 rounded-lg hover:bg-green-700 transition-colors"
              >
                Add to Cart
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Orders -->
      <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Recent Orders</h3>
          <router-link 
            to="/orders"
            class="text-green-600 hover:text-green-700 text-sm font-medium"
          >
            View all orders
          </router-link>
        </div>
        
        <div v-if="recentOrders.length > 0" class="bg-white rounded-lg shadow">
          <div class="divide-y divide-gray-200">
            <div 
              v-for="order in recentOrders.slice(0, 5)" 
              :key="order.id"
              class="p-6 hover:bg-gray-50 transition-colors"
            >
              <div class="flex items-center justify-between">
                <div>
                  <h4 class="text-sm font-medium text-gray-900">
                    Order #{{ order.id.toString().slice(-6) }}
                  </h4>
                  <p class="text-sm text-gray-500">
                    {{ order.order_items?.length || 0 }} items
                  </p>
                  <p class="text-xs text-gray-400">
                    {{ formatDate(order.created_at) }}
                  </p>
                </div>
                <div class="text-right">
                  <span 
                    :class="getOrderStatusClass(order.status)"
                    class="inline-flex px-2 py-1 text-xs font-medium rounded-full"
                  >
                    {{ order.status }}
                  </span>
                  <p class="text-sm font-medium text-gray-900 mt-1">
                    ${{ order.total_amount }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <div v-else class="bg-white rounded-lg shadow p-8 text-center">
          <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
          </svg>
          <p class="text-gray-600 mb-4">No orders yet</p>
          <router-link 
            to="/marketplace"
            class="text-green-600 hover:text-green-700 font-medium"
          >
            Start shopping
          </router-link>
        </div>
      </div>

      <!-- Quick Actions -->
      <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <router-link 
            to="/marketplace"
            class="bg-white rounded-lg shadow p-4 text-center hover:shadow-md transition-shadow"
          >
            <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <p class="text-sm font-medium text-gray-900">Browse Products</p>
          </router-link>

          <router-link 
            to="/cart"
            class="bg-white rounded-lg shadow p-4 text-center hover:shadow-md transition-shadow"
          >
            <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 11-4 0v-6m4 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01" />
              </svg>
            </div>
            <p class="text-sm font-medium text-gray-900">View Cart</p>
          </router-link>

          <router-link 
            to="/orders"
            class="bg-white rounded-lg shadow p-4 text-center hover:shadow-md transition-shadow"
          >
            <div class="h-8 w-8 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
              </svg>
            </div>
            <p class="text-sm font-medium text-gray-900">Order History</p>
          </router-link>

          <router-link 
            to="/profile"
            class="bg-white rounded-lg shadow p-4 text-center hover:shadow-md transition-shadow"
          >
            <div class="h-8 w-8 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <svg class="h-5 w-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
              </svg>
            </div>
            <p class="text-sm font-medium text-gray-900">Profile</p>
          </router-link>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import { useMarketplaceStore } from '@/stores/marketplace';

const router = useRouter();
const authStore = useAuthStore();
const marketplaceStore = useMarketplaceStore();

const loading = ref(false);

const featuredProducts = computed(() => marketplaceStore.riceProducts.slice(0, 4));
const recentOrders = computed(() => marketplaceStore.orders);

const addToCart = (product) => {
  marketplaceStore.addToCart(product, 1);
  // You could add a toast notification here
};

const viewProduct = (product) => {
  router.push(`/marketplace/products/${product.id}`);
};

const getOrderStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    processing: 'bg-purple-100 text-purple-800',
    shipped: 'bg-indigo-100 text-indigo-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800'
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const formatDate = (date) => {
  return new Date(date).toLocaleDateString();
};

const logout = async () => {
  await authStore.logout();
  router.push('/login');
};

onMounted(async () => {
  loading.value = true;
  try {
    await Promise.all([
      marketplaceStore.fetchProducts(),
      marketplaceStore.fetchOrders()
    ]);
  } catch (error) {
    console.error('Failed to load buyer dashboard data:', error);
  } finally {
    loading.value = false;
  }
});
</script>
