# Zumi Backend API (v1.0 MVP)

The core API and administrative dashboard for the Zumi platform, built with Laravel 11.

## 🚀 Key Features
- **Monetary System**: Drops ledger (immutable transaction history).
- **Social Engine**: Waves (video), Circles (communities), and Nested Comments.
- **Monetization**: Gated content, Skill Drops, and Premium Subscriptions (Stripe).
- **Security**: Rate limiting, UUIDs, and 100% Policy-based authorization.
- **Trust & Safety**: Integrated reporting and automated moderation.
- **Live Interaction**: Gated Live Rooms with entry fees.

## 🛠 Tech Stack
- **Framework**: [Laravel 11](https://laravel.com/)
- **Database**: PostgreSQL 15+ (Required)
- **Real-time**: Laravel Reverb
- **Search**: Meilisearch / Algolia
- **Queue**: Laravel Horizon
- **Monitoring**: Laravel Pulse & Telescope

## 📦 Getting Started

### Installation
1.  **Dependencies**: `composer install`
2.  **Environment**: `cp .env.example .env` (Configure DB_DATABASE and STRIPE keys)
3.  **Migration**: `php artisan migrate --seed`
4.  **Admin**: `php artisan make:filament-user`

### Running
```bash
php artisan serve
php artisan horizon
```

## 🧪 Testing
The backend is protected by a comprehensive suite of 83+ feature tests.
```bash
php artisan test
```

## 📄 API Documentation
A full Postman collection is available in the root directory: `Zumi_API_Collection.json`.
For a detailed technical breakdown, see `BACKEND_DOCUMENTATION.md`.

---
**Build with ❤️ for the Creator Economy.**
