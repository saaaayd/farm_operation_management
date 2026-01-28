<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Standard Header -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">My Orders</h1>
          <p class="text-gray-500 mt-1">Track your rice product orders</p>
        </div>
      </div>

      <!-- Status Tabs -->
      <div class="flex space-x-2 mb-6 overflow-x-auto pb-2">
        <button v-for="tab in tabs" :key="tab.value"
          @click="activeTab = tab.value"
          :class="[
            'px-4 py-2 rounded-full text-sm font-medium whitespace-nowrap transition-colors',
            activeTab === tab.value 
              ? 'bg-green-600 text-white' 
              : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
          ]"
        >
          {{ tab.label }}
          <span v-if="getOrderCount(tab.value) > 0" class="ml-1 px-2 py-0.5 rounded-full text-xs"
            :class="activeTab === tab.value ? 'bg-white/20' : 'bg-gray-300'"
          >{{ getOrderCount(tab.value) }}</span>
        </button>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
        <p class="text-gray-500 mt-4">Loading orders...</p>
      </div>

      <!-- Orders List -->
      <div v-else-if="filteredOrders.length > 0" class="space-y-4">
        <div v-for="order in filteredOrders" :key="order.id"
          class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 hover:shadow-md transition-shadow"
        >
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h3 class="font-semibold text-gray-900">{{ order.rice_product?.name || 'Rice Product' }}</h3>
                <span :class="getStatusClass(order.status)" class="px-2 py-1 rounded-full text-xs font-medium">
                  {{ formatStatus(order.status) }}
                </span>
              </div>
              <div class="text-sm text-gray-600 space-y-1">
                <p>Quantity: {{ order.quantity }} kg • ₱{{ Number(order.total_amount).toLocaleString() }}</p>
                <p>Seller: {{ order.rice_product?.farmer?.name || 'N/A' }}</p>
                <p>Ordered: {{ formatDate(order.order_date) }}</p>
              </div>
            </div>
            <div class="flex flex-col gap-2">
              <router-link :to="`/buyer/orders/${order.id}`"
                class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg text-sm font-medium hover:bg-gray-200 text-center"
              >View Details</router-link>

              <button v-if="order.status === 'picked_up' && !order.has_review"
                @click="openReviewModal(order)"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg text-sm font-medium hover:bg-blue-700"
              >Leave Review</button>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-else class="text-center py-12 bg-gray-50 rounded-xl">
        <div class="text-5xl mb-4">📦</div>
        <h3 class="text-lg font-medium text-gray-900">No orders found</h3>
        <p class="text-gray-500 mt-1">Start shopping to see your orders here</p>
        <router-link to="/buyer/products" class="inline-flex items-center px-4 py-2 mt-4 bg-green-600 text-white rounded-lg hover:bg-green-700">
          Browse Products
        </router-link>
      </div>
    </div>

    <!-- Review Modal -->
    <div v-if="showReviewModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
      <div class="bg-white rounded-xl max-w-lg w-full p-6 max-h-[90vh] overflow-y-auto">
        <h2 class="text-xl font-bold text-gray-900 mb-4">Leave a Review</h2>
        <p class="text-gray-600 mb-4">{{ reviewOrder?.rice_product?.name }}</p>
        
        <form @submit.prevent="submitReview">
          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-2">Overall Rating</label>
            <div class="flex gap-2">
              <button v-for="n in 5" :key="n" type="button"
                @click="reviewForm.rating = n"
                :class="['text-3xl', n <= reviewForm.rating ? 'text-yellow-400' : 'text-gray-300']"
              >★</button>
            </div>
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Review Title (Optional)</label>
            <input v-model="reviewForm.title" type="text" maxlength="100"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              placeholder="Summarize your experience"
            />
          </div>

          <div class="mb-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Your Review</label>
            <textarea v-model="reviewForm.review_text" rows="4" required minlength="10"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
              placeholder="Share your experience with this product..."
            ></textarea>
          </div>

          <div class="grid grid-cols-3 gap-4 mb-4">
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">Quality</label>
              <select v-model="reviewForm.quality_rating" class="w-full px-2 py-1 border rounded text-sm">
                <option :value="null">—</option>
                <option v-for="n in 5" :key="n" :value="n">{{ n }} ★</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">Delivery</label>
              <select v-model="reviewForm.delivery_rating" class="w-full px-2 py-1 border rounded text-sm">
                <option :value="null">—</option>
                <option v-for="n in 5" :key="n" :value="n">{{ n }} ★</option>
              </select>
            </div>
            <div>
              <label class="block text-xs font-medium text-gray-600 mb-1">Farmer</label>
              <select v-model="reviewForm.farmer_rating" class="w-full px-2 py-1 border rounded text-sm">
                <option :value="null">—</option>
                <option v-for="n in 5" :key="n" :value="n">{{ n }} ★</option>
              </select>
            </div>
          </div>

          <div class="mb-6">
            <label class="flex items-center gap-2">
              <input type="checkbox" v-model="reviewForm.would_recommend" class="rounded">
              <span class="text-sm text-gray-700">I would recommend this product</span>
            </label>
          </div>

          <div class="flex gap-3">
            <button type="button" @click="showReviewModal = false"
              class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
            >Cancel</button>
            <button type="submit" :disabled="submittingReview || reviewForm.rating === 0"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50"
            >{{ submittingReview ? 'Submitting...' : 'Submit Review' }}</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useMarketplaceStore } from '@/stores/marketplace'
import axios from 'axios'

const marketplaceStore = useMarketplaceStore()
const loading = ref(true)
const orders = ref([])
const activeTab = ref('all')

// Review modal state
const showReviewModal = ref(false)
const reviewOrder = ref(null)
const submittingReview = ref(false)
const reviewForm = ref({
  rating: 0,
  title: '',
  review_text: '',
  quality_rating: null,
  delivery_rating: null,
  farmer_rating: null,
  would_recommend: true,
})

const tabs = [
  { value: 'all', label: 'All Orders' },
  { value: 'pending', label: 'Pending' },
  { value: 'confirmed', label: 'Confirmed' },
  { value: 'ready_for_pickup', label: 'Ready for Pickup' },
  { value: 'picked_up', label: 'Picked Up' },
]

const filteredOrders = computed(() => {
  if (activeTab.value === 'all') return orders.value
  return orders.value.filter(o => o.status === activeTab.value)
})

const getOrderCount = (status) => {
  if (status === 'all') return orders.value.length
  return orders.value.filter(o => o.status === status).length
}

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    confirmed: 'bg-blue-100 text-blue-800',
    ready_for_pickup: 'bg-purple-100 text-purple-800',
    picked_up: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    disputed: 'bg-orange-100 text-orange-800',
  }
  return classes[status] || 'bg-gray-100 text-gray-800'
}

const formatStatus = (status) => {
  if (!status) return ''
  const labels = {
    pending: 'Pending',
    confirmed: 'Confirmed',
    ready_for_pickup: 'Ready for Pickup',
    picked_up: 'Picked Up',
    cancelled: 'Cancelled',
    disputed: 'Disputed'
  }
  return labels[status] || status.charAt(0).toUpperCase() + status.slice(1)
}

const formatDate = (date) => {
  if (!date) return 'N/A'
  return new Date(date).toLocaleDateString('en-PH', { month: 'short', day: 'numeric', year: 'numeric' })
}



const openReviewModal = (order) => {
  reviewOrder.value = order
  reviewForm.value = {
    rating: 0,
    title: '',
    review_text: '',
    quality_rating: null,
    delivery_rating: null,
    farmer_rating: null,
    would_recommend: true,
  }
  showReviewModal.value = true
}

const submitReview = async () => {
  if (reviewForm.value.rating === 0) {
    alert('Please select a rating')
    return
  }
  if (reviewForm.value.review_text.length < 10) {
    alert('Review must be at least 10 characters')
    return
  }

  submittingReview.value = true
  try {
    await axios.post('/api/rice-marketplace/reviews', {
      rice_order_id: reviewOrder.value.id,
      ...reviewForm.value,
    })
    alert('Review submitted successfully!')
    reviewOrder.value.has_review = true
    showReviewModal.value = false
  } catch (err) {
    alert(err.response?.data?.message || 'Failed to submit review')
  } finally {
    submittingReview.value = false
  }
}

onMounted(async () => {
  try {
    await marketplaceStore.fetchBuyerOrders()
    // Get orders from store, which properly handles pagination
    orders.value = marketplaceStore.orders || []
  } catch (err) {
    console.error('Failed to load orders', err)
  } finally {
    loading.value = false
  }
})
</script>

