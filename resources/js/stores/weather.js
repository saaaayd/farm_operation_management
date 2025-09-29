import { defineStore } from 'pinia';
import axios from 'axios';

export const useWeatherStore = defineStore('weather', {
  state: () => ({
    currentWeather: null,
    forecast: [],
    weatherHistory: [],
    alerts: [],
    loading: false,
    error: null,
  }),

  getters: {
    hasWeatherData: (state) => !!state.currentWeather,
    criticalAlerts: (state) => state.alerts.filter(alert => alert.severity === 'critical'),
    weatherWarnings: (state) => state.alerts.filter(alert => 
      ['heavy_rain', 'drought', 'typhoon', 'extreme_temperature'].includes(alert.type)
    ),
  },

  actions: {
    async fetchCurrentWeather(fieldId) {
      this.loading = true;
      try {
        const response = await axios.get(`/api/weather/fields/${fieldId}/current`);
        this.currentWeather = response.data.weather;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch current weather';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchForecast(fieldId) {
      this.loading = true;
      try {
        const response = await axios.get(`/api/weather/fields/${fieldId}/forecast`);
        this.forecast = response.data.forecast;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch forecast';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchWeatherHistory(fieldId, days = 30) {
      this.loading = true;
      try {
        const response = await axios.get(`/api/weather/fields/${fieldId}/history?days=${days}`);
        this.weatherHistory = response.data.history;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch weather history';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchWeatherAlerts(fieldId) {
      this.loading = true;
      try {
        const response = await axios.get(`/api/weather/fields/${fieldId}/alerts`);
        this.alerts = response.data.alerts;
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch weather alerts';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async updateWeather(fieldId, weatherData) {
      this.loading = true;
      try {
        const response = await axios.post(`/api/weather/fields/${fieldId}/update`, weatherData);
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to update weather';
        throw error;
      } finally {
        this.loading = false;
      }
    },
  },
});
