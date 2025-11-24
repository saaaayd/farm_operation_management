<template>
  <div class="flex items-center justify-center gap-3 text-green-600" role="status" aria-live="polite">
    <div class="relative">
      <div class="h-6 w-6 border-3 border-gray-200 rounded-full"></div>
      <div class="h-6 w-6 border-3 border-green-600 border-t-transparent rounded-full animate-spin absolute top-0 left-0"></div>
    </div>
    <span class="text-sm font-semibold">
      <slot>Loading…</slot>
    </span>
  </div>
</template>

<style scoped>
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.animate-spin {
  animation: spin 0.8s linear infinite;
}

.border-3 {
  border-width: 3px;
}
</style>






