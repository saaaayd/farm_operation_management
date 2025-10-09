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
      <div v-if="loading || authStore.loading" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white p-6 rounded-lg shadow-xl">
          <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto"></div>
          <p class="mt-2 text-gray-600">Loading...</p>
        </div>
      </div>

      <!-- Global Error Toast -->
      <div v-if="authStore.error" class="fixed top-4 right-4 z-50">
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded shadow-lg max-w-sm">
          <div class="flex items-center justify-between">
            <span class="text-sm">{{ authStore.error }}</span>
            <button @click="authStore.error = null" class="ml-2 text-red-500 hover:text-red-700">
              <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        </div>
      </div>
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
</style>