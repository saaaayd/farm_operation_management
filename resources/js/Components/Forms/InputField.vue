<template>
  <div class="w-full">
    <label
      v-if="label"
      :for="id"
      class="block text-sm font-semibold text-gray-700 mb-2"
      :class="{ 'text-red-600': error }"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-1">*</span>
    </label>
    
    <div class="relative">
      <input
        :id="id"
        :type="type"
        :value="modelValue"
        :placeholder="placeholder"
        :required="required"
        :disabled="disabled"
        :class="[
          'appearance-none relative block w-full px-4 py-3 border rounded-xl',
          'focus:outline-none focus:ring-2 focus:z-10 sm:text-sm',
          'transition-all duration-200 bg-gray-50 focus:bg-white',
          error
            ? 'border-red-300 focus:ring-red-500 focus:border-red-500 text-red-900 placeholder-red-300'
            : 'border-gray-300 focus:ring-green-500 focus:border-green-500 text-gray-900 placeholder-gray-400',
          disabled && 'bg-gray-100 cursor-not-allowed opacity-60',
          icon && 'pl-11',
        ]"
        @input="$emit('update:modelValue', $event.target.value)"
        @blur="$emit('blur', $event)"
        @focus="$emit('focus', $event)"
      />
      
      <div
        v-if="icon"
        class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
      >
        <component :is="icon" class="h-5 w-5 text-gray-400" />
      </div>
      
      <div
        v-if="error"
        class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none"
      >
        <svg class="h-5 w-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
          <path
            fill-rule="evenodd"
            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
            clip-rule="evenodd"
          />
        </svg>
      </div>
    </div>
    
    <p v-if="error" class="mt-1.5 text-sm text-red-600 font-medium flex items-center">
      <svg class="h-4 w-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
        <path
          fill-rule="evenodd"
          d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
          clip-rule="evenodd"
        />
      </svg>
      {{ error }}
    </p>
    
    <p v-else-if="hint" class="mt-1.5 text-sm text-gray-500">
      {{ hint }}
    </p>
  </div>
</template>

<script setup>
defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  id: {
    type: String,
    default: () => `input-${Math.random().toString(36).substr(2, 9)}`,
  },
  type: {
    type: String,
    default: 'text',
  },
  label: {
    type: String,
    default: '',
  },
  placeholder: {
    type: String,
    default: '',
  },
  required: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  },
  hint: {
    type: String,
    default: '',
  },
  icon: {
    type: Object,
    default: null,
  },
})

defineEmits(['update:modelValue', 'blur', 'focus'])
</script>

