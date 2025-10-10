<template>
  <div class="chart-container bg-purple-50 border-2 border-dashed border-purple-300 rounded-lg flex items-center justify-center">
    <div class="text-center p-4">
      <div class="text-purple-500 mb-2">
        <svg class="h-12 w-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
        </svg>
      </div>
      <h3 class="text-sm font-medium text-gray-900">Pie Chart Placeholder</h3>
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
  console.log('🔄 PieChart (Placeholder): Component mounted successfully');
  console.log('📊 PieChart (Placeholder): Data received:', props.data);
});
</script>

<style scoped>
.chart-container {
  position: relative;
  width: 100%;
  height: v-bind(height + 'px');
}
</style>
