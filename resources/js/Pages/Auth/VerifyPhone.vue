<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Verify your phone number
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          We sent a verification code to your phone number.
        </p>
      </div>
      
      <form class="mt-8 space-y-6" @submit.prevent="handleVerify">
        <div class="rounded-md shadow-sm -space-y-px">
          <div>
            <label for="code" class="sr-only">Verification Code</label>
            <input
              id="code"
              v-model="code"
              name="code"
              type="text"
              required
              class="appearance-none rounded-none relative block w-full px-3 py-2 border border-gray-300 placeholder-gray-500 text-gray-900 rounded-md focus:outline-none focus:ring-green-500 focus:border-green-500 focus:z-10 sm:text-sm"
              placeholder="Enter 6-digit code"
              maxlength="6"
            />
          </div>
        </div>

        <div v-if="error" class="text-red-600 text-sm text-center">
          {{ error }}
        </div>

        <div>
          <button
            type="submit"
            :disabled="loading"
            class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
          >
            <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-3">
              <!-- Spinner -->
              <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
            </span>
            Verify
          </button>
        </div>

        <div class="text-center">
          <button
            type="button"
            @click="handleResend"
            class="text-sm font-medium text-green-600 hover:text-green-500"
          >
            Resend Code
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import axios from 'axios';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const route = useRoute();
const authStore = useAuthStore();

const code = ref('');
const loading = ref(false);
const error = ref('');
const phone = route.query.phone; // Pass phone via query param

const handleVerify = async () => {
  loading.value = true;
  error.value = '';
  
  try {
    await authStore.verifyPhone(phone, code.value);
    router.push('/');
  } catch (err) {
    error.value = authStore.error || 'Verification failed';
  } finally {
    loading.value = false;
  }
};

const handleResend = async () => {
  try {
    await axios.post('/api/resend-verification', { phone });
    alert('Verification code resent!');
  } catch (err) {
    alert('Failed to resend code.');
  }
};
</script>
