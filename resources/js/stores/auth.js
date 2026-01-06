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

        if (!response.data.token || !response.data.user) {
          throw new Error('Invalid response from server');
        }

        this.token = response.data.token;
        this.user = response.data.user;

        localStorage.setItem('token', this.token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

        console.log('✓ Login successful for user:', this.user.name);
        return response.data;
      } catch (error) {
        console.error('Login error:', error);
        this.error = error.userMessage || error.response?.data?.message || 'Login failed. Please try again.';

        // Clear any partial state
        this.token = null;
        this.user = null;
        localStorage.removeItem('token');
        delete axios.defaults.headers.common['Authorization'];

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

        // Registration successful, but no token yet (requires verification)
        // this.token = response.data.token;
        // this.user = response.data.user;

        // localStorage.setItem('token', this.token);
        // axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

        return response.data;
      } catch (error) {
        // Handle validation errors
        if (error.response?.status === 422 && error.response?.data?.errors) {
          // Format validation errors for display
          const errors = error.response.data.errors;
          const errorMessages = Object.values(errors).flat().join(', ');
          this.error = error.response.data.message + ': ' + errorMessages;
        } else {
          this.error = error.response?.data?.message || 'Registration failed';
        }
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async verifyPhone(identifier, code) {
      this.loading = true;
      this.error = null;

      try {
        // Determine if identifier is email or phone
        const isEmail = identifier && identifier.includes('@');
        const payload = isEmail
          ? { email: identifier, code }
          : { phone: identifier, code };

        const response = await axios.post('/api/verify-phone', payload);

        this.token = response.data.token;
        this.user = response.data.user;

        localStorage.setItem('token', this.token);
        axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Verification failed';
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
      if (!this.token) {
        console.warn('No token available for fetching user');
        return;
      }

      this.loading = true;
      this.error = null;

      try {
        const response = await axios.get('/api/user');

        if (!response.data.user) {
          throw new Error('Invalid user data received');
        }

        this.user = response.data.user;
        console.log('✓ User data fetched successfully');
      } catch (error) {
        console.error('Fetch user error:', error);

        // If token is invalid or expired, logout gracefully
        if (error.response?.status === 401) {
          console.warn('Token expired or invalid, logging out...');
          await this.logout();
          // Redirect to login if not already there
          if (window.location.pathname !== '/login') {
            window.location.href = '/login';
          }
        } else {
          this.error = error.userMessage || 'Failed to fetch user data';
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