<template>
  <div class="min-h-screen bg-gray-50">
    <div class="container mx-auto px-4 py-8">
      <!-- Standard Header -->
      <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <div>
          <h1 class="text-3xl font-bold text-gray-800">Shopping Cart</h1>
          <p class="text-gray-500 mt-1">Review and manage your cart items</p>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loading" class="text-center py-12">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-green-600 mx-auto"></div>
      </div>

      <!-- Empty Cart -->
      <div v-else-if="cartItems.length === 0" class="text-center py-16 bg-white rounded-xl shadow">
        <div class="text-6xl mb-4">🛒</div>
        <h2 class="text-xl font-medium text-gray-900 mb-2">Your cart is empty</h2>
        <p class="text-gray-500 mb-6">Browse our marketplace to find quality rice products</p>
        <router-link to="/buyer/products"
          class="inline-flex items-center px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700"
        >Browse Products</router-link>
      </div>

      <!-- Cart Items -->
      <div v-else class="space-y-6">
        <div class="bg-white rounded-xl shadow divide-y">
          <div v-for="item in cartItems" :key="item.id" class="p-6 flex gap-6">
            <div class="w-20 h-20 bg-green-100 rounded-lg flex items-center justify-center text-4xl flex-shrink-0">
              🌾
            </div>
            <div class="flex-1">
              <h3 class="font-semibold text-gray-900 mb-1">{{ item.rice_product?.name }}</h3>
              <p class="text-sm text-gray-500 mb-2">
                Seller: {{ item.rice_product?.farmer?.name || 'N/A' }}
              </p>
              <p class="text-lg font-medium text-green-600">
                ₱{{ formatNumber(item.rice_product?.price_per_unit || 0) }} / {{ item.rice_product?.unit || 'kg' }}
              </p>
            </div>
            <div class="flex flex-col items-end gap-2">
              <div class="flex items-center gap-2">
                <button @click="updateQuantity(item, -1)" class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300">−</button>
                <input v-model.number="item.quantity" type="number" min="1" 
                  class="w-16 text-center border rounded-md" @change="saveQuantity(item)" />
                <button @click="updateQuantity(item, 1)" class="w-8 h-8 rounded-full bg-gray-200 hover:bg-gray-300">+</button>
              </div>
              <p class="font-semibold text-gray-900">₱{{ formatNumber(item.quantity * (item.rice_product?.price_per_unit || 0)) }}</p>
              <button @click="confirmRemoveItem(item)" class="text-red-600 text-sm hover:underline">Remove</button>
            </div>
          </div>
        </div>

        <!-- Summary -->
        <div class="bg-white rounded-xl shadow p-6">
          <div class="flex justify-between items-center mb-4">
            <span class="text-gray-600">Subtotal ({{ cartItems.length }} items)</span>
            <span class="text-xl font-bold text-gray-900">₱{{ formatNumber(total) }}</span>
          </div>
          <button @click="showCheckoutModal = true"
            class="w-full py-3 bg-green-600 text-white rounded-lg font-medium hover:bg-green-700"
          >Proceed to Checkout</button>
          <button @click="confirmClearCart"
            class="w-full mt-2 py-2 text-gray-600 hover:text-red-600"
          >Clear Cart</button>
        </div>
      </div>

      <!-- Checkout Modal -->
      <div v-if="showCheckoutModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl max-w-md w-full p-6">
          <h2 class="text-xl font-bold text-gray-900 mb-4">Checkout</h2>
          
          <form @submit.prevent="confirmCheckout">
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Address</label>
              <textarea v-model="checkoutForm.delivery_address" rows="3" required
                class="w-full px-3 py-2 border rounded-lg" placeholder="Enter your full address"></textarea>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Delivery Method</label>
              <div class="w-full px-3 py-2 border rounded-lg bg-gray-50 text-gray-700">
                Pickup from Farm
                <input type="hidden" v-model="checkoutForm.delivery_method" value="pickup">
              </div>
              <p class="mt-1 text-xs text-gray-500">Products must be picked up from the farmer's location.</p>
            </div>

            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-1">Payment Method</label>
              <select v-model="checkoutForm.payment_method" required class="w-full px-3 py-2 border rounded-lg">
                <option value="Cash on Delivery">Cash on Delivery</option>
                <option value="Bank Transfer">Bank Transfer</option>
                <option value="GCash">GCash</option>
              </select>
            </div>

            <div class="mb-6">
              <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
              <textarea v-model="checkoutForm.notes" rows="2" class="w-full px-3 py-2 border rounded-lg"
                placeholder="Any special instructions?"></textarea>
            </div>

            <div class="border-t pt-4 mb-4">
              <div class="flex justify-between text-lg font-bold">
                <span>Total</span>
                <span>₱{{ formatNumber(total) }}</span>
              </div>
            </div>

            <div class="flex gap-3">
              <button type="button" @click="showCheckoutModal = false"
                class="flex-1 px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200"
              >Cancel</button>
              <button type="submit"
                class="flex-1 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700"
              >Place Order</button>
            </div>
          </form>
        </div>
      </div>
      
      <!-- Confirmation Modal -->
      <ConfirmationModal
        :show="showConfirmModal"
        :title="confirmTitle"
        :message="confirmMessage"
        :confirm-text="confirmButtonText"
        :type="confirmType"
        @close="showConfirmModal = false"
        @confirm="handleConfirmAction"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useMarketplaceStore } from '@/stores/marketplace'
import ConfirmationModal from '@/Components/UI/ConfirmationModal.vue'

const router = useRouter()
const marketplaceStore = useMarketplaceStore()
const showCheckoutModal = ref(false)
const checkingOut = ref(false)

// Confirmation State
const showConfirmModal = ref(false)
const confirmTitle = ref('')
const confirmMessage = ref('')
const confirmButtonText = ref('Confirm')
const confirmType = ref('danger')
const pendingAction = ref(null)

const checkoutForm = ref({
  delivery_address: '',
  delivery_method: 'pickup',
  payment_method: 'Cash on Delivery',
  notes: '',
})

// Use store getters and state
const loading = computed(() => marketplaceStore.loading)
const cartItems = computed(() => marketplaceStore.cart)
const total = computed(() => marketplaceStore.cartTotal)

const updateQuantity = async (item, delta) => {
  const newQty = item.quantity + delta
  if (newQty < 1) return
  await marketplaceStore.updateCartQuantity(item.id, newQty)
}

const saveQuantity = async (item) => {
  await marketplaceStore.updateCartQuantity(item.id, item.quantity)
}

const confirmRemoveItem = (item) => {
  pendingAction.value = () => removeItem(item)
  confirmTitle.value = 'Remove Item'
  confirmMessage.value = `Are you sure you want to remove "${item.name}" from your cart?`
  confirmButtonText.value = 'Remove'
  confirmType.value = 'danger'
  showConfirmModal.value = true
}

const removeItem = async (item) => {
  await marketplaceStore.removeFromCart(item.id)
  showConfirmModal.value = false
}

const confirmClearCart = () => {
  pendingAction.value = clearCart
  confirmTitle.value = 'Clear Cart'
  confirmMessage.value = 'Are you sure you want to remove all items from your cart? This action cannot be undone.'
  confirmButtonText.value = 'Clear Cart'
  confirmType.value = 'danger'
  showConfirmModal.value = true
}

const clearCart = async () => {
  await marketplaceStore.clearCart()
  showConfirmModal.value = false
}

const confirmCheckout = () => {
  pendingAction.value = processCheckout
  confirmTitle.value = 'Confirm Order'
  confirmMessage.value = `Are you sure you want to place this order? Total amount to pay is ₱${formatNumber(total.value)}.`
  confirmButtonText.value = 'Place Order'
  confirmType.value = 'success'
  showCheckoutModal.value = false 
  showConfirmModal.value = true
}

const processCheckout = async () => {
  checkingOut.value = true
  showConfirmModal.value = false 
  
  try {
    const addressParts = checkoutForm.value.delivery_address.split(',').map(s => s.trim())
    
    await marketplaceStore.checkout({
      delivery_address: {
        street: addressParts[0] || checkoutForm.value.delivery_address,
        city: addressParts[1] || '',
        state: addressParts[2] || '',
        country: 'Philippines'
      },
      delivery_method: checkoutForm.value.delivery_method,
      payment_method: checkoutForm.value.payment_method,
      notes: checkoutForm.value.notes,
    })
    
    alert('Order placed successfully!')
    router.push('/buyer/orders')
  } catch (error) {
    alert(marketplaceStore.error || 'Checkout failed')
  } finally {
    checkingOut.value = false
  }
}

const handleConfirmAction = () => {
  if (pendingAction.value) {
    pendingAction.value()
  }
  pendingAction.value = null
}

const formatNumber = (value) => {
  return Number(value || 0).toLocaleString('en-PH', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

onMounted(() => {
  marketplaceStore.fetchCart()
})
</script>
