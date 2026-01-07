<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">Add Rice Product</h1>
            <p class="text-sm text-gray-500 mt-1">
              Publish a new product with quality details and pricing.
            </p>
          </div>
          <button
            @click="router.push('/marketplace/my-products')"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
          >
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Cancel
          </button>
        </div>
      </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <form @submit.prevent="submit" class="divide-y divide-gray-200">
          <!-- Form Alert -->
          <div class="p-6">
            <FormAlert
              :visible="!!formError.message"
              :message="formError.message"
              :fieldErrors="formError.fieldErrors"
            />
          </div>

          <!-- Basic Information Section -->
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
              Basic Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Product Name <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.name"
                  type="text"
                  required
                  :class="[
                    'w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 transition-colors',
                    formError.fieldErrors?.name || errors.name
                      ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                      : 'border-gray-300 focus:border-green-500 focus:ring-green-500'
                  ]"
                  placeholder="e.g. Premium Jasmine Rice"
                />
                <p v-if="formError.fieldErrors?.name || errors.name" class="mt-1.5 text-xs text-red-600">
                  {{ Array.isArray(formError.fieldErrors?.name || errors.name) 
                      ? (formError.fieldErrors?.name || errors.name)[0] 
                      : (formError.fieldErrors?.name || errors.name) }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Rice Variety <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.rice_variety_id"
                  required
                  :class="[
                    'w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 transition-colors bg-white',
                    formError.fieldErrors?.rice_variety_id || errors.rice_variety_id
                      ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                      : 'border-gray-300 focus:border-green-500 focus:ring-green-500'
                  ]"
                >
                  <option value="">Select variety</option>
                  <option
                    v-for="variety in riceVarieties"
                    :key="variety.id"
                    :value="variety.id"
                  >
                    {{ variety.name }}
                  </option>
                </select>
                <p v-if="formError.fieldErrors?.rice_variety_id || errors.rice_variety_id" class="mt-1.5 text-xs text-red-600">
                  {{ Array.isArray(formError.fieldErrors?.rice_variety_id || errors.rice_variety_id) 
                      ? (formError.fieldErrors?.rice_variety_id || errors.rice_variety_id)[0] 
                      : (formError.fieldErrors?.rice_variety_id || errors.rice_variety_id) }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Quality Grade <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.quality_grade"
                  required
                  :class="[
                    'w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 transition-colors bg-white',
                    formError.fieldErrors?.quality_grade || errors.quality_grade
                      ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                      : 'border-gray-300 focus:border-green-500 focus:ring-green-500'
                  ]"
                >
                  <option
                    v-for="(label, value) in qualityGrades"
                    :key="value"
                    :value="value"
                  >
                    {{ label }}
                  </option>
                </select>
                <p v-if="formError.fieldErrors?.quality_grade || errors.quality_grade" class="mt-1.5 text-xs text-red-600">
                  {{ Array.isArray(formError.fieldErrors?.quality_grade || errors.quality_grade) 
                      ? (formError.fieldErrors?.quality_grade || errors.quality_grade)[0] 
                      : (formError.fieldErrors?.quality_grade || errors.quality_grade) }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Linked Harvest
                </label>
                <select
                  v-model="form.harvest_id"
                  :class="[
                    'w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 transition-colors bg-white',
                    formError.fieldErrors?.harvest_id || errors.harvest_id
                      ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                      : 'border-gray-300 focus:border-green-500 focus:ring-green-500'
                  ]"
                >
                  <option value="">Select harvest (optional)</option>
                  <option
                    v-for="harvest in harvests"
                    :key="harvest.id"
                    :value="harvest.id"
                  >
                    {{ formatHarvestOption(harvest) }}
                  </option>
                </select>
                <p v-if="formError.fieldErrors?.harvest_id || errors.harvest_id" class="mt-1.5 text-xs text-red-600">
                  {{ Array.isArray(formError.fieldErrors?.harvest_id || errors.harvest_id) 
                      ? (formError.fieldErrors?.harvest_id || errors.harvest_id)[0] 
                      : (formError.fieldErrors?.harvest_id || errors.harvest_id) }}
                </p>
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Description <span class="text-red-500">*</span>
                </label>
                <textarea
                  v-model="form.description"
                  rows="4"
                  required
                  :class="[
                    'w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 transition-colors resize-none',
                    formError.fieldErrors?.description || errors.description
                      ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                      : 'border-gray-300 focus:border-green-500 focus:ring-green-500'
                  ]"
                  placeholder="Describe your rice product, including quality, origin, and any special characteristics..."
                ></textarea>
                <p v-if="formError.fieldErrors?.description || errors.description" class="mt-1.5 text-xs text-red-600">
                  {{ Array.isArray(formError.fieldErrors?.description || errors.description) 
                      ? (formError.fieldErrors?.description || errors.description)[0] 
                      : (formError.fieldErrors?.description || errors.description) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Product Images Section -->
          <div class="p-6 bg-amber-50/50">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-amber-200">
              Product Images
            </h2>
            <p class="text-sm text-gray-600 mb-4">Upload photos of your rice to showcase quality to buyers. (Maximum 5 images, 2MB each)</p>
            
            <!-- Upload Area -->
            <div
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="handleDrop"
              :class="[
                'border-2 border-dashed rounded-lg p-8 text-center transition-colors cursor-pointer',
                isDragging ? 'border-green-500 bg-green-50' : 'border-gray-300 hover:border-gray-400'
              ]"
              @click="$refs.fileInput.click()"
            >
              <input
                ref="fileInput"
                type="file"
                accept="image/jpeg,image/jpg,image/png,image/webp"
                multiple
                class="hidden"
                @change="handleFileSelect"
              />
              <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <p class="mt-2 text-sm text-gray-600">
                <span class="font-semibold text-green-600">Click to upload</span> or drag and drop
              </p>
              <p class="text-xs text-gray-500 mt-1">JPG, JPEG, PNG, WebP up to 2MB each</p>
            </div>

            <!-- Upload Progress -->
            <div v-if="uploadingImages" class="mt-4 flex items-center justify-center gap-2">
              <svg class="animate-spin h-5 w-5 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span class="text-sm text-gray-600">Uploading images...</span>
            </div>

            <!-- Image Previews -->
            <div v-if="uploadedImages.length > 0" class="mt-4 grid grid-cols-2 md:grid-cols-5 gap-4">
              <div
                v-for="(image, index) in uploadedImages"
                :key="index"
                class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200"
              >
                <img :src="image" alt="Product image" class="w-full h-full object-cover" />
                <button
                  type="button"
                  @click="removeImage(index)"
                  class="absolute top-1 right-1 p-1 bg-red-500 text-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity"
                >
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Image Upload Error -->
            <p v-if="imageError" class="mt-2 text-sm text-red-600">{{ imageError }}</p>
          </div>

          <!-- Pricing & Quantity Section -->
          <div class="p-6 bg-green-50/50">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-green-200">
              Pricing & Quantity
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Quantity Available <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.quantity_available"
                  type="number"
                  min="0"
                  step="0.01"
                  required
                  :class="[
                    'w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 transition-colors bg-white',
                    formError.fieldErrors?.quantity_available || errors.quantity_available
                      ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                      : 'border-green-200 focus:border-green-500 focus:ring-green-500'
                  ]"
                  placeholder="0.00"
                />
                <p v-if="formError.fieldErrors?.quantity_available || errors.quantity_available" class="mt-1.5 text-xs text-red-600">
                  {{ Array.isArray(formError.fieldErrors?.quantity_available || errors.quantity_available) 
                      ? (formError.fieldErrors?.quantity_available || errors.quantity_available)[0] 
                      : (formError.fieldErrors?.quantity_available || errors.quantity_available) }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Unit <span class="text-red-500">*</span>
                </label>
                <select
                  v-model="form.unit"
                  required
                  :class="[
                    'w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 transition-colors bg-white',
                    formError.fieldErrors?.unit || errors.unit
                      ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                      : 'border-green-200 focus:border-green-500 focus:ring-green-500'
                  ]"
                >
                  <option
                    v-for="unit in units"
                    :key="unit"
                    :value="unit"
                  >
                    {{ unit.charAt(0).toUpperCase() + unit.slice(1) }}
                  </option>
                </select>
                <p v-if="formError.fieldErrors?.unit || errors.unit" class="mt-1.5 text-xs text-red-600">
                  {{ Array.isArray(formError.fieldErrors?.unit || errors.unit) 
                      ? (formError.fieldErrors?.unit || errors.unit)[0] 
                      : (formError.fieldErrors?.unit || errors.unit) }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Price per Sack (₱) <span class="text-red-500">*</span>
                </label>
                <input
                  v-model="form.price_per_unit"
                  type="number"
                  min="0"
                  step="0.01"
                  required
                  :class="[
                    'w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 transition-colors bg-white',
                    formError.fieldErrors?.price_per_unit || errors.price_per_unit
                      ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                      : 'border-green-200 focus:border-green-500 focus:ring-green-500'
                  ]"
                  placeholder="0.00"
                />
                <p v-if="formError.fieldErrors?.price_per_unit || errors.price_per_unit" class="mt-1.5 text-xs text-red-600">
                  {{ Array.isArray(formError.fieldErrors?.price_per_unit || errors.price_per_unit) 
                      ? (formError.fieldErrors?.price_per_unit || errors.price_per_unit)[0] 
                      : (formError.fieldErrors?.price_per_unit || errors.price_per_unit) }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Minimum Order Quantity
                </label>
                <input
                  v-model="form.minimum_order_quantity"
                  type="number"
                  min="0"
                  step="0.1"
                  :class="[
                    'w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 transition-colors bg-white',
                    formError.fieldErrors?.minimum_order_quantity || errors.minimum_order_quantity
                      ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                      : 'border-green-200 focus:border-green-500 focus:ring-green-500'
                  ]"
                  placeholder="Optional"
                />
                <p v-if="formError.fieldErrors?.minimum_order_quantity || errors.minimum_order_quantity" class="mt-1.5 text-xs text-red-600">
                  {{ Array.isArray(formError.fieldErrors?.minimum_order_quantity || errors.minimum_order_quantity) 
                      ? (formError.fieldErrors?.minimum_order_quantity || errors.minimum_order_quantity)[0] 
                      : (formError.fieldErrors?.minimum_order_quantity || errors.minimum_order_quantity) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Quality Specifications Section -->
          <div class="p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
              Quality Specifications
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Processing Method
                </label>
                <select
                  v-model="form.processing_method"
                  :class="[
                    'w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 transition-colors bg-white',
                    formError.fieldErrors?.processing_method || errors.processing_method
                      ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                      : 'border-gray-300 focus:border-green-500 focus:ring-green-500'
                  ]"
                >
                  <option value="">Select method (optional)</option>
                  <option
                    v-for="(label, value) in processingMethods"
                    :key="value"
                    :value="value"
                  >
                    {{ label }}
                  </option>
                </select>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Moisture Content (%)
                </label>
                <input
                  v-model="form.moisture_content"
                  type="number"
                  min="5"
                  max="25"
                  step="0.1"
                  :class="[
                    'w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 transition-colors',
                    formError.fieldErrors?.moisture_content || errors.moisture_content
                      ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                      : 'border-gray-300 focus:border-green-500 focus:ring-green-500'
                  ]"
                  placeholder="5-25%"
                />
                <p v-if="formError.fieldErrors?.moisture_content || errors.moisture_content" class="mt-1.5 text-xs text-red-600">
                  {{ Array.isArray(formError.fieldErrors?.moisture_content || errors.moisture_content) 
                      ? (formError.fieldErrors?.moisture_content || errors.moisture_content)[0] 
                      : (formError.fieldErrors?.moisture_content || errors.moisture_content) }}
                </p>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Purity Percentage
                </label>
                <input
                  v-model="form.purity_percentage"
                  type="number"
                  min="50"
                  max="100"
                  step="0.1"
                  :class="[
                    'w-full px-4 py-2.5 border rounded-lg focus:outline-none focus:ring-2 transition-colors',
                    formError.fieldErrors?.purity_percentage || errors.purity_percentage
                      ? 'border-red-300 focus:border-red-500 focus:ring-red-500'
                      : 'border-gray-300 focus:border-green-500 focus:ring-green-500'
                  ]"
                  placeholder="50-100%"
                />
                <p v-if="formError.fieldErrors?.purity_percentage || errors.purity_percentage" class="mt-1.5 text-xs text-red-600">
                  {{ Array.isArray(formError.fieldErrors?.purity_percentage || errors.purity_percentage) 
                      ? (formError.fieldErrors?.purity_percentage || errors.purity_percentage)[0] 
                      : (formError.fieldErrors?.purity_percentage || errors.purity_percentage) }}
                </p>
              </div>

              <div class="flex items-center pt-7">
                <input
                  id="is_organic"
                  v-model="form.is_organic"
                  type="checkbox"
                  class="h-5 w-5 text-green-600 border-gray-300 rounded focus:ring-green-500"
                />
                <label for="is_organic" class="ml-3 block text-sm font-medium text-gray-700">
                  Certified Organic
                </label>
              </div>
            </div>
          </div>

          <!-- Additional Information Section -->
          <div class="p-6 bg-gray-50/50">
            <h2 class="text-lg font-semibold text-gray-900 mb-4 pb-2 border-b border-gray-200">
              Additional Information
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Storage Conditions
                </label>
                <input
                  v-model="form.storage_conditions"
                  type="text"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:border-green-500 focus:ring-green-500 transition-colors"
                  placeholder="e.g. Cool, dry place"
                />
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Certification
                </label>
                <input
                  v-model="form.certification"
                  type="text"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:border-green-500 focus:ring-green-500 transition-colors"
                  placeholder="e.g. ISO, Organic Certified"
                />
              </div>

              <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Notes
                </label>
                <textarea
                  v-model="form.notes"
                  rows="3"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:border-green-500 focus:ring-green-500 transition-colors resize-none"
                  placeholder="Any additional notes or special instructions..."
                ></textarea>
              </div>
            </div>
          </div>

          <!-- Form Actions -->
          <div class="p-6 bg-gray-50 border-t border-gray-200">
            <div class="flex justify-end gap-3">
              <button
                type="button"
                @click="router.push('/marketplace/my-products')"
                class="px-6 py-2.5 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
              >
                Cancel
              </button>
              <button
                type="submit"
                :disabled="submitting"
                class="inline-flex items-center px-6 py-2.5 text-sm font-medium rounded-lg bg-green-600 text-white hover:bg-green-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm"
              >
                <svg
                  v-if="submitting"
                  class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ submitting ? 'Creating Product...' : 'Create Product' }}
              </button>
            </div>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useMarketplaceStore } from '@/stores/marketplace'
import { useFarmStore } from '@/stores/farm'
import FormAlert from '@/Components/UI/FormAlert.vue'
import { extractFormErrors, resetFormErrors } from '@/utils/form'
import axios from 'axios'

const router = useRouter()
const marketplaceStore = useMarketplaceStore()
const farmStore = useFarmStore()

const submitting = ref(false)
const errors = ref({})
const formError = reactive({
  message: '',
  fieldErrors: {},
})

// Image upload state
const fileInput = ref(null)
const uploadedImages = ref([])
const uploadingImages = ref(false)
const isDragging = ref(false)
const imageError = ref('')

const form = reactive({
  rice_variety_id: '',
  harvest_id: '',
  name: '',
  description: '',
  quantity_available: '',
  quantity_available: '',
  unit: 'sacks',
  price_per_unit: '',
  quality_grade: 'premium',
  moisture_content: '',
  purity_percentage: '',
  processing_method: '',
  storage_conditions: '',
  certification: '',
  is_organic: false,
  minimum_order_quantity: '',
  notes: ''
})

const units = ['sacks']
const qualityGrades = {
  premium: 'Premium',
  grade_a: 'Grade A',
  grade_b: 'Grade B',
  commercial: 'Commercial'
}
const processingMethods = {
  milled: 'Milled',
  brown: 'Brown Rice',
  parboiled: 'Parboiled',
  organic: 'Organic'
}

const riceVarieties = computed(() => marketplaceStore.riceVarieties || [])
const harvests = computed(() => farmStore.harvests || [])

const formatHarvestOption = (harvest) => {
  const crop = harvest?.planting?.crop_type || 'Harvest'
  const date = harvest?.harvest_date ? new Date(harvest.harvest_date).toLocaleDateString() : 'Undated'
  const yieldKg = harvest?.yield ? `${Number(harvest.yield).toLocaleString()} kg` : ''
  return `${crop} • ${date}${yieldKg ? ` • ${yieldKg}` : ''}`
}

// Image upload handlers
const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  uploadImages(files)
  event.target.value = '' // Reset input
}

const handleDrop = (event) => {
  isDragging.value = false
  const files = Array.from(event.dataTransfer.files).filter(file => file.type.startsWith('image/'))
  uploadImages(files)
}

const uploadImages = async (files) => {
  imageError.value = ''
  
  // Check total count
  if (uploadedImages.value.length + files.length > 5) {
    imageError.value = 'You can upload a maximum of 5 images.'
    return
  }
  
  // Validate file sizes
  const oversizedFiles = files.filter(f => f.size > 2 * 1024 * 1024)
  if (oversizedFiles.length > 0) {
    imageError.value = 'Some files are larger than 2MB and were not uploaded.'
    files = files.filter(f => f.size <= 2 * 1024 * 1024)
  }
  
  if (files.length === 0) return
  
  uploadingImages.value = true
  
  try {
    const formData = new FormData()
    files.forEach(file => formData.append('images[]', file))
    
    const response = await axios.post('/api/rice-marketplace/products/images/upload', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })
    
    uploadedImages.value.push(...response.data.urls)
  } catch (error) {
    console.error('Upload error:', error)
    imageError.value = error.response?.data?.message || 'Failed to upload images. Please try again.'
  } finally {
    uploadingImages.value = false
  }
}

const removeImage = async (index) => {
  const url = uploadedImages.value[index]
  
  try {
    await axios.post('/api/rice-marketplace/products/images/delete', { url })
  } catch (error) {
    console.warn('Failed to delete image from server:', error)
  }
  
  uploadedImages.value.splice(index, 1)
}

const submit = async () => {
  if (submitting.value) return
  
  submitting.value = true
  errors.value = {}
  resetFormErrors(formError)

  try {
    const payload = {
      ...form,
      rice_variety_id: Number(form.rice_variety_id),
      harvest_id: form.harvest_id || null,
      quantity_available: Number(form.quantity_available),
      price_per_unit: Number(form.price_per_unit),
      moisture_content: form.moisture_content ? Number(form.moisture_content) : null,
      purity_percentage: form.purity_percentage ? Number(form.purity_percentage) : null,
      minimum_order_quantity: form.minimum_order_quantity ? Number(form.minimum_order_quantity) : null,
      is_organic: Boolean(form.is_organic),
    }

    if (!payload.harvest_id) delete payload.harvest_id
    if (!payload.moisture_content) delete payload.moisture_content
    if (!payload.purity_percentage) delete payload.purity_percentage
    if (!payload.minimum_order_quantity) delete payload.minimum_order_quantity
    if (!payload.processing_method) delete payload.processing_method
    if (!payload.storage_conditions) delete payload.storage_conditions
    if (!payload.certification) delete payload.certification
    if (!payload.notes) delete payload.notes
    
    // Add uploaded images
    if (uploadedImages.value.length > 0) {
      payload.images = uploadedImages.value
    }

    const result = await marketplaceStore.createRiceProduct(payload)
    console.log('Product created, result:', result)
    resetFormErrors(formError)
    
    // Small delay to ensure state is updated before navigation
    await new Promise(resolve => setTimeout(resolve, 100))
    
    router.push('/marketplace/my-products')
  } catch (error) {
    const parsed = extractFormErrors(error)
    formError.message = parsed.message
    formError.fieldErrors = parsed.fieldErrors
    errors.value = parsed.fieldErrors
  } finally {
    submitting.value = false
  }
}

onMounted(async () => {
  await Promise.all([
    marketplaceStore.fetchRiceVarieties(),
    farmStore.fetchHarvests()
  ])
})
</script>
