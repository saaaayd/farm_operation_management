<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between py-6 gap-4">
          <div>
            <div class="flex items-center text-sm text-gray-500 mb-1">
              <router-link to="/dashboard" class="text-gray-500 hover:text-gray-700 mr-2">
                Dashboard
              </router-link>
              <span>/</span>
              <router-link to="/tasks" class="ml-2 text-gray-500 hover:text-gray-700">
                Tasks
              </router-link>
            </div>
            <h1 class="text-2xl font-semibold text-gray-900">Task Calendar</h1>
            <p class="text-sm text-gray-500">
              Visualize upcoming farm work and stay on top of deadlines.
            </p>
          </div>
          <div class="flex items-center gap-3">
            <router-link
              to="/tasks/create"
              class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md bg-green-600 text-white hover:bg-green-700"
            >
              Schedule Task
            </router-link>
            <router-link
              to="/tasks"
              class="inline-flex items-center px-4 py-2 text-sm font-medium rounded-md border border-gray-300 text-gray-700 bg-white hover:bg-gray-50"
            >
              Back to List
            </router-link>
          </div>
        </div>
      </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="error" class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="h-5 w-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm text-red-700">{{ error }}</p>
            <button
              @click="loadTasks"
              class="mt-2 text-sm font-medium text-red-700 hover:text-red-800"
            >
              Try again
            </button>
          </div>
        </div>
      </div>

      <div v-else-if="loading" class="flex flex-col items-center justify-center py-24 text-gray-600">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mb-4"></div>
        <p>Loading calendar...</p>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow p-4 sm:p-6 mb-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
              <div>
                <p class="text-sm text-gray-500 uppercase tracking-wide">Viewing</p>
                <h2 class="text-2xl font-semibold text-gray-900">{{ monthLabel }}</h2>
              </div>
              <div class="flex items-center gap-2">
                <button
                  @click="goToPrevMonth"
                  class="p-2 rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50"
                  aria-label="Previous month"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                  </svg>
                </button>
                <button
                  @click="goToToday"
                  class="px-4 py-2 rounded-md border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50"
                >
                  Today
                </button>
                <button
                  @click="goToNextMonth"
                  class="p-2 rounded-md border border-gray-200 text-gray-600 hover:bg-gray-50"
                  aria-label="Next month"
                >
                  <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                  </svg>
                </button>
              </div>
            </div>

            <div class="grid grid-cols-7 gap-2 mt-6 text-xs font-semibold text-gray-500 uppercase tracking-wide">
              <div v-for="dayName in weekdayLabels" :key="dayName" class="text-center">
                {{ dayName }}
              </div>
            </div>

            <div class="grid grid-cols-7 gap-2 mt-2">
              <button
                v-for="day in calendarDays"
                :key="day.isoDate"
                @click="selectDay(day)"
                :class="[
                  'flex flex-col items-start bg-gray-50 rounded-lg p-2 min-h-[110px] text-left transition',
                  day.isCurrentMonth ? 'bg-gray-50' : 'bg-white border border-dashed border-gray-200 opacity-70',
                  isSelectedDay(day.isoDate) ? 'ring-2 ring-green-500 ring-offset-2' : '',
                ]"
              >
                <div class="flex items-center justify-between w-full">
                  <span
                    :class="[
                      'text-sm font-semibold',
                      day.isCurrentMonth ? 'text-gray-900' : 'text-gray-400',
                      day.isToday ? 'bg-green-100 text-green-700 px-2 py-0.5 rounded-full' : ''
                    ]"
                  >
                    {{ day.date.getDate() }}
                  </span>
                  <span v-if="day.tasks.length" class="text-xs text-gray-400">
                    {{ day.tasks.length + day.extraTasks }} task{{ (day.tasks.length + day.extraTasks) === 1 ? '' : 's' }}
                  </span>
                </div>
                <div class="mt-2 w-full space-y-1">
                  <div
                    v-for="task in day.tasks"
                    :key="`preview-${day.isoDate}-${task.id}`"
                    class="text-xs truncate px-2 py-1 rounded-md bg-white border border-gray-100 text-gray-700"
                  >
                    <span :class="['inline-flex h-2 w-2 rounded-full mr-1 align-middle', getStatusDotClass(task.status)]"></span>
                    {{ getTaskTypeLabel(task.task_type) }}
                  </div>
                  <div
                    v-if="day.extraTasks > 0"
                    class="text-[11px] text-gray-500 font-medium mt-1"
                  >
                    +{{ day.extraTasks }} more
                  </div>
                </div>
              </button>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <div>
              <p class="text-sm text-gray-500">Selected Day</p>
              <h3 class="text-xl font-semibold text-gray-900">
                {{ formatLongDate(selectedDate) }}
              </h3>
            </div>
            <div class="text-right text-sm text-gray-500">
              {{ selectedTasks.length }} task{{ selectedTasks.length === 1 ? '' : 's' }}
            </div>
          </div>

          <div v-if="selectedTasks.length === 0" class="text-center py-10 text-gray-500">
            <svg class="h-12 w-12 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2" />
            </svg>
            <p class="text-sm">No tasks scheduled for this day.</p>
          </div>

          <div v-else class="space-y-3">
            <div
              v-for="task in selectedTasks"
              :key="task.id"
              class="border border-gray-100 rounded-lg p-4"
            >
              <div class="flex items-center justify-between">
                <div>
                  <p class="text-sm font-semibold text-gray-900">{{ getTaskTypeLabel(task.task_type) }}</p>
                  <p class="text-xs text-gray-500">
                    {{ task.planting?.crop_type || 'Unlinked Planting' }}
                    <span v-if="task.planting?.field?.name"> • {{ task.planting.field.name }}</span>
                  </p>
                </div>
                <span
                  :class="[
                    'px-2 py-0.5 rounded-full text-xs font-medium',
                    getStatusClass(task.status)
                  ]"
                >
                  {{ task.status.replace('_', ' ') }}
                </span>
              </div>
              <p class="text-sm text-gray-600 mt-2 line-clamp-2">{{ task.description }}</p>
              <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                <div class="flex items-center space-x-4">
                  <span class="flex items-center">
                    <svg class="h-3.5 w-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Due {{ formatLongDate(task.due_date) }}
                  </span>
                  <span v-if="task.assigned_to && task.laborer">
                    Assigned: {{ task.laborer.name }}
                  </span>
                </div>
                <button
                  @click="viewTask(task.id)"
                  class="text-green-600 hover:text-green-700 font-medium"
                >
                  View
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useFarmStore } from '@/stores/farm';
import { getTaskTypeLabel } from '@/utils/taskTypes';

const router = useRouter();
const farmStore = useFarmStore();

const loading = ref(true);
const error = ref('');
const currentMonth = ref(new Date());
const selectedDate = ref(new Date());

const tasks = computed(() => farmStore.tasks || []);

const formatDateKey = (value) => {
  if (!value) return '';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '';
  return date.toISOString().split('T')[0];
};

const weekdayLabels = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

const monthLabel = computed(() => {
  return currentMonth.value.toLocaleDateString(undefined, { month: 'long', year: 'numeric' });
});

const todayKey = formatDateKey(new Date());

const calendarDays = computed(() => {
  const firstDayOfMonth = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth(), 1);
  const startDay = new Date(firstDayOfMonth);
  startDay.setDate(startDay.getDate() - firstDayOfMonth.getDay());

  const days = [];

  for (let i = 0; i < 42; i += 1) {
    const date = new Date(startDay);
    date.setDate(startDay.getDate() + i);

    const isoDate = formatDateKey(date);
    const tasksForDay = tasks.value.filter((task) => formatDateKey(task.due_date) === isoDate);

    days.push({
      date,
      isoDate,
      isCurrentMonth: date.getMonth() === currentMonth.value.getMonth(),
      isToday: isoDate === todayKey,
      tasks: tasksForDay.slice(0, 3),
      extraTasks: Math.max(0, tasksForDay.length - 3),
    });
  }

  return days;
});

const selectedTasks = computed(() => {
  const key = formatDateKey(selectedDate.value);
  return tasks.value.filter((task) => formatDateKey(task.due_date) === key);
});

const isSelectedDay = (isoDate) => isoDate === formatDateKey(selectedDate.value);

const goToPrevMonth = () => {
  currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() - 1, 1);
};

const goToNextMonth = () => {
  currentMonth.value = new Date(currentMonth.value.getFullYear(), currentMonth.value.getMonth() + 1, 1);
};

const goToToday = () => {
  const today = new Date();
  currentMonth.value = new Date(today.getFullYear(), today.getMonth(), 1);
  selectedDate.value = today;
};

const selectDay = (day) => {
  selectedDate.value = day.date;
  currentMonth.value = new Date(day.date.getFullYear(), day.date.getMonth(), 1);
};

const formatLongDate = (value) => {
  if (!value) return '';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return '';
  return date.toLocaleDateString(undefined, { weekday: 'long', month: 'short', day: 'numeric' });
};

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    in_progress: 'bg-blue-100 text-blue-800',
    completed: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusDotClass = (status) => {
  const colors = {
    pending: 'bg-yellow-400',
    in_progress: 'bg-blue-500',
    completed: 'bg-green-500',
    cancelled: 'bg-red-500',
  };
  return colors[status] || 'bg-gray-300';
};

const viewTask = (taskId) => {
  router.push(`/tasks/${taskId}`);
};

const loadTasks = async () => {
  loading.value = true;
  error.value = '';

  try {
    await farmStore.fetchTasks();
    if (!farmStore.plantings?.length) {
      await farmStore.fetchPlantings();
    }
  } catch (err) {
    console.error('Failed to load task calendar data:', err);
    error.value = err.userMessage || err.response?.data?.message || 'Unable to load tasks for the calendar.';
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  if (!tasks.value.length) {
    await loadTasks();
  } else {
    loading.value = false;
  }
});
</script>

