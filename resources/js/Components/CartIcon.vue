<template>
  <router-link to="/buyer/cart" class="relative p-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100 rounded-full transition-colors">
    <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
    <span
      v-if="itemCount > 0"
      class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-green-600 rounded-full"
    >
      {{ itemCount > 9 ? '9+' : itemCount }}
    </span>
  </router-link>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import axios from 'axios'

const itemCount = ref(0)

const fetchCount = async () => {
  try {
    const response = await axios.get('/api/rice-marketplace/cart/count')
    itemCount.value = response.data.count || 0
  } catch (error) {
    // Silently fail for non-buyers
    itemCount.value = 0
  }
}

let pollInterval = null

onMounted(() => {
  fetchCount()
  pollInterval = setInterval(fetchCount, 60000) // Poll every minute
})

onUnmounted(() => {
  if (pollInterval) clearInterval(pollInterval)
})
</script>
