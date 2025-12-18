<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Standard Header -->
      <!-- Standard Header with Back Button Top -->
      <div class="mb-8">
        <div class="mb-4">
          <button
            @click="router.push('/laborers')"
            class="flex items-center text-sm text-gray-500 hover:text-gray-700 transition-colors"
          >
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Laborers
          </button>
        </div>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
          <div>
            <h1 class="text-3xl font-bold text-gray-800">Laborer Groups</h1>
            <p class="text-gray-500 mt-1">Organize your laborers into groups/teams for easier assignment.</p>
          </div>
          <button
            @click="openCreateModal"
            class="flex items-center gap-2 bg-blue-600 text-white px-5 py-2 rounded-lg hover:bg-blue-700 transition-colors shadow-sm font-medium"
          >
            <span class="text-xl leading-none">+</span> New Group
          </button>
        </div>
      </div>



      <div v-if="loading && groups.length === 0" class="flex justify-center py-12">
           <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-600"></div>
      </div>

      <div v-else-if="groups.length === 0" class="bg-white rounded-2xl shadow-xl p-12 text-center border border-gray-100">
          <div class="inline-flex items-center justify-center h-20 w-20 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-2xl mb-6">
            <svg class="h-10 w-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
            </svg>
          </div>
          <h2 class="text-2xl font-bold text-gray-900 mb-2">No groups yet</h2>
          <p class="text-sm text-gray-600 mb-8 max-w-md mx-auto">
            Create groups to organize your workforce (e.g., "Planting Team", "Harvest Squad").
          </p>
          <button
            @click="openCreateModal"
            class="inline-flex items-center px-6 py-3 text-sm font-semibold rounded-lg bg-gradient-to-r from-blue-600 to-indigo-600 text-white hover:from-blue-700 hover:to-indigo-700 transition-all duration-200"
          >
            Create Your First Group
          </button>
      </div>

      <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <article
          v-for="group in groups"
          :key="group.id"
          class="bg-white rounded-xl shadow-md border border-gray-100 p-6 flex flex-col hover:shadow-lg transition-shadow"
        >
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <span
                class="h-8 w-8 rounded-full flex items-center justify-center text-white text-xs font-bold"
                :style="{ backgroundColor: group.color || '#10B981' }"
              >
                {{ getInitials(group.name) }}
              </span>
              <h3 class="text-lg font-bold text-gray-900">{{ group.name }}</h3>
            </div>
            <div class="flex items-center gap-1">
               <button
                  @click="router.push(`/laborers/groups/${group.id}`)"
                  class="p-1.5 text-gray-400 hover:text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                  title="View Details"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                  </svg>
               </button>
               <button
                  @click="editGroup(group)"
                  class="p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                  title="Edit"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
               </button>
               <button
                  @click="deleteGroup(group)"
                  class="p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                  title="Delete"
                >
                  <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
               </button>
            </div>
          </div>

          <p class="text-sm text-gray-600 mb-4 flex-grow">{{ group.description || 'No description provided.' }}</p>
          
          <div class="pt-4 border-t border-gray-100 flex items-center justify-between">
             <span class="text-xs font-medium text-gray-500 uppercase">Members</span>
             <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
               {{ group.laborers_count || 0 }} laborers
             </span>
          </div>
        </article>
      </div>
    </div>

    <!-- Modal -->
    <div v-if="showModal" class="relative z-50" aria-labelledby="modal-title" role="dialog" aria-modal="true">
      <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal"></div>

      <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
            <form @submit.prevent="submitForm">
              <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                  <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                      {{ isEditing ? 'Edit Group' : 'New Group' }}
                    </h3>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Group Name</label>
                            <input v-model="form.name" type="text" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="e.g., Harvest Team">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Description</label>
                            <textarea v-model="form.description" rows="3" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm p-2 border" placeholder="Briefly describe this group..."></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Color Tag</label>
                            <div class="flex items-center gap-2 mt-1">
                                <input v-model="form.color" type="color" class="h-8 w-14 rounded cursor-pointer border border-gray-300 p-0.5">
                                <span class="text-sm text-gray-500">{{ form.color }}</span>
                            </div>
                        </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                <button type="submit" :disabled="formLoading" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:ml-3 sm:w-auto sm:text-sm">
                  {{ formLoading ? 'Saving...' : (isEditing ? 'Update Group' : 'Create Group') }}
                </button>
                <button type="button" @click="closeModal" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                  Cancel
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>

</template>

<script setup>
import { ref, onMounted, reactive } from 'vue'
import { useRouter } from 'vue-router'
import axios from 'axios'

const router = useRouter()

const loading = ref(true)
const formLoading = ref(false)
const groups = ref([])
const showModal = ref(false)
const isEditing = ref(false)
const editingId = ref(null)

const form = reactive({
  name: '',
  description: '',
  color: '#10B981',
})

const fetchGroups = async () => {
  loading.value = true
  try {
    const { data } = await axios.get('/api/laborers/groups')
    groups.value = data.groups || []
  } catch (err) {
    console.error('Failed to load groups:', err)
  } finally {
    loading.value = false
  }
}

const openCreateModal = () => {
    isEditing.value = false
    editingId.value = null
    form.name = ''
    form.description = ''
    form.color = '#10B981'
    showModal.value = true
}

const editGroup = (group) => {
    isEditing.value = true
    editingId.value = group.id
    form.name = group.name
    form.description = group.description
    form.color = group.color || '#10B981'
    showModal.value = true
}

const closeModal = () => {
    showModal.value = false
}

const submitForm = async () => {
    formLoading.value = true
    try {
        if (isEditing.value) {
            await axios.put(`/api/laborers/groups/${editingId.value}`, form)
        } else {
            await axios.post('/api/laborers/groups', form)
        }
        await fetchGroups()
        closeModal()
    } catch (err) {
        console.error('Failed to save group:', err)
        alert('Failed to save group.')
    } finally {
        formLoading.value = false
    }
}

const deleteGroup = async (group) => {
    if (confirm(`Are you sure you want to delete "${group.name}"?`)) {
        try {
            await axios.delete(`/api/laborers/groups/${group.id}`)
            fetchGroups()
        } catch (err) {
            console.error('Failed to delete group:', err)
            alert('Failed to delete group.')
        }
    }
}

const getInitials = (name) => {
    return name ? name.substring(0, 2).toUpperCase() : 'LG'
}

onMounted(() => {
  fetchGroups()
})
</script>
