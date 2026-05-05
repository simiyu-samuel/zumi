"use client";

import React from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { useAuth } from "@/context/AuthContext";

const NAV_ITEMS = [
  { label: "Home", icon: "🏠", href: "/feed" },
  { label: "Explore", icon: "🔍", href: "/explore" },
  { label: "Notifications", icon: "🔔", href: "/notifications" },
  { label: "Create", icon: "➕", href: "/create" },
  { label: "Profile", icon: "👤", href: "/profile" },
];

export function Sidebar() {
  const pathname = usePathname();
  const { user } = useAuth();

  return (
    <aside className="hidden lg:flex flex-col w-64 h-screen border-r border-slate-100 bg-white sticky top-0 px-4 py-8">
      <div className="font-head font-extrabold text-3xl mb-12 px-2 text-dark">
        zu<span className="text-teal">mi</span>
      </div>

      <nav className="flex-1 space-y-2">
        {NAV_ITEMS.map((item) => {
          const isActive = pathname === item.href;
          return (
            <Link 
              key={item.href} 
              href={item.href}
              className={`flex items-center gap-4 px-4 py-3.5 rounded-r16 text-[15px] font-semibold transition-all ${isActive ? 'bg-teal-light text-teal-dark' : 'text-slate-600 hover:bg-slate-50'}`}
            >
              <span className="text-xl">{item.icon}</span>
              {item.label}
            </Link>
          );
        })}
      </nav>

      <div className="mt-auto pt-6 border-t border-slate-50">
        <div className="flex items-center gap-3 px-2">
          <div className="w-10 h-10 rounded-full bg-teal text-white flex items-center justify-center font-bold uppercase">
            {user?.name?.charAt(0) || "U"}
          </div>
          <div className="min-w-0">
            <div className="text-sm font-bold text-dark truncate">{user?.name}</div>
            <div className="text-xs text-slate-400 truncate">@{user?.username}</div>
          </div>
        </div>
      </div>
    </aside>
  );
}

export function BottomNav() {
  const pathname = usePathname();

  return (
    <nav className="lg:hidden flex justify-around items-center h-16 border-t border-slate-100 bg-white sticky bottom-0 z-50">
      {NAV_ITEMS.map((item) => {
        const isActive = pathname === item.href;
        return (
          <Link 
            key={item.href} 
            href={item.href}
            className={`flex flex-col items-center justify-center gap-1 w-full h-full transition-all ${isActive ? 'text-teal' : 'text-slate-400'}`}
          >
            <span className="text-xl">{item.icon}</span>
          </Link>
        );
      })}
    </nav>
  );
}
