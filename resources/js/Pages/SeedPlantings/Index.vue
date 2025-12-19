<template>
  <div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
      <h1 class="text-2xl font-bold text-gray-800">Nursery (Seed Plantings)</h1>
      <button
        @click="$router.push('/seed-plantings/create')"
        class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition"
      >
        Add New Sowing
      </button>
    </div>

    <div v-if="loading" class="text-center py-8">
      <p class="text-gray-500">Loading...</p>
    </div>

    <div v-else-if="seedPlantings.length === 0" class="text-center py-8 bg-white rounded-lg shadow">
      <p class="text-gray-500 mb-4">No seed plantings found.</p>
      <button
        @click="$router.push('/seed-plantings/create')"
        class="text-green-600 hover:text-green-700 font-medium"
      >
        Start your first sowing
      </button>
    </div>

    <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="planting in seedPlantings"
        :key="planting.id"
        class="bg-white rounded-lg shadow p-6 hover:shadow-md transition"
      >
        <div class="flex justify-between items-start mb-4">
          <div>
            <h3 class="font-bold text-lg text-gray-900">{{ planting.rice_variety?.name }}</h3>
            <span
              class="inline-block px-2 py-1 text-xs rounded-full mt-1"
              :class="getStatusClass(planting.status)"
            >
              {{ formatStatus(planting.status) }}
            </span>
          </div>
          <div class="text-right text-sm text-gray-500">
            <div>{{ formatDate(planting.planting_date) }}</div>
            <div class="text-xs">Sown Date</div>
          </div>
        </div>

        <div class="space-y-2 mb-4">
          <div class="flex justify-between text-sm">
            <span class="text-gray-500">Quantity:</span>
            <span class="font-medium">{{ planting.quantity }} {{ planting.unit }}</span>
          </div>
          <div class="flex justify-between text-sm" v-if="planting.expected_transplant_date">
            <span class="text-gray-500">Est. Transplant:</span>
            <span class="font-medium">{{ formatDate(planting.expected_transplant_date) }}</span>
          </div>
        </div>

        <div class="pt-4 border-t border-gray-100 flex justify-end space-x-2">
           <button
             v-if="planting.status === 'sown' || planting.status === 'germinating'"
             @click="updateStatus(planting, 'ready')"
             class="text-xs bg-blue-50 text-blue-600 px-3 py-1 rounded hover:bg-blue-100"
           >
             Mark Ready
           </button>
           <button
             @click="deletePlanting(planting)"
             class="text-xs text-red-600 px-3 py-1 hover:bg-red-50 rounded"
           >
             Delete
           </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const seedPlantings = ref([]);
const loading = ref(true);

const fetchSeedPlantings = async () => {
  try {
    const response = await axios.get('/api/seed-plantings');
    seedPlantings.value = response.data;
  } catch (error) {
    console.error('Error fetching seed plantings:', error);
  } finally {
    loading.value = false;
  }
};

const updateStatus = async (planting, status) => {
  if (!confirm(`Mark this planting as ${status}?`)) return;
  
  try {
    await axios.put(`/api/seed-plantings/${planting.id}`, { status });
    await fetchSeedPlantings();
  } catch (error) {
    console.error('Error updating status:', error);
    alert('Failed to update status');
  }
};

const deletePlanting = async (planting) => {
  if (!confirm('Are you sure you want to delete this record?')) return;
  
  try {
    await axios.delete(`/api/seed-plantings/${planting.id}`);
    await fetchSeedPlantings();
  } catch (error) {
    console.error('Error deleting planting:', error);
    alert('Failed to delete record');
  }
};

const getStatusClass = (status) => {
  const classes = {
    sown: 'bg-gray-100 text-gray-800',
    germinating: 'bg-yellow-100 text-yellow-800',
    ready: 'bg-green-100 text-green-800',
    transplanted: 'bg-blue-100 text-blue-800',
    failed: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100';
};

const formatStatus = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1);
};

const formatDate = (dateString) => {
  if (!dateString) return '-';
  return new Date(dateString).toLocaleDateString();
};

onMounted(() => {
  fetchSeedPlantings();
});
</script>
