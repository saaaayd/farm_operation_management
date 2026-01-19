import { defineStore } from 'pinia';
import axios from 'axios';
import api, { riceMarketplaceAPI, riceVarietiesAPI, cartAPI } from '@/services/api';
import { useAuthStore } from './auth';

export const useMarketplaceStore = defineStore('marketplace', {
  state: () => {
    return {
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
    };
  },

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
        // Return all products - rice marketplace products don't use category filtering
        return state.products;
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
            console.log(`  Product ${index + 1}: ${product.name} (ID: ${product.id}, Status: ${product.production_status}, Farmer ID: ${product.farmer_id})`);
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
        const authStore = useAuthStore();
        const isFarmer = authStore.user?.role === 'farmer';

        // Use role-specific endpoint
        const endpoint = isFarmer
          ? '/rice-marketplace/farmer/orders'
          : '/rice-marketplace/buyer/orders';

        const response = await api.get(endpoint, { params: filters });
        const payload = response.data?.orders;
        const items = payload?.data || payload || [];

        this.orders = Array.isArray(items) ? items : [];
        // Only set pagination if the response actually contains pagination data
        this.ordersPagination = (payload && typeof payload.current_page === 'number')
          ? {
            current_page: payload.current_page,
            last_page: payload.last_page || 1,
            per_page: payload.per_page || 10,
            total: payload.total || items.length,
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
    async fetchCart() {
      this.loading = true;
      try {
        const response = await cartAPI.get();
        // The backend returns { items: [...], total: ..., item_count: ... }
        // Map backend items to frontend structure if needed, or update components to match backend
        const items = response.data?.items || [];

        // Transform backend items to expected frontend format
        this.cart = items.map(item => ({
          id: item.id, // Cart item ID
          product_id: item.rice_product_id,
          name: item.rice_product?.name,
          price: Number(item.rice_product?.price_per_unit || 0),
          unit: item.rice_product?.unit || 'kg',
          quantity: Number(item.quantity) || 0, // Parse as number
          image: item.rice_product?.images?.[0] || item.rice_product?.image,
          farmer: item.rice_product?.farmer,
          stock: item.rice_product?.quantity_available
        }));

        return response.data;
      } catch (error) {
        console.error('Failed to fetch cart:', error);
        this.error = error.response?.data?.message || 'Failed to load cart';
        // Fallback to empty cart on error?
        // this.cart = [];
      } finally {
        this.loading = false;
      }
    },

    async addToCart(product, quantity = 1) {
      this.loading = true;
      this.error = null;
      try {
        const response = await cartAPI.add({
          rice_product_id: product.id,
          quantity: quantity
        });

        // Refresh cart to get updated state
        await this.fetchCart();

        return response.data;
      } catch (error) {
        console.error('Failed to add to cart:', error);
        this.error = error.response?.data?.message || 'Failed to add item to cart';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async removeFromCart(cartItemId) {
      this.loading = true;
      try {
        await cartAPI.remove(cartItemId);
        // Remove locally 
        this.cart = this.cart.filter(item => item.id !== cartItemId);
      } catch (error) {
        console.error('Failed to remove from cart:', error);
        this.error = error.response?.data?.message || 'Failed to remove item';
        // Refetch to be safe
        await this.fetchCart();
      } finally {
        this.loading = false;
      }
    },

    async updateCartQuantity(cartItemId, quantity) {
      if (quantity <= 0) {
        return this.removeFromCart(cartItemId);
      }

      this.loading = true;
      try {
        await cartAPI.update(cartItemId, { quantity });
        // Update locally
        const item = this.cart.find(item => item.id === cartItemId);
        if (item) {
          item.quantity = quantity;
        }
      } catch (error) {
        console.error('Failed to update quantity:', error);
        this.error = error.response?.data?.message || 'Failed to update quantity';
        // Refetch to be safe
        await this.fetchCart();
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async clearCart() {
      this.loading = true;
      try {
        await cartAPI.clear();
        this.cart = [];
      } catch (error) {
        console.error('Failed to clear cart:', error);
        this.error = error.response?.data?.message || 'Failed to clear cart';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // saveCartToStorage and loadCartFromStorage are no longer needed for backend-sync cart

    async createOrder(orderData) {
      this.loading = true;
      try {
        const response = await riceMarketplaceAPI.createOrder(orderData);
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create order';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async checkout(checkoutData) {
      this.loading = true;
      this.error = null;
      try {
        // Use the bulk checkout endpoint
        const response = await cartAPI.checkout(checkoutData);

        // Cart is cleared on backend, clear it locally
        this.cart = [];

        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Checkout failed';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // Buyer order actions
    async fetchBuyerOrders() {
      this.loading = true;
      try {
        const response = await axios.get('/api/rice-marketplace/buyer/orders');
        const payload = response.data?.orders;
        // Handle paginated response - extract data array
        const items = payload?.data || payload || [];
        this.orders = Array.isArray(items) ? items : [];
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch orders';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async confirmOrderDelivery(orderId) {
      try {
        const response = await axios.post(`/api/rice-marketplace/orders/${orderId}/deliver`);
        return response.data;
      } catch (error) {
        throw error;
      }
    },

    async disputeOrder(orderId, reason) {
      try {
        const response = await axios.post(`/api/rice-marketplace/orders/${orderId}/dispute`, { reason });
        return response.data;
      } catch (error) {
        throw error;
      }
    },

    // Farmer order actions
    async fetchFarmerOrders() {
      this.loading = true;
      try {
        const response = await axios.get('/api/rice-marketplace/farmer/orders');
        const payload = response.data?.orders;
        // Handle paginated response - extract data array
        const items = payload?.data || payload || [];
        this.orders = Array.isArray(items) ? items : [];
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch orders';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async acceptOrder(orderId, expectedDeliveryDate = null, notes = null) {
      try {
        const response = await axios.post(`/api/rice-marketplace/orders/${orderId}/accept`, {
          expected_delivery_date: expectedDeliveryDate,
          farmer_notes: notes
        });
        return response.data;
      } catch (error) {
        throw error;
      }
    },

    async rejectOrder(orderId, reason = null) {
      try {
        const response = await axios.post(`/api/rice-marketplace/orders/${orderId}/reject`, { reason });
        return response.data;
      } catch (error) {
        throw error;
      }
    },

    async shipOrder(orderId, trackingNumber = null) {
      try {
        const response = await axios.post(`/api/rice-marketplace/orders/${orderId}/ship`, {
          tracking_number: trackingNumber
        });
        return response.data;
      } catch (error) {
        throw error;
      }
    },

    async markAsPaid(orderId) {
      try {
        const response = await axios.post(`/api/rice-marketplace/orders/${orderId}/mark-paid`);
        return response.data;
      } catch (error) {
        throw error;
      }
    },

    async respondToNegotiation(orderId, action) {
      try {
        const response = await axios.post(`/api/rice-marketplace/orders/${orderId}/negotiate`, {
          action: action // 'accept' or 'reject'
        });
        return response.data;
      } catch (error) {
        throw error;
      }
    },
  },
});
