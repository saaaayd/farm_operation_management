import { defineStore } from 'pinia';
import axios from 'axios';

export const useInventoryStore = defineStore('inventory', {
  state: () => ({
    items: [],
    categories: [
      { value: 'seeds', label: 'Rice Seeds' },
      { value: 'fertilizer', label: 'Fertilizers' },
      { value: 'pesticide', label: 'Pesticides' },
      { value: 'equipment', label: 'Equipment' },
      { value: 'produce', label: 'Harvested Rice' },
    ],
    riceVarietals: [
      { value: 'IR64', label: 'IR64' },
      { value: 'Jasmine', label: 'Jasmine Rice' },
      { value: 'Basmati', label: 'Basmati Rice' },
      { value: 'Arborio', label: 'Arborio Rice' },
      { value: 'Brown Rice', label: 'Brown Rice' },
    ],
    loading: false,
    error: null,
  }),

  getters: {
    lowStockItems: (state) => {
      try {
        if (!Array.isArray(state.items)) return [];
        return state.items.filter(item => {
          // Normalize properties to handle both potential formats
          const qty = item.current_stock !== undefined ? parseFloat(item.current_stock) : (item.quantity !== undefined ? parseFloat(item.quantity) : 0);
          const min = item.minimum_stock !== undefined ? parseFloat(item.minimum_stock) : (item.min_stock !== undefined ? parseFloat(item.min_stock) : 0);

          return item && qty <= min;
        });
      } catch (error) {
        console.warn('Error in lowStockItems getter:', error);
        return [];
      }
    },
    outOfStockItems: (state) => {
      try {
        if (!Array.isArray(state.items)) return [];
        return state.items.filter(item => {
          const qty = item.current_stock !== undefined ? parseFloat(item.current_stock) : (item.quantity !== undefined ? parseFloat(item.quantity) : 0);
          return item && qty <= 0;
        });
      } catch (error) {
        console.warn('Error in outOfStockItems getter:', error);
        return [];
      }
    },
    riceSeeds: (state) => {
      try {
        if (!Array.isArray(state.items)) return [];
        return state.items.filter(item => item && item.category === 'seeds');
      } catch (error) {
        console.warn('Error in riceSeeds getter:', error);
        return [];
      }
    },
    fertilizers: (state) => {
      try {
        if (!Array.isArray(state.items)) return [];
        return state.items.filter(item => item && item.category === 'fertilizer');
      } catch (error) {
        console.warn('Error in fertilizers getter:', error);
        return [];
      }
    },
    pesticides: (state) => {
      try {
        if (!Array.isArray(state.items)) return [];
        return state.items.filter(item => item && item.category === 'pesticide');
      } catch (error) {
        console.warn('Error in pesticides getter:', error);
        return [];
      }
    },
    equipment: (state) => {
      try {
        if (!Array.isArray(state.items)) return [];
        return state.items.filter(item => item && item.category === 'equipment');
      } catch (error) {
        console.warn('Error in equipment getter:', error);
        return [];
      }
    },
    harvestedRice: (state) => {
      try {
        if (!Array.isArray(state.items)) return [];
        return state.items.filter(item => item && item.category === 'produce');
      } catch (error) {
        console.warn('Error in harvestedRice getter:', error);
        return [];
      }
    },
  },

  actions: {
    async fetchItems() {
      this.loading = true;
      this.error = null;

      try {
        const response = await axios.get('/api/inventory');

        if (!response.data) {
          console.warn('No inventory data received, using empty array');
          this.items = [];
          return { inventory_items: [] };
        }

        // Handle different response formats
        const items = response.data.inventory_items || response.data.items || response.data.inventory || [];

        if (!Array.isArray(items)) {
          console.warn('Invalid inventory items data received, using empty array');
          this.items = [];
          return { inventory_items: [] };
        }

        this.items = items;
        console.log(`✓ Loaded ${this.items.length} inventory items`);
        return response.data;
      } catch (error) {
        console.error('Failed to fetch inventory items:', error);
        this.error = error.userMessage || error.response?.data?.message || 'Failed to fetch inventory items';

        if (!this.items.length) {
          this.items = [];
        }

        throw error;
      } finally {
        this.loading = false;
      }
    },

    async createItem(itemData) {
      this.loading = true;
      try {
        const response = await axios.post('/api/inventory', itemData);
        this.items.push(response.data.inventory_item);
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create inventory item';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateItem(itemId, itemData) {
      this.loading = true;
      try {
        const response = await axios.put(`/api/inventory/${itemId}`, itemData);
        const index = this.items.findIndex(item => item.id === itemId);
        if (index !== -1) {
          this.items[index] = response.data.inventory_item;
        }
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update inventory item';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async deleteItem(itemId) {
      this.loading = true;
      try {
        await axios.delete(`/api/inventory/${itemId}`);
        this.items = this.items.filter(item => item.id !== itemId);
        return true;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete inventory item';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async addStock(itemId, quantity, unitCost) {
      this.loading = true;
      try {
        const payload = { quantity };
        if (unitCost !== undefined && unitCost !== null && unitCost !== '') {
          payload.unit_cost = unitCost;
        }
        const response = await axios.post(`/api/inventory/${itemId}/add-stock`, payload);
        const index = this.items.findIndex(item => item.id === itemId);
        if (index !== -1) {
          this.items[index] = response.data.inventory_item;
        }
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to add stock';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async removeStock(itemId, quantity) {
      this.loading = true;
      try {
        const response = await axios.post(`/api/inventory/${itemId}/remove-stock`, { quantity });
        const index = this.items.findIndex(item => item.id === itemId);
        if (index !== -1) {
          this.items[index] = response.data.inventory_item;
        }
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to remove stock';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchLowStockAlerts() {
      this.loading = true;
      try {
        const response = await axios.get('/api/inventory/alerts/low-stock');
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch low stock alerts';
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});
