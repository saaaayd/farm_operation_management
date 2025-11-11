<template>
  <div class="min-h-screen bg-gray-100 py-10">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">
            {{ planting?.crop_type || 'Planting Details' }}
          </h1>
          <p class="text-sm text-gray-600">
            Field: {{ planting?.field?.name ?? 'Unknown field' }}
          </p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="goBack"
            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50"
          >
            Back to Plantings
          </button>
          <button
            @click="goToEdit"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md text-sm font-medium text-white bg-green-600 hover:bg-green-700"
          >
            Edit Planting
          </button>
        </div>
      </div>

      <div v-if="loading" class="bg-white shadow rounded-lg p-6 flex items-center justify-center">
        <svg class="animate-spin h-6 w-6 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
      </div>

      <div v-else-if="error" class="bg-white shadow rounded-lg p-6">
        <p class="text-red-600">{{ error }}</p>
      </div>

      <div v-else-if="planting" class="space-y-6">
        <!-- Overview -->
        <div class="bg-white shadow rounded-lg p-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Variety</h2>
              <p class="mt-2 text-lg text-gray-900">
                {{ planting.rice_variety?.name ?? 'Not specified' }}
              </p>
              <p class="text-sm text-gray-600">
                {{ planting.rice_variety?.description }}
              </p>
            </div>
            <div>
              <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wide">Status</h2>
              <span
                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                :class="statusColor(planting.status)"
              >
                {{ formatLabel(planting.status) }}
              </span>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
            <div>
              <h3 class="text-sm text-gray-500">Planting Date</h3>
              <p class="text-base text-gray-900">{{ formatDate(planting.planting_date) }}</p>
            </div>
            <div>
              <h3 class="text-sm text-gray-500">Expected Harvest</h3>
              <p class="text-base text-gray-900">{{ formatDate(planting.expected_harvest_date) }}</p>
            </div>
            <div>
              <h3 class="text-sm text-gray-500">Season</h3>
              <p class="text-base text-gray-900 capitalize">{{ planting.season }}</p>
            </div>
          </div>
        </div>

        <!-- Metrics -->
        <div class="bg-white shadow rounded-lg p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Planting Metrics</h2>
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
              <p class="text-sm text-gray-500">Area Planted</p>
              <p class="text-xl font-semibold text-gray-900">{{ planting.area_planted ?? '—' }} ha</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Seed Rate</p>
              <p class="text-xl font-semibold text-gray-900">
                {{ planting.seed_rate ? planting.seed_rate + ' kg/ha' : '—' }}
              </p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Planting Method</p>
              <p class="text-xl font-semibold text-gray-900">{{ formatLabel(planting.planting_method) }}</p>
            </div>
            <div>
              <p class="text-sm text-gray-500">Tasks Linked</p>
              <p class="text-xl font-semibold text-gray-900">{{ (planting.tasks || []).length }}</p>
            </div>
          </div>
        </div>

        <!-- Notes -->
        <div class="bg-white shadow rounded-lg p-6" v-if="planting.notes">
          <h2 class="text-lg font-semibold text-gray-900 mb-2">Notes</h2>
          <p class="text-gray-700 whitespace-pre-line">{{ planting.notes }}</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { plantingsAPI } from '@/services/api';

const route = useRoute();
const router = useRouter();

const planting = ref(null);
const loading = ref(true);
const error = ref('');

const formatDate = (value) => {
  if (!value) return 'Not set';
  const date = new Date(value);
  return Number.isNaN(date.getTime()) ? 'Not set' : date.toLocaleDateString();
};

const formatLabel = (value) => {
  if (!value) return 'Not set';
  return value
    .toString()
    .split('_')
    .map(part => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ');
};

const statusColor = (status) => {
  const map = {
    planned: 'bg-indigo-100 text-indigo-800',
    planted: 'bg-blue-100 text-blue-800',
    growing: 'bg-green-100 text-green-800',
    ready: 'bg-yellow-100 text-yellow-800',
    harvested: 'bg-gray-100 text-gray-800',
    failed: 'bg-red-100 text-red-800',
  };
  return map[status] || 'bg-gray-100 text-gray-800';
};

const goBack = () => {
  router.push('/plantings');
};

const goToEdit = () => {
  router.push(`/plantings/${route.params.id}/edit`);
};

const loadPlanting = async () => {
  try {
    const response = await plantingsAPI.getById(route.params.id);
    planting.value = response.data?.planting || response.data;
    if (!planting.value) {
      throw new Error('Planting not found');
    }
  } catch (err) {
    console.error('Failed to load planting:', err);
    error.value = err.response?.data?.message || err.message || 'Failed to load planting details.';
  } finally {
    loading.value = false;
  }
};

onMounted(loadPlanting);
</script>

