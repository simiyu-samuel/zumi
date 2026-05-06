"use client";

import Link from "next/link";
import React, { useEffect, useState } from "react";
import { Sidebar, BottomNav } from "./Navigation";
import { Avatar } from "@/components/ui/Avatar";
import { CreateModal } from "./CreateModal";
import { getSuggestedUsers, followUser, getGatedRooms, getTrending, type SuggestedUser, type GatedRoom, type TrendingData } from "@/lib/api";
import { useAuth } from "@/context/AuthContext";

export function ShellLayout({ children, hideSidebarOnMobile = false }: { children: React.ReactNode, hideSidebarOnMobile?: boolean }) {
  const { token, user } = useAuth();
  const [suggested, setSuggested] = useState<SuggestedUser[]>([]);
  const [rooms, setRooms] = useState<GatedRoom[]>([]);
  const [trending, setTrending] = useState<TrendingData | null>(null);
  const [followedIds, setFollowedIds] = useState<Set<string>>(new Set());
  const [showCreateModal, setShowCreateModal] = useState(false);

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

    // Fetch trending data
    getTrending()
      .then((res) => setTrending(res))
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

  const toggleCreate = () => setShowCreateModal(!showCreateModal);

  return (
    <div className="flex h-screen overflow-hidden aurora-bg text-slate-200">
      <div className="flex w-full max-w-[1280px] mx-auto relative h-screen">
        {/* Sidebar for Desktop */}
        {!hideSidebarOnMobile && <Sidebar onCreateClick={toggleCreate} />}
        {hideSidebarOnMobile && (
          <div className="hidden lg:block h-full">
            <Sidebar onCreateClick={toggleCreate} />
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
        
        {/* User Header */}
        {user && (
          <Link href={`/profile/${user.username}`} className="flex items-center gap-3 group/header">
            <Avatar 
              src={user.avatar_url} 
              name={user.name} 
              size="md" 
              role={user.role}
              className="w-12 h-12 border-2 border-transparent group-hover/header:border-white/10 transition-all shadow-lg" 
            />
            <div className="flex-1 min-w-0">
              <div className="text-[15px] font-bold text-white truncate leading-tight group-hover/header:text-teal transition-colors">
                {user.name}
              </div>
              <div className="text-[13px] text-slate-500 truncate font-semibold">
                @{user.username}
              </div>
            </div>
          </Link>
        )}

        {/* Suggested Creators */}
        <div className="glass p-6 rounded-r12 border border-white/5 relative overflow-hidden group/widget">
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

        {/* Active Wave Challenge Widget */}
        {trending?.active_challenge && (
          <div className="relative overflow-hidden rounded-r12 group/challenge">
            <div className="absolute inset-0 bg-gradient-to-br from-indigo-600 to-indigo-900 opacity-90 group-hover/challenge:opacity-100 transition-opacity" />
            <div className="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/carbon-fibre.png')] opacity-10" />
            
            <div className="relative p-5 z-10">
              <div className="flex items-center justify-between mb-4">
                <span className="text-[9px] font-black uppercase tracking-[0.2em] bg-white/20 px-2 py-0.5 rounded backdrop-blur-md">Active Challenge</span>
                <span className="text-[11px] font-black text-indigo-200">
                  {Math.max(0, Math.ceil((new Date(trending.active_challenge.ends_at).getTime() - Date.now()) / (1000 * 60 * 60 * 24)))} Days Left
                </span>
              </div>
              
              <h4 className="text-[18px] font-head font-black text-white leading-tight mb-2">
                {trending.active_challenge.title}
              </h4>
              
              <div className="flex items-center gap-3 mt-4">
                <div className="flex-1">
                  <div className="text-[10px] text-indigo-200 font-bold uppercase tracking-wider">Prize Pool</div>
                  <div className="text-[16px] font-black text-white flex items-center gap-1">
                    {trending.active_challenge.prize_pool.toLocaleString()} <span className="text-teal">◆</span>
                  </div>
                </div>
                <Link 
                  href={`/challenges/${trending.active_challenge.id}`}
                  className="bg-white text-indigo-900 px-4 py-2 rounded-full text-[11px] font-black uppercase tracking-widest hover:scale-105 transition-transform active:scale-95 shadow-xl"
                >
                  Join
                </Link>
              </div>
            </div>
          </div>
        )}

        {/* Top Gated Rooms */}
        <div className="glass p-6 rounded-r12 border border-white/5 relative overflow-hidden group/widget">
          <div className="flex justify-between items-center mb-6">
            <h3 className="text-[11px] font-bold text-slate-500 uppercase tracking-widest leading-none">
              Top Gated Rooms
            </h3>
          </div>
          <div className="space-y-4">
            {rooms.slice(0, 3).map((room, idx) => (
              <div key={room.id} className="flex items-center gap-4 group/room cursor-pointer">
                <div className="text-teal font-black text-[15px] w-4">{idx + 1}</div>
                <div className="text-[13px] font-bold text-white group-hover/room:text-teal transition-colors truncate flex-1">
                  {room.title}
                </div>
                <div className="text-[10px] bg-white/5 text-slate-300 px-2 py-0.5 rounded-sm font-black flex items-center gap-1 shrink-0">
                  {room.entry_fee_drops} <span className="text-teal">◆</span>
                </div>
              </div>
            ))}
            {rooms.length === 0 && <div className="text-[11px] text-slate-500 italic">No live rooms right now</div>}
          </div>
        </div>

        {/* Trending on Zumi */}
        <div className="py-2">
          <h3 className="text-[13px] font-bold text-slate-400 mb-4">Trending on Zumi</h3>
          <div className="space-y-5">
            {(trending?.hashtags || []).map((t) => (
              <div key={t.tag} className="group/tag cursor-pointer">
                <div className="text-[15px] font-black text-teal hover:underline transition-all truncate">
                  {t.tag}
                </div>
                <div className="text-[11px] font-bold text-slate-500 truncate mt-0.5">
                  {t.label}
                </div>
              </div>
            ))}
            {(!trending?.hashtags || trending.hashtags.length === 0) && (
              <div className="text-[11px] text-slate-500 italic">Exploring the zeitgeist...</div>
            )}
          </div>
        </div>

        {/* Footer */}
        <div className="pt-4 border-t border-white/5 text-[10px] font-bold text-slate-500 uppercase tracking-widest space-y-4">
          <div className="flex gap-4 flex-wrap">
            <a href="#" className="hover:text-teal transition-colors">Privacy</a>
            <a href="#" className="hover:text-teal transition-colors">Terms</a>
            <a href="#" className="hover:text-teal transition-colors">Guidelines</a>
            <a href="#" className="hover:text-teal transition-colors">Support</a>
          </div>
          <div className="text-slate-600 normal-case tracking-normal">
            © 2024 ZUMI DIGITAL INC. ALL RIGHTS RESERVED.
          </div>
        </div>
      </aside>
    </div>

      {/* Bottom Nav for Mobile */}
      <BottomNav onCreateClick={toggleCreate} />

      {/* Global Create Modal */}
      <CreateModal isOpen={showCreateModal} onClose={() => setShowCreateModal(false)} />
    </div>
  );
}

