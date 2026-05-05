"use client";

import React from "react";
import { Sidebar, BottomNav } from "./Navigation";

export function ShellLayout({ children }: { children: React.ReactNode }) {
  return (
    <div className="flex min-h-screen bg-white">
      {/* Sidebar for Desktop */}
      <Sidebar />

      {/* Main Content Area */}
      <main className="flex-1 flex flex-col min-h-screen bg-slate-50/50">
        <div className="flex-1 w-full max-w-[640px] mx-auto bg-white min-h-screen border-x border-slate-50 flex flex-col">
          {children}
        </div>
      </main>

      {/* Right Sidebar for Desktop (Widgets, Trends, etc.) */}
      <aside className="hidden xl:flex flex-col w-80 h-screen sticky top-0 p-8 border-l border-slate-50">
        <div className="bg-slate-50 rounded-r24 p-6">
          <h3 className="font-head font-extrabold text-lg mb-4">Suggested Users</h3>
          <div className="space-y-4">
             {/* Suggested users placeholders */}
             {[1,2,3].map(i => (
               <div key={i} className="flex items-center gap-3">
                 <div className="w-10 h-10 rounded-full bg-slate-200 animate-pulse"></div>
                 <div className="flex-1 space-y-1">
                   <div className="h-3 w-20 bg-slate-200 rounded animate-pulse"></div>
                   <div className="h-2 w-12 bg-slate-200 rounded animate-pulse"></div>
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
