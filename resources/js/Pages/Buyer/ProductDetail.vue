<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <button
          @click="goBack"
          class="flex items-center text-gray-600 hover:text-gray-900 mb-4"
        >
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Products
        </button>
        <h1 class="text-2xl font-bold text-gray-900">{{ product.name || 'Loading...' }}</h1>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8" v-if="!loading && product.id">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Product Image -->
          <div class="bg-white rounded-lg shadow p-6">
            <!-- Main Image -->
            <div class="aspect-w-16 aspect-h-9 bg-gradient-to-br from-green-100 to-emerald-100 rounded-lg overflow-hidden h-96">
              <img
                v-if="product.images && product.images.length > 0"
                :src="product.images[selectedImageIndex]"
                :alt="product.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <span class="text-9xl">🌾</span>
              </div>
            </div>
            
            <!-- Thumbnail Gallery -->
            <div v-if="product.images && product.images.length > 1" class="flex gap-2 mt-4 overflow-x-auto py-2">
              <button
                v-for="(image, index) in product.images"
                :key="index"
                @click="selectedImageIndex = index"
                :class="[
                  'flex-shrink-0 w-20 h-20 rounded-lg overflow-hidden border-2 transition-all',
                  selectedImageIndex === index ? 'border-green-500 ring-2 ring-green-200' : 'border-gray-200 hover:border-gray-300'
                ]"
              >
                <img :src="image" :alt="`Product image ${index + 1}`" class="w-full h-full object-cover" />
              </button>
            </div>
          </div>

          <!-- Product Description -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Description</h2>
            <p class="text-gray-700 leading-relaxed">{{ product.description }}</p>
          </div>

          <!-- Product Details -->
          <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-semibold mb-4">Product Details</h2>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <span class="text-gray-600">Rice Variety:</span>
                <span class="ml-2 font-medium">{{ product.rice_variety?.name || 'N/A' }}</span>
              </div>
              <div>
                <span class="text-gray-600">Quality Grade:</span>
                <span class="ml-2 font-medium capitalize">{{ product.quality_grade?.replace('_', ' ') || 'N/A' }}</span>
              </div>
              <div>
                <span class="text-gray-600">Processing Method:</span>
                <span class="ml-2 font-medium capitalize">{{ product.processing_method || 'N/A' }}</span>
              </div>
              <div>
                <span class="text-gray-600">Organic:</span>
                <span class="ml-2 font-medium">{{ product.is_organic ? 'Yes' : 'No' }}</span>
              </div>
            </div>
          </div>

          <!-- Customer Reviews -->
          <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between mb-4">
              <h2 class="text-xl font-semibold">Customer Reviews</h2>
              <div v-if="reviewStats.total_reviews > 0" class="flex items-center gap-2">
                <span class="text-2xl font-bold text-yellow-500">{{ reviewStats.average_rating }}</span>
                <div class="flex">
                  <span v-for="n in 5" :key="n" :class="n <= Math.round(reviewStats.average_rating) ? 'text-yellow-400' : 'text-gray-300'">★</span>
                </div>
                <span class="text-gray-500">({{ reviewStats.total_reviews }} reviews)</span>
              </div>
            </div>

            <div v-if="loadingReviews" class="text-center py-8">
              <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600 mx-auto"></div>
            </div>

            <div v-else-if="reviews.length === 0" class="text-center py-8 text-gray-500">
              <p>No reviews yet. Be the first to review this product!</p>
            </div>

            <div v-else class="space-y-4">
              <div v-for="review in reviews" :key="review.id" class="border-b border-gray-100 pb-4 last:border-0">
                <div class="flex items-center justify-between mb-2">
                  <div class="flex items-center gap-2">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-600 font-semibold text-sm">
                      {{ review.buyer?.name?.charAt(0) || 'U' }}
                    </div>
                    <span class="font-medium">{{ review.buyer?.name || 'Anonymous' }}</span>
                    <span v-if="review.verified_purchase" class="text-xs bg-green-100 text-green-700 px-2 py-0.5 rounded">Verified</span>
                  </div>
                  <span class="text-sm text-gray-500">{{ formatDate(review.created_at) }}</span>
                </div>
                <div class="flex items-center gap-1 mb-2">
                  <span v-for="n in 5" :key="n" :class="n <= review.rating ? 'text-yellow-400' : 'text-gray-300'" class="text-sm">★</span>
                  <span v-if="review.title" class="ml-2 font-medium text-gray-900">{{ review.title }}</span>
                </div>
                <p class="text-gray-700 text-sm">{{ review.review_text }}</p>
                <div v-if="review.would_recommend" class="mt-2 text-xs text-green-600">✓ Would recommend</div>
              </div>
            </div>
          </div>


        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Order Card -->
          <div class="bg-white rounded-lg shadow p-6 sticky top-4">
            <!-- Production Status -->
            <div class="mb-4">
              <span
                v-if="product.production_status === 'in_production'"
                class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-yellow-100 text-yellow-800"
              >
                Pre-order Available
              </span>
              <span
                v-else-if="product.production_status === 'available'"
                class="inline-flex px-3 py-1 text-sm font-medium rounded-full bg-green-100 text-green-800"
              >
                Available Now
              </span>
            </div>

            <!-- Price -->
            <div class="mb-4">
              <div class="text-3xl font-bold text-green-600">
                {{ formatCurrency(product.price_per_unit) }}
              </div>
              <div class="text-gray-600">per {{ product.unit }}</div>
            </div>

            <!-- Available From (for pre-orders) -->
            <div v-if="product.production_status === 'in_production' && product.available_from" class="mb-4 p-3 bg-yellow-50 rounded-lg">
              <p class="text-sm font-medium text-yellow-900 mb-1">Available From</p>
              <p class="text-lg font-semibold text-yellow-800">{{ formatDate(product.available_from) }}</p>
            </div>

            <!-- Stock Info -->
            <div class="mb-4">
              <p class="text-sm text-gray-600">
                <span v-if="product.production_status === 'available'">
                  Stock: <span class="font-medium">{{ product.quantity_available }} {{ product.unit }}</span>
                </span>
                <span v-else class="font-medium">
                  Pre-order now and get notified when available
                </span>
              </p>
            </div>

            <!-- Quantity Selector -->
            <div class="mb-4" v-if="product.production_status === 'available'">
              <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
              <div class="flex items-center space-x-2">
                <button
                  @click="decreaseQuantity"
                  class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300"
                  :disabled="quantity <= 1"
                >
                  -
                </button>
                <input
                  v-model.number="quantity"
                  type="number"
                  min="1"
                  :max="product.quantity_available"
                  class="w-20 text-center border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                />
                <button
                  @click="increaseQuantity"
                  class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300"
                  :disabled="quantity >= product.quantity_available"
                >
                  +
                </button>
              </div>
            </div>

            <!-- Pre-order Quantity (for in production) -->
            <div class="mb-4" v-else>
              <label class="block text-sm font-medium text-gray-700 mb-2">Pre-order Quantity</label>
              <input
                v-model.number="quantity"
                type="number"
                min="1"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
              />
            </div>

            <!-- Total -->
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
              <div class="flex justify-between">
                <span class="text-gray-600">Total:</span>
                <span class="text-lg font-bold text-gray-900">
                  {{ formatCurrency(product.price_per_unit * quantity) }}
                </span>
              </div>
            </div>

            <p class="text-xs text-gray-500 mb-4">
              Payments are settled directly with the farmer. After placing your order, you’ll coordinate pickup or delivery details together.
            </p>

            <!-- Pre-order Button -->
            <button
              v-if="product.production_status === 'in_production'"
              @click="showPreOrderModal = true"
              class="w-full bg-yellow-600 text-white py-3 px-4 rounded-lg hover:bg-yellow-700 transition-colors font-medium"
            >
              Pre-order Now
            </button>

            <!-- Order Button -->
            <button
              v-else
              @click="showOrderModal = true"
              :disabled="quantity > product.quantity_available"
              class="w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition-colors font-medium disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Order Now
            </button>
          </div>

          <!-- Seller Information -->
          <div class="bg-white rounded-lg shadow p-6">
            <h3 class="text-lg font-semibold mb-4">Seller Information</h3>
            <div class="space-y-3">
              <div>
                <span class="text-gray-600">Farmer:</span>
                <span class="ml-2 font-medium">{{ product.farmer?.name || 'N/A' }}</span>
              </div>
              <div v-if="product.location">
                <span class="text-gray-600">Location:</span>
                <span class="ml-2 font-medium">{{ product.location.address || 'N/A' }}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- Loading State -->
    <div v-else-if="loading" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="animate-pulse">
        <div class="h-8 bg-gray-200 rounded w-1/4 mb-4"></div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <div class="lg:col-span-2 space-y-6">
            <div class="h-96 bg-gray-200 rounded"></div>
            <div class="h-32 bg-gray-200 rounded"></div>
          </div>
          <div class="h-64 bg-gray-200 rounded"></div>
        </div>
      </div>
    </div>

    <!-- Pre-order Modal -->
    <div v-if="showPreOrderModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-4">Pre-order Product</h3>
        <p class="text-gray-600 mb-4">
          This product is currently in production and will be available on {{ formatDate(product.available_from) }}.
          You will be notified via SMS when the product is ready and the day before pickup.
        </p>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Address</label>
          <textarea
            v-model="orderForm.delivery_address"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
            placeholder="Enter your delivery address"
          ></textarea>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number (Optional, for SMS notifications)</label>
          <input
            v-model="orderForm.phone"
            type="tel"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
            placeholder="Enter your phone number for notifications"
          />
          <p class="text-xs text-gray-500 mt-1">We'll notify you via SMS when your pre-order is ready</p>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Method</label>
          <select
            v-model="orderForm.delivery_method"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            <option value="pickup">Pickup</option>
            <option value="courier">Courier</option>
            <option value="postal">Postal</option>
            <option value="truck">Truck</option>
          </select>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
          <input
            v-model="orderForm.payment_method"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
            placeholder="e.g., Cash on Delivery, Bank Transfer"
          />
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
          <textarea
            v-model="orderForm.notes"
            rows="2"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
            placeholder="Any special instructions?"
          ></textarea>
        </div>

        <div class="flex space-x-3">
          <button
            @click="showPreOrderModal = false"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="submitPreOrder"
            :disabled="submitting"
            class="flex-1 px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700 disabled:opacity-50"
          >
            {{ submitting ? 'Submitting...' : 'Confirm Pre-order' }}
          </button>
        </div>
      </div>
    </div>

    <!-- Order Modal -->
    <div v-if="showOrderModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-xl font-bold mb-4">Place Order</h3>
        
        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Address</label>
          <textarea
            v-model="orderForm.delivery_address"
            rows="3"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
            placeholder="Enter your delivery address"
          ></textarea>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Delivery Method</label>
          <select
            v-model="orderForm.delivery_method"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            <option value="pickup">Pickup</option>
            <option value="courier">Courier</option>
            <option value="postal">Postal</option>
            <option value="truck">Truck</option>
          </select>
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Payment Method</label>
          <input
            v-model="orderForm.payment_method"
            type="text"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
            placeholder="e.g., Cash on Delivery, Bank Transfer"
          />
        </div>

        <div class="mb-4">
          <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
          <textarea
            v-model="orderForm.notes"
            rows="2"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
            placeholder="Any special instructions?"
          ></textarea>
        </div>

        <div class="flex space-x-3">
          <button
            @click="showOrderModal = false"
            class="flex-1 px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Cancel
          </button>
          <button
            @click="submitOrder"
            :disabled="submitting"
            class="flex-1 px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 disabled:opacity-50"
          >
            {{ submitting ? 'Submitting...' : 'Confirm Order' }}
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { riceMarketplaceAPI, authAPI } from '@/services/api'
import { formatCurrency } from '@/utils/format'
import axios from 'axios'

const route = useRoute()
const router = useRouter()

const loading = ref(true)
const product = ref({})
const quantity = ref(1)
const selectedImageIndex = ref(0)
const showPreOrderModal = ref(false)
const showOrderModal = ref(false)
const submitting = ref(false)

// Reviews state
const reviews = ref([])
const reviewStats = ref({ average_rating: 0, total_reviews: 0 })
const loadingReviews = ref(false)

const orderForm = ref({
  delivery_address: '',
  delivery_method: 'pickup',
  payment_method: 'Cash on Delivery',
  notes: '',
  phone: ''
})

const loadProduct = async () => {
  loading.value = true
  try {
    const response = await riceMarketplaceAPI.getProductById(route.params.id)
    product.value = response.data.product
    quantity.value = 1
    // Load reviews after product loads
    loadReviews()
  } catch (error) {
    console.error('Error loading product:', error)
    alert('Failed to load product')
  } finally {
    loading.value = false
  }
}

const loadReviews = async () => {
  loadingReviews.value = true
  try {
    const response = await axios.get(`/api/rice-marketplace/products/${route.params.id}/reviews`)
    reviews.value = response.data.reviews?.data || response.data.reviews || []
    reviewStats.value = {
      average_rating: response.data.average_rating || 0,
      total_reviews: response.data.total_reviews || 0
    }
  } catch (error) {
    console.error('Failed to load reviews:', error)
  } finally {
    loadingReviews.value = false
  }
}


const increaseQuantity = () => {
  if (product.value.production_status === 'available') {
    if (quantity.value < product.value.quantity_available) {
      quantity.value++
    }
  } else {
    quantity.value++
  }
}

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const parseDeliveryAddress = () => {
  const addressParts = orderForm.value.delivery_address.split(',').map(part => part.trim())
  return {
    street: addressParts[0] || orderForm.value.delivery_address,
    city: addressParts[1] || '',
    state: addressParts[2] || '',
    postal_code: addressParts[3] || '',
    country: 'Philippines'
  }
}

const placeOrder = async (isPreOrder = false) => {
  if (!orderForm.value.delivery_address) {
    alert('Please fill in delivery address')
    return
  }

  submitting.value = true
  try {
    await riceMarketplaceAPI.createOrder({
      rice_product_id: product.value.id,
      quantity: quantity.value,
      delivery_address: parseDeliveryAddress(),
      delivery_method: orderForm.value.delivery_method,
      payment_method: orderForm.value.payment_method,
      notes: orderForm.value.notes
    })

    if (orderForm.value.phone) {
      try {
        await authAPI.updateProfile({ phone: orderForm.value.phone })
      } catch (error) {
        console.warn('Failed to update phone number:', error)
      }
    }

    alert(
      isPreOrder
        ? 'Pre-order placed successfully! We will notify you once the product is ready.'
        : 'Order placed successfully! Coordinate final details directly with the farmer.'
    )
    showPreOrderModal.value = false
    showOrderModal.value = false
    router.push('/orders')
  } catch (error) {
    console.error('Error placing order:', error)
    alert(error.response?.data?.message || 'Failed to place order')
  } finally {
    submitting.value = false
  }
}

const submitPreOrder = () => placeOrder(true)
const submitOrder = () => placeOrder(false)

const goBack = () => {
  router.push('/buyer/products')
}

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

onMounted(() => {
  loadProduct()
})
</script>

