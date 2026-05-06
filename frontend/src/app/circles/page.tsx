"use client";

import React from "react";
import { ShellLayout } from "@/components/layout/ShellLayout";

export default function CirclesPage() {
  return (
    <ShellLayout>
      <div className="px-8 py-10 flex-1">
        <div className="mb-10 flex justify-between items-end">
          <div>
            <h1 className="text-3xl font-black text-white tracking-tighter mb-2">CIRCLES</h1>
            <p className="text-slate-500 font-bold uppercase tracking-widest text-[12px]">Your Communities & Gated Rooms</p>
          </div>
          <button className="px-6 py-3 bg-teal text-slate-950 font-black rounded-full uppercase tracking-widest text-[11px] hover:scale-105 transition-transform">
            Create Circle
          </button>
        </div>

        {/* Circles Grid */}
        <div className="space-y-6">
           <section>
              <h3 className="text-[13px] font-black text-slate-500 uppercase tracking-widest mb-4">Active Rooms</h3>
              <div className="bg-white/5 border border-white/10 rounded-r24 p-20 flex flex-col items-center justify-center text-center">
                 <p className="text-slate-600 font-bold mb-4">No active rooms in your circles</p>
                 <button className="text-teal font-black uppercase tracking-widest text-[11px] hover:underline">Start a live room</button>
              </div>
           </section>

           <section>
              <h3 className="text-[13px] font-black text-slate-500 uppercase tracking-widest mb-4">My Circles</h3>
              <div className="grid grid-cols-1 gap-4">
                 {[1, 2].map(i => (
                   <div key={i} className="glass p-6 rounded-r24 border border-white/5 flex items-center justify-between group cursor-pointer hover:border-teal/30 transition-all">
                      <div className="flex items-center gap-4">
                         <div className="w-14 h-14 rounded-r16 bg-gradient-to-br from-slate-800 to-slate-900 border border-white/5 flex items-center justify-center text-slate-700">
                            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                               <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                         </div>
                         <div>
                            <div className="text-lg font-black text-white group-hover:text-teal transition-colors">Digital Renaissance</div>
                            <div className="text-[12px] text-slate-500 font-bold uppercase tracking-wider">1.2k Members • 4 Rooms Active</div>
                         </div>
                      </div>
                      <svg className="w-5 h-5 text-slate-700 group-hover:text-teal transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                         <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M9 5l7 7-7 7" />
                      </svg>
                   </div>
                 ))}
              </div>
           </section>
        </div>
      </div>
    </ShellLayout>
  );
}
