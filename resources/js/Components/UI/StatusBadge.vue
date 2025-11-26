-<template>
-  <span class="inline-block">Status Badge</span>
-</template>
<template>
  <span
    :class="[
      'inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold transition-all duration-200',
      badgeClass,
    ]"
    role="status"
    aria-live="polite"
  >
    <slot>{{ displayLabel }}</slot>
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  status: {
    type: String,
    default: '',
  },
  label: {
    type: String,
    default: '',
  },
  variant: {
    type: String,
    default: 'neutral',
  },
  mappings: {
    type: Object,
    default: () => ({}),
  },
})

const BASE_MAP = {
  success: 'bg-gradient-to-r from-green-50 to-emerald-50 text-green-800 border border-green-200 shadow-sm',
  warning: 'bg-gradient-to-r from-yellow-50 to-amber-50 text-yellow-800 border border-yellow-200 shadow-sm',
  danger: 'bg-gradient-to-r from-red-50 to-rose-50 text-red-800 border border-red-200 shadow-sm',
  info: 'bg-gradient-to-r from-blue-50 to-cyan-50 text-blue-800 border border-blue-200 shadow-sm',
  neutral: 'bg-gradient-to-r from-gray-50 to-slate-50 text-gray-800 border border-gray-200 shadow-sm',
  active: 'bg-gradient-to-r from-green-50 to-emerald-50 text-green-800 border border-green-200 shadow-sm',
  pending: 'bg-gradient-to-r from-yellow-50 to-amber-50 text-yellow-800 border border-yellow-200 shadow-sm',
  completed: 'bg-gradient-to-r from-blue-50 to-cyan-50 text-blue-800 border border-blue-200 shadow-sm',
  cancelled: 'bg-gradient-to-r from-red-50 to-rose-50 text-red-800 border border-red-200 shadow-sm',
  failed: 'bg-gradient-to-r from-red-50 to-rose-50 text-red-800 border border-red-200 shadow-sm',
  draft: 'bg-gradient-to-r from-gray-50 to-slate-50 text-gray-800 border border-gray-200 shadow-sm',
}

const normalizedStatus = computed(() => props.status?.toLowerCase().replace(/\s+/g, '_') || '')

const badgeClass = computed(() => {
  const map = { ...BASE_MAP, ...props.mappings }
  return map[normalizedStatus.value] || map[props.variant] || map.neutral
})

const displayLabel = computed(() => {
  if (props.label) return props.label
  if (!props.status) return 'N/A'
  return props.status
    .toString()
    .replace(/_/g, ' ')
    .replace(/\b\w/g, char => char.toUpperCase())
})
</script>







