<template>
  <div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      
      <!-- Back Button -->
      <div class="mb-6">
        <button 
          @click="$router.back()" 
          class="flex items-center text-sm text-gray-500 hover:text-gray-700 transition-colors"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-1" />
          Back to Nursery
        </button>
      </div>

      <div v-if="loading" class="animate-pulse space-y-4">
        <div class="h-8 bg-gray-200 rounded w-1/3"></div>
        <div class="h-64 bg-white rounded-2xl shadow-sm"></div>
      </div>

      <div v-else-if="planting" class="space-y-6">
        <!-- Header Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
          <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-4">
            <div>
              <div class="flex items-center space-x-3 mb-2">
                <h1 class="text-2xl font-bold text-gray-900">{{ planting.rice_variety?.name }}</h1>
                <span 
                  v-if="planting.batch_id" 
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 border border-gray-200"
                >
                  <TagIcon class="mr-1 h-3 w-3" />
                  {{ planting.batch_id }}
                </span>
              </div>
              <div class="flex items-center space-x-2">
                <span 
                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-sm font-medium"
                  :class="getStatusClass(planting.status)"
                >
                  <span class="w-1.5 h-1.5 mr-1.5 rounded-full" :class="getStatusDotClass(planting.status)"></span>
                  {{ formatStatus(planting.status) }}
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap gap-2">
              <button
                v-if="planting.status === 'sown' || planting.status === 'germinating'"
                @click="confirmUpdateStatus('ready')"
                class="inline-flex items-center px-4 py-2 border border-blue-200 shadow-sm text-sm font-medium rounded-lg text-blue-700 bg-blue-50 hover:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors"
              >
                <CheckCircleIcon class="-ml-1 mr-2 h-4 w-4" />
                Mark Ready
              </button>
              
              <!-- Placeholder for Edit (future) -->
              <!-- <button class="p-2 text-gray-400 hover:text-gray-500 rounded-lg hover:bg-gray-50">
                <PencilIcon class="h-5 w-5" />
              </button> -->
              
              <button 
                @click="confirmDelete"
                class="inline-flex items-center px-4 py-2 border border-red-200 shadow-sm text-sm font-medium rounded-lg text-red-700 bg-red-50 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 transition-colors"
              >
                <TrashIcon class="-ml-1 mr-2 h-4 w-4" />
                Delete
              </button>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Main Details -->
          <div class="md:col-span-2 space-y-6">
            <!-- Timeline Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
              <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                <CalendarDaysIcon class="h-5 w-5 mr-2 text-green-500" />
                Timeline
              </h3>
              <div class="space-y-6 relative pl-2">
                <!-- Vertical Line -->
                <div class="absolute left-2.5 top-2 bottom-2 w-0.5 bg-gray-100"></div>

                <!-- Sown Date -->
                <div class="relative flex items-start space-x-4">
                  <div class="relative z-10 w-5 h-5 rounded-full bg-green-100 border-2 border-white flex items-center justify-center shrink-0">
                    <div class="w-2 h-2 rounded-full bg-green-500"></div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Date Sown</label>
                    <p class="mt-1 text-base text-gray-900 font-medium">{{ formatDate(planting.planting_date) }}</p>
                  </div>
                </div>

                <!-- Est Transplant Date -->
                <div class="relative flex items-start space-x-4" v-if="planting.expected_transplant_date">
                  <div class="relative z-10 w-5 h-5 rounded-full bg-blue-100 border-2 border-white flex items-center justify-center shrink-0">
                     <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-500">Est. Transplant Date</label>
                    <p class="mt-1 text-base text-gray-900 font-medium">{{ formatDate(planting.expected_transplant_date) }}</p>
                    <p class="text-xs text-gray-500 mt-1">
                      {{ getDaysFromNow(planting.expected_transplant_date) }}
                    </p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Notes Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6" v-if="planting.notes">
               <h3 class="text-lg font-medium text-gray-900 mb-4 flex items-center">
                <DocumentTextIcon class="h-5 w-5 mr-2 text-gray-400" />
                Notes
              </h3>
              <p class="text-gray-600 whitespace-pre-line">{{ planting.notes }}</p>
            </div>
          </div>

          <!-- Sidebar Details -->
          <div class="space-y-6">
            <!-- Quantity Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
              <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-4">Inventory Info</h3>
              
              <div class="space-y-4">
                <div>
                  <label class="text-xs text-gray-500 block">Quantity</label>
                  <div class="mt-1 flex items-baseline">
                    <span class="text-2xl font-bold text-gray-900">{{ planting.quantity }}</span>
                    <span class="ml-1 text-gray-500">{{ planting.unit }}</span>
                  </div>
                </div>
                
                <div class="pt-4 border-t border-gray-100">
                  <label class="text-xs text-gray-500 block mb-1">Created At</label>
                  <span class="text-sm text-gray-700">{{ formatDateTime(planting.created_at) }}</span>
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
       <div v-else class="text-center py-12">
        <h3 class="mt-2 text-sm font-medium text-gray-900">Planting not found</h3>
        <button 
          @click="$router.push('/seed-plantings')"
          class="mt-3 text-green-600 hover:text-green-500 text-sm font-medium"
        >
          Go back to list
        </button>
      </div>

    </div>
    
     <!-- Confirmation Modal -->
    <ConfirmationModal
      :show="showConfirmModal"
      :title="modalConfig.title"
      :message="modalConfig.message"
      :confirm-text="modalConfig.confirmText"
      :type="modalConfig.type"
      @close="showConfirmModal = false"
      @confirm="handleConfirm"
    />
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import axios from 'axios';
import { 
  ArrowLeftIcon, 
  CalendarDaysIcon, 
  TagIcon, 
  CheckCircleIcon, 
  TrashIcon, 
  PencilIcon,
  DocumentTextIcon
} from '@heroicons/vue/24/outline';
import ConfirmationModal from '@/Components/UI/ConfirmationModal.vue';

const route = useRoute();
const router = useRouter();
const planting = ref(null);
const loading = ref(true);

const showConfirmModal = ref(false)
const pendingAction = ref(null)
const modalConfig = ref({
  title: '',
  message: '',
  confirmText: 'Confirm',
  type: 'danger'
})

const fetchPlanting = async () => {
  try {
    const response = await axios.get(`/api/seed-plantings/${route.params.id}`);
    planting.value = response.data;
  } catch (error) {
    console.error('Error fetching planting:', error);
  } finally {
    loading.value = false;
  }
};

const confirmUpdateStatus = (status) => {
  pendingAction.value = { type: 'update', status }
  modalConfig.value = {
    title: 'Update Status',
    message: `Mark this planting as ${status}?`,
    confirmText: 'Update',
    type: 'primary'
  }
  showConfirmModal.value = true
}

const confirmDelete = () => {
  pendingAction.value = { type: 'delete' }
  modalConfig.value = {
    title: 'Delete Sowing',
    message: 'Are you sure you want to delete this record? This cannot be undone.',
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
       await axios.put(`/api/seed-plantings/${planting.value.id}`, { status: action.status });
       await fetchPlanting();
    } else if (action.type === 'delete') {
      await axios.delete(`/api/seed-plantings/${planting.value.id}`);
      router.push('/seed-plantings');
    }
  } catch (error) {
    console.error(`Error performing ${action.type}:`, error);
    alert(`Failed to ${action.type === 'delete' ? 'delete record' : 'update status'}`);
  } finally {
    pendingAction.value = null
  }
}

// Helpers
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
  if (!status) return '';
  return status.charAt(0).toUpperCase() + status.slice(1);
};

const formatDate = (dateString) => {
  if (!dateString) return '-';
  const options = { year: 'numeric', month: 'long', day: 'numeric' };
  return new Date(dateString).toLocaleDateString(undefined, options);
};

const formatDateTime = (dateString) => {
   if (!dateString) return '-';
   return new Date(dateString).toLocaleString();
}

const getDaysFromNow = (dateString) => {
  const date = new Date(dateString);
  const now = new Date();
  const diffTime = date - now;
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  
  if (diffDays === 0) return 'Today';
  if (diffDays === 1) return 'Tomorrow';
  if (diffDays < 0) return `${Math.abs(diffDays)} days ago`;
  return `In ${diffDays} days`;
};

onMounted(() => {
  fetchPlanting();
});
</script>
