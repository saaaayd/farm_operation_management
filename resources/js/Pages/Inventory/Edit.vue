<template>
  <div class="inventory-edit-page min-h-screen bg-gradient-to-br from-green-50 via-emerald-50 to-teal-50 font-sans py-10 px-4 sm:px-6 lg:px-8">
    <div class="max-w-full mx-auto space-y-8">
      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-emerald-600"></div>
      </div>

      <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-xl p-6 text-center">
        <p class="text-red-700 font-medium">{{ error }}</p>
        <button 
          @click="router.push('/inventory')" 
          class="mt-4 text-emerald-600 hover:text-emerald-700 font-medium"
        >
          Return to Inventory
        </button>
      </div>

      <div v-else>
        <!-- Header -->
        <div>
          <button
            type="button"
            @click="router.back()"
            class="inline-flex items-center text-sm font-medium text-emerald-700 hover:text-emerald-900 transition-colors"
          >
            <svg class="h-4 w-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Back to Item
          </button>
          <div class="flex items-center gap-3 mt-4">
            <h1 class="text-3xl font-bold text-gray-900">Edit Product</h1>
            <span class="px-3 py-1 bg-emerald-100 text-emerald-800 text-sm font-semibold rounded-full">{{ item?.name }}</span>
          </div>
          <p class="mt-2 text-base text-gray-600 max-w-2xl">
            Update product details, stock levels, or supplier information.
          </p>
        </div>

        <!-- Form -->
        <InventoryForm 
          :item="item"
          :is-editing="true"
          :submitting="submitting" 
          :error-message="error" 
          :errors="fieldErrors"
          @submit="handleUpdate" 
          @cancel="cancel" 
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useInventoryStore } from '@/stores/inventory'
import InventoryForm from './InventoryForm.vue'
import api from '@/services/api' // Use direct API for fallback if store empty

const router = useRouter()
const route = useRoute()
const store = useInventoryStore()

const loading = ref(true)
const submitting = ref(false)
const error = ref('')
const fieldErrors = ref({})
const item = ref(null)

onMounted(async () => {
  const id = route.params.id
  try {
    // Try to find in store first
    if (store.items.length === 0) {
      await store.fetchItems()
    }
    const found = store.items.find(i => i.id == id)
    
    if (found) {
      item.value = found
    } else {
      // Fallback fetch if not in store list (e.g. direct link)
      const res = await api.get(`/inventory/${id}`)
      item.value = res.data.data || res.data
    }
  } catch (e) {
    console.error('Failed to load item:', e)
    error.value = 'Failed to load item data.'
  } finally {
    loading.value = false
  }
})

const handleUpdate = async (payload) => {
  submitting.value = true
  error.value = ''
  fieldErrors.value = {}

  try {
    await store.updateItem(item.value.id, payload)
    // Refresh items
    await store.fetchItems()
    router.push(`/inventory/${item.value.id}`)
  } catch (e) {
    console.error('Failed to update item:', e)
    if (e.response?.data?.errors) {
      fieldErrors.value = e.response.data.errors
      error.value = e.response.data.message || 'Please check the form for errors.'
    } else {
      error.value = e.response?.data?.message || e.message || 'Failed to update item.'
    }
  } finally {
    submitting.value = false
  }
}

const cancel = () => {
  router.back()
}
</script>
