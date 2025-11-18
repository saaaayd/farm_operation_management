# RiceFARM Frontend - Vue.js 3 Application

A comprehensive Vue.js 3 frontend for the RiceFARM monolithic rice farming management system, built with modern web technologies and optimized for rice farming operations.

## 🚀 Features

### Core Functionality
- **Role-based Authentication**: Farmer, Buyer, and Admin roles with different access levels
- **Farm Profile Onboarding**: Mandatory setup for new farmers with field details and rice varietal preferences
- **Rice Operations Management**: Complete lifecycle from planting to harvest
- **Weather Analytics**: Real-time weather data and historical analysis
- **Marketplace**: Rice product browsing and purchasing system
- **Comprehensive Reporting**: Yield, financial, and weather correlation reports

### Technical Features
- **Vue.js 3** with Composition API and `<script setup>` syntax
- **Pinia** for state management
- **Vue Router** with navigation guards
- **Tailwind CSS** for responsive styling
- **Chart.js** with vue-chartjs for data visualization
- **Axios** for API communication
- **Heroicons** for consistent iconography

## 📁 Project Structure

```
resources/js/
├── Components/           # Reusable UI components
│   ├── Charts/          # Chart.js components
│   ├── Forms/           # Form components
│   └── UI/              # General UI components
├── Pages/               # Page components
│   ├── Auth/            # Authentication pages
│   ├── Onboarding/      # Farm profile setup
│   ├── Farmer/          # Farmer-specific pages
│   │   ├── Dashboard.vue
│   │   ├── Plantings/   # Rice planting management
│   │   ├── Tasks/       # Task management
│   │   ├── Harvests/    # Harvest tracking
│   │   ├── Weather/     # Weather analytics
│   │   └── Reports/     # Reporting & analytics
│   ├── Buyer/           # Buyer-specific pages
│   ├── Admin/           # Admin pages
│   └── Marketplace/     # Marketplace pages
├── stores/              # Pinia stores
│   ├── auth.js          # Authentication state
│   ├── farm.js          # Farm operations state
│   ├── weather.js       # Weather data state
│   ├── marketplace.js   # Marketplace state
│   └── inventory.js     # Inventory state
├── services/            # API services
│   └── api.js           # Centralized API client
├── routes.js            # Vue Router configuration
├── app.js               # Main application entry
└── bootstrap.js         # Axios configuration
```

## 🛠 Installation & Setup

### Prerequisites
- Node.js 18+ 
- npm or yarn
- Laravel backend running

### Installation Steps

1. **Install Dependencies**
   ```bash
   npm install
   ```

2. **Development Server**
   ```bash
   npm run dev
   ```

3. **Production Build**
   ```bash
   npm run build
   ```

## 🎯 User Roles & Features

### 👨‍🌾 Farmer Role
- **Dashboard**: Overview of farm operations with weather widget
- **Farm Profile**: Mandatory onboarding with field details and rice preferences
- **Planting Management**: Create and track rice plantings with variety selection
- **Task Management**: Rice-specific tasks (land prep, transplanting, fertilizing, etc.)
- **Harvest Tracking**: Record harvests with quality assessment
- **Weather Analytics**: Real-time weather and historical analysis
- **Inventory Management**: Track seeds, fertilizers, pesticides, equipment
- **Reports**: Yield, financial, and weather correlation reports

### 🛒 Buyer Role
- **Marketplace**: Browse and purchase rice products
- **Shopping Cart**: Add products and manage quantities
- **Order Management**: Track order history, status, and pre-order availability
- **In-app Messaging**: Chat with farmers on each order to coordinate pickup or delivery (payments remain offline)
- **Product Details**: View rice variety information and farmer details

### 👨‍💼 Admin Role
- **User Management**: Manage farmers and buyers
- **System Overview**: Monitor system-wide statistics
- **Content Management**: Manage categories and products

## 🔧 Key Components

### Authentication System
- **Login/Register**: Role-based registration (Farmer/Buyer)
- **Onboarding**: Mandatory farm profile setup for farmers
- **Route Guards**: Automatic redirection based on authentication and role

### Weather Integration
- **Current Weather Widget**: Real-time weather for primary field
- **5-Day Forecast**: Planning assistance for farming activities
- **Weather Alerts**: Critical weather warnings for rice farming
- **Historical Analysis**: Weather correlation with yield data

### Rice Operations
- **Planting Management**: 
  - Field selection and rice variety choice
  - Growth duration calculation
  - Planting schedule management
- **Task Management**:
  - Rice-specific task types (land prep, transplanting, etc.)
  - Priority and status tracking
  - Labor assignment
- **Harvest Tracking**:
  - Yield recording with quality grades
  - Moisture content and broken grain percentage
  - Pricing and storage information

### Marketplace Features
- **Product Catalog**: Filter by rice variety and farmer
- **Shopping Cart**: Persistent cart with quantity management
- **Order Processing**: Complete checkout flow
- **Product Details**: Comprehensive rice product information

## 📊 State Management (Pinia)

### Auth Store (`stores/auth.js`)
- User authentication state
- Role-based access control
- Profile management
- Token handling

### Farm Store (`stores/farm.js`)
- Farm profile data
- Fields, plantings, tasks, harvests
- CRUD operations for farm data
- Computed properties for dashboard stats

### Weather Store (`stores/weather.js`)
- Current weather data
- Forecast information
- Historical weather data
- Weather alerts and warnings

### Marketplace Store (`stores/marketplace.js`)
- Product catalog
- Shopping cart management
- Order history
- Sales data

### Inventory Store (`stores/inventory.js`)
- Inventory items management
- Stock level tracking
- Low stock alerts
- Category management

## 🌐 API Integration

### Centralized API Service (`services/api.js`)
- Organized API endpoints by feature
- Automatic token management
- Error handling and interceptors
- Consistent response handling

### API Endpoints Structure
```javascript
// Authentication
authAPI.login(credentials)
authAPI.register(userData)
authAPI.getUser()

// Farm Operations
plantingsAPI.getAll()
plantingsAPI.create(plantingData)
tasksAPI.update(taskId, taskData)
harvestsAPI.create(harvestData)

// Weather
weatherAPI.getCurrentWeather(fieldId)
weatherAPI.getForecast(fieldId)
weatherAPI.getHistory(fieldId, days)

// Marketplace
marketplaceAPI.getProducts(filters)
ordersAPI.create(orderData)
```

## 🎨 UI/UX Design

### Design System
- **Color Scheme**: Green/earth-tone palette reflecting agriculture
- **Typography**: Clean, readable fonts with proper hierarchy
- **Components**: Consistent button styles, form inputs, and cards
- **Responsive**: Mobile-first design with Tailwind CSS

### Key UI Patterns
- **Dashboard Cards**: Statistical overview with icons
- **Data Tables**: Sortable and filterable data presentation
- **Form Validation**: Real-time validation with error messages
- **Loading States**: Skeleton loaders and spinners
- **Empty States**: Helpful guidance when no data is available

## 📱 Responsive Design

The application is fully responsive with breakpoints:
- **Mobile**: < 768px
- **Tablet**: 768px - 1024px  
- **Desktop**: > 1024px

Key responsive features:
- Collapsible navigation on mobile
- Stacked layouts on smaller screens
- Touch-friendly buttons and inputs
- Optimized chart sizing

## 🔒 Security Features

- **JWT Token Management**: Secure authentication with automatic refresh
- **Route Protection**: Role-based access control
- **Input Validation**: Client-side validation with server-side verification
- **XSS Protection**: Sanitized user inputs
- **CSRF Protection**: Laravel CSRF tokens

## 📈 Performance Optimizations

- **Lazy Loading**: Route-based code splitting
- **Image Optimization**: Optimized product images
- **Caching**: API response caching in stores
- **Bundle Optimization**: Tree-shaking and minification
- **Chart Performance**: Optimized Chart.js rendering

## 🧪 Testing Strategy

### Component Testing
- Unit tests for individual components
- Integration tests for store interactions
- E2E tests for critical user flows

### Test Coverage Areas
- Authentication flows
- Farm operations CRUD
- Weather data integration
- Marketplace functionality
- Report generation

## 🚀 Deployment

### Production Build
```bash
npm run build
```

### Build Output
- Optimized JavaScript bundles
- Minified CSS
- Asset optimization
- Source maps for debugging

### Integration with Laravel
The built assets are automatically integrated with Laravel's `app.blade.php` template through Vite.

## 🔄 Development Workflow

### Code Organization
- **Components**: Reusable UI components
- **Pages**: Route-specific page components
- **Stores**: Centralized state management
- **Services**: API communication layer
- **Utils**: Helper functions and utilities

### Best Practices
- **Composition API**: Use `<script setup>` syntax
- **Reactive Data**: Proper use of `ref()` and `reactive()`
- **Computed Properties**: Efficient derived state
- **Error Handling**: Comprehensive error management
- **Type Safety**: JSDoc comments for better IDE support

## 📚 Documentation

### Component Documentation
Each component includes:
- Props documentation
- Event documentation
- Usage examples
- Styling guidelines

### API Documentation
- Endpoint descriptions
- Request/response formats
- Error handling
- Authentication requirements

## 🤝 Contributing

### Development Setup
1. Fork the repository
2. Create a feature branch
3. Make changes following the coding standards
4. Test thoroughly
5. Submit a pull request

### Coding Standards
- **Vue.js**: Follow Vue 3 Composition API best practices
- **JavaScript**: ES6+ features with proper error handling
- **CSS**: Tailwind CSS utility classes
- **Naming**: Descriptive component and variable names
- **Comments**: JSDoc for complex functions

## 📞 Support

For technical support or questions:
- Check the component documentation
- Review the API service layer
- Examine the Pinia store implementations
- Test with the Laravel backend

## 🔮 Future Enhancements

### Planned Features
- **Real-time Notifications**: WebSocket integration
- **Mobile App**: React Native or Flutter
- **Advanced Analytics**: Machine learning insights
- **Multi-language Support**: Internationalization
- **Offline Support**: Progressive Web App features

### Technical Improvements
- **TypeScript Migration**: Enhanced type safety
- **Performance Monitoring**: Real-time performance tracking
- **Advanced Caching**: Redis integration
- **Microservices**: Backend service separation

---

This Vue.js frontend provides a comprehensive, user-friendly interface for rice farming management, with modern web technologies and a focus on usability and performance.
