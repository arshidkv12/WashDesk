# 🧺 WashDesk - Simple Laundry Management System

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![Inertia](https://img.shields.io/badge/Inertia-9553E9?style=for-the-badge&logo=inertia&logoColor=white)](https://inertiajs.com)
[![Svelte](https://img.shields.io/badge/Svelte-FF3E00?style=for-the-badge&logo=svelte&logoColor=white)](https://svelte.dev)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)](https://tailwindcss.com)
[![License: MIT](https://img.shields.io/badge/License-MIT-yellow.svg)](https://opensource.org/licenses/MIT)

A modern, lightweight laundry management system built with Laravel, Inertia.js, and Svelte. Perfect for small to medium-sized laundry businesses seeking a reactive, seamless single-page application experience without the complexity of a full SPA setup.


## ✨ Features

### 📊 Order Management
- Create and track laundry orders with unique order IDs
- Real-time order status updates (Pending, Processing, Ready, Delivered, Collected)
- Service type management (Wash, Dry Clean, Iron, Fold, Stain Removal)
- Weight-based and item-based pricing models
- Express service upcharge options

### 💰 Billing & Payments
- Automatic invoice generation with PDF export
- Configurable tax rates (GST/VAT/Sales Tax)
- Multiple payment methods (Cash, Card, Mobile Money, Bank Transfer)
- Partial payment support
- Discount codes and loyalty discounts
- Digital receipt delivery via SMS/Email

### 👥 Customer Management
- Comprehensive customer profiles with contact details
- Complete order history and preferences
- Loyalty points system with automated rewards
- Customer notes and special instructions
- Birthday/anniversary promotions
- Customer communication log

### 📈 Reports & Analytics
- Interactive revenue charts and graphs
- Daily/weekly/monthly/yearly performance reports
- Service popularity analytics
- Customer retention and churn metrics
- Peak hour/service analysis
- Export reports to PDF/CSV

### 🔧 Admin Dashboard
- Role-based access control (Admin, Manager, Staff)
- Activity logging and audit trails
- Inventory management (detergents, supplies)
- Staff performance tracking
- System settings and configurations

## 🚀 Tech Stack

**Backend:**
- [Laravel 11.x](https://laravel.com) - PHP framework
- [MySQL](https://www.mysql.com) / [PostgreSQL](https://www.postgresql.org) - Database
- [Laravel Sanctum](https://laravel.com/docs/sanctum) - Authentication
- [Laravel Excel](https://laravel-excel.com) - Import/Export

**Frontend:**
- [Svelte](https://svelte.dev) - Reactive UI components
- [Inertia.js](https://inertiajs.com) - Monolithic SPA bridge
- [Tailwind CSS](https://tailwindcss.com) - Utility-first styling
- [Svelte Headless UI](https://svelte-headlessui.goss.io) - Accessible components
- [Chart.js](https://www.chartjs.org) / [Svelte Charts](https://svelte-charts.vercel.app) - Data visualization

## 📦 Installation

### Prerequisites
- PHP 8.2+
- Composer 2.x
- Node.js 18+
- NPM or Yarn
- MySQL 8.0+ / PostgreSQL 14+
- Redis (optional, for caching)

### Step-by-Step Setup

1. **Clone the repository**
   ```bash
   git clone https://github.com/yourusername/laundryflow.git
   cd laundryflow