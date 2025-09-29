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
      const nextWeek = new Date();
      nextWeek.setDate(nextWeek.getDate() + 7);
      return state.tasks.filter(t => 
        new Date(t.due_date) <= nextWeek && 
        ['pending', 'in_progress'].includes(t.status)
      );
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

    async fetchFields() {
      this.loading = true;
      try {
        const response = await fieldsAPI.getAll();
        this.fields = response.data.fields;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch fields';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchPlantings() {
      this.loading = true;
      try {
        const response = await plantingsAPI.getAll();
        this.plantings = response.data.plantings;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch plantings';
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
      try {
        const response = await tasksAPI.getAll();
        this.tasks = response.data.tasks;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch tasks';
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
