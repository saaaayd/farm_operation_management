# Farm Operations Management System - Setup Guide

## 🚀 Quick Start

### 1. Prerequisites
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL 8.0+
- OpenWeatherMap API key (free)

### 2. Installation Steps

```bash
# Clone and setup
git clone <repository-url>
cd farm-operations-system

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Configure .env file with:
# - MySQL database connection
# - OpenWeatherMap API key

# Run migrations
php artisan migrate

# Build assets and start
npm run dev
php artisan serve
```

### 3. Default Login Credentials

**Admin**: admin@farmops.com / password
**Farmer**: john@farmops.com / password  
**Buyer**: alice@farmops.com / password

## 🗄 MySQL Database Setup

1. Install MySQL 8.0+ on your system
2. Create a database named `farm_operation_management`
3. Update database credentials in `.env` file:
   ```
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=farm_operation_management
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```
4. Run migrations: `php artisan migrate`
5. Seed the database: `php artisan db:seed`

## 🌤 OpenWeatherMap API Setup

1. Sign up at https://openweathermap.org/api
2. Get your free API key
3. Update `OPENWEATHER_API_KEY` in `.env`

## 📱 Key Features

- **Role-based Dashboard**: Different views for Admin/Farmer/Buyer
- **Field Management**: GPS tracking and soil data
- **Weather Integration**: Real-time data and forecasts
- **Task Management**: Assign work to laborers
- **Inventory Tracking**: Low-stock alerts
- **Marketplace**: Product sales platform
- **Financial Reports**: Expense and income tracking

## 🛠 Tech Stack

- Laravel 12 + MySQL
- Vue.js 3 + Tailwind CSS
- Laravel Sanctum (Auth)
- OpenWeatherMap API
- Chart.js for analytics

## 📊 Database Tables

- users, fields, plantings, harvests
- tasks, laborers, labor_wages
- inventory_items, orders, sales
- expenses, weather_logs

## 🔗 API Endpoints

- `/api/login` - Authentication
- `/api/dashboard` - Role-based dashboard
- `/api/fields` - Field management
- `/api/weather/*` - Weather data
- `/api/marketplace/*` - Product marketplace

## 🎯 Usage

1. **Admin**: Manage all users and system-wide stats
2. **Farmers**: Manage fields, plantings, tasks, inventory
3. **Buyers**: Browse products, place orders, track deliveries

## 🔒 Security

- Laravel Sanctum API tokens
- Role-based access control
- Input validation
- SQL injection prevention

---

For detailed documentation, see the full README.md file.