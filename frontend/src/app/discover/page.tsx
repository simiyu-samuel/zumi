"use client";

import React from "react";
import { ShellLayout } from "@/components/layout/ShellLayout";

export default function DiscoverPage() {
  return (
    <ShellLayout>
      <div className="px-8 py-10 flex-1">
        <div className="mb-10">
          <h1 className="text-3xl font-black text-white tracking-tighter mb-2">DISCOVER</h1>
          <p className="text-slate-500 font-bold uppercase tracking-widest text-[12px]">Explore the Zumi Universe</p>
        </div>

        {/* Search Bar */}
        <div className="relative mb-12">
          <div className="absolute inset-y-0 left-5 flex items-center pointer-events-none">
            <svg className="w-5 h-5 text-slate-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </div>
          <input 
            type="text" 
            placeholder="Search creators, waves, or drops..." 
            className="w-full bg-white/5 border border-white/10 rounded-full py-4 pl-14 pr-6 text-white font-bold focus:outline-none focus:ring-2 focus:ring-teal/50 transition-all"
          />
        </div>

        {/* Categories Grid */}
        <div className="grid grid-cols-2 gap-4">
          {[
            { name: "Digital Art", color: "from-teal/20 to-teal/5" },
            { name: "Motion Study", color: "from-violet-500/20 to-violet-500/5" },
            { name: "Music Production", color: "from-blue-500/20 to-blue-500/5" },
            { name: "Creative Coding", color: "from-rose-500/20 to-rose-500/5" },
          ].map((cat, i) => (
            <div key={i} className={`p-8 rounded-r24 bg-gradient-to-br ${cat.color} border border-white/5 hover:border-white/20 transition-all cursor-pointer group`}>
               <div className="text-xl font-black text-white group-hover:text-teal transition-colors">{cat.name}</div>
               <div className="text-[11px] text-slate-500 font-bold mt-2">EXPLORE CATEGORY</div>
            </div>
          ))}
        </div>
      </div>
    </ShellLayout>
  );
}
