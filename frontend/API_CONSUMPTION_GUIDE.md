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
