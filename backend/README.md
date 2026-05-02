# Zumi Backend API

The core API and administrative dashboard for the Zumi platform, built with Laravel 11.

## Tech Stack
- **Framework**: [Laravel 11](https://laravel.com/)
- **PHP Version**: 8.4+
- **Database**: PostgreSQL (Production), SQLite (Local)
- **Monitoring**: 
  - [Laravel Horizon](https://laravel.com/docs/horizon) (Queue Management)
  - [Laravel Pulse](https://laravel.com/docs/pulse) (Health Monitoring)
  - [Laravel Telescope](https://laravel.com/docs/telescope) (Debug Assistant)
- **Admin Panel**: [Filament v3](https://filamentphp.com/)

## Key Features Implemented
- **Monetary System**: Drops ledger (immutable transaction history).
- **ACL**: Role-based access control via Spatie Permission.
- **Media**: Robust file handling via Spatie MediaLibrary.
- **Search**: Algolia/Meilisearch integration via Laravel Scout.
- **Billing**: Stripe integration via Laravel Cashier.

## Getting Started

### Prerequisites
- PHP 8.4+
- Composer
- SQLite (for local dev) or PostgreSQL

### Installation
1. Clone the repository and navigate to the backend:
   ```bash
   cd zumi/backend
   ```
2. Install dependencies:
   ```bash
   composer install
   ```
3. Set up environment:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
4. Run migrations:
   ```bash
   php artisan migrate
   ```
5. Create an admin user for Filament:
   ```bash
   php artisan make:filament-user
   ```

### Running Locally
Start the development server:
```bash
php artisan serve
```

## Architecture
This backend follows a **Domain-Driven** architecture:
- `app/Domain`: Core domain logic and business rules.
- `app/Services`: Business logic orchestration.
- `app/Repositories`: Data access abstraction.
- `app/Enums`: Single source of truth for fixed values.
