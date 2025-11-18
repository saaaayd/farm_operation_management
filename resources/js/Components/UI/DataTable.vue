-<template>
-  <div>Data Table</div>
-</template>
<template>
  <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th
            v-for="column in columns"
            :key="column.key"
            scope="col"
            class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-gray-600"
          >
            <div class="flex items-center gap-1">
              <span>{{ column.label }}</span>
              <button
                v-if="column.sortable"
                type="button"
                class="text-gray-400 transition hover:text-gray-600"
                @click="() => toggleSort(column.key)"
              >
                <span class="sr-only">Sort</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24">
                  <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M8 15l4 4 4-4m0-6l-4-4-4 4" />
                </svg>
              </button>
            </div>
          </th>
        </tr>
      </thead>
      <tbody class="divide-y divide-gray-200 text-sm text-gray-700">
        <tr v-if="!rows.length">
          <td :colspan="columns.length" class="px-4 py-6 text-center text-gray-500">
            <slot name="empty">No records found.</slot>
          </td>
        </tr>
        <tr
          v-for="row in sortedRows"
          :key="row[rowKey]"
          class="transition hover:bg-gray-50"
          @click="() => handleRowClick(row)"
        >
          <td v-for="column in columns" :key="column.key" class="px-4 py-3 align-top">
            <slot :name="`cell:${column.key}`" :row="row">
              {{ row[column.key] ?? '—' }}
            </slot>
          </td>
        </tr>
      </tbody>
    </table>

    <footer v-if="showFooter" class="flex items-center justify-between px-4 py-3 text-xs text-gray-500">
      <div>{{ footerSummary }}</div>
      <slot name="footer-actions" />
    </footer>
  </div>
</template>

<script setup>
import { computed, reactive, getCurrentInstance } from 'vue'

const props = defineProps({
  columns: {
    type: Array,
    default: () => [],
  },
  rows: {
    type: Array,
    default: () => [],
  },
  rowKey: {
    type: String,
    default: 'id',
  },
  footerSummary: {
    type: String,
    default: '',
  },
  sortable: {
    type: Boolean,
    default: false,
  },
})

const emit = defineEmits(['row:click', 'sort:change'])

const showFooter = computed(() => !!(props.footerSummary || getSlots().has('footer-actions')))

const state = reactive({
  sortKey: '',
  sortDirection: 'asc',
})

const sortedRows = computed(() => {
  if (!props.sortable || !state.sortKey) {
    return props.rows
  }

  return [...props.rows].sort((a, b) => {
    const aValue = a[state.sortKey]
    const bValue = b[state.sortKey]

    if (aValue === bValue) return 0

    const order = aValue > bValue ? 1 : -1
    return state.sortDirection === 'asc' ? order : -order
  })
})

const toggleSort = key => {
  if (!props.sortable) return

  if (state.sortKey === key) {
    state.sortDirection = state.sortDirection === 'asc' ? 'desc' : 'asc'
  } else {
    state.sortKey = key
    state.sortDirection = 'asc'
  }

  emit('sort:change', { key: state.sortKey, direction: state.sortDirection })
}

const handleRowClick = row => {
  emit('row:click', row)
}

const getSlots = () => {
  const slots = getCurrentInstance()?.slots || {}
  const has = name => !!slots[name]
  return { has }
}
</script>

