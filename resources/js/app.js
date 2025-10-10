import './bootstrap';
import { createApp } from 'vue';
import { createRouter, createWebHistory } from 'vue-router';
import App from './App.vue';
import routes, { setupRouterGuards } from './routes';
import { createPinia } from 'pinia';
import { useAuthStore } from './stores/auth';

// Create Pinia store
const pinia = createPinia();

// Create Vue Router
const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Create Vue app
console.log('🚀 Creating Vue app...');
const app = createApp(App);

console.log('📦 Installing Pinia store...');
app.use(pinia);
console.log('🛣️ Installing Vue Router...');
app.use(router);

// Set up router guards after Pinia is installed
setupRouterGuards(router);

// Initialize auth state with error handling
const authStore = useAuthStore();
authStore.initializeAuth();

// Fetch user data if token exists (handled in App.vue onMounted)
// This prevents double fetching and race conditions

console.log('🎯 Mounting Vue app to #app...');
app.mount('#app');
console.log('✅ Vue app mounted successfully!');
