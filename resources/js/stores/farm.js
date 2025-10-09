import { defineStore } from 'pinia';
import { plantingsAPI, tasksAPI, harvestsAPI, fieldsAPI, farmProfileAPI } from '@/services/api';

export const useFarmStore = defineStore('farm', {
  state: () => ({
    farmProfile: null,
    fields: [],
    plantings: [],
    tasks: [],
    harvests: [],
    loading: false,
    error: null,
  }),

  getters: {
    hasFarmProfile: (state) => !!state.farmProfile,
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
    lowStockItems: (state) => state.inventory?.filter(item => item.quantity <= item.min_stock) || [],
  },

  actions: {
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
        const response = await farmProfileAPI.createRiceFarm(profileData);
        this.farmProfile = response.data.farmProfile;
        this.fields = response.data.fields || [];
        return response.data;
      } catch (error) {
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
        
        // Don't clear existing data on error, just log it
        if (!this.fields.length) {
          this.fields = [];
        }
        
        throw error;
      } finally {
        this.loading = false;
      }
    },

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

    async createPlanting(plantingData) {
      this.loading = true;
      try {
        const response = await plantingsAPI.create(plantingData);
        this.plantings.push(response.data.planting);
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create planting';
        throw error;
      } finally {
        this.loading = false;
      }
    },

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

    async fetchHarvests() {
      this.loading = true;
      try {
        const response = await harvestsAPI.getAll();
        this.harvests = response.data.harvests;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch harvests';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async createHarvest(harvestData) {
      this.loading = true;
      try {
        const response = await harvestsAPI.create(harvestData);
        this.harvests.push(response.data.harvest);
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to create harvest';
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});
