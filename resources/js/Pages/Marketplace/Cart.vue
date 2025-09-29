<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-4">
          <div class="flex items-center">
            <router-link to="/marketplace" class="text-gray-500 hover:text-gray-700 mr-4">
              <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
              </svg>
            </router-link>
            <div>
              <h1 class="text-xl font-semibold text-gray-900">Shopping Cart</h1>
              <p class="text-sm text-gray-500">Review your rice products before checkout</p>
            </div>
          </div>
          
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-600">
              {{ marketplaceStore.cartItemsCount }} items
            </span>
            <button 
              v-if="marketplaceStore.cartItemsCount > 0"
              @click="clearCart"
              class="text-red-600 hover:text-red-700 text-sm font-medium"
            >
              Clear Cart
            </button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div v-if="marketplaceStore.cartItemsCount === 0" class="text-center py-12">
        <svg class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4m0 0L7 13m0 0l-2.5 5M7 13l2.5 5m6-5v6a2 2 0 11-4 0v-6m4 0V9a2 2 0 00-2-2H9a2 2 0 00-2 2v4.01" />
        </svg>
        <h3 class="text-lg font-medium text-gray-900 mb-2">Your cart is empty</h3>
        <p class="text-gray-600 mb-4">Add some rice products to get started</p>
        <router-link 
          to="/marketplace"
          class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors inline-flex items-center"
        >
          <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
          </svg>
          Browse Products
        </router-link>
      </div>

      <div v-else class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Cart Items -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Cart Items</h3>
            </div>
            
            <div class="divide-y divide-gray-200">
              <div 
                v-for="item in marketplaceStore.cart" 
                :key="item.id"
                class="p-6"
              >
                <div class="flex items-center space-x-4">
                  <!-- Product Image -->
                  <div class="h-20 w-20 bg-gradient-to-br from-green-100 to-emerald-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="h-10 w-10 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                  </div>

                  <!-- Product Info -->
                  <div class="flex-1 min-w-0">
                    <h4 class="text-lg font-medium text-gray-900">{{ item.name }}</h4>
                    <p class="text-sm text-gray-600">{{ item.farmer?.name || 'Local Farmer' }}</p>
                    <p class="text-sm font-medium text-green-600">${{ item.price }}/{{ item.unit }}</p>
                  </div>

                  <!-- Quantity Controls -->
                  <div class="flex items-center space-x-3">
                    <button 
                      @click="updateQuantity(item.id, item.quantity - 1)"
                      class="h-8 w-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50"
                    >
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4" />
                      </svg>
                    </button>
                    
                    <span class="text-lg font-medium text-gray-900 w-8 text-center">
                      {{ item.quantity }}
                    </span>
                    
                    <button 
                      @click="updateQuantity(item.id, item.quantity + 1)"
                      class="h-8 w-8 rounded-full border border-gray-300 flex items-center justify-center hover:bg-gray-50"
                    >
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                      </svg>
                    </button>
                  </div>

                  <!-- Item Total -->
                  <div class="text-right">
                    <p class="text-lg font-semibold text-gray-900">
                      ${{ (item.price * item.quantity).toFixed(2) }}
                    </p>
                    <button 
                      @click="removeItem(item.id)"
                      class="text-red-600 hover:text-red-700 text-sm font-medium"
                    >
                      Remove
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Order Summary -->
        <div class="lg:col-span-1">
          <div class="bg-white rounded-lg shadow p-6 sticky top-8">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Order Summary</h3>
            
            <div class="space-y-3 mb-6">
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Subtotal</span>
                <span class="text-gray-900">${{ marketplaceStore.cartTotal.toFixed(2) }}</span>
              </div>
              
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Shipping</span>
                <span class="text-gray-900">${{ shippingCost.toFixed(2) }}</span>
              </div>
              
              <div class="flex justify-between text-sm">
                <span class="text-gray-600">Tax</span>
                <span class="text-gray-900">${{ taxAmount.toFixed(2) }}</span>
              </div>
              
              <div class="border-t border-gray-200 pt-3">
                <div class="flex justify-between text-lg font-semibold">
                  <span class="text-gray-900">Total</span>
                  <span class="text-gray-900">${{ totalAmount.toFixed(2) }}</span>
                </div>
              </div>
            </div>

            <!-- Delivery Information -->
            <div class="mb-6">
              <h4 class="text-sm font-medium text-gray-900 mb-3">Delivery Information</h4>
              <div class="space-y-2 text-sm text-gray-600">
                <div class="flex items-center">
                  <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Estimated delivery: 3-5 business days
                </div>
                <div class="flex items-center">
                  <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  Free shipping on orders over $50
                </div>
              </div>
            </div>

            <!-- Checkout Button -->
            <button 
              @click="proceedToCheckout"
              class="w-full bg-green-600 text-white py-3 px-4 rounded-lg hover:bg-green-700 transition-colors font-medium"
            >
              Proceed to Checkout
            </button>

            <!-- Continue Shopping -->
            <router-link 
              to="/marketplace"
              class="block w-full text-center bg-gray-200 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-300 transition-colors font-medium mt-3"
            >
              Continue Shopping
            </router-link>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue';
import { useRouter } from 'vue-router';
import { useMarketplaceStore } from '@/stores/marketplace';

const router = useRouter();
const marketplaceStore = useMarketplaceStore();

const shippingCost = computed(() => {
  return marketplaceStore.cartTotal >= 50 ? 0 : 10;
});

const taxAmount = computed(() => {
  return marketplaceStore.cartTotal * 0.08; // 8% tax
});

const totalAmount = computed(() => {
  return marketplaceStore.cartTotal + shippingCost.value + taxAmount.value;
});

const updateQuantity = (productId, newQuantity) => {
  if (newQuantity <= 0) {
    marketplaceStore.removeFromCart(productId);
  } else {
    marketplaceStore.updateCartQuantity(productId, newQuantity);
  }
};

const removeItem = (productId) => {
  marketplaceStore.removeFromCart(productId);
};

const clearCart = () => {
  marketplaceStore.clearCart();
};

const proceedToCheckout = () => {
  router.push('/checkout');
};
</script>