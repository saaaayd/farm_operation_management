<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div>
        <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
          Verify your {{ isEmailVerification ? 'email' : 'phone number' }}
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          We sent a verification code to your {{ isEmailVerification ? 'email address' : 'phone number' }}.
        </p>
      </div>

      <!-- Debug info for development -->
      <div v-if="debugCode" class="rounded-md bg-yellow-50 border border-yellow-200 p-4">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
              <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-yellow-800">Development Mode</h3>
            <p class="mt-1 text-sm text-yellow-700">
              Your verification code is: <strong class="font-mono text-lg">{{ debugCode }}</strong>
            </p>
            <p class="mt-1 text-xs text-yellow-600">
              ({{ isEmailVerification ? 'Check your email inbox' : 'SMS pending Semaphore approval' }})
            </p>
          </div>
        </div>
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
const email = route.query.email; // Pass email via query param
const method = route.query.method || 'sms'; // Verification method (sms or email)
const debugCode = route.query.debug_code; // Debug code for development testing
const isEmailVerification = method === 'email';

const handleVerify = async () => {
  loading.value = true;
  error.value = '';
  
  try {
    // Use email or phone based on verification method
    const identifier = isEmailVerification ? email : phone;
    await authStore.verifyPhone(identifier, code.value);
    router.push('/');
  } catch (err) {
    error.value = authStore.error || 'Verification failed';
  } finally {
    loading.value = false;
  }
};

const handleResend = async () => {
  try {
    const identifier = isEmailVerification ? email : phone;
    await axios.post('/api/resend-verification', { 
      phone: isEmailVerification ? null : identifier,
      email: isEmailVerification ? identifier : null,
      method: method
    });
    alert('Verification code resent!');
  } catch (err) {
    alert('Failed to resend code.');
  }
};
</script>
