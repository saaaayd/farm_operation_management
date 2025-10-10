<template>
  <div class="chart-container bg-gray-100 border-2 border-dashed border-gray-300 rounded-lg flex items-center justify-center">
    <div class="text-center p-4">
      <div class="text-gray-500 mb-2">
        <svg class="h-12 w-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
        </svg>
      </div>
      <h3 class="text-sm font-medium text-gray-900">Line Chart Placeholder</h3>
      <p class="text-xs text-gray-500 mt-1">Chart temporarily disabled for debugging</p>
      <div class="mt-2 text-xs text-gray-400">
        Labels: {{ data?.labels?.length || 0 }} | 
        Datasets: {{ data?.datasets?.length || 0 }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';

const props = defineProps({
  data: {
    type: Object,
    required: true,
    default: () => ({
      labels: [],
      datasets: []
    })
  },
  options: {
    type: Object,
    default: () => ({})
  },
  height: {
    type: Number,
    default: 300
  }
});

onMounted(() => {
  console.log('🔄 LineChart (Placeholder): Component mounted successfully');
  console.log('📊 LineChart (Placeholder): Data received:', props.data);
});
</script>

<style scoped>
.chart-container {
  position: relative;
  width: 100%;
  height: v-bind(height + 'px');
}
</style>
