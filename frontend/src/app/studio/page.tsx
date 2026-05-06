"use client";

import React from "react";
import { ShellLayout } from "@/components/layout/ShellLayout";

export default function StudioPage() {
  return (
    <ShellLayout>
      <div className="px-8 py-10 flex-1">
        <div className="mb-10">
          <h1 className="text-3xl font-black text-white tracking-tighter mb-2">CREATOR STUDIO</h1>
          <p className="text-slate-500 font-bold uppercase tracking-widest text-[12px]">Manage your waves, skill drops, and earnings</p>
        </div>

        {/* Stats Grid */}
        <div className="grid grid-cols-3 gap-4 mb-10">
           {[
             { label: "Total Drops", value: "45,280", trend: "+12%" },
             { label: "Active Waves", value: "12", trend: "0" },
             { label: "Skill Sales", value: "84", trend: "+5%" },
           ].map((stat, i) => (
             <div key={i} className="glass p-6 rounded-r24 border border-white/5">
                <div className="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-1">{stat.label}</div>
                <div className="text-2xl font-black text-white mb-2">{stat.value}</div>
                <div className={`text-[10px] font-black ${stat.trend.startsWith('+') ? 'text-teal' : 'text-slate-600'}`}>{stat.trend} THIS WEEK</div>
             </div>
           ))}
        </div>

        {/* Quick Actions */}
        <div className="grid grid-cols-2 gap-4">
           <button className="p-8 rounded-r24 bg-teal/10 border border-teal/20 hover:bg-teal hover:text-slate-950 transition-all group flex flex-col items-center gap-4">
              <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
              </svg>
              <span className="font-black uppercase tracking-widest text-[13px]">Drop New Wave</span>
           </button>
           <button className="p-8 rounded-r24 bg-violet-500/10 border border-violet-500/20 hover:bg-violet-500 hover:text-white transition-all group flex flex-col items-center gap-4">
              <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                 <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
              </svg>
              <span className="font-black uppercase tracking-widest text-[13px]">Create Skill Drop</span>
           </button>
        </div>
      </div>
    </ShellLayout>
  );
}
