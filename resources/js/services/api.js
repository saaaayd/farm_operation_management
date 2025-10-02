import axios from 'axios';

// API Base Configuration
const API_BASE_URL = '/api';

// Create axios instance
const api = axios.create({
  baseURL: API_BASE_URL,
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
});

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
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      localStorage.removeItem('token');
      window.location.href = '/login';
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
export const farmProfileAPI = {
  get: () => api.get('/farmer/profile'),
  create: (profileData) => api.post('/farmer/profile', profileData),
  createRiceFarm: (profileData) => api.post('/farmer/rice-farm-profile', profileData),
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
  confirmOrder: (id, data = {}) => api.post(`/rice-marketplace/orders/${id}/confirm`, data),
  cancelOrder: (id, data) => api.post(`/rice-marketplace/orders/${id}/cancel`, data),
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
  getAll: () => api.get('/sales'),
  getById: (id) => api.get(`/sales/${id}`),
  create: (saleData) => api.post('/sales', saleData),
  update: (id, saleData) => api.put(`/sales/${id}`, saleData),
  delete: (id) => api.delete(`/sales/${id}`),
};

// Expenses API
export const expensesAPI = {
  getAll: () => api.get('/expenses'),
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
};

// Dashboard API
export const dashboardAPI = {
  getStats: () => api.get('/dashboard'),
  getFarmerStats: () => api.get('/dashboard/farmer'),
  getBuyerStats: () => api.get('/dashboard/buyer'),
  getAdminStats: () => api.get('/dashboard/admin'),
};

// Analytics API
export const analyticsAPI = {
  getRiceFarmingAnalytics: (period = '12') => api.get(`/analytics/rice-farming?period=${period}`),
};

// Reports API
export const reportsAPI = {
  getFinancialReport: (period = '365') => api.get(`/reports/financial?period=${period}`),
  getCropYieldReport: (period = '365') => api.get(`/reports/crop-yield?period=${period}`),
  getWeatherReport: (period = '365') => api.get(`/reports/weather?period=${period}`),
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

// Admin API
export const adminAPI = {
  getUsers: () => api.get('/admin/users'),
  getUserById: (id) => api.get(`/admin/users/${id}`),
  updateUser: (id, userData) => api.put(`/admin/users/${id}`, userData),
  deleteUser: (id) => api.delete(`/admin/users/${id}`),
  getSystemStats: () => api.get('/admin/system-stats'),
};

export default api;
