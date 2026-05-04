# Zumi API: Complete Reference & Integration Guide

This document is the source of truth for the Zumi Backend API v1.0.

---

## 1. Core Architecture
- **Base URL**: `http://localhost:8000/api/v1`
- **Global Metadata**: **Every single API response** includes a `meta` block (see section 2).
- **Authentication**: Bearer Token (Sanctum).
- **Onboarding**: All users must complete `POST /user/onboarding` before full access is granted.

---

## 2. Global Response Structure
Regardless of the endpoint, the response will always contain a `meta` object.
```json
{
    "data": { ... },
    "meta": {
        "api_version": "1.0.0",
        "timestamp": "2026-05-04T19:20:00Z",
        "request_id": "uuid-v4",
        "seo": {
            "title": "Page Title | Zumi",
            "description": "...",
            "image": "...",
            "type": "..."
        }
    }
}
```
*Note: SEO sub-object is present for entities like Users, Waves, and Circles.*

---

## 3. Auth & Onboarding Flow

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

### **POST** `/user/onboarding`
**Req:** `{ "interests": ["tech"], "follows": ["uuid-1"] }`
**Res:** `200 OK`
```json
{
    "message": "Onboarding completed",
    "user": { "id": "...", "onboarding_completed": true },
    "meta": { "api_version": "1.0.0" }
}
```

---

## 4. Waves & Content

### **GET** `/waves` (Feed)
**Res:** `200 OK`
```json
{
    "data": [{ "id": "...", "title": "Great Video", "is_liked": false }],
    "meta": { "api_version": "1.0.0", "total": 150 }
}
```

### **POST** `/waves`
**Req:** `{ "title": "Sunset", "cloudflare_id": "cf-uid", "visibility": "gated", "gated_drops": 50 }`
**Res:** `201 Created`
```json
{
    "wave": { "id": "wave-uuid", "title": "Sunset" },
    "meta": { "api_version": "1.0.0", "seo": { "title": "Sunset | Zumi", "image": "..." } }
}
```

---

## 5. Circles (Communities)

### **POST** `/circles`
**Req:** `{ "name": "Design Pros", "type": "public" }`
**Res:** `201 Created`
```json
{
    "id": "circle-uuid",
    "name": "Design Pros",
    "meta": { "api_version": "1.0.0", "seo": { "title": "Design Pros | Zumi" } }
}
```

### **POST** `/circles/{id}/messages`
**Req:** `{ "content": "Hello!" }`
**Res:** `201 Created`
```json
{
    "message": { "id": "msg-uuid", "content": "Hello!" },
    "meta": { "api_version": "1.0.0" }
}
```

---

## 6. Drops & Wallet

### **GET** `/wallet`
**Res:** `200 OK`
```json
{
    "balance": 1000,
    "transactions": [{ "id": "...", "amount": 100, "direction": "debit" }],
    "meta": { "api_version": "1.0.0" }
}
```

### **POST** `/drops/gift`
**Req:** `{ "receiver_id": "uuid", "amount": 100 }`
**Res:** `200 OK`
```json
{
    "success": true,
    "new_balance": 900,
    "meta": { "api_version": "1.0.0" }
}
```

---

## 7. Challenges & Skill Drops

### **POST** `/challenges`
**Req:** `{ "title": "Dance", "prize_pool": 1000, "ends_at": "2026-12-31" }`
**Res:** `201 Created`
```json
{
    "id": "challenge-uuid",
    "title": "Dance",
    "meta": { "api_version": "1.0.0" }
}
```

### **POST** `/skill-drops`
**Req:** `{ "title": "PDF Guide", "price_drops": 500, "content_url": "..." }`
**Res:** `201 Created`
```json
{
    "id": "skill-drop-uuid",
    "title": "PDF Guide",
    "meta": { "api_version": "1.0.0" }
}
```

---

## 8. Gated Rooms & Live

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

---

## 9. Payouts (Creator Withdrawals)

### **POST** `/payouts/onboard`
**Res:** `200 OK`
```json
{
    "url": "https://connect.stripe.com/...",
    "meta": { "api_version": "1.0.0" }
}
```

### **POST** `/payouts/withdraw`
**Req:** `{ "amount": 5000 }`
**Res:** `200 OK`
```json
{
    "message": "Withdrawal processed",
    "transaction_id": "...",
    "meta": { "api_version": "1.0.0" }
}
```

---

## 10. Search & Moderation

### **GET** `/search?q=test`
**Res:** `200 OK`
```json
{
    "users": [], "waves": [], "circles": [],
    "meta": { "api_version": "1.0.0" }
}
```

### **POST** `/reports`
**Req:** `{ "reportable_type": "wave", "reportable_id": "...", "reason": "spam" }`
**Res:** `201 Created`
```json
{
    "message": "Report submitted",
    "meta": { "api_version": "1.0.0" }
}
```
