<template>
  <div>
    <!-- Farmer Dashboard -->
    <RiceFarmingDashboard v-if="authStore.user?.role === 'farmer'" />
    
    <!-- Buyer Dashboard -->
    <UserDashboard v-else-if="authStore.user?.role === 'buyer'" />
    
    <!-- Fallback for unknown roles -->
    <div v-else class="p-6 w-full mx-auto">
      <div class="text-center">
        <h1 class="text-2xl font-bold text-gray-900 mb-4">Welcome to ANIBUKID</h1>
        <p class="text-gray-600">Your account role is not recognized. Please contact support.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import RiceFarmingDashboard from './RiceFarmingDashboard.vue';
import UserDashboard from './UserDashboard.vue';

const authStore = useAuthStore();
</script>