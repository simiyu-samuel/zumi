"use client";

import Link from "next/link";
import React, { useEffect, useState } from "react";
import { Sidebar, BottomNav } from "./Navigation";
import { Avatar } from "@/components/ui/Avatar";
import { getSuggestedUsers, followUser, getGatedRooms, type SuggestedUser, type GatedRoom } from "@/lib/api";
import { useAuth } from "@/context/AuthContext";

export function ShellLayout({ children, hideSidebarOnMobile = false }: { children: React.ReactNode, hideSidebarOnMobile?: boolean }) {
  const { token } = useAuth();
  const [suggested, setSuggested] = useState<SuggestedUser[]>([]);
  const [rooms, setRooms] = useState<GatedRoom[]>([]);
  const [followedIds, setFollowedIds] = useState<Set<string>>(new Set());

  useEffect(() => {
    if (!token) return;
    
    // Fetch suggested creators
    getSuggestedUsers(5)
      .then((res) => setSuggested(res.data || []))
      .catch(() => {});

    // Fetch top gated rooms
    getGatedRooms()
      .then((res) => setRooms(res.data || []))
      .catch(() => {});
  }, [token]);

  const handleFollow = async (userId: string) => {
    try {
      await followUser(userId);
      setFollowedIds((prev) => new Set(prev).add(userId));
    } catch (err) {
      console.error("Follow error:", err);
    }
  };

  return (
    <div className="flex h-screen overflow-hidden aurora-bg text-slate-200">
      <div className="flex w-full max-w-[1280px] mx-auto relative h-screen">
        {/* Sidebar for Desktop */}
        {!hideSidebarOnMobile && <Sidebar />}
        {hideSidebarOnMobile && (
          <div className="hidden lg:block h-full">
            <Sidebar />
          </div>
        )}

        {/* Main Content Area */}
        <main className="flex-1 h-full overflow-y-auto no-scrollbar relative border-x border-white/5 bg-slate-950/20">
          <div className="w-full max-w-[600px] mx-auto flex flex-col relative z-10">
            {children}
          </div>
        </main>

        {/* Right Sidebar for Desktop */}
        <aside className="hidden xl:flex flex-col w-[350px] h-full py-8 px-6 space-y-6 overflow-y-auto no-scrollbar border-l border-white/5">
        {/* Suggested Creators */}
        <div className="glass p-8 rounded-r12 border border-white/5 relative overflow-hidden group/widget">
          <div className="flex justify-between items-center mb-6">
            <h3 className="font-head font-black text-[17px] tracking-tight uppercase text-white leading-none">
              Suggested <span className="text-teal">Creators</span>
            </h3>
            <Link href="/discover" className="text-[11px] font-black text-slate-500 hover:text-teal uppercase tracking-widest transition-all">
              See all
            </Link>
          </div>
          <div className="space-y-5">
            {suggested.map((u) => {
              const isFollowed = followedIds.has(u.id);
              return (
                <div key={u.id} className="flex items-center gap-4 group">
                  <Link href={`/profile/${u.username}`} className="flex items-center gap-3 flex-1 min-w-0">
                    <Avatar 
                      src={u.avatar_url} 
                      name={u.name} 
                      size="sm" 
                      role={u.role}
                      className="w-10 h-10 border-2 border-white/5 group-hover:border-teal/30 transition-all shadow-lg" 
                    />
                    <div className="flex-1 min-w-0">
                      <div className="text-[14px] font-bold text-white truncate group-hover:text-teal transition-colors leading-tight">
                        {u.name}
                      </div>
                      <div className="text-[11px] text-slate-500 truncate font-semibold">
                        @{u.username}
                      </div>
                    </div>
                  </Link>
                  <button 
                    onClick={() => handleFollow(u.id)}
                    disabled={isFollowed}
                    className={`px-4 py-1.5 rounded-full text-[11px] font-black uppercase tracking-wider transition-all ${
                      isFollowed 
                        ? 'bg-white/5 text-slate-500 border border-white/10' 
                        : 'bg-teal/10 text-teal border border-teal/20 hover:bg-teal hover:text-slate-950'
                    }`}
                  >
                    {isFollowed ? 'Followed' : 'Follow'}
                  </button>
                </div>
              );
            })}
          </div>
        </div>

        {/* Trending on Zumi */}
        <div className="glass p-8 rounded-r12 border border-white/5 relative overflow-hidden group/widget">
          <div className="flex justify-between items-center mb-6">
            <h3 className="font-head font-black text-[17px] tracking-tight uppercase text-white leading-none">
              Trending <span className="text-teal">on Zumi</span>
            </h3>
          </div>
          <div className="space-y-6">
            {[
              { tag: "#SkillDrops", sub: "12.5k Drops this hour" },
              { tag: "#WaveChallenge", sub: "Trending in Creative" },
              { tag: "#ZumiFlow", sub: "New community event" },
              { tag: "#DigitalDeepSea", sub: "Aesthetic of the month" }
            ].map((t) => (
              <div key={t.tag} className="group/tag cursor-pointer">
                <div className="text-[15px] font-black text-white group-hover/tag:text-teal transition-colors">
                  {t.tag}
                </div>
                <div className="text-[11px] font-bold text-slate-500 uppercase tracking-widest mt-1">
                  {t.sub}
                </div>
              </div>
            ))}
          </div>
        </div>
        {/* Challenge Highlight */}
        <div className="glass p-8 rounded-r12 border border-teal/10 relative overflow-hidden group/challenge cursor-pointer">
           <div className="relative z-10">
              <div className="text-teal text-[10px] font-black uppercase tracking-widest mb-2">Active Challenge</div>
              <h4 className="text-[18px] font-black text-white mb-3">#DigitalDeepSea</h4>
              <p className="text-[12px] text-slate-500 leading-relaxed mb-5">
                Interpret bioluminescent life in any digital medium. Win 5,000 Drops.
              </p>
              <button className="w-full py-3 bg-teal text-slate-950 rounded-r12 text-[11px] font-black uppercase tracking-widest transition-all hover:scale-[1.02]">
                Enter Now
              </button>
           </div>
           <div className="absolute -bottom-10 -right-10 w-32 h-32 bg-teal/10 blur-3xl"></div>
        </div>
      </aside>
    </div>

      {/* Bottom Nav for Mobile */}
      <BottomNav />
    </div>
  );
}
