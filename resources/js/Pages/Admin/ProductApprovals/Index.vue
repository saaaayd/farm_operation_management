<template>
  <div class="product-approvals-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Product Approvals</h1>
          <p class="text-gray-600 mt-2">Review and approve pending product listings</p>
        </div>
        <div class="flex space-x-3">
          <button
            @click="loadPendingProducts"
            :disabled="loading"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 disabled:opacity-50"
          >
            {{ loading ? 'Loading...' : 'Refresh' }}
          </button>
          <button
            @click="showAllProducts = !showAllProducts"
            class="bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700"
          >
            {{ showAllProducts ? 'Show Pending Only' : 'Show All Products' }}
          </button>
        </div>
      </div>

      <!-- Stats -->
      <div v-if="stats" class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="text-2xl font-bold text-yellow-600">{{ stats.pending || 0 }}</div>
          <div class="text-sm text-gray-600">Pending</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="text-2xl font-bold text-green-600">{{ stats.approved || 0 }}</div>
          <div class="text-sm text-gray-600">Approved</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="text-2xl font-bold text-red-600">{{ stats.rejected || 0 }}</div>
          <div class="text-sm text-gray-600">Rejected</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-6">
          <div class="text-2xl font-bold text-blue-600">{{ stats.total || 0 }}</div>
          <div class="text-sm text-gray-600">Total</div>
        </div>
      </div>

      <!-- Products List -->
      <div class="bg-white rounded-lg shadow-md">
        <div class="p-6 border-b border-gray-200">
          <h2 class="text-xl font-semibold">
            {{ showAllProducts ? 'All Products' : 'Pending Approvals' }}
          </h2>
        </div>
        <div v-if="loading && !products.length" class="p-12 text-center text-gray-500">
          <div class="mx-auto mb-4 h-10 w-10 animate-spin rounded-full border-b-2 border-blue-600"></div>
          Loading products...
        </div>
        <div v-else-if="!loading && !products.length" class="p-12 text-center text-gray-500">
          <p class="text-lg">No products found</p>
        </div>
        <div v-else class="divide-y divide-gray-200">
          <div
            v-for="product in products"
            :key="product.id"
            class="p-6 hover:bg-gray-50"
          >
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center space-x-3 mb-2">
                  <h3 class="text-lg font-semibold text-gray-900">{{ product.name }}</h3>
                  <span
                    :class="{
                      'bg-yellow-100 text-yellow-800': product.approval_status === 'pending',
                      'bg-green-100 text-green-800': product.approval_status === 'approved',
                      'bg-red-100 text-red-800': product.approval_status === 'rejected'
                    }"
                    class="px-2 py-1 text-xs font-medium rounded-full capitalize"
                  >
                    {{ product.approval_status }}
                  </span>
                </div>
                <p class="text-gray-600">{{ product.description }}</p>
                <div class="mt-2 flex flex-wrap gap-4 text-sm text-gray-500">
                  <span>Farmer: {{ product.farmer?.name || 'Unknown' }}</span>
                  <span>Price: {{ formatCurrency(product.price_per_unit) }}/{{ product.unit }}</span>
                  <span>Quantity: {{ product.quantity_available }} {{ product.unit }}</span>
                  <span>Grade: {{ product.quality_grade }}</span>
                </div>
                <p v-if="product.rejection_reason" class="mt-2 text-sm text-red-600">
                  <strong>Rejection Reason:</strong> {{ product.rejection_reason }}
                </p>
                <p class="text-xs text-gray-400 mt-2">
                  Created: {{ formatDate(product.created_at) }}
                </p>
              </div>
              <div v-if="product.approval_status === 'pending'" class="flex space-x-2 ml-4">
                <button
                  @click="approveProduct(product.id)"
                  :disabled="processing === product.id"
                  class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 disabled:opacity-50 text-sm"
                >
                  {{ processing === product.id ? 'Processing...' : 'Approve' }}
                </button>
                <button
                  @click="showRejectModal(product)"
                  :disabled="processing === product.id"
                  class="bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 disabled:opacity-50 text-sm"
                >
                  Reject
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Reject Modal -->
      <div
        v-if="rejectModalProduct"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="rejectModalProduct = null"
      >
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
          <h3 class="text-xl font-semibold mb-4">Reject Product Listing</h3>
          <p class="text-gray-600 mb-4">
            Are you sure you want to reject <strong>{{ rejectModalProduct.name }}</strong>?
          </p>
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Rejection Reason (required)
            </label>
            <textarea
              v-model="rejectionReason"
              rows="4"
              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-red-500"
              placeholder="Enter reason for rejection..."
            ></textarea>
          </div>
          <div class="flex space-x-3">
            <button
              @click="confirmReject"
              :disabled="!rejectionReason.trim() || processing === rejectModalProduct.id"
              class="flex-1 bg-red-600 text-white px-4 py-2 rounded-md hover:bg-red-700 disabled:opacity-50"
            >
              {{ processing === rejectModalProduct.id ? 'Processing...' : 'Confirm Reject' }}
            </button>
            <button
              @click="rejectModalProduct = null; rejectionReason = ''"
              class="flex-1 bg-gray-300 text-gray-700 px-4 py-2 rounded-md hover:bg-gray-400"
            >
              Cancel
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue'
import { adminAPI } from '@/services/api'

const loading = ref(false)
const processing = ref(null)
const products = ref([])
const stats = ref(null)
const showAllProducts = ref(false)
const rejectModalProduct = ref(null)
const rejectionReason = ref('')

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-PH', { style: 'currency', currency: 'PHP' }).format(amount || 0)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const loadPendingProducts = async () => {
  loading.value = true
  try {
    const endpoint = showAllProducts.value ? adminAPI.getAllProducts() : adminAPI.getPendingProducts()
    const [productsResponse, statsResponse] = await Promise.all([
      endpoint,
      adminAPI.getProductApprovalStats()
    ])
    products.value = productsResponse.data.data || []
    stats.value = statsResponse.data
  } catch (error) {
    console.error('Error loading products:', error)
    alert('Failed to load products')
  } finally {
    loading.value = false
  }
}

const approveProduct = async (productId) => {
  if (!confirm('Are you sure you want to approve this product?')) return
  
  processing.value = productId
  try {
    await adminAPI.approveProduct(productId)
    await loadPendingProducts()
    alert('Product approved successfully')
  } catch (error) {
    console.error('Error approving product:', error)
    alert(error.response?.data?.message || 'Failed to approve product')
  } finally {
    processing.value = null
  }
}

const showRejectModal = (product) => {
  rejectModalProduct.value = product
  rejectionReason.value = ''
}

const confirmReject = async () => {
  if (!rejectionReason.value.trim()) {
    alert('Please provide a rejection reason')
    return
  }

  processing.value = rejectModalProduct.value.id
  try {
    await adminAPI.rejectProduct(rejectModalProduct.value.id, rejectionReason.value)
    await loadPendingProducts()
    rejectModalProduct.value = null
    rejectionReason.value = ''
    alert('Product rejected successfully')
  } catch (error) {
    console.error('Error rejecting product:', error)
    alert(error.response?.data?.message || 'Failed to reject product')
  } finally {
    processing.value = null
  }
}

watch(showAllProducts, () => {
  loadPendingProducts()
})

onMounted(() => {
  loadPendingProducts()
})
</script>

<style scoped>
.product-approvals-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>

