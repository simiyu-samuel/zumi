"use client";

import React, { useState, useEffect, useCallback } from "react";
import { ShellLayout } from "@/components/layout/ShellLayout";
import { useAuth } from "@/context/AuthContext";
import {
  getNotifications,
  markNotificationRead,
  markAllNotificationsRead,
  type Notification,
} from "@/lib/api";

// ─── Notification type icons & colors ───────────────────────────────
function getNotificationMeta(notification: Notification) {
  const type = notification.data?.type || notification.type;

  switch (type) {
    case "drops_received":
      return {
        icon: (
          <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        ),
        color: "text-emerald-400",
        bg: "bg-emerald-500/20",
        label: notification.data?.message || `You received ${notification.data?.amount} Drops!`,
      };
    case "new_comment":
      return {
        icon: (
          <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
          </svg>
        ),
        color: "text-blue-400",
        bg: "bg-blue-500/20",
        label: `${notification.data?.commenter_name || "Someone"} commented: "${notification.data?.comment_excerpt || "..."}"`,
      };
    case "wave_liked":
      return {
        icon: (
          <svg className="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
            <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
          </svg>
        ),
        color: "text-pink-400",
        bg: "bg-pink-500/20",
        label: notification.data?.message || "Someone liked your wave!",
      };
    case "new_follower":
      return {
        icon: (
          <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
          </svg>
        ),
        color: "text-cyan-400",
        bg: "bg-cyan-500/20",
        label: notification.data?.message || "You have a new follower!",
      };
    case "mention":
      return {
        icon: (
          <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
          </svg>
        ),
        color: "text-violet-400",
        bg: "bg-violet-500/20",
        label: notification.data?.message || `${notification.data?.creator_name || "Someone"} mentioned you`,
      };
    default:
      return {
        icon: (
          <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
          </svg>
        ),
        color: "text-slate-400",
        bg: "bg-slate-500/20",
        label: notification.data?.message || "New notification",
      };
  }
}

function timeAgo(dateStr: string) {
  const now = new Date();
  const date = new Date(dateStr);
  const seconds = Math.floor((now.getTime() - date.getTime()) / 1000);

  if (seconds < 60) return "just now";
  if (seconds < 3600) return `${Math.floor(seconds / 60)}m ago`;
  if (seconds < 86400) return `${Math.floor(seconds / 3600)}h ago`;
  if (seconds < 604800) return `${Math.floor(seconds / 86400)}d ago`;
  return date.toLocaleDateString(undefined, { month: "short", day: "numeric" });
}

export default function NotificationsPage() {
  const { token } = useAuth();
  const [notifications, setNotifications] = useState<Notification[]>([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);

  const fetchNotifications = useCallback(async (pageNum: number, append = false) => {
    if (!token) return;
    if (pageNum === 1) setLoading(true);
    else setLoadingMore(true);

    try {
      const res = await getNotifications(pageNum, 20);
      const items = res.data || [];
      if (append) {
        setNotifications((prev) => [...prev, ...items]);
      } else {
        setNotifications(items);
      }
      setHasMore(items.length >= 20);
    } catch (err) {
      console.error("Failed to load notifications:", err);
    } finally {
      setLoading(false);
      setLoadingMore(false);
    }
  }, [token]);

  useEffect(() => {
    fetchNotifications(1);
  }, [fetchNotifications]);

  const handleMarkRead = async (id: string) => {
    try {
      await markNotificationRead(id);
      setNotifications((prev) =>
        prev.map((n) => (n.id === id ? { ...n, is_read: true, read_at: new Date().toISOString() } : n))
      );
    } catch (err) {
      console.error("Failed to mark read:", err);
    }
  };

  const handleMarkAllRead = async () => {
    try {
      await markAllNotificationsRead();
      setNotifications((prev) =>
        prev.map((n) => ({ ...n, is_read: true, read_at: new Date().toISOString() }))
      );
    } catch (err) {
      console.error("Failed to mark all read:", err);
    }
  };

  const handleLoadMore = () => {
    const nextPage = page + 1;
    setPage(nextPage);
    fetchNotifications(nextPage, true);
  };

  const unreadCount = notifications.filter((n) => !n.is_read).length;

  return (
    <ShellLayout>
      {/* Header */}
      <header className="px-8 py-5 flex justify-between items-center sticky top-0 bg-black/40 backdrop-blur-xl z-30 border-b border-white/5">
        <div>
          <h1 className="text-[18px] font-black text-white tracking-tight">Notifications</h1>
          {unreadCount > 0 && (
            <p className="text-[12px] text-teal font-bold mt-0.5">
              {unreadCount} unread
            </p>
          )}
        </div>
        {unreadCount > 0 && (
          <button
            onClick={handleMarkAllRead}
            className="text-[12px] font-black text-teal hover:text-teal-light transition-colors uppercase tracking-wider"
          >
            Mark all read
          </button>
        )}
      </header>

      {/* Notification List */}
      <div className="flex-1 overflow-y-auto no-scrollbar">
        {loading ? (
          <div className="flex flex-col items-center py-20 gap-4">
            <div className="w-10 h-10 border-2 border-teal/30 border-t-teal rounded-full animate-spin" />
            <p className="text-sm text-slate-500">Loading notifications...</p>
          </div>
        ) : notifications.length === 0 ? (
          <div className="flex flex-col items-center py-20 gap-4 opacity-50">
            <svg className="w-16 h-16 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
            </svg>
            <p className="text-lg font-bold text-slate-400">All caught up!</p>
            <p className="text-sm text-slate-600">No notifications yet.</p>
          </div>
        ) : (
          <div className="divide-y divide-white/5">
            {notifications.map((notification) => {
              const meta = getNotificationMeta(notification);
              return (
                <button
                  key={notification.id}
                  onClick={() => !notification.is_read && handleMarkRead(notification.id)}
                  className={`w-full flex items-start gap-4 px-8 py-5 text-left transition-all hover:bg-white/5 group ${
                    !notification.is_read ? "bg-teal/5" : ""
                  }`}
                >
                  {/* Icon */}
                  <div className={`w-11 h-11 rounded-full ${meta.bg} ${meta.color} flex items-center justify-center shrink-0 mt-0.5`}>
                    {meta.icon}
                  </div>

                  {/* Content */}
                  <div className="flex-1 min-w-0">
                    <p className={`text-[14px] leading-relaxed ${notification.is_read ? "text-slate-400" : "text-white font-semibold"}`}>
                      {meta.label}
                    </p>
                    <p className="text-[11px] text-slate-600 font-bold mt-1 uppercase tracking-wider">
                      {timeAgo(notification.created_at)}
                    </p>
                  </div>

                  {/* Unread dot */}
                  {!notification.is_read && (
                    <div className="w-2.5 h-2.5 rounded-full bg-teal shrink-0 mt-2 shadow-[0_0_6px_rgba(26,158,117,0.5)]" />
                  )}
                </button>
              );
            })}

            {/* Load More */}
            {hasMore && (
              <div className="px-8 py-6 flex justify-center">
                <button
                  onClick={handleLoadMore}
                  disabled={loadingMore}
                  className="px-6 py-3 rounded-full bg-white/5 border border-white/10 text-[13px] font-bold text-slate-400 hover:text-white hover:bg-white/10 transition-all disabled:opacity-50"
                >
                  {loadingMore ? (
                    <div className="flex items-center gap-2">
                      <div className="w-4 h-4 border-2 border-teal/30 border-t-teal rounded-full animate-spin" />
                      Loading...
                    </div>
                  ) : (
                    "Load more"
                  )}
                </button>
              </div>
            )}
          </div>
        )}
      </div>
    </ShellLayout>
  );
}
