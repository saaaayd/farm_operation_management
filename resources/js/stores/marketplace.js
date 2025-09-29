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
    cartItemsCount: (state) => state.cart.reduce((total, item) => total + item.quantity, 0),
    cartTotal: (state) => state.cart.reduce((total, item) => total + (item.price * item.quantity), 0),
    riceProducts: (state) => state.products.filter(product => product.category === 'Harvested Rice'),
    availableProducts: (state) => state.products.filter(product => product.quantity > 0),
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
      try {
        const response = await axios.get('/api/orders');
        this.orders = response.data.orders;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch orders';
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
