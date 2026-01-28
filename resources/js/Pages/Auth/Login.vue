<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-50 via-white to-emerald-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div class="text-center">
        <div class="mx-auto h-16 w-16 flex items-center justify-center rounded-2xl bg-gradient-to-br from-green-500 to-emerald-600 shadow-lg transform transition-transform hover:scale-110">
          <svg class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
          </svg>
        </div>
        <h2 class="mt-6 text-center text-3xl font-bold gradient-text">
          ANIBUKID
        </h2>
        <p class="mt-2 text-center text-sm text-gray-600 font-medium">
          Sign in to your account
        </p>
      </div>
      
      <div class="bg-white rounded-2xl shadow-2xl p-8 border border-gray-100">
        <form class="space-y-6" @submit.prevent="handleLogin">
          <div class="space-y-4">
          <div>
              <label for="login_id" class="block text-sm font-semibold text-gray-700 mb-2">
                Email or Phone Number
              </label>
            <input
              id="login_id"
              v-model="form.login_id"
              name="login_id"
              type="text"
              required
                class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:z-10 sm:text-sm transition-all duration-200 bg-gray-50 focus:bg-white"
                placeholder="Enter your email or phone number"
            />
          </div>
          <div>
              <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                Password
              </label>
            <input
              id="password"
              v-model="form.password"
              name="password"
              type="password"
              autocomplete="current-password"
              required
                class="appearance-none relative block w-full px-4 py-3 border border-gray-300 placeholder-gray-400 text-gray-900 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 focus:z-10 sm:text-sm transition-all duration-200 bg-gray-50 focus:bg-white"
                placeholder="Enter your password"
            />
          </div>
        </div>

          <div v-if="authStore.error" class="bg-red-50 border-l-4 border-red-500 text-red-800 px-4 py-3 rounded-lg">
            <div class="flex items-center">
              <svg class="h-5 w-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
              <p class="text-sm font-medium">{{ authStore.error }}</p>
            </div>
        </div>

        <div>
          <button
            type="submit"
            :disabled="authStore.loading"
              class="btn-primary w-full flex justify-center items-center py-3 px-4 disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
          >
              <span v-if="authStore.loading" class="absolute left-0 inset-y-0 flex items-center pl-4">
                <div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div>
            </span>
              <span class="font-semibold">{{ authStore.loading ? 'Signing in...' : 'Sign in' }}</span>
          </button>
        </div>

        <div class="text-center">
          <p class="text-sm text-gray-600">
            Don't have an account?
              <router-link to="/register" class="font-semibold text-green-600 hover:text-green-700 transition-colors">
              Sign up here
            </router-link>
          </p>
        </div>
      </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/stores/auth';

const router = useRouter();
const authStore = useAuthStore();

const form = ref({
  login_id: '',
  password: ''
});

const handleLogin = async () => {
  try {
    await authStore.login(form.value);
    // Let the router guard handle the redirect based on auth state
    router.push('/');
  } catch (error) {
    // Error is handled in the store
    console.error('Login error:', error);
  }
};
</script>