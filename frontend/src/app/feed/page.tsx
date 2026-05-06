"use client";

import React, { useEffect, useState, useCallback } from "react";
import { useSearchParams } from "next/navigation";
import Link from "next/link";
import { ShellLayout } from "@/components/layout/ShellLayout";
import { useAuth } from "@/context/AuthContext";
import {
  getDiscoveryFeed,
  getFollowedFeed,
  getGatedRooms,
  getWave,
  getTrending,
  getSkillDrops,
  likeWave,
  shareWave,
  purchaseWave,
  type Wave,
  type TrendingData,
  type SkillDrop,
} from "@/lib/api";
import { CommentSheet } from "@/components/waves/CommentSheet";
import { ShareSheet } from "@/components/waves/ShareSheet";
import { GiftSheet } from "@/components/waves/GiftSheet";
import { Avatar } from "@/components/ui/Avatar";

type FeedTab = "for-you" | "following";

function formatCount(n: number): string {
  if (n >= 1000000) return (n / 1000000).toFixed(1) + "M";
  if (n >= 1000) return (n / 1000).toFixed(1) + "k";
  return String(n);
}

export default function FeedPage() {
  const { user, token } = useAuth();
  const searchParams = useSearchParams();
  const waveIdParam = searchParams.get("wave");
  const [activeTab, setActiveTab] = useState<FeedTab>("for-you");
  const [waves, setWaves] = useState<Wave[]>([]);
  const [gatedRooms, setGatedRooms] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [nextCursor, setNextCursor] = useState<string | null>(null);
  const [hasMore, setHasMore] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const observerTarget = React.useRef(null);

  const [activeCommentWaveId, setActiveCommentWaveId] = useState<string | null>(null);
  const [activeShareWaveId, setActiveShareWaveId] = useState<string | null>(null);
  const [activeGiftWaveId, setActiveGiftWaveId] = useState<string | null>(null);
  const [trending, setTrending] = useState<TrendingData | null>(null);
  const [skillDrops, setSkillDrops] = useState<SkillDrop[]>([]);

  const fetchData = useCallback(async () => {
    try {
      const [trendingRes, dropsRes] = await Promise.all([
        getTrending(),
        getSkillDrops(undefined, 5)
      ]);
      setTrending(trendingRes);
      setSkillDrops(dropsRes.data || []);
    } catch (err) {
      console.error("Data fetch error:", err);
    }
  }, [token]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  const fetchFeed = useCallback(async (cursor?: string | null, append = false) => {
    if (!append) setLoading(true);
    else setLoadingMore(true);

    try {
      const fetcher = activeTab === "for-you" ? getDiscoveryFeed : getFollowedFeed;
      const res = await fetcher(cursor || undefined, 15);
      let items = res.data || [];
      
      // If we have a specific wave requested via param and it's not the first load of more items
      if (!append && waveIdParam) {
        try {
          const specificWaveRes = await getWave(waveIdParam);
          if (specificWaveRes.data) {
            // Remove it from the list if it's already there to avoid duplicates
            items = items.filter((w: Wave) => w.id !== waveIdParam);
            // Prepend it
            items = [specificWaveRes.data, ...items];
          }
        } catch (err) {
          console.error("Error fetching specific wave:", err);
        }
      }
      
      if (append) {
        setWaves(prev => {
          // Robust duplicate prevention
          const existingIds = new Set(prev.map((w: Wave) => w.id));
          const newItems = items.filter((w: Wave) => !existingIds.has(w.id));
          return [...prev, ...newItems];
        });
      } else {
        setWaves(items);
      }
      
      setNextCursor(res.next_cursor || null);
      setHasMore(!!res.next_cursor);
    } catch (err) {
      console.error("Feed fetch error:", err);
    } finally {
      setLoading(false);
      setLoadingMore(false);
    }
  }, [activeTab, token, waveIdParam]);

  useEffect(() => {
    fetchFeed(null, false);
  }, [activeTab, fetchFeed]);

  // Infinite Scroll Observer
  useEffect(() => {
    const target = observerTarget.current;
    if (loading || !hasMore || loadingMore || !target) return;

    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting && !loadingMore) {
          fetchFeed(nextCursor, true);
        }
      },
      { threshold: 0.1, rootMargin: '200px' }
    );

    observer.observe(target);
    return () => observer.disconnect();
  }, [loading, hasMore, loadingMore, nextCursor, fetchFeed]);

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
    if (token) {
      fetchRooms();
    }
  }, [fetchRooms, token]);

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

  const handleShare = (waveId: string) => {
    setActiveShareWaveId(waveId);
    // Optionally call API to record share count
    shareWave(waveId).then((res) => {
      setWaves((prev) =>
        prev.map((w) =>
          w.id === waveId ? { ...w, shares_count: res.shares_count ?? w.shares_count + 1 } : w
        )
      );
    });
  };

  const handleComment = (waveId: string) => {
    setActiveCommentWaveId(waveId);
  };

  const handleGift = (waveId: string) => {
    setActiveGiftWaveId(waveId);
  };

  const handleUnlock = async (waveId: string, amount: number) => {
    try {
      await purchaseWave(waveId);
      setWaves((prev) =>
        prev.map((w) =>
          w.id === waveId ? { ...w, is_unlocked: true } : w
        )
      );
    } catch (err: any) {
      alert(err.message || "Failed to unlock wave");
    }
  };

  const liveRooms = gatedRooms.filter((r) => r.status === "live");

  return (
    <ShellLayout>
      {/* Comment Sheet Modal */}
      {activeCommentWaveId && (
        <CommentSheet 
          waveId={activeCommentWaveId} 
          onClose={() => setActiveCommentWaveId(null)} 
        />
      )}

      {/* Share Sheet Modal */}
      {activeShareWaveId && (
        <ShareSheet 
          waveId={activeShareWaveId} 
          onClose={() => setActiveShareWaveId(null)} 
        />
      )}

      {/* Gift Sheet Modal */}
      {activeGiftWaveId && (
        <GiftSheet 
          waveId={activeGiftWaveId} 
          onClose={() => setActiveGiftWaveId(null)} 
        />
      )}

      {/* Sticky Header */}
      <header className="px-8 py-4 flex justify-between items-center sticky top-0 bg-slate-950/80 backdrop-blur-2xl z-30 border-b border-white/5">
        <div className="flex gap-8">
          <button
            onClick={() => setActiveTab("for-you")}
            className={`text-[15px] font-bold tracking-wide pb-1 transition-all ${activeTab === "for-you" ? "text-teal border-b-2 border-teal" : "text-slate-500 hover:text-slate-300"}`}
          >
            For You
          </button>
          <button
            onClick={() => setActiveTab("following")}
            className={`text-[15px] font-bold tracking-wide pb-1 transition-all ${activeTab === "following" ? "text-teal border-b-2 border-teal" : "text-slate-500 hover:text-slate-300"}`}
          >
            Following
          </button>
        </div>
        <div className="flex items-center gap-4">
          <div className="relative">
            <div className="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
              <svg className="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
              </svg>
            </div>
            <input 
              type="text" 
              placeholder="Search Zumi" 
              className="bg-slate-900 border border-white/5 text-slate-200 text-[13px] font-semibold rounded-full pl-10 pr-4 py-2 w-48 lg:w-64 focus:outline-none focus:border-teal/50 transition-colors placeholder:text-slate-600" 
            />
          </div>
          <button className="text-slate-500 hover:text-white transition-colors">
            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
          </button>
        </div>
      </header>

      <div className="flex-1 overflow-y-auto no-scrollbar">
        {/* Live Gated Rooms Tray */}
        {liveRooms.length > 0 && (
          <section className="px-8 py-6">
            <div className="flex items-center justify-between mb-5">
              <div className="flex items-center gap-2">
                <h2 className="text-[20px] font-bold text-white tracking-wide">
                  Live Rooms
                </h2>
                <div className="w-1.5 h-1.5 rounded-full bg-red-500/80 shadow-[0_0_8px_rgba(239,68,68,0.5)]"></div>
              </div>
              <Link href="/circles" className="text-[13px] font-bold text-teal hover:text-teal-400 transition-colors">
                View All
              </Link>
            </div>
            <div className="flex gap-4 overflow-x-auto no-scrollbar pb-4">
              {liveRooms.map((room) => (
                <div
                  key={room.id}
                  className="flex-shrink-0 w-[180px] rounded-[16px] bg-[#0B0F15] border border-white/5 overflow-hidden relative group cursor-pointer shadow-lg hover:border-teal/30 transition-all hover:-translate-y-1"
                >
                  <div className="relative h-[160px] w-full">
                    <img 
                      src={room.host?.avatar_url || "/brand/default-avatar.png"} 
                      alt={room.title}
                      className="w-full h-full object-cover"
                    />
                    <div className="absolute inset-0 bg-gradient-to-t from-[#0B0F15] via-black/20 to-transparent" />
                    
                    <div className="absolute top-2 left-2 px-1.5 py-0.5 bg-[#FF6B6B]/90 backdrop-blur rounded-[4px] text-[9px] font-black text-white flex items-center gap-1">
                       <div className="w-1 h-1 rounded-full bg-white"></div> LIVE
                    </div>
                    
                    <div className="absolute top-2 right-2 px-1.5 py-0.5 bg-black/60 backdrop-blur rounded-[4px] text-[9px] font-bold text-white flex items-center gap-1">
                       {room.entry_fee > 0 ? `${room.entry_fee} ◆` : 'FREE'}
                    </div>

                    <Link href={`/profile/${room.host?.username}`} className="absolute bottom-2 left-2 flex items-center gap-1.5 group/host cursor-pointer z-20">
                       <Avatar src={room.host?.avatar_url} name={room.host?.name} size="xs" className="w-5 h-5 border border-white/20 group-hover/host:border-teal transition-colors" />
                       <span className="text-[11px] font-bold text-white/90 truncate max-w-[120px] group-hover/host:text-teal transition-colors">{room.host?.name}</span>
                    </Link>
                  </div>
                  <div className="p-3">
                    <h3 className="text-[12px] font-bold text-white/90 truncate">
                      {room.title}
                    </h3>
                  </div>
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
          <div className="px-8 py-20 flex flex-col items-center gap-6">
            <div className="w-24 h-24 rounded-full bg-white/5 flex items-center justify-center relative">
               <svg className="w-10 h-10 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
               </svg>
               <div className="absolute inset-0 rounded-full border border-teal/20 animate-ping"></div>
            </div>
            <div className="text-center">
              <p className="text-xl font-black text-white mb-2">Seed your feed</p>
              <p className="text-[14px] text-slate-500 max-w-xs mx-auto leading-relaxed">
                {activeTab === "following"
                  ? "You're not following anyone yet. Head to Discover to find amazing creators."
                  : "We're curating the perfect waves for you. While we wait, why not check out some trending creators?"}
              </p>
            </div>
            <Link 
              href="/discover"
              className="px-8 py-3 bg-teal text-slate-950 font-black rounded-full uppercase tracking-widest text-[12px] hover:scale-105 transition-transform"
            >
              Go to Discover
            </Link>
          </div>
        )}

        {/* Video Feed */}
        {!loading && waves.length > 0 && (
          <div className="px-8 space-y-10 pb-20">
            {waves.map((wave, idx) => {
              const elements = [
                <WaveCard
                  key={wave.id}
                  wave={wave}
                  onLike={handleLike}
                  onShare={handleShare}
                  onComment={handleComment}
                  onGift={handleGift}
                  onUnlock={handleUnlock}
                />
              ];

              // Inject promo cards every few items
              // Inject promo cards every few items
              if (idx === 1 && activeTab === "for-you" && trending?.active_challenge) {
                elements.push(<WaveChallengeCard key="challenge-injection" challenge={trending.active_challenge} />);
              }
              if (idx === 3 && activeTab === "for-you" && skillDrops.length > 0) {
                elements.push(<SkillDropPromoCard key="skilldrop-injection" drop={skillDrops[0]} />);
              }

              return elements;
            })}

            {/* Infinite Scroll Sentinel */}
            <div ref={observerTarget} className="h-32 flex items-center justify-center">
              {loadingMore && (
                <div className="flex items-center gap-3">
                  <div className="w-5 h-5 border-2 border-teal/20 border-t-teal rounded-full animate-spin" />
                  <span className="text-[12px] text-slate-500 font-bold uppercase tracking-widest">More Waves Incoming</span>
                </div>
              )}
              {!hasMore && waves.length > 0 && (
                <div className="flex flex-col items-center gap-2 opacity-30">
                  <div className="w-8 h-px bg-slate-700" />
                  <span className="text-[10px] text-slate-500 font-bold uppercase tracking-widest">End of the Ocean</span>
                </div>
              )}
            </div>
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
  onComment,
  onGift,
  onUnlock,
}: {
  wave: Wave;
  onLike: (id: string) => void;
  onShare: (id: string) => void;
  onComment: (id: string) => void;
  onGift: (id: string) => void;
  onUnlock: (id: string, amount: number) => void;
}) {
  return (
    <div className="relative aspect-[4/5] w-full max-h-[600px] mx-auto rounded-r24 overflow-hidden bg-slate-900 group shadow-2xl">
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
            <Link href={`/profile/${wave.user.username}`}>
              <Avatar src={wave.user.avatar_url} name={wave.user.name} size="lg" role={wave.user.role} className="border-2 border-teal/50" />
            </Link>
            <Link href={`/profile/${wave.user.username}`} className="group/creator">
              <div className="font-bold text-white text-[17px] group-hover/creator:text-teal transition-colors">{wave.user.name}</div>
              <div className="text-teal text-sm font-semibold opacity-80 group-hover/creator:opacity-100 transition-opacity">@{wave.user.username}</div>
            </Link>
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
                onClick={() => onUnlock(wave.id, wave.gated_drops)}
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
                onClick={() => onGift(wave.id)} 
                className="flex items-center gap-3 px-6 py-3.5 rounded-full bg-teal text-slate-950 font-black uppercase tracking-widest text-[13px] hover:scale-105 transition-all shadow-[0_8px_20px_rgba(26,158,117,0.3)] group/gift"
              >
                <svg className="w-5 h-5 animate-bounce-subtle" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Gift Drops
              </button>
            </>
          )}
        </div>

        {/* Actions Sidebar */}
        <div className="flex flex-col gap-6 items-center z-20 drop-shadow-[0_2px_10px_rgba(0,0,0,0.8)]">
          {/* Like */}
          <button
            onClick={() => onLike(wave.id)}
            className="flex flex-col items-center gap-1 group/action cursor-pointer"
          >
            <div className={`w-12 h-12 rounded-full glass-dark flex items-center justify-center transition-all ${wave.is_liked ? "text-red-400" : "group-hover/action:text-teal"}`}>
              <svg className="w-6 h-6 drop-shadow-md" fill={wave.is_liked ? "currentColor" : "none"} viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={wave.is_liked ? 0 : 2} d="M11.645 20.91l-.007-.003-.022-.012a15.247 15.247 0 01-.383-.218 25.18 25.18 0 01-4.244-3.17C4.688 15.36 2.25 12.174 2.25 8.25 2.25 5.322 4.714 3 7.688 3c1.74 0 3.124.588 4.312 1.57A6.981 6.981 0 0116.313 3c2.973 0 5.437 2.322 5.437 5.25 0 3.925-2.438 7.111-4.739 9.256a25.175 25.175 0 01-4.244 3.17 15.247 15.247 0 01-.383.219l-.022.012-.007.004-.003.001a.752.752 0 01-.704 0l-.003-.001z" />
              </svg>
            </div>
            <span className="text-[12px] font-bold text-white">{formatCount(wave.likes_count)}</span>
          </button>

          {/* Comment */}
          <div 
            onClick={() => onComment(wave.id)}
            className="flex flex-col items-center gap-1 group/action cursor-pointer"
          >
            <div className="w-12 h-12 rounded-full glass-dark flex items-center justify-center group-hover/action:text-teal transition-all">
              <svg className="w-6 h-6 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
            </div>
            <span className="text-[12px] font-bold text-white drop-shadow-sm">{formatCount(wave.comments_count)}</span>
          </div>

          {/* Share */}
          <button
            onClick={() => onShare(wave.id)}
            className="flex flex-col items-center gap-1 group/action cursor-pointer"
          >
            <div className="w-12 h-12 rounded-full glass-dark flex items-center justify-center group-hover/action:text-teal transition-all">
              <svg className="w-6 h-6 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
              </svg>
            </div>
            <span className="text-[12px] font-bold text-white drop-shadow-sm">{formatCount(wave.shares_count)}</span>
          </button>

          {/* Views */}
          <div className="flex flex-col items-center gap-1">
            <div className="w-12 h-12 rounded-full glass-dark flex items-center justify-center text-slate-400">
              <svg className="w-6 h-6 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
              </svg>
            </div>
            <span className="text-[12px] font-bold text-slate-400 drop-shadow-sm">{formatCount(wave.views_count)}</span>
          </div>
        </div>
      </div>
    </div>
  );
}

function WaveChallengeCard({ challenge }: { challenge: any }) {
  return (
    <div className="glass p-8 rounded-r24 border border-teal/20 relative overflow-hidden group cursor-pointer">
      <div className="absolute top-0 right-0 p-4">
        <div className="bg-teal text-slate-950 text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-tighter">
          Active Challenge
        </div>
      </div>
      
      <div className="relative z-10 flex flex-col gap-6">
        <div className="flex items-center gap-3">
           <div className="w-12 h-12 rounded-full bg-teal/20 flex items-center justify-center text-teal">
              <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
              </svg>
           </div>
           <div>
              <div className="text-[18px] font-black text-white">{challenge.title}</div>
              <div className="text-[12px] text-teal font-bold uppercase tracking-widest">{challenge.prize_pool.toLocaleString()} Drops Prize Pool</div>
           </div>
        </div>

        <p className="text-slate-400 text-[14px] leading-relaxed line-clamp-2">
          {challenge.description}
        </p>

        <div className="flex items-center gap-4">
           <span className="text-[12px] text-slate-500 font-bold">{challenge.participations_count || 0}+ Entries already</span>
        </div>

        <Link href={`/challenges/${challenge.id}`} className="w-full py-4 bg-teal text-slate-950 text-center font-black uppercase tracking-widest rounded-r16 hover:scale-[1.02] transition-transform">
           Enter Now
        </Link>
      </div>

      <div className="absolute -bottom-10 -right-10 w-48 h-48 bg-teal/5 blur-3xl group-hover:bg-teal/10 transition-all"></div>
    </div>
  );
}

function SkillDropPromoCard({ drop }: { drop: SkillDrop }) {
  return (
    <div className="glass p-8 rounded-r24 border border-violet-500/20 relative overflow-hidden group cursor-pointer">
      <div className="absolute top-0 right-0 p-4">
        <div className="bg-violet-500 text-white text-[10px] font-black px-3 py-1 rounded-full uppercase tracking-tighter">
          Skill Drop
        </div>
      </div>
      
      <div className="relative z-10 flex flex-col gap-6">
        <div className="flex items-center gap-4">
           <div className="w-20 h-20 rounded-r16 bg-slate-800 overflow-hidden shrink-0 border border-white/5">
              <img src={drop.thumbnail_url || "/api/placeholder/80/80"} alt={drop.title} className="w-full h-full object-cover" />
           </div>
           <div>
              <div className="text-[18px] font-black text-white group-hover:text-violet-400 transition-colors line-clamp-1">{drop.title}</div>
              <Link href={`/profile/${drop.user?.username}`} className="text-[13px] text-slate-500 font-bold hover:text-violet-400 transition-colors">by @{drop.user?.username}</Link>
           </div>
        </div>

        <div className="flex items-center justify-between p-4 bg-white/5 rounded-r16 border border-white/5">
           <div className="flex items-baseline gap-1">
              <span className="text-xl font-black text-white">{drop.price_drops}</span>
              <span className="text-[11px] font-bold text-violet-400 uppercase">Drops</span>
           </div>
           <Link href={`/market/${drop.id}`} className="px-6 py-2 bg-violet-500 text-white font-black uppercase tracking-widest text-[11px] rounded-full hover:bg-violet-600 transition-colors">
              Buy Now
           </Link>
        </div>
      </div>

      <div className="absolute -bottom-10 -right-10 w-48 h-48 bg-violet-500/5 blur-3xl group-hover:bg-violet-500/10 transition-all"></div>
    </div>
  );
}
