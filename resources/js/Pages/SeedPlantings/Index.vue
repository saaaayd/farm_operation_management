<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="w-full mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Header Section -->
      <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            Nursery Management
          </h2>
          <p class="mt-1 text-sm text-gray-500">
            Track your seed sowings and monitor nursery status.
          </p>
        </div>
        <div class="mt-4 flex md:mt-0 md:ml-4">
          <button
            @click="$router.push('/seed-plantings/create')"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all transform hover:-translate-y-0.5"
          >
            <PlusIcon class="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
            New Sowing
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
        <div v-for="n in 3" :key="n" class="bg-white rounded-xl shadow-sm p-6 animate-pulse border border-gray-100">
          <div class="h-4 bg-gray-200 rounded w-3/4 mb-4"></div>
          <div class="space-y-3">
            <div class="h-3 bg-gray-200 rounded"></div>
            <div class="h-3 bg-gray-200 rounded w-5/6"></div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else-if="seedPlantings.length === 0" class="max-w-xl mx-auto text-center py-16 bg-white rounded-2xl shadow-sm border border-gray-100 border-dashed">
        <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-green-50">
          <BeakerIcon class="h-8 w-8 text-green-600" />
        </div>
        <h3 class="mt-4 text-lg font-medium text-gray-900">No active sowings</h3>
        <p class="mt-1 text-sm text-gray-500 max-w-sm mx-auto">
          Get started by recording your first batch of seeds in the nursery.
        </p>
        <div class="mt-6">
          <button
            @click="$router.push('/seed-plantings/create')"
            class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
          >
            <PlusIcon class="-ml-1 mr-2 h-5 w-5" aria-hidden="true" />
            Start Sowing
          </button>
        </div>
      </div>

      <!-- Content Grid -->
      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">

        <div
          v-for="planting in seedPlantings"
          :key="planting.id"
          class="bg-white rounded-2xl shadow-sm border border-gray-100 hover:shadow-md transition-shadow duration-200 overflow-hidden flex flex-col group"
        >
          <!-- Card Header & Status -->
          <div 
            class="p-5 border-b border-gray-50 bg-gray-50/50 group-hover:bg-gray-100/50 transition-colors cursor-pointer"
            @click="$router.push(`/seed-plantings/${planting.id}`)"
          >
            <div class="flex justify-between items-start">
              <div>
                <div class="flex items-center space-x-2">
                  <h3 class="text-lg font-bold text-gray-900 line-clamp-1 group-hover:text-green-700 transition-colors" :title="planting.rice_variety?.name">
                    {{ planting.rice_variety?.name }}
                  </h3>
                   <span v-if="planting.batch_id" class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200">
                    <TagIcon class="mr-1 h-3 w-3" />
                    {{ planting.batch_id }}
                  </span>
                </div>
                <!-- Status Badge -->
                 <div class="mt-2">
                    <span
                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                      :class="getStatusClass(planting.status)"
                    >
                      <span class="w-1.5 h-1.5 mr-1.5 rounded-full" :class="getStatusDotClass(planting.status)"></span>
                      {{ formatStatus(planting.status) }}
                    </span>
                 </div>
              </div>
              
              <!-- Menu/Actions Placeholder or simple delete -->
              <button @click.stop="confirmDelete(planting)" class="text-gray-400 hover:text-red-500 transition-colors p-1 rounded-full hover:bg-red-50">
                 <TrashIcon class="h-4 w-4" />
              </button>
            </div>
          </div>

          <!-- Card Body -->
          <div 
            class="p-5 flex-1 space-y-4 cursor-pointer"
            @click="$router.push(`/seed-plantings/${planting.id}`)"
          >
             <!-- Dates Row -->
             <div class="flex items-start justify-between text-sm">
                <div class="flex flex-col">
                   <span class="text-xs text-gray-500 mb-1 flex items-center">
                     <CalendarIcon class="w-3 h-3 mr-1" /> Sown
                   </span>
                   <span class="font-medium text-gray-700">{{ formatDate(planting.planting_date) }}</span>
                </div>
                <div class="flex flex-col text-right" v-if="planting.expected_transplant_date">
                   <span class="text-xs text-gray-500 mb-1 flex items-center justify-end">
                      <ClockIcon class="w-3 h-3 mr-1" /> Est. Transplant
                   </span>
                   <span class="font-medium text-gray-700">{{ formatDate(planting.expected_transplant_date) }}</span>
                </div>
             </div>

             <!-- Quantity Row -->
             <div>
                <span class="text-xs text-gray-500 mb-1 flex items-center">
                  <ScaleIcon class="w-3 h-3 mr-1" /> Quantity
                </span>
                <div class="text-sm font-medium text-gray-900 bg-gray-50 rounded-lg py-2 px-3 border border-gray-100">
                   {{ planting.quantity }} <span class="text-gray-500">{{ planting.unit }}</span>
                </div>
             </div>
          </div>

          <!-- Card Footer (Actions) -->
          <div class="p-4 bg-gray-50 border-t border-gray-100 flex justify-end" v-if="planting.status === 'sown' || planting.status === 'germinating'">
             <button
               @click="confirmUpdateStatus(planting, 'ready', $event)"
               class="w-full inline-flex justify-center items-center px-4 py-2 border border-blue-200 shadow-sm text-sm font-medium rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
             >
               <CheckCircleIcon class="-ml-1 mr-2 h-4 w-4" />
               Mark as Ready to Transplant
             </button>
          </div>
          <div class="p-4 bg-green-50 border-t border-green-100 flex justify-center items-center text-green-700 text-sm font-medium space-x-2" v-else-if="planting.status === 'ready'">
              <CheckCircleIcon class="h-5 w-5" />
              <span>Ready for Field</span>
          </div>
        </div>
      </div>
    </div>
      <!-- Confirmation Modal -->
    <Teleport to="body">
      <ConfirmationModal
        :show="showConfirmModal"
        :title="modalConfig.title"
        :message="modalConfig.message"
        :confirm-text="modalConfig.confirmText"
        :type="modalConfig.type"
        @close="showConfirmModal = false"
        @confirm="handleConfirm"
      />
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { 
  PlusIcon, 
  BeakerIcon, 
  TrashIcon, 
  TagIcon, 
  CalendarIcon, 
  ClockIcon, 
  ScaleIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline';
import ConfirmationModal from '@/Components/UI/ConfirmationModal.vue';

const seedPlantings = ref([]);
const loading = ref(true);

const showConfirmModal = ref(false)
const pendingAction = ref(null)
const modalConfig = ref({
  title: '',
  message: '',
  confirmText: 'Confirm',
  type: 'danger'
})

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

const confirmUpdateStatus = (planting, status, event) => {
  if (event) event.stopPropagation();
  pendingAction.value = { type: 'update', planting, status }
  modalConfig.value = {
    title: 'Update Status',
    message: `Mark this planting as ${status}?`,
    confirmText: 'Update',
    type: 'primary' // or 'info' depending on your modal styles, but primary usually implies safety or normal action
  }
  showConfirmModal.value = true
}

const confirmDelete = (planting) => {
  pendingAction.value = { type: 'delete', planting }
  modalConfig.value = {
    title: 'Delete Sowing',
    message: 'Are you sure you want to delete this record?',
    confirmText: 'Delete',
    type: 'danger'
  }
  showConfirmModal.value = true
}

const handleConfirm = async () => {
  showConfirmModal.value = false
  const action = pendingAction.value
  if (!action) return

  try {
    if (action.type === 'update') {
      await axios.put(`/api/seed-plantings/${action.planting.id}`, { status: action.status });
      await fetchSeedPlantings();
    } else if (action.type === 'delete') {
      await axios.delete(`/api/seed-plantings/${action.planting.id}`);
      await fetchSeedPlantings();
    }
  } catch (error) {
    console.error(`Error performing ${action.type}:`, error);
    alert(`Failed to ${action.type === 'delete' ? 'delete record' : 'update status'}`);
  } finally {
    pendingAction.value = null
  }
}

const getStatusClass = (status) => {
  const classes = {
    sown: 'bg-gray-100 text-gray-800 border-gray-200',
    germinating: 'bg-yellow-50 text-yellow-700 border-yellow-200',
    ready: 'bg-green-50 text-green-700 border-green-200',
    transplanted: 'bg-blue-50 text-blue-700 border-blue-200',
    failed: 'bg-red-50 text-red-700 border-red-200',
  };
  return classes[status] || 'bg-gray-100 text-gray-800 border-gray-200';
};

const getStatusDotClass = (status) => {
   const classes = {
    sown: 'bg-gray-400',
    germinating: 'bg-yellow-400',
    ready: 'bg-green-400',
    transplanted: 'bg-blue-400',
    failed: 'bg-red-400',
  };
  return classes[status] || 'bg-gray-400';
}

const formatStatus = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1);
};

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

onMounted(() => {
  fetchSeedPlantings();
});
</script>
