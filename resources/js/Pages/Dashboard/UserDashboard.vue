<template>
  <div class="p-6 max-w-7xl mx-auto">
    <!-- Header -->
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-900">Rice Marketplace</h1>
      <p class="text-gray-600 mt-2">Welcome back, {{ authStore.user?.name }}! Discover fresh rice products from local farmers.</p>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <ShoppingCartIcon class="h-8 w-8 text-blue-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">My Orders</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.total_orders || 0 }}</p>
            <p class="text-xs text-gray-500">{{ stats.pending_orders || 0 }} pending</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <HeartIcon class="h-8 w-8 text-red-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Favorites</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.favorites || 0 }}</p>
            <p class="text-xs text-gray-500">Saved products</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <CurrencyDollarIcon class="h-8 w-8 text-green-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Total Spent</p>
            <p class="text-2xl font-bold text-gray-900">${{ stats.total_spent || 0 }}</p>
            <p class="text-xs text-gray-500">This year</p>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <SparklesIcon class="h-8 w-8 text-purple-600" />
          </div>
          <div class="ml-4">
            <p class="text-sm font-medium text-gray-500">Available Products</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.available_products || 0 }}</p>
            <p class="text-xs text-gray-500">Fresh rice varieties</p>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      <!-- Left Column -->
      <div class="lg:col-span-2 space-y-8">
        <!-- Featured Products -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-900">Featured Rice Products</h2>
            <router-link
              to="/marketplace"
              class="text-sm text-green-600 hover:text-green-700 font-medium"
            >
              View all products
            </router-link>
          </div>
          <div class="p-6">
            <div v-if="featuredProducts.length === 0" class="text-center py-8">
              <SparklesIcon class="mx-auto h-12 w-12 text-gray-400" />
              <h3 class="mt-2 text-sm font-medium text-gray-900">No products available</h3>
              <p class="mt-1 text-sm text-gray-500">Check back later for fresh rice products.</p>
            </div>
            
            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div
                v-for="product in featuredProducts.slice(0, 4)"
                :key="product.id"
                class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow cursor-pointer"
                @click="viewProduct(product.id)"
              >
                <div class="aspect-w-16 aspect-h-9 bg-gray-200">
                  <img
                    v-if="product.images && product.images[0]"
                    :src="product.images[0]"
                    :alt="product.name"
                    class="w-full h-32 object-cover"
                  />
                  <div v-else class="w-full h-32 bg-green-100 flex items-center justify-center">
                    <SparklesIcon class="h-8 w-8 text-green-600" />
                  </div>
                </div>
                
                <div class="p-4">
                  <h3 class="text-sm font-medium text-gray-900 mb-1">{{ product.name }}</h3>
                  <p class="text-xs text-gray-500 mb-2">{{ product.rice_variety?.name }}</p>
                  
                  <div class="flex items-center justify-between">
                    <div>
                      <p class="text-lg font-bold text-green-600">${{ product.price_per_unit }}</p>
                      <p class="text-xs text-gray-500">per {{ product.unit }}</p>
                    </div>
                    
                    <div class="text-right">
                      <div class="flex items-center">
                        <StarIcon class="h-4 w-4 text-yellow-400 fill-current" />
                        <span class="text-sm text-gray-600 ml-1">{{ product.average_rating || 0 }}</span>
                      </div>
                      <p class="text-xs text-gray-500">{{ product.quantity_available }} {{ product.unit }} left</p>
                    </div>
                  </div>
                  
                  <div class="mt-3 flex items-center justify-between">
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                          :class="getGradeClass(product.quality_grade)">
                      {{ formatGrade(product.quality_grade) }}
                    </span>
                    
                    <span v-if="product.is_organic" class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                      Organic
                    </span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
            <h2 class="text-lg font-semibold text-gray-900">Recent Orders</h2>
            <router-link
              to="/marketplace/orders"
              class="text-sm text-green-600 hover:text-green-700 font-medium"
            >
              View all orders
            </router-link>
          </div>
          <div class="p-6">
            <div v-if="recentOrders.length === 0" class="text-center py-8">
              <ShoppingCartIcon class="mx-auto h-12 w-12 text-gray-400" />
              <h3 class="mt-2 text-sm font-medium text-gray-900">No orders yet</h3>
              <p class="mt-1 text-sm text-gray-500">Start shopping for fresh rice products!</p>
              <div class="mt-6">
                <router-link
                  to="/marketplace"
                  class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700"
                >
                  Browse Products
                </router-link>
              </div>
            </div>
            
            <div v-else class="space-y-4">
              <div
                v-for="order in recentOrders.slice(0, 3)"
                :key="order.id"
                class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition-shadow cursor-pointer"
                @click="viewOrder(order.id)"
              >
                <div class="flex items-center justify-between">
                  <div>
                    <h3 class="text-sm font-medium text-gray-900">
                      Order #{{ order.id }}
                    </h3>
                    <p class="text-sm text-gray-500">
                      {{ order.rice_product?.name }} - {{ order.quantity }} {{ order.rice_product?.unit }}
                    </p>
                    <p class="text-xs text-gray-500">
                      Ordered {{ formatDate(order.order_date) }}
                    </p>
                  </div>
                  <div class="text-right">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="getOrderStatusClass(order.status)"
                    >
                      {{ formatOrderStatus(order.status) }}
                    </span>
                    <p class="text-sm font-medium text-gray-900 mt-1">
                      ${{ order.total_amount }}
                    </p>
                  </div>
                </div>
                
                <!-- Progress Bar -->
                <div class="mt-3">
                  <div class="bg-gray-200 rounded-full h-2">
                    <div
                      class="bg-green-600 h-2 rounded-full transition-all duration-300"
                      :style="{ width: `${order.progress_percentage || 0}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column -->
      <div class="space-y-8">
        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Quick Actions</h2>
          </div>
          <div class="p-6 space-y-3">
            <router-link
              to="/marketplace"
              class="flex items-center p-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <MagnifyingGlassIcon class="w-5 h-5 mr-3 text-green-600" />
              Browse Rice Products
            </router-link>
            
            <router-link
              to="/marketplace/cart"
              class="flex items-center p-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <ShoppingBagIcon class="w-5 h-5 mr-3 text-blue-600" />
              View Shopping Cart
            </router-link>
            
            <router-link
              to="/marketplace/favorites"
              class="flex items-center p-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <HeartIcon class="w-5 h-5 mr-3 text-red-600" />
              My Favorites
            </router-link>
            
            <router-link
              to="/profile"
              class="flex items-center p-3 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
            >
              <UserCircleIcon class="w-5 h-5 mr-3 text-purple-600" />
              Account Settings
            </router-link>
          </div>
        </div>

        <!-- Rice Varieties -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Popular Rice Varieties</h2>
          </div>
          <div class="p-6">
            <div class="space-y-3">
              <div
                v-for="variety in popularVarieties"
                :key="variety.id"
                class="flex items-center justify-between p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
                @click="searchByVariety(variety.id)"
              >
                <div>
                  <p class="text-sm font-medium text-gray-900">{{ variety.name }}</p>
                  <p class="text-xs text-gray-500">{{ variety.description }}</p>
                </div>
                <div class="text-right">
                  <p class="text-sm font-medium text-green-600">{{ variety.product_count }} products</p>
                  <p class="text-xs text-gray-500">{{ variety.maturity_days }} days</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Seasonal Recommendations -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Seasonal Recommendations</h2>
          </div>
          <div class="p-6">
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
              <div class="flex items-center">
                <SparklesIcon class="h-5 w-5 text-green-600 mr-2" />
                <h3 class="text-sm font-medium text-green-800">Fresh Harvest Season</h3>
              </div>
              <p class="text-sm text-green-700 mt-2">
                New rice harvests are now available! Get the freshest rice directly from local farmers.
              </p>
              <div class="mt-3">
                <router-link
                  to="/marketplace?filter=fresh"
                  class="text-sm font-medium text-green-600 hover:text-green-700"
                >
                  Shop Fresh Rice →
                </router-link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import {
  ShoppingCartIcon,
  HeartIcon,
  CurrencyDollarIcon,
  SparklesIcon,
  StarIcon,
  MagnifyingGlassIcon,
  ShoppingBagIcon,
  UserCircleIcon,
} from '@heroicons/vue/24/outline';

const router = useRouter();
const authStore = useAuthStore();

// Reactive data
const stats = ref({});
const featuredProducts = ref([]);
const recentOrders = ref([]);
const popularVarieties = ref([]);
const loading = ref(true);

// Methods
const formatDate = (date) => {
  if (!date) return '';
  return new Date(date).toLocaleDateString();
};

const getGradeClass = (grade) => {
  const gradeClasses = {
    'premium': 'bg-purple-100 text-purple-800',
    'grade_a': 'bg-green-100 text-green-800',
    'grade_b': 'bg-blue-100 text-blue-800',
    'commercial': 'bg-gray-100 text-gray-800',
  };
  return gradeClasses[grade] || 'bg-gray-100 text-gray-800';
};

const formatGrade = (grade) => {
  const gradeLabels = {
    'premium': 'Premium',
    'grade_a': 'Grade A',
    'grade_b': 'Grade B',
    'commercial': 'Commercial',
  };
  return gradeLabels[grade] || grade;
};

const getOrderStatusClass = (status) => {
  const statusClasses = {
    'pending': 'bg-yellow-100 text-yellow-800',
    'confirmed': 'bg-blue-100 text-blue-800',
    'shipped': 'bg-purple-100 text-purple-800',
    'delivered': 'bg-green-100 text-green-800',
    'cancelled': 'bg-red-100 text-red-800',
  };
  return statusClasses[status] || 'bg-gray-100 text-gray-800';
};

const formatOrderStatus = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1);
};

const viewProduct = (productId) => {
  router.push(`/marketplace/products/${productId}`);
};

const viewOrder = (orderId) => {
  router.push(`/marketplace/orders/${orderId}`);
};

const searchByVariety = (varietyId) => {
  router.push(`/marketplace?variety=${varietyId}`);
};

const loadDashboardData = async () => {
  try {
    loading.value = true;
    
    // Mock data for user dashboard
    stats.value = {
      total_orders: 12,
      pending_orders: 2,
      favorites: 8,
      total_spent: 1250,
      available_products: 156,
    };
    
    featuredProducts.value = [
      {
        id: 1,
        name: 'Premium Jasmine Rice',
        rice_variety: { name: 'Jasmine' },
        price_per_unit: 25.99,
        unit: 'kg',
        quantity_available: 150,
        quality_grade: 'premium',
        is_organic: true,
        average_rating: 4.8,
        images: null,
      },
      {
        id: 2,
        name: 'Organic Brown Rice',
        rice_variety: { name: 'Brown Rice' },
        price_per_unit: 18.50,
        unit: 'kg',
        quantity_available: 200,
        quality_grade: 'grade_a',
        is_organic: true,
        average_rating: 4.6,
        images: null,
      },
    ];
    
    recentOrders.value = [
      {
        id: 1001,
        rice_product: { name: 'Premium Basmati Rice', unit: 'kg' },
        quantity: 5,
        total_amount: 89.95,
        status: 'shipped',
        order_date: '2024-09-28',
        progress_percentage: 75,
      },
      {
        id: 1002,
        rice_product: { name: 'Organic Jasmine Rice', unit: 'kg' },
        quantity: 10,
        total_amount: 259.90,
        status: 'confirmed',
        order_date: '2024-10-01',
        progress_percentage: 40,
      },
    ];
    
    popularVarieties.value = [
      {
        id: 1,
        name: 'Jasmine Rice',
        description: 'Aromatic long-grain rice',
        product_count: 23,
        maturity_days: 120,
      },
      {
        id: 2,
        name: 'Basmati Rice',
        description: 'Premium aromatic rice',
        product_count: 18,
        maturity_days: 130,
      },
      {
        id: 3,
        name: 'Brown Rice',
        description: 'Nutritious whole grain',
        product_count: 15,
        maturity_days: 110,
      },
    ];
    
  } catch (error) {
    console.error('Error loading user dashboard data:', error);
  } finally {
    loading.value = false;
  }
};

onMounted(() => {
  loadDashboardData();
});
</script>