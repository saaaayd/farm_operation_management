<template>
  <ErrorBoundary>
    <div id="app" class="min-h-screen bg-gray-50">
      <!-- Authentication Pages (Login/Register) -->
      <template v-if="!authStore.user">
        <router-view />
      </template>

      <!-- Main Application with Sidebar -->
      <template v-else>
        <Sidebar>
          <ErrorBoundary>
            <router-view />
          </ErrorBoundary>
        </Sidebar>
      </template>

      <!-- Global Loading -->
      <transition name="fade">
        <div v-if="loading || authStore.loading" class="fixed inset-0 bg-gray-900/60 backdrop-blur-sm flex items-center justify-center z-50">
          <div class="bg-white p-8 rounded-2xl shadow-2xl max-w-sm w-full mx-4 transform transition-all">
            <div class="flex flex-col items-center">
              <div class="relative">
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-gray-200"></div>
                <div class="animate-spin rounded-full h-12 w-12 border-4 border-green-600 border-t-transparent absolute top-0 left-0"></div>
              </div>
              <p class="mt-4 text-gray-700 font-medium">Loading...</p>
              <p class="mt-1 text-sm text-gray-500">Please wait</p>
            </div>
          </div>
        </div>
      </transition>

      <!-- Global Error Toast -->
      <transition name="slide-fade">
        <div v-if="authStore.error" class="fixed top-4 right-4 z-50 max-w-sm w-full">
          <div class="bg-gradient-to-r from-red-50 to-red-100 border-l-4 border-red-500 text-red-800 px-5 py-4 rounded-lg shadow-2xl flex items-start justify-between animate-slide-in">
            <div class="flex items-start">
              <svg class="h-5 w-5 text-red-500 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
              <div class="flex-1">
                <p class="font-semibold text-sm">Error</p>
                <p class="text-sm mt-1">{{ authStore.error }}</p>
              </div>
            </div>
            <button 
              @click="authStore.error = null" 
              class="ml-4 text-red-500 hover:text-red-700 transition-colors focus:outline-none focus:ring-2 focus:ring-red-500 rounded"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </transition>
    </div>
  </ErrorBoundary>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import Sidebar from '@/components/Navigation/Sidebar.vue';
import ErrorBoundary from '@/Components/UI/ErrorBoundary.vue';

const router = useRouter();
const authStore = useAuthStore();

const loading = ref(false);

onMounted(async () => {
  // Initialize app with proper error handling
  loading.value = true;
  
  try {
    // Check if user is authenticated on app load
    if (authStore.token) {
      console.log('Token found, fetching user data...');
      await authStore.fetchUser();
    } else {
      console.log('No token found, user needs to login');
    }
  } catch (error) {
    console.error('App initialization error:', error);
    // Don't block the app from loading if user fetch fails
  } finally {
    loading.value = false;
  }
});
</script>

<style>
/* Global styles */
body {
  font-family: 'Inter', sans-serif;
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-fade-enter-active {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.slide-fade-leave-active {
  transition: all 0.25s cubic-bezier(0.4, 0, 1, 1);
}

.slide-fade-enter-from {
  transform: translateX(100%);
  opacity: 0;
}

.slide-fade-leave-to {
  transform: translateX(100%);
  opacity: 0;
}

@keyframes slide-in {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.animate-slide-in {
  animation: slide-in 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
</style>