"use client";

import React from "react";
import { ShellLayout } from "@/components/layout/ShellLayout";
import { useAuth } from "@/context/AuthContext";

export default function FeedPage() {
  const { user } = useAuth();

  return (
    <ShellLayout>
      <header className="px-6 py-4 flex justify-between items-center border-b border-slate-50 sticky top-0 bg-white/80 backdrop-blur-md z-10">
        <h1 className="font-head font-extrabold text-xl">For You</h1>
      </header>

      <div className="flex-1 p-6">
        <div className="bg-teal-light/50 p-6 rounded-r24 mb-8 border border-teal/10">
          <h2 className="font-head text-xl font-extrabold text-teal-dark mb-2 tracking-tight">
            Welcome back, {user?.name.split(' ')[0]}!
          </h2>
          <p className="text-[15px] text-slate-600 leading-relaxed">
            Your personalized feed is being curated based on your interests.
          </p>
        </div>

        <div className="space-y-6">
          {[1, 2, 3].map((i) => (
            <div key={i} className="space-y-3">
              <div className="flex items-center gap-3">
                <div className="w-10 h-10 rounded-full bg-slate-100 animate-pulse"></div>
                <div className="h-4 w-32 bg-slate-100 rounded-full animate-pulse"></div>
              </div>
              <div className="aspect-[16/9] w-full bg-slate-50 rounded-r24 animate-pulse"></div>
            </div>
          ))}
        </div>
      </div>
    </ShellLayout>
  );
}
