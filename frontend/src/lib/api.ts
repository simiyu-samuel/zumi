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
