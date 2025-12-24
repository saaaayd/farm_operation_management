<template>
  <form @submit.prevent="submitForm" class="space-y-6 w-full">
    <div v-if="form.errors.general" class="p-4 bg-red-50 border border-red-300 text-red-800 rounded-md">
      <h3 class="font-medium">An error occurred:</h3>
      <p>{{ form.errors.general }}</p>
    </div>

    <!-- Section 1: Core Planting Details -->
    <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100">
      <div class="flex items-center mb-6">
        <div class="h-12 w-12 rounded-xl bg-emerald-100 flex items-center justify-center mr-4 shadow-sm">
          <!-- Sprout Icon -->
          <svg class="h-6 w-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" /></svg>
        </div>
        <div>
          <h3 class="text-xl font-bold text-gray-900 tracking-tight">Planting Details</h3>
          <p class="text-sm text-gray-500 font-medium">Core information about the crop cycle.</p>
        </div>
      </div>

      <div class="space-y-8">
        <!-- Top Row: Location & Basic Info -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Field -->
          <div>
            <label for="field_id" class="block text-sm font-bold text-gray-700 mb-2">Field <span class="text-red-500">*</span></label>
            <select
              id="field_id"
              v-model="form.data.field_id"
              required
              class="w-full rounded-xl border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all font-medium text-gray-700"
              :class="{ 'border-red-500': form.errors.field_id }"
            >
              <option value="" disabled>Select a field</option>
              <option v-for="field in farmStore.fields" :key="field.id" :value="field.id">
                {{ field.name }} ({{ field.size }} ha)
              </option>
            </select>
            <p v-if="form.errors.field_id" class="mt-1 text-xs text-red-600 font-medium">{{ form.errors.field_id }}</p>
          </div>

          <!-- Crop Name -->
          <div>
            <label for="crop_type" class="block text-sm font-bold text-gray-700 mb-2">Crop Name</label>
            <input
              type="text"
              id="crop_type"
              v-model="form.data.crop_type"
              placeholder="e.g., Rice"
              class="w-full rounded-xl border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all"
              :class="{ 'border-red-500': form.errors.crop_type }"
            />
            <p v-if="form.errors.crop_type" class="mt-1 text-xs text-red-600 font-medium">{{ form.errors.crop_type }}</p>
          </div>

          <!-- Season -->
          <div>
            <label for="season" class="block text-sm font-bold text-gray-700 mb-2">Season</label>
            <select
              id="season"
              v-model="form.data.season"
              class="w-full rounded-xl border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all text-gray-700"
              :class="{ 'border-red-500': form.errors.season }"
            >
              <option value="wet">Rainy Season (Jun - Nov)</option>
              <option value="dry">Dry Season (Dec - May)</option>
            </select>
            <p v-if="form.errors.season" class="mt-1 text-xs text-red-600 font-medium">{{ form.errors.season }}</p>
            <p class="mt-1.5 text-xs text-gray-500 flex items-center">
               <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
               Auto-detected from planting date.
            </p>
          </div>
        </div>

        <!-- Middle Section: Source Configuration (Highlighted) -->
        <div class="p-6 bg-gradient-to-br from-emerald-50/50 to-teal-50/50 rounded-2xl border border-emerald-100/80">
          <label class="block text-sm font-bold text-gray-800 mb-4 uppercase tracking-wide">Planting Source</label>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-start">
            <!-- Source Type Selection (Radio Cards) -->
            <div class="flex flex-col gap-3">
              <label 
                class="relative flex items-center p-4 cursor-pointer rounded-xl border-2 transition-all duration-200 group hover:shadow-md"
                :class="sourceType === 'direct' ? 'border-emerald-500 bg-white shadow-md' : 'border-gray-200 bg-white/50 text-gray-500 hover:border-emerald-200'"
              >
                <input type="radio" v-model="sourceType" value="direct" class="sr-only">
                <div class="h-10 w-10 rounded-full flex items-center justify-center mr-4 transition-colors"
                  :class="sourceType === 'direct' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400'"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                </div>
                <div>
                  <span class="block text-base font-bold transition-colors" :class="sourceType === 'direct' ? 'text-gray-900' : 'text-gray-600'">Direct / Inventory</span>
                  <span class="block text-xs mt-0.5 font-medium" :class="sourceType === 'direct' ? 'text-emerald-600' : 'text-gray-400'">Use seeds directly from inventory stock</span>
                </div>
                <div class="ml-auto" v-if="sourceType === 'direct'">
                  <svg class="w-6 h-6 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                </div>
              </label>

              <label 
                class="relative flex items-center p-4 cursor-pointer rounded-xl border-2 transition-all duration-200 group hover:shadow-md"
                :class="sourceType === 'nursery' ? 'border-emerald-500 bg-white shadow-md' : 'border-gray-200 bg-white/50 text-gray-500 hover:border-emerald-200'"
              >
                <input type="radio" v-model="sourceType" value="nursery" class="sr-only">
                <div class="h-10 w-10 rounded-full flex items-center justify-center mr-4 transition-colors"
                  :class="sourceType === 'nursery' ? 'bg-emerald-100 text-emerald-600' : 'bg-gray-100 text-gray-400'"
                >
                  <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z"></path></svg>
                </div>
                <div>
                  <span class="block text-base font-bold transition-colors" :class="sourceType === 'nursery' ? 'text-gray-900' : 'text-gray-600'">Nursery (Transplant)</span>
                  <span class="block text-xs mt-0.5 font-medium" :class="sourceType === 'nursery' ? 'text-emerald-600' : 'text-gray-400'">Use ready seedlings from nursery</span>
                </div>
                <div class="ml-auto" v-if="sourceType === 'nursery'">
                   <svg class="w-6 h-6 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                </div>
              </label>
            </div>

            <!-- Dependent Dropdowns with nicer styling -->
            <div class="space-y-6">
              <div v-if="sourceType === 'direct'" class="animate-fadeIn">
                <label for="inventory_item_id" class="block text-sm font-bold text-gray-700 mb-2">Select Seeds from Inventory</label>
                <div class="relative">
                  <select
                    id="inventory_item_id"
                    v-model="form.data.inventory_item_id"
                    class="w-full rounded-xl border-gray-300 pl-4 pr-10 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all font-medium text-gray-800 appearance-none"
                    :class="{ 'border-red-500': form.errors.inventory_item_id }"
                  >
                    <option value="">-- Choose Seeds --</option>
                    <option
                      v-for="item in inventoryStore.riceSeeds"
                      :key="item.id"
                      :value="item.id"
                      :disabled="item.current_stock <= 0"
                    >
                      {{ item.name }} ({{ item.current_stock }} {{ item.unit || 'kg' }} avail)
                    </option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </div>
                </div>
                <p v-if="form.errors.inventory_item_id" class="mt-1 text-xs text-red-600 font-medium">{{ form.errors.inventory_item_id }}</p>
                
                <div v-if="selectedRiceVariety" class="mt-3 p-3 bg-blue-50/80 rounded-lg text-sm text-blue-700 border border-blue-100 flex items-start animate-fadeIn">
                   <svg class="w-5 h-5 mr-2 mt-0.5 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                   <span><strong>Auto-Link:</strong> This item is matched to the <strong>{{ selectedRiceVariety.name }}</strong> variety.</span>
                </div>
              </div>

              <div v-else class="animate-fadeIn">
                <label for="seed_planting_id" class="block text-sm font-bold text-gray-700 mb-2">Select Ready Seedlings</label>
                <div class="relative">
                  <select
                    id="seed_planting_id"
                    v-model="form.data.seed_planting_id"
                    class="w-full rounded-xl border-gray-300 pl-4 pr-10 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all font-medium text-gray-800 appearance-none"
                    :class="{ 'border-red-500': form.errors.seed_planting_id }"
                  >
                    <option value="">-- Choose Seedlings --</option>
                    <option
                      v-for="planting in readySeedPlantings"
                      :key="planting.id"
                      :value="planting.id"
                    >
                      {{ planting.batch_id ? `[${planting.batch_id}] ` : '' }}{{ planting.rice_variety?.name }} ({{ planting.quantity }} {{ planting.unit }})
                    </option>
                  </select>
                  <div class="absolute inset-y-0 right-0 flex items-center px-4 pointer-events-none text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </div>
                </div>
                 <p v-if="readySeedPlantings.length === 0" class="mt-2 text-sm text-amber-600 font-medium flex items-center">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    No ready seedlings found in nursery.
                 </p>
              </div>

              <!-- Rice Variety (Always visible but auto-filled) -->
              <div>
                 <label for="rice_variety_id" class="block text-sm font-bold text-gray-700 mb-2 flex justify-between items-center">
                   <span>Rice Variety</span>
                   <span class="text-xs font-normal text-gray-500 bg-gray-100 px-2 py-0.5 rounded-full">Auto-matched</span>
                 </label>
                 <select
                   id="rice_variety_id"
                   v-model="form.data.rice_variety_id"
                   class="w-full rounded-xl border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all font-medium text-gray-800"
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
                 <p v-if="form.errors.rice_variety_id" class="mt-1 text-xs text-red-600 font-medium">{{ form.errors.rice_variety_id }}</p>
                 <p v-if="selectedRiceVariety" class="mt-2 text-xs text-gray-500 font-medium flex items-center">
                    <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Maturity: {{ selectedRiceVariety.maturity_days }} days
                 </p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Section: Schedule & Status -->
    <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100">
      <div class="flex items-center mb-6">
        <div class="h-12 w-12 rounded-xl bg-indigo-50 flex items-center justify-center mr-4 shadow-sm">
           <svg class="h-6 w-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
        </div>
        <div>
          <h3 class="text-xl font-bold text-gray-900 tracking-tight">Schedule & Status</h3>
          <p class="text-sm text-gray-500 font-medium">Timeline and current state.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div>
          <label for="planting_date" class="block text-sm font-bold text-gray-700 mb-2">Planting Date <span class="text-red-500">*</span></label>
          <input
            type="date"
            id="planting_date"
            v-model="form.data.planting_date"
            required
            class="w-full rounded-xl border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all font-medium text-gray-800"
            :class="{ 'border-red-500': form.errors.planting_date }"
          />
          <p v-if="form.errors.planting_date" class="mt-1 text-xs text-red-600 font-medium">{{ form.errors.planting_date }}</p>
        </div>
        
        <div>
          <label for="expected_harvest_date" class="block text-sm font-bold text-gray-700 mb-2">
            Expected Harvest Date
            <span v-if="isAutoCalculated" class="text-xs font-normal text-emerald-600 bg-emerald-50 ml-2 px-2 py-0.5 rounded-full border border-emerald-100">Auto</span>
          </label>
          <input
            type="date"
            id="expected_harvest_date"
            v-model="form.data.expected_harvest_date"
            :min="minHarvestDate"
            class="w-full rounded-xl border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all font-medium text-gray-800"
            :class="{ 'border-red-500': form.errors.expected_harvest_date, 'bg-gray-50': isAutoCalculated }"
            @input="onHarvestDateManualChange"
          />
          <p v-if="form.errors.expected_harvest_date" class="mt-1 text-xs text-red-600 font-medium">{{ form.errors.expected_harvest_date }}</p>
          <p v-if="isAutoCalculated && !form.errors.expected_harvest_date" class="mt-1.5 text-xs text-gray-500 flex items-start">
             <svg class="w-3 h-3 mr-1 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
             <span>Calculated from planting date + <strong>{{ selectedRiceVariety?.maturity_days || 'variety' }}</strong> days.</span>
          </p>
          <p v-else-if="!isAutoCalculated && form.data.planting_date && form.data.expected_harvest_date" class="mt-1.5 text-xs text-gray-500">
            You can manually adjust this date if needed.
          </p>
        </div>

        <div>
          <label for="status" class="block text-sm font-bold text-gray-700 mb-2">Status</label>
          <select
            id="status"
            v-model="form.data.status"
            class="w-full rounded-xl border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all font-medium text-gray-800"
            :class="{ 'border-red-500': form.errors.status }"
          >
            <!-- When creating new planting -->
            <template v-if="!isEditMode">
              <option value="planned">Planned (Future)</option>
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
          <p v-if="form.errors.status" class="mt-1 text-xs text-red-600 font-medium">{{ form.errors.status }}</p>
        </div>
      </div>
    </div>

    <!-- Section 2: Method & Quantity -->
    <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100">
      <div class="flex items-center mb-6">
        <div class="h-12 w-12 rounded-xl bg-blue-50 flex items-center justify-center mr-4 shadow-sm">
           <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3" /></svg>
        </div>
        <div>
          <h3 class="text-xl font-bold text-gray-900 tracking-tight">Method & Quantity</h3>
          <p class="text-sm text-gray-500 font-medium">How and how much to plant.</p>
        </div>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div>
            <label for="planting_method" class="block text-sm font-bold text-gray-700 mb-2">Planting Method</label>
            <select
              id="planting_method"
              v-model="form.data.planting_method"
              class="w-full rounded-xl border-gray-300 px-4 py-3 shadow-sm bg-white focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all font-medium text-gray-800"
              :class="{ 'border-red-500': form.errors.planting_method }"
            >
              <option 
                v-for="method in availablePlantingMethods" 
                :key="method.value" 
                :value="method.value"
              >
                {{ method.label }}
              </option>
            </select>
            <p v-if="form.errors.planting_method" class="mt-1 text-xs text-red-600 font-medium">{{ form.errors.planting_method }}</p>
          </div>
          
          <div>
            <label for="area_planted" class="block text-sm font-bold text-gray-700 mb-2">
              Area Planted (ha)
              <span v-if="selectedField" class="ml-2 px-2 py-0.5 rounded-full text-xs font-medium border"
                :class="availableArea < 0.1 ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'"
              >
                Available: {{ formatNumber(availableArea) }} ha
              </span>
            </label>
            <input
              type="number"
              step="0.01"
              min="0"
              :max="availableArea"
              id="area_planted"
              v-model.number="form.data.area_planted"
              class="w-full rounded-xl border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all font-medium"
              :class="{ 'border-red-500': form.errors.area_planted || areaExceedsField }"
              @input="validateAreaPlanted"
            />
            <p v-if="form.errors.area_planted" class="mt-1 text-xs text-red-600 font-medium">{{ form.errors.area_planted }}</p>
            <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 -translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-1">
              <p v-if="areaExceedsField" class="mt-1 text-xs text-red-600 font-medium bg-red-50 p-2 rounded-lg border border-red-100 flex items-start">
                <svg class="w-4 h-4 mr-1 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                <span>Cannot exceed available area ({{ formatNumber(availableArea) }} ha)</span>
              </p>
            </transition>
          </div>

          <div>
            <label for="seed_rate" class="block text-sm font-bold text-gray-700 mb-2">
              {{ sourceType === 'nursery' ? 'Quantity (Seedlings)' : 'Seed Quantity' }}
            </label>
            <div class="flex shadow-sm rounded-xl">
              <input
                type="number"
                step="0.01"
                min="0"
                id="seed_rate"
                v-model.number="form.data.seed_rate"
                class="block w-full rounded-l-xl border-gray-300 focus:border-emerald-500 focus:ring-emerald-500 focus:z-10 sm:text-sm font-medium h-12 px-4"
                :class="{ 'border-red-500': form.errors.seed_rate }"
              />
              <select
                v-model="form.data.seed_unit"
                class="inline-flex items-center rounded-r-xl border border-l-0 border-gray-300 bg-gray-50 text-gray-700 sm:text-sm font-medium focus:border-emerald-500 focus:ring-emerald-500 px-4"
              >
                 <option v-for="unit in availableUnits" :key="unit.value" :value="unit.value">
                   {{ unit.label }}
                 </option>
              </select>
            </div>
            <p v-if="form.errors.seed_rate" class="mt-1 text-xs text-red-600 font-medium">{{ form.errors.seed_rate }}</p>
            <transition enter-active-class="transition ease-out duration-200" enter-from-class="opacity-0 translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-150" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 translate-y-1">
              <p v-if="selectedInventoryItem && sourceType === 'direct'" class="mt-2 text-xs text-emerald-600 font-medium bg-emerald-50 px-3 py-1.5 rounded-lg inline-block border border-emerald-100">
                <span class="font-bold">✓</span> Stock Available: {{ selectedInventoryItem.current_stock }} {{ selectedInventoryItem.unit || 'kg' }}
              </p>
              <p v-else-if="form.data.seed_planting_id && sourceType === 'nursery'" class="mt-2 text-xs text-emerald-600 font-medium bg-emerald-50 px-3 py-1.5 rounded-lg inline-block border border-emerald-100">
                <span class="font-bold">✓</span> Available from Nursery: {{ selectedSeedPlanting?.quantity }}
              </p>
            </transition>
          </div>
      </div>
    </div>

    <div class="bg-white shadow-xl rounded-2xl p-8 border border-gray-100">
      <div class="flex items-center mb-6">
        <div class="h-12 w-12 rounded-xl bg-amber-50 flex items-center justify-center mr-4 shadow-sm">
           <svg class="h-6 w-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
        </div>
        <div>
          <h3 class="text-xl font-bold text-gray-900 tracking-tight">Additional Notes</h3>
          <p class="text-sm text-gray-500 font-medium">Any extra details or observations.</p>
        </div>
      </div>
      <div class="mt-4">
        <textarea
          id="notes"
          v-model="form.data.notes"
          rows="4"
          class="w-full rounded-xl border-gray-300 px-4 py-3 shadow-sm focus:border-emerald-500 focus:ring-4 focus:ring-emerald-500/10 transition-all font-medium text-gray-700 resize-none"
          :class="{ 'border-red-500': form.errors.notes }"
          placeholder="e.g. Weather conditions were sunny during planting..."
        ></textarea>
        <p v-if="form.errors.notes" class="mt-1 text-xs text-red-600 font-medium">{{ form.errors.notes }}</p>
      </div>
    </div>
    
    <div class="flex justify-end gap-4 pt-6 pb-2">
      <button
        type="button"
        @click="cancelForm"
        class="inline-flex items-center px-6 py-3 text-sm font-bold rounded-xl border border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all shadow-sm hover:shadow"
      >
        Cancel
      </button>
      <button
        type="submit"
        :disabled="form.processing"
        class="inline-flex items-center justify-center px-6 py-3 text-sm font-bold rounded-xl bg-gradient-to-r from-emerald-500 to-teal-600 text-white hover:from-emerald-600 hover:to-teal-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 disabled:opacity-50 transition-all shadow-md hover:shadow-lg transform hover:-translate-y-0.5"
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

const formatNumber = (num) => {
  return Number(num).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
};

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

// Get max area that can be planted (use available_area if exists, otherwise size)
// If in edit mode, we should add back the *current* planting's area to the available area
const availableArea = computed(() => {
  if (!selectedField.value) return 0;
  
  let available = Number(selectedField.value.available_area !== undefined ? selectedField.value.available_area : selectedField.value.size);
  
  // If editing, add back the current area so we can maintain or slightly modify it
  if (isEditMode.value && props.planting && props.planting.field_id === selectedField.value.id) {
    available += Number(props.planting.area_planted || 0);
  }
  
  return Math.max(0, available); // Ensure no negative values
})

const maxAreaPlanted = computed(() => {
  return availableArea.value;
})

// Check if area planted exceeds available area
const areaExceedsField = computed(() => {
  if (!selectedField.value || !form.value.data.area_planted) return false
  return Number(form.value.data.area_planted) > availableArea.value + 0.001 // Add tiny buffer for float comparison
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
    seed_unit: props.planting?.seed_unit || 'kg',
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

// Unit Configuration
const unitConfig = {
  seeds: [
    { value: 'kg', label: 'kg' },
    { value: 'bags', label: 'Bags' },
    { value: 'sacks', label: 'Sacks' },
    { value: 'packets', label: 'Packets' },
    { value: 'pounds', label: 'lbs' }
  ],
  nursery: [
    { value: 'pieces', label: 'Pcs' },
    { value: 'trays', label: 'Trays' },
    { value: 'bundles', label: 'Bundles' },
    { value: 'seedlings', label: 'Seedlings' }
  ],
  default: [
    { value: 'kg', label: 'kg' },
    { value: 'pieces', label: 'Pcs' },
    { value: 'bags', label: 'Bags' }
  ]
}

const availableUnits = computed(() => {
  if (sourceType.value === 'nursery') {
    return unitConfig.nursery
  }
  // If inventory item selected, ideally filter by its category, but general seed units work
  return unitConfig.seeds
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

const selectedSeedPlanting = computed(() => {
   if (!form.value.data.seed_planting_id) return null;
   return readySeedPlantings.value.find(p => p.id == form.value.data.seed_planting_id);
});

onMounted(() => {
  fetchReadySeedPlantings();
});

// Planting Method Configuration
const plantingMethodConfig = {
  direct: [
    { value: 'direct_seeding', label: 'Direct Seeding' },
    { value: 'broadcasting', label: 'Broadcasting' }
  ],
  nursery: [
    { value: 'transplanting', label: 'Transplanting' }
  ]
}

const availablePlantingMethods = computed(() => {
  return plantingMethodConfig[sourceType.value] || []
})

// Watch source type to reset planting method
watch(sourceType, (newSource) => {
  // If current method is not valid for new source, pick the first valid one
  const validMethods = plantingMethodConfig[newSource] || []
  const isCurrentValid = validMethods.some(m => m.value === form.value.data.planting_method)
  
  if (!isCurrentValid && validMethods.length > 0) {
    form.value.data.planting_method = validMethods[0].value
  }
})

// Watch for seed_planting_id selection to autofill
watch(() => form.value.data.seed_planting_id, (newId) => {
  if (!newId) return;
  
  const seedPlanting = readySeedPlantings.value.find(p => p.id == newId);
  if (seedPlanting) {
    form.value.data.rice_variety_id = seedPlanting.rice_variety_id;
    form.value.data.planting_method = 'transplanting';
    // Default unit for nursery
    form.value.data.seed_unit = 'pieces';
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
  if (item) {
    if (item.name) {
      // Attempt to fuzzy match the inventory item name with rice varieties
      const match = riceVarieties.value.find(v => 
        item.name.toLowerCase().includes(v.name.toLowerCase()) || 
        v.name.toLowerCase().includes(item.name.toLowerCase())
      );
      
      if (match) {
        form.value.data.rice_variety_id = match.id;
      }
    }
    
    // Auto-set unit from inventory item if possible
    if (item.unit) {
       // Check if item.unit is in our list, if not, maybe add it or just use it?
       // For now, let's just set it, assuming it matches one of our values or is valid
       form.value.data.seed_unit = item.unit;
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

  // Validate area planted doesn't exceed available area
  if (form.value.data.area_planted && selectedField.value) {
    const areaPlanted = Number(form.value.data.area_planted)
    const available = availableArea.value;
    
    if (areaPlanted > available + 0.001) {
      form.value.errors.area_planted = `Area planted (${areaPlanted} ha) cannot exceed available area (${formatNumber(available)} ha)`
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