"use client";

import React, { useState, useEffect } from "react";
import { ShellLayout } from "@/components/layout/ShellLayout";
import { Avatar } from "@/components/ui/Avatar";
import { useAuth } from "@/context/AuthContext";
import { getDiscoveryFeed, likeWave, shareWave, followUser, type Wave } from "@/lib/api";
import { CommentSheet } from "@/components/waves/CommentSheet";
import { ShareSheet } from "@/components/waves/ShareSheet";
import { GiftSheet } from "@/components/waves/GiftSheet";

export default function WavesPage() {
  const { token } = useAuth();
  const [waves, setWaves] = useState<Wave[]>([]);
  const [loading, setLoading] = useState(true);
  const [activeIndex, setActiveIndex] = useState(0);
  const [showComments, setShowComments] = useState(false);
  const [showShare, setShowShare] = useState(false);
  const [showGifts, setShowGifts] = useState(false);
  const [activeWaveId, setActiveWaveId] = useState<string | null>(null);

  useEffect(() => {
    if (!token) {
      setLoading(false);
      return;
    }
    loadWaves();
  }, [token]);

  const loadWaves = (cursor?: string) => {
    getDiscoveryFeed(cursor, 10).then(res => {
      setWaves(prev => cursor ? [...prev, ...res.data] : res.data || []);
    }).catch(err => {
      console.error("Failed to load waves:", err);
    }).finally(() => {
      setLoading(false);
    });
  };

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
        w.user.id === userId ? { ...w, user: { ...w.user, is_following: true } } : w
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

  if (loading) {
    return (
      <ShellLayout>
        <div className="flex-1 flex items-center justify-center h-screen bg-[#050914]">
          <div className="w-10 h-10 border-2 border-teal/20 border-t-teal rounded-full animate-spin"></div>
        </div>
      </ShellLayout>
    );
  }

  return (
    <ShellLayout hideSidebarOnMobile>
      <div className="flex-1 relative bg-[#050914] overflow-hidden h-screen">
        {waves.length > 0 ? (
          <div className="h-full w-full relative snap-y snap-mandatory overflow-y-scroll no-scrollbar">
            {waves.map((wave, index) => (
              <div key={wave.id} className="h-full w-full relative snap-start">
                {/* Immersive Video Placeholder */}
                <div className="absolute inset-0 bg-slate-900">
                  {wave.thumbnail_url ? (
                    wave.thumbnail_url.endsWith('.mp4') ? (
                      <video 
                        src={wave.thumbnail_url} 
                        className="w-full h-full object-cover"
                        autoPlay={index === activeIndex}
                        loop
                        muted
                        playsInline
                      />
                    ) : (
                      <img 
                        src={wave.thumbnail_url} 
                        alt={wave.title} 
                        className="w-full h-full object-cover"
                      />
                    )
                  ) : (
                    <div className="w-full h-full bg-gradient-to-br from-teal-900/40 to-[#050914]" />
                  )}
                </div>

                {/* Gradients for text readability */}
                <div className="absolute inset-x-0 bottom-0 h-2/3 bg-gradient-to-t from-[#050914] via-[#050914]/40 to-transparent pointer-events-none" />
                <div className="absolute inset-x-0 top-0 h-32 bg-gradient-to-b from-[#050914]/60 to-transparent pointer-events-none" />

                {/* Content Overlays */}
                <div className="absolute inset-0 flex flex-col justify-end z-10 pointer-events-none">
                  
                  <div className="p-6 flex items-end justify-between pointer-events-auto pb-24">
                    {/* Bottom Left Info */}
                    <div className="flex-1 pr-16 max-w-[80%]">
                      <div className="text-teal font-bold text-[16px] mb-2 drop-shadow-md flex items-center gap-2">
                        @{wave.user.username}
                        {wave.user.role === 'pro' && <span className="text-[10px] bg-teal/20 text-teal px-1.5 py-0.5 rounded uppercase tracking-widest font-black">Pro</span>}
                      </div>
                      <p className="text-white/90 text-[15px] leading-relaxed mb-4 drop-shadow-md">
                        {wave.description || wave.title}
                      </p>
                      <div className="flex items-center gap-2 text-teal/80 text-[13px] font-bold">
                        <svg className="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M12 3v10.55c-.59-.34-1.27-.55-2-.55-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4V7h4V3h-6z"/>
                        </svg>
                        <div className="overflow-hidden w-48 relative">
                          <div className="whitespace-nowrap animate-[marquee_10s_linear_infinite]">
                            Original Sound - {wave.user.name}
                          </div>
                        </div>
                      </div>
                    </div>

                    {/* Right Actions Container */}
                    <div className="flex flex-col items-center gap-6 pb-4">
                      {/* Avatar Action */}
                      <div className="relative group cursor-pointer mb-2">
                        <Avatar 
                          src={wave.user.avatar_url} 
                          name={wave.user.name} 
                          size="md"
                          className="w-12 h-12 border-2 border-white/20 shadow-lg"
                        />
                        {!wave.user.is_following && (
                          <button 
                            onClick={() => handleFollow(wave.user.id)}
                            className="absolute -bottom-2 left-1/2 -translate-x-1/2 w-5 h-5 bg-teal text-black rounded-full flex items-center justify-center border border-black shadow-md hover:scale-110 transition-transform"
                          >
                            <svg className="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={3}>
                              <path strokeLinecap="round" strokeLinejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                          </button>
                        )}
                      </div>

                      <div onClick={() => handleLike(wave)}>
                        <ActionButton icon={<HeartIcon active={wave.is_liked} />} label={formatCount(wave.likes_count)} />
                      </div>
                      <div onClick={() => handleAction('comment', wave.id)}>
                        <ActionButton icon={<CommentIcon />} label={formatCount(wave.comments_count)} />
                      </div>
                      <div onClick={() => handleAction('share', wave.id)}>
                        <ActionButton icon={<ShareIcon />} label={formatCount(wave.shares_count)} />
                      </div>
                    </div>
                  </div>

                  {/* Gifts Button (Bottom Right positioned independently) */}
                  <div className="absolute right-6 bottom-32 pointer-events-auto flex flex-col items-center">
                    <button 
                      onClick={() => handleAction('gift', wave.id)}
                      className="w-[52px] h-[52px] bg-[#0A2E24] rounded-full flex items-center justify-center border border-teal/20 shadow-lg hover:bg-teal/20 transition-all group"
                    >
                      <svg className="w-7 h-7 text-teal group-hover:scale-110 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                         <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
                         <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M14.5 5a2.5 2.5 0 00-5 0" />
                      </svg>
                    </button>
                    <span className="text-[12px] font-bold text-teal mt-1">Gifts</span>
                  </div>

                  {/* Progress Bar Line */}
                  <div className="w-full h-[1px] bg-white/10 absolute bottom-0">
                    <div className="h-full bg-teal w-0 animate-[progress_15s_linear_infinite]" />
                  </div>
                </div>
              </div>
            ))}
          </div>
        ) : (
          <div className="flex-1 flex flex-col items-center justify-center gap-4 text-slate-500 h-full">
             <svg className="w-16 h-16 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
             </svg>
             <p className="font-bold">No Waves available right now</p>
          </div>
        )}
      </div>

      {/* Social Sheets */}
      {activeWaveId && (
        <>
          <CommentSheet 
            isOpen={showComments} 
            onClose={() => setShowComments(false)} 
            waveId={activeWaveId} 
          />
          <ShareSheet 
            isOpen={showShare} 
            onClose={() => setShowShare(false)} 
            waveId={activeWaveId} 
          />
          <GiftSheet 
            isOpen={showGifts} 
            onClose={() => setShowGifts(false)} 
            waveId={activeWaveId} 
          />
        </>
      )}
    </ShellLayout>
  );
}

function formatCount(n: number): string {
  if (n >= 1000000) return (n / 1000000).toFixed(1) + "M";
  if (n >= 1000) return (n / 1000).toFixed(1) + "K";
  return String(n);
}

function ActionButton({ icon, label }: { icon: React.ReactNode, label: string }) {
  return (
    <button className="flex flex-col items-center gap-1.5 group">
       <div className="w-12 h-12 rounded-full bg-white/10 backdrop-blur-md flex items-center justify-center text-white group-hover:bg-white/20 transition-all border border-white/5">
          {icon}
       </div>
       <span className="text-[12px] font-bold text-white/90 drop-shadow-md">{label}</span>
    </button>
  );
}

function HeartIcon({ active }: { active?: boolean }) {
  return (
    <svg className={`w-6 h-6 ${active ? 'text-red-500' : ''}`} fill={active ? "currentColor" : "none"} viewBox="0 0 24 24" stroke="currentColor">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
    </svg>
  );
}

function CommentIcon() {
  return (
    <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
    </svg>
  );
}

function ShareIcon() {
  return (
    <svg className="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
      <path d="M18 16.08c-.76 0-1.44.3-1.96.77L8.91 12.7c.05-.23.09-.46.09-.7s-.04-.47-.09-.7l7.05-4.11c.54.5 1.25.81 2.04.81 1.66 0 3-1.34 3-3s-1.34-3-3-3-3 1.34-3 3c0 .24.04.47.09.7L8.04 9.81C7.5 9.31 6.79 9 6 9c-1.66 0-3 1.34-3 3s1.34 3 3 3c.79 0 1.5-.31 2.04-.81l7.12 4.16c-.05.21-.08.43-.08.65 0 1.61 1.31 2.92 2.92 2.92 1.61 0 2.92-1.31 2.92-2.92s-1.31-2.92-2.92-2.92z"/>
    </svg>
  );
}
