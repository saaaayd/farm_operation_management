<template>
  <div class="profile-page">
    <div class="container mx-auto px-4 py-8">
      <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
          <h1 class="text-3xl font-bold text-gray-900">Profile</h1>
          <p class="text-gray-600 mt-2">Manage your account information and preferences</p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Profile Information -->
          <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow-md p-6">
              <h2 class="text-xl font-semibold mb-6">Personal Information</h2>
              
              <form @submit.prevent="updateProfile" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">First Name</label>
                    <input
                      v-model="profile.first_name"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      required
                    />
                  </div>
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Last Name</label>
                    <input
                      v-model="profile.last_name"
                      type="text"
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                      required
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                  <input
                    v-model="profile.email"
                    type="email"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                  <input
                    v-model="profile.phone"
                    type="tel"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Bio</label>
                  <textarea
                    v-model="profile.bio"
                    rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    placeholder="Tell us about yourself..."
                  ></textarea>
                </div>

                <div class="flex justify-end">
                  <button
                    type="submit"
                    :disabled="loading"
                    class="bg-blue-600 text-white px-6 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 disabled:opacity-50"
                  >
                    {{ loading ? 'Saving...' : 'Save Changes' }}
                  </button>
                </div>
              </form>
            </div>

            <!-- Change Password -->
            <div class="bg-white rounded-lg shadow-md p-6 mt-6">
              <h2 class="text-xl font-semibold mb-6">Change Password</h2>
              
              <form @submit.prevent="changePassword" class="space-y-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                  <input
                    v-model="passwordForm.current_password"
                    type="password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                  <input
                    v-model="passwordForm.new_password"
                    type="password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">Confirm New Password</label>
                  <input
                    v-model="passwordForm.new_password_confirmation"
                    type="password"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                    required
                  />
                </div>

                <div class="flex justify-end">
                  <button
                    type="submit"
                    :disabled="passwordLoading"
                    class="bg-green-600 text-white px-6 py-2 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 disabled:opacity-50"
                  >
                    {{ passwordLoading ? 'Updating...' : 'Update Password' }}
                  </button>
                </div>
              </form>
            </div>
          </div>

          <!-- Profile Sidebar -->
          <div class="lg:col-span-1">
            <!-- Profile Picture -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
              <h3 class="text-lg font-semibold mb-4">Profile Picture</h3>
              <div class="text-center">
                <!-- Profile Image or Initials -->
                <div class="relative w-32 h-32 mx-auto mb-4">
                  <div 
                    v-if="profilePictureUrl"
                    class="w-32 h-32 rounded-full overflow-hidden border-4 border-gray-200"
                  >
                    <img 
                      :src="profilePictureUrl" 
                      alt="Profile picture" 
                      class="w-full h-full object-cover"
                    />
                  </div>
                  <div 
                    v-else 
                    class="w-32 h-32 bg-gray-200 rounded-full flex items-center justify-center border-4 border-gray-200"
                  >
                    <span class="text-4xl text-gray-500">{{ initials }}</span>
                  </div>
                  
                  <!-- Upload overlay on hover -->
                  <label 
                    class="absolute inset-0 w-32 h-32 rounded-full bg-black bg-opacity-50 flex items-center justify-center cursor-pointer opacity-0 hover:opacity-100 transition-opacity"
                  >
                    <span class="text-white text-sm font-medium">
                      {{ uploadingPicture ? 'Uploading...' : 'Change' }}
                    </span>
                    <input 
                      type="file" 
                      accept="image/*" 
                      class="hidden"
                      @change="handlePictureUpload"
                      :disabled="uploadingPicture"
                    />
                  </label>
                </div>
                
                <!-- Action Buttons -->
                <div class="space-y-2">
                  <label 
                    class="block w-full text-center text-blue-600 hover:text-blue-800 text-sm cursor-pointer font-medium"
                  >
                    {{ uploadingPicture ? 'Uploading...' : 'Upload New Picture' }}
                    <input 
                      type="file" 
                      accept="image/*" 
                      class="hidden"
                      @change="handlePictureUpload"
                      :disabled="uploadingPicture"
                    />
                  </label>
                  <button 
                    v-if="profilePictureUrl"
                    @click="deletePicture"
                    :disabled="deletingPicture"
                    class="text-red-600 hover:text-red-800 text-sm font-medium disabled:opacity-50"
                  >
                    {{ deletingPicture ? 'Removing...' : 'Remove Picture' }}
                  </button>
                </div>
                
                <p class="text-xs text-gray-500 mt-3">
                  Recommended: Square image, at least 200x200px. Max 2MB.
                </p>
              </div>
            </div>

            <!-- Account Stats -->
            <div class="bg-white rounded-lg shadow-md p-6">
              <h3 class="text-lg font-semibold mb-4">Account Information</h3>
              <div class="space-y-3">
                <div class="flex justify-between">
                  <span class="text-gray-600">Member since:</span>
                  <span class="font-medium">{{ formatDate(profile.created_at) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Role:</span>
                  <span class="font-medium capitalize">{{ profile.role }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Status:</span>
                  <span class="font-medium text-green-600">Active</span>
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
import { useAuthStore } from '@/stores/auth'
import axios from 'axios'

const authStore = useAuthStore()
const loading = ref(false)
const passwordLoading = ref(false)
const uploadingPicture = ref(false)
const deletingPicture = ref(false)

const profile = ref({
  first_name: '',
  last_name: '',
  email: '',
  phone: '',
  bio: '',
  role: '',
  created_at: '',
  profile_picture: null
})

const passwordForm = ref({
  current_password: '',
  new_password: '',
  new_password_confirmation: ''
})

const initials = computed(() => {
  const first = profile.value.first_name?.charAt(0) || ''
  const last = profile.value.last_name?.charAt(0) || ''
  return (first + last).toUpperCase()
})

const profilePictureUrl = computed(() => {
  if (profile.value.profile_picture) {
    // Handle both relative paths and full URLs
    if (profile.value.profile_picture.startsWith('http')) {
      return profile.value.profile_picture
    }
    return `/storage/${profile.value.profile_picture}`
  }
  return null
})

const formatDate = (date) => {
  if (!date) return ''
  return new Date(date).toLocaleDateString()
}

const handlePictureUpload = async (event) => {
  const file = event.target.files?.[0]
  if (!file) return

  // Validate file size (2MB max)
  if (file.size > 2 * 1024 * 1024) {
    alert('File size must be less than 2MB')
    return
  }

  // Validate file type
  if (!file.type.startsWith('image/')) {
    alert('Please select an image file')
    return
  }

  uploadingPicture.value = true
  try {
    const formData = new FormData()
    formData.append('profile_picture', file)

    const response = await axios.post('/api/profile/picture', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })

    // Update local state
    profile.value.profile_picture = response.data.user.profile_picture
    
    // Update auth store
    if (authStore.user) {
      authStore.user.profile_picture = response.data.user.profile_picture
    }

    alert('Profile picture updated successfully!')
  } catch (error) {
    console.error('Failed to upload profile picture:', error)
    alert(error.response?.data?.message || 'Failed to upload profile picture')
  } finally {
    uploadingPicture.value = false
    // Reset file input
    event.target.value = ''
  }
}

const deletePicture = async () => {
  if (!confirm('Are you sure you want to remove your profile picture?')) return

  deletingPicture.value = true
  try {
    await axios.delete('/api/profile/picture')
    
    // Update local state
    profile.value.profile_picture = null
    
    // Update auth store
    if (authStore.user) {
      authStore.user.profile_picture = null
    }

    alert('Profile picture removed successfully!')
  } catch (error) {
    console.error('Failed to delete profile picture:', error)
    alert(error.response?.data?.message || 'Failed to remove profile picture')
  } finally {
    deletingPicture.value = false
  }
}

const updateProfile = async () => {
  loading.value = true
  try {
    await authStore.updateProfile(profile.value)
    alert('Profile updated successfully!')
  } catch (error) {
    console.error('Error updating profile:', error)
    alert(error.response?.data?.message || 'Failed to update profile')
  } finally {
    loading.value = false
  }
}

const changePassword = async () => {
  if (passwordForm.value.new_password !== passwordForm.value.new_password_confirmation) {
    alert('Passwords do not match')
    return
  }

  passwordLoading.value = true
  try {
    await authStore.changePassword(passwordForm.value)
    passwordForm.value = {
      current_password: '',
      new_password: '',
      new_password_confirmation: ''
    }
    alert('Password changed successfully!')
  } catch (error) {
    console.error('Error changing password:', error)
    alert(error.response?.data?.message || 'Failed to change password')
  } finally {
    passwordLoading.value = false
  }
}

onMounted(() => {
  // Load user profile data
  if (authStore.user) {
    profile.value = { ...authStore.user }
  }
})
</script>

<style scoped>
.profile-page {
  min-height: 100vh;
  background-color: #f8fafc;
}
</style>