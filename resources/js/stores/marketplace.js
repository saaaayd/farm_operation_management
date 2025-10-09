import { defineStore } from 'pinia';
import axios from 'axios';

export const useMarketplaceStore = defineStore('marketplace', {
  state: () => ({
    products: [],
    categories: [],
    cart: [],
    orders: [],
    sales: [],
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
      try {
        const response = await axios.get('/api/marketplace/products', { params: filters });
        this.products = response.data.available_products;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch products';
        throw error;
      } finally {
        this.loading = false;
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

    async fetchOrders() {
      this.loading = true;
      this.error = null;
      
      try {
        const response = await axios.get('/api/orders');
        
        if (!response.data) {
          console.warn('No orders data received, using empty array');
          this.orders = [];
          return { orders: [] };
        }
        
        // Handle different response formats
        const orders = response.data.orders || response.data.data || [];
        
        if (!Array.isArray(orders)) {
          console.warn('Invalid orders data received, using empty array');
          this.orders = [];
          return { orders: [] };
        }
        
        this.orders = orders;
        console.log(`✓ Loaded ${this.orders.length} orders`);
        return response.data;
      } catch (error) {
        console.error('Failed to fetch orders:', error);
        this.error = error.userMessage || error.response?.data?.message || 'Failed to fetch orders';
        
        if (!this.orders.length) {
          this.orders = [];
        }
        
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
          price: product.price,
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
        const response = await axios.post('/api/orders', orderData);
        this.clearCart(); // Clear cart after successful order
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
