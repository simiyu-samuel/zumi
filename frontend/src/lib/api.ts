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
  drops_balance: number;
  followers_count: number | null;
  following_count: number | null;
  role: string;
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
}

export function getDiscoveryFeed(page = 1, perPage = 10) {
  return apiFetch<{ data: Wave[] }>(`/waves?page=${page}&per_page=${perPage}`);
}

export function getFollowedFeed(page = 1, perPage = 10) {
  return apiFetch<{ data: Wave[] }>(`/waves/followed?page=${page}&per_page=${perPage}`);
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
  return apiFetch<{ data: SuggestedUser[] }>(`/users/search?query=${encodeURIComponent(query)}`);
}

// ─── Wallet / Gift History ──────────────────────────────────────────
export interface GiftTransaction {
  id: string;
  type: string;
  amount: number;
  balance_after: number;
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
