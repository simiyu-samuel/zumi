"use client";

import React, { useState, useEffect } from "react";
import { ShellLayout } from "@/components/layout/ShellLayout";
import { Avatar } from "@/components/ui/Avatar";
import { useAuth } from "@/context/AuthContext";
import { getDiscoveryFeed, type Wave } from "@/lib/api";

export default function WavesPage() {
  const { token } = useAuth();
  const [waves, setWaves] = useState<Wave[]>([]);
  const [loading, setLoading] = useState(true);
  const [activeIndex, setActiveIndex] = useState(0);

  useEffect(() => {
    if (!token) return;
    getDiscoveryFeed(undefined, 10).then(res => {
      setWaves(res.data || []);
      setLoading(false);
    });
  }, [token]);

  if (loading) {
    return (
      <ShellLayout>
        <div className="flex-1 flex items-center justify-center">
          <div className="w-10 h-10 border-2 border-teal/20 border-t-teal rounded-full animate-spin"></div>
        </div>
      </ShellLayout>
    );
  }

  return (
    <ShellLayout hideSidebarOnMobile>
      <div className="flex-1 relative bg-black overflow-hidden h-full">
        {waves.length > 0 ? (
          <div className="h-full w-full relative">
            {/* Immersive Video Placeholder */}
            <div className="absolute inset-0 bg-gradient-to-b from-slate-900 to-black">
              {waves[activeIndex].thumbnail_url && (
                <img 
                  src={waves[activeIndex].thumbnail_url} 
                  alt={waves[activeIndex].title} 
                  className="w-full h-full object-cover opacity-60"
                />
              )}
            </div>

            {/* Content Overlays */}
            <div className="absolute inset-0 flex flex-col justify-between z-10">
              {/* Top Tabs */}
              <div className="pt-6 flex justify-center gap-8">
                <button className="text-[15px] font-black text-white border-b-2 border-teal pb-1">FOR YOU</button>
                <button className="text-[15px] font-bold text-white/40 hover:text-white transition-colors pb-1">FOLLOWING</button>
              </div>

              {/* Bottom Info & Sidebar */}
              <div className="p-6 pb-24 md:pb-8 flex justify-between items-end">
                <div className="flex-1 max-w-[80%]">
                  <div className="flex items-center gap-3 mb-4">
                    <Avatar 
                      src={waves[activeIndex].user.avatar_url} 
                      name={waves[activeIndex].user.name} 
                      size="lg" 
                      role={waves[activeIndex].user.role}
                      className="border-2 border-teal"
                    />
                    <div>
                      <div className="font-black text-white text-[17px]">{waves[activeIndex].user.name}</div>
                      <div className="text-teal text-[13px] font-bold">@{waves[activeIndex].user.username}</div>
                    </div>
                    <button className="ml-2 px-3 py-1 bg-teal text-slate-950 text-[11px] font-black rounded-full uppercase tracking-widest">Follow</button>
                  </div>
                  <p className="text-white text-[15px] leading-relaxed mb-4 line-clamp-3">
                    {waves[activeIndex].description || waves[activeIndex].title}
                  </p>
                  <div className="flex items-center gap-2 text-white/60 text-[13px] font-bold">
                    <svg className="w-4 h-4 animate-spin-slow" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                       <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3" />
                    </svg>
                    <span className="overflow-hidden whitespace-nowrap">Original Sound - {waves[activeIndex].user.name}</span>
                  </div>
                </div>

                {/* Right Actions */}
                <div className="flex flex-col gap-6 items-center">
                  <ActionButton icon={<HeartIcon active={waves[activeIndex].is_liked} />} label={waves[activeIndex].likes_count.toLocaleString()} />
                  <ActionButton icon={<CommentIcon />} label={waves[activeIndex].comments_count.toLocaleString()} />
                  <ActionButton icon={<ShareIcon />} label={waves[activeIndex].shares_count.toLocaleString()} />
                  <ActionButton 
                    icon={
                      <div className="w-12 h-12 rounded-full bg-teal/20 flex items-center justify-center text-teal border border-teal/30 group-hover:bg-teal group-hover:text-slate-950 transition-all shadow-[0_0_15px_rgba(26,158,117,0.3)]">
                        <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                      </div>
                    } 
                    label="Gift" 
                  />
                </div>
              </div>
            </div>
          </div>
        ) : (
          <div className="flex-1 flex flex-col items-center justify-center gap-4 text-slate-500">
             <svg className="w-16 h-16 opacity-20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
             </svg>
             <p className="font-bold">No Waves available right now</p>
          </div>
        )}
      </div>
    </ShellLayout>
  );
}

function ActionButton({ icon, label }: { icon: React.ReactNode, label: string }) {
  return (
    <button className="flex flex-col items-center gap-1 group">
       <div className="w-12 h-12 rounded-full bg-white/5 backdrop-blur-md flex items-center justify-center text-white group-hover:bg-white/10 transition-all border border-white/10">
          {icon}
       </div>
       <span className="text-[12px] font-bold text-white drop-shadow-lg">{label}</span>
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
    <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
    </svg>
  );
}
