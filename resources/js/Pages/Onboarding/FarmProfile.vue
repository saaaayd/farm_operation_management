<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-7xl mx-auto space-y-8">
      <div class="text-center">
        <div class="mx-auto h-20 w-20 bg-gradient-to-br from-green-500 to-emerald-600 rounded-2xl flex items-center justify-center shadow-lg transform hover:scale-105 transition-transform duration-200">
          <svg class="h-10 w-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
          </svg>
        </div>
        <h2 class="mt-6 text-4xl font-bold bg-gradient-to-r from-green-600 to-emerald-600 bg-clip-text text-transparent">
          Welcome to RiceFARM!
        </h2>
        <p class="mt-3 text-base text-gray-600 max-w-md mx-auto">
          Let's set up your farm profile to get started with comprehensive rice farming management
        </p>
      </div>
      
      <form @submit.prevent="submitProfile" class="mt-8 space-y-6">
        <div class="bg-white shadow-xl rounded-2xl p-8 md:p-10 space-y-8 border border-gray-100">
          <!-- Farm Information -->
          <div class="border-b border-gray-200 pb-8">
            <div class="flex items-center mb-6">
              <div class="h-10 w-10 bg-green-100 rounded-lg flex items-center justify-center mr-3">
                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-gray-900">Farm Information</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="farm_name" class="block text-sm font-semibold text-gray-700 mb-2">Farm Name *</label>
                <input
                id="farm_name"
                v-model="form.farm_name"
                type="text"
                required
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                placeholder="Enter your farm name"
                />
              </div>
              
              <div>
                <label for="total_area" class="block text-sm font-semibold text-gray-700 mb-2">Total Farm Area (hectares) *</label>
                <input
                id="total_area"
                v-model="form.total_area"
                type="number"
                step="0.01"
                min="0"
                required
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                placeholder="0.00"
                />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
              <div>
                <label for="rice_area" class="block text-sm font-semibold text-gray-700 mb-2">Rice Cultivation Area (hectares) *</label>
                <input
                id="rice_area"
                v-model="form.rice_area"
                type="number"
                step="0.01"
                min="0"
                :max="form.total_area"
                required
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                placeholder="0.00"
                />
                <p v-if="form.rice_area && parseFloat(form.rice_area) > parseFloat(form.total_area || 0)" class="mt-1 text-xs text-red-600">
                  Rice area cannot exceed total farm area
                </p>
              </div>

              <div>
                <label for="farming_experience" class="block text-sm font-semibold text-gray-700 mb-2">Years of Rice Farming Experience</label>
                <input
                id="farming_experience"
                v-model="form.farming_experience"
                type="number"
                min="0"
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
                placeholder="0"
                />
              </div>
            </div>

            <div class="mt-6">
              <label for="farm_description" class="block text-sm font-semibold text-gray-700 mb-2">Farm Description</label>
              <textarea
              id="farm_description"
              v-model="form.farm_description"
              rows="3"
              class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 resize-none"
              placeholder="Brief description of your farm..."
              ></textarea>
            </div>
          </div>
          
          <!-- Farm Location -->
          <div class="border-b border-gray-200 pb-8">
            <div class="flex items-center mb-6">
              <div class="h-10 w-10 bg-blue-100 rounded-lg flex items-center justify-center mr-3">
                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
              </div>
              <h3 class="text-xl font-semibold text-gray-900">Farm Location</h3>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <label for="province" class="block text-sm font-semibold text-gray-700 mb-2">Province</label>
                <select
                id="province"
                v-model="form.provinceCode"
                @change="fetchCities"
                required
                class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 bg-white"
                >
                <option value="">Select Province</option>
                <option v-for="p in provinces" :key="p.code" :value="p.code">
                  {{ p.name }}
                </option>
              </select>
            </div>
            
            <div>
              <label for="city" class="block text-sm font-semibold text-gray-700 mb-2">City / Municipality</label>
              <select
              id="city"
              v-model="form.cityCode"
              @change="fetchBarangays"
              :disabled="!form.provinceCode"
              required
              class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 disabled:bg-gray-100 disabled:cursor-not-allowed bg-white"
              >
              <option value="">Select City or Municipality</option>
              <option v-for="c in cities" :key="c.code" :value="c.code">
                {{ c.name }}
              </option>
            </select>
          </div>
          
          <div class="md:col-span-2">
            <label for="barangay" class="block text-sm font-semibold text-gray-700 mb-2">Barangay</label>
            <select
            id="barangay"
            v-model="form.barangayCode"
            @change="setAddress"
            :disabled="!form.cityCode"
            required
            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 disabled:bg-gray-100 disabled:cursor-not-allowed bg-white"
            >
            <option value="">Select Barangay</option>
            <option v-for="b in barangays" :key="b.code" :value="b.code">
              {{ b.name }}
            </option>
          </select>
        </div>
      </div>
      
      <div v-if="form.address" class="mt-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl shadow-sm">
        <div class="flex items-start">
          <svg class="h-5 w-5 text-green-600 mt-0.5 mr-2 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <div>
            <p class="text-sm font-semibold text-gray-800 mb-1">Selected Address:</p>
            <p class="text-sm text-gray-700 mb-2">{{ form.address }}</p>
            <p class="text-xs text-gray-600 flex items-center">
              <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              Location will be automatically converted to coordinates for weather data
            </p>
          </div>
        </div>
      </div>
    </div>
    
    <!-- Field and Soil Information -->
    <div class="border-b border-gray-200 pb-8">
      <div class="flex items-center mb-6">
        <div class="h-10 w-10 bg-amber-100 rounded-lg flex items-center justify-center mr-3">
          <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-900">Field and Soil Information</h3>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div>
          <label for="field_name" class="block text-sm font-semibold text-gray-700 mb-2">Field Name *</label>
          <input
            type="text"
            id="field_name"
            v-model="form.field_name"
            placeholder="e.g., North Field, Main Field, etc."
            required
            class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
          />
          <p class="mt-2 text-xs text-gray-500 flex items-center">
            <svg class="h-3 w-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Name your primary rice field
          </p>
        </div>
        
        <div>
          <label for="soil_type" class="block text-sm font-semibold text-gray-700 mb-2">Primary Soil Type *</label>
          <select
          id="soil_type"
          v-model="form.soil_type"
          required
          class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 bg-white"
          >
          <option value="">Select soil type</option>
          <option value="clay">Clay</option>
          <option value="loam">Loam</option>
          <option value="sandy">Sandy</option>
          <option value="silt">Silt</option>
          <option value="clay_loam">Clay Loam</option>
          <option value="sandy_loam">Sandy Loam</option>
          <option value="silty_clay">Silty Clay</option>
          <option value="silty_loam">Silty Loam</option>
        </select>
      </div>
    </div>
    
    <div class="mt-6">
      <label for="soil_ph" class="block text-sm font-semibold text-gray-700 mb-2">Soil pH Level</label>
      <input
      id="soil_ph"
      v-model="form.soil_ph"
      type="number"
      step="0.1"
      min="3.0"
      max="10.0"
      class="block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
      placeholder="6.5"
      />
      <p class="mt-2 text-xs text-gray-500">Optimal pH for rice: 5.5 - 7.0</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
      <div>
        <label for="organic_matter_content" class="block text-sm font-semibold text-gray-700 mb-2">Organic Matter Content (%)</label>
        <input
        id="organic_matter_content"
        v-model="form.organic_matter_content"
        type="number"
        step="0.1"
        min="0"
        max="20"
        class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
        placeholder="2.5"
        />
      </div>

      <div>
        <label for="nitrogen_level" class="block text-sm font-semibold text-gray-700 mb-2">Nitrogen Level (ppm)</label>
        <input
        id="nitrogen_level"
        v-model="form.nitrogen_level"
        type="number"
        step="0.1"
        min="0"
        class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
        placeholder="20"
        />
      </div>

      <div>
        <label for="phosphorus_level" class="block text-sm font-semibold text-gray-700 mb-2">Phosphorus Level (ppm)</label>
        <input
        id="phosphorus_level"
        v-model="form.phosphorus_level"
        type="number"
        step="0.1"
        min="0"
        class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
        placeholder="15"
        />
      </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
      <div>
        <label for="potassium_level" class="block text-sm font-semibold text-gray-700 mb-2">Potassium Level (ppm)</label>
        <input
        id="potassium_level"
        v-model="form.potassium_level"
        type="number"
        step="0.1"
        min="0"
        class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
        placeholder="25"
        />
      </div>

      <div>
        <label for="elevation" class="block text-sm font-semibold text-gray-700 mb-2">Field Elevation (meters above sea level)</label>
        <input
        id="elevation"
        v-model="form.elevation"
        type="number"
        step="0.1"
        min="0"
        class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
        placeholder="100"
        />
      </div>
    </div>
  </div>

  <!-- Water Management -->
  <div class="border-b border-gray-200 pb-8">
    <div class="flex items-center mb-6">
      <div class="h-10 w-10 bg-cyan-100 rounded-lg flex items-center justify-center mr-3">
        <svg class="h-6 w-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
        </svg>
      </div>
      <h3 class="text-xl font-semibold text-gray-900">Water Management</h3>
    </div>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label for="water_source" class="block text-sm font-semibold text-gray-700 mb-2">Primary Water Source *</label>
        <select
        id="water_source"
        v-model="form.water_source"
        required
        class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 bg-white"
        >
        <option value="">Select water source</option>
        <option value="irrigation_canal">Irrigation Canal</option>
        <option value="river">River</option>
        <option value="well">Deep Well</option>
        <option value="shallow_well">Shallow Well</option>
        <option value="pond">Farm Pond</option>
        <option value="rainfall">Rainfall Dependent</option>
        <option value="spring">Natural Spring</option>
      </select>
    </div>
    
    <div>
      <label for="irrigation_type" class="block text-sm font-semibold text-gray-700 mb-2">Irrigation System *</label>
      <select
      id="irrigation_type"
      v-model="form.irrigation_type"
      required
      class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 bg-white"
      >
      <option value="">Select irrigation type</option>
      <option value="flood">Flood Irrigation</option>
      <option value="furrow">Furrow Irrigation</option>
      <option value="sprinkler">Sprinkler System</option>
      <option value="drip">Drip Irrigation</option>
      <option value="manual">Manual Watering</option>
      <option value="none">No Irrigation System</option>
    </select>
  </div>
  </div>
  
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
    <div>
      <label for="water_access" class="block text-sm font-semibold text-gray-700 mb-2">Water Access Quality *</label>
      <select
      id="water_access"
      v-model="form.water_access"
      required
      class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 bg-white"
      >
      <option value="">Select water access quality</option>
      <option value="excellent">Excellent - Always available</option>
      <option value="good">Good - Usually available</option>
      <option value="moderate">Moderate - Sometimes limited</option>
      <option value="poor">Poor - Often limited</option>
      <option value="very_poor">Very Poor - Rarely available</option>
    </select>
  </div>
  
  <div>
    <label for="drainage_quality" class="block text-sm font-semibold text-gray-700 mb-2">Field Drainage Quality *</label>
    <select
    id="drainage_quality"
    v-model="form.drainage_quality"
    required
    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 bg-white"
    >
    <option value="">Select drainage quality</option>
    <option value="excellent">Excellent - Quick drainage</option>
    <option value="good">Good - Adequate drainage</option>
    <option value="moderate">Moderate - Slow drainage</option>
    <option value="poor">Poor - Water logging issues</option>
  </select>
</div>
</div>
</div>

<!-- Rice Varieties and Farming Practices -->
<div class="border-b border-gray-200 pb-8">
  <div class="flex items-center mb-6">
    <div class="h-10 w-10 bg-indigo-100 rounded-lg flex items-center justify-center mr-3">
      <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    </div>
    <h3 class="text-xl font-semibold text-gray-900">Rice Varieties and Farming Practices</h3>
  </div>
  
  <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    <div class="flex flex-col">
      <label for="preferred_varieties" class="block text-sm font-semibold text-gray-700 mb-2">Preferred Rice Varieties</label>
      <div class="flex-1 flex flex-col justify-between border border-gray-300 rounded-lg p-4 bg-gray-50">
        <div v-for="variety in riceVarieties" :key="variety.value" class="flex items-center flex-1">
          <input
            :id="variety.value"
            v-model="form.preferred_varieties"
            :value="variety.value"
            type="checkbox"
            class="h-5 w-5 text-green-600 focus:ring-green-500 border-gray-300 rounded"
          />
          <label :for="variety.value" class="ml-3 text-base text-gray-700">
            {{ variety.label }}
          </label>
        </div>
      </div>
    </div>

    <div class="flex flex-col space-y-6">
      <div>
        <label for="planting_method" class="block text-sm font-semibold text-gray-700 mb-2">Preferred Planting Method</label>
        <select
        id="planting_method"
        v-model="form.planting_method"
        class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 bg-white"
        >
        <option value="">Select planting method</option>
        <option value="direct_seeding">Direct Seeding</option>
        <option value="transplanting">Transplanting</option>
        <option value="broadcasting">Broadcasting</option>
        <option value="drilling">Drilling</option>
      </select>
    </div>

    <div>
      <label for="previous_yield" class="block text-sm font-semibold text-gray-700 mb-2">Previous Average Yield (tons/ha)</label>
      <input
      id="previous_yield"
      v-model="form.previous_yield"
      type="number"
      step="0.1"
      min="0"
      class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
      placeholder="3.5"
      />
    </div>

    <div>
      <label for="target_yield" class="block text-sm font-semibold text-gray-700 mb-2">Target Yield (tons/ha)</label>
      <input
      id="target_yield"
      v-model="form.target_yield"
      type="number"
      step="0.1"
      min="0"
      class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400"
      placeholder="4.0"
      />
    </div>

    <div>
      <label for="cropping_seasons" class="block text-sm font-semibold text-gray-700 mb-2">Cropping Seasons per Year</label>
      <select
      id="cropping_seasons"
      v-model="form.cropping_seasons"
      class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 bg-white"
      >
      <option value="">Select seasons</option>
      <option value="1">1 Season (Wet or Dry)</option>
      <option value="2">2 Seasons (Wet & Dry)</option>
      <option value="3">3 Seasons (Continuous)</option>
    </select>
  </div>
  </div>
</div>

<div class="mt-6">
  <label for="farming_challenges" class="block text-sm font-semibold text-gray-700 mb-3">Main Farming Challenges (select all that apply)</label>
  <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
    <div v-for="challenge in farmingChallenges" :key="challenge.value" class="flex items-center">
      <input
        :id="challenge.value"
        v-model="form.farming_challenges"
        :value="challenge.value"
        type="checkbox"
        class="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
      />
      <label :for="challenge.value" class="ml-2 text-sm text-gray-700">
        {{ challenge.label }}
      </label>
    </div>
  </div>
</div>
</div>

<!-- Additional Information -->
<div>
  <div class="flex items-center mb-6">
    <div class="h-10 w-10 bg-gray-100 rounded-lg flex items-center justify-center mr-3">
      <svg class="h-6 w-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
      </svg>
    </div>
    <h3 class="text-xl font-semibold text-gray-900">Additional Information</h3>
  </div>
  
  <div>
    <label for="notes" class="block text-sm font-semibold text-gray-700 mb-2">Additional Notes</label>
    <textarea
    id="notes"
    v-model="form.notes"
    rows="3"
    class="mt-1 block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 hover:border-gray-400 resize-none"
    placeholder="Any additional information about your farm..."
    ></textarea>
  </div>
</div>
</div>

<!-- Error Message -->
<div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4">
  <div class="flex">
    <div class="flex-shrink-0">
      <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
      </svg>
    </div>
    <div class="ml-3">
      <p class="text-sm text-red-800">{{ error }}</p>
    </div>
  </div>
</div>

<!-- Submit Button -->
<div class="flex justify-end pt-6">
  <button
  type="submit"
  :disabled="loading"
  class="group relative w-full md:w-auto min-w-[200px] flex justify-center items-center py-4 px-8 border border-transparent text-base font-semibold rounded-xl text-white bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50 disabled:cursor-not-allowed shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 transition-all duration-200"
  >
  <span v-if="loading" class="absolute left-0 inset-y-0 flex items-center pl-4">
    <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>
  </span>
  <span v-if="!loading" class="mr-2">
    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
    </svg>
  </span>
  {{ loading ? 'Setting up your farm...' : 'Complete Setup' }}
</button>
</div>
</form>
</div>
</div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useFarmStore } from '@/stores/farm';
import { useAuthStore } from '@/stores/auth';
import axios from 'axios';

const router = useRouter();
const farmStore = useFarmStore();
const authStore = useAuthStore();

const loading = ref(false);
const error = ref('');

const provinces = ref([]);
const cities = ref([]);
const barangays = ref([]);

const form = reactive({
  // Basic Information
  farm_name: '',
  total_area: '',
  rice_area: '',
  farming_experience: '',
  farm_description: '',
  
  // Location (Philippine system)
  provinceCode: '',
  cityCode: '',
  barangayCode: '',
  address: '',
  
  // Field Information
  field_name: '',
  
  // Soil Information
  soil_type: '',
  soil_ph: '',
  organic_matter_content: '',
  nitrogen_level: '',
  phosphorus_level: '',
  potassium_level: '',
  elevation: '',
  
  // Water Management
  water_source: '',
  irrigation_type: '',
  water_access: '',
  drainage_quality: '',
  
  // Rice Varieties and Practices
  preferred_varieties: [],
  planting_method: '',
  previous_yield: '',
  target_yield: '',
  cropping_seasons: '',
  farming_challenges: [],
  
  // Additional
  notes: '',
});

const riceVarieties = [
  { value: 'ir64', label: 'IR64 - High yielding variety' },
  { value: 'jasmine', label: 'Jasmine Rice - Aromatic' },
  { value: 'basmati', label: 'Basmati - Premium aromatic' },
  { value: 'brown_rice', label: 'Brown Rice - Nutritious' },
  { value: 'glutinous', label: 'Glutinous Rice - Sticky' },
  { value: 'red_rice', label: 'Red Rice - Antioxidant rich' },
  { value: 'black_rice', label: 'Black Rice - Superfood' },
  { value: 'local_variety', label: 'Local Traditional Variety' }
];

const farmingChallenges = [
  { value: 'pests', label: 'Pest Management' },
  { value: 'diseases', label: 'Disease Control' },
  { value: 'water_shortage', label: 'Water Shortage' },
  { value: 'flooding', label: 'Flooding Issues' },
  { value: 'soil_fertility', label: 'Soil Fertility' },
  { value: 'weather', label: 'Weather Variability' },
  { value: 'labor', label: 'Labor Shortage' },
  { value: 'market_access', label: 'Market Access' },
  { value: 'input_costs', label: 'High Input Costs' },
  { value: 'storage', label: 'Storage Facilities' }
];

// Load provinces on mount
onMounted(async () => {
  try {
    const res = await axios.get('/api/locations/provinces');
    provinces.value = res.data;
  } catch (err) {
    error.value = 'Failed to load provinces. Please refresh the page.';
  }
});

// Fetch cities when province changes
const fetchCities = async () => {
  if (!form.provinceCode) return;
  try {
    const res = await axios.get(`/api/locations/provinces/${form.provinceCode}/cities`);
    cities.value = res.data;
    barangays.value = [];
    form.cityCode = '';
    form.barangayCode = '';
    form.address = '';
  } catch (err) {
    error.value = 'Failed to load cities. Please try again.';
  }
};

// Fetch barangays when city changes
const fetchBarangays = async () => {
  if (!form.cityCode) return;
  try {
    const res = await axios.get(`/api/locations/cities/${form.cityCode}/barangays`);
    barangays.value = res.data;
    form.barangayCode = '';
    form.address = '';
  } catch (err) {
    error.value = 'Failed to load barangays. Please try again.';
  }
};

// Build address string when barangay selected
const setAddress = () => {
  const province = provinces.value.find(p => p.code === form.provinceCode)?.name || '';
  const city = cities.value.find(c => c.code === form.cityCode)?.name || '';
  const barangay = barangays.value.find(b => b.code === form.barangayCode)?.name || '';
  form.address = `${barangay}, ${city}, ${province}`;
};

const submitProfile = async () => {
  // Validate form
  if (!form.farm_name || !form.total_area || !form.rice_area || !form.address || !form.field_name) {
    error.value = 'Please fill in all required fields.';
    return;
  }
  
  if (parseFloat(form.rice_area) > parseFloat(form.total_area)) {
    error.value = 'Rice cultivation area cannot exceed total farm area.';
    return;
  }
  
  if (!form.soil_type) {
    error.value = 'Please select your soil type.';
    return;
  }
  
  if (!form.water_source || !form.irrigation_type || !form.water_access || !form.drainage_quality) {
    error.value = 'Please fill in all water management fields.';
    return;
  }
  
  loading.value = true;
  error.value = '';
  
  try {
    // Map form data to match backend expectations
    // Use address as location for geocoding
    const profileData = {
      farm_name: form.farm_name,
      farm_location: form.address, // Use Philippine address as location string for geocoding
      total_area: form.total_area,
      rice_area: form.rice_area,
      farming_experience: form.farming_experience || null,
      farm_description: form.farm_description || null,
      
      // Field Information
      field_name: form.field_name,
      
      // Soil Information
      soil_type: form.soil_type,
      soil_ph: form.soil_ph || null,
      organic_matter_content: form.organic_matter_content || null,
      nitrogen_level: form.nitrogen_level || null,
      phosphorus_level: form.phosphorus_level || null,
      potassium_level: form.potassium_level || null,
      elevation: form.elevation || null,
      
      // Water Management
      water_source: form.water_source,
      irrigation_type: form.irrigation_type,
      water_access: form.water_access,
      drainage_quality: form.drainage_quality,
      
      // Rice Varieties and Practices
      preferred_varieties: form.preferred_varieties || [],
      planting_method: form.planting_method || null,
      previous_yield: form.previous_yield || null,
      target_yield: form.target_yield || null,
      cropping_seasons: form.cropping_seasons || null,
      farming_challenges: form.farming_challenges || [],
    };
    
    await farmStore.createRiceFarmProfile(profileData);
    
    // Update user data to reflect farm profile completion
    await authStore.fetchUser();
    
    // Redirect to dashboard
    router.push('/dashboard');
  } catch (err) {
    error.value = err.message || 'Failed to create farm profile. Please try again.';
  } finally {
    loading.value = false;
  }
};
</script>