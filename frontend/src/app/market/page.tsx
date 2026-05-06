"use client";

import React from "react";
import { ShellLayout } from "@/components/layout/ShellLayout";

export default function MarketPage() {
  return (
    <ShellLayout>
      <div className="flex-1 flex flex-col items-center justify-center p-8 text-center">
        <div className="w-24 h-24 rounded-full bg-teal/10 flex items-center justify-center text-teal mb-8 relative">
           <svg className="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
           </svg>
           <div className="absolute inset-0 rounded-full border border-teal/20 animate-ping"></div>
        </div>
        
        <h1 className="text-3xl font-black text-white tracking-tighter mb-4">FLOW MARKET</h1>
        <p className="text-slate-400 max-w-xs leading-relaxed mb-8">
          The ultimate destination for premium Skill Drops, exclusive assets, and creator tools. 
        </p>

        <div className="glass px-6 py-3 rounded-full border border-teal/30">
           <span className="text-teal font-black uppercase tracking-[0.3em] text-[12px]">Coming Soon</span>
        </div>

        <div className="mt-12 grid grid-cols-3 gap-4 w-full max-w-md opacity-20 grayscale">
           {[1, 2, 3].map(i => (
             <div key={i} className="aspect-square bg-white/5 rounded-r16 border border-white/10"></div>
           ))}
        </div>
      </div>
    </ShellLayout>
  );
}
