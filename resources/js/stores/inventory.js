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
    lowStockItems: (state) => state.items.filter(item => item.quantity <= item.min_stock),
    outOfStockItems: (state) => state.items.filter(item => item.quantity <= 0),
    riceSeeds: (state) => state.items.filter(item => item.category === 'seeds'),
    fertilizers: (state) => state.items.filter(item => item.category === 'fertilizer'),
    pesticides: (state) => state.items.filter(item => item.category === 'pesticide'),
    equipment: (state) => state.items.filter(item => item.category === 'equipment'),
    harvestedRice: (state) => state.items.filter(item => item.category === 'produce'),
  },

  actions: {
    async fetchItems() {
      this.loading = true;
      try {
        const response = await axios.get('/api/inventory');
        this.items = response.data.inventory_items;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch inventory items';
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

    async addStock(itemId, quantity) {
      this.loading = true;
      try {
        const response = await axios.post(`/api/inventory/${itemId}/add-stock`, { quantity });
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
