<template>
  <div class="product-detail-page">
    <div class="container mx-auto px-4 py-8">
      <!-- Header -->
      <div class="flex justify-between items-center mb-8">
        <div>
          <nav class="text-sm text-gray-500 mb-2">
            <router-link to="/marketplace" class="hover:text-gray-700">Marketplace</router-link>
            <span class="mx-2">/</span>
            <span class="text-gray-900">{{ product.name }}</span>
          </nav>
          <h1 class="text-3xl font-bold text-gray-900">{{ product.name }}</h1>
          <p class="text-gray-600 mt-2">Sold by {{ product.seller_name }}</p>
        </div>
        <div class="flex space-x-3">
          <button
            type="button"
            @click="addToCart"
            class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
          >
            Add to Cart
          </button>
          <button
            type="button"
            @click="contactSeller"
            class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
            Contact Seller
          </button>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Product Images -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <div class="aspect-w-16 aspect-h-9 bg-gray-200 rounded-lg mb-4">
              <div class="w-full h-96 bg-gray-200 flex items-center justify-center">
                <span class="text-gray-500 text-8xl">{{ product.icon }}</span>
              </div>
            </div>
            <div class="grid grid-cols-4 gap-2">
              <div class="aspect-w-1 aspect-h-1 bg-gray-200 rounded cursor-pointer">
                <div class="w-full h-20 bg-gray-200 flex items-center justify-center">
                  <span class="text-gray-500 text-2xl">{{ product.icon }}</span>
                </div>
              </div>
              <div class="aspect-w-1 aspect-h-1 bg-gray-200 rounded cursor-pointer">
                <div class="w-full h-20 bg-gray-200 flex items-center justify-center">
                  <span class="text-gray-500 text-2xl">{{ product.icon }}</span>
                </div>
              </div>
              <div class="aspect-w-1 aspect-h-1 bg-gray-200 rounded cursor-pointer">
                <div class="w-full h-20 bg-gray-200 flex items-center justify-center">
                  <span class="text-gray-500 text-2xl">{{ product.icon }}</span>
                </div>
              </div>
              <div class="aspect-w-1 aspect-h-1 bg-gray-200 rounded cursor-pointer">
                <div class="w-full h-20 bg-gray-200 flex items-center justify-center">
                  <span class="text-gray-500 text-2xl">{{ product.icon }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Product Description -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Description</h2>
            <p class="text-gray-700 leading-relaxed">{{ product.description }}</p>
          </div>

          <!-- Product Specifications -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Specifications</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div v-for="spec in product.specifications" :key="spec.name" class="flex justify-between">
                <span class="text-gray-600">{{ spec.name }}:</span>
                <span class="font-medium">{{ spec.value }}</span>
              </div>
            </div>
          </div>

          <!-- Reviews -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold mb-4">Reviews</h2>
            <div class="space-y-4">
              <div
                v-for="review in reviews"
                :key="review.id"
                class="border-b border-gray-200 pb-4 last:border-b-0"
              >
                <div class="flex items-start justify-between mb-2">
                  <div>
                    <div class="font-medium text-gray-900">{{ review.author }}</div>
                    <div class="flex items-center">
                      <div class="flex text-yellow-400">
                        <span v-for="i in 5" :key="i" class="text-sm">★</span>
                      </div>
                      <span class="text-sm text-gray-600 ml-2">{{ review.rating }}/5</span>
                    </div>
                  </div>
                  <span class="text-sm text-gray-500">{{ formatDate(review.date) }}</span>
                </div>
                <p class="text-gray-700">{{ review.comment }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Sidebar -->
        <div class="lg:col-span-1 space-y-6">
          <!-- Price and Purchase -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <div class="text-3xl font-bold text-green-600 mb-2">{{ formatCurrency(product.price) }}</div>
            <div class="text-gray-600 mb-4">{{ product.unit }}</div>
            
            <div class="space-y-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Quantity</label>
                <div class="flex items-center space-x-2">
                  <button
                    type="button"
                    @click="decreaseQuantity"
                    class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300"
                  >
                    -
                  </button>
                  <input
                    v-model="quantity"
                    type="number"
                    min="1"
                    :max="product.stock"
                    class="w-16 text-center border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                  <button
                    type="button"
                    @click="increaseQuantity"
                    class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center hover:bg-gray-300"
                  >
                    +
                  </button>
                </div>
              </div>
              
              <div class="text-sm text-gray-600">
                Stock: {{ product.stock }} available
              </div>
              
              <div class="text-lg font-semibold">
                Total: {{ formatCurrency(product.price * quantity) }}
              </div>
              
              <button
                type="button"
                @click="addToCart"
                class="w-full bg-green-600 text-white py-3 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500"
              >
                Add to Cart
              </button>
            </div>
          </div>

          <!-- Seller Information -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Seller Information</h3>
            <div class="space-y-3">
              <div class="flex items-center space-x-3">
                <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center">
                  <span class="text-gray-600 text-lg">👤</span>
                </div>
                <div>
                  <div class="font-medium text-gray-900">{{ product.seller_name }}</div>
                  <div class="text-sm text-gray-600">{{ product.location }}</div>
                </div>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-gray-600">Rating:</span>
                <div class="flex items-center">
                  <div class="flex text-yellow-400">
                    <span v-for="i in 5" :key="i" class="text-sm">★</span>
                  </div>
                  <span class="text-sm text-gray-600 ml-2">{{ product.rating }}/5</span>
                </div>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-gray-600">Member since:</span>
                <span class="text-sm">{{ product.member_since }}</span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-gray-600">Products sold:</span>
                <span class="text-sm">{{ product.products_sold }}</span>
              </div>
            </div>
          </div>

          <!-- Shipping Information -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Shipping & Delivery</h3>
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-gray-600">Shipping:</span>
                <span class="text-sm font-medium">{{ product.shipping_cost === 0 ? 'Free' : formatCurrency(product.shipping_cost) }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-600">Delivery:</span>
                <span class="text-sm">{{ product.delivery_time }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="text-gray-600">Pickup available:</span>
                <span class="text-sm">{{ product.pickup_available ? 'Yes' : 'No' }}</span>
              </div>
            </div>
          </div>

          <!-- Related Products -->
          <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold mb-4">Related Products</h3>
            <div class="space-y-3">
              <div
                v-for="related in relatedProducts"
                :key="related.id"
                class="flex items-center space-x-3 p-2 hover:bg-gray-50 rounded cursor-pointer"
                @click="viewProduct(related.id)"
              >
                <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center">
                  <span class="text-gray-500">{{ related.icon }}</span>
                </div>
                <div class="flex-1">
                  <div class="font-medium text-gray-900 text-sm">{{ related.name }}</div>
                  <div class="text-green-600 font-medium text-sm">{{ formatCurrency(related.price) }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { formatCurrency } from '@/utils/format'

const route = useRoute()
const router = useRouter()
const quantity = ref(1)

const product = ref({
  id: null,
  name: '',
  description: '',
  price: 0,
  unit: '',
  category: '',
  seller_name: '',
  location: '',
  rating: 0,
  icon: '',
  stock: 0,
  specifications: [],
  shipping_cost: 0,
  delivery_time: '',
  pickup_available: false,
  member_since: '',
  products_sold: 0
})

const reviews = ref([
  {
    id: 1,
    author: 'John Smith',
    rating: 5,
    comment: 'Excellent quality seeds. Great germination rate and healthy plants.',
    date: '2024-03-20T10:00:00Z'
  },
  {
    id: 2,
    author: 'Mike Johnson',
    rating: 4,
    comment: 'Good product, fast shipping. Would recommend to other farmers.',
    date: '2024-03-18T14:30:00Z'
  },
  {
    id: 3,
    author: 'Sarah Wilson',
    rating: 5,
    comment: 'Perfect for my farm. High yield and disease resistant.',
    date: '2024-03-15T09:15:00Z'
  }
])

const relatedProducts = ref([
  {
    id: 2,
    name: 'Nitrogen Fertilizer',
    price: 450.00,
    icon: '🌿'
  },
  {
    id: 3,
    name: 'Garden Tools Set',
    price: 89.99,
    icon: '🔧'
  }
])

const formatDate = (date) => {
  return new Date(date).toLocaleDateString()
}

const increaseQuantity = () => {
  if (quantity.value < product.value.stock) {
    quantity.value++
  }
}

const decreaseQuantity = () => {
  if (quantity.value > 1) {
    quantity.value--
  }
}

const addToCart = () => {
  // Add to cart logic
  console.log('Add to cart:', product.value.id, quantity.value)
}

const contactSeller = () => {
  // Contact seller logic
  console.log('Contact seller')
}

const viewProduct = (id) => {
  router.push(`/marketplace/product/${id}`)
}

onMounted(() => {
  const productId = route.params.id
  // Load product data from API
  loadProductData(productId)
})

const loadProductData = async (id) => {
  try {
    // API call to load product data
    // For now, using mock data
    product.value = {
      id: id,
      name: 'Corn Seeds - Pioneer 1234',
      description: 'High-yield corn seeds perfect for spring planting. These seeds offer excellent disease resistance and are specifically bred for optimal performance in various soil conditions. Perfect for both commercial and small-scale farming operations.',
      price: 180.00,
      unit: 'per bag',
      category: 'seeds',
      seller_name: 'AgriSupply Co.',
      location: 'Springfield, IL',
      rating: 4.8,
      icon: '🌱',
      stock: 50,
      specifications: [
        { name: 'Variety', value: 'Pioneer 1234' },
        { name: 'Germination Rate', value: '95%' },
        { name: 'Maturity', value: '110 days' },
        { name: 'Yield Potential', value: '200+ bushels/acre' },
        { name: 'Disease Resistance', value: 'High' },
        { name: 'Drought Tolerance', value: 'Good' }
      ],
      shipping_cost: 15.00,
      delivery_time: '3-5 business days',
      pickup_available: true,
      member_since: '2020',
      products_sold: 1250
    }
  } catch (error) {
    console.error('Error loading product data:', error)
  }
}
</script>

<style scoped>
.product-detail-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>