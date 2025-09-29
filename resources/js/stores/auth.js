import { defineStore } from 'pinia';
import axios from 'axios';

export const useAuthStore = defineStore('auth', {
  state: () => ({
    user: null,
    token: localStorage.getItem('token'),
    loading: false,
    error: null,
  }),

  getters: {
    isAuthenticated: (state) => !!state.token,
    isAdmin: (state) => state.user?.role === 'admin',
    isFarmer: (state) => state.user?.role === 'farmer',
    isBuyer: (state) => state.user?.role === 'buyer',
    needsOnboarding: (state) => state.user?.role === 'farmer' && !state.user?.farm_profile,
  },

  actions: {
    async login(credentials) {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.post('/api/login', credentials);
        
        this.token = response.data.token;
        this.user = response.data.user;
        
        localStorage.setItem('token', this.token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Login failed';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async register(userData) {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.post('/api/register', userData);
        
        this.token = response.data.token;
        this.user = response.data.user;
        
        localStorage.setItem('token', this.token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
        
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Registration failed';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async logout() {
      this.loading = true;

      try {
        if (this.token) {
          await axios.post('/api/logout');
        }
      } catch (error) {
        console.error('Logout error:', error);
      } finally {
        this.user = null;
        this.token = null;
        this.error = null;
        this.loading = false;
        
        localStorage.removeItem('token');
        delete axios.defaults.headers.common['Authorization'];
      }
    },

    async fetchUser() {
      if (!this.token) return;

      this.loading = true;

      try {
        const response = await axios.get('/api/user');
        this.user = response.data.user;
      } catch (error) {
        console.error('Fetch user error:', error);
        // If token is invalid, logout
        if (error.response?.status === 401) {
          this.logout();
        }
      } finally {
        this.loading = false;
      }
    },

    async updateProfile(profileData) {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.put('/api/profile', profileData);
        this.user = response.data.user;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Profile update failed';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async changePassword(passwordData) {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.put('/api/change-password', passwordData);
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Password change failed';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    initializeAuth() {
      if (this.token) {
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;
      }
    },
  },
});