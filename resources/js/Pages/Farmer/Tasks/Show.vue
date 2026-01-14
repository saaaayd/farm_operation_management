<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto space-y-8">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <button
            type="button"
            @click="router.push('/tasks')"
            class="inline-flex items-center text-sm font-medium text-emerald-700 hover:text-emerald-900 transition-colors"
          >
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Tasks
          </button>
          <h1 class="mt-4 text-3xl font-bold text-gray-900">
            {{ task ? taskTitle : 'Loading task...' }}
          </h1>
          <p class="mt-2 text-base text-gray-600 max-w-2xl">
            Task details
          </p>
        </div>
      </div>


        <div
          v-if="loading"
          class="bg-white border border-gray-100 rounded-2xl shadow px-6 py-8 text-center text-gray-500"
        >
          Loading task details...
        </div>

        <div
          v-else-if="error"
          class="bg-white border border-red-100 rounded-2xl shadow px-6 py-8 text-center"
        >
          <p class="text-red-600 font-medium">{{ error }}</p>
          <button
            class="mt-4 text-sm text-green-600 hover:text-green-700 font-medium"
            @click="fetchTask"
          >
            Retry
          </button>
        </div>

        <div
          v-else-if="task"
          class="space-y-6"
        >
          <section class="bg-white border border-gray-100 rounded-2xl shadow divide-y divide-gray-100">
            <div class="px-6 py-6 flex flex-col gap-4">
              <div class="flex flex-wrap items-center gap-3">
                <span :class="['inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold', statusBadgeClass]">
                  {{ statusLabel }}
                </span>
                <span class="text-sm text-gray-500">
                  Due {{ formattedDueDate }}
                </span>
              </div>

              <p class="text-gray-900 leading-relaxed">
                {{ task.description || 'No description provided.' }}
              </p>
            </div>

            <div class="px-6 py-5 grid grid-cols-1 md:grid-cols-2 gap-6">
              <div>
                <p class="text-sm text-gray-500">Linked planting</p>
                <p class="text-base font-medium text-gray-900 mt-1">
                  {{ plantingSummary }}
                </p>
                <p v-if="task.planting?.field" class="text-sm text-gray-500">
                  Field: {{ task.planting.field.name }}
                </p>
              </div>

              <div>
                <p class="text-sm text-gray-500">Assigned laborer</p>
                <p class="text-base font-medium text-gray-900 mt-1">
                  {{ task.laborer?.name || 'Unassigned' }}
                </p>
                <p v-if="task.laborer?.contact" class="text-sm text-gray-500">
                  {{ task.laborer.contact }}
                </p>
              </div>
            </div>
          </section>

          <section class="bg-white border border-gray-100 rounded-2xl shadow px-6 py-5">
            <div class="flex flex-wrap items-center gap-4">
              <div class="flex items-center gap-2 text-sm text-gray-600">
                <span class="inline-flex h-2 w-2 rounded-full" :class="statusDotClass"></span>
                <span>{{ statusLabel }}</span>
              </div>
              <div class="text-sm text-gray-500">
                Last updated {{ formattedUpdatedAt }}
              </div>
            </div>

            <div class="mt-5 flex flex-wrap gap-3">
              <button
                v-if="canStart"
                :disabled="actionLoading"
                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md border border-gray-200 text-gray-700 hover:border-gray-300 disabled:opacity-50"
                @click="updateStatus('in_progress')"
              >
                Start task
              </button>
              <button
                v-if="canComplete"
                :disabled="actionLoading"
                class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-md bg-green-600 text-white hover:bg-green-700 disabled:opacity-50"
                @click="updateStatus('completed')"
              >
                Mark complete
              </button>
              <button
                v-if="canReopen"
                :disabled="actionLoading"
                class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md border border-gray-200 text-gray-700 hover:border-gray-300 disabled:opacity-50"
                @click="updateStatus('pending')"
              >
                Reopen task
              </button>
            </div>
          </section>
        </div>
      </div>

  </div>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { tasksAPI } from '@/services/api'
import { useFarmStore } from '@/stores/farm'
import { getTaskTypeLabel } from '@/utils/taskTypes'

const route = useRoute()
const router = useRouter()
const farmStore = useFarmStore()

const task = ref(null)
const loading = ref(true)
const error = ref(null)
const actionLoading = ref(false)

const taskId = computed(() => Number(route.params.id))

const normalizeLabel = (value = '') =>
  value
    .toString()
    .split('_')
    .map((part) => part.charAt(0).toUpperCase() + part.slice(1))
    .join(' ')

const statusLabel = computed(() => {
  if (!task.value?.status) return 'Unknown'
  return normalizeLabel(task.value.status)
})

const statusBadgeClass = computed(() => {
  switch (task.value?.status) {
    case 'completed':
      return 'bg-green-100 text-green-800'
    case 'in_progress':
      return 'bg-blue-100 text-blue-800'
    case 'pending':
      return 'bg-yellow-100 text-yellow-800'
    case 'cancelled':
      return 'bg-gray-100 text-gray-700'
    default:
      return 'bg-gray-100 text-gray-700'
  }
})

const statusDotClass = computed(() => {
  switch (task.value?.status) {
    case 'completed':
      return 'bg-green-500'
    case 'in_progress':
      return 'bg-blue-500'
    case 'pending':
      return 'bg-yellow-400'
    case 'cancelled':
      return 'bg-gray-400'
    default:
      return 'bg-gray-300'
  }
})

const formattedDueDate = computed(() => {
  if (!task.value?.due_date) return 'Not set'
  return new Date(task.value.due_date).toLocaleDateString()
})

const formattedUpdatedAt = computed(() => {
  if (!task.value?.updated_at) return 'just now'
  return new Date(task.value.updated_at).toLocaleString()
})

const plantingSummary = computed(() => {
  if (!task.value?.planting) return 'No planting linked'
  const planting = task.value.planting
  return planting.crop_type || `Planting #${planting.id}`
})

const taskTitle = computed(() => {
  if (!task.value) return ''
  return getTaskTypeLabel(task.value.task_type) || 'Task'
})

const canStart = computed(() => task.value?.status === 'pending')
const canComplete = computed(() => ['pending', 'in_progress'].includes(task.value?.status))
const canReopen = computed(() => task.value?.status === 'completed')

const fetchTask = async () => {
  loading.value = true
  error.value = null

  if (!taskId.value) {
    error.value = 'Missing task id'
    loading.value = false
    return
  }

  try {
    const response = await tasksAPI.getById(taskId.value)
    task.value = response.data.task

    // sync task into store cache for list/calendar
    if (task.value) {
      if (!Array.isArray(farmStore.tasks)) {
        farmStore.tasks = []
      }
      const idx = farmStore.tasks.findIndex((t) => Number(t.id) === Number(task.value.id))
      if (idx === -1) {
        farmStore.tasks.unshift(task.value)
      } else {
        farmStore.tasks[idx] = task.value
      }
    }
  } catch (err) {
    if (err.response?.status === 404) {
      error.value = 'Task not found. It may have been deleted.'
      setTimeout(() => router.push('/tasks'), 2000)
    } else {
      error.value = err.userMessage || err.response?.data?.message || 'Failed to load task.'
    }
  } finally {
    loading.value = false
  }
}

const updateStatus = async (nextStatus) => {
  if (!task.value) return
  actionLoading.value = true
  error.value = null

  try {
    const response = await farmStore.updateTask(task.value.id, { status: nextStatus })
    task.value = response?.task || { ...task.value, status: nextStatus }
  } catch (err) {
    error.value = err.userMessage || err.response?.data?.message || 'Failed to update task.'
  } finally {
    actionLoading.value = false
  }
}

onMounted(fetchTask)

watch(taskId, (newId, oldId) => {
  if (newId && newId !== oldId) {
    fetchTask()
  }
})
</script>

