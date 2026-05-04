# Zumi Frontend: API Consumption Guide

This guide is for the frontend agent to efficiently integrate with the Zumi Backend API.

## 1. General Info
- **Base URL**: `http://localhost:8000/api/v1` (Update to production URL in `.env`)
- **Headers**: Always send `Accept: application/json` and `Content-Type: application/json`.
- **Authentication**: Uses Laravel Sanctum. After login/register, store the `token` and send it as `Authorization: Bearer {token}`.

---

## 2. Global State & Data Types
- **UUIDs**: All IDs (User, Wave, Circle) are UUID strings. Do not expect incremental integers.
- **Enums**: Pay close attention to Enums for `UserRole`, `WaveStatus`, `WaveVisibility`, and `CircleType`.
- **Currency**: `Drops` are integers. Ensure the UI handles balance updates reactively.

---

## 3. Core Modules & Integration Tips

### 🔑 Authentication
- **Login/Register**: Returns a `user` object and a `token`.
- **Onboarding**: Check the `onboarding_completed` flag on the User object. If `false`, redirect to `/onboarding`.

### 🎥 Waves (Video)
- **Uploading**:
  1. Call `POST /waves/initialize-upload` with `size_bytes`.
  2. The API returns a Cloudflare Stream upload URL.
  3. Upload the file directly from the frontend to Cloudflare.
  4. Once done, call `POST /waves` with the `cloudflare_id`.
- **Gated Content**: If `visibility === 'gated'`, show a "Unlock for X Drops" button. Only allow viewing if `is_purchased` is true (returned in the resource).

### 💰 Wallet & Payouts
- **Balance**: Poll `/wallet` or use WebSockets for real-time balance updates after purchases.
- **Stripe Connect**: Before a user can withdraw, they must onboard via Stripe. Use `POST /payouts/onboard` to get the redirect URL.

### 🌐 Circles
- **Private Circles**: If `type === 'private'`, the "Join" button should trigger a request. The UI should show "Pending" until an admin approves.

---

## 4. Error Handling
The API returns standard HTTP status codes:
- `422 Unprocessable Content`: Validation errors. Show the `errors` object to the user.
- `402 Payment Required`: User has insufficient Drops.
- `403 Forbidden`: User is banned or lacks permissions for that action.

---

## 5. Development Utilities
- **Postman**: A full collection is available at the project root: `Zumi_API_Collection.json`. Import this into Postman to see example payloads for all 80+ endpoints.
- **Testing**: Use the `test@zumi.app` / `password` credentials for initial testing.

---

## 🚀 Key Endpoints Summary
| Feature | Endpoint | Method |
| :--- | :--- | :--- |
| Discovery Feed | `/waves` | GET |
| Wallet | `/wallet` | GET |
| Profile | `/users/me` | PATCH |
| Live Rooms | `/rooms` | POST |
| Payouts | `/payouts/withdraw` | POST |
