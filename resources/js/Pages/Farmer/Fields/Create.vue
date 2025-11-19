<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-6xl mx-auto space-y-8">
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
          <h1 class="mt-4 text-3xl font-bold text-gray-900">Create New Field</h1>
          <p class="mt-2 text-base text-gray-600 max-w-2xl">
            Capture detailed field information so we can tailor planting plans, weather insights, and soil recommendations for this specific parcel.
          </p>
        </div>
        <div class="bg-white/90 border border-emerald-100 rounded-2xl px-5 py-4 shadow-md">
          <p class="text-xs uppercase tracking-wide text-gray-500">Field Completeness</p>
          <p class="text-2xl font-semibold text-emerald-600 mt-1">{{ completionScore }}%</p>
          <p class="text-sm text-gray-500">Add as much detail as possible for better insights.</p>
        </div>
      </div>

      <div v-if="error" class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-800">
        {{ error }}
      </div>

      <form @submit.prevent="submitField" class="space-y-8">
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
                  <option v-for="variety in riceVarieties" :key="variety.value" :value="variety.value">
                    {{ variety.label }}
                  </option>
                </optgroup>
                <optgroup label="Other Crops">
                  <option value="corn">Corn</option>
                  <option value="wheat">Wheat</option>
                  <option value="soybeans">Soybeans</option>
                  <option value="vegetables">Vegetables</option>
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
          </div>

          <div v-if="form.location.address" class="bg-gradient-to-r from-emerald-50 to-green-50 border border-emerald-200 rounded-xl p-4">
            <div class="flex items-start">
              <svg class="h-5 w-5 text-emerald-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <div class="flex-1">
                <p class="text-sm font-semibold text-gray-800 mb-1">Selected Address:</p>
                <p class="text-sm text-gray-700">{{ form.location.address }}</p>
                <p class="text-xs text-gray-600 mt-2 flex items-center">
                  <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Address is automatically generated from your location selections
                </p>
              </div>
            </div>
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              Field Location on Map *
            </label>
            <p class="text-xs text-gray-600 mb-3">Click on the map to set the field's center coordinates</p>
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
                  placeholder="Click map or enter"
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
                  placeholder="Click map or enter"
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
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Soil pH</label>
              <input
                v-model.number="form.soil_ph"
                type="number"
                step="0.1"
                min="3"
                max="10"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                placeholder="6.5"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Organic Matter (%)</label>
              <input
                v-model.number="form.organic_matter_content"
                type="number"
                step="0.1"
                min="0"
                max="20"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                placeholder="2.5"
              />
            </div>
            <div>
              <label class="block text-sm font-semibold text-gray-700 mb-2">Elevation (m)</label>
              <input
                v-model.number="form.elevation"
                type="number"
                step="0.1"
                min="0"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                placeholder="100"
              />
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div v-for="nutrient in nutrientInputs" :key="nutrient.id">
              <label class="block text-sm font-semibold text-gray-700 mb-2">{{ nutrient.label }} (ppm)</label>
              <input
                v-model.number="form[nutrient.model]"
                type="number"
                step="0.1"
                min="0"
                class="w-full rounded-lg border border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-2 focus:ring-emerald-500 transition"
                :placeholder="nutrient.placeholder"
              />
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
                  <option v-for="variety in riceVarieties" :key="variety.value" :value="variety.value">
                    {{ variety.label }}
                  </option>
                </optgroup>
                <optgroup label="Other Crops">
                  <option value="corn">Corn</option>
                  <option value="wheat">Wheat</option>
                  <option value="soybeans">Soybeans</option>
                  <option value="vegetables">Vegetables</option>
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

        <div class="flex flex-col sm:flex-row gap-4 justify-end pt-4">
          <button
            type="button"
            @click="router.push('/fields')"
            class="inline-flex items-center justify-center px-6 py-3 rounded-xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            type="submit"
            :disabled="loading"
            class="inline-flex items-center justify-center px-8 py-3 rounded-xl font-semibold text-white bg-gradient-to-r from-emerald-600 to-green-600 shadow-lg hover:shadow-xl hover:from-emerald-700 hover:to-green-700 disabled:opacity-60 disabled:cursor-not-allowed"
          >
            <LoadingSpinner v-if="loading" class="mr-2" />
            {{ loading ? 'Saving field...' : 'Create Field' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'
import LoadingSpinner from '@/Components/UI/LoadingSpinner.vue'
import { useFarmStore } from '@/stores/farm'

const router = useRouter()
const farmStore = useFarmStore()

const loading = ref(false)
const error = ref('')

const provinces = ref([])
const cities = ref([])
const barangays = ref([])

const mapContainer = ref(null)
let map = null
let marker = null

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

const riceVarieties = [
  { value: 'ir64', label: 'IR64 - High yielding variety' },
  { value: 'jasmine', label: 'Jasmine Rice - Aromatic' },
  { value: 'basmati', label: 'Basmati - Premium aromatic' },
  { value: 'brown_rice', label: 'Brown Rice - Nutritious' },
  { value: 'glutinous', label: 'Glutinous Rice - Sticky' },
  { value: 'red_rice', label: 'Red Rice - Antioxidant rich' },
  { value: 'black_rice', label: 'Black Rice - Superfood' },
  { value: 'local_variety', label: 'Local Traditional Variety' },
]

const nutrientInputs = [
  { id: 'nitrogen', label: 'Nitrogen', model: 'nitrogen_level', placeholder: '20' },
  { id: 'phosphorus', label: 'Phosphorus', model: 'phosphorus_level', placeholder: '15' },
  { id: 'potassium', label: 'Potassium', model: 'potassium_level', placeholder: '25' },
]

const form = reactive({
  name: '',
  nickname: '',
  description: '',
  size: '',
  current_crop: '',
  soil_type: '',
  soil_ph: '',
  organic_matter_content: '',
  nitrogen_level: '',
  phosphorus_level: '',
  potassium_level: '',
  elevation: '',
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
    form.location.provinceCode,
    form.location.cityCode,
    form.location.barangayCode,
    form.location.lat,
    form.location.lon,
    form.soil_type,
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
    error.value = 'Failed to load provinces. Please refresh the page.'
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
    error.value = 'Failed to load cities. Please try again.'
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
    error.value = 'Failed to load barangays. Please try again.'
  }
}

const setAddress = () => {
  const province = provinces.value.find(p => p.code === form.location.provinceCode)?.name || ''
  const city = cities.value.find(c => c.code === form.location.cityCode)?.name || ''
  const barangay = barangays.value.find(b => b.code === form.location.barangayCode)?.name || ''
  form.location.address = [barangay, city, province].filter(Boolean).join(', ')
  
  // Geocode the address and update map
  if (barangay && city && province) {
    geocodeLocation(barangay, city, province)
  }
}

const geocodeLocation = async (barangay, city, province) => {
  try {
    // Use backend proxy to avoid CORS issues and set proper User-Agent
    const query = `${barangay}, ${city}, ${province}, Philippines`
    const response = await axios.get('/api/geocode', {
      params: {
        q: query,
      },
    })

    const data = response.data

    if (data && data.length > 0) {
      const { lat: geocodeLat, lon: geocodeLon } = data[0]
      const parsedLat = parseFloat(parseFloat(geocodeLat).toFixed(6))
      const parsedLon = parseFloat(parseFloat(geocodeLon).toFixed(6))
      
      form.location.lat = parsedLat
      form.location.lon = parsedLon
      
      // Update map view with higher zoom for barangay-level
      if (map) {
        if (marker) {
          marker.setLatLng([parsedLat, parsedLon])
        } else {
          marker = L.marker([parsedLat, parsedLon], {
            draggable: true,
          }).addTo(map)
          marker.on('dragend', (e) => {
            const { lat, lng } = e.target.getLatLng()
            form.location.lat = parseFloat(lat.toFixed(6))
            form.location.lon = parseFloat(lng.toFixed(6))
          })
        }
        map.setView([parsedLat, parsedLon], 15) // Higher zoom for barangay-level location
      }
    }
  } catch (err) {
    console.warn('Geocoding failed, user can set location manually on map:', err)
    // Don't show error to user, they can still use the map
  }
}

const parseNumber = (value) => {
  return value === '' || value === null || typeof value === 'undefined'
    ? null
    : Number(value)
}

const submitField = async () => {
  if (!form.name || !form.size || !form.soil_type || !form.location.provinceCode || !form.location.cityCode || !form.location.barangayCode || form.location.lat === '' || form.location.lon === '') {
    error.value = 'Please complete all required fields before saving.'
    return
  }
  
  // Ensure address is set from location selections
  if (!form.location.address) {
    setAddress()
  }

  error.value = ''
  loading.value = true

  const provinceName = provinces.value.find(p => p.code === form.location.provinceCode)?.name || null
  const cityName = cities.value.find(c => c.code === form.location.cityCode)?.name || null
  const barangayName = barangays.value.find(b => b.code === form.location.barangayCode)?.name || null

  const payload = {
    name: form.name,
    description: form.description || null,
    size: parseNumber(form.size),
    soil_type: form.soil_type,
    soil_ph: parseNumber(form.soil_ph),
    organic_matter_content: parseNumber(form.organic_matter_content),
    nitrogen_level: parseNumber(form.nitrogen_level),
    phosphorus_level: parseNumber(form.phosphorus_level),
    potassium_level: parseNumber(form.potassium_level),
    elevation: parseNumber(form.elevation),
    water_source: form.water_source || null,
    irrigation_type: form.irrigation_type || null,
    water_access: form.water_access || null,
    drainage_quality: form.drainage_quality || null,
    planting_method: form.planting_method || null,
    cropping_seasons: form.cropping_seasons || null,
    target_yield: parseNumber(form.target_yield),
    previous_crop: form.previous_crop || null,
    current_crop: form.current_crop || null,
    infrastructure_notes: form.infrastructure_notes || null,
    notes: form.notes || null,
    location: {
      address: form.location.address,
      lat: parseNumber(form.location.lat),
      lon: parseNumber(form.location.lon),
      province: provinceName,
      city: cityName,
      barangay: barangayName,
    },
  }

  try {
    await farmStore.createField(payload)
    router.push('/fields')
  } catch (err) {
    console.error('Failed to create field', err)
    error.value = err.response?.data?.message || err.userMessage || 'Failed to create field. Please review the details and try again.'
  } finally {
    loading.value = false
  }
}

const initMap = () => {
  if (!mapContainer.value) return

  // Default to Philippines center (Manila area)
  const defaultLat = 14.5995
  const defaultLon = 120.9842

  // Load Leaflet dynamically
  if (typeof L === 'undefined') {
    // Load Leaflet CSS
    const link = document.createElement('link')
    link.rel = 'stylesheet'
    link.href = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css'
    link.integrity = 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY='
    link.crossOrigin = ''
    document.head.appendChild(link)

    // Load Leaflet JS
    const script = document.createElement('script')
    script.src = 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js'
    script.integrity = 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo='
    script.crossOrigin = ''
    script.onload = () => {
      createMap()
    }
    document.head.appendChild(script)
  } else {
    createMap()
  }

  function createMap() {
    // Initialize map centered on Philippines
    map = L.map(mapContainer.value).setView([defaultLat, defaultLon], 6)

    // Add OpenStreetMap tiles
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
      attribution: '© OpenStreetMap contributors',
      maxZoom: 19,
    }).addTo(map)

    // Add click handler
    map.on('click', (e) => {
      const { lat, lng } = e.latlng
      form.location.lat = parseFloat(lat.toFixed(6))
      form.location.lon = parseFloat(lng.toFixed(6))
      updateMarkerPosition()
    })

    // If coordinates already exist, set marker
    if (form.location.lat && form.location.lon) {
      updateMarkerPosition()
    }
  }
}

const updateMarkerPosition = () => {
  if (!map || !form.location.lat || !form.location.lon) return

  const lat = parseFloat(form.location.lat)
  const lon = parseFloat(form.location.lon)

  if (marker) {
    marker.setLatLng([lat, lon])
  } else {
    marker = L.marker([lat, lon], {
      draggable: true,
    }).addTo(map)

    marker.on('dragend', (e) => {
      const { lat, lng } = e.target.getLatLng()
      form.location.lat = parseFloat(lat.toFixed(6))
      form.location.lon = parseFloat(lng.toFixed(6))
    })
  }

  map.setView([lat, lon], 13)
}

const updateMapMarker = () => {
  if (form.location.lat && form.location.lon) {
    updateMarkerPosition()
  }
}

onMounted(() => {
  loadProvinces()
  // Initialize map after a short delay to ensure DOM is ready
  setTimeout(() => {
    initMap()
  }, 100)
})
</script>

