<template>
  <div class="min-h-screen bg-gray-50">
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="py-6">
          <div class="flex items-center gap-3">
            <button @click="goBack" class="text-gray-500 hover:text-gray-700 transition-colors">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </button>
            <div>
              <h1 class="text-2xl font-semibold text-gray-900">Create New Planting</h1>
              <p class="text-sm text-gray-500">
                Start a new planting cycle for one of your fields.
              </p>
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="w-full py-8">
      <div class="w-full px-4 sm:px-6 lg:px-8">
        <PlantingForm />
      </div>
    </main>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import PlantingForm from './PlantingForm.vue' // Import the reusable form

const router = useRouter()

const goBack = () => {
  router.back()
}
</script>