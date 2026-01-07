<template>
  <div class="flex h-screen bg-gray-100 overflow-hidden">
    <!-- Sidebar -->
    <div 
      class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-2xl transform transition-transform duration-300 ease-in-out lg:translate-x-0 flex flex-col h-full border-r border-gray-100"
      :class="{ '-translate-x-full': !sidebarOpen, 'translate-x-0': sidebarOpen }"
    >
      <!-- Sidebar Header -->
      <div class="flex items-center justify-between h-16 px-6 bg-gradient-to-r from-green-600 to-emerald-600 shadow-lg flex-shrink-0">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-10 h-10 bg-white rounded-xl flex items-center justify-center shadow-md transform transition-transform hover:scale-110">
              <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
              </svg>
            </div>
          </div>
          <div class="ml-3">
            <h1 class="text-white text-lg font-bold tracking-tight">RiceFARM</h1>
            <p class="text-green-50 text-xs font-medium">Management System</p>
          </div>
        </div>
        <button
          @click="toggleSidebar"
          class="lg:hidden text-white hover:text-green-100 focus:outline-none focus:ring-2 focus:ring-white focus:ring-offset-2 focus:ring-offset-green-600 rounded-lg p-1 transition-all"
        >
          <XMarkIcon class="w-6 h-6" />
        </button>
      </div>

      <!-- User Info -->
      <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white flex-shrink-0">
        <div class="flex items-center">
          <div class="flex-shrink-0">
            <div class="w-12 h-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-md ring-2 ring-white">
              <UserIcon class="w-7 h-7 text-white" />
            </div>
          </div>
          <div class="ml-3 flex-1 min-w-0">
            <p class="text-sm font-semibold text-gray-900 truncate">{{ authStore.user?.name }}</p>
            <p class="text-xs text-gray-600 capitalize font-medium">{{ authStore.user?.role }}</p>
          </div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <div class="flex flex-col flex-1 overflow-y-auto">
        <nav class="flex-1 px-3 py-4">
          <!-- ... navigation items ... -->
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

          <!-- Farmer Section -->
          <template v-if="authStore.user?.role === 'farmer'">
            <!-- Core Farm Management -->
            <router-link
              to="/fields"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/fields') }"
            >
              <GlobeAltIcon class="nav-icon" />
              Rice Fields
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
              to="/seed-plantings"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/seed-plantings') }"
            >
              <BeakerIcon class="nav-icon" />
              Nursery
            </router-link>

            <router-link
              to="/harvests"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/harvests') }"
            >
              <ArchiveBoxIcon class="nav-icon" />
              Harvests
            </router-link>

            <router-link
              to="/tasks"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/tasks') }"
            >
              <ClipboardDocumentListIcon class="nav-icon" />
              Tasks
            </router-link>

            <router-link
              to="/laborers"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/laborers') }"
            >
              <UserGroupIcon class="nav-icon" />
              Laborers
            </router-link>

            <router-link
              to="/inventory"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/inventory') }"
            >
              <CubeIcon class="nav-icon" />
              Inventory
            </router-link>

            <div class="nav-section-title">Analytics</div>
            
            <router-link
              to="/weather"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/weather') }"
            >
              <CloudIcon class="nav-icon" />
              Weather
            </router-link>

            <router-link
              to="/reports"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/reports') }"
            >
              <DocumentChartBarIcon class="nav-icon" />
              Reports
            </router-link>

            <div class="nav-section-title">Business</div>
            
            <router-link
              to="/marketplace/my-products"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/marketplace/my-products') }"
            >
              <ShoppingBagIcon class="nav-icon" />
              My Products
            </router-link>

            <router-link
              to="/marketplace/orders"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/marketplace/orders') }"
            >
              <DocumentTextIcon class="nav-icon" />
              Orders
            </router-link>

            <router-link
              to="/financial/expenses"
              class="nav-item"
              :class="{ 'nav-item-active': $route.path.startsWith('/financial/expenses') }"
            >
              <CurrencyDollarIcon class="nav-icon" />
              Expenses
            </router-link>
          </template>

          <!-- Marketplace Buyer Section -->
          <template v-if="authStore.user?.role === 'buyer'">
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
        </nav>

        <!-- Bottom Section -->
        <div class="px-3 py-4 border-t border-gray-200 bg-white flex-shrink-0">
          <router-link
            to="/profile"
            class="nav-item"
            :class="{ 'nav-item-active': $route.path === '/profile' }"
          >
            <UserCircleIcon class="nav-icon" />
            Profile
          </router-link>

          <button
            @click="logout"
            :disabled="isNavigating"
            class="nav-item text-red-600 hover:bg-red-50 hover:text-red-700 w-full text-left disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200"
          >
            <ArrowRightOnRectangleIcon v-if="!isNavigating" class="nav-icon" />
            <div v-else class="nav-icon animate-spin rounded-full h-5 w-5 border-2 border-red-300 border-t-red-600"></div>
            <span class="font-medium">{{ isNavigating ? 'Logging out...' : 'Logout' }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Mobile overlay -->
    <div
      v-if="sidebarOpen"
      class="fixed inset-0 z-40 bg-gray-600 bg-opacity-75 lg:hidden"
      @click="closeSidebar"
    ></div>

    <!-- Main content container -->
    <div class="flex-1 flex flex-col min-w-0 lg:ml-64 h-full">
      <!-- Mobile header -->
      <div class="lg:hidden bg-white shadow-md border-b border-gray-200 sticky top-0 z-40 flex-shrink-0">
        <div class="flex items-center justify-between h-16 px-4">
          <button
            @click="toggleSidebar"
            class="text-gray-600 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-green-500 rounded-lg p-2 transition-all"
          >
            <Bars3Icon class="w-6 h-6" />
          </button>
          <h1 class="text-lg font-bold text-gray-900 gradient-text">RiceFARM</h1>
          <div class="w-6"></div> <!-- Spacer -->
        </div>
      </div>

      <!-- Page content -->
      <main class="flex-1 overflow-y-auto w-full">
        <slot />
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';
import {
  HomeIcon,
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
  BeakerIcon,
} from '@heroicons/vue/24/outline';

const router = useRouter();
const authStore = useAuthStore();

const sidebarOpen = ref(false);
const isNavigating = ref(false);

const toggleSidebar = () => {
  sidebarOpen.value = !sidebarOpen.value;
};

const closeSidebar = () => {
  sidebarOpen.value = false;
};

const safeNavigate = async (path) => {
  if (isNavigating.value) return; // Prevent double-clicks
  
  isNavigating.value = true;
  try {
    await router.push(path);
    closeSidebar(); // Close sidebar on mobile after navigation
  } catch (error) {
    console.error('Navigation error:', error);
    // Fallback to window location if router fails
    window.location.href = path;
  } finally {
    setTimeout(() => {
      isNavigating.value = false;
    }, 500);
  }
};

const logout = async () => {
  if (isNavigating.value) return; // Prevent double-clicks
  
  isNavigating.value = true;
  try {
    await authStore.logout();
    router.push('/login');
  } catch (error) {
    console.error('Logout error:', error);
    // Force logout even if API call fails
    authStore.user = null;
    authStore.token = null;
    localStorage.removeItem('token');
    delete axios.defaults.headers.common['Authorization'];
    window.location.href = '/login';
  } finally {
    isNavigating.value = false;
  }
};
</script>

<style scoped>
.nav-item {
  display: flex;
  align-items: center;
  padding-left: 0.75rem;
  padding-right: 0.75rem;
  padding-top: 0.625rem;
  padding-bottom: 0.625rem;
  font-size: 0.875rem;
  font-weight: 500;
  color: rgb(55 65 81);
  border-radius: 0.75rem;
  transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
  position: relative;
  margin-bottom: 0.25rem;
}

.nav-item:hover {
  background: linear-gradient(to right, rgb(243 244 246), rgb(249 250 251));
  color: rgb(17 24 39);
  transform: translateX(4px);
}

.nav-item-active {
  background: linear-gradient(to right, rgb(220 252 231), rgb(209 250 229));
  color: rgb(21 128 61);
  font-weight: 600;
  box-shadow: 0 2px 8px rgba(34, 197, 94, 0.15);
}

.nav-item-active::before {
  content: '';
  position: absolute;
  left: 0;
  top: 50%;
  transform: translateY(-50%);
  width: 3px;
  height: 60%;
  background: linear-gradient(to bottom, rgb(34 197 94), rgb(16 185 129));
  border-radius: 0 2px 2px 0;
}

.nav-icon {
  width: 1.25rem;
  height: 1.25rem;
  margin-right: 0.75rem;
  flex-shrink: 0;
}

.nav-section-title {
  padding-left: 0.75rem;
  padding-right: 0.75rem;
  padding-top: 0.75rem;
  padding-bottom: 0.5rem;
  font-size: 0.6875rem;
  font-weight: 700;
  color: rgb(107 114 128);
  text-transform: uppercase;
  letter-spacing: 0.1em;
  margin-top: 1.5rem;
  position: relative;
}

.nav-section-title:not(:first-child)::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0.75rem;
  right: 0.75rem;
  height: 1px;
  background: linear-gradient(to right, transparent, rgb(229 231 235), transparent);
}

.nav-section-title:first-child {
  margin-top: 0;
}

/* Ensure sidebar is always fixed and doesn't scroll with content */
.sidebar-container {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  width: 16rem; /* w-64 */
  z-index: 50;
}

@media (max-width: 1023px) {
  .sidebar-container {
    transform: translateX(-100%);
  }
  
  .sidebar-container.open {
    transform: translateX(0);
  }
}
</style>