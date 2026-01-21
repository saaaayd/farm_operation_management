<template>
  <div class="min-h-screen bg-slate-50 flex flex-col font-sans">
    <!-- Navbar -->
    <nav class="sticky top-0 z-50 w-full bg-white/95 backdrop-blur-sm border-b border-gray-200 shadow-sm transition-all duration-300">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-20">
          <div class="flex items-center space-x-3">
            <div class="h-10 w-10 bg-emerald-700 rounded-lg flex items-center justify-center shadow-md">
              <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
            <span class="text-xl font-bold text-gray-800 tracking-tight">ANIBUKID</span>
          </div>
          
          <div class="hidden md:flex items-center space-x-8">
            <a href="#capabilities" class="text-sm font-medium text-gray-600 hover:text-emerald-700 transition-colors">Capabilities</a>
            <a href="#about" class="text-sm font-medium text-gray-600 hover:text-emerald-700 transition-colors">About System</a>
            <a href="#contact" class="text-sm font-medium text-gray-600 hover:text-emerald-700 transition-colors">Support</a>
          </div>

          <div class="flex items-center space-x-4">
             <router-link to="/register" class="hidden md:inline-flex px-5 py-2.5 rounded-md text-sm font-semibold text-white bg-emerald-700 hover:bg-emerald-800 transition-all duration-200 shadow-sm">
              Register
            </router-link>
          </div>
        </div>
      </div>
    </nav>

    <!-- Hero Section -->
    <main class="flex-grow">
      <div class="relative overflow-hidden bg-white">
        <div class="max-w-7xl mx-auto">
          <div class="relative z-10 pb-8 bg-white sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32 min-h-[calc(100vh-5rem)] flex items-center">
            
            <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-0 lg:px-8 xl:mt-0">
              <div class="sm:text-center lg:text-left">
                <div class="inline-flex items-center px-3 py-1 rounded-md bg-emerald-50 text-emerald-800 text-xs font-semibold uppercase tracking-wide mb-6 border border-emerald-100">
                  <span class="w-2 h-2 bg-emerald-700 rounded-full mr-2"></span>
                  Agricultural Management System
                </div>
                <h1 class="text-4xl tracking-tight font-extrabold text-gray-900 sm:text-5xl md:text-6xl mb-6">
                  <span class="block">Precision Farming</span>
                  <span class="block text-emerald-700">Enterprise Solutions</span>
                </h1>
                <p class="mt-3 text-base text-gray-600 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0 leading-relaxed">
                  A comprehensive platform designed to optimize agricultural productivity through data-driven insights, resource management, and operational analytics. Be empowered with precision tools for the modern agricultural landscape.
                </p>
                
                <!-- Integrated Login Form for Mobile/Desktop -->
                <div class="mt-8 sm:max-w-md sm:w-full sm:mx-auto lg:mx-0">
                   <div class="bg-white rounded-lg shadow-lg border border-gray-200 p-6 sm:p-8 relative overflow-hidden">
                      
                      <h3 class="text-lg font-bold text-gray-900 mb-6 flex items-center border-b border-gray-100 pb-4">
                        Authorized Personnel Access
                        <span class="ml-auto text-xs font-normal text-gray-500 flex items-center">
                            <svg class="h-3 w-3 mr-1 text-emerald-600" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                            Secure Connection
                        </span>
                      </h3>

                      <form @submit.prevent="handleLogin" class="space-y-5">
                        <div class="space-y-4">
                            <div>
                                <label for="login_id" class="block text-sm font-medium text-gray-700 mb-1">Account ID</label>
                                <div class="relative">
                                    <input
                                        id="login_id"
                                        v-model="form.login_id"
                                        name="login_id"
                                        type="text"
                                        required
                                        class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm bg-gray-50 focus:bg-white transition-colors"
                                        placeholder="Email or Phone Number"
                                    />
                                </div>
                            </div>

                            <div>
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                                <div class="relative">
                                    <input
                                        id="password"
                                        v-model="form.password"
                                        name="password"
                                        type="password"
                                        required
                                        class="block w-full px-4 py-3 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-1 focus:ring-emerald-600 focus:border-emerald-600 sm:text-sm bg-gray-50 focus:bg-white transition-colors"
                                        placeholder="Enter your password"
                                    />
                                </div>
                            </div>
                        </div>

                        <div v-if="authStore.error" class="rounded-md bg-red-50 p-3 border border-red-100">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <p class="text-sm font-medium text-red-800">{{ authStore.error }}</p>
                                </div>
                            </div>
                        </div>

                        <button
                            type="submit"
                            :disabled="authStore.loading"
                            class="w-full flex justify-center py-3 px-4 border border-transparent rounded-md shadow-sm text-sm font-semibold text-white bg-emerald-700 hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-600 transition-all duration-200 disabled:opacity-75 disabled:cursor-not-allowed"
                        >
                            <span v-if="authStore.loading" class="mr-2">
                                <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                            </span>
                            {{ authStore.loading ? 'Authenticating...' : 'Sign In' }}
                        </button>
                      </form>
                   </div>
                   <div class="mt-4 text-center">
                      <p class="text-sm text-gray-600">
                          Need access? <router-link to="/register" class="font-medium text-emerald-700 hover:text-emerald-800 underline">Register for an account</router-link>
                      </p>
                   </div>
                </div>
              </div>
            </main>
          </div>
        </div>
        
        <!-- Right Image Side -->
        <div class="lg:absolute lg:inset-y-0 lg:right-0 lg:w-1/2 bg-slate-100 border-l border-gray-200">
          <div class="h-56 w-full sm:h-72 md:h-96 lg:w-full lg:h-full relative overflow-hidden">
             <!-- Clean Professional Gradient -->
             <div class="absolute inset-0 bg-gradient-to-br from-slate-200 to-gray-200 opacity-50"></div>
             <!-- Background Texture -->
            <div class="absolute inset-0 opacity-10" style="background-image: radial-gradient(#444 1px, transparent 1px); background-size: 20px 20px;"></div>

             <!-- Floating Metric Card - More Structured -->
             <div class="absolute bottom-1/4 left-12 right-12 lg:left-24 lg:right-auto lg:w-96 bg-white shadow-xl rounded-lg border border-gray-200 p-6">
                <div class="flex items-center justify-between border-b border-gray-100 pb-4 mb-4">
                    <h4 class="text-sm font-bold text-gray-800 uppercase tracking-widest">Yield Analytics</h4>
                    <span class="text-xs font-semibold text-emerald-600 bg-emerald-50 px-2 py-1 rounded">Live Data</span>
                </div>
                <div class="space-y-4">
                    <div class="flex justify-between items-end">
                        <span class="text-3xl font-bold text-gray-900">24.8%</span>
                        <span class="text-sm font-medium text-emerald-600 mb-1">↑ Year over Year</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                        <div class="bg-emerald-600 h-1.5 rounded-full" style="width: 75%"></div>
                    </div>
                </div>
             </div>

          </div>
        </div>
      </div>

      <!-- Feature Section -->
      <div id="capabilities" class="py-24 bg-white relative">
          <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
              <div class="text-center max-w-3xl mx-auto mb-16">
                  <h2 class="text-sm font-bold text-emerald-700 tracking-widest uppercase">System Capabilities</h2>
                  <p class="mt-2 text-3xl font-extrabold text-gray-900 tracking-tight">Centralized Operations Control</p>
                  <p class="mt-4 text-lg text-gray-500 max-w-2xl mx-auto">A unified interface for comprehensive farm management, ensuring seamless integration of all operational aspects.</p>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-10">
                  <!-- Feature 1 -->
                  <div class="group p-6 bg-white rounded-lg border border-gray-200 hover:border-emerald-300 hover:shadow-md transition-all duration-300">
                      <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center mb-6 text-emerald-700">
                          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" /></svg>
                      </div>
                      <h3 class="text-lg font-bold text-gray-900 mb-2">Task Delegation & Tracking</h3>
                      <p class="text-gray-600 text-sm leading-relaxed">Efficiently assign operational units, monitor completion rates, and manage labor resources with precision-based scheduling.</p>
                  </div>

                  <!-- Feature 2 -->
                  <div class="group p-6 bg-white rounded-lg border border-gray-200 hover:border-emerald-300 hover:shadow-md transition-all duration-300">
                      <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mb-6 text-blue-700">
                          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" /></svg>
                      </div>
                      <h3 class="text-lg font-bold text-gray-900 mb-2">Climatic Analytics</h3>
                      <p class="text-gray-600 text-sm leading-relaxed">Leverage hyper-local meteorological data for strategic decision-making and risk mitigation for your agricultural assets.</p>
                  </div>

                  <!-- Feature 3 -->
                  <div class="group p-6 bg-white rounded-lg border border-gray-200 hover:border-emerald-300 hover:shadow-md transition-all duration-300">
                      <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center mb-6 text-amber-700">
                          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                      </div>
                      <h3 class="text-lg font-bold text-gray-900 mb-2">Commercial Marketplace</h3>
                      <p class="text-gray-600 text-sm leading-relaxed">Direct business-to-business engagement infrastructure, optimizing supply chain efficiency and maximizing revenue potential.</p>
                  </div>
              </div>
          </div>
      </div>
    </main>

    <!-- Footer -->
    <footer class="bg-gray-900 border-t border-gray-800 text-white">
      <div class="max-w-7xl mx-auto py-12 px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row justify-between items-center">
            <div class="flex items-center space-x-3 mb-4 md:mb-0">
               <div class="h-8 w-8 bg-emerald-800 rounded flex items-center justify-center">
                   <svg class="h-5 w-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                   </svg>
               </div>
               <span class="font-bold text-lg tracking-wider text-gray-300">ANIBUKID CORPORATE</span>
            </div>
            <p class="text-gray-500 text-xs">
                &copy; {{ new Date().getFullYear() }} ANIBUKID Systems. All Rights Reserved. Restricted Access.
            </p>
        </div>
      </div>
    </footer>
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
    
    // Check if user is already redirected or we need to handle it
    if (authStore.isAuthenticated) {
        router.push('/dashboard');
    }
  } catch (error) {
    console.error('Login error:', error);
  }
};
</script>
