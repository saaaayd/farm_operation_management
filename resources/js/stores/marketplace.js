import { defineStore } from 'pinia';
import axios from 'axios';
import { riceMarketplaceAPI, riceVarietiesAPI } from '@/services/api';
import { useAuthStore } from './auth';

export const useMarketplaceStore = defineStore('marketplace', {
  state: () => ({
    products: [],
    productsPagination: null,
    categories: [],
    cart: [],
    orders: [],
    ordersPagination: null,
    sales: [],
    farmerProducts: [],
    riceVarieties: [],
    loading: false,
    error: null,
  }),

  getters: {
    cartItemsCount: (state) => {
      try {
        if (!Array.isArray(state.cart)) return 0;
        return state.cart.reduce((total, item) => {
          return total + (item && typeof item.quantity === 'number' ? item.quantity : 0);
        }, 0);
      } catch (error) {
        console.warn('Error in cartItemsCount getter:', error);
        return 0;
      }
    },
    cartTotal: (state) => {
      try {
        if (!Array.isArray(state.cart)) return 0;
        return state.cart.reduce((total, item) => {
          if (!item || typeof item.price !== 'number' || typeof item.quantity !== 'number') {
            return total;
          }
          return total + (item.price * item.quantity);
        }, 0);
      } catch (error) {
        console.warn('Error in cartTotal getter:', error);
        return 0;
      }
    },
    riceProducts: (state) => {
      try {
        if (!Array.isArray(state.products)) return [];
        return state.products.filter(product => {
          return product && product.category === 'Harvested Rice';
        });
      } catch (error) {
        console.warn('Error in riceProducts getter:', error);
        return [];
      }
    },
    availableProducts: (state) => {
      try {
        if (!Array.isArray(state.products)) return [];
        return state.products.filter(product => {
          return product && typeof product.quantity === 'number' && product.quantity > 0;
        });
      } catch (error) {
        console.warn('Error in availableProducts getter:', error);
        return [];
      }
    },
  },

  actions: {
    async fetchProducts(filters = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await riceMarketplaceAPI.getProducts(filters);
        const payload = response.data?.products;
        const items = payload?.data || payload || [];

        this.products = Array.isArray(items) ? items : [];
        this.productsPagination = payload
          ? {
            current_page: payload.current_page,
            last_page: payload.last_page,
            per_page: payload.per_page,
            total: payload.total,
          }
          : null;

        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch products';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchFarmerProducts(filters = {}) {
      this.loading = true;
      this.error = null;

      try {
        const authStore = useAuthStore();
        const farmerId = authStore.user?.id;
        console.log('Fetching farmer products for user ID:', farmerId);

        const params = {
          per_page: 100,
          my_products: true, // Request all products for the logged-in farmer
          sort_by: 'created_at',
          sort_order: 'desc',
          ...filters,
        };

        // Remove production_status from filters for my_products to avoid filtering out pending products
        delete params.production_status;

        console.log('Fetching with params:', params);
        const response = await riceMarketplaceAPI.getProducts(params);
        console.log('API response received:', response.data);

        const products = response.data?.products?.data || response.data?.products || [];
        console.log('Extracted products array:', products.length, 'items');

        // Only update if we got valid data
        if (Array.isArray(products)) {
          console.log(`✓ Setting ${products.length} farmer products in store`);
          this.farmerProducts = products;

          // Log each product for debugging
          products.forEach((product, index) => {
            console.log(`  Product ${index + 1}: ${product.name} (ID: ${product.id}, Status: ${product.approval_status}, Farmer ID: ${product.farmer_id})`);
          });
        } else {
          console.warn('Invalid products data received:', response.data);
          console.warn('Response structure:', JSON.stringify(response.data, null, 2));
          this.farmerProducts = [];
        }

        return response.data;
      } catch (error) {
        console.error('Failed to fetch farmer products:', error);
        console.error('Error response:', error.response?.data);
        console.error('Error status:', error.response?.status);
        this.error = error.response?.data?.message || 'Failed to fetch products';
        // Don't clear existing products on error
        if (!Array.isArray(this.farmerProducts)) {
          this.farmerProducts = [];
        }
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchRiceVarieties() {
      try {
        const response = await riceVarietiesAPI.getAll();
        const payload = response?.data;
        const varieties = payload?.data || payload?.varieties || payload || [];
        this.riceVarieties = Array.isArray(varieties) ? varieties : [];
        return response.data;
      } catch (error) {
        console.error('Failed to fetch rice varieties:', error);
        this.riceVarieties = [];
        return [];
      }
    },

    async createRiceProduct(productData) {
      this.loading = true;
      this.error = null;

      try {
        console.log('Creating rice product with data:', productData);
        const response = await riceMarketplaceAPI.createProduct(productData);
        console.log('Product creation response:', response.data);

        // Optimistically add the product to the store immediately
        const product = response.data?.product;
        if (product) {
          console.log('Adding product to farmerProducts array:', product);
          // Add to the beginning of the array
          if (!Array.isArray(this.farmerProducts)) {
            this.farmerProducts = [];
          }
          this.farmerProducts = [product, ...this.farmerProducts];
          console.log(`✓ Product added. Total products: ${this.farmerProducts.length}`);
        } else {
          console.warn('No product in response:', response.data);
        }

        return response.data;
      } catch (error) {
        console.error('Failed to create rice product:', error);
        console.error('Error response:', error.response?.data);
        this.error = error.response?.data?.message || 'Failed to create product';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateRiceProduct(productId, productData) {
      this.loading = true;
      this.error = null;

      try {
        const response = await riceMarketplaceAPI.updateProduct(productId, productData);
        const updated = response.data?.product;
        if (updated) {
          this.farmerProducts = (this.farmerProducts || []).map(product =>
            product.id === updated.id ? updated : product
          );
        }
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update product';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteRiceProduct(productId) {
      this.loading = true;
      this.error = null;

      try {
        const response = await riceMarketplaceAPI.deleteProduct(productId);
        this.farmerProducts = (this.farmerProducts || []).filter(product => product.id !== Number(productId));
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete product';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async getRiceProduct(productId) {
      try {
        const response = await riceMarketplaceAPI.getProductById(productId);
        return response.data?.product || response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to load product';
        throw error;
      }
    },

    async fetchCategories() {
      this.loading = true;
      try {
        const response = await axios.get('/api/marketplace/categories');
        this.categories = response.data.categories;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch categories';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchOrders(filters = {}) {
      this.loading = true;
      this.error = null;

      try {
        const response = await riceMarketplaceAPI.getOrders(filters);
        const payload = response.data?.orders;
        const items = payload?.data || payload || [];

        this.orders = Array.isArray(items) ? items : [];
        this.ordersPagination = payload
          ? {
            current_page: payload.current_page,
            last_page: payload.last_page,
            per_page: payload.per_page,
            total: payload.total,
          }
          : null;

        return response.data;
      } catch (error) {
        console.error('Failed to fetch orders:', error);
        this.error = error.userMessage || error.response?.data?.message || 'Failed to fetch orders';
        this.orders = Array.isArray(this.orders) ? this.orders : [];
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchSales() {
      this.loading = true;
      try {
        const response = await axios.get('/api/sales');
        this.sales = response.data.sales;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch sales';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Cart management
    addToCart(product, quantity = 1) {
      const existingItem = this.cart.find(item => item.id === product.id);

      if (existingItem) {
        existingItem.quantity += quantity;
      } else {
        this.cart.push({
          id: product.id,
          name: product.name,
          price: Number(product.price), // Ensure price is a number
          quantity: quantity,
          image: product.image,
          farmer: product.farmer,
        });
      }

      this.saveCartToStorage();
    },

    removeFromCart(productId) {
      this.cart = this.cart.filter(item => item.id !== productId);
      this.saveCartToStorage();
    },

    updateCartQuantity(productId, quantity) {
      const item = this.cart.find(item => item.id === productId);
      if (item) {
        if (quantity <= 0) {
          this.removeFromCart(productId);
        } else {
          item.quantity = quantity;
        }
        this.saveCartToStorage();
      }
    },

    clearCart() {
      this.cart = [];
      this.saveCartToStorage();
    },

    saveCartToStorage() {
      localStorage.setItem('marketplace_cart', JSON.stringify(this.cart));
    },

    loadCartFromStorage() {
      const savedCart = localStorage.getItem('marketplace_cart');
      if (savedCart) {
        this.cart = JSON.parse(savedCart);
      }
    },

    async createOrder(orderData) {
      this.loading = true;
      try {
        const response = await riceMarketplaceAPI.createOrder(orderData);
        // Don't clear cart here, let the component handle it after all orders are created
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create order';
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});
