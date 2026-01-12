<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Standard Header -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Buyer Dashboard</h1>
          <p class="text-gray-500 mt-1">Welcome back, {{ authStore.user?.name }}</p>
        </div>
        <div class="flex items-center space-x-4">
          <router-link 
            to="/cart"
            class="relative p-2 text-gray-500 hover:text-gray-700 transition-colors bg-white rounded-lg border border-gray-300"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 3h1.5l1.5 12h11.5l1.5-8h-14" />
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.25 21a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM17.25 21a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
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
            class="p-2 text-gray-500 hover:text-gray-700 transition-colors bg-white rounded-lg border border-gray-300"
          >
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
            </svg>
          </button>
        </div>
      </div>

      <!-- Main Content -->
      <!-- Quick Actions -->
      <section class="mb-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <router-link 
            to="/buyer/products"
            class="bg-white rounded-lg shadow p-4 text-center hover:shadow-md transition-shadow"
          >
            <div class="h-8 w-8 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <p class="text-sm font-medium text-gray-900">Browse Rice Products</p>
          </router-link>

          <router-link 
            to="/cart"
            class="bg-white rounded-lg shadow p-4 text-center hover:shadow-md transition-shadow"
          >
            <div class="h-8 w-8 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
              <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M2.25 3h1.5l1.5 12h11.5l1.5-8h-14" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8.25 21a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM17.25 21a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
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
      </section>

      <!-- Welcome Section -->
      <div class="mb-8">
        <h2 class="text-2xl font-bold text-gray-900 mb-2">
          Fresh Rice Products
        </h2>
        <p class="text-gray-600">
          Discover premium rice varieties from local farmers
        </p>
      </div>

      <!-- Notifications Section -->
      <div v-if="notifications.length > 0" class="mb-8">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Notifications</h3>
        <div class="space-y-3">
          <div 
            v-for="notification in notifications" 
            :key="notification.id"
            class="bg-blue-50 border border-blue-100 rounded-lg p-4 flex items-start justify-between"
          >
            <div class="flex gap-3">
              <div class="mt-1">
                <svg class="h-5 w-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
              </div>
              <div>
                <h4 class="text-sm font-medium text-blue-900">{{ notification.title }}</h4>
                <p class="text-sm text-blue-700 mt-1">{{ notification.message }}</p>
                <router-link 
                  v-if="notification.link" 
                  :to="notification.link"
                  class="inline-block mt-2 text-xs font-medium text-blue-600 hover:text-blue-800 underline"
                  @click="markAsRead(notification)"
                >
                  View Details
                </router-link>
              </div>
            </div>
            <button 
              @click="markAsRead(notification)"
              class="text-blue-400 hover:text-blue-600"
              title="Mark as read"
            >
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <!-- Featured Products -->
      <div class="mb-10">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold text-gray-900">Available Products</h3>
          <router-link 
            to="/marketplace"
            class="text-green-600 hover:text-green-700 text-sm font-medium"
          >
            View marketplace
          </router-link>
        </div>
        <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div v-for="n in 4" :key="n" class="bg-white rounded-lg shadow p-6 animate-pulse">
            <div class="h-32 bg-gray-200 rounded mb-4"></div>
            <div class="h-4 bg-gray-200 rounded mb-2"></div>
            <div class="h-4 bg-gray-200 rounded w-3/4"></div>
          </div>
        </div>
        
        <div v-else-if="featuredProducts.length" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
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
              <p class="text-sm text-gray-600 mb-3">{{ getProductDescription(product) }}</p>
              <div class="flex justify-between items-center">
                <span class="text-lg font-bold text-green-600">
                  {{ formatCurrency(getProductPrice(product)) }}/{{ getProductUnit(product) }}
                </span>
                <span class="text-sm text-gray-500">
                  {{ getProductQuantity(product) }} available
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
        <div v-else class="bg-white rounded-lg shadow p-8 text-center">
          <svg class="h-12 w-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V7a2 2 0 00-2-2h-3.5a3.5 3.5 0 00-7 0H4a2 2 0 00-2 2v6" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 13h20l-1.5 7.5a2 2 0 01-2 1.5H5.5a2 2 0 01-2-1.5L2 13z" />
          </svg>
          <p class="text-gray-600 mb-4">No products available yet. Check back soon!</p>
          <router-link 
            to="/marketplace"
            class="text-green-600 hover:text-green-700 font-medium"
          >
            Explore marketplace
          </router-link>
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
                    {{ order.quantity || 1 }} {{ order.rice_product?.unit || 'kg' }} - {{ order.rice_product?.name || 'Product' }}
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
                    {{ formatCurrency(order.total_amount) }}
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
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api, { riceMarketplaceAPI, riceVarietiesAPI, cartAPI, notificationsAPI } from '@/services/api';
import { useAuthStore } from '@/stores/auth';
import { useMarketplaceStore } from '@/stores/marketplace';
import { formatCurrency } from '@/utils/format';

const router = useRouter();
const authStore = useAuthStore();
const marketplaceStore = useMarketplaceStore();

const loading = ref(false);
const notifications = ref([]);

const fetchNotifications = async () => {
  try {
    const response = await notificationsAPI.getAll({ unread_only: true });
    notifications.value = response.data.notifications.data || [];
  } catch (error) {
    console.error('Failed to fetch notifications:', error);
  }
};

const markAsRead = async (notification) => {
  try {
    await notificationsAPI.markAsRead(notification.id);
    notifications.value = notifications.value.filter(n => n.id !== notification.id);
  } catch (error) {
    console.error('Failed to mark notification as read:', error);
  }
};

// Toast notification state
const toast = ref({
  show: false,
  message: '',
  type: 'success'
});

const showToast = (message, type = 'success') => {
  toast.value = { show: true, message, type };
  setTimeout(() => {
    toast.value.show = false;
  }, 3000);
};

const deriveProducts = (source) => {
  if (!source) return [];
  if (Array.isArray(source?.data)) {
    return source.data;
  }
  return Array.isArray(source) ? source : [];
};

const availableProducts = computed(() => {
  const primary = deriveProducts(marketplaceStore.products);
  if (primary.length) return primary;
  return marketplaceStore.farmerProducts || [];
});

const featuredProducts = computed(() => {
  const list = availableProducts.value;
  if (list.length) return list.slice(0, 8);
  const fallback = marketplaceStore.riceProducts || [];
  return fallback.slice(0, 8);
});
const recentOrders = computed(() => marketplaceStore.orders);

const addToCart = async (product) => {
  try {
    await marketplaceStore.addToCart(product, 1);
    showToast(`Added ${product.name} to cart!`, 'success');
  } catch (error) {
    console.error('Failed to add to cart:', error);
    showToast(error.response?.data?.message || 'Failed to add to cart', 'error');
  }
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

const getProductPrice = (product) => {
  return product.price_per_unit ?? product.price ?? product.unit_price ?? 0;
};

const getProductQuantity = (product) => {
  return product.quantity_available ?? product.quantity ?? product.available_quantity ?? 0;
};

const getProductUnit = (product) => {
  return product.unit ?? product.unit_type ?? 'unit';
};

const getProductDescription = (product) => {
  return product.description || product.summary || 'Premium rice variety';
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
      marketplaceStore.fetchOrders(),
      fetchNotifications()
    ]);
  } catch (error) {
    console.error('Failed to load buyer dashboard data:', error);
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
