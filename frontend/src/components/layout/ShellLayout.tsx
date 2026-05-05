"use client";

import Link from "next/link";
import React, { useEffect, useState } from "react";
import { Sidebar, BottomNav } from "./Navigation";
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
    <div className="flex min-h-screen aurora-bg text-slate-200">
      {/* Sidebar for Desktop */}
      <Sidebar />

      {/* Main Content Area */}
      <main className="flex-1 flex flex-col min-h-screen feed-container">
        <div className="flex-1 w-full max-w-[600px] mx-auto glass-dark min-h-screen border-x border-white/5 flex flex-col relative shadow-2xl">
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
          <div className="space-y-5">
            {suggested.length === 0 &&
              [1, 2, 3].map((i) => (
                <div key={i} className="flex items-center gap-3">
                  <div className="w-11 h-11 rounded-full bg-slate-800 animate-pulse" />
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
                    <div className="w-11 h-11 rounded-full bg-teal/20 border border-teal/30 overflow-hidden flex items-center justify-center font-bold text-teal text-sm ring-2 ring-transparent group-hover:ring-teal/30 transition-all shrink-0">
                      {u.avatar_url ? (
                        <img src={u.avatar_url} alt={u.name} className="w-full h-full object-cover" />
                      ) : (
                        u.name.charAt(0)
                      )}
                    </div>
                    <div className="flex-1 min-w-0">
                      <div className="text-[14px] font-bold text-white truncate group-hover:text-teal transition-colors">
                        {u.name}
                      </div>
                      <div className="text-[12px] text-slate-500 truncate">
                        @{u.username}
                      </div>
                    </div>
                  </Link>
                  <button
                    onClick={() => handleFollow(u.id)}
                    disabled={isFollowed}
                    className={`px-4 py-1.5 rounded-full text-[13px] font-bold transition-all ${
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
          <div className="space-y-5">
            {rooms.length === 0 && (
              <p className="text-[12px] text-slate-500 italic">No rooms active</p>
            )}
            {rooms.slice(0, 3).map((room) => (
              <div key={room.id} className="flex flex-col gap-2 p-3 rounded-r12 bg-white/5 border border-white/5 hover:border-teal/30 transition-all cursor-pointer group">
                <div className="flex justify-between items-start">
                  <div className="text-[14px] font-bold text-white group-hover:text-teal transition-colors truncate max-w-[140px]">
                    {room.title}
                  </div>
                  <div className="flex items-center gap-1 px-2 py-0.5 rounded-full bg-teal/20 text-teal text-[10px] font-black uppercase">
                    <svg className="w-2.5 h-2.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    {room.entry_fee_drops}
                  </div>
                </div>
                <div className="flex items-center gap-2">
                   <div className="w-5 h-5 rounded-full bg-slate-800 flex items-center justify-center text-[10px] text-slate-400">
                      {room.host?.name?.charAt(0)}
                   </div>
                   <span className="text-[11px] text-slate-500">by {room.host?.name}</span>
                </div>
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
