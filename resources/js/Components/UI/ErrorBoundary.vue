<template>
  <div v-if="hasError" class="min-h-screen flex items-center justify-center bg-gray-50">
    <div class="max-w-md w-full bg-white rounded-lg shadow-lg p-6">
      <div class="flex items-center mb-4">
        <div class="flex-shrink-0">
          <svg class="h-8 w-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-lg font-medium text-gray-900">Something went wrong</h3>
        </div>
      </div>
      
      <div class="mb-4">
        <p class="text-sm text-gray-600">
          We're sorry, but something unexpected happened. Please try refreshing the page or contact support if the problem persists.
        </p>
        
        <div v-if="showDetails" class="mt-3 p-3 bg-gray-100 rounded text-xs text-gray-700 font-mono">
          {{ errorMessage }}
        </div>
      </div>
      
      <div class="flex space-x-3">
        <button
          @click="refreshPage"
          class="flex-1 bg-green-600 text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
        >
          Refresh Page
        </button>
        
        <button
          @click="showDetails = !showDetails"
          class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-green-500"
        >
          {{ showDetails ? 'Hide' : 'Show' }} Details
        </button>
      </div>
      
      <div class="mt-4 text-center">
        <button
          @click="goHome"
          class="text-sm text-green-600 hover:text-green-700"
        >
          Go to Dashboard
        </button>
      </div>
    </div>
  </div>
  
  <slot v-else />
</template>

<script setup>
import { ref, onErrorCaptured } from 'vue';
import { useRouter } from 'vue-router';

const router = useRouter();

const hasError = ref(false);
const errorMessage = ref('');
const showDetails = ref(false);

onErrorCaptured((error, instance, info) => {
  console.error('Vue Error Boundary caught error:', error);
  console.error('Component instance:', instance);
  console.error('Error info:', info);
  
  hasError.value = true;
  errorMessage.value = error.message || 'Unknown error occurred';
  
  // Log error to external service if available
  if (window.gtag) {
    window.gtag('event', 'exception', {
      description: error.message,
      fatal: false
    });
  }
  
  return false; // Prevent error from propagating
});

const refreshPage = () => {
  window.location.reload();
};

const goHome = () => {
  hasError.value = false;
  router.push('/dashboard').catch(() => {
    window.location.href = '/dashboard';
  });
};
</script>