<template>
  <div id="app" class="min-h-screen bg-gray-50">
    <!-- Authentication Pages (Login/Register) -->
    <template v-if="!authStore.user">
      <router-view />
    </template>

    <!-- Main Application with Sidebar -->
    <template v-else>
      <Sidebar>
        <router-view />
      </Sidebar>
    </template>

    <!-- Global Loading -->
    <div v-if="loading" class="fixed inset-0 bg-gray-600 bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white p-6 rounded-lg shadow-xl">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto"></div>
        <p class="mt-2 text-gray-600">Loading...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';
import Sidebar from '@/components/Navigation/Sidebar.vue';

const router = useRouter();
const authStore = useAuthStore();

const loading = ref(false);

onMounted(() => {
  // Check if user is authenticated on app load
  if (authStore.token) {
    authStore.fetchUser();
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