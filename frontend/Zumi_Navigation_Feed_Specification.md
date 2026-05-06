# Zumi — Navigation Architecture & Feed Specification

**Version 1.0 — Frontend Reference Document**  
Covers: Desktop Sidebar | Mobile Bottom Nav | Feed Content | Missing Pieces | Decisions  

*Confidential — Internal Use Only*

---

## Table of Contents

1. [Executive Summary & Key Decisions](#1-executive-summary--key-decisions)
2. [The Home Feed — What It Shows & Why](#2-the-home-feed--what-it-shows--why)
3. [Desktop Navigation — Sidebar](#3-desktop-navigation--sidebar)
4. [Mobile Navigation — Bottom Tab Bar](#4-mobile-navigation--bottom-tab-bar)
5. [Individual Page Specifications](#5-individual-page-specifications)
6. [Missing Pieces — Complete Gap Analysis](#6-missing-pieces--complete-gap-analysis)
7. [Official Navigation Naming Glossary](#7-official-navigation-naming-glossary)
8. [Implementation Checklist](#8-implementation-checklist)

---

## 1. Executive Summary & Key Decisions

This document defines the complete navigation architecture for Zumi across both desktop and mobile, specifies the exact content and logic of the Home Feed, identifies gaps in the current implementation, and provides clear direction on every open question. It supersedes any informal decisions and should be treated as the **single source of truth** for frontend architecture.

---

### 1.1 Screenshot Analysis — What Is Right & What Needs Changing

The current `/feed` implementation shows a strong foundation. The layout direction is correct — left sidebar, central feed, right contextual panel. Several details need to be refined based on this document.

#### ✅ What is correct in the current screenshot

- Left sidebar navigation with Home, Explore, Circles & Rooms, Notifications, Drops Wallet, Profile
- For You / Following tab switcher at the top of the feed
- Live Gated Rooms row with entry price (Drops) shown — this is a unique Zumi differentiator, **keep it**
- Right panel with Suggested Creators and Top Gated Rooms
- User balance card at the bottom of the sidebar
- User profile row at the very bottom of the sidebar with name and handle

#### ⚠️ What needs to change

- The "Circles & Rooms" nav item should be split: **Circles** and **Waves** should be separate nav items (see Section 3)
- A dedicated **"Create" button** is missing from the sidebar and mobile nav — this is critical
- "Explore" should be renamed to **"Discover"** to align with Zumi brand language
- **"Waves"** (short video) needs its own top-level nav tab on both desktop and mobile
- **Flow Market** is missing from navigation entirely — needs a nav item (see Section 4)
- The notification badge currently polls every 30 seconds — upgrade to WebSocket push (Reverb) in V1
- Profile power menu needs **Creator Studio / Dashboard** added as a menu item

---

## 2. The Home Feed — What It Shows & Why

### 2.1 Default Landing Screen Decision

> **Decision: The Home Feed (Social Feed) is the default screen after login and after onboarding completion. Zumi Waves (short video) is NOT the default — it is a dedicated tab.**

**Rationale:**

- Waves require a following graph to be meaningful. A brand new user who just completed onboarding has followed 3–5 people — there are not enough Waves to fill an infinite swipe feed without resorting to entirely algorithmic random content, which feels hollow.
- The Social Feed can blend followed content with algorithmic suggestions, pinned onboarding prompts ("Find more people to follow"), and trending posts — creating a richer, fuller first experience.
- This mirrors how Instagram, YouTube, and TikTok handle first-login — they drop you into a feed, not directly into Reels/Shorts, because the feed can be padded with non-video content immediately.
- Waves should feel like a reward — a dedicated destination you go to, not the first thing you are forced into.

---

### 2.2 Feed Tabs

| Tab | Content & Logic |
|---|---|
| **For You** | Algorithmic feed. Blends: posts from followed users, Wave previews, trending Skill Drops, Circle announcements, and recommended creators. Personalised by interests set during onboarding. **This is the default tab.** |
| **Following** | Chronological feed. Only content from users the viewer explicitly follows. No algorithmic injection. Ordered by recency (newest first). For power users who want control over their feed. |

---

### 2.3 Home Feed — Content Card Types

The feed renders a mixed stream of the following card types. The algorithm weights and orders them:

| Card Type | Description | API Source |
|---|---|---|
| **Live Room Banner** | Full-width card at the top. Shows active Gated Rooms from followed creators. Entry price in Drops shown. "Join now" CTA. Dismiss-able. Max 2 shown. | `GET /rooms?status=live&following=true` |
| **Wave Preview Card** | Inline video thumbnail that auto-plays muted on scroll. Shows creator name, caption, like/comment count, and a "Gift Drops" button. Tapping opens full Waves player. | `GET /waves/feed` |
| **Text Post** | Standard social post. Text up to 2,000 chars. Like, comment, share, tip Drops inline. Shows hashtags, mentions. | `GET /feed` |
| **Image Post** | Post with up to 10 images in a carousel. Same interaction layer as text post. | `GET /feed` |
| **Poll** | 2–6 option interactive poll. Shows live results after voting. Duration badge (e.g. "2 days left"). | `GET /feed` |
| **Skill Drop Promo** | Creator announces a new Skill Drop. Card shows preview image, title, price in Drops, one-tap purchase. Only shown if creator is followed or algorithmically recommended. | `GET /skill-drops/feed` |
| **Circle Announcement** | Creator announces a new Circle event, new member milestone, or exclusive content drop. Shows Circle name, member count. | `GET /circles/announcements` |
| **Wave Challenge** | Active challenge card. Shows creator, prompt, prize pool in Drops, submission count, time remaining. "Enter Challenge" CTA. | `GET /challenges?status=active` |
| **Suggested Creators** | A row of 3–5 creator cards with Follow buttons. Injected once every ~15 cards in the For You feed. Not shown in Following feed. | `GET /users/suggested` |

---

### 2.4 Live Gated Rooms Row (Top of Feed)

The horizontal scrollable row of live rooms at the top of the feed is a **correct implementation and should be kept**. This is one of Zumi's most distinctive visual elements — no other mainstream platform shows monetised live rooms this prominently in the feed.

- Shows rooms from followed creators first, then algorithmically suggested rooms
- Each room card shows: host avatar with **LIVE** badge, room title (truncated), entry price in Drops
- Maximum 8 rooms shown in the horizontal scroll
- If no live rooms exist: **hide the entire row** — do not show empty state or placeholder
- On tap: shows room detail modal with description, participant count, and "Join for X Drops" CTA

---

### 2.5 Right Panel (Desktop Only)

The right panel on desktop is contextual and sticky. It contains:

| Widget | Content |
|---|---|
| **Suggested Creators** | Top 5 suggested creators with Follow buttons. Refreshed daily. "See all" links to `/discover/creators`. Matches the current screenshot. |
| **Top Gated Rooms** | Top 3 upcoming or live rooms by participant interest. Shows host, title, price. "See all" links to `/circles?tab=rooms`. Matches the current screenshot. |
| **Trending on Zumi** | ⭐ NEW — Top 5 trending hashtags or topics right now. Simple list with post count. Links to `/discover?tag=X`. Replaces empty space below Top Gated Rooms. |
| **Active Wave Challenge** | ⭐ NEW — Highlights the highest-prize-pool active challenge. Single card with prize amount, creator, and "Enter" CTA. Only shown if at least one challenge is active. |

---

## 3. Desktop Navigation — Sidebar

### 3.1 Sidebar Structure (Corrected)

The sidebar has three zones: **Top** (logo), **Middle** (primary nav), **Bottom** (user card). The current implementation is close to correct but needs the additions below.

| Zone | Contents |
|---|---|
| **Top** | Zumi wordmark logo. Links to `/feed`. No other elements. |
| **Middle — Primary Nav** | Home, Discover, Waves, Circles, Flow Market, Notifications, Drops Wallet. See Section 3.2 for full spec. |
| **Middle — Create Button** | A prominent `+ Create` button below the primary nav items. Opens a create modal with options: New Post, New Wave, New Skill Drop, New Challenge. **Currently MISSING — must be added.** |
| **Bottom** | Drops balance card (mini version: balance only). User profile row (avatar, name, handle, chevron for power menu). |

---

### 3.2 Primary Navigation Items — Full Specification

Items marked **NEW** are currently missing from the implementation.

| Nav Item | Route | Icon | Badge / Notes |
|---|---|---|---|
| **Home** | `/feed` | House icon | Green dot if unread feed items. Default landing after login. |
| **Discover** | `/discover` | Compass icon | Previously called "Explore" — **rename to "Discover"** to match Zumi language. Shows trending content, hashtags, creators, Skill Drops. |
| **Waves** ⭐ NEW | `/waves` | Play/video icon | **Currently missing as a top-level nav item.** Opens the dedicated Waves swipe player. Core feature — needs prominent placement. |
| **Circles** | `/circles` | Users icon | Previously "Circles & Rooms" — **rename to just "Circles"**. Gated Rooms are accessed from within the Circles page. Reduces nav clutter. |
| **Flow Market** ⭐ NEW | `/market` | Briefcase icon | **Currently completely missing from navigation.** Creator-to-creator gig economy. V1 feature, but nav item should be added now (can show "Coming Soon" state until V1 launches). |
| **Notifications** | `/notifications` | Bell icon | Red badge with unread count. Currently polls every 30s — upgrade to WebSocket push in V1. Count resets on page visit. |
| **Drops Wallet** | `/wallet` | Coin/wallet icon | No badge. Show current balance as a mini chip next to the label (e.g. `1,240 ◆`). Seeing your balance in the nav encourages earning behaviour. |

---

### 3.3 Profile Power Menu (Bottom of Sidebar)

Clicking the user row at the bottom of the sidebar opens a popover menu. Current items are correct but **two additions are needed**:

| Menu Item | Action & Notes |
|---|---|
| **View Profile** | Navigate to `/profile/{username}`. Shows the user's public-facing page. |
| **Creator Studio** ⭐ NEW | Navigate to `/studio`. Shows Flow Score breakdown, Skill Drop sales, Wave performance, Circle revenue, earnings history. **Currently missing.** Critical for creator retention — creators need to see their numbers. |
| **Settings & Privacy** | Navigate to `/settings`. Currently a placeholder — needs full implementation in V1: notification preferences, account security, blocked users, privacy controls. |
| **Manage Subscriptions** | Navigate to `/settings/subscriptions`. Shows active Circle memberships (as subscriber) and Pro/Studio plan status. Currently a placeholder. |
| **Refer a Friend** ⭐ NEW | Opens a share modal with the user's referral link. Referral program drives viral growth and earns both parties bonus Drops. Add in V1. |
| **Dark Mode Toggle** | Toggles between light and dark theme. Persists to localStorage and user profile. Currently implemented — keep as-is. |
| **Log Out** | Clears session token, clears global state, redirects to `/login`. |

---

## 4. Mobile Navigation — Bottom Tab Bar

### 4.1 Mobile Navigation Philosophy

Mobile navigation must be **permanently visible** at the bottom of the screen as a native-feeling tab bar. It is **NOT** a hamburger menu. The five items in the tab bar represent the five highest-priority actions on the platform. Everything else is accessible through these five entry points.

The centre position is reserved for the **Create action** — the highest-frequency action for creators. It is visually elevated with a teal background circle.

---

### 4.2 Bottom Tab Bar — 5 Items

Exact 5 items, fixed. No more, no less. Each item is an icon + label.

| Position | Label | Route | Icon | Notes |
|---|---|---|---|---|
| 1 (left) | **Home** | `/feed` | House icon | Default tab. Green dot badge for unread feed items. |
| 2 | **Waves** | `/waves` | Play icon | Opens the full-screen swipe Waves player. Replaces "Explore" on mobile — Waves is a more engaging default second tab. |
| 3 (centre) ⭐ | **Create** | Modal | Plus icon | **Elevated button** — teal filled circle, white plus icon. Tapping opens a bottom sheet with: New Post, New Wave, New Skill Drop, New Challenge. Does not navigate to a route — opens a modal overlay. |
| 4 | **Circles** | `/circles` | Users icon | Communities and Gated Rooms. Combined into one tab on mobile to save space. |
| 5 (right) | **Profile** | `/profile` | Person icon | Own profile page. Entry point to Drops Wallet, Notifications, and Settings (via icons in the profile header). |

---

### 4.3 Mobile — Items Accessible via Profile Tab

On mobile, Notifications and Drops Wallet are not in the bottom tab bar (5 slots are too few). They are accessible from within the Profile tab:

- **Notifications** — bell icon in the top-right of the Profile screen header. Red badge with count.
- **Drops Wallet** — coin/wallet icon in the Profile screen header. Shows balance chip next to it.
- **Settings** — gear icon in the Profile screen header.
- **Creator Studio** — "Studio" tab within the Profile screen (visible only to users who have created content).

> **Note:** If the team feels Notifications is too important to hide behind Profile, replace "Circles" in the tab bar with "Notifications" and move Circles inside Profile or the Waves tab. **Recommendation:** Keep the current structure for MVP. In V1, consider showing a notification count badge on the Profile tab icon itself as a middle-ground.

---

## 5. Individual Page Specifications

### 5.1 `/feed` — Home Feed

Covered in full in Section 2. Summary:

- Default tab: **For You** (algorithmic)
- Secondary tab: **Following** (chronological)
- Top: Live Gated Rooms horizontal scroll row
- Feed: mixed content cards (waves, posts, polls, skill drops, challenges, circle announcements)
- Right panel (desktop): Suggested Creators, Top Gated Rooms, Trending Topics, Active Challenge

---

### 5.2 `/discover` — Discover Page

The renamed "Explore" page. Organised into tabs:

- **Trending** — top hashtags, viral posts, trending Waves right now
- **Creators** — people to follow, filtered by interest category
- **Skill Drops** — browse all available Skill Drops by category and price
- **Circles** — browse public and gated Circles to join
- **Search bar** — global search across users, Waves, Circles, Skill Drops, hashtags

---

### 5.3 `/waves` — Waves Player

Full-screen vertical swipe interface. Distinct from feed Wave preview cards.

- Swipe up/down to move between Waves
- Right-side action column: Like, Comment (slides up), Share, Gift Drops, More (…)
- Creator info overlay at the bottom: avatar, name, handle, caption, hashtags
- Audio control: tap anywhere to mute/unmute
- "Follow" button on creator overlay (if not already following)
- **Drops gifting:** tap gift icon → bottom sheet with quick amounts (50, 100, 250, 500 Drops) + custom input
- **Challenges tab** at top: switches between "For You" Waves and "Challenges" submissions feed

---

### 5.4 `/circles` — Circles & Rooms

Two tabs at the top of this page:

- **My Circles** — Circles the user is a member of, with unread post count badges
- **Discover Circles** — browse public and gated Circles, filtered by category, with Join/Subscribe CTA

Gated Rooms section appears **below Circles** on this page (not a separate page):

- **Live Now** — active rooms with entry price and participant count
- **Upcoming** — scheduled rooms with date/time, host, and "Reserve Spot" (no Drops charged until entry)
- **Past / Replay** — ended rooms with recording available (if enabled by host), pay to watch

---

### 5.5 `/market` — Flow Market ⭐ NEW

Creator-to-creator gig economy. Two tabs:

- **Browse Offers** — creators listing services they provide (e.g. "Wave editing — 800 Drops")
- **Post a Request** — creator posts what they need with a budget

Each listing card shows: creator avatar, service title, price in Drops, category tag, response time, rating (V2).

> **For MVP:** Show a "Coming Soon" banner inside the page with an interest form. Add the nav item now so users know it is coming.

---

### 5.6 `/wallet` — Drops Wallet

The financial hub. Organised into sections:

- **Balance card** — large display of current Drops balance with USD equivalent
- **Quick actions row** — Buy Drops, Cash Out, Send (gift to a user directly)
- **Transaction history** — paginated ledger. Each entry: type icon, description, amount (+/-), date, status badge
- **Buy Drops** — opens a modal with preset amounts ($5 = 500, $10 = 1,000, $25 = 2,500, $50 = 5,000) and custom input. Stripe payment sheet.
- **Cash Out** — available to creators on Pro/Studio plan. Shows minimum threshold (5,000 Drops = $50), payout schedule, and connected bank account (via Stripe Connect).

---

### 5.7 `/studio` — Creator Studio ⭐ NEW

Accessible from the Profile power menu. A dashboard for creators only (users with at least one Circle or published Wave/Skill Drop).

| Studio Section | Content |
|---|---|
| **Overview** | Total Drops earned (all time, this month, this week). Total followers. Average Wave views. Flow Score tier badge with progress to next tier. |
| **Waves Analytics** | Per-Wave stats: views, likes, comments, Drops gifted, completion rate. Sorted by date or performance. |
| **Skill Drops** | Per-Skill-Drop stats: total purchases, total Drops earned, conversion rate (views vs purchases). "Create new" CTA. |
| **Circles** | Per-Circle stats: member count, churn rate, monthly Drops revenue, top content. Links to full Circle Insights dashboard. |
| **Challenges** | Challenges created, participation count, total prize pool distributed. |
| **Earnings History** | Full payout history with dates and amounts. Filtered to earnings only. |

---

## 6. Missing Pieces — Complete Gap Analysis

### 6.1 Navigation Gaps

| Missing Item | Priority | Action Required |
|---|---|---|
| Waves tab in sidebar/mobile nav | **Critical — MVP** | Add as 3rd item in desktop sidebar. Add as 2nd item in mobile tab bar. Route: `/waves` |
| Create button in sidebar | **Critical — MVP** | Add below nav items in desktop sidebar. Add as centre item in mobile tab bar. Opens modal with post/wave/skill-drop/challenge options. |
| Flow Market nav item | **Important — MVP nav, V1 content** | Add to desktop sidebar and mobile Profile tab. Page can show "Coming Soon" until V1 feature is built. |
| Creator Studio link in power menu | **Important — MVP** | Add "Creator Studio" to the profile power menu. Route: `/studio`. Show only to users who have created content. |
| Rename Explore → Discover | **Minor — MVP** | Update nav label and route from `/explore` to `/discover` to match Zumi brand language. |
| Rename Circles & Rooms → Circles | **Minor — MVP** | Rooms are a sub-section of Circles. Shortening the label reduces clutter. Route stays `/circles`. |
| Drops balance in nav label | **Nice to have — MVP** | Show mini balance chip `"1,240 ◆"` next to Drops Wallet nav label. Reinforces earning behaviour. |

---

### 6.2 Feed Content Gaps

- **Wave Challenge cards** are not shown in the feed — these should be injected into the For You feed to drive challenge participation
- **Skill Drop promo cards** are missing from the feed — creators need a way for their Skill Drops to be discovered organically
- **Circle Announcement cards** are missing — creators should be able to broadcast to the feed from within a Circle
- The **"Trending on Zumi"** widget in the right desktop panel is not implemented — add a `/trending` endpoint
- The **"Active Wave Challenge"** highlight widget in the right desktop panel is missing

---

### 6.3 Home Feed — Empty State Handling

| Scenario | UI Response |
|---|---|
| New user — 0 to 3 follows | Show a full-width "Seed your feed" onboarding card at the top. Link to Discover. Fill feed with algorithmically popular content. |
| Following tab — 0 follows | Show empty state with CTA: "You're not following anyone yet. Head to Discover." Do **not** show random content in the Following tab — it must be pure follows-only. |
| Following tab — follows but no recent posts | Show: "Your people have been quiet lately. Check Discover for new creators." |
| No live rooms | **Hide the Live Gated Rooms row entirely.** Do not show empty placeholder. |
| No active challenges | Hide the Active Challenge widget in the right panel. |

---

### 6.4 Notification System Gap

Currently the notification bell polls the backend every 30 seconds. This creates a 30-second delay on real-time events like Drops gifts, new followers, and Challenge wins.

**Upgrade path:**

- **MVP:** Keep 30-second polling. Acceptable for launch.
- **V1:** Replace with Laravel Reverb WebSocket. Subscribe to a private channel per user: `private-user.{id}.notifications`. Backend fires a `NotificationCreated` event on any new notification. Frontend receives it instantly and increments the badge without a page reload.
- In-app notification panel: shows last 50 notifications, grouped by type, with mark-all-read action.

---

## 7. Official Navigation Naming Glossary

This table is the **definitive reference** for all navigation labels. Use these exact names in code, design files, and documentation. Do not deviate.

| Feature / Screen | ✅ Official Label (use this) | ❌ Do NOT use |
|---|---|---|
| Main feed screen | **Home** | Feed, Timeline, Dashboard |
| Search & trending screen | **Discover** | Explore, Search, Browse |
| Short video screen | **Waves** | Shorts, Reels, Videos |
| Communities screen | **Circles** | Circles & Rooms, Groups, Communities |
| Creator gig economy screen | **Flow Market** | Marketplace, Gigs, Market |
| Alert centre | **Notifications** | Alerts, Updates, Activity |
| Currency management | **Drops Wallet** | Wallet, Balance, Coins |
| Creator analytics screen | **Creator Studio** | Dashboard, Analytics, Studio |
| User account screen | **Profile** | Account, Me, User |
| Platform currency | **Drops** | Coins, Credits, Tokens, Points |
| Short video clip | **Wave** (singular) | Short, Reel, Clip, Video |
| Paid content unit | **Skill Drop** | Course, Product, Item, Post |
| Video challenge | **Wave Challenge** | Challenge, Contest, Competition |
| Live/async paid room | **Gated Room** | Room, Space, Session, Live |
| Creator reputation score | **Flow Score** | Reputation, Score, Rank |
| Creator community | **Circle** (singular) | Group, Community, Club, Space |

---

## 8. Implementation Checklist

### MVP — Must be done before launch

- [ ] Add **Waves** as a top-level nav item in desktop sidebar (position 3, after Discover)
- [ ] Add **Waves** as tab 2 in mobile bottom nav
- [ ] Add **Create** button in desktop sidebar below nav items — opens modal with 4 options
- [ ] Add **Create** elevated centre button in mobile bottom nav
- [ ] Add **Flow Market** nav item to desktop sidebar (can show Coming Soon state)
- [ ] Rename **"Explore" → "Discover"** across all labels and routes
- [ ] Rename **"Circles & Rooms" → "Circles"** in nav
- [ ] Add **Creator Studio** to profile power menu
- [ ] Add **Wave Challenge cards** to the For You feed
- [ ] Add **Skill Drop promo cards** to the For You feed
- [ ] Add **"Trending on Zumi"** widget to the desktop right panel
- [ ] Add empty state handling for all feed scenarios (Section 6.3)
- [ ] Add **Drops balance chip** next to "Drops Wallet" label in sidebar

### Version 1 — Post-launch improvements

- [ ] Replace notification polling with **Laravel Reverb WebSocket** push
- [ ] Build `/studio` **Creator Studio** page with all sections from Section 5.7
- [ ] Build full `/market` **Flow Market** page with Offers and Requests tabs
- [ ] Add **"Refer a Friend"** to profile power menu
- [ ] Add notification count badge to **mobile Profile tab icon**
- [ ] Add **"Active Wave Challenge"** widget to desktop right panel
- [ ] Full `/settings` page with notification preferences, security, privacy
- [ ] Full `/settings/subscriptions` page

---

*— End of Zumi Navigation & Feed Specification —*
