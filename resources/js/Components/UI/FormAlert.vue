<template>
  <div
    v-if="visible"
    :class="[
      'rounded-md border px-4 py-3 text-sm',
      type === 'error'
        ? 'border-red-200 bg-red-50 text-red-800'
        : 'border-green-200 bg-green-50 text-green-800',
    ]"
    role="alert"
  >
    <div class="flex items-start gap-2">
      <slot name="icon">
        <svg
          v-if="type === 'error'"
          class="mt-0.5 h-5 w-5 flex-shrink-0"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 6h.01"
          />
        </svg>
        <svg
          v-else
          class="mt-0.5 h-5 w-5 flex-shrink-0"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            d="M5 13l4 4L19 7"
          />
        </svg>
      </slot>
      <div class="flex-1">
        <p class="font-medium">
          <slot>{{ message }}</slot>
        </p>

        <ul v-if="hasFieldErrors" class="mt-2 space-y-1 text-xs">
          <li v-for="(fieldMessages, field) in fieldErrors" :key="field">
            <span class="font-semibold">
              {{ prettifyField(field) }}:
            </span>
            <span>{{ formatMessages(fieldMessages) }}</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  visible: {
    type: Boolean,
    default: false,
  },
  type: {
    type: String,
    default: 'error',
  },
  message: {
    type: String,
    default: '',
  },
  fieldErrors: {
    type: Object,
    default: () => ({}),
  },
});

const hasFieldErrors = computed(
  () =>
    props.fieldErrors &&
    typeof props.fieldErrors === 'object' &&
    Object.keys(props.fieldErrors).length > 0,
);

const formatMessages = (messages) => {
  if (Array.isArray(messages)) {
    return messages.join(', ');
  }
  if (typeof messages === 'string') {
    return messages;
  }
  return '';
};

const prettifyField = (field) => {
  if (!field) return '';
  return field
    .replace(/_/g, ' ')
    .replace(/\b\w/g, (char) => char.toUpperCase());
};
</script>


