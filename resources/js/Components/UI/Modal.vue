<template>
  <Teleport to="body">
    <transition name="fade">
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-center justify-center px-4 py-6 sm:px-6"
        role="dialog"
        aria-modal="true"
        @keydown.esc.prevent="closeOnEscape"
      >
        <div class="absolute inset-0 bg-gray-900/70 backdrop-blur-md" @click="handleBackdrop" />
        <div
          class="relative z-10 w-full max-w-2xl overflow-hidden rounded-2xl bg-white shadow-2xl ring-1 ring-gray-100 transform transition-all"
          :class="contentClass"
        >
          <header v-if="withHeader" class="flex items-start justify-between border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white px-6 py-5">
            <div>
              <h3 class="text-lg font-bold text-gray-900">
                <slot name="title">{{ title }}</slot>
              </h3>
              <p v-if="subtitle" class="mt-1 text-sm text-gray-600 font-medium">{{ subtitle }}</p>
            </div>
            <button
              type="button"
              class="inline-flex items-center justify-center rounded-lg p-2 text-gray-400 transition-all hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
              @click="emitClose"
            >
              <span class="sr-only">Close modal</span>
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path
                  fill-rule="evenodd"
                  d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>
          </header>

          <section class="px-0 py-0 text-left text-gray-700">
            <slot />
          </section>

          <footer v-if="$slots.footer" class="flex items-center justify-end gap-3 bg-gradient-to-r from-gray-50 to-white border-t border-gray-200 px-6 py-4">
            <slot name="footer" />
          </footer>
        </div>
      </div>
    </transition>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: '',
  },
  subtitle: {
    type: String,
    default: '',
  },
  dismissible: {
    type: Boolean,
    default: true,
  },
  withHeader: {
    type: Boolean,
    default: true,
  },
  size: {
    type: String,
    default: 'md',
    validator: value => ['sm', 'md', 'lg', 'xl'].includes(value),
  },
})

const emit = defineEmits(['update:modelValue', 'close', 'backdrop'])

const sizes = {
  sm: 'max-w-md',
  md: 'max-w-2xl',
  lg: 'max-w-4xl',
  xl: 'max-w-5xl',
}

const contentClass = computed(() => sizes[props.size] || sizes.md)

const emitClose = () => {
  if (!props.dismissible) return
  emit('update:modelValue', false)
  emit('close')
}

const handleBackdrop = () => {
  emit('backdrop')
  emitClose()
}

const closeOnEscape = event => {
  if (event.key === 'Escape') {
    emitClose()
  }
}
</script>

<style scoped>
.fade-enter-active {
  transition: opacity 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.fade-leave-active {
  transition: opacity 0.2s cubic-bezier(0.4, 0, 1, 1);
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>





