<template>
  <div>
    <!-- Route to appropriate dashboard based on user role -->
    <FarmerDashboard v-if="authStore.isFarmer" />
    <BuyerDashboard v-else-if="authStore.isBuyer" />
    <div v-else class="flex items-center justify-center h-64">
      <div class="text-center">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
        <p class="mt-4 text-gray-600">Loading dashboard...</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useAuthStore } from '@/stores/auth';
import FarmerDashboard from '@/Pages/Farmer/Dashboard.vue';
import BuyerDashboard from '@/Pages/Buyer/Dashboard.vue';

const authStore = useAuthStore();
</script>