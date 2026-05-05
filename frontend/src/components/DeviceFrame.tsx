"use client";

import React from "react";

export function DeviceFrame({ children }: { children: React.ReactNode }) {
  return (
    <div className="flex items-center justify-center min-h-screen bg-[#0B1120] md:p-6 lg:p-12 overflow-hidden">
      {/* 
         On Mobile: Full screen.
         On Desktop: A focused container card.
         Overflow hidden to prevent double scrollbars.
      */}
      <div className="device w-full h-full sm:h-screen md:max-w-[480px] md:h-[844px] md:max-h-[90vh] bg-white md:rounded-[32px] overflow-hidden relative shadow-none md:shadow-2xl transition-all duration-500 border border-transparent md:border-white/10">
        {children}
      </div>
    </div>
  );
}
