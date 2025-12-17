<template>
  <div class="inventory-create-page min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 font-sans py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-full mx-auto space-y-8">
      <!-- Header -->
      <div>
        <button
          type="button"
          @click="router.push('/inventory')"
          class="inline-flex items-center text-sm font-medium text-emerald-700 hover:text-emerald-900 transition-colors"
        >
          <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Inventory
        </button>
        <h1 class="mt-4 text-3xl font-bold text-gray-900">Add New Product</h1>
        <p class="mt-2 text-base text-gray-600 max-w-2xl">
          Add a new item to your farm inventory to track stock levels, costs, and usage.
        </p>
      </div>

      <!-- Form -->
      <InventoryForm 
        :submitting="submitting" 
        :error-message="error" 
        :errors="fieldErrors"
        @submit="handleCreate" 
        @cancel="cancel" 
      />
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { useInventoryStore } from '@/stores/inventory'
import InventoryForm from './InventoryForm.vue'

const router = useRouter()
const store = useInventoryStore()

const submitting = ref(false)
const error = ref('')
const fieldErrors = ref({})

const handleCreate = async (payload) => {
  submitting.value = true
  error.value = ''
  fieldErrors.value = {}

  try {
    await store.createItem(payload)
    // Refresh items to include the new one
    await store.fetchItems()
    router.push('/inventory')
  } catch (e) {
    console.error('Failed to create item:', e)
    if (e.response?.data?.errors) {
      fieldErrors.value = e.response.data.errors
      error.value = e.response.data.message || 'Please check the form for errors.'
    } else {
      error.value = e.response?.data?.message || e.message || 'Failed to create item.'
    }
  } finally {
    submitting.value = false
  }
}

const cancel = () => {
  router.back()
}
</script>
