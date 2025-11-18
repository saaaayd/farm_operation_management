<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">Add Rice Product</h1>
            <p class="text-sm text-gray-500">
              Publish a new product with quality details and pricing.
            </p>
          </div>
          <button
            @click="router.push('/marketplace/my-products')"
            class="text-sm text-gray-600 hover:text-gray-800"
          >
            Cancel
          </button>
        </div>
      </div>
    </header>

    <main class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="bg-white rounded-lg shadow p-6">
        <form @submit.prevent="submit" class="space-y-6">
          <FormAlert
            :visible="!!formError.message"
            :message="formError.message"
            :field-errors="formError.fieldErrors"
          />

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
              <label class="form-label">Linked Harvest</label>
              <select
                v-model="form.harvest_id"
                class="form-input"
              >
                <option value="">Select harvest</option>
                <option
                  v-for="harvest in harvests"
                  :key="harvest.id"
                  :value="harvest.id"
                >
                  {{ formatHarvestOption(harvest) }}
                </option>
              </select>
              <p v-if="errors.harvest_id" class="form-error">{{ errors.harvest_id[0] }}</p>
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
              <p v-if="errors.quality_grade" class="form-error">{{ errors.quality_grade[0] }}</p>
            </div>

            <div>
              <label class="form-label">Quantity Available (numeric) *</label>
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
              <p v-if="errors.unit" class="form-error">{{ errors.unit[0] }}</p>
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
              <p v-if="errors.moisture_content" class="form-error">{{ errors.moisture_content[0] }}</p>
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
            <p v-if="errors.description" class="form-error">{{ errors.description[0] }}</p>
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

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

            <div class="flex items-center mt-6">
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
          </div>

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
              {{ submitting ? 'Saving...' : 'Create Product' }}
            </button>
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

const router = useRouter()
const marketplaceStore = useMarketplaceStore()
const farmStore = useFarmStore()

const submitting = ref(false)
const errors = ref({})
const formError = reactive({
  message: '',
  fieldErrors: {},
})

const form = reactive({
  rice_variety_id: '',
  harvest_id: '',
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
const harvests = computed(() => farmStore.harvests || [])

const formatHarvestOption = (harvest) => {
  const crop = harvest?.planting?.crop_type || 'Harvest'
  const date = harvest?.harvest_date ? new Date(harvest.harvest_date).toLocaleDateString() : 'Undated'
  const yieldKg = harvest?.yield ? `${Number(harvest.yield).toLocaleString()} kg` : ''
  return `${crop} • ${date}${yieldKg ? ` • ${yieldKg}` : ''}`
}

const submit = async () => {
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

    await marketplaceStore.createRiceProduct(payload)
    resetFormErrors(formError)
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

