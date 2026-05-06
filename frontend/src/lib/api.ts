const API_URL = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8001/api/v1";

function getHeaders(): HeadersInit {
  const token = typeof window !== "undefined" ? localStorage.getItem("zumi_token") : null;
  const headers: HeadersInit = {
    "Content-Type": "application/json",
    Accept: "application/json",
  };
  if (token) {
    headers["Authorization"] = `Bearer ${token}`;
  }
  return headers;
}

export async function apiFetch<T = any>(
  path: string,
  options?: RequestInit
): Promise<T> {
  const res = await fetch(`${API_URL}${path}`, {
    ...options,
    headers: {
      ...getHeaders(),
      ...options?.headers,
    },
  });

  if (!res.ok) {
    const error = await res.json().catch(() => ({ message: "Request failed" }));
    throw new Error(error.message || `API Error: ${res.status}`);
  }

  return res.json();
}

// ─── Feed ───────────────────────────────────────────────────────────
export interface WaveUser {
  id: string;
  name: string;
  username: string;
  bio: string | null;
  avatar_url: string;
  banner_url: string | null;
  drops_balance: number;
  flow_score: number;
  flow_score_summary?: {
    score: number;
    tier: string;
    tier_label: string;
    next_threshold: number | null;
    points_to_next: number | null;
  };
  followers_count: number;
  following_count: number;
  role: string;
  is_following?: boolean;
}

export interface Wave {
  id: string;
  user: WaveUser;
  title: string;
  description: string | null;
  thumbnail_url: string | null;
  visibility: string;
  gated_drops: number;
  likes_count: number;
  comments_count: number;
  shares_count: number;
  views_count: number;
  is_liked: boolean;
  is_unlocked: boolean;
  created_at: string;
}

export interface GatedRoom {
  id: string;
  title: string;
  description: string | null;
  entry_fee_drops: number;
  status: string;
  host: WaveUser;
  is_host: boolean;
  is_participant: boolean;
  participants_count: number;
  started_at: string | null;
}

export interface SuggestedUser {
  id: string;
  name: string;
  username: string;
  bio: string | null;
  avatar_url: string;
  followers_count: number | null;
  role: string;
}

export function getDiscoveryFeed(cursor?: string, perPage = 15) {
  const url = cursor ? `/waves?cursor=${cursor}&per_page=${perPage}` : `/waves?per_page=${perPage}`;
  return apiFetch<any>(url);
}

export function getFollowedFeed(cursor?: string, perPage = 15) {
  const url = cursor ? `/waves/followed?cursor=${cursor}&per_page=${perPage}` : `/waves/followed?per_page=${perPage}`;
  return apiFetch<any>(url);
}

export function getGatedRooms() {
  return apiFetch<{ data: GatedRoom[] }>("/rooms");
}

export function getSuggestedUsers(limit = 5) {
  return apiFetch<{ data: SuggestedUser[] }>(`/search/suggested?limit=${limit}`);
}

export function followUser(userId: string) {
  return apiFetch(`/users/${userId}/follow`, { method: "POST" });
}

export function unfollowUser(userId: string) {
  return apiFetch(`/users/${userId}/unfollow`, { method: "POST" });
}

export function getWave(waveId: string) {
  return apiFetch<{ data: Wave }>(`/waves/${waveId}`);
}

export function likeWave(waveId: string) {
  return apiFetch(`/waves/${waveId}/like`, { method: "POST" });
}

export function shareWave(waveId: string) {
  return apiFetch(`/waves/${waveId}/share`, { method: "POST" });
}

export function purchaseWave(waveId: string) {
  return apiFetch(`/waves/${waveId}/purchase`, { method: "POST" });
}

export function giftWave(waveId: string, amount: number) {
  return apiFetch(`/waves/${waveId}/gift`, {
    method: "POST",
    body: JSON.stringify({ amount }),
  });
}

export function giftUser(receiverId: string, amount: number) {
  return apiFetch(`/drops/gift`, {
    method: "POST",
    body: JSON.stringify({ receiver_id: receiverId, amount }),
  });
}

export interface Comment {
  id: string;
  content: string;
  user: WaveUser;
  parent_id: string | null;
  replies?: Comment[];
  likes_count: number;
  is_liked: boolean;
  created_at: string;
}

export function getComments(waveId: string) {
  return apiFetch<{ data: Comment[] }>(`/waves/${waveId}/comments`);
}

export function postComment(waveId: string, content: string, parentId?: string) {
  return apiFetch<Comment>(`/waves/${waveId}/comments`, {
    method: "POST",
    body: JSON.stringify({ content, parent_id: parentId }),
  });
}

export function likeComment(commentId: string) {
  return apiFetch(`/comments/${commentId}/like`, { method: "POST" });
}

// ─── Notifications ──────────────────────────────────────────────────
export interface Notification {
  id: string;
  type: string;
  data: {
    type?: string;
    message?: string;
    sender_name?: string;
    sender_id?: string;
    amount?: number;
    commenter_name?: string;
    commenter_username?: string;
    comment_excerpt?: string;
    creator_name?: string;
    source_type?: string;
    source_id?: string;
    [key: string]: any;
  };
  read_at: string | null;
  is_read: boolean;
  created_at: string;
}

export function getNotifications(page = 1, perPage = 15) {
  return apiFetch<{ data: Notification[]; meta: any }>(`/notifications?page=${page}&per_page=${perPage}`);
}

export function getUnreadCount() {
  return apiFetch<{ unread_count: number }>("/notifications/unread-count");
}

export function markNotificationRead(id: string) {
  return apiFetch(`/notifications/${id}/read`, { method: "POST" });
}

export function markAllNotificationsRead() {
  return apiFetch(`/notifications/read-all`, { method: "POST" });
}

// ─── Search ─────────────────────────────────────────────────────────
export function searchUsers(query: string) {
  return apiFetch<{ data: SuggestedUser[] }>(`/search/users?q=${encodeURIComponent(query)}`);
}

export function getUserProfile(username: string) {
  return apiFetch<{ data: WaveUser }>(`/users/${username}`);
}

export function getUserWaves(userId: string, cursor?: string) {
  const url = `/users/${userId}/waves${cursor ? `?cursor=${cursor}` : ""}`;
  return apiFetch<{ data: Wave[]; next_cursor: string | null }>(url);
}


// ─── Wallet / Gift History ──────────────────────────────────────────
export interface GiftTransaction {
  id: string;
  type: string;
  amount: number;
  balance_after: number;
  direction: 'credit' | 'debit';
  description: string;
  user: WaveUser | null;
  created_at: string;
}

export function getWalletData() {
  return apiFetch<{ balance: number; transactions: { data: GiftTransaction[] } }>("/wallet");
}

export function getGiftHistory(page = 1, perPage = 15) {
  return apiFetch<{ data: GiftTransaction[]; meta: any }>(`/wallet/gifts?page=${page}&per_page=${perPage}`);
}

// ─── Trending ───────────────────────────────────────────────────────
export interface TrendingHashtag {
  tag: string;
  count: number;
  label: string;
}

export interface TrendingWave {
  id: string;
  title: string;
  views_count: number;
  likes_count: number;
  user: { name: string; username: string };
}

export interface TrendingData {
  hashtags: TrendingHashtag[];
  top_waves: TrendingWave[];
  active_challenge: {
    id: string;
    title: string;
    description: string;
    prize_pool: number;
    ends_at: string;
    participations_count: number;
    user: { name: string; username: string; avatar_url: string };
  } | null;
}

export function getTrending() {
  return apiFetch<TrendingData>("/trending");
}

// ─── Challenges ─────────────────────────────────────────────────────
export interface Challenge {
  id: string;
  title: string;
  description: string;
  type: string;
  status: string;
  prize_pool: number;
  ends_at: string;
  user: WaveUser;
  winner: WaveUser | null;
  participations: any[];
  created_at: string;
}

export function getActiveChallenges(perPage = 5) {
  return apiFetch<{ data: Challenge[] }>(`/challenges?per_page=${perPage}`);
}

export function getChallenge(id: string) {
  return apiFetch<{ data: Challenge }>(`/challenges/${id}`);
}

export function joinChallenge(challengeId: string, waveId: string) {
  return apiFetch(`/challenges/${challengeId}/join`, {
    method: "POST",
    body: JSON.stringify({ wave_id: waveId }),
  });
}

export function createChallenge(data: { title: string; description: string; type: string; prize_pool: number; ends_at: string }) {
  return apiFetch(`/challenges`, {
    method: "POST",
    body: JSON.stringify(data),
  });
}

// ─── Skill Drops ────────────────────────────────────────────────────
export interface SkillDrop {
  id: string;
  title: string;
  slug: string;
  description: string;
  price_drops: number;
  preview_url: string | null;
  content_url: string | null;
  sales_count: number;
  rating_avg: number;
  user: WaveUser;
  is_owned: boolean;
  is_purchased: boolean;
  created_at: string;
}

export function getSkillDrops(perPage = 15) {
  return apiFetch<{ data: SkillDrop[] }>(`/skill-drops?per_page=${perPage}`);
}

export function getSkillDrop(id: string) {
  return apiFetch<{ data: SkillDrop }>(`/skill-drops/${id}`);
}

export function getMySkillDrops(perPage = 15) {
  return apiFetch<{ data: SkillDrop[] }>(`/skill-drops/my-drops?per_page=${perPage}`);
}

export function getSkillDropLibrary(perPage = 15) {
  return apiFetch<{ data: SkillDrop[] }>(`/skill-drops/library?per_page=${perPage}`);
}

export function purchaseSkillDrop(id: string) {
  return apiFetch(`/skill-drops/${id}/purchase`, { method: "POST" });
}

export function createSkillDrop(data: { title: string; description: string; price_drops: number; preview_url?: string; content_url?: string }) {
  return apiFetch(`/skill-drops`, {
    method: "POST",
    body: JSON.stringify(data),
  });
}

// ─── Circles ────────────────────────────────────────────────────────
export interface Circle {
  id: string;
  name: string;
  slug: string;
  description: string | null;
  type: string;
  status: string;
  members_count: number;
  owner: WaveUser;
  is_member: boolean;
  created_at: string;
}

export function getCircles(perPage = 15) {
  return apiFetch<{ data: Circle[] }>(`/circles?per_page=${perPage}`);
}

export function getCircle(slug: string) {
  return apiFetch<{ data: Circle }>(`/circles/${slug}`);
}

export function createCircle(data: { name: string; description: string; type: string }) {
  return apiFetch(`/circles`, {
    method: "POST",
    body: JSON.stringify(data),
  });
}

// ─── Search (Global) ────────────────────────────────────────────────
export function globalSearch(query: string, limit = 15) {
  return apiFetch<{ users: WaveUser[]; waves: Wave[]; circles: Circle[] }>(
    `/search?q=${encodeURIComponent(query)}&limit=${limit}`
  );
}

export function searchCircles(query: string, perPage = 15) {
  return apiFetch<{ data: Circle[] }>(`/search/circles?q=${encodeURIComponent(query)}&per_page=${perPage}`);
}

// ─── Waves (additional) ─────────────────────────────────────────────
export function recordWaveView(waveId: string) {
  return apiFetch(`/waves/${waveId}/view`, { method: "POST" });
}

export function toggleBookmark(waveId: string) {
  return apiFetch(`/waves/${waveId}/bookmark`, { method: "POST" });
}

// ─── Wave Upload ────────────────────────────────────────────────────
export function initializeWaveUpload(title: string, sizeBytes: number) {
  return apiFetch<{ upload_url: string; stream_id: string }>(`/waves/upload/initialize`, {
    method: "POST",
    body: JSON.stringify({ title, size_bytes: sizeBytes }),
  });
}

export function createWave(data: {
  title: string;
  description?: string;
  stream_id?: string;
  visibility?: string;
  gated_drops?: number;
  circle_id?: string;
}) {
  return apiFetch(`/waves`, {
    method: "POST",
    body: JSON.stringify(data),
  });
}

// ─── Gated Rooms (additional) ───────────────────────────────────────
export function createGatedRoom(data: {
  title: string;
  description?: string;
  entry_fee_drops: number;
  scheduled_at?: string;
}) {
  return apiFetch(`/rooms`, {
    method: "POST",
    body: JSON.stringify(data),
  });
}

export function joinGatedRoom(roomId: string) {
  return apiFetch(`/rooms/${roomId}/join`, { method: "POST" });
}

