-<template>
-  <span class="inline-block">Status Badge</span>
-</template>
<template>
  <span
    :class="[
      'inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium transition-colors duration-150',
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
  success: 'bg-green-100 text-green-800',
  warning: 'bg-yellow-100 text-yellow-800',
  danger: 'bg-red-100 text-red-800',
  info: 'bg-blue-100 text-blue-800',
  neutral: 'bg-gray-100 text-gray-800',
  active: 'bg-green-100 text-green-800',
  pending: 'bg-yellow-100 text-yellow-800',
  completed: 'bg-blue-100 text-blue-800',
  cancelled: 'bg-red-100 text-red-800',
  failed: 'bg-red-100 text-red-800',
  draft: 'bg-gray-100 text-gray-800',
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




