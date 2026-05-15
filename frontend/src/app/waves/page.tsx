"use client";

import React, { useEffect, useRef, useState } from "react";
import { ShellLayout } from "@/components/layout/ShellLayout";
import { Avatar } from "@/components/ui/Avatar";
import { useAuth } from "@/context/AuthContext";
import { getDiscoveryFeed, likeWave, followUser, type Wave } from "@/lib/api";
import { CommentSheet } from "@/components/waves/CommentSheet";
import { ShareSheet } from "@/components/waves/ShareSheet";
import { GiftSheet } from "@/components/waves/GiftSheet";
import Link from "next/link";

export default function WavesPage() {
  const { user } = useAuth();
  const [waves, setWaves] = useState<Wave[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [activeIndex, setActiveIndex] = useState(0);
  const [showComments, setShowComments] = useState(false);
  const [showShare, setShowShare] = useState(false);
  const [showGifts, setShowGifts] = useState(false);
  const [activeWaveId, setActiveWaveId] = useState<string | null>(null);
  const scrollerRef = useRef<HTMLDivElement | null>(null);

  useEffect(() => {
    async function fetchWaves() {
      setLoading(true);
      setError(null);
      try {
        const res = await getDiscoveryFeed(undefined, 15);

        let items: Wave[] = [];
        if (res && Array.isArray(res.data)) {
          items = res.data;
        } else if (Array.isArray(res)) {
          items = res;
        }

        setWaves(items);
      } catch (err: unknown) {
        console.error("[WavesPage] Fetch error:", err);
        setError(err instanceof Error ? err.message : "Failed to load");
      } finally {
        setLoading(false);
      }
    }
    fetchWaves();
  }, []);

  const handleLike = async (wave: Wave) => {
    try {
      await likeWave(wave.id);
      setWaves(prev => prev.map(w =>
        w.id === wave.id
          ? { ...w, is_liked: !w.is_liked, likes_count: w.is_liked ? w.likes_count - 1 : w.likes_count + 1 }
          : w
      ));
    } catch (err) {
      console.error("Like error:", err);
    }
  };

  const handleFollow = async (userId: string) => {
    try {
      await followUser(userId);
      setWaves(prev => prev.map(w =>
        w.user?.id === userId ? { ...w, user: { ...w.user, is_following: true } } : w
      ));
    } catch (err) {
      console.error("Follow error:", err);
    }
  };

  const handleAction = (type: 'comment' | 'share' | 'gift', waveId: string) => {
    setActiveWaveId(waveId);
    if (type === 'comment') setShowComments(true);
    if (type === 'share') setShowShare(true);
    if (type === 'gift') setShowGifts(true);
  };

  const onScroll = (e: React.UIEvent<HTMLDivElement>) => {
    const el = e.currentTarget;
    const height = el.clientHeight;
    if (height === 0) return;
    const index = Math.round(el.scrollTop / height);
    if (index !== activeIndex) setActiveIndex(index);
  };

  useEffect(() => {
    const scroller = scrollerRef.current;
    if (!scroller) return;

    scroller.scrollTo({ top: 0, behavior: "auto" });
    setActiveIndex(0);
  }, [waves.length]);

  // ─── Loading State ────────────────────────────────────────────────────
  if (loading) {
    return (
      <ShellLayout disableMainScroll>
        <div className="py-20 flex flex-col items-center gap-4">
          <div className="w-10 h-10 border-2 border-teal/20 border-t-teal rounded-full animate-spin" />
          <p className="text-sm text-slate-500 font-bold uppercase tracking-widest">Loading Waves...</p>
        </div>
      </ShellLayout>
    );
  }

  // ─── Error State ──────────────────────────────────────────────────────
  if (error) {
    return (
      <ShellLayout disableMainScroll>
        <div className="py-20 flex flex-col items-center gap-4">
          <p className="text-red-400 font-bold">{error}</p>
          <button onClick={() => window.location.reload()} className="px-4 py-2 bg-teal/20 text-teal rounded-full text-sm font-bold">Retry</button>
        </div>
      </ShellLayout>
    );
  }

  // ─── Empty State ──────────────────────────────────────────────────────
  if (waves.length === 0) {
    return (
      <ShellLayout disableMainScroll>
        <div className="py-20 flex flex-col items-center gap-4">
          <svg className="w-16 h-16 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
          </svg>
          <p className="text-slate-500 font-bold text-lg">No Waves Found</p>
          <p className="text-slate-600 text-sm">Check console for [WavesPage] debug logs</p>
          <button onClick={() => window.location.reload()} className="px-4 py-2 bg-teal/20 text-teal rounded-full text-sm font-bold mt-2">Retry</button>
        </div>
      </ShellLayout>
    );
  }

  // ─── Immersive Waves Feed ─────────────────────────────────────────────
  return (
    <ShellLayout hideSidebarOnMobile disableMainScroll>
      {/* Immersive snap-scroll container */}
      <div
        ref={scrollerRef}
        className="no-scrollbar flex-1"
        onScroll={onScroll}
        style={{
          width: '100%',
          height: '100%',
          overflowY: 'scroll',
          scrollSnapType: 'y mandatory',
          background: '#050914',
          overscrollBehaviorY: 'contain',
          WebkitOverflowScrolling: 'touch',
          touchAction: 'pan-y',
        }}
      >
        {waves.map((wave, index) => (
          <div
            key={wave.id}
            style={{
              height: '100dvh',
              minHeight: '100dvh',
              width: '100%',
              position: 'relative',
              scrollSnapAlign: 'start',
            }}
          >
            {/* Video / Image Background */}
            <div className="absolute inset-0 bg-slate-900">
              <WaveMedia url={wave.thumbnail_url} title={wave.title} isActive={index === activeIndex} />
            </div>

            {/* Gradient overlays for text readability */}
            <div className="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-[#050914] via-[#050914]/60 to-transparent pointer-events-none" />
            <div className="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-[#050914]/60 to-transparent pointer-events-none" />

            {/* Wave Title Indicator (top) */}
            <div className="absolute top-12 left-0 right-0 flex flex-col items-center z-20 pointer-events-none px-6">
              <span className="text-[10px] font-black text-teal uppercase tracking-[0.3em] mb-1 drop-shadow-lg">
                Discovery
              </span>
              <h2 className="text-white font-head font-black text-[18px] uppercase tracking-tight text-center drop-shadow-2xl line-clamp-1 max-w-xs">
                {wave.title}
              </h2>
            </div>

            {/* Content Overlay */}
            <div className="absolute inset-0 flex flex-col justify-end z-10 pointer-events-none">
              <div className="p-6 flex items-end justify-between pointer-events-auto pb-20 lg:pb-24">
                {/* Bottom Left - Creator Info */}
                <div className="flex-1 pr-16 max-w-[75%]">
                  <Link href={`/profile/${wave.user?.username}`} className="text-teal font-bold text-[16px] mb-2 drop-shadow-lg flex items-center gap-2 hover:underline">
                    @{wave.user?.username || 'unknown'}
                    {wave.user?.role === 'pro' && (
                      <span className="text-[10px] bg-teal/20 text-teal px-1.5 py-0.5 rounded uppercase tracking-widest font-black">Pro</span>
                    )}
                  </Link>
                  <p className="text-white/90 text-[15px] leading-relaxed mb-3 drop-shadow-lg line-clamp-3">
                    {wave.description || wave.title}
                  </p>
                  <div className="flex items-center gap-2 text-teal/80 text-[13px] font-bold">
                    <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                      <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z" />
                    </svg>
                    <span className="truncate">Original Sound - {wave.user?.name || 'Zumi Creator'}</span>
                  </div>
                </div>

                {/* Right Side - Action Buttons */}
                <div className="flex flex-col items-center gap-5 pb-4">
                  {/* Creator Avatar */}
                  <div className="relative group mb-2">
                    <Link href={`/profile/${wave.user?.username}`}>
                      <Avatar
                        src={wave.user?.avatar_url}
                        name={wave.user?.name || 'Z'}
                        size="md"
                        className="w-12 h-12 border-2 border-white/20 shadow-lg cursor-pointer"
                      />
                    </Link>
                    {wave.user && !wave.user.is_following && wave.user.id !== user?.id && (
                      <button
                        onClick={() => handleFollow(wave.user.id)}
                        className="absolute -bottom-2 left-1/2 -translate-x-1/2 w-5 h-5 bg-teal text-black rounded-full flex items-center justify-center border border-black shadow-md hover:scale-110 transition-transform z-10"
                      >
                        <svg className="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={3}>
                          <path strokeLinecap="round" strokeLinejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                      </button>
                    )}
                  </div>

                  {/* Like */}
                  <button onClick={() => handleLike(wave)} className="flex flex-col items-center gap-1.5 group">
                    <div className="w-12 h-12 rounded-full bg-white/10 backdrop-blur-md flex items-center justify-center group-hover:bg-white/20 transition-all border border-white/5">
                      <svg className={`w-6 h-6 ${wave.is_liked ? 'text-red-500' : 'text-white'}`} fill={wave.is_liked ? "currentColor" : "none"} viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                      </svg>
                    </div>
                    <span className="text-[12px] font-bold text-white/90 drop-shadow-md">{formatCount(wave.likes_count)}</span>
                  </button>

                  {/* Comment */}
                  <button onClick={() => handleAction('comment', wave.id)} className="flex flex-col items-center gap-1.5 group">
                    <div className="w-12 h-12 rounded-full bg-white/10 backdrop-blur-md flex items-center justify-center text-white group-hover:bg-white/20 transition-all border border-white/5">
                      <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                      </svg>
                    </div>
                    <span className="text-[12px] font-bold text-white/90 drop-shadow-md">{formatCount(wave.comments_count)}</span>
                  </button>

                  {/* Share */}
                  <button onClick={() => handleAction('share', wave.id)} className="flex flex-col items-center gap-1.5 group">
                    <div className="w-12 h-12 rounded-full bg-white/10 backdrop-blur-md flex items-center justify-center text-white group-hover:bg-white/20 transition-all border border-white/5">
                      <svg className="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z" />
                      </svg>
                    </div>
                    <span className="text-[12px] font-bold text-white/90 drop-shadow-md">{formatCount(wave.shares_count)}</span>
                  </button>

                  {/* Gift */}
                  <button onClick={() => handleAction('gift', wave.id)} className="flex flex-col items-center gap-1.5 group">
                    <div className="w-[52px] h-[52px] bg-[#0A2E24] rounded-full flex items-center justify-center border border-teal/20 shadow-lg group-hover:bg-teal/20 transition-all">
                      <svg className="w-7 h-7 text-teal group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                      </svg>
                    </div>
                    <span className="text-[12px] font-bold text-teal drop-shadow-md">Gifts</span>
                  </button>
                </div>
              </div>

              {/* Progress Bar */}
              <div className="w-full h-[2px] bg-white/10">
                <div className="h-full bg-teal" style={{ animation: 'progress 15s linear infinite' }} />
              </div>
            </div>
          </div>
        ))}
      </div>

      {/* Social Sheets - mount each only when its state is active */}
      {activeWaveId && showComments && (
        <CommentSheet onClose={() => { setShowComments(false); setActiveWaveId(null); }} waveId={activeWaveId} />
      )}
      {activeWaveId && showShare && (
        <ShareSheet onClose={() => { setShowShare(false); setActiveWaveId(null); }} waveId={activeWaveId} />
      )}
      {activeWaveId && showGifts && (
        <GiftSheet onClose={() => { setShowGifts(false); setActiveWaveId(null); }} waveId={activeWaveId} />
      )}
    </ShellLayout>
  );
}

function formatCount(n: number): string {
  if (n >= 1000000) return (n / 1000000).toFixed(1) + "M";
  if (n >= 1000) return (n / 1000).toFixed(1) + "K";
  return String(n);
}

function WaveMedia({ url, title, isActive }: { url: string | null; title: string; isActive: boolean }) {
  const videoRef = React.useRef<HTMLVideoElement>(null);

  React.useEffect(() => {
    const video = videoRef.current;
    if (!video) return;

    if (isActive) {
      video.play().catch(() => {
        // Autoplay blocked by browser - this is expected
      });
    } else {
      video.pause();
      video.currentTime = 0;
    }
  }, [isActive]);

  if (!url) {
    return (
      <div className="w-full h-full bg-gradient-to-br from-teal/20 via-indigo-900/30 to-slate-950 flex items-center justify-center">
        <svg className="w-20 h-20 text-teal/20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
        </svg>
      </div>
    );
  }

  const isVideo = url.includes('.mp4') || url.includes('.webm') || url.includes('video');

  if (isVideo) {
    return (
      <video
        ref={videoRef}
        src={url}
        className="w-full h-full object-cover"
        loop
        muted
        playsInline
      />
    );
  }

  return (
    <img
      src={url}
      alt={title}
      className="w-full h-full object-cover"
    />
  );
}
