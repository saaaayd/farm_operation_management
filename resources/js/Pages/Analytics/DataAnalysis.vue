<template>
  <div class="min-h-screen bg-gray-50/50 font-sans text-gray-900">
    <div class="container mx-auto px-4 py-8 max-w-7xl">

      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">
            Data Analytics Dashboard
          </h1>
          <p class="text-gray-500 mt-1">Comprehensive insights from your farm data</p>
        </div>

        <div class="flex items-center gap-3 bg-white p-3 rounded-xl shadow-sm border border-gray-200">
          <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-600">From</label>
            <input
              v-model="startDate"
              type="date"
              class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all"
            />
          </div>
          <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-600">To</label>
            <input
              v-model="endDate"
              type="date"
              class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 outline-none transition-all"
            />
          </div>
          <button
            @click="fetchAnalytics"
            :disabled="isLoading"
            class="px-5 py-1.5 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 active:bg-emerald-800 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm"
          >
            <span v-if="isLoading" class="flex items-center gap-2">
              <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Updating...
            </span>
            <span v-else>Update</span>
          </button>
        </div>
      </div>

      <div v-if="isLoading && !analyticsData" class="flex items-center justify-center py-32">
        <div class="text-center">
          <div class="animate-spin rounded-full h-12 w-12 border-[3px] border-emerald-100 border-t-emerald-600 mx-auto"></div>
          <p class="mt-4 text-gray-500 font-medium animate-pulse">Gathering insights...</p>
        </div>
      </div>

      <div v-else-if="analyticsData" class="space-y-6">

        <div
          v-if="analyticsData.executive_summary"
          class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
        >
          <div class="flex flex-col md:flex-row">
            <div
              :class="[
                'w-full md:w-1.5 h-2 md:h-auto',
                analyticsData.executive_summary.tone === 'positive' ? 'bg-emerald-500' :
                analyticsData.executive_summary.tone === 'concern' ? 'bg-amber-500' :
                'bg-blue-500'
              ]"
            ></div>

            <div class="p-6 flex-1">
              <div class="flex items-start gap-4">
                <div
                  :class="[
                    'p-2.5 rounded-lg shrink-0',
                    analyticsData.executive_summary.tone === 'positive' ? 'bg-emerald-50 text-emerald-600' :
                    analyticsData.executive_summary.tone === 'concern' ? 'bg-amber-50 text-amber-600' :
                    'bg-blue-50 text-blue-600'
                  ]"
                >
                  <svg v-if="analyticsData.executive_summary.tone === 'positive'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                  <svg v-else-if="analyticsData.executive_summary.tone === 'concern'" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                  <svg v-else class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                  <h2 class="text-base font-semibold text-gray-900 mb-1">Executive Summary</h2>
                  <p class="text-gray-600 text-sm leading-relaxed max-w-4xl">
                    {{ analyticsData.executive_summary.text }}
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="analyticsData.action_suggestions?.length">
          <div class="flex items-center justify-between mb-4 px-1">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
              Recommended Actions
            </h2>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div
              v-for="(suggestion, index) in analyticsData.action_suggestions"
              :key="index"
              @click="navigateTo(suggestion.action_url)"
              class="group bg-white rounded-xl p-5 border border-gray-200 shadow-sm hover:shadow-md hover:border-emerald-300 transition-all cursor-pointer relative overflow-hidden"
            >
              <div :class="[
                'absolute top-0 left-0 w-full h-1',
                suggestion.priority === 'high' ? 'bg-rose-500' :
                suggestion.priority === 'medium' ? 'bg-amber-500' :
                'bg-gray-300'
              ]"></div>

              <div class="flex flex-col h-full">
                <div class="flex justify-between items-start mb-3">
                  <span class="text-2xl filter grayscale group-hover:grayscale-0 transition-all">{{ suggestion.icon }}</span>
                  <span
                    :class="[
                      'text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-full',
                      suggestion.priority === 'high' ? 'bg-rose-50 text-rose-700' :
                      suggestion.priority === 'medium' ? 'bg-amber-50 text-amber-700' :
                      'bg-gray-100 text-gray-600'
                    ]"
                  >
                    {{ suggestion.priority }}
                  </span>
                </div>

                <h3 class="text-sm font-semibold text-gray-900 mb-1 line-clamp-1">{{ suggestion.category }}</h3>
                <p class="text-sm text-gray-500 mb-4 line-clamp-2 flex-grow">{{ suggestion.message }}</p>

                <div class="flex items-center text-xs font-medium text-emerald-600 group-hover:text-emerald-700 transition-colors">
                  {{ suggestion.action_label }}
                  <svg class="w-3 h-3 ml-1 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"></path></svg>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
            <div class="flex justify-between items-start mb-4">
              <div>
                <p class="text-sm font-medium text-gray-500">Weather</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">{{ analyticsData.weather?.avg_temperature ?? '--' }}°C</h3>
              </div>
              <div class="w-10 h-10 rounded-lg bg-sky-50 flex items-center justify-center text-sky-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
              </div>
            </div>
            <div class="space-y-2 pt-2 border-t border-gray-50">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Rainfall</span>
                <span class="font-medium text-gray-900">{{ analyticsData.weather?.total_rainfall ?? '--' }} mm</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Humidity</span>
                <span class="font-medium text-gray-900">{{ analyticsData.weather?.avg_humidity ?? '--' }}%</span>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
            <div class="flex justify-between items-start mb-4">
              <div>
                <p class="text-sm font-medium text-gray-500">Revenue</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">₱{{ formatNumber(analyticsData.sales?.total_revenue ?? 0) }}</h3>
              </div>
              <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center text-emerald-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
              </div>
            </div>
            <div class="space-y-2 pt-2 border-t border-gray-50">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Orders</span>
                <span class="font-medium text-gray-900">{{ analyticsData.sales?.total_orders ?? 0 }}</span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Pending</span>
                <span class="font-medium text-amber-600">{{ analyticsData.sales?.pending_orders ?? 0 }}</span>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
            <div class="flex justify-between items-start mb-4">
              <div>
                <p class="text-sm font-medium text-gray-500">Expenses</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">₱{{ formatNumber(analyticsData.expenses?.total_expenses ?? 0) }}</h3>
              </div>
              <div class="w-10 h-10 rounded-lg bg-rose-50 flex items-center justify-center text-rose-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"></path></svg>
              </div>
            </div>
            <div class="space-y-2 pt-2 border-t border-gray-50">
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Trend</span>
                <span :class="[
                  'font-medium flex items-center gap-1',
                  (analyticsData.expenses?.trend_percentage ?? 0) > 0 ? 'text-rose-600' : 'text-emerald-600'
                ]">
                  {{ (analyticsData.expenses?.trend_percentage ?? 0) > 0 ? '+' : '' }}{{ analyticsData.expenses?.trend_percentage ?? 0 }}%
                </span>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-gray-500">Records</span>
                <span class="font-medium text-gray-900">{{ analyticsData.expenses?.expense_count ?? 0 }}</span>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
            <div class="flex justify-between items-start mb-4">
              <div>
                <p class="text-sm font-medium text-gray-500">Stock Value</p>
                <h3 class="text-2xl font-bold text-gray-900 mt-1">₱{{ formatNumber(analyticsData.inventory?.total_value ?? 0) }}</h3>
              </div>
              <div class="w-10 h-10 rounded-lg bg-violet-50 flex items-center justify-center text-violet-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
              </div>
            </div>
            <div class="space-y-2 pt-2 border-t border-gray-50">
               <div class="flex justify-between text-sm">
                <span class="text-gray-500">Usage</span>
                <span class="font-medium text-gray-900">{{ formatNumber(analyticsData.inventory?.historical_usage?.total_consumed ?? 0) }} units</span>
              </div>
              <div v-if="analyticsData.inventory?.historical_usage?.most_consumed_item" class="flex justify-between items-center text-sm">
                <span class="text-gray-500 truncate max-w-[80px]">Top Item</span>
                <span class="font-medium text-violet-600 truncate max-w-[100px]" :title="analyticsData.inventory.historical_usage.most_consumed_item.name">
                  {{ analyticsData.inventory.historical_usage.most_consumed_item.name }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
           <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 flex items-center justify-between">
              <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fields Area</p>
                <p class="text-lg font-bold text-gray-900">{{ analyticsData.fields?.total_area ?? 0 }} ha</p>
              </div>
              <div class="text-right">
                <p class="text-xs text-emerald-600 font-medium">{{ analyticsData.fields?.utilization_rate ?? 0 }}%</p>
                <p class="text-[10px] text-gray-400">Utilized</p>
              </div>
           </div>

           <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 flex items-center justify-between">
              <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Nursery</p>
                <p class="text-lg font-bold text-gray-900">{{ analyticsData.nursery?.active_batches ?? 0 }}</p>
              </div>
              <div class="text-right">
                <p class="text-xs text-emerald-600 font-medium">{{ analyticsData.nursery?.ready_for_transplant ?? 0 }}</p>
                <p class="text-[10px] text-gray-400">Ready</p>
              </div>
           </div>

           <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 flex items-center justify-between">
              <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pest Incidents</p>
                <p class="text-lg font-bold text-gray-900">{{ analyticsData.pests?.total_incidents ?? 0 }}</p>
              </div>
              <div class="text-right">
                <p :class="['text-xs font-medium', (analyticsData.pests?.active_incidents ?? 0) > 0 ? 'text-rose-600' : 'text-emerald-600']">
                  {{ analyticsData.pests?.active_incidents ?? 0 }}
                </p>
                <p class="text-[10px] text-gray-400">Active</p>
              </div>
           </div>

           <div class="bg-white rounded-lg p-4 shadow-sm border border-gray-200 flex items-center justify-between">
              <div>
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Pending Tasks</p>
                <p class="text-lg font-bold text-gray-900">{{ analyticsData.tasks?.total_tasks - analyticsData.tasks?.completed_tasks }}</p>
              </div>
              <div class="text-right">
                <p :class="['text-xs font-medium', (analyticsData.tasks?.overdue_tasks ?? 0) > 0 ? 'text-rose-600' : 'text-gray-400']">
                  {{ analyticsData.tasks?.overdue_tasks ?? 0 }}
                </p>
                <p class="text-[10px] text-gray-400">Overdue</p>
              </div>
           </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

          <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200 flex flex-col">
            <div class="mb-6 flex justify-between items-center">
              <h3 class="text-lg font-bold text-gray-900">Financial Overview</h3>
              <div class="flex gap-4 text-xs">
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-emerald-500"></span> Revenue</div>
                <div class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-sm bg-rose-500"></span> Expenses</div>
              </div>
            </div>

            <div class="flex-1 flex items-end justify-center gap-8 md:gap-16 min-h-[240px] border-b border-gray-100 pb-2 relative">
              <div class="absolute inset-0 flex flex-col justify-between pointer-events-none">
                 <div class="w-full border-t border-dashed border-gray-100 h-px"></div>
                 <div class="w-full border-t border-dashed border-gray-100 h-px"></div>
                 <div class="w-full border-t border-dashed border-gray-100 h-px"></div>
                 <div class="w-full border-t border-dashed border-gray-100 h-px"></div>
              </div>

              <div class="relative z-10 flex flex-col items-center gap-3 group">
                <span class="text-xs font-bold text-emerald-700 opacity-0 group-hover:opacity-100 transition-opacity absolute -top-6">₱{{ formatNumber(analyticsData.sales?.total_revenue ?? 0) }}</span>
                <div
                  class="w-16 md:w-20 bg-emerald-500 rounded-t-sm transition-all duration-500 hover:bg-emerald-600"
                  :style="{ height: `${Math.min((analyticsData.sales?.total_revenue ?? 0) / Math.max((analyticsData.sales?.total_revenue ?? 1), (analyticsData.expenses?.total_expenses ?? 1)) * 200, 200)}px` }"
                ></div>
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Revenue</span>
              </div>

              <div class="relative z-10 flex flex-col items-center gap-3 group">
                <span class="text-xs font-bold text-rose-700 opacity-0 group-hover:opacity-100 transition-opacity absolute -top-6">₱{{ formatNumber(analyticsData.expenses?.total_expenses ?? 0) }}</span>
                <div
                  class="w-16 md:w-20 bg-rose-500 rounded-t-sm transition-all duration-500 hover:bg-rose-600"
                  :style="{ height: `${Math.min((analyticsData.expenses?.total_expenses ?? 0) / Math.max((analyticsData.sales?.total_revenue ?? 1), (analyticsData.expenses?.total_expenses ?? 1)) * 200, 200)}px` }"
                ></div>
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Expenses</span>
              </div>

               <div class="relative z-10 flex flex-col items-center gap-3 group">
                 <span class="text-xs font-bold opacity-0 group-hover:opacity-100 transition-opacity absolute -top-6" :class="netProfit >= 0 ? 'text-blue-700' : 'text-gray-700'">₱{{ formatNumber(Math.abs(netProfit)) }}</span>
                <div
                  class="w-16 md:w-20 rounded-t-sm transition-all duration-500 opacity-80"
                  :class="netProfit >= 0 ? 'bg-blue-500 hover:bg-blue-600' : 'bg-gray-400 hover:bg-gray-500'"
                  :style="{ height: `${Math.min(Math.abs(netProfit) / Math.max((analyticsData.sales?.total_revenue ?? 1), (analyticsData.expenses?.total_expenses ?? 1)) * 200, 200)}px` }"
                ></div>
                <span class="text-xs font-medium text-gray-500 uppercase tracking-wide">Net Profit</span>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-6">
            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
               <h3 class="text-lg font-bold text-gray-900 mb-6">Task Distribution</h3>
               <div class="flex items-center">
                  <div class="relative w-32 h-32 mr-8 flex-shrink-0">
                    <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
                      <circle cx="50" cy="50" r="40" fill="transparent" stroke="#e5e7eb" stroke-width="12" />
                      <circle cx="50" cy="50" r="40" fill="transparent" stroke="#10b981" stroke-width="12"
                        :stroke-dasharray="`${completedPercent * 2.51} 251.2`" class="transition-all duration-1000 ease-out" />
                      <circle cx="50" cy="50" r="40" fill="transparent" stroke="#f59e0b" stroke-width="12"
                        :stroke-dasharray="`${pendingPercent * 2.51} 251.2`" :stroke-dashoffset="`${-completedPercent * 2.51}`" class="transition-all duration-1000 ease-out" />
                      <circle cx="50" cy="50" r="40" fill="transparent" stroke="#f43f5e" stroke-width="12"
                        :stroke-dasharray="`${overduePercent * 2.51} 251.2`" :stroke-dashoffset="`${-(completedPercent + pendingPercent) * 2.51}`" class="transition-all duration-1000 ease-out" />
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                       <span class="text-2xl font-bold text-gray-900">{{ analyticsData.tasks?.total_tasks ?? 0 }}</span>
                       <span class="text-[10px] text-gray-400 uppercase">Total</span>
                    </div>
                  </div>

                  <div class="flex-1 space-y-3">
                    <div class="flex justify-between items-center p-2 rounded hover:bg-gray-50">
                       <div class="flex items-center gap-2">
                          <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                          <span class="text-sm text-gray-600">Completed</span>
                       </div>
                       <span class="font-bold text-sm text-gray-900">{{ analyticsData.tasks?.completed_tasks ?? 0 }}</span>
                    </div>
                     <div class="flex justify-between items-center p-2 rounded hover:bg-gray-50">
                       <div class="flex items-center gap-2">
                          <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                          <span class="text-sm text-gray-600">Pending</span>
                       </div>
                       <span class="font-bold text-sm text-gray-900">{{ analyticsData.tasks?.pending_tasks ?? 0 }}</span>
                    </div>
                     <div class="flex justify-between items-center p-2 rounded hover:bg-gray-50">
                       <div class="flex items-center gap-2">
                          <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                          <span class="text-sm text-gray-600">Overdue</span>
                       </div>
                       <span class="font-bold text-sm text-gray-900">{{ analyticsData.tasks?.overdue_tasks ?? 0 }}</span>
                    </div>
                  </div>
               </div>
            </div>

            <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
               <h3 class="text-lg font-bold text-gray-900 mb-4">Top Expenses</h3>
               <div class="space-y-4">
                  <div v-for="(data, category) in analyticsData.expenses?.by_category ?? {}" :key="category">
                    <div class="flex justify-between text-xs mb-1">
                      <span class="font-medium text-gray-700 capitalize">{{ category }}</span>
                      <span class="text-gray-500">{{ data.percentage }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                      <div class="bg-gray-800 h-1.5 rounded-full" :style="{ width: `${data.percentage}%` }"></div>
                    </div>
                  </div>
                   <div v-if="!Object.keys(analyticsData.expenses?.by_category ?? {}).length" class="text-center text-sm text-gray-400 py-4">
                      No data available
                   </div>
               </div>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl p-6 shadow-sm border border-gray-200">
           <h3 class="text-lg font-bold text-gray-900 mb-6">Labor Workforce</h3>
           <div class="grid grid-cols-2 md:grid-cols-4 gap-4 divide-x divide-gray-100">
              <div class="px-4 text-center">
                 <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Total Laborers</div>
                 <div class="text-2xl font-bold text-gray-900">{{ analyticsData.laborers?.total_laborers ?? 0 }}</div>
              </div>
              <div class="px-4 text-center">
                 <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Active Now</div>
                 <div class="text-2xl font-bold text-emerald-600">{{ analyticsData.laborers?.active_laborers ?? 0 }}</div>
              </div>
              <div class="px-4 text-center">
                 <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Payroll Est.</div>
                 <div class="text-2xl font-bold text-gray-900">₱{{ formatNumber(analyticsData.laborers?.total_labor_cost ?? 0) }}</div>
              </div>
              <div class="px-4 text-center border-none">
                 <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Efficiency</div>
                 <div class="text-2xl font-bold text-amber-600">{{ analyticsData.laborers?.completion_rate ?? 0 }}%</div>
              </div>
           </div>
        </div>

        <div v-if="analyticsData.pests?.forecasts?.length" class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
           <div class="p-6 border-b border-gray-100 flex justify-between items-center">
              <h3 class="text-lg font-bold text-gray-900">Disease & Pest Forecast</h3>
              <span class="text-xs font-medium text-gray-500 bg-gray-100 px-3 py-1 rounded-full">7 Day Outlook</span>
           </div>
           <div class="p-6 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div v-for="(field, index) in analyticsData.pests.forecasts" :key="index" class="space-y-3">
                 <div class="text-sm font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-gray-400"></span>
                    {{ field.field_name }}
                 </div>
                 <div class="space-y-2">
                    <div v-for="(pred, pIndex) in field.predictions.slice(0, 2)" :key="pIndex"
                         class="flex items-start gap-3 p-3 rounded-lg border border-gray-100 bg-gray-50/50 hover:border-gray-300 transition-colors">
                        <div class="mt-1">
                           <span v-if="pred.risks[0].risk_level === 'High'" class="block w-2 h-2 rounded-full bg-rose-500 shadow-sm shadow-rose-200"></span>
                           <span v-else-if="pred.risks[0].risk_level === 'Moderate'" class="block w-2 h-2 rounded-full bg-amber-500 shadow-sm shadow-amber-200"></span>
                           <span v-else class="block w-2 h-2 rounded-full bg-blue-500 shadow-sm shadow-blue-200"></span>
                        </div>
                        <div>
                           <div class="flex justify-between items-center w-full gap-2">
                              <span class="text-xs font-semibold text-gray-700">{{ pred.day_name }}</span>
                              <span class="text-[10px] text-gray-400">{{ pred.date }}</span>
                           </div>
                           <p class="text-sm font-medium text-gray-900 mt-0.5">{{ pred.risks[0].pest_name }}</p>
                           <p class="text-xs text-gray-500 mt-1 leading-snug">{{ pred.risks[0].description }}</p>
                        </div>
                    </div>
                 </div>
              </div>
           </div>
        </div>

        <div v-if="analyticsData.financial_forecast" class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
           <div class="mb-8 flex justify-between items-center">
              <h3 class="text-lg font-bold text-gray-900">Projected Cash Flow</h3>
              <div class="flex gap-4 text-xs">
                 <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-emerald-400"></span> Revenue</div>
                 <div class="flex items-center gap-1.5"><span class="w-3 h-3 rounded bg-rose-400"></span> Expenses</div>
              </div>
           </div>

           <div class="h-64 flex items-end justify-between gap-2 md:gap-4 overflow-x-auto pb-2">
              <div v-for="(month, index) in analyticsData.financial_forecast.months" :key="index" class="flex flex-col items-center gap-2 flex-1 min-w-[60px]">
                 <div class="w-full flex items-end justify-center gap-1 h-48 relative border-b border-gray-100">
                    <div class="w-3 md:w-5 bg-emerald-400 rounded-t-sm hover:bg-emerald-500 transition-all relative group"
                       :style="{ height: `${Math.min((analyticsData.financial_forecast.projected_revenue[index] / (Math.max(...analyticsData.financial_forecast.projected_revenue, ...analyticsData.financial_forecast.projected_expenses) || 1)) * 100, 100)}%` }">
                       <div class="opacity-0 group-hover:opacity-100 absolute bottom-full left-1/2 -translate-x-1/2 mb-1 bg-gray-900 text-white text-[10px] px-2 py-1 rounded whitespace-nowrap z-20 shadow-lg">
                          Rev: ₱{{ formatNumber(analyticsData.financial_forecast.projected_revenue[index]) }}
                       </div>
                    </div>
                    <div class="w-3 md:w-5 bg-rose-400 rounded-t-sm hover:bg-rose-500 transition-all relative group"
                       :style="{ height: `${Math.min((analyticsData.financial_forecast.projected_expenses[index] / (Math.max(...analyticsData.financial_forecast.projected_revenue, ...analyticsData.financial_forecast.projected_expenses) || 1)) * 100, 100)}%` }">
                        <div class="opacity-0 group-hover:opacity-100 absolute bottom-full left-1/2 -translate-x-1/2 mb-1 bg-gray-900 text-white text-[10px] px-2 py-1 rounded whitespace-nowrap z-20 shadow-lg">
                          Exp: ₱{{ formatNumber(analyticsData.financial_forecast.projected_expenses[index]) }}
                       </div>
                    </div>
                 </div>
                 <span class="text-xs font-medium text-gray-500 truncate w-full text-center">{{ month }}</span>
              </div>
           </div>
        </div>

      </div>

      <div v-else class="text-center py-32 bg-white rounded-xl border border-gray-200 border-dashed">
        <div class="w-16 h-16 bg-gray-50 rounded-full flex items-center justify-center mx-auto mb-4 text-gray-400">
           <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-1">No Analytics Available</h3>
        <p class="text-gray-500 text-sm mb-6">We couldn't load the data for the selected period.</p>
        <button
          @click="fetchAnalytics"
          class="px-5 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium text-sm transition-colors shadow-sm"
        >
          Try Again
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import api from '@/services/api';

const router = useRouter();

// State
const isLoading = ref(true);
const analyticsData = ref(null);
const startDate = ref(new Date(Date.now() - 90 * 24 * 60 * 60 * 1000).toISOString().split('T')[0]); // 90 days ago
const endDate = ref(new Date().toISOString().split('T')[0]); // Today

// Computed
const netProfit = computed(() => {
  const revenue = analyticsData.value?.sales?.total_revenue ?? 0;
  const expenses = analyticsData.value?.expenses?.total_expenses ?? 0;
  return revenue - expenses;
});

const completedPercent = computed(() => {
  const total = analyticsData.value?.tasks?.total_tasks ?? 0;
  const completed = analyticsData.value?.tasks?.completed_tasks ?? 0;
  return total > 0 ? (completed / total) * 100 : 0;
});

const pendingPercent = computed(() => {
  const total = analyticsData.value?.tasks?.total_tasks ?? 0;
  const pending = analyticsData.value?.tasks?.pending_tasks ?? 0;
  return total > 0 ? (pending / total) * 100 : 0;
});

const overduePercent = computed(() => {
  const total = analyticsData.value?.tasks?.total_tasks ?? 0;
  const overdue = analyticsData.value?.tasks?.overdue_tasks ?? 0;
  return total > 0 ? (overdue / total) * 100 : 0;
});

// Methods
const formatNumber = (num) => {
  return new Intl.NumberFormat('en-PH').format(num);
};

const navigateTo = (path) => {
  router.push(path);
};

const fetchAnalytics = async () => {
  isLoading.value = true;
  try {
    const response = await api.get('/analytics/data-analysis', {
      params: {
        start_date: startDate.value,
        end_date: endDate.value,
      },
    });
    analyticsData.value = response.data;
  } catch (error) {
    console.error('Failed to fetch analytics:', error);
    analyticsData.value = null;
  } finally {
    isLoading.value = false;
  }
};

// Lifecycle
onMounted(() => {
  fetchAnalytics();
});
</script>

<style scoped>
/* Standardize font display for numbers */
h3, .text-2xl, .text-lg {
  font-feature-settings: "tnum";
  font-variant-numeric: tabular-nums;
}

.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
