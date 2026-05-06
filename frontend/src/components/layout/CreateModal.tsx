"use client";

import React, { useState } from "react";
import { Avatar } from "@/components/ui/Avatar";
import { useAuth } from "@/context/AuthContext";
import Link from "next/link";

interface CreateOption {
  id: string;
  title: string;
  description: string;
  icon: React.ReactNode;
  color: string;
  minRole: string[];
  href: string;
}

const OPTIONS: CreateOption[] = [
  {
    id: "post",
    title: "New Post",
    description: "Share a thought, image or short update with your followers.",
    icon: (
      <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 6h16M4 12h16M4 18h7" />
      </svg>
    ),
    color: "bg-blue-500",
    minRole: ["user", "pro", "studio", "admin"],
    href: "/create/post"
  },
  {
    id: "wave",
    title: "New Wave",
    description: "Upload a cinematic short video and ride the platform's feed.",
    icon: (
      <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
      </svg>
    ),
    color: "bg-teal",
    minRole: ["user", "pro", "studio", "admin"],
    href: "/create/wave"
  },
  {
    id: "drop",
    title: "Skill Drop",
    description: "Package your expertise into a monetized digital asset.",
    icon: (
      <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7" />
      </svg>
    ),
    color: "bg-violet-500",
    minRole: ["pro", "studio", "admin"],
    href: "/create/drop"
  },
  {
    id: "challenge",
    title: "New Challenge",
    description: "Launch a community event with a custom prize pool.",
    icon: (
      <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
      </svg>
    ),
    color: "bg-amber-500",
    minRole: ["studio", "admin"],
    href: "/create/challenge"
  }
];

export function CreateModal({ isOpen, onClose }: { isOpen: boolean; onClose: () => void }) {
  const { user } = useAuth();
  
  if (!isOpen) return null;

  const userRole = user?.role || "user";

  return (
    <div className="fixed inset-0 z-[200] flex items-center justify-center p-4 sm:p-6">
      {/* Backdrop */}
      <div 
        className="absolute inset-0 bg-slate-950/80 backdrop-blur-xl animate-in fade-in duration-300"
        onClick={onClose}
      />
      
      {/* Modal Container */}
      <div className="relative w-full max-w-[500px] bg-slate-900/40 border border-white/10 rounded-[40px] overflow-hidden shadow-[0_32px_64px_rgba(0,0,0,0.5)] animate-in zoom-in-95 slide-in-from-bottom-10 duration-500 cubic-bezier(0.16, 1, 0.3, 1)">
        
        {/* Header */}
        <div className="p-8 pb-4 flex items-center justify-between">
          <div>
            <h2 className="text-2xl font-head font-black text-white tracking-tight uppercase leading-none mb-2">
              Create <span className="text-teal">Content</span>
            </h2>
            <p className="text-[13px] font-bold text-slate-500 uppercase tracking-widest">Select your next flow</p>
          </div>
          <button 
            onClick={onClose}
            className="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-slate-400 hover:text-white hover:bg-white/10 transition-all"
          >
            <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        {/* Options Grid */}
        <div className="p-4 grid gap-3">
          {OPTIONS.map((opt) => {
            const hasAccess = opt.minRole.includes(userRole);
            
            return (
              <Link
                key={opt.id}
                href={hasAccess ? opt.href : "#"}
                onClick={(e) => {
                  if (!hasAccess) {
                    e.preventDefault();
                    return;
                  }
                  onClose();
                }}
                className={`flex items-center gap-5 p-5 rounded-[24px] border border-white/5 transition-all group relative overflow-hidden ${
                  hasAccess 
                    ? 'hover:bg-white/5 hover:border-white/10 hover:translate-y-[-2px] active:scale-[0.98]' 
                    : 'opacity-40 grayscale cursor-not-allowed'
                }`}
              >
                {/* Accent Background Glow */}
                {hasAccess && (
                  <div className={`absolute -right-4 -bottom-4 w-32 h-32 rounded-full ${opt.color} opacity-0 group-hover:opacity-10 blur-3xl transition-opacity`} />
                )}

                <div className={`w-14 h-14 rounded-2xl ${opt.color} flex items-center justify-center text-slate-950 shadow-lg shrink-0 group-hover:scale-110 transition-transform duration-500`}>
                  {opt.icon}
                </div>
                
                <div className="flex-1 min-w-0 relative z-10">
                  <div className="flex items-center gap-2 mb-1">
                    <h3 className="text-[17px] font-black text-white uppercase tracking-tight group-hover:text-teal transition-colors">
                      {opt.title}
                    </h3>
                    {!hasAccess && (
                      <span className="text-[9px] font-black bg-white/10 text-slate-400 px-2 py-0.5 rounded uppercase tracking-widest flex items-center gap-1">
                        <svg className="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24">
                          <path d="M12 17a2 2 0 002-2V9a2 2 0 00-4 0v6a2 2 0 002 2z"/>
                          <path d="M18 10V7a6 6 0 10-12 0v3H4v10h16V10h-2zM8 7a4 4 0 118 0v3H8V7z"/>
                        </svg>
                        {opt.minRole[0]} only
                      </span>
                    )}
                  </div>
                  <p className="text-[13px] font-semibold text-slate-500 leading-snug group-hover:text-slate-400 transition-colors">
                    {opt.description}
                  </p>
                </div>

                {hasAccess && (
                  <div className="w-8 h-8 rounded-full bg-white/5 flex items-center justify-center text-slate-600 group-hover:text-teal group-hover:bg-teal/10 transition-all opacity-0 group-hover:opacity-100 -translate-x-4 group-hover:translate-x-0">
                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M9 5l7 7-7 7" />
                    </svg>
                  </div>
                )}
              </Link>
            );
          })}
        </div>

        {/* Footer info */}
        <div className="p-8 pt-4 border-t border-white/5 bg-white/[0.02]">
          <div className="flex items-center justify-between text-[11px] font-bold text-slate-500 uppercase tracking-widest">
            <span>Your Current Plan: <span className="text-teal">{userRole}</span></span>
            {userRole === 'user' && (
              <Link href="/subscriptions" className="text-teal hover:underline" onClick={onClose}>
                Upgrade to Pro
              </Link>
            )}
          </div>
        </div>
      </div>
    </div>
  );
}
