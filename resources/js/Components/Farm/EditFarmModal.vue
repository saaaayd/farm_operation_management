<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
      
      <!-- Background overlay -->
      <div 
        class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" 
        aria-hidden="true" 
        @click="$emit('close')"
      ></div>

      <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

      <!-- Modal panel -->
      <div class="relative z-10 inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-xl w-full">
        <!-- Header -->
        <div class="bg-gray-50 px-6 py-4 border-b border-gray-100 flex items-center justify-between">
          <div class="flex items-center">
            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-10 w-10 rounded-full bg-green-100 sm:mx-0 mr-3">
              <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
              </svg>
            </div>
            <h3 class="text-xl leading-6 font-semibold text-gray-900" id="modal-title">
              Edit Farm Details
            </h3>
          </div>
          <button @click="$emit('close')" class="text-gray-400 hover:text-gray-500">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="bg-white px-6 py-6 space-y-6 max-h-[70vh] overflow-y-auto">
            <!-- Farm Name -->
            <div>
              <label for="farm_name" class="block text-sm font-semibold text-gray-700 mb-2">Farm Name *</label>
              <input
                type="text"
                id="farm_name"
                v-model="form.farm_name"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition-all duration-200"
                placeholder="Enter farm name"
              />
            </div>

            <!-- Total Area -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
              <div>
                <label for="total_area" class="block text-sm font-semibold text-gray-700 mb-2">Total Farm Area (ha) *</label>
                <div class="relative rounded-md shadow-sm">
                  <input
                    type="number"
                    id="total_area"
                    v-model="form.total_area"
                    step="0.01"
                    min="0"
                    required
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition-all duration-200"
                    placeholder="0.00"
                  />
                  <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">ha</span>
                  </div>
                </div>
              </div>

              <!-- Rice Area -->
              <div>
                <label for="rice_area" class="block text-sm font-semibold text-gray-700 mb-2">Rice Area (ha) *</label>
                <div class="relative rounded-md shadow-sm">
                  <input
                    type="number"
                    id="rice_area"
                    v-model="form.rice_area"
                    step="0.01"
                    min="0"
                    :max="form.total_area"
                    required
                    class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition-all duration-200"
                    placeholder="0.00"
                  />
                   <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                    <span class="text-gray-500 sm:text-sm">ha</span>
                  </div>
                </div>
                <p v-if="parseFloat(form.rice_area) > parseFloat(form.total_area)" class="mt-1 text-xs text-red-600">
                  Cannot exceed total area.
                </p>
              </div>
            </div>

            <!-- Location -->
             <div>
              <label for="location" class="block text-sm font-semibold text-gray-700 mb-2">Location/Address *</label>
              <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                   <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                     <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                   </svg>
                </div>
                <input
                  type="text"
                  id="location"
                  v-model="form.location"
                  required
                  class="w-full rounded-lg border border-gray-300 pl-10 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition-all duration-200"
                  placeholder="e.g. Managok, Malaybalay City"
                />
              </div>
            </div>
            
             <!-- Description -->
             <div>
              <label for="farm_description" class="block text-sm font-semibold text-gray-700 mb-2">Description</label>
              <textarea
                id="farm_description"
                v-model="form.farm_description"
                rows="3"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition-all duration-200 resize-none"
                placeholder="Brief description of your farm..."
              ></textarea>
            </div>

            <!-- Experience -->
            <div>
              <label for="farming_experience" class="block text-sm font-semibold text-gray-700 mb-2">Years of Experience</label>
              <input
                type="number"
                id="farming_experience"
                v-model="form.farming_experience"
                min="0"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition-all duration-200"
                placeholder="e.g. 10"
              />
            </div>
            
            <!-- Error Display -->
            <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-3 text-sm text-red-600 flex items-center">
               <svg class="h-4 w-4 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
               </svg>
              {{ error }}
            </div>
        </div>

        <div class="bg-gray-50 px-6 py-4 flex flex-row-reverse gap-3 rounded-b-2xl border-t border-gray-100">
          <button
            type="button"
            @click="submit"
            :disabled="loading || (parseFloat(form.rice_area) > parseFloat(form.total_area))"
            class="inline-flex justify-center rounded-xl border border-transparent px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-green-600 text-base font-medium text-white shadow-lg hover:from-emerald-700 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-60 disabled:cursor-not-allowed transition-all"
          >
            {{ loading ? 'Saving...' : 'Save Changes' }}
          </button>
          <button
            type="button"
            @click="$emit('close')"
            class="inline-flex justify-center rounded-xl border border-gray-300 px-6 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 hover:text-gray-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-300 transition-all"
          >
            Cancel
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, watch } from 'vue';
import { useFarmStore } from '@/stores/farm';

const props = defineProps({
  show: Boolean,
  farm: Object,
});

const emit = defineEmits(['close', 'updated']);

const farmStore = useFarmStore();
const loading = ref(false);
const error = ref('');

const form = reactive({
  farm_name: '',
  total_area: '',
  rice_area: '',
  location: '',
  farm_description: '',
  farming_experience: '',
});

// Initialize form when farm prop changes or modal opens
// Initialize form when farm prop changes or modal opens
watch(() => props.farm, (newFarm) => {
  if (newFarm) {
    // Check for nested structure first (API response format)
    const farmData = newFarm.farm || newFarm;
    const userProfile = newFarm.user_profile || newFarm;

    form.farm_name = farmData.name || newFarm.farm_name || '';
    form.total_area = userProfile.total_area || newFarm.total_area || 0;
    form.rice_area = userProfile.rice_area || newFarm.rice_area || 0;
    form.location = farmData.location || userProfile.farm_location || newFarm.location || '';
    form.farm_description = farmData.description || newFarm.farm_description || '';
    form.farming_experience = userProfile.farming_experience || newFarm.farming_experience || '';
  }
}, { immediate: true, deep: true });

const submit = async () => {
  error.value = '';
  loading.value = true;

  try {
    const payload = {
      ...form,
    };
    
    await farmStore.updateFarmProfile(payload);
    emit('updated');
    emit('close');
  } catch (err) {
    console.error("Update error:", err);
    error.value = err.response?.data?.message || err.message || 'Failed to update farm details.';
  } finally {
    loading.value = false;
  }
};
</script>
