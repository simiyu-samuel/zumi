"use client";

import React from "react";
import Link from "next/link";
import { DeviceFrame, StatusBar } from "@/components/DeviceFrame";

export default function WelcomePage() {
  return (
    <DeviceFrame>
      <div className="screen relative bg-dark flex flex-col h-full overflow-hidden">
        {/* Background Effects */}
        <div className="welcome-bg absolute inset-0 z-0"></div>
        <div className="welcome-orb absolute w-[280px] h-[280px] rounded-full top-20 left-1/2 -translate-x-1/2 animate-[orb-pulse_4s_ease-in-out_infinite] bg-[radial-gradient(circle,rgba(26,158,117,0.18)_0%,transparent_70%)]"></div>
        
        {/* Logo Section */}
        <div className="flex-1 flex flex-col items-center justify-center relative z-1 p-6">
          <div className="welcome-logo font-head font-extrabold text-[64px] text-white tracking-[-2px] leading-none">
            zu<span className="text-teal">mi</span>
          </div>
          <div className="welcome-tagline text-[15px] font-normal text-white/55 mt-2.5 tracking-[0.01em]">
            Flow with your people
          </div>
        </div>

        {/* Bottom Content */}
        <div className="welcome-bottom relative z-2 p-8 bg-gradient-to-t from-dark via-dark/90 to-transparent">
          <h1 className="welcome-headline font-head text-[28px] font-extrabold text-white tracking-[-0.5px] leading-[1.15] mb-2">
            Your community,<br />your economy.
          </h1>
          <p className="welcome-sub text-sm text-white/50 leading-[1.55] mb-7">
            Create. Connect. Earn Drops. Cash out.
          </p>
          
          <Link href="/auth/signup" className="flex items-center justify-center gap-2 w-full p-[15px] rounded-r12 bg-teal text-white font-semibold text-[15px] tracking-[-0.01em] transition-all hover:bg-teal-dark hover:-translate-y-[1px] hover:shadow-[0_6px_20px_rgba(26,158,117,0.35)] active:translate-y-0">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
              <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2" />
              <circle cx="9" cy="7" r="4" />
              <line x1="19" y1="8" x2="19" y2="14" />
              <line x1="22" y1="11" x2="16" y2="11" />
            </svg>
            Create your account
          </Link>
          
          <div className="welcome-divider flex items-center gap-3 my-4">
            <div className="flex-1 h-[1px] bg-white/10"></div>
            <span className="text-[12px] text-white/30 font-medium lowercase">already have an account?</span>
            <div className="flex-1 h-[1px] bg-white/10"></div>
          </div>
          
          <Link href="/auth/login" className="flex items-center justify-center gap-2 w-full p-[15px] rounded-r12 bg-white/7 text-white font-semibold text-[15px] tracking-[-0.01em] border border-white/12 transition-all hover:bg-white/12">
            Sign in
          </Link>
        </div>
      </div>
    </DeviceFrame>
  );
}
