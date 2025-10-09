import { useAuthStore } from '@/stores/auth';

// Import components
import Login from '@/Pages/Auth/Login.vue';
import Register from '@/Pages/Auth/Register.vue';
import FarmProfile from '@/Pages/Onboarding/FarmProfile.vue';
import Dashboard from '@/Pages/Dashboard.vue';
import Profile from '@/Pages/Profile.vue';

// Farmer-specific components
import FarmerDashboard from '@/Pages/Farmer/Dashboard.vue';
import BuyerDashboard from '@/Pages/Buyer/Dashboard.vue';
import AdminDashboard from '@/Pages/Admin/Dashboard.vue';

// Farm Management
import PlantingsIndex from '@/Pages/Farmer/Plantings/Index.vue';
import PlantingsCreate from '@/Pages/Farmer/Plantings/Create.vue';
import TasksIndex from '@/Pages/Farmer/Tasks/Index.vue';
import HarvestsCreate from '@/Pages/Farmer/Harvests/Create.vue';

// Weather
import WeatherAnalytics from '@/Pages/Farmer/Weather/Analytics.vue';

// Marketplace
import Marketplace from '@/Pages/Marketplace/Index.vue';
import ProductDetail from '@/Pages/Marketplace/ProductDetail.vue';
import Cart from '@/Pages/Marketplace/Cart.vue';
import OrdersList from '@/Pages/Marketplace/Orders/Index.vue';
import OrderDetail from '@/Pages/Marketplace/Orders/Show.vue';

// Reports
import ReportsIndex from '@/Pages/Farmer/Reports/Index.vue';

// Inventory Management
import InventoryList from '@/Pages/Inventory/Index.vue';
import InventoryDetail from '@/Pages/Inventory/Show.vue';

// Weather
import WeatherDashboard from '@/Pages/Weather/Dashboard.vue';
import FieldWeather from '@/Pages/Weather/FieldWeather.vue';

// Admin
import UsersList from '@/Pages/Admin/Users/Index.vue';
import SystemStats from '@/Pages/Admin/SystemStats.vue';

// Reports
import FinancialReports from '@/Pages/Reports/Financial.vue';
import CropYieldReports from '@/Pages/Reports/CropYield.vue';
import WeatherReports from '@/Pages/Reports/Weather.vue';

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/Pages/Auth/Login.vue'), // Temporary, will be handled by router guard
  },
  {
    path: '/login',
    name: 'login',
    component: Login,
    meta: { requiresGuest: true }
  },
  {
    path: '/register',
    name: 'register',
    component: Register,
    meta: { requiresGuest: true }
  },
  {
    path: '/onboarding',
    name: 'onboarding',
    component: FarmProfile,
    meta: { requiresAuth: true, requiresOnboarding: true }
  },
  {
    path: '/dashboard',
    name: 'dashboard',
    component: Dashboard,
    meta: { requiresAuth: true }
  },
  {
    path: '/profile',
    name: 'profile',
    component: Profile,
    meta: { requiresAuth: true }
  },
  
  // Farm Management Routes (Rice-specific)
  {
    path: '/plantings',
    name: 'plantings',
    component: PlantingsIndex,
    meta: { requiresAuth: true, roles: ['farmer', 'admin'] }
  },
  {
    path: '/plantings/create',
    name: 'plantings-create',
    component: PlantingsCreate,
    meta: { requiresAuth: true, roles: ['farmer', 'admin'] }
  },
  {
    path: '/tasks',
    name: 'tasks',
    component: TasksIndex,
    meta: { requiresAuth: true, roles: ['farmer', 'admin'] }
  },
  {
    path: '/harvests/create',
    name: 'harvests-create',
    component: HarvestsCreate,
    meta: { requiresAuth: true, roles: ['farmer', 'admin'] }
  },
  
  // Weather Routes
  {
    path: '/weather',
    name: 'weather',
    component: WeatherDashboard,
    meta: { requiresAuth: true, roles: ['farmer', 'admin'] }
  },
  
  
  // Reports Routes
  {
    path: '/reports',
    name: 'reports',
    component: ReportsIndex,
    meta: { requiresAuth: true, roles: ['farmer', 'admin'] }
  },
  
  // Inventory Routes
  {
    path: '/inventory',
    name: 'inventory',
    component: InventoryList,
    meta: { requiresAuth: true, roles: ['farmer', 'admin'] }
  },
  {
    path: '/inventory/:id',
    name: 'inventory-detail',
    component: InventoryDetail,
    meta: { requiresAuth: true, roles: ['farmer', 'admin'] }
  },
  
  // Weather Routes  
  {
    path: '/weather/fields/:id',
    name: 'field-weather',
    component: FieldWeather,
    meta: { requiresAuth: true, roles: ['farmer', 'admin'] }
  },
  
  // Marketplace Routes
  {
    path: '/marketplace',
    name: 'marketplace',
    component: Marketplace,
    meta: { requiresAuth: true }
  },
  {
    path: '/marketplace/products/:id',
    name: 'product-detail',
    component: ProductDetail,
    meta: { requiresAuth: true }
  },
  {
    path: '/cart',
    name: 'cart',
    component: Cart,
    meta: { requiresAuth: true, roles: ['buyer'] }
  },
  {
    path: '/orders',
    name: 'orders',
    component: OrdersList,
    meta: { requiresAuth: true }
  },
  {
    path: '/orders/:id',
    name: 'order-detail',
    component: OrderDetail,
    meta: { requiresAuth: true }
  },
  
  // Admin Routes
  {
    path: '/admin',
    name: 'admin',
    component: AdminDashboard,
    meta: { requiresAuth: true, roles: ['admin'] }
  },
  {
    path: '/admin/users',
    name: 'admin-users',
    component: UsersList,
    meta: { requiresAuth: true, roles: ['admin'] }
  },
  {
    path: '/admin/stats',
    name: 'admin-stats',
    component: SystemStats,
    meta: { requiresAuth: true, roles: ['admin'] }
  },
  
  // Reports Routes
  {
    path: '/reports/financial',
    name: 'financial-reports',
    component: FinancialReports,
    meta: { requiresAuth: true, roles: ['farmer', 'admin'] }
  },
  {
    path: '/reports/crop-yield',
    name: 'crop-yield-reports',
    component: CropYieldReports,
    meta: { requiresAuth: true, roles: ['farmer', 'admin'] }
  },
  {
    path: '/reports/weather',
    name: 'weather-reports',
    component: WeatherReports,
    meta: { requiresAuth: true, roles: ['farmer', 'admin'] }
  },
];

export default routes;

// Navigation guard function to be used in main app
export const setupRouterGuards = (router) => {
  router.beforeEach((to, from, next) => {
    try {
      const authStore = useAuthStore();
      
      console.log(`Router: Navigating from ${from.path} to ${to.path}`);
      
      // Handle root path redirect
      if (to.path === '/') {
        if (authStore.isAuthenticated) {
          console.log('Router: Redirecting authenticated user to dashboard');
          next('/dashboard');
        } else {
          console.log('Router: Redirecting unauthenticated user to login');
          next('/login');
        }
        return;
      }
      
      // Check if route requires authentication
      if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        console.log('Router: Route requires auth, redirecting to login');
        next('/login');
        return;
      }
      
      // Check if route requires guest (not authenticated)
      if (to.meta.requiresGuest && authStore.isAuthenticated) {
        console.log('Router: Guest route accessed by authenticated user, redirecting to dashboard');
        next('/dashboard');
        return;
      }
      
      // Check if route requires onboarding completion
      if (to.meta.requiresOnboarding && authStore.needsOnboarding) {
        console.log('Router: Redirecting to onboarding');
        next('/onboarding');
        return;
      }
      
      // Check if user needs onboarding but trying to access other routes
      if (authStore.needsOnboarding && !to.meta.requiresOnboarding && to.path !== '/onboarding') {
        console.log('Router: User needs onboarding, redirecting');
        next('/onboarding');
        return;
      }
      
      // Check role-based access
      if (to.meta.roles && authStore.user) {
        if (!to.meta.roles.includes(authStore.user.role)) {
          console.log(`Router: User role ${authStore.user.role} not allowed for route, redirecting to dashboard`);
          next('/dashboard'); // Redirect to dashboard if role not allowed
          return;
        }
      }
      
      console.log(`Router: Navigation to ${to.path} allowed`);
      next();
    } catch (error) {
      console.error('Router guard error:', error);
      // Fallback to allow navigation to prevent infinite loops
      next();
    }
  });
  
  // Add error handler for router errors
  router.onError((error) => {
    console.error('Router error:', error);
    // Could show a toast notification here
  });
};