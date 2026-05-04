# Zumi API: Complete Reference & Onboarding Guide

This document provides an exhaustive guide for the frontend to consume the Zumi API.

---

## 1. General Concepts
- **Base URL**: `http://localhost:8000/api/v1`
- **Headers**: `Accept: application/json`, `Content-Type: application/json`
- **UUIDs**: All public IDs are UUID strings.
- **Currency**: `Drops` are integers.
- **Global Metadata**: **Every response** (single or list) now contains a `meta` object with `api_version`, `timestamp`, and `request_id`. Entities also include an `seo` sub-object within `meta` for easy head-tag management.

---

## 2. Global Response Format
All responses follow this standard structure:
```json
{
    "data": { ... },
    "meta": {
        "api_version": "1.0.0",
        "timestamp": "2026-05-04T19:20:00Z",
        "request_id": "uuid-v4",
        "seo": {
            "title": "Example Title | Zumi",
            "description": "...",
            "image": "...",
            "type": "..."
        }
    }
}
```
*Note: For non-entity responses (like "Message sent"), the `seo` block may be absent.*

---

## 3. Authentication & Identity Flow

Zumi uses **Laravel Sanctum** for token-based authentication.

### Standard Login/Register
1.  **Register** (`POST /auth/register`): Creates a user. Returns a `token` and `user` object.
2.  **Login** (`POST /auth/login`): Validates credentials. Returns a `token` and `user` object.
3.  **Logout** (`POST /auth/logout`): Revokes the current token.

### Onboarding Flow (CRITICAL)
Every user has an `onboarding_completed` (boolean) flag.
1.  **Check Status**: After login, check `user.onboarding_completed`.
2.  **Redirection**: If `false`, the frontend MUST show the onboarding screen.
3.  **Completion** (`POST /user/onboarding`): 
    *   **Body**: `{ "interests": ["tech", "music"], "follows": ["uuid1", "uuid2"] }`
    *   **Result**: Updates interests, follows selected creators, and sets `onboarding_completed = true`.

---

## 4. Comprehensive Endpoint Reference

### 👤 Profile & User Settings
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/user` | Get current authenticated user details. |
| `PATCH` | `/users/me` | Update name, username, bio, or location. |
| `POST` | `/user/avatar` | Upload profile picture (Multipart/Form-Data). |
| `PATCH` | `/users/me/notifications` | Update opt-in settings for push/email. |
| `POST` | `/user/fcm-token` | Register/Update the FCM token. |

### 🎥 Waves (Content)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/waves` | Discovery Feed. Includes `is_liked`, `is_bookmarked`. |
| `GET` | `/waves/followed` | Feed of creators the user follows. |
| `POST` | `/waves/initialize-upload` | Get Cloudflare upload URL. |
| `POST` | `/waves` | Create Wave with `cloudflare_id`. |
| `POST` | `/waves/{id}/purchase` | Purchase access to a **Gated Wave**. |

### ⭕ Circles (Communities)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/circles` | List discovery circles. |
| `POST` | `/circles` | Create a circle (`public` or `private`). |
| `POST` | `/circles/{id}/join` | Join a circle. |
| `POST` | `/circles/{id}/messages` | Send a message to the circle chat. |

### 💰 Wallet & Drops
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/wallet` | Get balance and transaction history. |
| `POST` | `/drops/gift` | Send Drops to another user. |

---

## 5. JSON Payload & Response Examples

### **POST** `/auth/register`
**Req:** `{ "name": "John", "username": "johndoe", "email": "john@zumi.app", "password": "password" }`
**Res:** `201 Created`
```json
{
    "user": { "id": "...", "name": "John", "onboarding_completed": false },
    "token": "1|...",
    "meta": { "api_version": "1.0.0", "timestamp": "..." }
}
```

### **POST** `/waves`
**Req:** `{ "title": "Sunset", "cloudflare_id": "cf-uid", "visibility": "gated", "gated_drops": 50 }`
**Res:** `201 Created`
```json
{
    "message": "Wave created successfully",
    "wave": { "id": "wave-uuid", "title": "Sunset" },
    "meta": {
        "api_version": "1.0.0",
        "seo": { "title": "Sunset | Zumi", "image": "...", "type": "video.other" }
    }
}
```

### **POST** `/rooms`
**Req:** `{ "title": "Live Jam", "entry_fee_drops": 100 }`
**Res:** `201 Created`
```json
{
    "id": "room-uuid",
    "title": "Live Jam",
    "live_stream_url": "...",
    "meta": { "api_version": "1.0.0" }
}
```
