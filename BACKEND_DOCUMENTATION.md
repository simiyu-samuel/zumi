# Zumi Backend Documentation (v1.0 MVP)

## 1. Overview
Zumi is a content-driven social platform focused on creator monetization through "Drops" (a virtual currency). This backend serves as the core API and administrative hub.

### Core Philosophy
- **Thin Controllers, Fat Services**: Business logic is encapsulated in Domain Services.
- **Repository Pattern**: Data access is abstracted to ensure consistency and testability.
- **UUIDs**: All public-facing IDs are version 4 UUIDs for security and decentralization.
- **Enums as Truth**: Statuses, Roles, and Types are strictly typed using PHP 8 Enums.

---

## 2. Technical Stack
- **Framework**: Laravel 11.x
- **Database**: PostgreSQL (v15+)
- **Cache/Queue**: Redis 7.x
- **Real-time**: Laravel Reverb / WebSockets
- **Search**: Meilisearch (via Laravel Scout)
- **Payments**: Stripe (via Laravel Cashier & Connect)
- **Media**: Cloudflare Stream (Video), Spatie MediaLibrary (Images/Files)

---

## 3. Database & Relationships
The database is designed for high-concurrency social interactions:
- **Users**: Core entity with `drops_balance` and `flow_score`.
- **Waves**: Short-form video content. Can be `Public`, `Followers-Only`, or `Gated` (Drops-purchase).
- **Circles**: Communities. Can be `Public` or `Private` (Request-based). Supports chat and exclusive feeds.
- **Drops Ledger**: A table tracking every single credit/debit of virtual currency. It is **immutable** (entries are never deleted or updated).
- **Social Graph**: `followers` table managing user-to-user relationships.
- **Monetization**: `WavePurchases`, `SkillDropPurchases`, and `CircleSubscriptions`.

---

## 4. Security & Hardening
- **Rate Limiting**: 
    - `api`: 60 requests/min per user.
    - `auth`: 10 requests/min per IP.
- **Financial Safety**: All Drops transfers use `DB::transaction()` and pessimistic locking where applicable.
- **Content Moderation**: Polymorphic reporting system with `ModerationService` for automated Banning/Content Removal.
- **Authorization**: 100% Policy coverage. Every action (view/edit/delete) checks against a model Policy.

---

## 5. API Modules
- **Auth**: Sanctum-based token auth with Socialite (Google/Apple) support.
- **Discovery**: Weighted feed logic based on Flow Score and recency.
- **Wallet**: Real-time balance and transaction history auditing.
- **Live**: Gated Rooms for real-time interaction with entry fees.

---

## 6. Future Roadmap (v2.0)
The following features are planned for the next iteration:
1.  **AI Moderation**: Integration with OpenAI/Google Vision for automated image/video NSFW detection.
2.  **Referral System**: Drops rewards for inviting new users who purchase subscriptions.
3.  **Ad Engine**: Native ad placements in the Discovery feed based on user interests.
4.  **Multi-currency Support**: Direct fiat-to-content purchases (bypassing Drops if preferred).
5.  **Offline Support**: Syncing likes/bookmarks when connectivity is restored.
6.  **Analytics Dashboard**: Advanced creator insights (Watch time, Retention, Conversion rates).

---

## 7. Operational Commands
- **Testing**: `php artisan test` (PostgreSQL required).
- **Monitoring**: Visit `/pulse` for real-time system health.
- **Admin**: Visit `/admin` for the Filament dashboard.
- **Queues**: `php artisan horizon` to start the worker.
