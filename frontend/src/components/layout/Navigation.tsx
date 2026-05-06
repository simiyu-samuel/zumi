"use client";

import React, { useState, useEffect, useRef } from "react";
import Link from "next/link";
import { usePathname } from "next/navigation";
import { useAuth } from "@/context/AuthContext";
import { Avatar } from "@/components/ui/Avatar";

const NAV_ITEMS = [
  { 
    label: "Home", 
    href: "/feed",
    icon: (
      <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
      </svg>
    )
  },
  { 
    label: "Explore", 
    href: "/explore",
    icon: (
      <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
      </svg>
    )
  },
  { 
    label: "Circles & Rooms", 
    href: "/circles",
    icon: (
      <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
      </svg>
    )
  },
  { 
    label: "Notifications", 
    href: "/notifications",
    icon: (
      <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
      </svg>
    )
  },
  { 
    label: "Drops Wallet", 
    href: "/wallet",
    icon: (
      <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
      </svg>
    )
  },
  { 
    label: "Profile", 
    href: "/profile",
    icon: (
      <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
      </svg>
    )
  },
];

export function Sidebar() {
  const pathname = usePathname();
  const { user, logout, token } = useAuth();
  const [showProfileMenu, setShowProfileMenu] = useState(false);
  const [unreadCount, setUnreadCount] = useState(0);
  const menuRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    if (!token) return;
    
    const fetchUnread = async () => {
      try {
        const res = await import("@/lib/api").then(m => m.getUnreadCount());
        setUnreadCount(res.unread_count);
      } catch (err) {}
    };

    fetchUnread();
    const interval = setInterval(fetchUnread, 30000); // Poll every 30s
    return () => clearInterval(interval);
  }, [token]);

  // Close profile menu on click outside
  useEffect(() => {
    function handleClickOutside(event: MouseEvent) {
      if (menuRef.current && !menuRef.current.contains(event.target as Node)) {
        setShowProfileMenu(false);
      }
    }
    if (showProfileMenu) {
      document.addEventListener("mousedown", handleClickOutside);
    }
    return () => document.removeEventListener("mousedown", handleClickOutside);
  }, [showProfileMenu]);

  return (
    <aside className="hidden lg:flex flex-col w-72 h-screen sticky top-0 px-8 py-10">
      <div className="font-head font-extrabold text-4xl mb-12 px-2 text-white tracking-tighter">
        zu<span className="text-teal">mi</span>
      </div>

      <nav className="flex-1 space-y-2">
        {NAV_ITEMS.map((item) => {
          let href = item.href;
          if (item.label === "Profile" && user?.username) {
            href = `/profile/${user.username}`;
          }
          const isActive = pathname === href;
          const isNotifications = item.label === "Notifications";
          
          return (
            <Link 
              key={item.href} 
              href={href}
              className={`flex items-center gap-4 px-4 py-4 rounded-r24 text-[16px] font-bold transition-all duration-300 group ${isActive ? 'bg-teal/10 text-teal' : 'text-slate-400 hover:text-white hover:bg-white/5'}`}
            >
              <div className={`relative transition-transform duration-300 ${isActive ? 'scale-110' : 'group-hover:scale-110'}`}>
                {item.icon}
                {isNotifications && unreadCount > 0 && (
                  <div className="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full flex items-center justify-center text-[10px] text-white font-black border-2 border-slate-950">
                    {unreadCount > 9 ? '9+' : unreadCount}
                  </div>
                )}
              </div>
              {item.label}
              {isActive && (
                 <div className="ml-auto w-1.5 h-1.5 rounded-full bg-teal shadow-[0_0_8px_#1a9e75]"></div>
              )}
            </Link>
          );
        })}
      </nav>

      <div className="mt-auto space-y-6">
        <div className="glass p-5 rounded-r24 relative overflow-hidden group">
           <div className="relative z-10">
              <div className="text-[11px] font-bold text-slate-500 uppercase tracking-widest mb-1">Your Balance</div>
              <div className="flex items-baseline gap-1.5">
                 <span className="text-2xl font-black text-white">
                   {user?.drops_balance?.toLocaleString() || "0"}
                 </span>
                 <span className="text-[13px] font-bold text-teal">DROPS</span>
              </div>
           </div>
        </div>

        <div ref={menuRef} className="pt-6 border-t border-white/5 relative">
          {showProfileMenu && (
            <div className="absolute bottom-[calc(100%+24px)] left-0 w-[280px] bg-slate-950/90 backdrop-blur-2xl border border-white/10 rounded-[32px] overflow-hidden shadow-[0_20px_50px_rgba(0,0,0,0.8)] z-[100] animate-in zoom-in-95 slide-in-from-bottom-10 duration-500 cubic-bezier(0.16, 1, 0.3, 1)">
              <div className="p-6 border-b border-white/5 bg-white/5">
                <div className="flex items-center gap-3">
                  <Avatar src={user?.avatar_url} name={user?.name} size="md" role={user?.role} />
                  <div className="min-w-0">
                    <div className="text-sm font-black text-white truncate">{user?.name}</div>
                    <div className="text-[11px] font-bold text-slate-500 truncate">@{user?.username}</div>
                  </div>
                </div>
              </div>

              <div className="p-2">
                <Link 
                  href={`/profile/${user?.username}`}
                  onClick={() => setShowProfileMenu(false)}
                  className="flex items-center gap-3 px-4 py-3.5 text-white hover:bg-white/5 rounded-r20 transition-all font-bold text-[13px] group/item"
                >
                  <div className="w-8 h-8 rounded-full bg-teal/10 flex items-center justify-center group-hover/item:bg-teal group-hover/item:text-white transition-colors">
                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                  </div>
                  View profile
                </Link>

                <button className="w-full flex items-center gap-3 px-4 py-3.5 text-white hover:bg-white/5 rounded-r20 transition-all font-bold text-[13px] group/item">
                  <div className="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center group-hover/item:bg-slate-700 transition-colors">
                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                  </div>
                  Settings & Privacy
                </button>

                <button className="w-full flex items-center gap-3 px-4 py-3.5 text-white hover:bg-white/5 rounded-r20 transition-all font-bold text-[13px] group/item">
                  <div className="w-8 h-8 rounded-full bg-violet-500/10 flex items-center justify-center group-hover/item:bg-violet-500 group-hover/item:text-white transition-colors">
                    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                  </div>
                  Manage Subscriptions
                </button>

                <div className="mx-2 my-2 p-3 bg-white/5 rounded-r20 flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <div className="w-8 h-8 rounded-full bg-slate-800 flex items-center justify-center">
                      <svg className="w-4 h-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                      </svg>
                    </div>
                    <span className="text-[13px] font-bold text-white">Dark Mode</span>
                  </div>
                  <button className="w-10 h-5 bg-teal rounded-full relative transition-all">
                    <div className="absolute right-1 top-1 w-3 h-3 bg-white rounded-full shadow-sm"></div>
                  </button>
                </div>

                <div className="mt-2 pt-2 border-t border-white/5">
                  <button 
                    onClick={() => logout()}
                    className="w-full flex items-center gap-3 px-4 py-3.5 text-red-400 hover:bg-red-500/10 rounded-r20 transition-all font-bold text-[13px] group/logout"
                  >
                    <div className="w-8 h-8 rounded-full bg-red-500/10 flex items-center justify-center group-hover/logout:bg-red-500 group-hover/logout:text-white transition-colors">
                      <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                      </svg>
                    </div>
                    Log out session
                  </button>
                </div>
              </div>
            </div>
          )}

          <div 
            onClick={() => setShowProfileMenu(!showProfileMenu)}
            className="flex items-center gap-4 px-2 group cursor-pointer"
          >
            <Avatar 
              src={user?.avatar_url} 
              name={user?.name} 
              size="lg" 
              role={user?.role}
              className="transition-transform group-hover:scale-105" 
            />
            <div className="min-w-0">
              <div className="text-[15px] font-bold text-white truncate">{user?.name}</div>
              <div className="text-[13px] text-slate-500 truncate">@{user?.username}</div>
            </div>
            <div className="ml-auto">
               <svg className={`w-5 h-5 text-slate-600 transition-transform duration-300 ${showProfileMenu ? 'rotate-180' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M5 15l7-7 7 7" />
               </svg>
            </div>
          </div>
        </div>
      </div>
    </aside>
  );
}

export function BottomNav() {
  const pathname = usePathname();
  const { token } = useAuth();
  const [unreadCount, setUnreadCount] = useState(0);

  useEffect(() => {
    if (!token) return;
    const fetchUnread = async () => {
      try {
        const res = await import("@/lib/api").then(m => m.getUnreadCount());
        setUnreadCount(res.unread_count);
      } catch (err) {}
    };
    fetchUnread();
    const interval = setInterval(fetchUnread, 30000);
    return () => clearInterval(interval);
  }, [token]);

  return (
    <nav className="lg:hidden flex justify-around items-center h-20 border-t border-white/5 glass-dark sticky bottom-0 z-50 px-4">
      {NAV_ITEMS.slice(0, 5).map((item) => {
        const isActive = pathname === item.href;
        const isNotifications = item.label === "Notifications";

        return (
          <Link 
            key={item.href} 
            href={item.href}
            className={`flex flex-col items-center justify-center gap-1 w-full h-full transition-all duration-300 relative ${isActive ? 'text-teal scale-110' : 'text-slate-500 hover:text-slate-300'}`}
          >
            <div className="relative">
              {item.icon}
              {isNotifications && unreadCount > 0 && (
                <div className="absolute -top-1 -right-1 w-4 h-4 bg-red-500 rounded-full flex items-center justify-center text-[10px] text-white font-black border-2 border-slate-950">
                  {unreadCount > 9 ? '9+' : unreadCount}
                </div>
              )}
            </div>
            {isActive && <div className="w-1 h-1 rounded-full bg-teal mt-1 shadow-[0_0_4px_#1a9e75]"></div>}
          </Link>
        );
      })}
    </nav>
  );
}
