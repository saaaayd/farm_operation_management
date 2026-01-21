<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Global Error Handler -->
    <div v-if="hasError" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-red-800">Dashboard Error</h3>
          <p class="mt-1 text-sm text-red-700">{{ errorMessage }}</p>
          <div class="mt-3">
            <button
            @click="resetError"
            class="bg-red-100 px-3 py-1 rounded-md text-sm font-medium text-red-800 hover:bg-red-200"
            >
            Dismiss
          </button>
          <button
          @click="() => window.location.reload()"
          class="ml-3 bg-red-600 px-3 py-1 rounded-md text-sm font-medium text-white hover:bg-red-700"
          >
          Refresh Page
        </button>
      </div>
    </div>
  </div>
</div>
<!-- Header -->
<div class="container mx-auto px-4 py-8">
  <!-- Standard Header -->
  <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
    <div>
      <h1 class="text-3xl font-bold text-gray-800">ANIBUKID Dashboard</h1>
      <p class="text-gray-500 mt-1">Welcome back, <span class="font-semibold text-gray-800">{{ authStore.user?.name }}</span></p>
    </div>

    <!-- Weather Widget (Moved to Header) -->
    <div class="w-full md:w-auto min-w-[300px]">
       <!-- Field Selector Dropdown (Compact) -->
       <div v-if="fieldsWithCoordinates.length > 1" class="mb-2">
         <select
           v-model="selectedFieldId"
           @change="onFieldChange"
           class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent bg-white text-xs"
         >
           <option v-for="field in fieldsWithCoordinates" :key="field.id" :value="field.id">
             {{ field.name }}
           </option>
         </select>
       </div>
       
       <!-- Weather Card -->
       <div v-if="primaryField && primaryField.id && hasValidCoordinates(primaryField)">
         <CurrentWeather :field-id="primaryField.id" :compact="true" />
       </div>
       <div v-else-if="primaryField && primaryField.id" class="p-4 bg-white rounded-lg shadow border border-gray-100 text-center">
         <p class="text-xs text-gray-500">Update field location for weather data</p>
         <button @click="navigateTo('/fields')" class="text-xs text-green-600 font-medium mt-1">Go to Fields</button>
       </div>
       <div v-else class="p-4 bg-white rounded-lg shadow border border-gray-100 text-center">
          <p class="text-xs text-gray-500">No fields available</p>
          <button @click="navigateTo('/fields')" class="text-xs text-green-600 font-medium mt-1">Add Field</button>
       </div>
    </div>
  </div>
  
  <!-- Loading State -->
  <div v-if="isInitialLoading" class="py-8">
    <div class="text-center">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
      <p class="mt-4 text-gray-600">Loading dashboard...</p>
    </div>
  </div>
  
  <!-- Main Content -->
  <div v-else>
    <!-- Stats Overview -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
      <div class="bg-gradient-to-br from-white to-green-50 rounded-xl shadow-lg hover:shadow-xl p-6 border border-green-100 transition-all duration-200 transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="h-12 w-12 bg-gradient-to-br from-green-500 to-emerald-600 rounded-xl flex items-center justify-center shadow-md">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
            </div>
            <div class="ml-4 flex-1 min-w-0">
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide whitespace-normal">Total Planted Area</p>
              <div class="flex items-baseline gap-2 flex-wrap">
                <p class="text-2xl font-bold text-gray-900">{{ totalPlantedArea }} <span class="text-sm font-normal text-gray-600">ha</span></p>
                <span 
                  v-if="plantedAreaTrend !== 0" 
                  :class="plantedAreaTrend > 0 ? 'text-green-600 bg-green-100' : 'text-red-600 bg-red-100'"
                  class="text-xs font-medium px-1.5 py-0.5 rounded-full flex items-center whitespace-nowrap"
                >
                  <svg v-if="plantedAreaTrend > 0" class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                  </svg>
                  <svg v-else class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  {{ Math.abs(plantedAreaTrend) }}%
                </span>
              </div>
            </div>
          </div>
        </div>
      
      </div>
      
      <div class="bg-gradient-to-br from-white to-blue-50 rounded-xl shadow-lg hover:shadow-xl p-6 border border-blue-100 transition-all duration-200 transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="h-12 w-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-md">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Tasks Due Today</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">{{ tasksDueToday }}</p>
            </div>
          </div>
        </div>
      </div>
      
      <div class="bg-gradient-to-br from-white to-yellow-50 rounded-xl shadow-lg hover:shadow-xl p-6 border border-yellow-100 transition-all duration-200 transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="h-12 w-12 bg-gradient-to-br from-yellow-500 to-orange-500 rounded-xl flex items-center justify-center shadow-md">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Low Stock Alerts</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">{{ lowStockCount }}</p>
            </div>
          </div>
        </div>
      </div>
      
      <!-- Nursery Stats -->
      <div class="bg-gradient-to-br from-white to-orange-50 rounded-xl shadow-lg hover:shadow-xl p-6 border border-orange-100 transition-all duration-200 transform hover:-translate-y-1">
        <div class="flex items-center justify-between">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="h-12 w-12 bg-gradient-to-br from-orange-400 to-red-500 rounded-xl flex items-center justify-center shadow-md">
                <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                </svg>
              </div>
            </div>
            <div class="ml-4">
              <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">Nursery Batches</p>
              <div class="flex items-baseline mt-1">
                <p class="text-2xl font-bold text-gray-900">{{ activeNurseryBatches }}</p>
                <span v-if="readyForTransplant > 0" class="ml-2 text-xs font-bold text-orange-600 bg-orange-100 px-2 py-0.5 rounded-full">
                  {{ readyForTransplant }} Ready
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Marketplace Overview Section -->
    <div class="mb-8">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-lg font-semibold text-gray-900">Marketplace Overview</h2>
        <button 
          @click="navigateTo('/marketplace/my-products')" 
          class="text-sm font-medium text-purple-600 hover:text-purple-700"
        >
          Manage Products
        </button>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <!-- Active Listings -->
        <div class="bg-white rounded-xl shadow p-6 border border-gray-100 flex items-center">
          <div class="h-12 w-12 rounded-full bg-green-100 flex items-center justify-center mr-4">
            <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <div>
            <p class="text-sm text-gray-500">Active Listings</p>
            <p class="text-2xl font-bold text-gray-900">{{ marketplaceStats.active_listings }}</p>
          </div>
        </div>

        <!-- Pending Orders -->
        <div class="bg-white rounded-xl shadow p-6 border border-gray-100 flex items-center">
           <div class="h-12 w-12 rounded-full bg-yellow-100 flex items-center justify-center mr-4">
             <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
             </svg>
           </div>
           <div>
             <p class="text-sm text-gray-500">Pending Orders</p>
             <p class="text-2xl font-bold text-gray-900">{{ marketplaceStats.pending_orders }}</p>
           </div>
        </div>

        <!-- Total Revenue -->
        <div class="bg-white rounded-xl shadow p-6 border border-gray-100 flex items-center">
           <div class="h-12 w-12 rounded-full bg-blue-100 flex items-center justify-center mr-4">
             <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
             </svg>
           </div>
           <div>
             <p class="text-sm text-gray-500">Total Revenue</p>
             <p class="text-2xl font-bold text-gray-900">₱{{ marketplaceStats.total_revenue.toLocaleString() }}</p>
           </div>
        </div>
        
        <!-- Total Products -->
        <div class="bg-white rounded-xl shadow p-6 border border-gray-100 flex items-center">
           <div class="h-12 w-12 rounded-full bg-purple-100 flex items-center justify-center mr-4">
             <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
               <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
             </svg>
           </div>
           <div>
             <p class="text-sm text-gray-500">Total Products</p>
             <p class="text-2xl font-bold text-gray-900">{{ marketplaceStats.total_products }}</p>
           </div>
        </div>
      </div>

       <!-- Recent Products Table (Collapsed view) -->
       <div class="bg-white rounded-xl shadow overflow-hidden">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-sm font-medium text-gray-900">Recent Products</h3>
          </div>
          <div class="divide-y divide-gray-200">
             <div v-if="recentProducts.length === 0" class="p-6 text-center text-gray-500">
                No products found
             </div>
             <div v-else v-for="product in recentProducts" :key="product.id" class="p-4 flex items-center justify-between hover:bg-gray-50">
                <div class="flex items-center">
                   <div class="h-10 w-10 flex-shrink-0 rounded bg-gray-100 mr-3 overflow-hidden">
                      <img v-if="product.images && product.images.length" :src="product.images[0]" class="h-full w-full object-cover" />
                      <div v-else class="h-full w-full flex items-center justify-center text-gray-400">
                         <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                         </svg>
                      </div>
                   </div>
                   <div>
                      <p class="text-sm font-medium text-gray-900">{{ product.name }}</p>
                      <p class="text-xs text-gray-500">{{ product.rice_variety?.name || 'Unknown Variety' }}</p>
                   </div>
                </div>
                <div class="flex items-center space-x-4">
                   <span class="text-sm font-medium text-gray-900">₱{{ product.price_per_unit }}/{{ product.unit }}</span>
                   <span 
                      class="px-2 py-1 text-xs font-semibold rounded-full"
                      :class="product.is_available ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                   >
                      {{ product.is_available ? 'Available' : 'Unavailable' }}
                   </span>
                </div>
             </div>
          </div>
       </div>
    </div>
  </div>
  <!-- Main Dashboard Grid -->
  <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-stretch">
    <!-- Weather Widget -->
    <!-- Quick Actions (Moved to Sidebar) -->
    <div class="lg:col-span-1 flex flex-col space-y-4">
        <h3 class="text-lg font-semibold text-gray-900">Quick Actions</h3>
        
        <button 
        @click="navigateTo('/tasks/create')"
        :disabled="isNavigating"
        class="bg-white rounded-lg shadow p-4 flex items-center hover:shadow-md transition-shadow cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed group"
        >
        <div class="h-10 w-10 bg-green-100 rounded-full flex items-center justify-center mr-4 group-hover:bg-green-200 transition-colors">
          <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
          </svg>
        </div>
        <div class="text-left">
           <p class="text-sm font-medium text-gray-900">Create Task</p>
           <p class="text-xs text-gray-500">Assign new work</p>
        </div>
      </button>
      
      <button 
      @click="navigateTo('/harvests/create')"
      :disabled="isNavigating"
      class="bg-white rounded-lg shadow p-4 flex items-center hover:shadow-md transition-shadow cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed group"
      >
      <div class="h-10 w-10 bg-yellow-100 rounded-full flex items-center justify-center mr-4 group-hover:bg-yellow-200 transition-colors">
        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />
        </svg>
      </div>
      <div class="text-left">
         <p class="text-sm font-medium text-gray-900">Record Harvest</p>
         <p class="text-xs text-gray-500">Log crop yields</p>
      </div>
    </button>
    
    <button 
    @click="navigateTo('/inventory')"
    :disabled="isNavigating"
    class="bg-white rounded-lg shadow p-4 flex items-center hover:shadow-md transition-shadow cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed group"
    >
    <div class="h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center mr-4 group-hover:bg-blue-200 transition-colors">
      <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
      </svg>
    </div>
    <div class="text-left">
       <p class="text-sm font-medium text-gray-900">Inventory</p>
       <p class="text-xs text-gray-500">Check stock levels</p>
    </div>
    </button>

    <button 
    @click="navigateTo('/marketplace')"
    :disabled="isNavigating"
    class="bg-white rounded-lg shadow p-4 flex items-center hover:shadow-md transition-shadow cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed group"
    >
    <div class="h-10 w-10 bg-purple-100 rounded-full flex items-center justify-center mr-4 group-hover:bg-purple-200 transition-colors">
      <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
      </svg>
    </div>
    <div class="text-left">
      <p class="text-sm font-medium text-gray-900">Marketplace</p>
      <p class="text-xs text-gray-500">Manage sales</p>
    </div>
    </button>
    </div>

        <!-- Column: Tasks & Alerts -->
        <div class="lg:col-span-2 flex flex-col space-y-6">
          
          <!-- Low Stock Alerts (Conditional) -->
          <div v-if="lowStockItems.length > 0" class="bg-amber-50 border border-amber-200 rounded-lg shadow-sm p-4 animate-fade-in-down">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-md font-bold text-amber-800 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                   <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                Low Stock Warnings
              </h3>
              <button 
                @click="navigateTo('/inventory')" 
                class="text-xs font-semibold text-amber-700 hover:text-amber-900 underline"
              >
                Restock Now
              </button>
            </div>
            <div class="space-y-2">
              <div 
                v-for="item in lowStockItems.slice(0, 3)" 
                :key="item.id" 
                class="flex items-center justify-between bg-white bg-opacity-60 p-2 rounded-md border border-amber-100"
              >
                <div class="flex items-center">
                   <div class="w-2 h-2 rounded-full bg-red-500 mr-2"></div>
                   <span class="text-sm font-medium text-gray-800">{{ item.name }}</span>
                </div>
                <div class="text-sm">
                  <span class="font-bold text-red-600">{{ item.current_stock ?? item.quantity }} {{ item.unit }}</span>
                  <span class="text-gray-500 text-xs ml-1">(Min: {{ item.minimum_stock ?? item.min_stock }})</span>
                </div>
              </div>
              <div v-if="lowStockItems.length > 3" class="text-center pt-1">
                 <span class="text-xs text-amber-700 font-medium">+ {{ lowStockItems.length - 3 }} more items</span>
              </div>
            </div>
          </div>

          <!-- Weather Farming Alerts -->
          <div v-if="weatherAlerts.length > 0" class="bg-gradient-to-r from-blue-50 to-sky-50 border border-blue-200 rounded-lg shadow-sm p-4 animate-fade-in-down">
            <div class="flex items-center justify-between mb-3">
              <h3 class="text-md font-bold text-blue-800 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z" />
                </svg>
                Weather Farming Suggestions
              </h3>
              <button 
                @click="navigateTo('/weather')" 
                class="text-xs font-semibold text-blue-700 hover:text-blue-900 underline"
              >
                View Forecast
              </button>
            </div>
            <div class="space-y-2">
              <div 
                v-for="(alert, index) in weatherAlerts.slice(0, 3)" 
                :key="index" 
                :class="[
                  'flex items-start p-3 rounded-md border',
                  alert.type === 'warning' ? 'bg-yellow-50 border-yellow-200' : 
                  alert.type === 'danger' ? 'bg-red-50 border-red-200' : 'bg-blue-50 border-blue-100'
                ]"
              >
                <div class="flex-shrink-0 mr-3">
                  <span class="text-xl">{{ alert.icon }}</span>
                </div>
                <div class="flex-1">
                  <p class="text-sm font-medium" :class="alert.type === 'danger' ? 'text-red-800' : alert.type === 'warning' ? 'text-yellow-800' : 'text-blue-800'">
                    {{ alert.title }}
                  </p>
                  <p class="text-xs text-gray-600 mt-0.5">{{ alert.message }}</p>
                </div>
              </div>
            </div>
          </div>

          <!-- Upcoming Tasks -->
          <div class="bg-white rounded-lg shadow-lg p-6 flex-1 flex flex-col">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-900">Upcoming Tasks</h3>
      <button 
      @click="navigateTo('/tasks')" 
      :disabled="isNavigating"
      class="text-green-600 hover:text-green-700 text-sm font-medium cursor-pointer disabled:opacity-50 disabled:cursor-not-allowed"
      >
      View all tasks
    </button>
  </div>
  
  <div v-if="upcomingTasks.length > 0" class="space-y-3 flex-1">
    <div 
    v-for="task in upcomingTasks.slice(0, 5)" 
    :key="task.id"
    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
    >
    <div class="flex items-center space-x-3">
      <div 
      :class="[
      'w-3 h-3 rounded-full',
      getTaskPriorityColor(task.priority)
      ]"
      ></div>
      <div>
        <p class="text-sm font-medium text-gray-900">{{ task.task_type }}</p>
        <p class="text-xs text-gray-500">{{ task.description }}</p>
      </div>
    </div>
    <div class="text-right">
      <p class="text-xs text-gray-500">{{ formatDate(task.due_date) }}</p>
      <span 
      :class="[
      'inline-flex items-center px-2 py-1 rounded-full text-xs font-medium',
      getTaskStatusClass(task.status)
      ]"
      >
      {{ task.status }}
    </span>
  </div>
</div>
</div>

<div v-else class="text-center py-8 text-gray-500 flex-1 flex flex-col justify-center">
  <svg class="h-12 w-12 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
  </svg>
  <p class="text-sm">No upcoming tasks</p>
</div>
</div>
</div>
</div>

<!-- Quick Actions -->

</div>
</div>
</template>

<script setup>
  import { ref, computed, onMounted, onErrorCaptured, watch } from 'vue';
  import { useRouter } from 'vue-router';
  import { useAuthStore } from '@/stores/auth';
  import { useFarmStore } from '@/stores/farm';
  import { useInventoryStore } from '@/stores/inventory';
  import { useMarketplaceStore } from '@/stores/marketplace';
  import { dashboardAPI } from '@/services/api';
  import CurrentWeather from '@/Components/Weather/CurrentWeather.vue';
  
  const router = useRouter();
  const authStore = useAuthStore();
  const farmStore = useFarmStore();
  const inventoryStore = useInventoryStore();
  const marketplaceStore = useMarketplaceStore();
  
  // Add loading state to prevent button spamming
  const isNavigating = ref(false);
  const isInitialLoading = ref(true);

  // Marketplace Data
  const marketplaceStats = ref({
    total_products: 0,
    active_listings: 0,
    pending_orders: 0,
    total_revenue: 0
  });
  const recentProducts = ref([]);
  
  // Global error handling for this component
  const hasError = ref(false);
  const errorMessage = ref('');
  
  // Catch any unhandled errors in this component
  onErrorCaptured((error, instance, info) => {
    console.error('Dashboard component error:', error);
    console.error('Error info:', info);
    
    hasError.value = true;
    errorMessage.value = 'An unexpected error occurred. Please refresh the page.';
    
    // Don't propagate the error further
    return false;
  });
  
  // Function to reset error state
  const resetError = () => {
    hasError.value = false;
    errorMessage.value = '';
  };
  
  const totalPlantedArea = computed(() => {
    try {
      if (!farmStore || !farmStore.plantings || !Array.isArray(farmStore.plantings)) {
        return '0.00';
      }
      return farmStore.plantings
      .filter(p => p && p.status !== 'harvested')
      .reduce((total, planting) => {
        // Use area_planted from the planting record, not field size
        const areaPlanted = parseFloat(planting?.area_planted) || 0;
        return total + areaPlanted;
      }, 0).toFixed(2);
    } catch (error) {
      console.warn('Error calculating total planted area:', error);
      return '0.00';
    }
  });

  // Trend calculation - simulated based on planting dates for demo
  const plantedAreaTrend = computed(() => {
    try {
      if (!farmStore || !farmStore.plantings || farmStore.plantings.length < 2) return 0;
      const now = new Date();
      const thirtyDaysAgo = new Date(now.getTime() - 30 * 24 * 60 * 60 * 1000);
      const sixtyDaysAgo = new Date(now.getTime() - 60 * 24 * 60 * 60 * 1000);
      
      const currentPeriod = farmStore.plantings.filter(p => {
        const plantDate = new Date(p.created_at || p.planting_date);
        return plantDate >= thirtyDaysAgo && p.status !== 'harvested';
      }).reduce((sum, p) => sum + (parseFloat(p.area_planted) || 0), 0);
      
      const previousPeriod = farmStore.plantings.filter(p => {
        const plantDate = new Date(p.created_at || p.planting_date);
        return plantDate >= sixtyDaysAgo && plantDate < thirtyDaysAgo;
      }).reduce((sum, p) => sum + (parseFloat(p.area_planted) || 0), 0);
      
      if (previousPeriod === 0) return currentPeriod > 0 ? 100 : 0;
      return Math.round(((currentPeriod - previousPeriod) / previousPeriod) * 100);
    } catch (e) {
      return 0;
    }
  });

  // Mini chart data - last 6 months of planting activity
  const plantedAreaHistory = computed(() => {
    try {
      if (!farmStore || !farmStore.plantings || farmStore.plantings.length === 0) {
        return [1, 1, 1, 1, 1, 1]; // Default flat line
      }
      const now = new Date();
      const monthlyData = [];
      for (let i = 5; i >= 0; i--) {
        const monthStart = new Date(now.getFullYear(), now.getMonth() - i, 1);
        const monthEnd = new Date(now.getFullYear(), now.getMonth() - i + 1, 0);
        const monthTotal = farmStore.plantings.filter(p => {
          const plantDate = new Date(p.created_at || p.planting_date);
          return plantDate >= monthStart && plantDate <= monthEnd;
        }).reduce((sum, p) => sum + (parseFloat(p.area_planted) || 0), 0);
        monthlyData.push(monthTotal || 0.1); // Min value to show bar
      }
      return monthlyData;
    } catch (e) {
      return [1, 1, 1, 1, 1, 1];
    }
  });
  
  const tasksDueToday = computed(() => {
    try {
      if (!farmStore || !farmStore.tasks || !Array.isArray(farmStore.tasks)) {
        return 0;
      }
      const today = new Date();
      today.setHours(23, 59, 59, 999);
      return farmStore.tasks.filter(task => {
        if (!task || !task.due_date) return false;
        try {
          const dueDate = new Date(task.due_date);
          return !isNaN(dueDate.getTime()) && 
          dueDate <= today && 
          ['pending', 'in_progress'].includes(task.status);
        } catch (dateError) {
          console.warn('Invalid date in task:', task.due_date);
          return false;
        }
      }).length;
    } catch (error) {
      console.warn('Error calculating tasks due today:', error);
      return 0;
    }
  });
  
  const upcomingTasks = computed(() => {
    try {
      if (!farmStore) return [];
      
      // Check if upcomingTasks getter exists, otherwise use tasks
      if (farmStore.upcomingTasks && Array.isArray(farmStore.upcomingTasks)) {
        return farmStore.upcomingTasks;
      }
      
      // Fallback: calculate upcoming tasks from all tasks
      if (farmStore.tasks && Array.isArray(farmStore.tasks)) {
        const nextWeek = new Date();
        nextWeek.setDate(nextWeek.getDate() + 7);
        return farmStore.tasks.filter(task => {
          if (!task || !task.due_date) return false;
          try {
            const dueDate = new Date(task.due_date);
            return !isNaN(dueDate.getTime()) && 
            dueDate <= nextWeek && 
            ['pending', 'in_progress'].includes(task.status);
          } catch (dateError) {
            return false;
          }
        }).slice(0, 5);
      }
      
      return [];
    } catch (error) {
      console.warn('Error getting upcoming tasks:', error);
      return [];
    }
  });
  
  
  const lowStockItems = computed(() => {
    try {
      if (!inventoryStore) return [];
      // Use getter if available
      if (inventoryStore.lowStockItems && Array.isArray(inventoryStore.lowStockItems)) {
        return inventoryStore.lowStockItems;
      }
      // Fallback
      if (inventoryStore.items && Array.isArray(inventoryStore.items)) {
        return inventoryStore.items.filter(item => {
           const currentStock = item.current_stock ?? item.quantity ?? 0;
           const minStock = item.minimum_stock ?? item.min_stock ?? 0;
           return item && 
           typeof currentStock === 'number' && 
           typeof minStock === 'number' && 
           currentStock <= minStock;
        });
      }
      return [];
    } catch(e) {
      console.warn('Error getting low stock items', e);
      return [];
    }
  });

  const lowStockCount = computed(() => lowStockItems.value.length);
  
  const pendingOrders = computed(() => {
    try {
      if (!marketplaceStore || !marketplaceStore.orders || !Array.isArray(marketplaceStore.orders)) {
        return 0;
      }
      return marketplaceStore.orders.filter(order => {
        return order && 
        order.status && 
        ['pending', 'confirmed', 'processing'].includes(order.status.toLowerCase());
      }).length;
    } catch (error) {
      console.warn('Error calculating pending orders:', error);
      return 0;
    }
  });
  
  const activeNurseryBatches = computed(() => {
    try {
      if (!farmStore || !farmStore.seedPlantings || !Array.isArray(farmStore.seedPlantings)) {
        return 0;
      }
      return farmStore.seedPlantings.filter(p => 
      ['sown', 'germinating', 'ready'].includes(p.status)
      ).length;
    } catch (error) {
      console.warn('Error calculating active nursery batches:', error);
      return 0;
    }
  });
  
  const readyForTransplant = computed(() => {
    try {
      if (!farmStore || !farmStore.seedPlantings || !Array.isArray(farmStore.seedPlantings)) {
        return 0;
      }
      return farmStore.seedPlantings.filter(p => p.status === 'ready').length;
    } catch (error) {
      console.warn('Error calculating ready for transplant:', error);
      return 0;
    }
  });
  
  // Weather-based farming alerts and suggestions
  const weatherAlerts = computed(() => {
    try {
      const alerts = [];
      const weather = weatherStore.currentWeather;
      const forecast = weatherStore.forecast || [];
      
      if (!weather) return alerts;
      
      const temp = weather.temperature || weather.temp;
      const humidity = weather.humidity;
      const description = (weather.description || weather.weather || '').toLowerCase();
      const windSpeed = weather.wind_speed || weather.windSpeed || 0;
      
      // Heavy rain warning
      if (description.includes('rain') || description.includes('storm') || description.includes('shower')) {
        alerts.push({
          type: 'warning',
          icon: '🌧️',
          title: 'Rain Expected Today',
          message: 'Delay pesticide application and fertilizer spreading. Check drainage systems.'
        });
      }
      
      // Extreme heat warning
      if (temp && temp > 35) {
        alerts.push({
          type: 'danger',
          icon: '🌡️',
          title: 'Extreme Heat Alert',
          message: 'Water crops early morning or evening. Avoid planting during peak heat hours.'
        });
      }
      
      // Good planting conditions
      if (temp >= 25 && temp <= 32 && humidity >= 60 && humidity <= 85 && !description.includes('rain')) {
        alerts.push({
          type: 'info',
          icon: '🌱',
          title: 'Ideal Planting Conditions',
          message: 'Temperature and humidity are optimal for transplanting rice seedlings.'
        });
      }
      
      // High winds
      if (windSpeed > 20) {
        alerts.push({
          type: 'warning',
          icon: '💨',
          title: 'High Wind Advisory',
          message: 'Delay spraying activities. Check plant supports and field structures.'
        });
      }
      
      // Drought conditions
      if (temp > 30 && humidity < 40) {
        alerts.push({
          type: 'warning',
          icon: '☀️',
          title: 'Dry Conditions',
          message: 'Increase irrigation frequency. Monitor soil moisture levels closely.'
        });
      }
      
      // Check forecast for upcoming rain
      const upcomingRain = forecast.slice(0, 3).find(f => {
        const desc = (f.description || f.weather || '').toLowerCase();
        return desc.includes('rain') || desc.includes('storm');
      });
      
      if (upcomingRain && !description.includes('rain')) {
        alerts.push({
          type: 'info',
          icon: '📅',
          title: 'Rain Expected Soon',
          message: 'Complete urgent field work today. Harvesting should be prioritized if crops are ready.'
        });
      }
      
      return alerts;
    } catch (error) {
      console.warn('Error generating weather alerts:', error);
      return [];
    }
  });
  
  // Helper function to check if a field has valid coordinates
  const hasValidCoordinates = (field) => {
    if (!field) return false;
    
    // Check location coordinates
    if (field.location && 
    typeof field.location === 'object' &&
    typeof field.location.lat === 'number' &&
    typeof field.location.lon === 'number' &&
    !isNaN(field.location.lat) &&
    !isNaN(field.location.lon)) {
      return true;
    }
    
    // Check field_coordinates
    if (field.field_coordinates &&
    typeof field.field_coordinates === 'object' &&
    typeof field.field_coordinates.lat === 'number' &&
    typeof field.field_coordinates.lon === 'number' &&
    !isNaN(field.field_coordinates.lat) &&
    !isNaN(field.field_coordinates.lon)) {
      return true;
    }
    
    return false;
  };
  
  // Get all fields with valid coordinates
  const fieldsWithCoordinates = computed(() => {
    try {
      if (!farmStore || !farmStore.fields || !Array.isArray(farmStore.fields)) {
        return [];
      }
      
      return farmStore.fields.filter(field => hasValidCoordinates(field));
    } catch (error) {
      console.warn('Error getting fields with coordinates:', error);
      return [];
    }
  });
  
  // Selected field ID (stored in localStorage for persistence)
  const selectedFieldId = ref(null);
  
  // Initialize selected field from localStorage or use first valid field
  const initializeSelectedField = () => {
    const savedFieldId = localStorage.getItem('selectedFieldId');
    if (savedFieldId && fieldsWithCoordinates.value.some(f => f.id == savedFieldId)) {
      selectedFieldId.value = savedFieldId;
    } else if (fieldsWithCoordinates.value.length > 0) {
      selectedFieldId.value = fieldsWithCoordinates.value[0].id;
      localStorage.setItem('selectedFieldId', selectedFieldId.value);
    }
  };
  
  // Handle field selection change
  const onFieldChange = () => {
    if (selectedFieldId.value) {
      localStorage.setItem('selectedFieldId', selectedFieldId.value);
    }
  };
  
  // Watch for fields loading and initialize selection
  watch(() => farmStore.fields, () => {
    if (fieldsWithCoordinates.value.length > 0 && !selectedFieldId.value) {
      initializeSelectedField();
    }
  }, { immediate: true, deep: true });
  
  const primaryField = computed(() => {
    try {
      if (!farmStore || !farmStore.fields || !Array.isArray(farmStore.fields)) {
        return null;
      }
      
      if (farmStore.fields.length === 0) {
        return null;
      }
      
      // If user has selected a field, use that
      if (selectedFieldId.value) {
        const selectedField = farmStore.fields.find(f => f.id == selectedFieldId.value);
        if (selectedField && hasValidCoordinates(selectedField)) {
          return selectedField;
        }
      }
      
      // Otherwise, find a field with valid location coordinates
      // First, try to find a field with valid location.lat and location.lon
      const fieldWithValidLocation = farmStore.fields.find(field => {
        if (!field || !field.location) return false;
        
        // Check if location has lat and lon
        const hasLocationCoords = 
        typeof field.location === 'object' &&
        field.location !== null &&
        typeof field.location.lat === 'number' &&
        typeof field.location.lon === 'number' &&
        !isNaN(field.location.lat) &&
        !isNaN(field.location.lon);
        
        return hasLocationCoords;
      });
      
      // If we found a field with valid coordinates, use it
      if (fieldWithValidLocation) {
        // Save it as selected
        selectedFieldId.value = fieldWithValidLocation.id;
        localStorage.setItem('selectedFieldId', selectedFieldId.value);
        return fieldWithValidLocation;
      }
      
      // Fallback: try field_coordinates if location doesn't have coords
      const fieldWithFieldCoordinates = farmStore.fields.find(field => {
        if (!field || !field.field_coordinates) return false;
        
        const hasFieldCoords = 
        typeof field.field_coordinates === 'object' &&
        field.field_coordinates !== null &&
        typeof field.field_coordinates.lat === 'number' &&
        typeof field.field_coordinates.lon === 'number' &&
        !isNaN(field.field_coordinates.lat) &&
        !isNaN(field.field_coordinates.lon);
        
        return hasFieldCoords;
      });
      
      // If we found a field with field_coordinates, use it
      if (fieldWithFieldCoordinates) {
        // Save it as selected
        selectedFieldId.value = fieldWithFieldCoordinates.id;
        localStorage.setItem('selectedFieldId', selectedFieldId.value);
        return fieldWithFieldCoordinates;
      }
      
      // Last resort: return first field (but it might not have valid coordinates)
      // This allows us to show a message about needing coordinates
      return farmStore.fields[0];
    } catch (error) {
      console.warn('Error getting primary field:', error);
      return null;
    }
  });
  
  const getTaskPriorityColor = (priority) => {
    const colors = {
      low: 'bg-green-500',
      medium: 'bg-yellow-500',
      high: 'bg-red-500'
    };
    return colors[priority] || 'bg-gray-500';
  };
  
  const getTaskStatusClass = (status) => {
    const classes = {
      pending: 'bg-yellow-100 text-yellow-800',
      in_progress: 'bg-blue-100 text-blue-800',
      completed: 'bg-green-100 text-green-800',
      cancelled: 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
  };
  
  const formatDate = (date) => {
    return new Date(date).toLocaleDateString();
  };
  
  const logout = async () => {
    try {
      await authStore.logout();
      router.push('/login');
    } catch (error) {
      console.error('Logout error:', error);
      // Force logout even if API call fails
      authStore.user = null;
      authStore.token = null;
      localStorage.removeItem('token');
      router.push('/login');
    }
  };
  
  const navigateTo = async (path) => {
    if (isNavigating.value) {
      console.warn('Navigation already in progress, ignoring click');
      return; // Prevent double-clicks
    }
    
    isNavigating.value = true;
    try {
      console.log(`Navigating to: ${path}`);
      await router.push(path);
      console.log(`✓ Navigation to ${path} successful`);
    } catch (error) {
      console.error('Navigation error:', error);
      // More robust fallback handling
      try {
        // Try using router.replace instead
        await router.replace(path);
        console.log(`✓ Navigation to ${path} successful via replace`);
      } catch (replaceError) {
        console.error('Router replace also failed:', replaceError);
        // Last resort: use window location
        console.log(`Using window.location fallback for ${path}`);
        window.location.href = path;
      }
    } finally {
      // Reset after a short delay
      setTimeout(() => {
        isNavigating.value = false;
      }, 1000); // Increased delay to prevent issues
    }
  };
  
  onMounted(async () => {
    // Initialize field selection
    initializeSelectedField();
    
    // Load data with improved error handling and retry logic
    const loadData = async () => {
      const loadingStates = {
        fields: false,
        plantings: false,
        tasks: false,
        inventory: false,
        orders: false,
        seedPlantings: false
      };
      
      // Load essential data first (fields are required for other operations)
      try {
        loadingStates.fields = true;
        await farmStore.fetchFields();
        console.log('✓ Fields loaded successfully');
      } catch (error) {
        console.warn('⚠ Failed to fetch fields:', error.userMessage || error.message);
        // Don't block other data loading if fields fail
      } finally {
        loadingStates.fields = false;
      }
      
      // Load remaining data with improved error handling
      const dataLoaders = [
      {
        name: 'plantings',
        loader: () => farmStore.fetchPlantings(),
        delay: 300,
        critical: false
      },
      {
        name: 'tasks',
        loader: () => farmStore.fetchTasks(),
        delay: 600,
        critical: false
      },
      {
        name: 'inventory',
        loader: () => inventoryStore.fetchItems(),
        delay: 900,
        critical: false
      },
      {
        name: 'orders',
        loader: () => marketplaceStore.fetchOrders(),
        delay: 1200,
        critical: false
      },
      {
        name: 'seedPlantings',
        loader: () => farmStore.fetchSeedPlantings(),
        delay: 1500,
        critical: false
      },
      {
        name: 'dashboardStats',
        loader: async () => {
          const response = await dashboardAPI.getFarmerStats();
          if (response.data && response.data.marketplace_stats) {
            marketplaceStats.value = response.data.marketplace_stats;
          }
          if (response.data && response.data.recent_products) {
            recentProducts.value = response.data.recent_products;
          }
        },
        delay: 1800,
        critical: false
      }
      ];
      
      // Load data with staggered delays and better error handling
      const loadPromises = dataLoaders.map(({ name, loader, delay, critical }) => {
        return new Promise((resolve) => {
          setTimeout(async () => {
            try {
              loadingStates[name] = true;
              await loader();
              console.log(`✓ ${name} loaded successfully`);
            } catch (error) {
              console.warn(`⚠ Failed to fetch ${name}:`, error.userMessage || error.message);
              if (critical) {
                // For critical data, we might want to retry
                console.log(`Retrying ${name} in 2 seconds...`);
                setTimeout(async () => {
                  try {
                    await loader();
                    console.log(`✓ ${name} loaded successfully on retry`);
                  } catch (retryError) {
                    console.error(`✗ ${name} failed even on retry:`, retryError.message);
                  }
                }, 2000);
              }
            } finally {
              loadingStates[name] = false;
              resolve(); // Always resolve to not block other operations
            }
          }, delay);
        });
      });
      
      // Don't wait for all to complete, let them load in background
      Promise.all(loadPromises).then(() => {
        console.log('✓ All dashboard data loading attempts completed');
      }).catch((error) => {
        console.warn('Some dashboard data failed to load:', error);
      }).finally(() => {
        // Hide initial loading after a reasonable time
        setTimeout(() => {
          isInitialLoading.value = false;
        }, 2000);
      });
    };
    
    // Start loading data
    try {
      await loadData();
    } catch (error) {
      console.error('Critical error during dashboard initialization:', error);
      // Dashboard should still be functional even if data loading fails
    } finally {
      // Ensure loading state is cleared even if there's an error
      setTimeout(() => {
        isInitialLoading.value = false;
      }, 3000); // Maximum loading time
    }
  });
</script>
