<template>
  <div class="flex h-screen bg-gray-100">
    <!-- Sidebar -->
    <div 
      class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0"
      :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }"
    >
      <!-- Sidebar Header -->
      <div class="flex items-center justify-between h-16 px-6 bg-green-600">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-8 h-8 bg-white rounded-full flex items-center justify-center">
              <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
              </svg>
            </div>
          </div>
          <div class="ml-3">
            <h1 class="text-white text-lg font-bold">RiceFARM</h1>
            <p class="text-green-100 text-xs">Management System</p>
          </div>
        </div>
        <button
          @click="toggleSidebar"
          class="lg:hidden text-white hover:text-green-200 focus:outline-none"
        >
          <XMarkIcon class="w-6 h-6" />
        </button>
      </div>

      <!-- User Info -->
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
              <UserIcon class="w-6 h-6 text-green-600" />
            </div>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-gray-900">{{ authStore.user?.name }}</p>
            <p class="text-xs text-gray-500 capitalize">{{ authStore.user?.role }}</p>
          </div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="mt-6 px-3 flex-1 overflow-y-auto">
        <div class="space-y-1">
          <!-- Dashboard -->
          <router-link
            to="/dashboard"
            class="nav-item"
            :class="{ 'nav-item-active': $route.path === '/dashboard' }"
          >
            <HomeIcon class="nav-icon" />
            Dashboard
          </router-link>

          <!-- Admin Section -->
          <template v-if="authStore.user?.role === 'admin'">
            <div class="nav-section-title">Administration</div>
            
            <router-link
              to="/admin/users"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/admin/users') }"
            >
              <UsersIcon class="nav-icon" />
              User Management
            </router-link>

            <router-link
              to="/admin/system"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/admin/system') }"
            >
              <CogIcon class="nav-icon" />
              System Settings
            </router-link>

            <router-link
              to="/admin/reports"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/admin/reports') }"
            >
              <ChartBarIcon class="nav-icon" />
              System Reports
            </router-link>
          </template>

          <!-- Farmer Section -->
          <template v-if="authStore.user?.role === 'farmer'">
            <div class="nav-section-title">Farm Management</div>
            
            <router-link
              to="/farm/overview"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/farm/overview') }"
            >
              <MapIcon class="nav-icon" />
              Farm Overview
            </router-link>

            <router-link
              to="/fields"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/fields') }"
            >
              <GlobeAltIcon class="nav-icon" />
              Rice Fields
            </router-link>

            <div class="nav-section-title">Rice Farming</div>
            
            <router-link
              to="/rice-farming/lifecycle"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/rice-farming/lifecycle') }"
            >
              <ArrowPathIcon class="nav-icon" />
              Lifecycle Management
            </router-link>

            <router-link
              to="/plantings"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/plantings') }"
            >
              <SparklesIcon class="nav-icon" />
              Plantings
            </router-link>

            <router-link
              to="/harvests"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/harvests') }"
            >
              <ArchiveBoxIcon class="nav-icon" />
              Harvests
            </router-link>

            <div class="nav-section-title">Operations</div>
            
            <router-link
              to="/tasks"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/tasks') }"
            >
              <ClipboardDocumentListIcon class="nav-icon" />
              Tasks & Activities
            </router-link>

            <router-link
              to="/inventory"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/inventory') }"
            >
              <CubeIcon class="nav-icon" />
              Inventory
            </router-link>

            <router-link
              to="/labor"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/labor') }"
            >
              <UserGroupIcon class="nav-icon" />
              Labor Management
            </router-link>

            <div class="nav-section-title">Analytics</div>
            
            <router-link
              to="/weather"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/weather') }"
            >
              <CloudIcon class="nav-icon" />
              Weather Analytics
            </router-link>

            <router-link
              to="/reports"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/reports') }"
            >
              <DocumentChartBarIcon class="nav-icon" />
              Farm Reports
            </router-link>

            <div class="nav-section-title">Marketplace</div>
            
            <router-link
              to="/marketplace/my-products"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/marketplace/my-products') }"
            >
              <ShoppingBagIcon class="nav-icon" />
              My Rice Products
            </router-link>

            <router-link
              to="/marketplace/orders"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/marketplace/orders') }"
            >
              <DocumentTextIcon class="nav-icon" />
              Sales Orders
            </router-link>

            <router-link
              to="/marketplace/browse"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path === '/marketplace/browse' }"
            >
              <MagnifyingGlassIcon class="nav-icon" />
              Browse Marketplace
            </router-link>

            <div class="nav-section-title">Financial</div>
            
            <router-link
              to="/financial/expenses"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/financial/expenses') }"
            >
              <CurrencyDollarIcon class="nav-icon" />
              Expenses
            </router-link>

            <router-link
              to="/financial/income"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/financial/income') }"
            >
              <BanknotesIcon class="nav-icon" />
              Income & Sales
            </router-link>
          </template>

          <!-- User (Marketplace Buyer) Section -->
          <template v-if="authStore.user?.role === 'user'">
            <div class="nav-section-title">Marketplace</div>
            
            <router-link
              to="/marketplace"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path === '/marketplace' }"
            >
              <ShoppingCartIcon class="nav-icon" />
              Browse Rice Products
            </router-link>

            <router-link
              to="/marketplace/cart"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/marketplace/cart') }"
            >
              <ShoppingBagIcon class="nav-icon" />
              Shopping Cart
            </router-link>

            <router-link
              to="/marketplace/orders"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/marketplace/orders') }"
            >
              <DocumentTextIcon class="nav-icon" />
              My Orders
            </router-link>

            <router-link
              to="/marketplace/favorites"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/marketplace/favorites') }"
            >
              <HeartIcon class="nav-icon" />
              Favorites
            </router-link>
          </template>
        </div>

        <!-- Bottom Section -->
        <div class="mt-auto pt-6 pb-4 border-t border-gray-200">
          <router-link
            to="/profile"
            class="nav-item"
            :class="{ 'nav-item-active': $route.path === '/profile' }"
          >
            <UserCircleIcon class="nav-icon" />
            Profile Settings
          </router-link>

          <button
            @click="logout"
            class="nav-item text-red-600 hover:bg-red-50 hover:text-red-700 w-full text-left"
          >
            <ArrowRightOnRectangleIcon class="nav-icon" />
            Logout
          </button>
        </div>
      </nav>
    </div>

    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
      @click="closeSidebar"
    ></div>

    <!-- Main content -->
    <div class="flex-1 lg:ml-0">
      <!-- Mobile header -->
      <div class="lg:hidden bg-white shadow-sm border-b border-gray-200">
        <div class="flex items-center justify-between h-16 px-4">
          <button
            @click="toggleSidebar"
            class="text-gray-500 hover:text-gray-600 focus:outline-none"
          >
            <Bars3Icon class="w-6 h-6" />
          </button>
          <h1 class="text-lg font-semibold text-gray-900">RiceFARM</h1>
          <div class="w-6"></div> <!-- Spacer -->
        </div>
      </div>

      <!-- Page content -->
      <main class="flex-1 overflow-y-auto">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import {
  HomeIcon,
  UsersIcon,
  CogIcon,
  ChartBarIcon,
  MapIcon,
  GlobeAltIcon,
  ArrowPathIcon,
  SparklesIcon,
  ArchiveBoxIcon,
  ClipboardDocumentListIcon,
  CubeIcon,
  UserGroupIcon,
  CloudIcon,
  DocumentChartBarIcon,
  ShoppingBagIcon,
  DocumentTextIcon,
  MagnifyingGlassIcon,
  CurrencyDollarIcon,
  BanknotesIcon,
  ShoppingCartIcon,
  HeartIcon,
  UserCircleIcon,
  ArrowRightOnRectangleIcon,
  UserIcon,
  XMarkIcon,
  Bars3Icon,
} from '@heroicons/vue/24/outline';

const router = useRouter();
const authStore = useAuthStore();

const sidebarOpen = ref(false);

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const closeSidebar = () => {
  sidebarOpen.value = false;
};

const logout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>

<style scoped>
.nav-item {
  @apply flex items-center px-3 py-2 text-sm font-medium text-gray-700 rounded-lg hover:bg-gray-100 hover:text-gray-900 transition-colors duration-150;
}

.nav-item-active {
  @apply bg-green-100 text-green-700;
}

.nav-icon {
  @apply w-5 h-5 mr-3 flex-shrink-0;
}

.nav-section-title {
  @apply px-3 py-2 text-xs font-semibold text-gray-500 uppercase tracking-wider mt-6 first:mt-0;
}
</style>