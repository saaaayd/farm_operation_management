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
import FarmerFieldsEdit from '@/Pages/Farmer/Fields/Edit.vue';
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

// Laborer Management
import LaborersIndex from '@/Pages/Farmer/Laborers/Index.vue';
import LaborersCreate from '@/Pages/Farmer/Laborers/Create.vue';
import LaborersShow from '@/Pages/Farmer/Laborers/Show.vue';
import LaborersEdit from '@/Pages/Farmer/Laborers/Edit.vue';
import LaborerGroupsIndex from '@/Pages/Farmer/LaborerGroups/Index.vue';
import LaborerGroupsShow from '@/Pages/Farmer/LaborerGroups/Show.vue';

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

// Seed Plantings
import SeedPlantingsIndex from '@/Pages/SeedPlantings/Index.vue';
import SeedPlantingsCreate from '@/Pages/SeedPlantings/Create.vue';

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('@/Pages/Landing.vue'),
    meta: { requiresGuest: true }
  },
  {
    path: '/verify-phone',
    name: 'verify-phone',
    component: () => import('@/Pages/Auth/VerifyPhone.vue'),
    meta: { requiresGuest: true }
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
    path: '/fields/:id/edit',
    name: 'fields-edit',
    component: FarmerFieldsEdit,
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

  // Seed Planting Routes
  {
    path: '/seed-plantings',
    name: 'seed-plantings',
    component: SeedPlantingsIndex,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/seed-plantings/create',
    name: 'seed-plantings-create',
    component: SeedPlantingsCreate,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/seed-plantings/:id',
    name: 'seed-plantings-show',
    component: () => import('@/Pages/SeedPlantings/Show.vue'),
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
  {
    path: '/harvests/:id',
    name: 'harvests-show',
    component: () => import('@/Pages/Farmer/Harvests/Show.vue'),
    meta: { requiresAuth: true, roles: ['farmer'] }
  },

  // Laborer Routes
  // Groups first to avoid catching by :id
  {
    path: '/laborers/groups',
    name: 'laborer-groups',
    component: LaborerGroupsIndex,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/laborers/groups/:id',
    name: 'laborer-groups-show',
    component: LaborerGroupsShow,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/laborers',
    name: 'laborers',
    component: LaborersIndex,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/laborers/create',
    name: 'laborers-create',
    component: LaborersCreate,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/laborers/:id',
    name: 'laborers-show',
    component: LaborersShow,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/laborers/:id/edit',
    name: 'laborers-edit',
    component: LaborersEdit,
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
    path: '/inventory/create',
    name: 'inventory-create',
    component: () => import('@/Pages/Inventory/Create.vue'),
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/inventory/:id',
    name: 'inventory-detail',
    component: InventoryDetail,
    meta: { requiresAuth: true, roles: ['farmer'] }
  },
  {
    path: '/inventory/:id/edit',
    name: 'inventory-edit',
    component: () => import('@/Pages/Inventory/Edit.vue'),
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
  {
    path: '/buyer/orders',
    name: 'buyer-orders',
    component: () => import('@/Pages/Buyer/Orders/Index.vue'),
    meta: { requiresAuth: true, roles: ['buyer'] }
  },
  {
    path: '/favorites',
    name: 'favorites',
    component: () => import('@/Pages/Buyer/Favorites.vue'),
    meta: { requiresAuth: true, roles: ['buyer'] }
  },
  {
    path: '/buyer/orders/:id',
    name: 'buyer-order-detail',
    component: () => import('@/Pages/Marketplace/Orders/Show.vue'),
    meta: { requiresAuth: true, roles: ['buyer'] }
  },

  // Farmer Order Routes
  {
    path: '/farmer/orders',
    name: 'farmer-orders',
    component: () => import('@/Pages/Farmer/Orders/Index.vue'),
    meta: { requiresAuth: true, roles: ['farmer'] }
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
    path: '/checkout',
    name: 'checkout',
    component: () => import('@/Pages/Marketplace/Checkout.vue'),
    meta: { requiresAuth: true, roles: ['buyer'] }
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

      // Handle root path redirect
      if (to.path === '/') {
        if (authStore.isAuthenticated) {
          next('/dashboard');
        } else {
          next('/login');
        }
        return;
      }

      // Check if route requires authentication
      if (to.meta.requiresAuth && !authStore.isAuthenticated) {
        return next({
          path: '/login',
          query: { redirect: to.fullPath }
        });
      }

      // Check if route requires guest (not authenticated)
      if (to.meta.requiresGuest && authStore.isAuthenticated) {
        return next('/dashboard');
      }

      // --- START OF THE FIX ---

      // If user is authenticated but user data is not loaded yet, wait for it
      // This is important for page reloads where the guard runs before user data is fetched
      if (authStore.isAuthenticated && !authStore.user && !authStore.loading) {
        try {
          await authStore.fetchUser();
        } catch (error) {
          console.error('Router: Failed to fetch user data:', error);
          // If fetch fails, allow navigation to continue (will be handled by auth checks)
        }
      }

      // Wait for user data to finish loading if it's currently loading
      if (authStore.isAuthenticated && authStore.loading) {
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
        next('/onboarding');
        return;
      }

      // Check if user is ALREADY onboarded but tries to go back to /onboarding
      // Only redirect if we have user data (to avoid redirecting when user is null on reload)
      if (to.meta.requiresOnboarding && user && !userHasNoFarm) {
        next('/dashboard');
        return;
      }

      // If we're on onboarding page and user data is still loading, allow navigation
      // (don't redirect away from onboarding while user data is being fetched)
      if (to.meta.requiresOnboarding && !user && authStore.isAuthenticated) {
        next();
        return;
      }

      // --- END OF THE FIX ---

      // Check role-based access
      if (to.meta.roles && authStore.user) {
        if (!to.meta.roles.includes(authStore.user.role)) {
          next('/dashboard');
          return;
        }
      }

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