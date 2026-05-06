"use client";

import Link from "next/link";
import React, { useEffect, useState } from "react";
import { Sidebar, BottomNav } from "./Navigation";
import { Avatar } from "@/components/ui/Avatar";
import { getSuggestedUsers, followUser, getGatedRooms, type SuggestedUser, type GatedRoom } from "@/lib/api";
import { useAuth } from "@/context/AuthContext";

export function ShellLayout({ children }: { children: React.ReactNode }) {
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
      {/* Sidebar for Desktop */}
      <Sidebar />

      {/* Main Content Area */}
      <main className="flex-1 overflow-y-auto no-scrollbar scroll-smooth relative">
        <div className="w-full max-w-[600px] mx-auto glass-dark min-h-screen border-x border-white/5 flex flex-col shadow-2xl">
          {children}
        </div>
      </main>

      {/* Right Sidebar for Desktop (Discovery, Gated Rooms, Leaderboard) */}
      <aside className="hidden xl:flex flex-col w-85 h-screen sticky top-0 p-8 space-y-8 overflow-y-auto no-scrollbar">
        {/* Suggested Creators */}
        <div className="glass p-6 rounded-r24">
          <div className="flex justify-between items-center mb-5">
            <h3 className="font-head font-extrabold text-[15px] tracking-tight uppercase text-teal">
              Suggested Creators
            </h3>
            <button className="text-[12px] text-slate-400 hover:text-white transition-colors">
              See all
            </button>
          </div>
          <div className="space-y-3">
            {suggested.length === 0 &&
              [1, 2, 3].map((i) => (
                <div key={i} className="flex items-center gap-3">
                  <div className="w-9 h-9 rounded-full bg-slate-800 animate-pulse" />
                  <div className="flex-1 space-y-2">
                    <div className="h-4 w-24 bg-slate-800 rounded animate-pulse" />
                    <div className="h-3 w-16 bg-slate-800/50 rounded animate-pulse" />
                  </div>
                </div>
              ))}
            {suggested.map((u) => {
              const isFollowed = followedIds.has(u.id);
              return (
                <div key={u.id} className="flex items-center gap-3 group">
                  <Link href={`/profile/${u.username}`} className="flex items-center gap-3 flex-1 min-w-0">
                    <Avatar 
                      src={u.avatar_url} 
                      name={u.name} 
                      size="sm" 
                      role={u.role}
                      className="w-9 h-9 ring-2 ring-transparent group-hover:ring-teal/30 transition-all" 
                    />
                    <div className="flex-1 min-w-0">
                      <div className="text-[13px] font-bold text-white truncate group-hover:text-teal transition-colors leading-tight">
                        {u.name}
                      </div>
                      <div className="text-[11px] text-slate-500 truncate">
                        @{u.username}
                      </div>
                    </div>
                  </Link>
                  <button
                    onClick={() => handleFollow(u.id)}
                    disabled={isFollowed}
                    className={`px-3 py-1 rounded-full text-[12px] font-bold transition-all ${
                      isFollowed
                        ? "bg-teal/20 text-teal border border-teal/30 cursor-default"
                        : "bg-white/5 border border-white/10 hover:bg-white hover:text-black"
                    }`}
                  >
                    {isFollowed ? "Following" : "Follow"}
                  </button>
                </div>
              );
            })}
          </div>
        </div>

        {/* Top Gated Rooms */}
        <div className="glass p-6 rounded-r24">
          <div className="flex justify-between items-center mb-5">
            <h3 className="font-head font-extrabold text-[15px] tracking-tight uppercase text-teal">
              Top Gated Rooms
            </h3>
          </div>
          <div className="space-y-3">
            {rooms.length === 0 && (
              <p className="text-[11px] text-slate-500 italic">No rooms active</p>
            )}
            {rooms.slice(0, 3).map((room) => (
              <div key={room.id} className="flex flex-col gap-1.5 p-2.5 rounded-r12 bg-white/5 border border-white/5 hover:border-teal/30 transition-all cursor-pointer group">
                <div className="flex justify-between items-start gap-2">
                  <div className="text-[13px] font-bold text-white group-hover:text-teal transition-colors truncate flex-1">
                    {room.title}
                  </div>
                  <div className="flex items-center gap-1 px-1.5 py-0.5 rounded-full bg-teal/20 text-teal text-[9px] font-black uppercase shrink-0">
                    <svg className="w-2 h-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    {room.entry_fee_drops}
                  </div>
                </div>
                <Link href={`/profile/${room.host?.username}`} className="flex items-center gap-2 group/host">
                   <Avatar src={room.host?.avatar_url} name={room.host?.name} size="sm" role={room.host?.role} className="w-4 h-4 text-[7px]" />
                   <span className="text-[10px] text-slate-500 group-hover/host:text-teal transition-colors">by {room.host?.name}</span>
                </Link>
              </div>
            ))}
          </div>
        </div>
      </aside>

      {/* Bottom Nav for Mobile */}
      <BottomNav />
    </div>
  );
}
