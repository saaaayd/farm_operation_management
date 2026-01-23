<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-gray-50 to-slate-100">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold bg-gradient-to-r from-emerald-600 to-teal-600 bg-clip-text text-transparent">
            Data Analytics Dashboard
          </h1>
          <p class="text-gray-500 mt-1">Comprehensive insights from your farm data</p>
        </div>

        <!-- Date Range Filter -->
        <div class="flex items-center gap-3 bg-white/80 backdrop-blur-sm p-3 rounded-xl shadow-sm border border-gray-100">
          <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-600">From</label>
            <input
              v-model="startDate"
              type="date"
              class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
            />
          </div>
          <div class="flex items-center gap-2">
            <label class="text-sm font-medium text-gray-600">To</label>
            <input
              v-model="endDate"
              type="date"
              class="px-3 py-1.5 border border-gray-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-transparent"
            />
          </div>
          <button
            @click="fetchAnalytics"
            :disabled="isLoading"
            class="px-4 py-1.5 bg-gradient-to-r from-emerald-500 to-teal-500 text-white rounded-lg text-sm font-medium hover:from-emerald-600 hover:to-teal-600 transition-all disabled:opacity-50"
          >
            <span v-if="isLoading" class="flex items-center gap-2">
              <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Loading...
            </span>
            <span v-else>Update</span>
          </button>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="isLoading && !analyticsData" class="flex items-center justify-center py-20">
        <div class="text-center">
          <div class="animate-spin rounded-full h-16 w-16 border-4 border-emerald-200 border-t-emerald-600 mx-auto"></div>
          <p class="mt-4 text-gray-600 font-medium">Loading analytics...</p>
        </div>
      </div>

      <!-- Main Content -->
      <div v-else-if="analyticsData" class="space-y-8">
        <!-- Executive Summary Card -->
        <div 
          v-if="analyticsData.executive_summary"
          :class="[
            'rounded-2xl p-6 shadow-lg border backdrop-blur-sm transition-all',
            analyticsData.executive_summary.tone === 'positive' ? 'bg-gradient-to-r from-emerald-50 to-green-50 border-emerald-200' :
            analyticsData.executive_summary.tone === 'concern' ? 'bg-gradient-to-r from-amber-50 to-red-50 border-amber-200' :
            'bg-gradient-to-r from-blue-50 to-slate-50 border-blue-200'
          ]"
        >
          <div class="flex items-start gap-4">
            <div 
              :class="[
                'p-3 rounded-xl shadow-sm',
                analyticsData.executive_summary.tone === 'positive' ? 'bg-emerald-100 text-emerald-600' :
                analyticsData.executive_summary.tone === 'concern' ? 'bg-amber-100 text-amber-600' :
                'bg-blue-100 text-blue-600'
              ]"
            >
              <span class="text-2xl">✨</span>
            </div>
            <div>
              <h2 
                :class="[
                  'text-lg font-bold mb-2',
                  analyticsData.executive_summary.tone === 'positive' ? 'text-emerald-800' :
                  analyticsData.executive_summary.tone === 'concern' ? 'text-amber-800' :
                  'text-blue-800'
                ]"
              >
                Executive Summary
              </h2>
              <p class="text-gray-700 leading-relaxed text-sm md:text-base">
                {{ analyticsData.executive_summary.text }}
              </p>
            </div>
          </div>
        </div>

        <!-- Action Suggestions Panel -->
        <div v-if="analyticsData.action_suggestions?.length" class="bg-gradient-to-r from-amber-50 to-orange-50 rounded-2xl p-6 border border-amber-200/50 shadow-lg">
          <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-amber-800 flex items-center gap-2">
              <span class="text-2xl">💡</span>
              Action Suggestions
            </h2>
            <span class="text-xs bg-amber-200/50 text-amber-700 px-2 py-1 rounded-full">
              {{ analyticsData.action_suggestions.length }} recommendations
            </span>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div
              v-for="(suggestion, index) in analyticsData.action_suggestions"
              :key="index"
              :class="[
                'group relative bg-white/80 backdrop-blur-sm rounded-xl p-4 border shadow-sm hover:shadow-md transition-all cursor-pointer',
                suggestion.priority === 'high' ? 'border-red-200 hover:border-red-300' :
                suggestion.priority === 'medium' ? 'border-amber-200 hover:border-amber-300' :
                'border-gray-200 hover:border-gray-300'
              ]"
              @click="navigateTo(suggestion.action_url)"
            >
              <div class="flex items-start gap-3">
                <span class="text-2xl">{{ suggestion.icon }}</span>
                <div class="flex-1 min-w-0">
                  <div class="flex items-center gap-2 mb-1">
                    <span
                      :class="[
                        'text-[10px] font-bold uppercase px-1.5 py-0.5 rounded',
                        suggestion.priority === 'high' ? 'bg-red-100 text-red-700' :
                        suggestion.priority === 'medium' ? 'bg-amber-100 text-amber-700' :
                        'bg-gray-100 text-gray-600'
                      ]"
                    >
                      {{ suggestion.priority }}
                    </span>
                    <span class="text-[10px] text-gray-400 uppercase">{{ suggestion.category }}</span>
                  </div>
                  <p class="text-sm text-gray-700 line-clamp-2">{{ suggestion.message }}</p>
                </div>
              </div>
              <div class="mt-3 text-xs font-medium text-emerald-600 group-hover:text-emerald-700 flex items-center gap-1">
                {{ suggestion.action_label }}
                <svg class="w-3 h-3 transform group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
              </div>
            </div>
          </div>
        </div>

        <!-- Summary Cards Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
          <!-- Weather Card -->
          <div class="bg-white rounded-2xl p-5 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-sky-400 to-blue-500 flex items-center justify-center">
                <span class="text-lg">🌤️</span>
              </div>
              <h3 class="font-semibold text-gray-700">Weather</h3>
            </div>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Avg Temp</span>
                <span class="font-bold text-gray-800">{{ analyticsData.weather?.avg_temperature ?? '--' }}°C</span>
              </div>
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Total Rainfall</span>
                <span class="font-bold text-gray-800">{{ analyticsData.weather?.total_rainfall ?? '--' }} mm</span>
              </div>
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Avg Humidity</span>
                <span class="font-bold text-gray-800">{{ analyticsData.weather?.avg_humidity ?? '--' }}%</span>
              </div>
            </div>
          </div>

          <!-- Sales Card -->
          <div class="bg-white rounded-2xl p-5 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-green-500 flex items-center justify-center">
                <span class="text-lg">💰</span>
              </div>
              <h3 class="font-semibold text-gray-700">Sales</h3>
            </div>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Total Revenue</span>
                <span class="font-bold text-emerald-600">₱{{ formatNumber(analyticsData.sales?.total_revenue ?? 0) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Total Orders</span>
                <span class="font-bold text-gray-800">{{ analyticsData.sales?.total_orders ?? 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Pending</span>
                <span class="font-bold text-amber-600">{{ analyticsData.sales?.pending_orders ?? 0 }}</span>
              </div>
            </div>
          </div>

          <!-- Expenses Card -->
          <div class="bg-white rounded-2xl p-5 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-400 to-rose-500 flex items-center justify-center">
                <span class="text-lg">📊</span>
              </div>
              <h3 class="font-semibold text-gray-700">Expenses</h3>
            </div>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Total Expenses</span>
                <span class="font-bold text-red-600">₱{{ formatNumber(analyticsData.expenses?.total_expenses ?? 0) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Records</span>
                <span class="font-bold text-gray-800">{{ analyticsData.expenses?.expense_count ?? 0 }}</span>
              </div>
              <div class="flex justify-between items-center">
                <span class="text-xs text-gray-500">Trend</span>
                <span :class="[
                  'font-bold text-sm flex items-center gap-1',
                  (analyticsData.expenses?.trend_percentage ?? 0) > 0 ? 'text-red-500' : 'text-emerald-500'
                ]">
                  <svg v-if="(analyticsData.expenses?.trend_percentage ?? 0) > 0" class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L11 7.414V15a1 1 0 11-2 0V7.414L6.707 9.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                  </svg>
                  <svg v-else class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L9 12.586V5a1 1 0 012 0v7.586l2.293-2.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                  </svg>
                  {{ Math.abs(analyticsData.expenses?.trend_percentage ?? 0) }}%
                </span>
              </div>
            </div>
          </div>

          <!-- Fields Card -->
          <div class="bg-white rounded-2xl p-5 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-lime-400 to-green-500 flex items-center justify-center">
                <span class="text-lg">🌾</span>
              </div>
              <h3 class="font-semibold text-gray-700">Fields</h3>
            </div>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Total Fields</span>
                <span class="font-bold text-gray-800">{{ analyticsData.fields?.total_fields ?? 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Total Area</span>
                <span class="font-bold text-gray-800">{{ analyticsData.fields?.total_area ?? 0 }} ha</span>
              </div>
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Utilization</span>
                <span class="font-bold text-emerald-600">{{ analyticsData.fields?.utilization_rate ?? 0 }}%</span>
              </div>
            </div>
          </div>

          <!-- Nursery Card -->
          <div class="bg-white rounded-2xl p-5 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-orange-400 to-amber-500 flex items-center justify-center">
                <span class="text-lg">🌱</span>
              </div>
              <h3 class="font-semibold text-gray-700">Nursery</h3>
            </div>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Active Batches</span>
                <span class="font-bold text-gray-800">{{ analyticsData.nursery?.active_batches ?? 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Ready to Transplant</span>
                <span class="font-bold text-emerald-600">{{ analyticsData.nursery?.ready_for_transplant ?? 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Total Batches</span>
                <span class="font-bold text-gray-800">{{ analyticsData.nursery?.total_batches ?? 0 }}</span>
              </div>
            </div>
          </div>

          <!-- Inventory Card -->
          <div class="bg-white rounded-2xl p-5 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-violet-400 to-purple-500 flex items-center justify-center">
                <span class="text-lg">📦</span>
              </div>
              <h3 class="font-semibold text-gray-700">Inventory</h3>
            </div>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Stock Value</span>
                <span class="font-bold text-gray-800">₱{{ formatNumber(analyticsData.inventory?.total_value ?? 0) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Consumed (Period)</span>
                <span class="font-bold text-gray-800">{{ formatNumber(analyticsData.inventory?.historical_usage?.total_consumed ?? 0) }} units</span>
              </div>
              <div v-if="analyticsData.inventory?.historical_usage?.most_consumed_item" class="pt-2 border-t border-gray-50">
                <div class="text-[10px] text-gray-500 uppercase tracking-wider mb-1">Top Used Item</div>
                <div class="flex justify-between items-center">
                  <span class="text-xs font-medium text-gray-700 truncate max-w-[120px]" :title="analyticsData.inventory.historical_usage.most_consumed_item.name">
                    {{ analyticsData.inventory.historical_usage.most_consumed_item.name }}
                  </span>
                  <span class="text-xs font-bold text-violet-600">
                    {{ formatNumber(analyticsData.inventory.historical_usage.most_consumed_item.quantity) }}
                  </span>
                </div>
              </div>
            </div>
          </div>

          <!-- Pests Card -->
          <div class="bg-white rounded-2xl p-5 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-pink-400 to-rose-500 flex items-center justify-center">
                <span class="text-lg">🐛</span>
              </div>
              <h3 class="font-semibold text-gray-700">Pests</h3>
            </div>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Total Incidents</span>
                <span class="font-bold text-gray-800">{{ analyticsData.pests?.total_incidents ?? 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Active</span>
                <span :class="[
                  'font-bold',
                  (analyticsData.pests?.active_incidents ?? 0) > 0 ? 'text-red-600' : 'text-emerald-600'
                ]">{{ analyticsData.pests?.active_incidents ?? 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Resolved</span>
                <span class="font-bold text-emerald-600">{{ analyticsData.pests?.resolved_incidents ?? 0 }}</span>
              </div>
            </div>
          </div>

          <!-- Tasks Card -->
          <div class="bg-white rounded-2xl p-5 shadow-lg border border-gray-100 hover:shadow-xl transition-shadow">
            <div class="flex items-center gap-3 mb-3">
              <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center">
                <span class="text-lg">📋</span>
              </div>
              <h3 class="font-semibold text-gray-700">Tasks</h3>
            </div>
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Total Tasks</span>
                <span class="font-bold text-gray-800">{{ analyticsData.tasks?.total_tasks ?? 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Completed</span>
                <span class="font-bold text-emerald-600">{{ analyticsData.tasks?.completed_tasks ?? 0 }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-xs text-gray-500">Overdue</span>
                <span :class="[
                  'font-bold',
                  (analyticsData.tasks?.overdue_tasks ?? 0) > 0 ? 'text-red-600' : 'text-emerald-600'
                ]">{{ analyticsData.tasks?.overdue_tasks ?? 0 }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Detailed Charts Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Revenue vs Expenses -->
          <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Revenue vs Expenses</h3>
            <div class="h-64 flex items-end justify-center gap-6">
              <div class="flex flex-col items-center gap-2">
                <div
                  class="w-20 bg-gradient-to-t from-emerald-500 to-emerald-300 rounded-t-lg transition-all"
                  :style="{ height: `${Math.min((analyticsData.sales?.total_revenue ?? 0) / Math.max((analyticsData.sales?.total_revenue ?? 1), (analyticsData.expenses?.total_expenses ?? 1)) * 200, 200)}px` }"
                ></div>
                <span class="text-xs font-medium text-gray-600">Revenue</span>
                <span class="text-sm font-bold text-emerald-600">₱{{ formatNumber(analyticsData.sales?.total_revenue ?? 0) }}</span>
              </div>
              <div class="flex flex-col items-center gap-2">
                <div
                  class="w-20 bg-gradient-to-t from-red-500 to-red-300 rounded-t-lg transition-all"
                  :style="{ height: `${Math.min((analyticsData.expenses?.total_expenses ?? 0) / Math.max((analyticsData.sales?.total_revenue ?? 1), (analyticsData.expenses?.total_expenses ?? 1)) * 200, 200)}px` }"
                ></div>
                <span class="text-xs font-medium text-gray-600">Expenses</span>
                <span class="text-sm font-bold text-red-600">₱{{ formatNumber(analyticsData.expenses?.total_expenses ?? 0) }}</span>
              </div>
              <div class="flex flex-col items-center gap-2">
                <div
                  class="w-20 rounded-t-lg transition-all"
                  :class="netProfit >= 0 ? 'bg-gradient-to-t from-blue-500 to-blue-300' : 'bg-gradient-to-t from-gray-400 to-gray-300'"
                  :style="{ height: `${Math.min(Math.abs(netProfit) / Math.max((analyticsData.sales?.total_revenue ?? 1), (analyticsData.expenses?.total_expenses ?? 1)) * 200, 200)}px` }"
                ></div>
                <span class="text-xs font-medium text-gray-600">Net Profit</span>
                <span :class="['text-sm font-bold', netProfit >= 0 ? 'text-blue-600' : 'text-red-600']">
                  {{ netProfit >= 0 ? '' : '-' }}₱{{ formatNumber(Math.abs(netProfit)) }}
                </span>
              </div>
            </div>
          </div>

          <!-- Task Status Distribution -->
          <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Task Status Distribution</h3>
            <div class="flex items-center justify-center h-64">
              <div class="relative w-48 h-48">
                <!-- Simple pie chart visualization -->
                <svg viewBox="0 0 100 100" class="w-full h-full transform -rotate-90">
                  <circle
                    cx="50" cy="50" r="40"
                    fill="transparent"
                    stroke="#10b981"
                    stroke-width="20"
                    :stroke-dasharray="`${completedPercent * 2.51} 251.2`"
                  />
                  <circle
                    cx="50" cy="50" r="40"
                    fill="transparent"
                    stroke="#f59e0b"
                    stroke-width="20"
                    :stroke-dasharray="`${pendingPercent * 2.51} 251.2`"
                    :stroke-dashoffset="`${-completedPercent * 2.51}`"
                  />
                  <circle
                    cx="50" cy="50" r="40"
                    fill="transparent"
                    stroke="#ef4444"
                    stroke-width="20"
                    :stroke-dasharray="`${overduePercent * 2.51} 251.2`"
                    :stroke-dashoffset="`${-(completedPercent + pendingPercent) * 2.51}`"
                  />
                </svg>
                <div class="absolute inset-0 flex items-center justify-center">
                  <span class="text-2xl font-bold text-gray-800">{{ analyticsData.tasks?.total_tasks ?? 0 }}</span>
                </div>
              </div>
              <div class="ml-6 space-y-3">
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded-full bg-emerald-500"></div>
                  <span class="text-sm text-gray-600">Completed ({{ analyticsData.tasks?.completed_tasks ?? 0 }})</span>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded-full bg-amber-500"></div>
                  <span class="text-sm text-gray-600">Pending ({{ analyticsData.tasks?.pending_tasks ?? 0 }})</span>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-3 h-3 rounded-full bg-red-500"></div>
                  <span class="text-sm text-gray-600">Overdue ({{ analyticsData.tasks?.overdue_tasks ?? 0 }})</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Expense Breakdown -->
          <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Expense Breakdown by Category</h3>
            <div class="space-y-4">
              <div
                v-for="(data, category) in analyticsData.expenses?.by_category ?? {}"
                :key="category"
                class="space-y-1"
              >
                <div class="flex justify-between text-sm">
                  <span class="font-medium text-gray-700 capitalize">{{ category }}</span>
                  <span class="text-gray-600">₱{{ formatNumber(data.total) }} ({{ data.percentage }}%)</span>
                </div>
                <div class="h-2 bg-gray-100 rounded-full overflow-hidden">
                  <div
                    class="h-full bg-gradient-to-r from-rose-400 to-red-500 rounded-full transition-all"
                    :style="{ width: `${data.percentage}%` }"
                  ></div>
                </div>
              </div>
              <div v-if="!Object.keys(analyticsData.expenses?.by_category ?? {}).length" class="text-center text-gray-500 py-8">
                No expense data available
              </div>
            </div>
          </div>

          <!-- Labor Analytics -->
          <div class="bg-white rounded-2xl p-6 shadow-lg border border-gray-100">
            <h3 class="text-lg font-bold text-gray-800 mb-4">Labor Analytics</h3>
            <div class="grid grid-cols-2 gap-4">
              <div class="text-center p-4 bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl">
                <div class="text-3xl font-bold text-blue-600">{{ analyticsData.laborers?.total_laborers ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-1">Total Laborers</div>
              </div>
              <div class="text-center p-4 bg-gradient-to-br from-emerald-50 to-green-50 rounded-xl">
                <div class="text-3xl font-bold text-emerald-600">{{ analyticsData.laborers?.active_laborers ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-1">Active</div>
              </div>
              <div class="text-center p-4 bg-gradient-to-br from-violet-50 to-purple-50 rounded-xl">
                <div class="text-3xl font-bold text-violet-600">₱{{ formatNumber(analyticsData.laborers?.total_labor_cost ?? 0) }}</div>
                <div class="text-xs text-gray-500 mt-1">Total Labor Cost</div>
              </div>
              <div class="text-center p-4 bg-gradient-to-br from-amber-50 to-orange-50 rounded-xl">
                <div class="text-3xl font-bold text-amber-600">{{ analyticsData.laborers?.completion_rate ?? 0 }}%</div>
                <div class="text-xs text-gray-500 mt-1">Completion Rate</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-20">
        <div class="text-6xl mb-4">📊</div>
        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Analytics Data</h3>
        <p class="text-gray-500">Unable to load analytics data. Please try again.</p>
        <button
          @click="fetchAnalytics"
          class="mt-4 px-6 py-2 bg-emerald-500 text-white rounded-lg hover:bg-emerald-600 transition-colors"
        >
          Retry
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
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>
