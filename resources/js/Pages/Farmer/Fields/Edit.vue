<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="w-full mx-auto space-y-8">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <button
            type="button"
            @click="router.push('/fields')"
            class="inline-flex items-center text-sm font-medium text-emerald-700 hover:text-emerald-900"
          >
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Fields
          </button>
          <h1 class="mt-4 text-3xl font-bold text-gray-900">Edit Field</h1>
          <p class="mt-2 text-base text-gray-600 max-w-2xl">
            Update detailed field information, soil data, and location-specific settings.
          </p>
        </div>
        <div class="bg-white/90 border border-emerald-100 rounded-2xl px-5 py-4 shadow-md">
          <p class="text-xs uppercase tracking-wide text-gray-500">Field Completeness</p>
          <p class="text-2xl font-semibold text-emerald-600 mt-1">{{ completionScore }}%</p>
          <p class="text-sm text-gray-500">Keep details up to date for accuracy.</p>
        </div>
      </div>

      <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-800">
        {{ error }}
      </div>
      
      <div v-if="loadingData" class="flex justify-center py-12">
         <LoadingSpinner class="h-10 w-10 text-emerald-600" />
      </div>

      <form v-else @submit.prevent="submitField" class="space-y-8">
        <!-- Field Identity -->
        <section class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100 space-y-6">
          <div class="flex items-center mb-4">
            <div class="h-12 w-12 rounded-xl bg-emerald-100 flex items-center justify-center mr-4">
              <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7l9-4 9 4-9 4-9-4zm0 6l9 4 9-4M3 7v10l9 4 9-4V7" />
              </svg>
            </div>
            <div>
              <h2 class="text-xl font-semibold text-gray-900">Field Identity</h2>
              <p class="text-sm text-gray-600">Core details about this specific field.</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Field Name *
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                placeholder="e.g., North Paddock"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Field Nickname (optional)
              </label>
              <input
                v-model="form.nickname"
                type="text"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                placeholder="Internal label for your team"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Field Size (hectares) *
              </label>
              <input
                v-model.number="form.size"
                type="number"
                step="0.01"
                min="0"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                placeholder="5.50"
              />
              <p class="mt-1 text-xs text-gray-500">
                Available: {{ availableFarmAreaWithCurrent.toFixed(2) }} ha (Total Farm: {{ totalFarmArea.toFixed(2) }} ha)
              </p>
              <p v-if="form.size && parseFloat(form.size) > availableFarmAreaWithCurrent" class="mt-1 text-xs text-red-600">
                Field size cannot exceed available farm area
              </p>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">
                Current / Planned Crop
              </label>
              <select
                v-model="form.current_crop"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              >
                <option value="">Select crop or variety</option>
                <optgroup label="Rice Varieties">
                  <option v-for="variety in riceVarieties" :key="variety.id" :value="variety.name">
                    {{ variety.name }}
                  </option>
                </optgroup>
                <optgroup label="Other Crops">
                  <option value="other">Other (specify in notes)</option>
                </optgroup>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Field Description / Notes
            </label>
            <textarea
              v-model="form.description"
              rows="3"
              class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition resize-none"
              placeholder="Describe terrain, accessibility, known issues, etc."
            ></textarea>
          </div>
        </section>

        <!-- Location -->
        <section class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100 space-y-6">
          <div class="flex items-center mb-4">
            <div class="h-12 w-12 rounded-xl bg-blue-100 flex items-center justify-center mr-4">
              <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a2 2 0 01-2.828 0l-4.243-4.243a8 8 0 1111.314 0z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
            </div>
            <div>
              <h2 class="text-xl font-semibold text-gray-900">Location Details</h2>
              <p class="text-sm text-gray-600">Exact position helps tailor weather forecasts.</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div v-if="isLocationLocked" class="col-span-1 md:col-span-3">
               <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center">
                 <div>
                    <h3 class="font-medium text-gray-900">Using Default/Existing Location</h3>
                    <p class="text-sm text-gray-500">{{ form.location.address || 'Address not set' }}</p>
                 </div>
                 <button type="button" @click="toggleLocationLock" class="mt-2 sm:mt-0 text-sm font-medium text-emerald-600 hover:text-emerald-700 underline">
                    Edit Location Details
                 </button>
               </div>
            </div>

            <template v-else>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Province *</label>
              <select
                v-model="form.location.provinceCode"
                @change="fetchCities"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              >
                <option value="">Select province</option>
                <option v-for="province in provinces" :key="province.code" :value="province.code">
                  {{ province.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">City / Municipality *</label>
              <select
                v-model="form.location.cityCode"
                @change="fetchBarangays"
                :disabled="!form.location.provinceCode"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition disabled:bg-gray-100"
              >
                <option value="">Select city</option>
                <option v-for="city in cities" :key="city.code" :value="city.code">
                  {{ city.name }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Barangay *</label>
              <select
                v-model="form.location.barangayCode"
                @change="setAddress"
                :disabled="!form.location.cityCode"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition disabled:bg-gray-100"
              >
                <option value="">Select barangay</option>
                <option v-for="barangay in barangays" :key="barangay.code" :value="barangay.code">
                  {{ barangay.name }}
                </option>
              </select>
            </div>
            <div class="col-span-1 md:col-span-3 flex justify-end -mt-4">
                 <button type="button" @click="toggleLocationLock" class="text-xs text-gray-500 hover:text-gray-700">
                    Cancel Location Edit
                 </button>
            </div>
            </template>
          </div>

          <div v-if="form.location.address" class="bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 rounded-xl p-4">
            <div class="flex items-start">
              <svg class="h-5 w-5 text-emerald-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800 mb-1">Selected Address:</p>
                <p class="text-sm text-gray-700">{{ form.location.address }}</p>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Field Location on Map *
            </label>
            <p class="text-xs text-gray-600 mb-3">Click on the map to update the field's center coordinates</p>
            <div class="relative">
              <div 
                ref="mapContainer" 
                class="w-full h-96 rounded-lg border-2 border-gray-300 overflow-hidden shadow-sm"
                style="min-height: 400px;"
              ></div>
              <div v-if="form.location.lat && form.location.lon" class="absolute top-2 right-2 bg-white/95 backdrop-blur-sm rounded-lg px-3 py-2 shadow-md text-xs">
                <div class="font-semibold text-gray-700">Selected Location</div>
                <div class="text-gray-600">Lat: {{ form.location.lat.toFixed(6) }}</div>
                <div class="text-gray-600">Lon: {{ form.location.lon.toFixed(6) }}</div>
              </div>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-4">
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Latitude</label>
                <input
                  v-model.number="form.location.lat"
                  type="number"
                  step="any"
                  min="-90"
                  max="90"
                  @input="updateMapMarker"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                />
              </div>
              <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Longitude</label>
                <input
                  v-model.number="form.location.lon"
                  type="number"
                  step="any"
                  min="-180"
                  max="180"
                  @input="updateMapMarker"
                  class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                />
              </div>
            </div>
          </div>
        </section>

        <!-- Soil & Terrain -->
        <section class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100 space-y-6">
          <div class="flex items-center mb-4">
            <div class="h-12 w-12 rounded-xl bg-amber-100 flex items-center justify-center mr-4">
              <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2 7-7 7 7 2 2v7a2 2 0 01-2 2h-5a2 2 0 01-2-2v-3H7v3a2 2 0 01-2 2H3v-7z" />
              </svg>
            </div>
            <div>
              <h2 class="text-xl font-semibold text-gray-900">Soil & Terrain</h2>
              <p class="text-sm text-gray-600">Soil tests help us calibrate nutrition plans.</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Primary Soil Type *</label>
              <select
                v-model="form.soil_type"
                required
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              >
                <option value="">Select soil type</option>
                <option v-for="soil in soilTypes" :key="soil.value" :value="soil.value">
                  {{ soil.label }}
                </option>
              </select>
            </div>
          </div>
        </section>


        <!-- Water & Infrastructure -->
        <section class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100 space-y-6">
          <div class="flex items-center mb-4">
            <div class="h-12 w-12 rounded-xl bg-cyan-100 flex items-center justify-center mr-4">
              <svg class="h-6 w-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 002-2V6a1 1 0 011-1h2m6 0h2a1 1 0 011 1v3a2 2 0 002 2h1.945M15 21v-6a3 3 0 00-3-3 3 3 0 00-3 3v6m6 0H9" />
              </svg>
            </div>
            <div>
              <h2 class="text-xl font-semibold text-gray-900">Water & Infrastructure</h2>
              <p class="text-sm text-gray-600">Understand irrigation readiness and constraints.</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Primary Water Source</label>
              <select
                v-model="form.water_source"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              >
                <option value="">Select water source</option>
                <option v-for="source in waterSources" :key="source.value" :value="source.value">
                  {{ source.label }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Irrigation System</label>
              <select
                v-model="form.irrigation_type"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              >
                <option value="">Select irrigation type</option>
                <option v-for="irrigation in irrigationTypes" :key="irrigation.value" :value="irrigation.value">
                  {{ irrigation.label }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Water Access Quality</label>
              <select
                v-model="form.water_access"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              >
                <option value="">Select access quality</option>
                <option v-for="option in waterAccessOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Drainage Quality</label>
              <select
                v-model="form.drainage_quality"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              >
                <option value="">Select drainage rating</option>
                <option v-for="option in drainageOptions" :key="option.value" :value="option.value">
                  {{ option.label }}
                </option>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Infrastructure Notes</label>
            <textarea
              v-model="form.infrastructure_notes"
              rows="3"
              class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition resize-none"
              placeholder="Describe pump condition, dikes, access roads, etc."
            ></textarea>
          </div>
        </section>

        <!-- Cultivation Plans -->
        <section class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100 space-y-6">
          <div class="flex items-center mb-4">
            <div class="h-12 w-12 rounded-xl bg-indigo-100 flex items-center justify-center mr-4">
              <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-.88 0-1.725.358-2.35.995A3.31 3.31 0 008.667 11v1.333H6.667A1.667 1.667 0 005 14v4.333a1.667 1.667 0 001.667 1.667h10.666A1.667 1.667 0 0019 18.333V14a1.667 1.667 0 00-1.667-1.667H15.333V11c0-.88-.358-1.725-.995-2.35A3.31 3.31 0 0012 8z" />
              </svg>
            </div>
            <div>
              <h2 class="text-xl font-semibold text-gray-900">Cultivation Plans</h2>
              <p class="text-sm text-gray-600">Optional details for production targets.</p>
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Planting Method</label>
              <select
                v-model="form.planting_method"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              >
                <option value="">Select planting method</option>
                <option v-for="method in plantingMethods" :key="method.value" :value="method.value">
                  {{ method.label }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Cropping Seasons per Year</label>
              <select
                v-model="form.cropping_seasons"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              >
                <option value="">Select seasons</option>
                <option v-for="season in croppingSeasonOptions" :key="season.value" :value="season.value">
                  {{ season.label }}
                </option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Target Yield (tons/ha)</label>
              <input
                v-model.number="form.target_yield"
                type="number"
                step="0.1"
                min="0"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                placeholder="4.5"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Previous Crop</label>
              <select
                v-model="form.previous_crop"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
              >
                <option value="">Select previous crop</option>
                <optgroup label="Rice Varieties">
                  <option v-for="variety in riceVarieties" :key="variety.id" :value="variety.name">
                    {{ variety.name }}
                  </option>
                </optgroup>
                <optgroup label="Other Crops">
                  <option value="fallow">Fallow (no crop)</option>
                  <option value="other">Other (specify in notes)</option>
                </optgroup>
              </select>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">Additional Notes</label>
            <textarea
              v-model="form.notes"
              rows="4"
              class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition resize-none"
              placeholder="Any other context we should know about this field."
            ></textarea>
          </div>
        </section>
        
        <!-- Danger Zone -->
        <section class="bg-red-50 shadow-sm rounded-2xl p-8 border border-red-100 flex flex-col md:flex-row items-center justify-between gap-4">
           <div>
              <h3 class="text-lg font-semibold text-red-800">Delete Field</h3>
              <p class="text-sm text-red-600 max-w-xl">
                 Removing this field will delete all associated data including historical records, soil tests, and tasks. This action cannot be undone.
              </p>
           </div>
           <button
             type="button"
             @click="confirmDelete"
             class="px-5 py-2.5 bg-white border border-red-300 text-red-600 rounded-lg hover:bg-red-50 font-medium transition-colors"
           >
             Delete Field
           </button>
        </section>

        <div class="flex flex-col sm:flex-row gap-4 justify-end pt-4 pb-12">
          <button
            type="button"
            @click="router.push('/fields')"
            class="inline-flex items-center justify-center px-6 py-3 rounded-xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 mb-4 sm:mb-0"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="inline-flex items-center justify-center px-8 py-3 rounded-xl font-semibold text-white bg-gradient-to-r from-emerald-600 to-green-600 shadow-lg hover:shadow-xl hover:from-emerald-700 hover:to-green-700 disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <LoadingSpinner v-if="loading" class="mr-2" />
            {{ loading ? 'Saving changes...' : 'Save Changes' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import axios from 'axios'
import LoadingSpinner from '@/Components/UI/LoadingSpinner.vue'
import { useFarmStore } from '@/stores/farm'
import { useMarketplaceStore } from '@/stores/marketplace'

const router = useRouter()
const route = useRoute()
const farmStore = useFarmStore()
const marketplaceStore = useMarketplaceStore()

const loading = ref(false)
const loadingData = ref(true)
const error = ref('')

// Initialize variables for map
const mapContainer = ref(null)
let map = null
let marker = null

const fieldId = route.params.id

onMounted(async () => {
  try {
     await Promise.all([
       loadProvinces(),
       farmStore.fetchFarmProfile(),
       farmStore.fetchFields(),
       marketplaceStore.fetchRiceVarieties()
     ])
     
     // Fetch field details
     if (fieldId) {
       // We can iterate fields from store, or fetch individual
       // farmStore.fetchFields already called, so fields should be there.
       // But better to fetch specific if API allows, or find in array.
       const field = farmStore.fields.find(f => Number(f.id) === Number(fieldId))
       if (field) {
         populateForm(field)
       } else {
         error.value = 'Field not found'
       }
     }
  } catch (err) {
     console.error("Error loading data", err)
     error.value = "Failed to load field data" 
  } finally {
     loadingData.value = false
     // Initialize map after data loaded so lat/lon are set
     setTimeout(() => {
        initMap()
     }, 100)
  }
})

const populateForm = (field) => {
  form.name = field.name
  form.nickname = field.nickname || ''
  form.description = field.notes || field.description || '' // 'notes' is mapped to description/notes
  form.size = field.size || field.field_size || field.area
  form.current_crop = field.current_crop || ''
  form.soil_type = field.soil_type || ''
  form.water_source = field.water_source || ''
  form.irrigation_type = field.irrigation_type || ''
  form.water_access = field.water_access || ''
  form.drainage_quality = field.drainage_quality || ''
  form.infrastructure_notes = field.infrastructure_notes || ''
  form.planting_method = field.planting_method || ''
  form.cropping_seasons = field.cropping_seasons || ''
  form.target_yield = field.target_yield || ''
  form.previous_crop = field.previous_crop || ''
  form.notes = field.notes || '' // Duplicate mapping? Let's assume notes is generic
  
  if (field.location) {
     if (typeof field.location === 'object') {
        form.location.lat = parseFloat(field.location.lat || field.location.latitude)
        form.location.lon = parseFloat(field.location.lon || field.location.longitude)
        form.location.address = field.location.address || ''
        
        // If we have detailed location codes (unlikely unless stored individually)
        // Usually stored as address string or json.
        // For now, assume address is just a string and we lock it unless user edits.
        isLocationLocked.value = true
     }
  } else {
     // Fallback coordinates if available at root
     if (field.latitude && field.longitude) {
         form.location.lat = parseFloat(field.latitude)
         form.location.lon = parseFloat(field.longitude)
     }
  }
}

const totalFarmArea = computed(() => {
  return parseFloat(farmStore.farmProfile?.total_area || 0)
})

const usedFarmArea = computed(() => {
  if (!farmStore.fields || !Array.isArray(farmStore.fields)) return 0
  return farmStore.fields.reduce((sum, field) => sum + parseFloat(field.size || 0), 0)
})

const availableFarmAreaWithCurrent = computed(() => {
    // Total - (Used - CurrentFieldSize)
    // We need the *original* size of this field to subtract from used.
    // Or just calculate used from *other* fields.
    const otherFields = farmStore.fields.filter(f => Number(f.id) !== Number(fieldId))
    const usedByOthers = otherFields.reduce((sum, f) => sum + parseFloat(f.size || 0), 0)
    return Math.max(0, totalFarmArea.value - usedByOthers)
})


const provinces = ref([])
const cities = ref([])
const barangays = ref([])


// Location locking logic
const isLocationLocked = ref(true)

const toggleLocationLock = () => {
  isLocationLocked.value = !isLocationLocked.value
  if (!isLocationLocked.value) {
     // If unlocking, maybe clear codes to force selection? 
     // Or try to match address to codes? (Hard without reverse geocoding to codes)
     // Let's just clear to be safe so user selects fresh.
     form.location.provinceCode = ''
     form.location.cityCode = ''
     form.location.barangayCode = ''
  }
}


const soilTypes = [
  { value: 'clay', label: 'Clay' },
  { value: 'loam', label: 'Loam' },
  { value: 'sandy', label: 'Sandy' },
  { value: 'silt', label: 'Silt' },
  { value: 'clay_loam', label: 'Clay Loam' },
  { value: 'sandy_loam', label: 'Sandy Loam' },
  { value: 'silty_clay', label: 'Silty Clay' },
  { value: 'silty_loam', label: 'Silty Loam' },
]

const waterSources = [
  { value: 'irrigation_canal', label: 'Irrigation Canal' },
  { value: 'river', label: 'River' },
  { value: 'well', label: 'Deep Well' },
  { value: 'shallow_well', label: 'Shallow Well' },
  { value: 'pond', label: 'Farm Pond' },
  { value: 'rainfall', label: 'Rainfall Dependent' },
  { value: 'spring', label: 'Natural Spring' },
]

const irrigationTypes = [
  { value: 'flood', label: 'Flood Irrigation' },
  { value: 'furrow', label: 'Furrow Irrigation' },
  { value: 'sprinkler', label: 'Sprinkler System' },
  { value: 'drip', label: 'Drip Irrigation' },
  { value: 'manual', label: 'Manual Watering' },
  { value: 'none', label: 'No Irrigation System' },
]

const waterAccessOptions = [
  { value: 'excellent', label: 'Excellent – always available' },
  { value: 'good', label: 'Good – usually available' },
  { value: 'moderate', label: 'Moderate – sometimes limited' },
  { value: 'poor', label: 'Poor – often limited' },
  { value: 'very_poor', label: 'Very Poor – rarely available' },
]

const drainageOptions = [
  { value: 'excellent', label: 'Excellent – quick drainage' },
  { value: 'good', label: 'Good – adequate drainage' },
  { value: 'moderate', label: 'Moderate – slow drainage' },
  { value: 'poor', label: 'Poor – water logging issues' },
]

const plantingMethods = [
  { value: 'direct_seeding', label: 'Direct Seeding' },
  { value: 'transplanting', label: 'Transplanting' },
  { value: 'broadcasting', label: 'Broadcasting' },
  { value: 'drilling', label: 'Drilling' },
]

const croppingSeasonOptions = [
  { value: '1', label: '1 Season (Wet or Dry)' },
  { value: '2', label: '2 Seasons (Wet & Dry)' },
  { value: '3', label: '3 Seasons (Continuous)' },
]

const riceVarieties = computed(() => marketplaceStore.riceVarieties || [])

const form = reactive({
  name: '',
  nickname: '',
  description: '',
  size: '',
  current_crop: '',
  soil_type: '',

  water_source: '',
  irrigation_type: '',
  water_access: '',
  drainage_quality: '',
  infrastructure_notes: '',
  planting_method: '',
  cropping_seasons: '',
  target_yield: '',
  previous_crop: '',
  notes: '',
  location: {
    provinceCode: '',
    cityCode: '',
    barangayCode: '',
    address: '',
    lat: '',
    lon: '',
  },
})

const completionScore = computed(() => {
  const essentials = [
    form.name,
    form.size,
    form.soil_type,
    form.location.lat,
  ]
  const optional = [
    form.description,
    form.current_crop,
    form.water_source,
    form.planting_method,
    form.target_yield,
  ]
  const essentialScore = (essentials.filter(Boolean).length / essentials.length) * 70
  const optionalScore = (optional.filter(Boolean).length / optional.length) * 30
  return Math.round(essentialScore + optionalScore)
})

const loadProvinces = async () => {
  try {
    const { data } = await axios.get('/api/locations/provinces')
    provinces.value = data
  } catch (err) {
    console.error('Failed to load provinces', err)
  }
}

const fetchCities = async () => {
  form.location.cityCode = ''
  form.location.barangayCode = ''
  form.location.address = ''
  cities.value = []
  barangays.value = []

  if (!form.location.provinceCode) return

  try {
    const { data } = await axios.get(`/api/locations/provinces/${form.location.provinceCode}/cities`)
    cities.value = data
  } catch (err) {
    console.error('Failed to load cities', err)
  }
}

const fetchBarangays = async () => {
  form.location.barangayCode = ''
  form.location.address = ''
  barangays.value = []

  if (!form.location.cityCode) return

  try {
    const { data } = await axios.get(`/api/locations/cities/${form.location.cityCode}/barangays`)
    barangays.value = data
  } catch (err) {
    console.error('Failed to load barangays', err)
  }
}

const setAddress = () => {
  const province = provinces.value.find(p => p.code === form.location.provinceCode)?.name || ''
  const city = cities.value.find(c => c.code === form.location.cityCode)?.name || ''
  const barangay = barangays.value.find(b => b.code === form.location.barangayCode)?.name || ''
  form.location.address = [barangay, city, province].filter(Boolean).join(', ')
  
  if (barangay && city && province) {
    geocodeLocation(barangay, city, province)
  }
}

const geocodeLocation = async (barangay, city, province) => {
  try {
    const query = `${barangay}, ${city}, ${province}, Philippines`
    const { data } = await axios.get('/api/geocode', { params: { q: query } })

    if (data && data.length > 0) {
      const { lat, lon } = data[0]
      const parsedLat = parseFloat(parseFloat(lat).toFixed(6))
      const parsedLon = parseFloat(parseFloat(lon).toFixed(6))
      
      form.location.lat = parsedLat
      form.location.lon = parsedLon
      
      if (map) {
         updateMapMarker()
         map.setView([parsedLat, parsedLon], 15)
      }
    }
  } catch (err) {
    console.warn('Geocoding failed', err)
  }
}

const initMap = () => {
  if (mapContainer.value && !map) {
    // Default to PH coords or farm location
    const defaultLat = form.location.lat || 8.157
    const defaultLon = form.location.lon || 125.128
    
    map = L.map(mapContainer.value).setView([defaultLat, defaultLon], 13)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      maxZoom: 19,
      attribution: '© OpenStreetMap'
    }).addTo(map)
    
    // Add marker if location exists
    if (form.location.lat && form.location.lon) {
       updateMapMarker()
    }
    
    map.on('click', (e) => {
       form.location.lat = parseFloat(e.latlng.lat.toFixed(6))
       form.location.lon = parseFloat(e.latlng.lng.toFixed(6))
       updateMapMarker()
    })
  }
}

const updateMapMarker = () => {
  if (!map) return
  const lat = form.location.lat
  const lon = form.location.lon
  if (lat && lon) {
    if (marker) {
      marker.setLatLng([lat, lon])
    } else {
      marker = L.marker([lat, lon], { draggable: true }).addTo(map)
      marker.on('dragend', (e) => {
         const { lat, lng } = e.target.getLatLng()
         form.location.lat = parseFloat(lat.toFixed(6))
         form.location.lon = parseFloat(lng.toFixed(6))
      })
    }
    map.flyTo([lat, lon], map.getZoom())
  }
}

const submitField = async () => {
  if (parseFloat(form.size) > availableFarmAreaWithCurrent.value) {
    error.value = `Field size cannot exceed available farm area (${availableFarmAreaWithCurrent.value.toFixed(2)} ha).`
    return
  }

  error.value = ''
  loading.value = true

  try {
    const payload = {
       ...form,
       // Flatten location if needed, or backend expects nested 'location'
       // Controller logic seemed to expect 'location' as string?
       // Let's check FieldController handling again.
       // It expects 'location' as value.
       // If I send object, Laravel might cast to string or JSON.
       // 'location' => $request->location ?? $request->input('location.address')
       location: form.location.address,
       latitude: form.location.lat,
       longitude: form.location.lon,
       
       // Handle other fields
    }
    
    await farmStore.updateField(fieldId, payload)
    router.push('/fields')
  } catch (err) {
    console.error("Submit error", err)
    error.value = err.response?.data?.message || 'Failed to update field.'
  } finally {
    loading.value = false
  }
}

const confirmDelete = async () => {
   if (confirm('Are you sure you want to delete this field? This action cannot be undone.')) {
      loading.value = true
      try {
         await farmStore.deleteField(fieldId)
         router.push('/fields')
      } catch (err) {
         error.value = "Failed to delete field."
         loading.value = false
      }
   }
}
</script>
