import { defineStore } from 'pinia';
// Make sure fieldsAPI is imported, I'm adding it based on your other imports
import { plantingsAPI, seedPlantingsAPI, tasksAPI, harvestsAPI, fieldsAPI, farmProfileAPI, salesAPI, expensesAPI } from '@/services/api';

export const useFarmStore = defineStore('farm', {
  state: () => ({
    farmProfile: null,
    fields: [],
    plantings: [],
    seedPlantings: [], // <-- Nursery storage
    currentPlanting: null,
    tasks: [],
    harvests: [], // <-- Harvests are here
    sales: [],
    expenses: [],
    loading: false,
    loadingPlanting: false,
    error: null,
  }),

  getters: {
    hasFarmProfile: (state) => !!state.farmProfile,
    // Getter to find plantings that can be harvested
    // Include all active plantings (not failed) as they can potentially be harvested
    harvestablePlantings: (state) => {
      if (!Array.isArray(state.plantings)) {
        console.warn('harvestablePlantings: state.plantings is not an array', state.plantings);
        return [];
      }
      const filtered = state.plantings.filter(p => {
        if (!p) {
          console.warn('harvestablePlantings: found null/undefined planting');
          return false;
        }
        if (!p.id) {
          console.warn('harvestablePlantings: planting missing id', p);
          return false;
        }
        // Only exclude explicitly failed plantings - include everything else
        const isFailed = p.status === 'failed';
        if (isFailed) {
          console.log('harvestablePlantings: excluding failed planting', p.id, p.status);
        }
        return !isFailed;
      });
      console.log('harvestablePlantings: filtered', filtered.length, 'from', state.plantings.length, 'plantings');
      return filtered;
    },
    activePlantings: (state) => state.plantings.filter(p => p.status !== 'harvested'),
    upcomingTasks: (state) => {
      try {
        if (!Array.isArray(state.tasks)) return [];

        const nextWeek = new Date();
        nextWeek.setDate(nextWeek.getDate() + 7);

        return state.tasks.filter(t => {
          if (!t || !t.due_date) return false;
          try {
            const dueDate = new Date(t.due_date);
            return !isNaN(dueDate.getTime()) &&
              dueDate <= nextWeek &&
              ['pending', 'in_progress'].includes(t.status);
          } catch (dateError) {
            console.warn('Invalid date in task:', t.due_date);
            return false;
          }
        });
      } catch (error) {
        console.warn('Error in upcomingTasks getter:', error);
        return [];
      }
    },
    lowStockItems: (state) => state.inventory?.filter(item => (item.current_stock ?? item.quantity ?? 0) <= (item.minimum_stock ?? item.min_stock ?? 0)) || [],
  },

  actions: {
    // --- PROFILE & FIELD ACTIONS ---
    async fetchFarmProfile() {
      this.loading = true;
      try {
        const response = await farmProfileAPI.get();
        this.farmProfile = response.data.farmProfile;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch farm profile';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async createFarmProfile(profileData) {
      this.loading = true;
      try {
        const response = await farmProfileAPI.create(profileData);
        this.farmProfile = response.data.farmProfile;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create farm profile';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async createRiceFarmProfile(profileData) {
      this.loading = true;
      try {
        console.log("📦 Sending farm profile data to API:", profileData);
        const response = await farmProfileAPI.createRiceFarm(profileData);
        this.farmProfile = response.data.farmProfile;
        this.fields = response.data.fields || [];
        console.log("✅ API Response:", response.data);
        return response.data;
      } catch (error) {
        console.error("❌ Farm profile creation failed:", error);
        this.error = error.response?.data?.message || 'Failed to create rice farm profile';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async fetchFields() {
      this.loading = true;
      this.error = null;
      try {
        const response = await fieldsAPI.getAll();
        if (!response.data || !Array.isArray(response.data.fields)) {
          console.warn('Invalid fields data received, using empty array');
          this.fields = [];
          return { fields: [] };
        }
        this.fields = response.data.fields;
        console.log(`✓ Loaded ${this.fields.length} fields`);
        return response.data;
      } catch (error) {
        console.error('Failed to fetch fields:', error);
        this.error = error.userMessage || error.response?.data?.message || 'Failed to fetch fields';
        if (!this.fields.length) {
          this.fields = [];
        }
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async createField(fieldData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await fieldsAPI.create(fieldData);
        if (response.data && response.data.field) {
          this.fields.push(response.data.field);
        }
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create field';
        console.error('Failed to create field:', error.response?.data);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async updateField(fieldId, fieldData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await fieldsAPI.update(fieldId, fieldData);
        if (response.data && response.data.field) {
          const index = this.fields.findIndex(f => f.id === fieldId);
          if (index !== -1) {
            this.fields[index] = response.data.field;
          }
        }
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update field';
        console.error('Failed to update field:', error.response?.data);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async deleteField(fieldId) {
      this.loading = true;
      this.error = null;
      try {
        await fieldsAPI.delete(fieldId);
        this.fields = this.fields.filter(f => f.id !== fieldId);
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete field';
        console.error('Failed to delete field:', error.response?.data);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // --- PLANTING ACTIONS ---
    async fetchPlantings() {
      this.loading = true;
      this.error = null;
      try {
        const response = await plantingsAPI.getAll();
        if (!response.data || !Array.isArray(response.data.plantings)) {
          console.warn('Invalid plantings data received, using empty array');
          this.plantings = [];
          return { plantings: [] };
        }
        this.plantings = response.data.plantings;
        console.log(`✓ Loaded ${this.plantings.length} plantings`);
        return response.data;
      } catch (error) {
        console.error('Failed to fetch plantings:', error);
        this.error = error.userMessage || error.response?.data?.message || 'Failed to fetch plantings';
        if (!this.plantings.length) {
          this.plantings = [];
        }
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async fetchPlantingById(plantingId) {
      this.loadingPlanting = true;
      this.currentPlanting = null;
      this.error = null;
      try {
        const response = await plantingsAPI.getById(plantingId);
        if (response.data && response.data.planting) {
          this.currentPlanting = response.data.planting;
        }
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch planting details';
        console.error('Failed to fetch planting:', error.response?.data);
        throw error;
      } finally {
        this.loadingPlanting = false;
      }
    },
    async createPlanting(plantingData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await plantingsAPI.create(plantingData);
        this.plantings.push(response.data.planting);
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create planting';
        console.error('Failed to create planting:', error.response?.data);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async updatePlanting(plantingId, plantingData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await plantingsAPI.update(plantingId, plantingData);
        const updated = response.data?.planting;
        if (updated) {
          this.plantings = (this.plantings || []).map(planting =>
            Number(planting.id) === Number(plantingId) ? updated : planting
          );
          if (this.currentPlanting && this.currentPlanting.id === plantingId) {
            this.currentPlanting = updated;
          }
        }
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update planting';
        console.error('Failed to update planting:', error.response?.data);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async deletePlanting(plantingId) {
      this.loading = true;
      this.error = null;
      try {
        await plantingsAPI.delete(plantingId);
        this.plantings = this.plantings.filter(p => p.id !== plantingId);
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete planting';
        console.error('Failed to delete planting:', error.response?.data);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // --- SEED PLANTING ACTIONS ---
    async fetchSeedPlantings() {
      this.loading = true;
      this.error = null;
      try {
        const response = await seedPlantingsAPI.getAll();
        this.seedPlantings = response.data || [];
        return response.data;
      } catch (error) {
        console.error('Failed to fetch seed plantings:', error);
        this.error = error.userMessage || 'Failed to fetch seed plantings';
        this.seedPlantings = [];
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // --- TASK ACTIONS ---
    async fetchTasks() {
      this.loading = true;
      this.error = null;
      try {
        const response = await tasksAPI.getAll();
        if (!response.data || !Array.isArray(response.data.tasks)) {
          console.warn('Invalid tasks data received, using empty array');
          this.tasks = [];
          return { tasks: [] };
        }
        this.tasks = response.data.tasks;
        console.log(`✓ Loaded ${this.tasks.length} tasks`);
        return response.data;
      } catch (error) {
        console.error('Failed to fetch tasks:', error);
        this.error = error.userMessage || error.response?.data?.message || 'Failed to fetch tasks';
        if (!this.tasks.length) {
          this.tasks = [];
        }
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async updateTask(taskId, taskData) {
      this.loading = true;
      try {
        const response = await tasksAPI.update(taskId, taskData);
        const index = this.tasks.findIndex(task => task.id === taskId);
        if (index !== -1) {
          this.tasks[index] = response.data.task;
        }
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update task';
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async createTask(taskData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await tasksAPI.create(taskData);
        if (response.data?.task) {
          this.tasks.unshift(response.data.task);
        } else if (response.data?.tasks) {
          this.tasks = response.data.tasks;
        }
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create task';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // --- HARVEST ACTIONS ---
    async fetchHarvests() {
      this.loading = true;
      this.error = null;
      try {
        const response = await harvestsAPI.getAll();
        this.harvests = response.data.harvests || [];
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch harvests';
        console.error('Failed to fetch harvests:', error);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async createHarvest(harvestData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await harvestsAPI.create(harvestData);
        if (response.data && response.data.harvest) {
          this.harvests.push(response.data.harvest);
        }
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create harvest';
        console.error('Failed to create harvest:', error.response?.data);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    // **NEW**
    async updateHarvest(harvestId, harvestData) {
      this.loading = true;
      this.error = null;
      try {
        const response = await harvestsAPI.update(harvestId, harvestData);
        if (response.data && response.data.harvest) {
          const index = this.harvests.findIndex(h => h.id === harvestId);
          if (index !== -1) {
            this.harvests[index] = response.data.harvest;
          }
        }
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update harvest';
        console.error('Failed to update harvest:', error.response?.data);
        throw error;
      } finally {
        this.loading = false;
      }
    },
    // **NEW**
    async deleteHarvest(harvestId) {
      this.loading = true;
      this.error = null;
      try {
        await harvestsAPI.delete(harvestId);
        this.harvests = this.harvests.filter(h => h.id !== harvestId);
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to delete harvest';
        console.error('Failed to delete harvest:', error.response?.data);
        throw error;
      } finally {
        this.loading = false;
      }
    },

    // --- SALES & EXPENSES ACTIONS ---
    async fetchSales(params = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await salesAPI.getAll(params);
        if (!response.data || !Array.isArray(response.data.sales)) {
          console.warn('Invalid sales data received, using empty array');
          this.sales = [];
          return { sales: [] };
        }
        this.sales = response.data.sales;
        console.log(`✓ Loaded ${this.sales.length} sales`);
        return response.data;
      } catch (error) {
        console.error('Failed to fetch sales:', error);
        this.error = error.userMessage || error.response?.data?.message || 'Failed to fetch sales';
        if (!this.sales.length) {
          this.sales = [];
        }
        throw error;
      } finally {
        this.loading = false;
      }
    },
    async fetchExpenses(params = {}) {
      this.loading = true;
      this.error = null;
      try {
        const response = await expensesAPI.getAll(params);
        if (!response.data || !Array.isArray(response.data.expenses)) {
          console.warn('Invalid expenses data received, using empty array');
          this.expenses = [];
          return { expenses: [] };
        }
        this.expenses = response.data.expenses;
        console.log(`✓ Loaded ${this.expenses.length} expenses`);
        return response.data;
      } catch (error) {
        console.error('Failed to fetch expenses:', error);
        this.error = error.userMessage || error.response?.data?.message || 'Failed to fetch expenses';
        if (!this.expenses.length) {
          this.expenses = [];
        }
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});