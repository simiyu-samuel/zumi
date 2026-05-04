# Zumi API: Complete Reference & Onboarding Guide

This document provides a exhaustive guide for the frontend to consume the Zumi API.

---

## 1. Authentication & Identity Flow

Zumi uses **Laravel Sanctum** for token-based authentication.

### Standard Login/Register
1.  **Register** (`POST /auth/register`): Creates a user. Returns a `token` and `user` object.
2.  **Login** (`POST /auth/login`): Validates credentials. Returns a `token` and `user` object.
3.  **Logout** (`POST /auth/logout`): Revokes the current token.

### Social Authentication (OAuth)
1.  **Redirect** (`GET /auth/{provider}/redirect`): Frontend should redirect the user to this URL.
2.  **Callback** (`GET /auth/{provider}/callback`): After provider login, Zumi redirects back here. This endpoint handles the user creation/login and returns the token in a way the frontend can capture (usually via a redirect with a query param or a cookie).

### Onboarding Flow (CRITICAL)
Every user has an `onboarding_completed` (boolean) flag.
1.  **Check Status**: After login, check `user.onboarding_completed`.
2.  **Redirection**: If `false`, the frontend MUST show the onboarding screen (Interests selection & Suggested follows).
3.  **Completion** (`POST /user/onboarding`): 
    *   **Body**: `{ "interests": ["tech", "music"], "follows": ["uuid1", "uuid2"] }`
    *   **Result**: This atomic call updates the user's interests, follows the selected creators, and sets `onboarding_completed = true`.

---

## 2. Comprehensive Endpoint Reference

### 👤 Profile & User Settings
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/user` | Get current authenticated user details. |
| `PATCH` | `/users/me` | Update name, username, bio, or location. |
| `POST` | `/user/avatar` | Upload profile picture (Multipart/Form-Data). |
| `POST` | `/user/banner` | Upload profile banner (Multipart/Form-Data). |
| `PATCH` | `/users/me/notifications` | Update opt-in settings for push/email. |
| `POST` | `/user/fcm-token` | Register/Update the Firebase Cloud Messaging token for push notifications. |
| `GET` | `/users/{username}` | Get public profile of another user (includes followers count, etc.). |

### 🎥 Waves (Content)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/waves` | Discovery Feed (Paginated). Includes `is_liked`, `is_bookmarked`. |
| `GET` | `/waves/followed` | Feed of creators the user follows. |
| `GET` | `/waves/{id}` | Single Wave details. |
| `POST` | `/waves/initialize-upload` | Returns a Cloudflare Stream upload URL. Requires `size_bytes`. |
| `POST` | `/waves` | Finalize Wave creation with `cloudflare_id`, `title`, `visibility`. |
| `POST` | `/waves/{id}/like` | Toggle like status. |
| `POST` | `/waves/{id}/bookmark` | Toggle bookmark status. |
| `GET` | `/waves/bookmarks` | List user's bookmarked waves. |
| `POST` | `/waves/{id}/purchase` | Purchase access to a **Gated Wave** using Drops. |
| `POST` | `/waves/{id}/view` | Record a view (analytics). |
| `POST` | `/waves/{id}/share` | Record a share (analytics). |

### 💬 Comments
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/waves/{wave}/comments` | List comments for a wave (nested replies supported). |
| `POST` | `/waves/{wave}/comments` | Post a new comment. Supports `@mentions`. |
| `DELETE` | `/comments/{id}` | Delete own comment. |

### ⭕ Circles (Communities)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/circles` | List discovery circles. |
| `GET` | `/circles/my` | List circles the user owns or has joined. |
| `POST` | `/circles` | Create a circle (`public` or `private`). |
| `POST` | `/circles/{id}/join` | Join a circle. If private, creates a `pending` request. |
| `GET` | `/circles/{id}/requests` | (Owner Only) List pending join requests. |
| `POST` | `/circles/requests/{id}/approve`| (Owner Only) Approve a request. |
| `GET` | `/circles/{id}/messages` | Get circle chat history. |
| `POST` | `/circles/{id}/messages` | Send a message to the circle chat. |

### 💰 Wallet & Drops
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/wallet` | Get balance and full transaction ledger. |
| `POST` | `/drops/gift` | Send Drops directly to another user. |
| `POST` | `/drops/purchase` | Initiate a Stripe Checkout session to buy Drops. |

### 🏆 Challenges & Skill Drops
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/challenges` | Create a challenge with a Drops prize pool. |
| `POST` | `/challenges/{id}/join` | Submit a Wave to a challenge. |
| `POST` | `/challenges/{id}/vote` | Vote for a submission. |
| `POST` | `/skill-drops` | (Creators) Create a digital product for sale. |
| `POST` | `/skill-drops/{id}/purchase`| Buy a digital product. Unlocks the `content_url`. |
| `GET` | `/skill-drops/library` | List all digital products owned by the user. |

### 📺 Gated Rooms (Live)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/rooms` | Create a live gated room with an entry fee. |
| `POST` | `/rooms/{id}/join` | Pay entry fee and get access to the live stream. |
| `POST` | `/rooms/{id}/start` | Mark room as Live. |

### 🏦 Payouts (Creator Earnings)
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/payouts/onboard` | Get a Stripe Connect onboarding link. |
| `POST` | `/payouts/withdraw` | Convert Drops back to fiat and withdraw to bank. |

### 🔍 Search
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/search` | Global search (Users, Waves, Circles). |
| `GET` | `/search/users` | Specific user search with pagination. |

### ⚖️ Trust & Safety
| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `POST` | `/reports` | Flag content or users for moderation. |

---

## 3. Important Status Codes & Errors
- **`402 Payment Required`**: Returned when a user tries to purchase something (Gated Wave, Skill Drop, Room) but has insufficient Drops. The frontend should trigger the "Top-up Wallet" flow.
- **`403 Forbidden`**: Returned if the user is **Banned** (Check `user.status === 'banned'`) or tries to access content without purchase.
- **`422 Unprocessable Content`**: Standard validation error. `response.data.errors` contains field-specific messages.
# Zumi API: Full Payload & Response Reference

This document provides exact JSON examples for Request Payloads and Response Objects for all Zumi API v1 endpoints.

---

## 1. Authentication

### **POST** `/auth/register`
**Request Payload:**
```json
{
    "name": "John Doe",
    "username": "johndoe",
    "email": "john@zumi.app",
    "password": "securepassword",
    "password_confirmation": "securepassword"
}
```
**Response (201 Created):**
```json
{
    "user": {
        "id": "550e8400-e29b-41d4-a716-446655440000",
        "name": "John Doe",
        "username": "johndoe",
        "email": "john@zumi.app",
        "onboarding_completed": false,
        "drops_balance": 0,
        "role": "user",
        "status": "active"
    },
    "token": "1|abcdef123456..."
}
```

### **POST** `/auth/login`
**Request Payload:**
```json
{
    "email": "john@zumi.app",
    "password": "securepassword"
}
```
**Response (200 OK):**
```json
{
    "user": { "id": "...", "name": "...", "onboarding_completed": false },
    "token": "2|ghijk7891011..."
}
```

---

## 2. Onboarding

### **POST** `/user/onboarding`
**Request Payload:**
```json
{
    "interests": ["tech", "music", "dance"],
    "follows": ["550e8400-e29b-41d4-a716-446655440001", "550e8400-e29b-41d4-a716-446655440002"]
}
```
**Response (200 OK):**
```json
{
    "message": "Onboarding completed successfully",
    "user": {
        "id": "...",
        "onboarding_completed": true,
        "interests": ["tech", "music", "dance"]
    }
}
```

---

## 3. Waves (Content)

### **GET** `/waves` (Feed)
**Response (200 OK - Paginated):**
```json
{
    "data": [
        {
            "id": "wave-uuid-123",
            "title": "My first Wave",
            "description": "Check this out!",
            "thumbnail_url": "https://...",
            "stream_id": "cf-stream-id",
            "visibility": "public",
            "likes_count": 12,
            "comments_count": 5,
            "is_liked": true,
            "is_unlocked": true,
            "user": { "id": "user-uuid", "name": "Creator Name" }
        }
    ],
    "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
    "meta": { "current_page": 1, "from": 1, "last_page": 10, "total": 150 }
}
```

### **POST** `/waves/initialize-upload`
**Request Payload:**
```json
{
    "size_bytes": 5242880,
    "title": "Sunset Video"
}
```
**Response (200 OK):**
```json
{
    "uploadURL": "https://upload.videodelivery.net/...",
    "uid": "cf-stream-uid-123"
}
```

### **POST** `/waves`
**Request Payload:**
```json
{
    "title": "Sunset Video",
    "description": "Beautiful evening",
    "cloudflare_id": "cf-stream-uid-123",
    "visibility": "gated",
    "gated_drops": 50
}
```
**Response (201 Created):**
```json
{
    "message": "Wave created successfully",
    "wave": { "id": "wave-uuid", "title": "Sunset Video", "status": "pending" }
}
```

---

## 4. Wallet & Drops

### **GET** `/wallet`
**Response (200 OK):**
```json
{
    "balance": 1250,
    "transactions": [
        {
            "id": "ledger-uuid",
            "type": "wave_gift",
            "amount": 100,
            "direction": "debit",
            "status": "completed",
            "created_at": "2026-05-04T12:00:00Z",
            "metadata": { "receiver_id": "user-uuid", "title": "Great video!" }
        }
    ]
}
```

### **POST** `/drops/gift`
**Request Payload:**
```json
{
    "receiver_id": "user-uuid-abc",
    "amount": 100
}
```
**Response (200 OK):**
```json
{
    "success": true,
    "message": "100 Drops gifted successfully",
    "new_balance": 1150
}
```

---

## 5. Circles

### **POST** `/circles`
**Request Payload:**
```json
{
    "name": "Design Pros",
    "description": "Exclusive design community",
    "type": "private",
    "monthly_drops_price": 500
}
```
**Response (201 Created):**
```json
{
    "id": "circle-uuid",
    "name": "Design Pros",
    "slug": "design-pros",
    "type": "private",
    "owner_id": "your-uuid"
}
```

---

## 6. Payouts

### **POST** `/payouts/withdraw`
**Request Payload:**
```json
{
    "amount": 5000
}
```
**Response (200 OK):**
```json
{
    "message": "Withdrawal processed successfully",
    "transaction_id": "stripe-transfer-id-xyz",
    "amount_withdrawn": 5000,
    "fee_deducted": 250,
    "net_payout": 4750
}
```

---

## 7. Error Responses

### **422 Unprocessable Content** (Validation)
```json
{
    "message": "The given data was invalid.",
    "errors": {
        "username": ["The username has already been taken."],
        "email": ["The email must be a valid email address."]
    }
}
```

### **402 Payment Required** (Insufficient Drops)
```json
{
    "message": "Insufficient Drops balance."
}
```
