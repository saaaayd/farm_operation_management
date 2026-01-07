<template>
  <div class="min-h-screen bg-gray-50/50">
    <!-- Header -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-30">
      <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">
          <div class="flex items-center gap-4">
            <button
              @click="router.push('/marketplace/my-products')"
              class="p-2 -ml-2 text-gray-400 hover:text-gray-600 rounded-full hover:bg-gray-100 transition-colors"
            >
              <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
              </svg>
            </button>
            <div>
              <h1 class="text-xl font-bold text-gray-900">Edit Product</h1>
              <p class="text-xs text-gray-500 mt-0.5">Update your product details and pricing</p>
            </div>
          </div>
          
          <div class="flex items-center gap-3">
            <button
              @click="deleteProduct"
              class="text-sm font-medium text-red-600 hover:text-red-700 px-3 py-2 rounded-lg hover:bg-red-50 transition-colors"
            >
              Delete
            </button>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading State -->
      <div v-if="loadingProduct" class="flex flex-col items-center justify-center py-20">
        <LoadingSpinner>Loading product details...</LoadingSpinner>
      </div>

      <!-- Main Form -->
      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left Column: Main Info -->
        <div class="lg:col-span-2 space-y-6">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
            
            <InputField
              v-model="form.name"
              label="Product Name"
              placeholder="e.g. Premium Jasmine Rice"
              required
              :error="errors.name?.[0]"
            />

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <SelectDropdown
                v-model="form.rice_variety_id"
                label="Rice Variety"
                required
                :error="errors.rice_variety_id?.[0]"
              >
                <option v-for="variety in riceVarieties" :key="variety.id" :value="variety.id">
                  {{ variety.name }}
                </option>
              </SelectDropdown>

              <SelectDropdown
                v-model="form.quality_grade"
                label="Quality Grade"
                required
                :error="errors.quality_grade?.[0]"
              >
                <option v-for="(label, value) in qualityGrades" :key="value" :value="value">
                  {{ label }}
                </option>
              </SelectDropdown>
            </div>

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Description <span class="text-red-500">*</span></label>
              <textarea
                v-model="form.description"
                rows="4"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                placeholder="Describe your product..."
                required
              ></textarea>
              <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description[0] }}</p>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Inventory & Pricing</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <InputField
                v-model="form.quantity_available"
                type="number"
                label="Quantity Available"
                placeholder="0.00"
                required
                min="0"
                step="0.01"
                :error="errors.quantity_available?.[0]"
              />

              <SelectDropdown
                v-model="form.unit"
                label="Unit"
                required
                :error="errors.unit?.[0]"
              >
                <option v-for="unit in units" :key="unit" :value="unit">
                  {{ unit }}
                </option>
              </SelectDropdown>

              <InputField
                v-model="form.price_per_unit"
                type="number"
                label="Price per Sack (₱)"
                placeholder="0.00"
                required
                min="0"
                step="0.01"
                :error="errors.price_per_unit?.[0]"
              />

              <InputField
                v-model="form.minimum_order_quantity"
                type="number"
                label="Min. Order Quantity"
                placeholder="Optional"
                min="0"
                step="0.1"
                :error="errors.minimum_order_quantity?.[0]"
              />
            </div>
          </div>

          <!-- Product Images Section -->
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Product Images</h2>
            <p class="text-sm text-gray-600 mb-4">Upload photos of your rice to showcase quality. (Maximum 5 images, 2MB each)</p>
            
            <!-- Upload Area -->
            <div
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="handleDrop"
              :class="[
                'border-2 border-dashed rounded-lg p-6 text-center transition-colors cursor-pointer',
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
              <svg class="mx-auto h-10 w-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
              </svg>
              <p class="mt-2 text-sm text-gray-600">
                <span class="font-semibold text-green-600">Click to upload</span> or drag and drop
              </p>
              <p class="text-xs text-gray-500 mt-1">JPG, PNG, WebP up to 2MB</p>
            </div>

            <!-- Upload Progress -->
            <div v-if="uploadingImages" class="flex items-center justify-center gap-2">
              <svg class="animate-spin h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              <span class="text-sm text-gray-600">Uploading...</span>
            </div>

            <!-- Image Previews -->
            <div v-if="uploadedImages.length > 0" class="grid grid-cols-3 gap-3">
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
                  <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                  </svg>
                </button>
              </div>
            </div>

            <!-- Image Error -->
            <p v-if="imageError" class="text-sm text-red-600">{{ imageError }}</p>
          </div>
        </div>

        <!-- Right Column: Additional Details -->
        <div class="space-y-6">
          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Specifications</h2>

            <SelectDropdown
              v-model="form.processing_method"
              label="Processing Method"
              placeholder="Select method"
            >
              <option v-for="(label, value) in processingMethods" :key="value" :value="value">
                {{ label }}
              </option>
            </SelectDropdown>

            <div class="grid grid-cols-2 gap-4">
              <InputField
                v-model="form.moisture_content"
                type="number"
                label="Moisture (%)"
                placeholder="e.g. 14"
                min="5"
                max="25"
                step="0.1"
              />

              <InputField
                v-model="form.purity_percentage"
                type="number"
                label="Purity (%)"
                placeholder="e.g. 98"
                min="50"
                max="100"
                step="0.1"
              />
            </div>

            <InputField
              v-model="form.certification"
              label="Certification"
              placeholder="e.g. GAP, Organic"
            />

            <div class="flex items-center justify-between p-4 bg-green-50 rounded-xl border border-green-100">
              <label for="is_organic" class="text-sm font-medium text-green-900 cursor-pointer select-none">
                Certified Organic
              </label>
              <div class="relative inline-block w-10 mr-2 align-middle select-none transition duration-200 ease-in">
                <input
                  type="checkbox"
                  name="is_organic"
                  id="is_organic"
                  v-model="form.is_organic"
                  class="toggle-checkbox absolute block w-5 h-5 rounded-full bg-white border-4 appearance-none cursor-pointer transition-transform duration-200 ease-in-out"
                  :class="{ 'translate-x-5 border-green-500': form.is_organic, 'border-gray-300': !form.is_organic }"
                />
                <label
                  for="is_organic"
                  class="toggle-label block overflow-hidden h-5 rounded-full cursor-pointer transition-colors duration-200"
                  :class="{ 'bg-green-500': form.is_organic, 'bg-gray-300': !form.is_organic }"
                ></label>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 space-y-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-4">Storage & Notes</h2>
            
            <InputField
              v-model="form.storage_conditions"
              label="Storage Conditions"
              placeholder="e.g. Cool, dry place"
            />

            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Internal Notes</label>
              <textarea
                v-model="form.notes"
                rows="3"
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200 bg-gray-50 focus:bg-white"
                placeholder="Private notes..."
              ></textarea>
            </div>
          </div>

          <!-- Sticky Action Bar for Mobile/Desktop -->
          <div class="sticky bottom-6">
            <div class="bg-white rounded-2xl shadow-lg border border-gray-100 p-4 flex gap-3">
              <button
                type="button"
                @click="router.push('/marketplace/my-products')"
                class="flex-1 px-4 py-2.5 text-sm font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-xl transition-colors"
              >
                Cancel
              </button>
              <button
                @click="submit"
                :disabled="submitting"
                class="flex-1 px-4 py-2.5 text-sm font-semibold text-white bg-green-600 hover:bg-green-700 rounded-xl shadow-md hover:shadow-lg transition-all disabled:opacity-70 disabled:cursor-not-allowed flex items-center justify-center gap-2"
              >
                <svg v-if="submitting" class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                {{ submitting ? 'Saving...' : 'Save Changes' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMarketplaceStore } from '@/stores/marketplace'
import InputField from '@/Components/Forms/InputField.vue'
import SelectDropdown from '@/Components/Forms/SelectDropdown.vue'
import LoadingSpinner from '@/Components/UI/LoadingSpinner.vue'
import axios from 'axios'

const route = useRoute()
const router = useRouter()
const marketplaceStore = useMarketplaceStore()

const submitting = ref(false)
const loadingProduct = ref(true)
const errors = ref({})

// Image upload state
const fileInput = ref(null)
const uploadedImages = ref([])
const uploadingImages = ref(false)
const isDragging = ref(false)
const imageError = ref('')

const form = reactive({
  rice_variety_id: '',
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

const loadProduct = async () => {
  try {
    const product = await marketplaceStore.getRiceProduct(route.params.id)
    Object.assign(form, {
      rice_variety_id: product.rice_variety_id,
      name: product.name,
      description: product.description,
      quantity_available: product.quantity_available,
      unit: product.unit,
      price_per_unit: product.price_per_unit,
      quality_grade: product.quality_grade,
      moisture_content: product.moisture_content,
      purity_percentage: product.purity_percentage,
      processing_method: product.processing_method,
      storage_conditions: product.storage_conditions,
      certification: product.certification,
      is_organic: Boolean(product.is_organic),
      minimum_order_quantity: product.minimum_order_quantity,
      notes: product.notes
    })
    // Load existing images
    if (product.images && Array.isArray(product.images)) {
      uploadedImages.value = [...product.images]
    }
  } catch (error) {
    console.error('Error loading product:', error)
    router.push('/marketplace/my-products')
  } finally {
    loadingProduct.value = false
  }
}

const submit = async () => {
  submitting.value = true
  errors.value = {}

  try {
    const payload = {
      rice_variety_id: Number(form.rice_variety_id),
      name: form.name,
      description: form.description,
      quantity_available: Number(form.quantity_available),
      unit: form.unit,
      price_per_unit: Number(form.price_per_unit),
      quality_grade: form.quality_grade,
      moisture_content: form.moisture_content ? Number(form.moisture_content) : null,
      purity_percentage: form.purity_percentage ? Number(form.purity_percentage) : null,
      processing_method: form.processing_method || null,
      storage_conditions: form.storage_conditions || null,
      certification: form.certification || null,
      is_organic: Boolean(form.is_organic),
      minimum_order_quantity: form.minimum_order_quantity ? Number(form.minimum_order_quantity) : null,
      notes: form.notes || null,
      images: uploadedImages.value.length > 0 ? uploadedImages.value : null
    }

    await marketplaceStore.updateRiceProduct(route.params.id, payload)
    router.push('/marketplace/my-products')
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    } else {
      console.error('Error updating product:', error)
    }
  } finally {
    submitting.value = false
  }
}

const deleteProduct = async () => {
  if (!confirm('Are you sure you want to delete this product? This action cannot be undone.')) return

  try {
    await marketplaceStore.deleteRiceProduct(route.params.id)
    router.push('/marketplace/my-products')
  } catch (error) {
    console.error('Failed to delete product:', error)
    alert('Failed to delete product. Please try again.')
  }
}

onMounted(async () => {
  await marketplaceStore.fetchRiceVarieties()
  await loadProduct()
})

// Image upload handlers
const handleFileSelect = (event) => {
  const files = Array.from(event.target.files)
  uploadImages(files)
  event.target.value = ''
}

const handleDrop = (event) => {
  isDragging.value = false
  const files = Array.from(event.dataTransfer.files).filter(file => file.type.startsWith('image/'))
  uploadImages(files)
}

const uploadImages = async (files) => {
  imageError.value = ''
  
  if (uploadedImages.value.length + files.length > 5) {
    imageError.value = 'You can upload a maximum of 5 images.'
    return
  }
  
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
    imageError.value = error.response?.data?.message || 'Failed to upload images.'
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
</script>

<style scoped>
/* Custom Toggle Switch */
.toggle-checkbox:checked {
  right: 0;
  border-color: #10b981;
}
.toggle-checkbox:checked + .toggle-label {
  background-color: #10b981;
}
</style>
