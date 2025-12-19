<template>
  <form @submit.prevent="submitForm" class="space-y-6 w-full">
    <div v-if="form.errors.general" class="p-4 bg-red-50 border border-red-300 text-red-800 rounded-md">
      <h3 class="font-medium">An error occurred:</h3>
      <p>{{ form.errors.general }}</p>
    </div>

    <div class="bg-white shadow-lg rounded-xl border border-gray-100">
      <div class="px-6 py-6 sm:px-8 sm:py-8">
        <h3 class="text-xl font-semibold text-gray-900">Planting Details</h3>
        <p class="mt-1 text-sm text-gray-600">
          Provide the core details about this planting cycle.
        </p>

        <div class="mt-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          <div>
            <label for="field_id" class="block text-sm font-semibold text-gray-700 mb-2">Field</label>
            <select
              id="field_id"
              v-model="form.data.field_id"
              required
              class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              :class="{ 'border-red-500': form.errors.field_id }"
            >
              <option value="" disabled>Select a field</option>
              <option v-for="field in farmStore.fields" :key="field.id" :value="field.id">
                {{ field.name }} ({{ field.size }} ha)
              </option>
            </select>
            <p v-if="form.errors.field_id" class="mt-1 text-xs text-red-600">{{ form.errors.field_id }}</p>
          </div>

          <div>
            <label for="crop_type" class="block text-sm font-semibold text-gray-700 mb-2">Crop Name</label>
            <input
              type="text"
              id="crop_type"
              v-model="form.data.crop_type"
              placeholder="e.g., Rice"
              class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              :class="{ 'border-red-500': form.errors.crop_type }"
            />
            <p v-if="form.errors.crop_type" class="mt-1 text-xs text-red-600">{{ form.errors.crop_type }}</p>
          </div>
          
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Planting Source</label>
            <div class="flex gap-4 mb-2">
              <label class="inline-flex items-center">
                <input type="radio" v-model="sourceType" value="direct" class="form-radio text-green-600">
                <span class="ml-2 text-sm text-gray-700">Direct / Inventory</span>
              </label>
              <label class="inline-flex items-center">
                <input type="radio" v-model="sourceType" value="nursery" class="form-radio text-green-600">
                <span class="ml-2 text-sm text-gray-700">Nursery (Transplant)</span>
              </label>
            </div>
          </div>

          <div v-if="sourceType === 'direct'">
            <label for="inventory_item_id" class="block text-sm font-semibold text-gray-700 mb-2">Seed Source (Inventory)</label>
            <select
              id="inventory_item_id"
              v-model="form.data.inventory_item_id"
              class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              :class="{ 'border-red-500': form.errors.inventory_item_id }"
            >
              <option value="">Select from Inventory</option>
              <option
                v-for="item in inventoryStore.riceSeeds"
                :key="item.id"
                :value="item.id"
                :disabled="item.current_stock <= 0"
              >
                {{ item.name }} (Available: {{ item.current_stock }} {{ item.unit || 'kg' }})
              </option>
            </select>
            <p v-if="form.errors.inventory_item_id" class="mt-1 text-xs text-red-600">{{ form.errors.inventory_item_id }}</p>
            <div v-if="selectedRiceVariety" class="mt-2 p-2 bg-green-50 rounded text-xs text-green-700 flex items-center">
              <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
              Linked to Variety: <strong>{{ selectedRiceVariety.name }}</strong>
            </div>
          </div>

          <div v-else>
            <label for="seed_planting_id" class="block text-sm font-semibold text-gray-700 mb-2">Nursery Source</label>
            <select
              id="seed_planting_id"
              v-model="form.data.seed_planting_id"
              class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              :class="{ 'border-red-500': form.errors.seed_planting_id }"
            >
              <option value="">Select Ready Seedlings</option>
              <option
                v-for="planting in readySeedPlantings"
                :key="planting.id"
                :value="planting.id"
              >
                {{ planting.rice_variety?.name }} - Sown: {{ formatDate(planting.planting_date) }} ({{ planting.quantity }} {{ planting.unit }})
              </option>
            </select>
            <p v-if="readySeedPlantings.length === 0" class="mt-1 text-xs text-amber-600">No ready seedlings found in nursery.</p>
          </div>

          <div>
            <label for="rice_variety_id" class="block text-sm font-semibold text-gray-700 mb-2">
              Rice Variety <span class="text-xs font-normal text-gray-500">(Auto-matched)</span>
            </label>
            <select
              id="rice_variety_id"
              v-model="form.data.rice_variety_id"
              class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              :class="{ 'border-red-500': form.errors.rice_variety_id }"
            >
              <option value="">Select rice variety</option>
              <option
                v-for="variety in riceVarieties"
                :key="variety.id"
                :value="variety.id"
              >
                {{ variety.name }}
              </option>
            </select>
            <p v-if="form.errors.rice_variety_id" class="mt-1 text-xs text-red-600">{{ form.errors.rice_variety_id }}</p>
            <p v-if="selectedRiceVariety" class="mt-1 text-xs text-gray-500">
               Maturity: {{ selectedRiceVariety.maturity_days }} days
            </p>
          </div>

          <div>
            <label for="season" class="block text-sm font-semibold text-gray-700 mb-2">Season</label>
            <select
              id="season"
              v-model="form.data.season"
              class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              :class="{ 'border-red-500': form.errors.season }"
            >
              <option value="wet">Rainy Season (Jun - Nov)</option>
              <option value="dry">Dry Season (Dec - May)</option>
            </select>
            <p v-if="form.errors.season" class="mt-1 text-xs text-red-600">{{ form.errors.season }}</p>
            <p class="mt-1 text-xs text-gray-500">
              Based on Philippines climate. Auto-detected from planting date.
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white shadow-lg rounded-xl border border-gray-100">
      <div class="px-6 py-6 sm:px-8 sm:py-8">
        <h3 class="text-xl font-semibold text-gray-900">Schedule & Status</h3>
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-6">
          <div>
            <label for="planting_date" class="block text-sm font-semibold text-gray-700 mb-2">Planting Date</label>
            <input
              type="date"
              id="planting_date"
              v-model="form.data.planting_date"
              required
              class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              :class="{ 'border-red-500': form.errors.planting_date }"
            />
            <p v-if="form.errors.planting_date" class="mt-1 text-xs text-red-600">{{ form.errors.planting_date }}</p>
          </div>
          
          <div>
            <label for="expected_harvest_date" class="block text-sm font-semibold text-gray-700 mb-2">
              Expected Harvest Date
              <span v-if="isAutoCalculated" class="text-xs font-normal text-gray-500 ml-2">(Auto-calculated)</span>
            </label>
            <input
              type="date"
              id="expected_harvest_date"
              v-model="form.data.expected_harvest_date"
              :min="minHarvestDate"
              class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              :class="{ 'border-red-500': form.errors.expected_harvest_date, 'bg-gray-50': isAutoCalculated }"
              @input="onHarvestDateManualChange"
            />
            <p v-if="form.errors.expected_harvest_date" class="mt-1 text-xs text-red-600">{{ form.errors.expected_harvest_date }}</p>
            <p v-if="isAutoCalculated && !form.errors.expected_harvest_date" class="mt-1 text-xs text-gray-500">
              Calculated from planting date + {{ selectedRiceVariety?.maturity_days || 'variety' }} days maturity
            </p>
            <p v-else-if="!isAutoCalculated && form.data.planting_date && form.data.expected_harvest_date" class="mt-1 text-xs text-gray-500">
              You can manually adjust this date if needed
            </p>
          </div>

          <div>
            <label for="status" class="block text-sm font-semibold text-gray-700 mb-2">Status</label>
            <select
              id="status"
              v-model="form.data.status"
              class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              :class="{ 'border-red-500': form.errors.status }"
            >
              <!-- When creating new planting -->
              <template v-if="!isEditMode">
                <option value="planned">Planned (Future planting)</option>
                <option value="planted">Planted</option>
              </template>
              
              <!-- When editing existing planting -->
              <template v-else>
                <option value="planned">Planned</option>
                <option value="planted">Planted</option>
                <option value="growing">Growing</option>
                <option value="ready">Ready for Harvest</option>
                <option value="harvested">Harvested</option>
                <option value="failed">Failed</option>
              </template>
            </select>
            <p v-if="form.errors.status" class="mt-1 text-xs text-red-600">{{ form.errors.status }}</p>
          </div>
        </div>
      </div>
    </div>
    
    <div class="bg-white shadow-lg rounded-xl border border-gray-100">
      <div class="px-6 py-6 sm:px-8 sm:py-8">
        <h3 class="text-xl font-semibold text-gray-900">Method & Quantity</h3>
        <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-6">
          <div>
            <label for="planting_method" class="block text-sm font-semibold text-gray-700 mb-2">Planting Method</label>
            <select
              id="planting_method"
              v-model="form.data.planting_method"
              class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              :class="{ 'border-red-500': form.errors.planting_method }"
            >
              <option value="transplanting">Transplanting</option>
              <option value="direct_seeding">Direct Seeding</option>
              <option value="broadcasting">Broadcasting</option>
            </select>
            <p v-if="form.errors.planting_method" class="mt-1 text-xs text-red-600">{{ form.errors.planting_method }}</p>
          </div>
          
          <div>
            <label for="area_planted" class="block text-sm font-semibold text-gray-700 mb-2">
              Area Planted (ha)
              <span v-if="selectedField" class="text-xs font-normal text-gray-500 ml-2">
                (Max: {{ selectedField.size }} ha)
              </span>
            </label>
            <input
              type="number"
              step="0.01"
              min="0"
              :max="maxAreaPlanted"
              id="area_planted"
              v-model.number="form.data.area_planted"
              class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              :class="{ 'border-red-500': form.errors.area_planted || areaExceedsField }"
              @input="validateAreaPlanted"
            />
            <p v-if="form.errors.area_planted" class="mt-1 text-xs text-red-600">{{ form.errors.area_planted }}</p>
            <p v-else-if="areaExceedsField" class="mt-1 text-xs text-red-600">
              Area planted ({{ form.data.area_planted || 0 }} ha) cannot exceed field size ({{ selectedField?.size || 0 }} ha)
            </p>
            <p v-else-if="selectedField && form.data.area_planted" class="mt-1 text-xs text-gray-500">
              Field size: {{ selectedField.size }} ha
            </p>
          </div>

          <div>
            <label for="seed_rate" class="block text-sm font-semibold text-gray-700 mb-2">Seed Quantity (kg)</label>
            <input
              type="number"
              step="1"
              min="0"
              id="seed_rate"
              v-model.number="form.data.seed_rate"
              class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              :class="{ 'border-red-500': form.errors.seed_rate }"
            />
            <p v-if="form.errors.seed_rate" class="mt-1 text-xs text-red-600">{{ form.errors.seed_rate }}</p>
            <p v-if="selectedInventoryItem" class="mt-1 text-xs text-gray-500">
              Stock Available: {{ selectedInventoryItem.current_stock }} {{ selectedInventoryItem.unit || 'kg' }}
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="bg-white shadow-lg rounded-xl border border-gray-100">
      <div class="px-6 py-6 sm:px-8 sm:py-8">
        <h3 class="text-xl font-semibold text-gray-900">Notes</h3>
        <div class="mt-4">
          <textarea
            id="notes"
            v-model="form.data.notes"
            rows="4"
            class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition resize-none"
            :class="{ 'border-red-500': form.errors.notes }"
            placeholder="Any additional notes about this planting..."
          ></textarea>
          <p v-if="form.errors.notes" class="mt-1 text-xs text-red-600">{{ form.errors.notes }}</p>
        </div>
      </div>
    </div>
    
    <div class="flex justify-end gap-3 pt-6 border-t border-gray-200 bg-white rounded-xl px-6 py-4">
      <button
        type="button"
        @click="cancelForm"
        class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-50"
      >
        Cancel
      </button>
      <button
        type="submit"
        :disabled="form.processing"
        class="inline-flex items-center justify-center px-4 py-2 text-sm font-medium rounded-md bg-green-600 text-white hover:bg-green-700 disabled:opacity-50"
      >
        <LoadingSpinner v-if="form.processing" class="mr-2" />
        {{ isEditMode ? 'Save Changes' : 'Create Planting' }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, watch, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useFarmStore } from '@/stores/farm'
import { useMarketplaceStore } from '@/stores/marketplace'
import { useInventoryStore } from '@/stores/inventory'
import LoadingSpinner from '@/Components/UI/LoadingSpinner.vue'
import axios from 'axios';

const formatDate = (dateString) => {
  if (!dateString) return '';
  return new Date(dateString).toLocaleDateString();
};

const props = defineProps({
  planting: {
    type: Object,
    default: null,
  },
})

const router = useRouter()
const farmStore = useFarmStore()

const marketplaceStore = useMarketplaceStore()
const inventoryStore = useInventoryStore()

const isEditMode = computed(() => !!props.planting)
const riceVarieties = computed(() => marketplaceStore.riceVarieties || [])

// Get selected rice variety
const selectedRiceVariety = computed(() => {
  if (!form.value.data.rice_variety_id) return null
  return riceVarieties.value.find(v => v.id == form.value.data.rice_variety_id)
})

// Get selected field
const selectedField = computed(() => {
  if (!form.value.data.field_id) return null
  return farmStore.fields.find(f => f.id == form.value.data.field_id)
})

// Get max area that can be planted (field size)
const maxAreaPlanted = computed(() => {
  return selectedField.value?.size || null
})

// Check if area planted exceeds field size
const areaExceedsField = computed(() => {
  if (!selectedField.value || !form.value.data.area_planted) return false
  return Number(form.value.data.area_planted) > Number(selectedField.value.size)
})

// Validate area planted
const validateAreaPlanted = () => {
  if (areaExceedsField.value) {
    // Clear any existing errors first
    if (form.value.errors.area_planted) {
      delete form.value.errors.area_planted
    }
  }
}



// Track if harvest date was manually changed
const harvestDateManuallyChanged = ref(false)

// Check if harvest date is auto-calculated
const isAutoCalculated = computed(() => {
  return !harvestDateManuallyChanged.value && 
         form.value.data.planting_date && 
         form.value.data.rice_variety_id &&
         selectedRiceVariety.value?.maturity_days
})

// Minimum harvest date (must be after planting date)
const minHarvestDate = computed(() => {
  if (!form.value.data.planting_date) return ''
  const plantingDate = new Date(form.value.data.planting_date)
  plantingDate.setDate(plantingDate.getDate() + 1) // At least 1 day after planting
  return plantingDate.toISOString().split('T')[0]
})

// Helper to format dates for <input type="date">
const formatDateForInput = (dateString) => {
  if (!dateString) return ''
  try {
    const date = new Date(dateString)
    return date.toISOString().split('T')[0]
  } catch (e) {
    return ''
  }
}

// Helper function to determine season based on month (Philippines)
const getSeasonFromDate = (dateString) => {
  if (!dateString) return 'wet'; // Default to rainy season
  
  try {
    const date = new Date(dateString);
    const month = date.getMonth() + 1; // JavaScript months are 0-indexed
    
    // Philippines seasons according to PAGASA:
    // Rainy season: June (6) to November (11)
    // Dry season: December (12) to May (5)
    if (month >= 6 && month <= 11) {
      return 'wet'; // Rainy season
    } else {
      return 'dry'; // Dry season
    }
  } catch (e) {
    return 'wet'; // Default fallback
  }
}

const getInitialFormData = () => {
  // Auto-set status based on planting date for new plantings
  let defaultStatus = 'planted';
  
  // Get planting date (either from existing planting or will be set later)
  const plantingDateStr = formatDateForInput(props.planting?.planting_date);
  
  // For new plantings, we'll default to 'planted' and let the watcher adjust it
  // For existing plantings, use their current status
  if (props.planting) {
    defaultStatus = props.planting.status || 'planted';
  }
  
  // Auto-detect season from planting date (Philippines)
  let defaultSeason = 'wet';
  if (props.planting?.season) {
    defaultSeason = props.planting.season;
  } else if (plantingDateStr) {
    defaultSeason = getSeasonFromDate(plantingDateStr);
  } else {
    // If no planting date, use current month to suggest season
    defaultSeason = getSeasonFromDate(new Date().toISOString());
  }
  
  return {
    field_id: props.planting?.field_id || '',
    rice_variety_id: props.planting?.rice_variety_id || '',
    crop_type: props.planting?.crop_type || 'Rice',
    planting_date: plantingDateStr,
    expected_harvest_date: formatDateForInput(props.planting?.expected_harvest_date) || null,
    planting_method: props.planting?.planting_method || 'transplanting',
    seed_rate: props.planting?.seed_rate || null,
    area_planted: props.planting?.area_planted || null,
    season: defaultSeason,
    status: defaultStatus,
    notes: props.planting?.notes || null,
    inventory_item_id: '', // Add inventory item tracking
    seed_planting_id: '', // Add seed planting source
  }
}

const form = ref({
  data: getInitialFormData(),
  errors: {},
  processing: false,
})

// Get selected inventory item
const selectedInventoryItem = computed(() => {
  if (!form.value.data.inventory_item_id) return null;
  return inventoryStore.riceSeeds.find(i => i.id == form.value.data.inventory_item_id);
});

// Source Type state
const sourceType = ref('direct');
const readySeedPlantings = ref([]);

// Fetch ready seed plantings
const fetchReadySeedPlantings = async () => {
  try {
    const response = await axios.get('/api/seed-plantings/ready');
    readySeedPlantings.value = response.data;
  } catch (error) {
    console.error('Error fetching ready seed plantings:', error);
  }
};

onMounted(() => {
  fetchReadySeedPlantings();
});

// Watch for seed_planting_id selection to autofill
watch(() => form.value.data.seed_planting_id, (newId) => {
  if (!newId) return;
  
  const seedPlanting = readySeedPlantings.value.find(p => p.id == newId);
  if (seedPlanting) {
    form.value.data.rice_variety_id = seedPlanting.rice_variety_id;
    form.value.data.planting_method = 'transplanting';
    // You might also want to set quantity/seed_rate based on the seed planting quantity
    // but usually field planting seed_rate is per hectare, while seed planting quantity is total.
  }
});

// Watch inventory item selection (existing logic)
watch(() => form.value.data.inventory_item_id, (newId) => {
  if (!newId) {
    if (sourceType.value === 'direct') {
       form.value.data.rice_variety_id = '';
    }
    return;
  }
  
  const item = inventoryStore.riceSeeds.find(i => i.id == newId);
  if (item && item.name) {
    // Attempt to fuzzy match the inventory item name with rice varieties
    // E.g. "IR64 Seeds" -> matches "IR64"
    const match = riceVarieties.value.find(v => 
      item.name.toLowerCase().includes(v.name.toLowerCase()) || 
      v.name.toLowerCase().includes(item.name.toLowerCase())
    );
    
    if (match) {
      form.value.data.rice_variety_id = match.id;
    }
  }
});

// If the planting prop changes (e.g., in edit mode), reset the form
watch(() => props.planting, () => {
  form.value.data = getInitialFormData()
  form.value.errors = {}
  harvestDateManuallyChanged.value = false // Reset manual change flag
})

// Calculate expected harvest date when planting date or rice variety changes
const calculateExpectedHarvestDate = () => {
  if (!harvestDateManuallyChanged.value && 
      form.value.data.planting_date && 
      selectedRiceVariety.value?.maturity_days) {
    const plantingDate = new Date(form.value.data.planting_date)
    const harvestDate = new Date(plantingDate)
    const maturityDays = Number(selectedRiceVariety.value.maturity_days) || 0
    harvestDate.setDate(harvestDate.getDate() + maturityDays)
    form.value.data.expected_harvest_date = formatDateForInput(harvestDate.toISOString())
  }
}

// Watch for planting date changes
watch(() => form.value.data.planting_date, (newDate) => {
  if (newDate) {
    // Auto-update status based on date
    if (!isEditMode.value) {
      const plantingDate = new Date(newDate);
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      plantingDate.setHours(0, 0, 0, 0);
      
      // If planting date is in the future, suggest 'planned'
      // If today or past, suggest 'planted'
      if (plantingDate > today && form.value.data.status === 'planted') {
        form.value.data.status = 'planned';
      } else if (plantingDate <= today && form.value.data.status === 'planned') {
        form.value.data.status = 'planted';
      }
    }
    
    // Auto-update season based on planting date month (Philippines)
    const detectedSeason = getSeasonFromDate(newDate);
    if (detectedSeason !== form.value.data.season) {
      form.value.data.season = detectedSeason;
    }
    
    // Auto-calculate harvest date if not manually changed
    calculateExpectedHarvestDate()
  }
})

// Watch for rice variety changes
watch(() => form.value.data.rice_variety_id, () => {
  // Auto-calculate harvest date if not manually changed
  calculateExpectedHarvestDate()
})

// Handle manual changes to harvest date
const onHarvestDateManualChange = () => {
  harvestDateManuallyChanged.value = true
}

// Fetch fields and rice varieties if not already in store
onMounted(async () => {
  try {
    if (farmStore.fields.length === 0) {
      await farmStore.fetchFields()
    }
    if (marketplaceStore.riceVarieties.length === 0) {
      await marketplaceStore.fetchRiceVarieties()
    }
    // Fetch inventory items
    if (inventoryStore.items.length === 0) {
      await inventoryStore.fetchItems();
    }
    
    // If in edit mode and harvest date exists, mark as manually changed to preserve it
    if (isEditMode.value && form.value.data.expected_harvest_date) {
      harvestDateManuallyChanged.value = true
    }
    
    // For new plantings, try to auto-calculate if we have the data
    if (!isEditMode.value && form.value.data.planting_date && form.value.data.rice_variety_id) {
      calculateExpectedHarvestDate()
    }
  } catch (err) {
    form.value.errors.general = "Could not load required data. Please refresh."
    console.error('Error loading form data:', err)
  }
})

const submitForm = async () => {
  form.value.processing = true
  form.value.errors = {}

  // Validate area planted doesn't exceed field size
  if (form.value.data.area_planted && selectedField.value) {
    const areaPlanted = Number(form.value.data.area_planted)
    const fieldSize = Number(selectedField.value.size)
    
    if (areaPlanted > fieldSize) {
      form.value.errors.area_planted = `Area planted (${areaPlanted} ha) cannot exceed field size (${fieldSize} ha)`
      form.value.processing = false
      return
    }
  }

  // Validate expected harvest date is after planting date
  if (form.value.data.planting_date && form.value.data.expected_harvest_date) {
    const plantingDate = new Date(form.value.data.planting_date)
    const harvestDate = new Date(form.value.data.expected_harvest_date)
    
    if (harvestDate <= plantingDate) {
      form.value.errors.expected_harvest_date = 'Expected harvest date must be after the planting date'
      form.value.processing = false
      return
    }
  }

  // Auto-calculate harvest date if not set and we have the required data
  if (!form.value.data.expected_harvest_date && 
      form.value.data.planting_date && 
      selectedRiceVariety.value?.maturity_days) {
    calculateExpectedHarvestDate()
  }

  // Validate seed quantity against inventory stock
  if (form.value.data.inventory_item_id && form.value.data.seed_rate) {
    const item = inventoryStore.riceSeeds.find(i => i.id == form.value.data.inventory_item_id);
    if (item) {
      const quantityNeeded = Number(form.value.data.seed_rate);
      if (quantityNeeded > item.current_stock) {
        form.value.errors.seed_rate = `Insufficient stock. You only have ${item.current_stock} ${item.unit || 'kg'} available.`;
        form.value.processing = false;
        return;
      }
    }
  }

  // --- DATA CLEANING STEP ---
  // Create a copy of the data to send
  const payload = { ...form.value.data };
  
  // Convert any remaining empty strings "" to null
  // This is crucial for 'nullable' rules in Laravel
  for (const key in payload) {
    if (payload[key] === '') {
      payload[key] = null;
    }
  }
  
  // Convert rice_variety_id to number if it exists, otherwise set to null
  if (payload.rice_variety_id && payload.rice_variety_id !== '') {
    payload.rice_variety_id = Number(payload.rice_variety_id);
  } else {
    payload.rice_variety_id = null;
  }
  // --- END CLEANING STEP ---

  try {
    if (isEditMode.value) {
      // Send the cleaned payload
      await farmStore.updatePlanting(props.planting.id, payload)
    } else {
      // Send the cleaned payload
      await farmStore.createPlanting(payload)
      // Refresh fields so current_crop updates in the fields view
      await farmStore.fetchFields()
    }
    // Success - navigate back to the index page
    router.push('/plantings')
  } catch (err) {
    if (err.response && err.response.status === 422) {
      form.value.errors = err.response.data.errors || {}
      form.value.errors.general = err.response.data.message || 'Validation failed. Please check the fields.'
    } else {
      form.value.errors.general = 'An unexpected error occurred. Please try again.'
    }
    console.error('Form submission error:', err)
  } finally {
    form.value.processing = false
  }
}

const cancelForm = () => {
  router.back() // Go back to the previous page
}
</script>