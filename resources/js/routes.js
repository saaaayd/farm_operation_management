import { useAuthStore } from '@/stores/auth';

// Import components
import Login from '@/Pages/Auth/Login.vue';
import Register from '@/Pages/Auth/Register.vue';
import FarmProfile from '@/Pages/Onboarding/FarmProfile.vue';
import Dashboard from '@/Pages/Dashboard.vue';
import Profile from '@/Pages/Profile.vue';

// Farmer-specific components

// Farm Management
import FarmerFieldsIndex from '@/Pages/Farmer/Fields/Index.vue';
import FarmerFieldsCreate from '@/Pages/Farmer/Fields/Create.vue';
import PlantingsIndex from '@/Pages/Farmer/Plantings/Index.vue';
import PlantingsCreate from '@/Pages/Farmer/Plantings/Create.vue';
import PlantingsShow from '@/Pages/Farmer/Plantings/Show.vue';
import PlantingsEdit from '@/Pages/Farmer/Plantings/Edit.vue';
import TasksIndex from '@/Pages/Farmer/Tasks/Index.vue';
import TasksCreate from '@/Pages/Farmer/Tasks/Create.vue';
import TasksShow from '@/Pages/Farmer/Tasks/Show.vue';
import TasksCalendar from '@/Pages/Farmer/Tasks/Calendar.vue';
import HarvestsIndex from '@/Pages/Farmer/Harvests/Index.vue';
import HarvestsCreate from '@/Pages/Farmer/Harvests/Create.vue';

// Weather
import WeatherAnalytics from '@/Pages/Farmer/Weather/Analytics.vue';

// Marketplace
import Marketplace from '@/Pages/Marketplace/Index.vue';
import ProductDetail from '@/Pages/Marketplace/ProductDetail.vue';
import Cart from '@/Pages/Marketplace/Cart.vue';
import OrdersList from '@/Pages/Marketplace/Orders/Index.vue';
import OrderDetail from '@/Pages/Marketplace/Orders/Show.vue';
import MarketplaceProductsIndex from '@/Pages/Marketplace/Product/Index.vue';
import MarketplaceProductCreate from '@/Pages/Marketplace/Product/Create.vue';
import MarketplaceProductEdit from '@/Pages/Marketplace/Product/Edit.vue';

// Reports
import ReportsIndex from '@/Pages/Farmer/Reports/Index.vue';

// Inventory Management
import InventoryList from '@/Pages/Inventory/Index.vue';
import InventoryDetail from '@/Pages/Inventory/Show.vue';

// Weather
import WeatherDashboard from '@/Pages/Weather/Dashboard.vue';
import FieldWeather from '@/Pages/Weather/FieldWeather.vue';

// Reports
import FinancialReports from '@/Pages/Reports/Financial.vue';
import CropYieldReports from '@/Pages/Reports/CropYield.vue';
import WeatherReports from '@/Pages/Reports/Weather.vue';

// Financial
import FinancialExpensesIndex from '@/Pages/Financial/Expenses/Index.vue';

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
    path: '/fields',
    name: 'fields',
    component: FarmerFieldsIndex,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/fields/create',
    name: 'fields-create',
    component: FarmerFieldsCreate,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/plantings',
    name: 'plantings',
    component: PlantingsIndex,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/plantings/create',
    name: 'plantings-create',
    component: PlantingsCreate,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/plantings/:id',
    name: 'plantings-show',
    component: PlantingsShow,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/plantings/:id/edit',
    name: 'plantings-edit',
    component: PlantingsEdit,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/tasks',
    name: 'tasks',
    component: TasksIndex,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/tasks/create',
    name: 'tasks-create',
    component: TasksCreate,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/tasks/:id',
    name: 'tasks-show',
    component: TasksShow,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/tasks/calendar',
    name: 'tasks-calendar',
    component: TasksCalendar,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/harvests',
    name: 'harvests',
    component: HarvestsIndex,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/harvests/create',
    name: 'harvests-create',
    component: HarvestsCreate,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  
  // Weather Routes
  {
    path: '/weather',
    name: 'weather',
    component: WeatherDashboard,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/weather/analytics',
    name: 'weather-analytics',
    component: WeatherAnalytics,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  
  
  // Reports Routes
  {
    path: '/reports',
    name: 'reports',
    component: ReportsIndex,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  
  // Inventory Routes
  {
    path: '/inventory',
    name: 'inventory',
    component: InventoryList,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/inventory/:id',
    name: 'inventory-detail',
    component: InventoryDetail,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  
  // Weather Routes  
  {
    path: '/weather/fields/:id',
    name: 'field-weather',
    component: FieldWeather,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  
  // Buyer Routes
  {
    path: '/buyer/products',
    name: 'buyer-products',
    component: () => import('@/Pages/Buyer/Products.vue'),
    meta: { requiresAuth: true, roles: ['buyer'] }
  },
  {
    path: '/buyer/products/:id',
    name: 'buyer-product-detail',
    component: () => import('@/Pages/Buyer/ProductDetail.vue'),
    meta: { requiresAuth: true, roles: ['buyer'] }
  },

  // Marketplace Routes
  {
    path: '/marketplace',
    name: 'marketplace',
    component: Marketplace,
    meta: { requiresAuth: true }
  },
  {
    path: '/marketplace/product/create',
    name: 'marketplace-product-create',
    component: MarketplaceProductCreate,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/marketplace/product/:id/edit',
    name: 'marketplace-product-edit',
    component: MarketplaceProductEdit,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/marketplace/my-products',
    name: 'marketplace-my-products',
    component: MarketplaceProductsIndex,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/marketplace/orders',
    name: 'marketplace-orders',
    component: OrdersList,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/marketplace/orders/:id',
    name: 'marketplace-order-detail',
    component: OrderDetail,
    meta: { requiresAuth: true, roles: ['farmer'] }
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
    meta: { requiresAuth: true, roles: ['buyer'] }
  },
  {
    path: '/orders/:id',
    name: 'order-detail',
    component: OrderDetail,
    meta: { requiresAuth: true, roles: ['buyer'] }
  },
  
  // Financial Routes
  {
    path: '/financial/expenses',
    name: 'financial-expenses',
    component: FinancialExpensesIndex,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },

  // Reports Routes
  {
    path: '/reports/financial',
    name: 'financial-reports',
    component: FinancialReports,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/reports/crop-yield',
    name: 'crop-yield-reports',
    component: CropYieldReports,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/reports/weather',
    name: 'weather-reports',
    component: WeatherReports,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
];

export default routes;

// Navigation guard function to be used in main app
export const setupRouterGuards = (router) => {
  router.beforeEach(async (to, from, next) => {
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
      
      // --- START OF THE FIX ---
      
      // If user is authenticated but user data is not loaded yet, wait for it
      // This is important for page reloads where the guard runs before user data is fetched
      if (authStore.isAuthenticated && !authStore.user && !authStore.loading) {
        console.log('Router: User authenticated but data not loaded, fetching user data...');
        try {
          await authStore.fetchUser();
        } catch (error) {
          console.error('Router: Failed to fetch user data:', error);
          // If fetch fails, allow navigation to continue (will be handled by auth checks)
        }
      }
      
      // Wait for user data to finish loading if it's currently loading
      if (authStore.isAuthenticated && authStore.loading) {
        console.log('Router: Waiting for user data to load...');
        // Wait up to 3 seconds for user data to load
        let attempts = 0;
        while (authStore.loading && attempts < 30) {
          await new Promise(resolve => setTimeout(resolve, 100));
          attempts++;
        }
      }
      
      // We calculate the onboarding status manually from the
      // 'authStore.user' object, which we know is fresh.
      const user = authStore.user;
      const userIsFarmer = user && user.role === 'farmer';
      
      // Check if the user's address (where farm data is stored) is missing.
      // This is the real source of truth for onboarding.
      const userHasNoFarm = userIsFarmer && (!user.address || !user.address.farm_location);

      // Check if user needs onboarding but is trying to go to a normal page
      if (userHasNoFarm && !to.meta.requiresOnboarding && to.path !== '/onboarding') {
        console.log('Router: User is a farmer with no farm, redirecting to onboarding');
        next('/onboarding');
        return;
      }

      // Check if user is ALREADY onboarded but tries to go back to /onboarding
      // Only redirect if we have user data (to avoid redirecting when user is null on reload)
      if (to.meta.requiresOnboarding && user && !userHasNoFarm) {
        console.log('Router: User is already onboarded, redirecting from /onboarding');
        next('/dashboard');
        return;
      }
      
      // If we're on onboarding page and user data is still loading, allow navigation
      // (don't redirect away from onboarding while user data is being fetched)
      if (to.meta.requiresOnboarding && !user && authStore.isAuthenticated) {
        console.log('Router: On onboarding page, user data still loading, allowing navigation');
        next();
        return;
      }
      
      // --- END OF THE FIX ---
      
      // Check role-based access
      if (to.meta.roles && authStore.user) {
        if (!to.meta.roles.includes(authStore.user.role)) {
          console.log(`Router: User role ${authStore.user.role} not allowed for route, redirecting to dashboard`);
          next('/dashboard');
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