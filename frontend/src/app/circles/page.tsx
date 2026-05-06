"use client";

import { useState, useEffect } from "react";
import { ShellLayout } from "@/components/layout/ShellLayout";
import { getCircles, getGatedRooms, type Circle, type GatedRoom } from "@/lib/api";
import { useAuth } from "@/context/AuthContext";
import { Avatar } from "@/components/ui/Avatar";
import Link from "next/link";

export default function CirclesPage() {
  const { token } = useAuth();
  const [circles, setCircles] = useState<Circle[]>([]);
  const [rooms, setRooms] = useState<GatedRoom[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    if (!token) return;
    
    setLoading(true);
    Promise.all([
      getCircles(),
      getGatedRooms()
    ]).then(([circlesRes, roomsRes]) => {
      setCircles(circlesRes.data || []);
      setRooms(roomsRes.data || []);
    }).catch(err => {
      console.error("Circles fetch error:", err);
    }).finally(() => {
      setLoading(false);
    });
  }, [token]);

  const activeRooms = rooms.filter(r => r.status === 'live');

  return (
    <ShellLayout>
      <div className="px-8 py-10 flex-1">
        <div className="mb-10 flex justify-between items-end">
          <div>
            <h1 className="text-3xl font-black text-white tracking-tighter mb-2 uppercase">Circles</h1>
            <p className="text-slate-500 font-bold uppercase tracking-widest text-[12px]">Your Communities & Gated Rooms</p>
          </div>
        </div>

        {loading ? (
          <div className="py-20 flex flex-col items-center gap-4">
            <div className="w-10 h-10 border-2 border-teal/30 border-t-teal rounded-full animate-spin"></div>
            <p className="text-sm text-slate-500 font-bold uppercase tracking-widest">Gathering your circles...</p>
          </div>
        ) : (
          <div className="space-y-10">
            {/* Active Live Rooms */}
            <section>
              <div className="flex items-center gap-2 mb-6">
                <h3 className="text-[13px] font-black text-slate-400 uppercase tracking-[0.2em]">Active Live Rooms</h3>
                {activeRooms.length > 0 && <div className="w-2 h-2 rounded-full bg-red-500 animate-pulse shadow-[0_0_8px_rgba(239,68,68,0.6)]"></div>}
              </div>
              
              {activeRooms.length > 0 ? (
                <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                  {activeRooms.map(room => (
                    <Link 
                      key={room.id} 
                      href={`/rooms/${room.id}`}
                      className="glass p-5 rounded-r24 border border-teal/20 hover:border-teal/50 transition-all group relative overflow-hidden"
                    >
                      <div className="flex items-center gap-4 relative z-10">
                        <Avatar src={room.host?.avatar_url} name={room.host?.name} size="md" className="border-2 border-teal/30" />
                        <div className="flex-1 min-w-0">
                          <div className="text-[15px] font-black text-white truncate group-hover:text-teal transition-colors">{room.title}</div>
                          <div className="text-[11px] text-slate-500 font-bold uppercase">Host: {room.host?.name}</div>
                        </div>
                        <div className="text-right">
                          <div className="text-[14px] font-black text-white">{room.entry_fee_drops} ◆</div>
                          <div className="text-[9px] text-teal font-black uppercase tracking-tighter">Enter Room</div>
                        </div>
                      </div>
                      <div className="absolute inset-0 bg-gradient-to-r from-teal/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    </Link>
                  ))}
                </div>
              ) : (
                <div className="bg-slate-950/40 border border-white/5 rounded-r24 p-12 flex flex-col items-center justify-center text-center">
                  <div className="w-12 h-12 rounded-full bg-white/5 flex items-center justify-center mb-4 text-slate-700">
                    <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                  </div>
                  <p className="text-slate-600 font-bold text-sm mb-1 uppercase tracking-widest">No active rooms in your circles</p>
                  <p className="text-slate-700 text-[11px] font-bold">Start a live room to invite your community</p>
                </div>
              )}
            </section>

            {/* My Communities (Circles) */}
            <section>
              <h3 className="text-[13px] font-black text-slate-400 uppercase tracking-[0.2em] mb-6">My Communities</h3>
              
              {circles.length > 0 ? (
                <div className="grid grid-cols-1 gap-4">
                  {circles.map(circle => (
                    <Link 
                      key={circle.id} 
                      href={`/circles/${circle.slug}`}
                      className="glass p-6 rounded-r24 border border-white/5 flex items-center justify-between group hover:border-teal/30 transition-all"
                    >
                      <div className="flex items-center gap-5">
                        <div className="w-16 h-16 rounded-r20 bg-gradient-to-br from-slate-800 to-slate-900 border border-white/10 flex items-center justify-center text-slate-600 group-hover:text-teal transition-all group-hover:scale-105">
                          <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                             <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                          </svg>
                        </div>
                        <div>
                          <div className="text-[18px] font-black text-white group-hover:text-teal transition-colors tracking-tight">{circle.name}</div>
                          <div className="text-[11px] text-slate-500 font-black uppercase tracking-wider flex items-center gap-2">
                            <span>{circle.members_count.toLocaleString()} Members</span>
                            <span className="w-1 h-1 rounded-full bg-slate-700"></span>
                            <span>{circle.type} Circle</span>
                          </div>
                        </div>
                      </div>
                      <div className="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-slate-600 group-hover:text-teal group-hover:bg-teal/10 transition-all">
                        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                           <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M9 5l7 7-7 7" />
                        </svg>
                      </div>
                    </Link>
                  ))}
                </div>
              ) : (
                <div className="bg-slate-950/40 border border-white/5 rounded-r24 p-12 flex flex-col items-center justify-center text-center">
                  <p className="text-slate-600 font-bold text-sm mb-4 uppercase tracking-widest">You haven't joined any circles yet</p>
                  <Link 
                    href="/discover"
                    className="px-6 py-2 bg-white/5 text-teal font-black uppercase tracking-widest text-[11px] rounded-full hover:bg-white/10 transition-colors border border-teal/20"
                  >
                    Discover Communities
                  </Link>
                </div>
              )}
            </section>
          </div>
        )}
      </div>
    </ShellLayout>
  );
}

