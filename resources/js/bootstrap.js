import axios from 'axios';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;

// Set base URL
window.axios.defaults.baseURL = window.location.origin;

// Add token from localStorage if available
const token = localStorage.getItem('token');
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
}

// Response interceptor is handled in services/api.js to avoid conflicts

// Export axios for use in Vue components
export default axios;
