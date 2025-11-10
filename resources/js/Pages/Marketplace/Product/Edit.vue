<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">Edit Product</h1>
            <p class="text-sm text-gray-500">
              Update pricing, availability, and quality details.
            </p>
          </div>
          <button
            @click="router.push('/marketplace/my-products')"
            class="text-sm text-gray-600 hover:text-gray-800"
          >
            Back to list
          </button>
        </div>
      </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="loadingProduct" class="bg-white rounded-lg shadow p-8 text-center text-gray-500">
        Loading product details...
      </div>

      <div v-else class="bg-white rounded-lg shadow p-6 space-y-6">
        <div class="flex justify-end">
          <button
            @click="deleteProduct"
            class="text-sm text-red-600 hover:text-red-800"
          >
            Delete product
          </button>
        </div>

        <form @submit.prevent="submit" class="space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="form-label">Product Name *</label>
              <input
                v-model="form.name"
                type="text"
                class="form-input"
                required
              />
              <p v-if="errors.name" class="form-error">{{ errors.name[0] }}</p>
            </div>

            <div>
              <label class="form-label">Rice Variety *</label>
              <select
                v-model="form.rice_variety_id"
                class="form-input"
                required
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
              <p v-if="errors.rice_variety_id" class="form-error">{{ errors.rice_variety_id[0] }}</p>
            </div>

            <div>
              <label class="form-label">Quality Grade *</label>
              <select
                v-model="form.quality_grade"
                class="form-input"
                required
              >
                <option
                  v-for="(label, value) in qualityGrades"
                  :key="value"
                  :value="value"
                >
                  {{ label }}
                </option>
              </select>
            </div>

            <div>
              <label class="form-label">Quantity Available *</label>
              <input
                v-model="form.quantity_available"
                type="number"
                min="0"
                step="0.01"
                class="form-input"
                required
              />
              <p v-if="errors.quantity_available" class="form-error">{{ errors.quantity_available[0] }}</p>
            </div>

            <div>
              <label class="form-label">Unit *</label>
              <select
                v-model="form.unit"
                class="form-input"
                required
              >
                <option
                  v-for="unit in units"
                  :key="unit"
                  :value="unit"
                >
                  {{ unit }}
                </option>
              </select>
            </div>

            <div>
              <label class="form-label">Price per Unit (₱) *</label>
              <input
                v-model="form.price_per_unit"
                type="number"
                min="0"
                step="0.01"
                class="form-input"
                required
              />
              <p v-if="errors.price_per_unit" class="form-error">{{ errors.price_per_unit[0] }}</p>
            </div>

            <div>
              <label class="form-label">Moisture Content (%)</label>
              <input
                v-model="form.moisture_content"
                type="number"
                min="5"
                max="25"
                step="0.1"
                class="form-input"
              />
            </div>

            <div>
              <label class="form-label">Purity Percentage</label>
              <input
                v-model="form.purity_percentage"
                type="number"
                min="50"
                max="100"
                step="0.1"
                class="form-input"
              />
            </div>
          </div>

          <div>
            <label class="form-label">Description *</label>
            <textarea
              v-model="form.description"
              rows="4"
              class="form-input"
              required
            ></textarea>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="form-label">Processing Method</label>
              <select
                v-model="form.processing_method"
                class="form-input"
              >
                <option value="">Select method</option>
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
              <label class="form-label">Minimum Order Quantity</label>
              <input
                v-model="form.minimum_order_quantity"
                type="number"
                min="0"
                step="0.1"
                class="form-input"
              />
            </div>
          </div>

          <div class="flex items-center">
            <input
              id="is_organic"
              v-model="form.is_organic"
              type="checkbox"
              class="h-4 w-4 text-green-600 border-gray-300 rounded"
            />
            <label for="is_organic" class="ml-2 block text-sm text-gray-700">
              Certified organic
            </label>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="form-label">Storage Conditions</label>
              <input
                v-model="form.storage_conditions"
                type="text"
                class="form-input"
              />
            </div>

            <div>
              <label class="form-label">Certification</label>
              <input
                v-model="form.certification"
                type="text"
                class="form-input"
              />
            </div>
          </div>

          <div>
            <label class="form-label">Notes</label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="form-input"
            ></textarea>
          </div>

          <div class="flex justify-end gap-3">
            <button
              type="button"
              @click="router.push('/marketplace/my-products')"
              class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800"
            >
              Cancel
            </button>
            <button
              type="submit"
              :disabled="submitting"
              class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md bg-green-600 text-white hover:bg-green-700 disabled:opacity-50"
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
              {{ submitting ? 'Saving...' : 'Update Product' }}
            </button>
          </div>
        </form>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useMarketplaceStore } from '@/stores/marketplace'

const route = useRoute()
const router = useRouter()
const marketplaceStore = useMarketplaceStore()

const submitting = ref(false)
const loadingProduct = ref(true)
const errors = ref({})

const form = reactive({
  rice_variety_id: '',
  name: '',
  description: '',
  quantity_available: '',
  unit: 'kg',
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

const units = ['kg', 'tons', 'bags', 'sacks']
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
      is_organic: product.is_organic,
      minimum_order_quantity: product.minimum_order_quantity,
      notes: product.notes
    })
  } catch (error) {
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
      notes: form.notes || null
    }

    await marketplaceStore.updateRiceProduct(route.params.id, payload)
    router.push('/marketplace/my-products')
  } catch (error) {
    if (error.response?.data?.errors) {
      errors.value = error.response.data.errors
    }
  } finally {
    submitting.value = false
  }
}

const deleteProduct = async () => {
  if (!confirm('Delete this product? This action cannot be undone.')) return

  try {
    await marketplaceStore.deleteRiceProduct(route.params.id)
    router.push('/marketplace/my-products')
  } catch (error) {
    console.error('Failed to delete product:', error)
  }
}

onMounted(async () => {
  await marketplaceStore.fetchRiceVarieties()
  await loadProduct()
})
</script>

<style scoped lang="postcss">
@reference "tailwindcss";
.form-label {
  @apply block text-sm font-medium text-gray-700 mb-2;
}

.form-input {
  @apply w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-green-500 focus:border-green-500;
}

.form-error {
  @apply mt-1 text-xs text-red-600;
}
</style>

