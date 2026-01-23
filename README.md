# ANIBUKID - Farm Operation Management System

<p align="center">
  <strong>A comprehensive farm management platform for rice farmers in Bukidnon, Philippines</strong>
</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel">
  <img src="https://img.shields.io/badge/Vue.js-3.5-4FC08D?style=for-the-badge&logo=vue.js&logoColor=white" alt="Vue.js">
  <img src="https://img.shields.io/badge/TailwindCSS-4.0-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white" alt="TailwindCSS">
  <img src="https://img.shields.io/badge/PostgreSQL-14.0-336791?style=for-the-badge&logo=postgresql&logoColor=white" alt="PostgreSQL">
</p>

---

## 📋 Table of Contents

1.  [About the Project](#about-the-project)
2.  [Features](#features)
3.  [Tech Stack](#tech-stack)
4.  [System Architecture](#system-architecture)
5.  [Module Overview](#module-overview)
6.  [Security Implementation](#security-implementation)
7.  [Weather & Analytics Engine](#weather--analytics-engine)
8.  [Installation & Setup](#installation--setup)
9.  [API Endpoints](#api-endpoints)
10. [Testing](#testing)

---

## 🌾 About the Project

**ANIBUKID** is a capstone project designed to digitize and optimize rice farming operations in Bukidnon, Philippines. The system integrates:

*   **Smart Weather Analysis:** Real-time weather monitoring with agronomic threshold-based alerts.
*   **Lifecycle Management:** Tracks crops from planting through all growth stages to harvest.
*   **Marketplace Integration:** Connects farmers directly with buyers for B2B rice trading.
*   **Financial Tracking:** Comprehensive expense, labor, and revenue management.

### Problem Statement
Traditional farming relies on manual record-keeping and experience-based decisions, leading to inefficiencies and potential crop losses. ANIBUKID addresses this by providing data-driven insights and actionable recommendations.

---

## ✨ Features

### 👨‍🌾 For Farmers
- **Field Management:** Register and manage multiple fields with GPS coordinates.
- **Planting Lifecycle:** Track growth stages (Seedling → Tillering → Flowering → Grain Filling → Maturity).
- **Weather Alerts:** Receive actionable advice based on temperature, humidity, and rainfall thresholds.
- **Harvest Recording:** Log yields, quality grades, and harvester shares.
- **Inventory Management:** Track seeds, fertilizers, pesticides with Weighted Average Cost (WAC).
- **Labor Management:** Assign tasks with Daily Rate, Piece Rate, or Pakyao (contract) payments.
- **Sales Dashboard:** Track revenue, analyze trends, and record direct sales.
- **Product Listing:** List rice products for sale in the marketplace.

### 🛒 For Buyers
- **Product Discovery:** Browse available rice products by variety, grade, and location.
- **Order Management:** Place orders with price negotiation capability.
- **Order Tracking:** Real-time status updates (Pending → Confirmed → Ready → Picked Up).

---

## 🛠️ Tech Stack

### Backend
| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | 8.2+ | Server-side language |
| **Laravel** | 12.x | MVC Framework |
| **Laravel Sanctum** | 4.2 | API Token Authentication |
| **Guzzle HTTP** | 7.8 | External API requests (Weather) |
| **PostgreSQL** | 14.0+ | Relational Database |
| **Doctrine DBAL** | 4.3 | Database schema modifications |

### Frontend
| Technology | Version | Purpose |
|------------|---------|---------|
| **Vue.js** | 3.5 | Reactive UI Framework |
| **Vue Router** | 4.4 | SPA Routing |
| **Pinia** | 2.2 | State Management |
| **Tailwind CSS** | 4.0 | Utility-first CSS |
| **Chart.js** | 4.4 | Data Visualization |
| **Vite** | 7.0 | Build Tool & Dev Server |
| **Axios** | 1.11 | HTTP Client |
| **Heroicons** | 2.1 | Icon Library |
| **Leaflet** | 1.9 | Interactive Maps (via CDN) |

### External Services
| Service | Purpose |
|---------|---------|
| **Open-Meteo API** | Primary (Current conditions & Historical logs) |
| **ColorfulClouds API** | Primary Forecasts (10-day precision) |
| **OpenWeatherMap** | Agricultural Intelligence (Pest/Disease risks) |
| **Windy.com** | Interactive weather map embed |
| **Twilio SDK** | SMS Verification (OTP) |
| **OpenStreetMap** | Base map tiles for field visualization |

---

## 🏗️ System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                         FRONTEND (Vue 3 SPA)                    │
│  ┌─────────┐  ┌─────────┐  ┌─────────┐  ┌─────────────────────┐ │
│  │  Pages  │  │  Stores │  │ Router  │  │  API Service Layer  │ │
│  │  (Vue)  │  │ (Pinia) │  │         │  │      (Axios)        │ │
│  └────┬────┘  └────┬────┘  └────┬────┘  └──────────┬──────────┘ │
│       └────────────┴────────────┴──────────────────┘            │
└───────────────────────────────┬─────────────────────────────────┘
                                │ HTTP/JSON
                                ▼
┌─────────────────────────────────────────────────────────────────┐
│                    BACKEND (Laravel 12)                         │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │                     API Routes                            │   │
│  │   /api/farmer/*  (FarmerMiddleware)                      │   │
│  │   /api/buyer/*   (BuyerMiddleware)                       │   │
│  │   /api/marketplace/* (Public + Auth)                     │   │
│  └──────────────────────────────────────────────────────────┘   │
│                                │                                │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────────────┐   │
│  │  Controllers │  │   Services   │  │       Models         │   │
│  │  (HTTP I/O)  │  │  (Business)  │  │  (Eloquent ORM)      │   │
│  └──────────────┘  └──────────────┘  └──────────────────────┘   │
│         │                  │                    │               │
│         └──────────────────┴────────────────────┘               │
│                           │                                     │
│  ┌────────────────────────┴─────────────────────────────────┐   │
│  │                  PostgreSQL Database                     │   │
│  │   users, farms, fields, plantings, harvests, expenses,   │   │
│  │   inventory_items, tasks, rice_products, rice_orders...  │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
```

---

## 📦 Module Overview

### 1. Farm & Field Management
- **Models:** `Farm`, `Field`, `Planting`, `PlantingStage`
- **Controllers:** `FarmController`, `FieldController`, `PlantingController`
- **Features:** GPS-based field registration, area calculation, crop rotation tracking.

### 2. Weather Analytics
- **Services:** `WeatherService`, `WeatherAnalyticsService`
- **Model:** `WeatherLog`
- **Features:**
  - Real-time weather fetching from Open-Meteo API
  - Growing Degree Days (GDD) calculation (Base: 10°C, Max: 30°C)
  - Agronomic threshold alerts (Heat Stress, Cold Stress, Drought, etc.)
  - Yield prediction using multi-factor analysis

### 3. Intelligent Data Analytics
- **Controller:** `DataAnalysisController`
- **Features:**
  - **Comprehensive Dashboard:** Aggregates metrics from 8+ modules (Weather, Sales, Expenses, Fields, Nursery, Inventory, Pests, Labor).
  - **Intelligent Action Suggestions:** AI-driven recommendations based on data patterns (e.g., "Restock seeds" when low, "Apply treatment" when pests detected).
  - **Visualizations:** Interactive charts for revenue vs expenses, task distribution, and labor productivity.
  - **Trend Analysis:** Tracks historical performance and identifies anomalies.

### 4. Inventory Management
- **Model:** `InventoryItem`, `InventoryTransaction`
- **Controller:** `InventoryItemController`
- **Features:**
  - **Weighted Average Cost (WAC):** Auto-recalculates unit price on restock
  - Low stock alerts and expiry tracking
  - Automatic expense creation on stock purchase

### 5. Labor & Tasks
- **Models:** `Task`, `Laborer`, `LaborerGroup`, `LaborWage`
- **Controller:** `TaskController`
- **Features:**
  - Polymorphic payment types: `wage` (daily), `piece_rate`, `share`
  - Auto-expense generation on task completion
  - Laborer group assignment

### 6. Harvest & Sales
- **Models:** `Harvest`, `Sale`
- **Controller:** `HarvestController`, `SalesController`
- **Features:**
  - Yield recording, quality grading, harvester share calculation.
  - **Marketplace Integration:** Auto-creation of sales records upon order completion.
  - **Sales Tracking:** Unified view of marketplace and off-platform sales.

### 7. Marketplace
- **Models:** `RiceProduct`, `RiceOrder`, `Cart`, `CartItem`
- **Controllers:** `RiceProductController`, `RiceOrderController`, `CartController`
- **Features:**
  - Product listing with quality grades and certifications
  - Price negotiation flow
  - Order state machine (Pending → Confirmed → Ready → Picked Up)
  - Pessimistic locking to prevent overselling

### 8. Financial Reporting
- **Models:** `Expense`, `Sale`
- **Services:** `FinancialService`
- **Features:** 
  - Income/expense tracking, profit/loss reports, crop profitability analysis.
  - **PDF/CSV Export:** Generate professional reports for records and analysis.
  - **Scheduled Reports:** Automated email summaries (Financial, Crop Yield).

---

## 🔐 Security Implementation

### Authentication
| Method | Implementation |
|--------|----------------|
| Token-based API Auth | **Laravel Sanctum** |
| Password Hashing | **Bcrypt/Argon2** (via `Hash::make`) |
| Two-Factor Verification | SMS OTP via **Twilio** |

### Authorization (RBAC)
```
┌────────────────────────────────────────────────────┐
│                   API Request                       │
│                        │                           │
│            ┌───────────┴───────────┐               │
│            ▼                       ▼               │
│   ┌────────────────┐      ┌────────────────┐       │
│   │ FarmerMiddleware│      │ BuyerMiddleware │       │
│   │ (isFarmer())   │      │ (canBuy())     │       │
│   └───────┬────────┘      └───────┬────────┘       │
│           │                        │               │
│     ┌─────┴─────┐           ┌──────┴─────┐         │
│     │  ALLOW    │           │   ALLOW    │         │
│     │  (200)    │           │   (200)    │         │
│     └───────────┘           └────────────┘         │
│                                                    │
│           │ Unauthorized                           │
│           ▼                                        │
│   ┌────────────────┐                               │
│   │  403 Forbidden │                               │
│   └────────────────┘                               │
└────────────────────────────────────────────────────┘
```

### Data Integrity
- **Input Validation:** Strict `FormRequest` classes for all endpoints.
- **SQL Injection Prevention:** Eloquent ORM with parameterized queries.
- **Transaction Safety:** `DB::transaction()` with `lockForUpdate()` for critical operations.

---

## ⚡ Caching Strategy

The system implements **Laravel Cache** to optimize performance and reduce database load on frequently accessed data.

### Cache Implementation

| Module | Cache Key Pattern | TTL | Description |
|--------|-------------------|-----|-------------|
| **Farmer Dashboard** | `farmer_dashboard_{user_id}` | 5 min | Stats, tasks, weather, marketplace data |
| **Buyer Dashboard** | `buyer_dashboard_{user_id}` | 10 min | Order stats, recent orders, products |
| **Marketplace** | `marketplace_products` | 5 min | Product listings and availability |
| **Weather Analytics** | `weather_analytics_{field_id}` | 15 min | GDD, stress analysis, recommendations |
| **Weather Data** | `weather_current_{lat}_{lon}` | 30 min | Shared regional weather cache (deduplicated) |
| **Weather Forecast** | `weather_forecast_{lat}_{lon}` | 3 hours | 5-day forecast data (deduplicated) |

### Cache Invalidation
Caches are automatically invalidated when:
- User creates/updates a planting, harvest, or task
- Inventory stock changes
- Orders are placed or status changes
- Weather data is refreshed from API

### Configuration
```php
// Default cache driver (configured in .env)
CACHE_DRIVER=file  // Options: file, redis, memcached, database
```

---

## 🌤️ Weather & Analytics Engine

### Data Source Architecture (Hybrid Approach)
The system leverages a multi-provider strategy to balance accuracy and coverage:

| Provider | Max Forecast | Role | Usage |
|----------|--------------|------|-------|
| **Open-Meteo** | 14 days | **Current Weather** | Real-time conditions, historical logging, and forecast fallback. |
| **ColorfulClouds** | 10 days | **Main Forecasts** | Primary source for extended forecasts, preferred for its range and accuracy. |
| **OpenWeatherMap** | 5 days | **Agri-Insights** | Specialized data for crop protection, alerts, and detailed agricultural metrics. |

#### Forecast Request Flow
1. Client requests forecast (up to **14 days** supported)
2. `WeatherController` routes to `ColorfulCloudsWeatherService` (primary)
3. If ColorfulClouds fails → Falls back to `WeatherService` (Open-Meteo)
4. Response is cached for 30 minutes (service-level) and 3 hours (forecast-level)

#### Verified Capabilities
- ✅ **10-day forecasts** fully supported via ColorfulClouds
- ✅ **14-day forecasts** available via Open-Meteo fallback
- ✅ Date handling uses `Carbon` with `Asia/Manila` timezone for consistency
- ✅ All forecast dates are validated against "today" before response

### Threshold Alerts (Based on Agronomic Standards)

| Condition | Trigger | Alert Type | Recommendation |
|-----------|---------|------------|----------------|
| **Cold Stress** | Temp < 15°C | High | "Consider water management to maintain soil temperature." |
| **Heat Stress** | Temp > 35°C | High | "Ensure adequate water depth (5-10cm) to cool the crop." |
| **Critical (Flowering)** | Temp > 30°C | Critical | "Maintain 5-10cm water depth; early morning irrigation." |
| **Low Humidity (Flowering)** | Humidity < 60% | Medium | "Increase water depth to maintain field humidity." |
| **Disease Risk** | Humidity > 85% | Medium | "Monitor for disease; consider preventive fungicide." |
| **Wind Advisory** | Wind > 15 km/h | High | "Monitor for lodging; consider early harvest." |
| **Flooding** | Heavy Rain/Storm | Medium | "Ensure proper drainage; check bund integrity." |

### GDD Calculation
```php
$baseTemp = 10; // Minimum for rice growth
$maxTemp = 30;  // Growth plateaus above this

$effectiveTemp = min($currentTemp, $maxTemp);
$dailyGDD = max(0, $effectiveTemp - $baseTemp);
```

### Performance Optimization
To minimize external API reliance and costs, the system implements a multi-layered optimization strategy:
1. **Deduplication:** Coordinates are rounded to 2 decimal places (~1.1km) to group nearby fields.
2. **Database Caching:** Recent `WeatherLog` entries (< 30 mins) serve as the primary data source.
3. **Service Caching:** External API responses are cached in Redis/File for 30 minutes (Current) / 3 hours (Forecast).
4. **Batch Processing:** Fields are grouped by location for bulk weather updates.
5. **Client-Side Caching:** Frontend prevents redundant requests within a 10-minute window.

---


---

## 🤖 Automation & Scheduled Jobs

The system relies on Laravel Scheduler to perform background maintenance and notifications.

| Command | Schedule | Purpose |
|---------|----------|---------|
| `inventory:check-expiry` | Daily (8:00 AM) | Checks for expiring items and notifies farmers. |
| `reports:send-scheduled` | Hourly | Checks for due scheduled reports and emails them. |

---

## ⚙️ Installation & Setup

### Prerequisites
- PHP 8.2+
- Composer 2.x
- Node.js 18+ / npm
- PostgreSQL 14.0+

### Steps

```bash
# 1. Clone the repository
git clone <repository-url>
cd farm_operation_management

# 2. Install PHP dependencies
composer install

# 3. Install Node dependencies
npm install

# 4. Environment setup
cp .env.example .env
php artisan key:generate

# 5. Configure database in .env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=farm_management
DB_USERNAME=postgres
DB_PASSWORD=your_password

# 6. Run migrations and seeders
php artisan migrate --seed

# 7. Start development servers
composer run dev
# This runs: php artisan serve & npm run dev (concurrently)
```

---

## 🔌 API Endpoints

### Authentication
| Method | Endpoint | Description |
|--------|----------|-------------|
| POST | `/api/register` | User registration |
| POST | `/api/login` | User login (returns Sanctum token) |
| POST | `/api/logout` | Revoke token |

### Farmer Routes (`/api/farmer/*`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/farms` | List user's farms |
| POST | `/farms` | Create farm |
| GET | `/fields` | List fields |
| POST | `/fields` | Create field |
| GET | `/plantings` | List plantings |
| POST | `/plantings` | Create planting |
| GET | `/harvests` | List harvests |
| POST | `/harvests` | Record harvest |
| GET | `/inventory` | List inventory items |
| POST | `/inventory` | Add inventory item |
| GET | `/tasks` | List tasks |
| POST | `/tasks` | Create task |
| POST | `/tasks/{id}/complete` | Mark task complete |

### Marketplace Routes (`/api/marketplace/*`)
| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/products` | List available products |
| POST | `/products` | Create product listing |
| GET | `/cart` | View cart |
| POST | `/cart/add` | Add to cart |
| POST | `/cart/checkout` | Place order |
| GET | `/orders` | List orders |
| POST | `/orders/{id}/accept` | Accept order (farmer) |
| POST | `/orders/{id}/ready` | Mark ready for pickup |

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter=InventoryTest
php artisan test --filter=OrderNegotiationTest
php artisan test --filter=HarvestTest
```

### Test Coverage Areas
- **AuthTest:** Registration, login, 2FA verification
- **InventoryTest:** WAC calculation, stock management
- **HarvestTest:** Yield recording, share calculation
- **OrderNegotiationTest:** Price negotiation flow
- **SystemSimulationTest:** End-to-end farmer/buyer lifecycle

---

## 📄 License

This project is developed as a capstone project for academic purposes.

---

## 👥 Contributors

- **Capstone Team** - Development
- **Advisor** - Project Guidance

---

<p align="center">
  <sub>Built with ❤️ for the farmers of Bukidnon</sub>
</p>
