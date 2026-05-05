"use client";

import React, { useEffect, useState, useCallback } from "react";
import { ShellLayout } from "@/components/layout/ShellLayout";
import { useAuth } from "@/context/AuthContext";
import {
  getDiscoveryFeed,
  getFollowedFeed,
  getGatedRooms,
  likeWave,
  shareWave,
  purchaseWave,
  type Wave,
  type GatedRoom,
} from "@/lib/api";

type FeedTab = "for-you" | "following";

function formatCount(n: number): string {
  if (n >= 1000000) return (n / 1000000).toFixed(1) + "M";
  if (n >= 1000) return (n / 1000).toFixed(1) + "k";
  return String(n);
}

export default function FeedPage() {
  const { user, token } = useAuth();
  const [activeTab, setActiveTab] = useState<FeedTab>("for-you");
  const [waves, setWaves] = useState<Wave[]>([]);
  const [gatedRooms, setGatedRooms] = useState<GatedRoom[]>([]);
  const [loading, setLoading] = useState(true);

  const fetchFeed = useCallback(async () => {
    setLoading(true);
    try {
      const fetcher = activeTab === "for-you" ? getDiscoveryFeed : getFollowedFeed;
      const res = await fetcher(1, 10);
      setWaves(res.data || []);
    } catch (err) {
      console.error("Feed fetch error:", err);
    } finally {
      setLoading(false);
    }
  }, [activeTab]);

  const fetchRooms = useCallback(async () => {
    if (!token) return;
    try {
      const res = await getGatedRooms();
      setGatedRooms(res.data || []);
    } catch (err) {
      console.error("Rooms fetch error:", err);
    }
  }, [token]);

  useEffect(() => {
    fetchFeed();
  }, [fetchFeed]);

  useEffect(() => {
    fetchRooms();
  }, [fetchRooms]);

  const handleLike = async (waveId: string) => {
    try {
      await likeWave(waveId);
      setWaves((prev) =>
        prev.map((w) =>
          w.id === waveId
            ? { ...w, is_liked: !w.is_liked, likes_count: w.is_liked ? w.likes_count - 1 : w.likes_count + 1 }
            : w
        )
      );
    } catch (err) {
      console.error("Like error:", err);
    }
  };

  const handleShare = async (waveId: string) => {
    try {
      const res = await shareWave(waveId);
      setWaves((prev) =>
        prev.map((w) =>
          w.id === waveId ? { ...w, shares_count: res.shares_count ?? w.shares_count + 1 } : w
        )
      );
    } catch (err) {
      console.error("Share error:", err);
    }
  };

  const handleGift = async (waveId: string, amount: number) => {
    try {
      await purchaseWave(waveId);
      // Update wave as unlocked
      setWaves((prev) =>
        prev.map((w) =>
          w.id === waveId ? { ...w, is_unlocked: true } : w
        )
      );
      alert(`Successfully unlocked!`);
    } catch (err: any) {
      alert(err.message || "Failed to unlock wave");
    }
  };

  const liveRooms = gatedRooms.filter((r) => r.status === "live");

  return (
    <ShellLayout>
      {/* Sticky Header */}
      <header className="px-8 py-5 flex justify-between items-center sticky top-0 bg-black/40 backdrop-blur-xl z-30 border-b border-white/5">
        <div className="flex gap-8">
          <button
            onClick={() => setActiveTab("for-you")}
            className={`text-[15px] font-black tracking-tight pb-1 transition-colors ${activeTab === "for-you" ? "text-white border-b-2 border-teal" : "text-slate-500 hover:text-white"}`}
          >
            FOR YOU
          </button>
          <button
            onClick={() => setActiveTab("following")}
            className={`text-[15px] font-bold tracking-tight pb-1 transition-colors ${activeTab === "following" ? "text-white border-b-2 border-teal" : "text-slate-500 hover:text-white"}`}
          >
            FOLLOWING
          </button>
        </div>
      </header>

      <div className="flex-1 overflow-y-auto no-scrollbar">
        {/* Live Gated Rooms Tray */}
        {liveRooms.length > 0 && (
          <section className="px-8 py-6">
            <div className="flex items-center justify-between mb-4">
              <h2 className="text-[13px] font-black text-slate-500 uppercase tracking-widest">
                Live Gated Rooms
              </h2>
              <div className="flex gap-1">
                <div className="w-1.5 h-1.5 rounded-full bg-teal animate-pulse"></div>
                <div className="w-1.5 h-1.5 rounded-full bg-slate-800"></div>
              </div>
            </div>
            <div className="flex gap-5 overflow-x-auto no-scrollbar pb-2">
              {liveRooms.map((room) => (
                <div
                  key={room.id}
                  className="flex-shrink-0 flex flex-col items-center gap-2 group cursor-pointer"
                >
                  <div className="relative p-1 rounded-full bg-gradient-to-tr from-teal to-cyan-400 group-hover:scale-105 transition-transform">
                    <div className="w-16 h-16 rounded-full bg-slate-900 border-2 border-black overflow-hidden relative">
                      <div className="absolute inset-0 bg-slate-800 flex items-center justify-center">
                        <span className="text-[18px] font-bold text-teal">
                          {room.host?.name?.charAt(0) || "?"}
                        </span>
                      </div>
                      <div className="absolute inset-0 bg-black/30 flex items-center justify-center">
                        <svg className="w-5 h-5 text-white/80" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                      </div>
                    </div>
                    <div className="absolute -bottom-1 left-1/2 -translate-x-1/2 bg-teal text-[9px] font-black px-2 py-0.5 rounded-full text-white ring-2 ring-black">
                      LIVE
                    </div>
                  </div>
                  <span className="text-[11px] font-bold text-slate-300 max-w-[72px] truncate text-center">
                    {room.title.split(" ").slice(0, 2).join(" ")}
                  </span>
                  <span className="text-[10px] text-teal font-bold">{room.entry_fee_drops} Drops</span>
                </div>
              ))}
            </div>
          </section>
        )}

        {/* Loading State */}
        {loading && (
          <div className="px-8 py-20 flex flex-col items-center gap-4">
            <div className="w-10 h-10 border-2 border-teal/30 border-t-teal rounded-full animate-spin"></div>
            <p className="text-sm text-slate-500">Loading your feed...</p>
          </div>
        )}

        {/* Empty State */}
        {!loading && waves.length === 0 && (
          <div className="px-8 py-20 flex flex-col items-center gap-4">
            <svg className="w-16 h-16 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
            <p className="text-lg font-bold text-slate-400">No waves yet</p>
            <p className="text-sm text-slate-600 text-center max-w-xs">
              {activeTab === "following"
                ? "Follow some creators to see their waves here."
                : "Be the first to drop a wave!"}
            </p>
          </div>
        )}

        {/* Video Feed */}
        {!loading && waves.length > 0 && (
          <div className="px-8 space-y-10 pb-20">
            {waves.map((wave) => (
              <WaveCard
                key={wave.id}
                wave={wave}
                onLike={handleLike}
                onShare={handleShare}
                onGift={handleGift}
              />
            ))}
          </div>
        )}
      </div>
    </ShellLayout>
  );
}

function WaveCard({
  wave,
  onLike,
  onShare,
  onGift,
}: {
  wave: Wave;
  onLike: (id: string) => void;
  onShare: (id: string) => void;
  onGift: (id: string, amount: number) => void;
}) {
  return (
    <div className="relative aspect-[9/16] w-full rounded-r24 overflow-hidden bg-slate-900 group shadow-2xl">
      {/* Thumbnail / Video */}
      {wave.thumbnail_url ? (
        <img
          src={wave.thumbnail_url}
          alt={wave.title}
          className="absolute inset-0 w-full h-full object-cover"
        />
      ) : (
        <div className="absolute inset-0 bg-gradient-to-br from-slate-900 to-slate-800" />
      )}

      {/* Gradient Overlay */}
      <div className="absolute inset-0 bg-gradient-to-b from-black/20 via-transparent to-black/80 z-10" />

      {/* Creator Overlay */}
      <div className="absolute bottom-0 left-0 right-0 p-6 z-20 flex justify-between items-end">
        <div className="flex-1">
          <div className="flex items-center gap-3 mb-3">
            <div className="w-12 h-12 rounded-full border-2 border-teal/50 p-0.5 overflow-hidden">
              {wave.user.avatar_url ? (
                <img src={wave.user.avatar_url} alt={wave.user.name} className="w-full h-full rounded-full object-cover" />
              ) : (
                <div className="w-full h-full rounded-full bg-teal/20 flex items-center justify-center font-bold text-teal text-lg">
                  {wave.user.name.charAt(0)}
                </div>
              )}
            </div>
            <div>
              <div className="font-bold text-white text-[17px]">{wave.user.name}</div>
              <div className="text-teal text-sm font-semibold">@{wave.user.username}</div>
            </div>
          </div>

          {/* Gated Overlay or Title */}
          {wave.visibility === "gated" && !wave.is_unlocked ? (
            <div className="mb-4">
              <div className="flex items-center gap-2 text-teal font-bold text-sm mb-2">
                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                </svg>
                GATED CONTENT
              </div>
              <p className="text-white font-bold text-lg mb-4">Unlock this wave for {wave.gated_drops} Drops</p>
              <button 
                onClick={() => onGift(wave.id, wave.gated_drops)}
                className="w-full py-3 rounded-full bg-teal text-white font-black uppercase tracking-wider hover:bg-teal-dark transition-colors shadow-lg shadow-teal/20"
              >
                Unlock Now
              </button>
            </div>
          ) : (
            <>
              <p className="text-[15px] text-slate-200 line-clamp-2 mb-4 leading-relaxed max-w-[80%]">
                {wave.description || wave.title}
              </p>

              {/* Gift Drops Button */}
              <button 
                onClick={() => onGift(wave.id, 100)} // Default gift 100
                className="flex items-center gap-2 px-5 py-3 rounded-full bg-white/10 backdrop-blur-md border border-white/20 hover:bg-white/20 transition-all group/gift"
              >
                <div className="p-1.5 rounded-full bg-teal/20 text-teal group-hover/gift:bg-teal group-hover/gift:text-white transition-colors">
                  <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </div>
                <span className="text-[14px] font-black text-white uppercase tracking-wider">Gift Drops</span>
              </button>
            </>
          )}
        </div>

        {/* Actions Sidebar */}
        <div className="flex flex-col gap-6 items-center">
          {/* Like */}
          <button
            onClick={() => onLike(wave.id)}
            className="flex flex-col items-center gap-1 group/action cursor-pointer"
          >
            <div className={`w-12 h-12 rounded-full glass flex items-center justify-center transition-all ${wave.is_liked ? "text-red-400" : "group-hover/action:text-teal"}`}>
              <svg className="w-6 h-6" fill={wave.is_liked ? "currentColor" : "none"} viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={wave.is_liked ? 0 : 2} d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3c1.74 0 3.124.588 4.312 1.57A6.981 6.981 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
              </svg>
            </div>
            <span className="text-[12px] font-bold text-white">{formatCount(wave.likes_count)}</span>
          </button>

          {/* Comment */}
          <div className="flex flex-col items-center gap-1 group/action cursor-pointer">
            <div className="w-12 h-12 rounded-full glass flex items-center justify-center group-hover/action:text-teal transition-all">
              <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
            </div>
            <span className="text-[12px] font-bold text-white">{formatCount(wave.comments_count)}</span>
          </div>

          {/* Share */}
          <button
            onClick={() => onShare(wave.id)}
            className="flex flex-col items-center gap-1 group/action cursor-pointer"
          >
            <div className="w-12 h-12 rounded-full glass flex items-center justify-center group-hover/action:text-teal transition-all">
              <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
              </svg>
            </div>
            <span className="text-[12px] font-bold text-white">{formatCount(wave.shares_count)}</span>
          </button>

          {/* Views */}
          <div className="flex flex-col items-center gap-1">
            <div className="w-12 h-12 rounded-full glass flex items-center justify-center text-slate-400">
              <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </div>
            <span className="text-[12px] font-bold text-slate-400">{formatCount(wave.views_count)}</span>
          </div>
        </div>
      </div>
    </div>
  );
}
