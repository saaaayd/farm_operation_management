<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Header with Back Button -->
      <div class="mb-8">
        <div class="mb-4">
          <button
            @click="router.push('/laborers/groups')"
            class="flex items-center text-sm text-gray-500 hover:text-gray-700 transition-colors"
          >
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Groups
          </button>
        </div>

        <!-- Stats Overview -->
        <div v-if="group && group.stats" class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs font-medium text-gray-500 uppercase">Total Members</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">{{ group.laborers ? group.laborers.length : 0 }}</p>
            </div>
             <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs font-medium text-gray-500 uppercase">Hourly Run Rate</p>
                <p class="text-2xl font-bold text-gray-900 mt-1">₱{{ group.stats.total_hourly_cost ? Number(group.stats.total_hourly_cost).toFixed(2) : '0.00' }}</p>
                <p class="text-xs text-gray-400 mt-1">*Est. hourly cost of active members</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs font-medium text-gray-500 uppercase">Active Tasks</p>
                <p class="text-2xl font-bold text-blue-600 mt-1">{{ group.stats.active_tasks || 0 }}</p>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
                <p class="text-xs font-medium text-gray-500 uppercase">Tasks Completed</p>
                <p class="text-2xl font-bold text-green-600 mt-1">{{ group.stats.completed_tasks || 0 }}</p>
            </div>
        </div>

        <div v-if="group" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
          <div class="flex flex-col md:flex-row justify-between items-start gap-6">
            <div class="flex items-center gap-4">
               <span
                class="h-16 w-16 rounded-full flex items-center justify-center text-white text-2xl font-bold shadow-sm"
                :style="{ backgroundColor: group.color || '#10B981' }"
              >
                {{ getInitials(group.name) }}
              </span>
              <div>
                <h1 class="text-3xl font-bold text-gray-800">{{ group.name }}</h1>
                <p class="text-gray-500 mt-1 text-lg">{{ group.description || 'No description provided.' }}</p>
              </div>
            </div>
            
             <div class="flex items-center gap-3">
               <!-- Future: Add Edit/Delete Actions here if needed on this page -->
               <div class="text-sm text-gray-500 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-100">
                  Created {{ formatDate(group.created_at) }}
               </div>
            </div>
          </div>
          
          <div class="mt-8 flex gap-8 border-b border-gray-200">
              <button 
                @click="activeTab = 'members'"
                class="pb-4 px-2 font-medium text-sm transition-colors relative"
                :class="activeTab === 'members' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700'"
              >
                  Members
                  <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ group.laborers ? group.laborers.length : 0 }}</span>
                  <div v-if="activeTab === 'members'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600"></div>
              </button>
              <button 
                @click="activeTab = 'tasks'"
                class="pb-4 px-2 font-medium text-sm transition-colors relative"
                :class="activeTab === 'tasks' ? 'text-blue-600' : 'text-gray-500 hover:text-gray-700'"
              >
                  Group Tasks
                  <span class="ml-2 bg-gray-100 text-gray-600 py-0.5 px-2 rounded-full text-xs">{{ group.tasks ? group.tasks.length : 0 }}</span>
                  <div v-if="activeTab === 'tasks'" class="absolute bottom-0 left-0 right-0 h-0.5 bg-blue-600"></div>
              </button>
          </div>
        </div>
        
        <div v-else-if="loading" class="h-64 bg-white rounded-xl shadow-sm border border-gray-100 animate-pulse"></div>
      </div>

      <!-- Content -->
      <div v-if="group && !loading">
          
          <!-- Members Tab -->
          <div v-if="activeTab === 'members'" class="space-y-6">
              <div v-if="group.laborers && group.laborers.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                  <div v-for="laborer in group.laborers" :key="laborer.id" class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow">
                      <div class="flex items-center gap-4">
                          <div class="h-12 w-12 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold text-lg">
                              {{ getInitials(laborer.name) }}
                          </div>
                          <div>
                              <h3 class="font-bold text-gray-900">{{ laborer.name }}</h3>
                              <p class="text-sm text-gray-500">{{ laborer.specialization || 'General Laborer' }}</p>
                          </div>
                      </div>
                      <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-2 gap-4 text-sm">
                          <div>
                              <p class="text-gray-500 text-xs">Rate</p>
                              <p class="font-medium text-gray-900">
                                  <span v-if="laborer.rate_type === 'per_job'">Per Job</span>
                                  <span v-else>₱{{ laborer.rate ? Number(laborer.rate).toFixed(2) : '0.00' }} / {{ laborer.rate_type === 'daily' ? 'day' : 'hr' }}</span>
                              </p>
                          </div>
                          <div>
                              <p class="text-gray-500 text-xs">Status</p>
                              <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium" :class="laborer.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                  {{ laborer.status }}
                              </span>
                          </div>
                      </div>
                  </div>
              </div>
              <div v-else class="text-center py-12 bg-white rounded-xl border border-gray-100 border-dashed">
                  <p class="text-gray-500">No members assigned to this group yet.</p>
              </div>
          </div>

          <!-- Tasks Tab -->
           <div v-if="activeTab === 'tasks'" class="space-y-6">
               <div v-if="group.tasks && group.tasks.length > 0">
                   <!-- Task Type Summary -->
                   <div v-if="group.stats && group.stats.task_type_breakdown" class="flex gap-2 mb-4 flex-wrap">
                       <span v-for="(count, type) in group.stats.task_type_breakdown" :key="type" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                           {{ type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}: {{ count }}
                       </span>
                   </div>

                   <div class="space-y-4">
                       <div v-for="task in group.tasks" :key="task.id" class="bg-white rounded-lg shadow-sm border border-gray-100 p-5 hover:shadow-md transition-shadow flex items-start justify-between">
                           <div>
                               <div class="flex items-center gap-3 mb-1">
                                   <h3 class="font-bold text-gray-900">{{ task.task_type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase()) }}</h3>
                                   <span class="px-2 py-0.5 rounded-full text-xs font-medium" 
                                        :class="{
                                            'bg-yellow-100 text-yellow-800': task.status === 'pending',
                                            'bg-blue-100 text-blue-800': task.status === 'in_progress',
                                            'bg-green-100 text-green-800': task.status === 'completed',
                                            'bg-gray-100 text-gray-800': task.status === 'cancelled'
                                        }">
                                       {{ task.status.replace('_', ' ') }}
                                   </span>
                               </div>
                               <p class="text-gray-600 text-sm mb-2">{{ task.description }}</p>
                               <div class="flex items-center gap-4 text-xs text-gray-500">
                                   <span class="flex items-center gap-1">
                                       <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                       Due {{ formatDate(task.due_date) }}
                                   </span>
                                   <span v-if="task.planting && task.planting.field" class="flex items-center gap-1">
                                       <svg class="h-3 w-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                       {{ task.planting.field.name }}
                                   </span>
                               </div>
                           </div>
                       </div>
                   </div>
               </div>
               <div v-else class="text-center py-12 bg-white rounded-xl border border-gray-100 border-dashed">
                  <div class="mb-4 text-gray-400">
                      <svg class="h-12 w-12 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                      </svg>
                  </div>
                  <p class="text-gray-500 mb-1">No tasks assigned to this group yet.</p>
                  <p class="text-sm text-gray-400">Head to Tasks to assign work to this group.</p>
              </div>
           </div>
          
      </div>
    
      <div v-else-if="!loading && !group" class="text-center py-12">
          <p class="text-gray-500">Group not found.</p>
          <button @click="router.push('/laborers/groups')" class="text-blue-600 hover:underline mt-2">Back to Groups</button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import axios from 'axios'


const route = useRoute()
const router = useRouter()
const groupId = route.params.id

const loading = ref(true)
const group = ref(null)
const activeTab = ref('members')

const fetchGroup = async () => {
  loading.value = true
  try {
    const { data } = await axios.get(`/api/laborers/groups/${groupId}`)
    group.value = data.group
  } catch (err) {
    console.error('Failed to load group:', err)
  } finally {
    loading.value = false
  }
}

const getInitials = (name) => {
    return name ? name.substring(0, 2).toUpperCase() : 'LG'
}

const formatDate = (date) => {
    if (!date) return 'N/A'
    return new Date(date).toLocaleDateString(undefined, {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

onMounted(() => {
  if (groupId) {
    fetchGroup()
  }
})
</script>
