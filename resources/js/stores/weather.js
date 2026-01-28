import { defineStore } from 'pinia';
import axios from 'axios';

export const useWeatherStore = defineStore('weather', {
  state: () => ({
    currentWeather: null,
    fieldsWeather: {}, // Cache for field-specific weather { fieldId: { data: ..., timestamp: ... } }
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
      [
        'heavy_rain',
        'drought',
        'typhoon',
        'extreme_temperature',
        'high_humidity',
        'low_humidity'
      ].includes(alert.type)
    ),
  },

  actions: {
    async fetchCurrentWeather(fieldId, force = false) {
      // Check cache first (10 minute TTL)
      const now = Date.now();
      const cached = this.fieldsWeather[fieldId];
      if (!force && cached && (now - cached.timestamp < 10 * 60 * 1000)) {
        this.currentWeather = cached.data;
        return { weather: cached.data, alerts: cached.alerts };
      }

      this.loading = true;
      try {
        const response = await axios.get(`/api/weather/fields/${fieldId}/current`);
        const weatherData = response.data.weather;

        this.currentWeather = weatherData;
        if (response.data.alerts) {
          this.alerts = response.data.alerts;
        }

        // Update cache
        this.fieldsWeather[fieldId] = {
          data: weatherData,
          alerts: response.data.alerts || [],
          timestamp: now
        };

        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch current weather';
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchForecast(fieldId, days = 7) {
      this.loading = true;
      try {
        const response = await axios.get(`/api/weather/fields/${fieldId}/forecast`, {
          params: { days }
        });
        this.forecast = response.data.forecast || [];
        return response.data;
      } catch (error) {
        this.error = error.response?.data?.message || 'Failed to fetch forecast';
        console.error('Forecast fetch error:', error);
        this.forecast = []; // Set to empty array on error
        throw error;
      } finally {
        this.loading = false;
      }
    },

    async fetchWeatherHistory(fieldId, days = 30) {
      this.loading = true;
      try {
        const response = await axios.get(`/api/weather/fields/${fieldId}/history`, {
          params: { days, per_page: 5000 }
        });
        // Handle paginated or direct response structure
        if (response.data.weather_logs) {
          this.weatherHistory = response.data.weather_logs.data || response.data.weather_logs;
        } else {
          this.weatherHistory = response.data.data || response.data.history || [];
        }
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
