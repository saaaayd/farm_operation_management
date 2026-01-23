import axios from 'axios';
import { useNotificationStore } from '@/stores/notification';

// API Base Configuration
const API_BASE_URL = '/api';

// Create axios instance with timeout and retry configuration
const api = axios.create({
  baseURL: API_BASE_URL,
  timeout: 15000, // Reduced to 15 second timeout to fail faster
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

// Retry configuration
const MAX_RETRIES = 2; // Reduced retries to fail faster
const RETRY_DELAY = 500; // Reduced delay

// Retry logic for failed requests
const retryRequest = async (config, retryCount = 0) => {
  try {
    return await api(config);
  } catch (error) {
    if (retryCount < MAX_RETRIES && shouldRetry(error)) {
      console.warn(`Request failed, retrying... (${retryCount + 1}/${MAX_RETRIES})`);
      await new Promise(resolve => setTimeout(resolve, RETRY_DELAY * (retryCount + 1)));
      return retryRequest(config, retryCount + 1);
    }
    throw error;
  }
};

// Determine if request should be retried
const shouldRetry = (error) => {
  // Don't retry on client errors (4xx) except timeout
  if (error.response && error.response.status >= 400 && error.response.status < 500) {
    return error.response.status === 408; // Only retry on request timeout
  }

  return (
    !error.response || // Network error
    error.response.status >= 500 || // Server error
    error.code === 'ECONNABORTED' // Axios timeout
  );
};

// Request interceptor to add auth token
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor for error handling
api.interceptors.response.use(
  (response) => {
    // Handle Success Notifications for non-GET requests
    if (['post', 'put', 'patch', 'delete'].includes(response.config.method)) {
      const notificationStore = useNotificationStore();
      const message = response.data?.message || 'Action completed successfully';

      // Check if it's not a background sync or similar silent operation
      if (!response.config?.silent) {
        notificationStore.success(message);
      }
    }
    return response;
  },
  async (error) => {
    const originalRequest = error.config;
    const notificationStore = useNotificationStore();

    // Log error for debugging
    console.error('API Error:', error);

    if (error.response && error.response.status === 422) {
      console.error('422 Validation Error Details:', error.response.data);
      // For validation errors, we might not want a global toast if the form handles it inline
      // But for now, let's show a generic "Check form" message if needed, or rely on form handling.
      // Often, 422s are handled by specific components.
      // Let's optionally show it if it's a critical action or configured to do so.
      // For global feedback requested by user:
      notificationStore.error(error.response.data.message || 'Please check your input.');
    }
    // Handle authentication errors
    else if (error.response?.status === 401) {
      localStorage.removeItem('token');
      delete axios.defaults.headers.common['Authorization'];

      // Use router if available, otherwise fallback to window.location
      if (window.location.pathname !== '/login') {
        // Don't toast on auth error redirect, it's disruptive
        window.location.href = '/login';
      }
      return Promise.reject(error);
    }
    else {
      // General Error Handling
      let errorMessage = 'An unexpected error occurred.';

      if (error.response?.data?.message) {
        errorMessage = error.response.data.message;
      } else if (error.userMessage) {
        errorMessage = error.userMessage;
      } else if (error.message) {
        errorMessage = error.message;
      }

      // Check for timeout/network specific messages from retry logic
      if (error.code === 'ECONNABORTED') {
        errorMessage = 'Request timed out. Please try again.';
      } else if (!error.response) {
        errorMessage = 'Network error. Please check your connection.';
      } else if (error.response?.status >= 500) {
        errorMessage = 'Server error. Please try again later.';
      }

      // Ensure we don't spam toasts if one is already showing for the exact same error?
      // For now, just show it.
      if (!originalRequest?.silent) {
        notificationStore.error(errorMessage);
      }
    }

    // Handle timeout and network errors with retry (EXISTING LOGIC)
    if (shouldRetry(error) && !originalRequest._retry) {
      originalRequest._retry = true;
      try {
        console.log(`Retrying request to ${originalRequest.url}`);
        return await retryRequest(originalRequest);
      } catch (retryError) {
        console.error('Request failed after retries:', retryError);
        // The error from retryRequest will propagate up and trigger the catch block above (or promise rejection)
        // But since we are inside the interceptor's error handler, we need to return reject.

        // Note: The recursive retryRequest calls api(), which triggers interceptors again.
        // This might lead to duplicate toasts if not careful. 
        // But retryRequest uses the 'api' instance. 
        // Let's rely on the final rejection to show the toast? 
        // Actually, if we retry, we don't want to show toast yet.
        // But we already showed it above because we are in the error handler!

        // Refinement: Move Notification Logic AFTER Retry Logic checks?
        // If we are observing a retry-able error, we might want to wait.
        // However, existing structure has retry logic separate.

        // Let's keep it simple for now. If it retries, the user might see an error then a success. 
        // Or multiple errors if all retries fail.

        return Promise.reject(retryError);
      }
    }

    return Promise.reject(error);
  }
);

// Auth API
export const authAPI = {
  login: (credentials) => api.post('/login', credentials),
  register: (userData) => api.post('/register', userData),
  logout: () => api.post('/logout'),
  getUser: () => api.get('/user'),
  updateProfile: (profileData) => api.put('/profile', profileData),
  changePassword: (passwordData) => api.put('/change-password', passwordData),
};

// Farm Profile API
// Farm Profile API
export const farmProfileAPI = {
  get: () => api.get('/farmer/profile'),

  create: (data) => {
    // Map frontend values to backend expected values
    const payload = {
      farm_name: data.farm_name,
      location: data.address || data.farm_location || data.location,
      total_area: parseFloat(data.farm_size),
      rice_area: parseFloat(data.farm_size),
      water_source: data.water_source || 'irrigation_canal',
      irrigation_type: data.irrigation_type || 'flood',
      water_access: data.water_access || 'good',
      drainage_quality: data.drainage_quality || 'good',
      soil_type: data.soil_type,
      preferred_varieties: data.preferred_variety ? [data.preferred_variety] : [],
      previous_yield: data.previous_yield ? parseFloat(data.previous_yield) : null,
      farming_experience: data.farming_experience ? parseInt(data.farming_experience) : null,
      notes: data.notes || null,
      farm_description: data.notes || null,
      planting_method: data.planting_method || null,
      target_yield: data.target_yield ? parseFloat(data.target_yield) : null,
      cropping_seasons: data.cropping_seasons || null,
      farming_challenges: data.farming_challenges || [],
    };

    console.log("📦 Sending payload to /api/farmer/profile:", payload);
    return api.post('/farmer/profile', payload);
  },

  createRiceFarm: (data) => {
    // Map onboarding form data to backend expected format
    const payload = {
      // Basic Information
      farm_name: data.farm_name,
      location: data.farm_location || data.location, // Map farm_location to location
      total_area: parseFloat(data.total_area) || 0,
      rice_area: parseFloat(data.rice_area) || 0,
      farming_experience: data.farming_experience ? parseInt(data.farming_experience) : null,
      farm_description: data.farm_description || null,

      // Field Information - REMOVED
      // Soil Information - REMOVED
      // Water Management - REMOVED
    };


    console.log("📦 Sending rice farm profile data to API:", payload);
    return api.post('/farmer/profile', payload);
  },

  update: (profileData) => api.put('/farmer/profile', profileData),
};

// Fields API
export const fieldsAPI = {
  getAll: () => api.get('/fields'),
  getById: (id) => api.get(`/fields/${id}`),
  create: (fieldData) => api.post('/fields', fieldData),
  update: (id, fieldData) => api.put(`/fields/${id}`, fieldData),
  delete: (id) => api.delete(`/fields/${id}`),
};

// Plantings API
export const plantingsAPI = {
  getAll: () => api.get('/plantings'),
  getById: (id) => api.get(`/plantings/${id}`),
  create: (plantingData) => api.post('/plantings', plantingData),
  update: (id, plantingData) => api.put(`/plantings/${id}`, plantingData),
  delete: (id) => api.delete(`/plantings/${id}`),
};

// Seed Plantings API
export const seedPlantingsAPI = {
  getAll: () => api.get('/seed-plantings'),
  getById: (id) => api.get(`/seed-plantings/${id}`),
  create: (data) => api.post('/seed-plantings', data),
  update: (id, data) => api.put(`/seed-plantings/${id}`, data),
  delete: (id) => api.delete(`/seed-plantings/${id}`),
  getReady: () => api.get('/seed-plantings/ready'),
};

// Tasks API
export const tasksAPI = {
  getAll: () => api.get('/tasks'),
  getById: (id) => api.get(`/tasks/${id}`),
  create: (taskData) => api.post('/tasks', taskData),
  update: (id, taskData) => api.put(`/tasks/${id}`, taskData),
  delete: (id) => api.delete(`/tasks/${id}`),
  markCompleted: (id) => api.post(`/tasks/${id}/complete`),
};

// Harvests API
export const harvestsAPI = {
  getAll: () => api.get('/harvests'),
  getById: (id) => api.get(`/harvests/${id}`),
  create: (harvestData) => api.post('/harvests', harvestData),
  update: (id, harvestData) => api.put(`/harvests/${id}`, harvestData),
  delete: (id) => api.delete(`/harvests/${id}`),
};

// Inventory API
export const inventoryAPI = {
  getAll: () => api.get('/inventory'),
  getById: (id) => api.get(`/inventory/${id}`),
  create: (itemData) => api.post('/inventory', itemData),
  update: (id, itemData) => api.put(`/inventory/${id}`, itemData),
  delete: (id) => api.delete(`/inventory/${id}`),
  addStock: (id, quantity) => api.post(`/inventory/${id}/add-stock`, { quantity }),
  removeStock: (id, quantity) => api.post(`/inventory/${id}/remove-stock`, { quantity }),
  getLowStockAlerts: () => api.get('/inventory/alerts/low-stock'),
};

// Weather API
export const weatherAPI = {
  getCurrentWeather: (fieldId) => api.get(`/weather/fields/${fieldId}/current`),
  getForecast: (fieldId) => api.get(`/weather/fields/${fieldId}/forecast`),
  getHistory: (fieldId, days = 30) => api.get(`/weather/fields/${fieldId}/history?days=${days}`),
  getAlerts: (fieldId) => api.get(`/weather/fields/${fieldId}/alerts`),
  updateWeather: (fieldId, weatherData) => api.post(`/weather/fields/${fieldId}/update`, weatherData),
  // ColorfulClouds Weather API proxy
  getColorfulCloudsWeather: (lat, lon, options = {}) => {
    const params = {
      lat,
      lon,
      unit: options.unit || 'imperial',
      lang: options.lang || 'en_US',
      granu: options.granu || 'realtime',
    };
    return api.get('/weather/colorfulclouds', { params });
  },
};

// Rice Marketplace API
export const riceMarketplaceAPI = {
  getProducts: (filters = {}) => api.get('/rice-marketplace/products', { params: filters }),
  getProductById: (id) => api.get(`/rice-marketplace/products/${id}`),
  createProduct: (productData) => api.post('/rice-marketplace/products', productData),
  updateProduct: (id, productData) => api.put(`/rice-marketplace/products/${id}`, productData),
  deleteProduct: (id) => api.delete(`/rice-marketplace/products/${id}`),
  getStats: () => api.get('/rice-marketplace/stats'),

  // Orders
  getOrders: (filters = {}) => api.get('/rice-marketplace/orders', { params: filters }),
  getOrderById: (id) => api.get(`/rice-marketplace/orders/${id}`),
  createOrder: (orderData) => api.post('/rice-marketplace/orders', orderData),
  confirmOrder: (id, data = {}) => api.post(`/rice-marketplace/orders/${id}/accept`, data),
  markReadyForPickup: (id) => api.post(`/rice-marketplace/orders/${id}/ready-for-pickup`),
  confirmPickup: (id) => api.post(`/rice-marketplace/orders/${id}/confirm-pickup`),
  markOrderAsPaid: (id) => api.post(`/rice-marketplace/orders/${id}/mark-paid`),
  cancelOrder: (id, data) => api.post(`/rice-marketplace/orders/${id}/cancel`, data),
  getOrderMessages: (id) => api.get(`/rice-marketplace/orders/${id}/messages`),
  sendOrderMessage: (id, payload) => api.post(`/rice-marketplace/orders/${id}/messages`, payload),
};

// Cart API
export const cartAPI = {
  get: () => api.get('/rice-marketplace/cart'),
  add: (itemData) => api.post('/rice-marketplace/cart', itemData),
  update: (itemId, itemData) => api.put(`/rice-marketplace/cart/${itemId}`, itemData),
  remove: (itemId) => api.delete(`/rice-marketplace/cart/${itemId}`),
  clear: () => api.delete('/rice-marketplace/cart'),
  checkout: (checkoutData) => api.post('/rice-marketplace/cart/checkout', checkoutData),
  count: () => api.get('/rice-marketplace/cart/count'),
};

// Legacy Marketplace API (for backward compatibility)
export const marketplaceAPI = {
  getProducts: (filters = {}) => api.get('/marketplace/products', { params: filters }),
  getProductById: (id) => api.get(`/marketplace/products/${id}`),
  getCategories: () => api.get('/marketplace/categories'),
  getByCategory: (categoryId) => api.get(`/marketplace/categories/${categoryId}/products`),
};

// Orders API
export const ordersAPI = {
  getAll: () => api.get('/orders'),
  getById: (id) => api.get(`/orders/${id}`),
  create: (orderData) => api.post('/orders', orderData),
  update: (id, orderData) => api.put(`/orders/${id}`, orderData),
  confirm: (id) => api.post(`/orders/${id}/confirm`),
  cancel: (id) => api.post(`/orders/${id}/cancel`),
  ship: (id) => api.post(`/orders/${id}/ship`),
  deliver: (id) => api.post(`/orders/${id}/deliver`),
};

// Sales API
export const salesAPI = {
  getAll: (params = {}) => api.get('/sales', { params }),
  getById: (id) => api.get(`/sales/${id}`),
  create: (saleData) => api.post('/sales', saleData),
  update: (id, saleData) => api.put(`/sales/${id}`, saleData),
  delete: (id) => api.delete(`/sales/${id}`),
};

// Expenses API
export const expensesAPI = {
  getAll: (params = {}) => api.get('/expenses', { params }),
  getById: (id) => api.get(`/expenses/${id}`),
  create: (expenseData) => api.post('/expenses', expenseData),
  update: (id, expenseData) => api.put(`/expenses/${id}`, expenseData),
  delete: (id) => api.delete(`/expenses/${id}`),
};

// Labor API
export const laborAPI = {
  getLaborers: () => api.get('/laborers'),
  getLaborerById: (id) => api.get(`/laborers/${id}`),
  createLaborer: (laborerData) => api.post('/laborers', laborerData),
  updateLaborer: (id, laborerData) => api.put(`/laborers/${id}`, laborerData),
  deleteLaborer: (id) => api.delete(`/laborers/${id}`),

  getWages: () => api.get('/labor-wages'),
  getWageById: (id) => api.get(`/labor-wages/${id}`),
  createWage: (wageData) => api.post('/labor-wages', wageData),
  updateWage: (id, wageData) => api.put(`/labor-wages/${id}`, wageData),
  deleteWage: (id) => api.delete(`/labor-wages/${id}`),

  // Groups
  getGroups: () => api.get('/laborers/groups'),
  addMembers: (groupId, laborerIds) => api.post(`/laborers/groups/${groupId}/members`, { laborer_ids: laborerIds }),
};

// Dashboard API
export const dashboardAPI = {
  getStats: () => api.get('/dashboard'),
  getFarmerStats: () => api.get('/dashboard'),
  getBuyerStats: () => api.get('/dashboard'),
};

// Analytics API
export const analyticsAPI = {
  getRiceFarmingAnalytics: (period = '12') => api.get(`/analytics/rice-farming?period=${period}`),
};

// Reports API
export const reportsAPI = {
  getFinancialReport: (period = '365') => api.get(`/reports/financial?period=${period}`),
  getCropYieldReport: (params = {}) => {
    const period = params.period || '365';
    const crop = params.crop || '';
    const field = params.field || '';
    let url = `/reports/crop-yield?period=${period}`;
    if (crop) url += `&crop=${crop}`;
    if (field) url += `&field=${field}`;
    return api.get(url);
  },
  getCropYieldFilterOptions: () => api.get('/reports/crop-yield/filter-options'),
  getWeatherReport: (period = '365', fieldId = null) => {
    let url = `/reports/weather?period=${period}`;
    if (fieldId) url += `&field_id=${fieldId}`;
    return api.get(url);
  },
  getLaborCostReport: (period = '365') => api.get(`/reports/labor-cost?period=${period}`),
  getInventoryUsageReport: (period = '365') => api.get(`/reports/inventory-usage?period=${period}`),
};

// Rice Varieties API
export const riceVarietiesAPI = {
  getAll: () => api.get('/rice-varieties'),
  getById: (id) => api.get(`/rice-varieties/${id}`),
  getCurrentSeason: () => api.get('/rice-varieties/current-season'),
  getRecommended: (fieldId) => api.get(`/rice-varieties/recommended/${fieldId}`),
};

// Rice Growth Stages API
export const riceGrowthStagesAPI = {
  getAll: () => api.get('/rice-growth-stages'),
  getById: (id) => api.get(`/rice-growth-stages/${id}`),
  getOrdered: () => api.get('/rice-growth-stages/ordered'),
};

// Planting Stages API
export const plantingStagesAPI = {
  getByPlanting: (plantingId) => api.get(`/plantings/${plantingId}/stages`),
  updateStage: (stageId, stageData) => api.put(`/planting-stages/${stageId}`, stageData),
  startStage: (stageId) => api.post(`/planting-stages/${stageId}/start`),
  completeStage: (stageId, notes = null) => api.post(`/planting-stages/${stageId}/complete`, { notes }),
  markDelayed: (stageId, notes = null) => api.post(`/planting-stages/${stageId}/delayed`, { notes }),
};

// Rice Farming Lifecycle API
export const riceFarmingAPI = {
  createPlanting: (plantingData) => api.post('/rice-farming/plantings', plantingData),
  getLifecycleOverview: () => api.get('/rice-farming/lifecycle-overview'),
  getPlantingLifecycle: (plantingId) => api.get(`/rice-farming/plantings/${plantingId}/lifecycle`),
  advanceStage: (plantingId, stageData) => api.post(`/rice-farming/plantings/${plantingId}/advance-stage`, stageData),
  getRecommendations: (plantingId) => api.get(`/rice-farming/plantings/${plantingId}/recommendations`),
  markStageDelayed: (stageId, delayData) => api.post(`/rice-farming/stages/${stageId}/delay`, delayData),
};

// Notifications API
export const notificationsAPI = {
  getAll: (params = {}) => api.get('/notifications', { params }), // supports unread_only=true
  getUnreadCount: () => api.get('/notifications/unread-count'),
  markAsRead: (id) => api.post(`/notifications/${id}/read`),
  markAllAsRead: () => api.post('/notifications/read-all'),
  delete: (id) => api.delete(`/notifications/${id}`),
};

export default api;
