<template>
  <div class="chart-container">
    <canvas ref="chartCanvas"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue';
import {
  Chart,
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend,
} from 'chart.js';

// Register Chart.js components
Chart.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
);

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

const chartCanvas = ref(null);
let chartInstance = null;

const defaultOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: true,
      position: 'top',
    },
    title: {
      display: false,
    },
    tooltip: {
      enabled: true,
      mode: 'index',
      intersect: false,
    }
  },
  scales: {
    x: {
      display: true,
      title: {
        display: true,
        text: 'Category'
      }
    },
    y: {
      display: true,
      beginAtZero: true,
      title: {
        display: true,
        text: 'Value'
      }
    }
  },
  interaction: {
    mode: 'index',
    intersect: false,
  },
};

const createChart = () => {
  if (!chartCanvas.value) return;

  const ctx = chartCanvas.value.getContext('2d');
  
  if (chartInstance) {
    chartInstance.destroy();
  }

  chartInstance = new Chart(ctx, {
    type: 'bar',
    data: props.data,
    options: {
      ...defaultOptions,
      ...props.options,
    }
  });
};

const updateChart = () => {
  if (chartInstance) {
    chartInstance.data = props.data;
    chartInstance.options = {
      ...defaultOptions,
      ...props.options,
    };
    chartInstance.update();
  }
};

onMounted(async () => {
  await nextTick();
  createChart();
});

watch(() => props.data, () => {
  updateChart();
}, { deep: true });

watch(() => props.options, () => {
  updateChart();
}, { deep: true });

// Cleanup on unmount
onUnmounted(() => {
  if (chartInstance) {
    chartInstance.destroy();
  }
});
</script>

<style scoped>
.chart-container {
  position: relative;
  width: 100%;
  height: v-bind(height + 'px');
}
</style>
