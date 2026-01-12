<template>
  <div class="min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-7xl mx-auto space-y-8">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
          <button
            type="button"
            @click="router.push('/plantings')"
            class="inline-flex items-center text-sm font-medium text-emerald-700 hover:text-emerald-900 transition-colors"
          >
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Plantings
          </button>
          <h1 class="mt-4 text-3xl font-bold text-gray-900">Create New Planting</h1>
          <p class="mt-2 text-base text-gray-600 max-w-2xl">
            Start a new planting cycle for one of your fields.
          </p>
        </div>
      </div>

      <PlantingForm />
    </div>
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