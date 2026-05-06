"use client";

import React, { useEffect, useState, useCallback, useRef } from "react";
import { useParams, useRouter } from "next/navigation";
import { ShellLayout } from "@/components/layout/ShellLayout";
import { useAuth } from "@/context/AuthContext";
import { 
  getUserProfile, 
  getUserWaves, 
  followUser, 
  unfollowUser, 
  type Wave, 
  type WaveUser 
} from "@/lib/api";
import { Avatar } from "@/components/ui/Avatar";
import { GiftSheet } from "@/components/waves/GiftSheet";

type ProfileTab = "WAVES" | "CIRCLES" | "SKILL DROPS";

function formatCount(n: number): string {
  if (n >= 1000000) return (n / 1000000).toFixed(1) + "M";
  if (n >= 1000) return (n / 1000).toFixed(1) + "k";
  return String(n);
}

export default function ProfilePage() {
  const { username } = useParams() as { username: string };
  const router = useRouter();
  const { user: currentUser, token } = useAuth();
  
  const [profile, setProfile] = useState<WaveUser | null>(null);
  const [waves, setWaves] = useState<Wave[]>([]);
  const [loading, setLoading] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);
  const [hasMore, setHasMore] = useState(true);
  const [cursor, setCursor] = useState<string | null>(null);
  const [activeTab, setActiveTab] = useState<ProfileTab>("WAVES");
  const [isFollowing, setIsFollowing] = useState(false);
  
  // Modals
  const [showGiftSheet, setShowGiftSheet] = useState(false);
  
  const observerTarget = useRef(null);

  const fetchProfile = useCallback(async () => {
    try {
      const res = await getUserProfile(username);
      setProfile(res.data);
      setIsFollowing(res.data.is_following || false);
    } catch (err) {
      console.error("Profile fetch error:", err);
    }
  }, [username]);

  const fetchWaves = useCallback(async (isInitial = false) => {
    if (!profile?.id) return;
    if (!isInitial && (!hasMore || loadingMore)) return;

    if (isInitial) setLoading(true);
    else setLoadingMore(true);

    try {
      const res = await getUserWaves(profile.id, isInitial ? undefined : cursor || undefined);
      if (isInitial) {
        setWaves(res.data);
      } else {
        setWaves((prev) => [...prev, ...res.data]);
      }
      setCursor(res.next_cursor);
      setHasMore(!!res.next_cursor);
    } catch (err) {
      console.error("Waves fetch error:", err);
    } finally {
      setLoading(false);
      setLoadingMore(false);
    }
  }, [profile?.id, cursor, hasMore, loadingMore]);

  useEffect(() => {
    fetchProfile();
  }, [fetchProfile]);

  useEffect(() => {
    if (profile?.id && activeTab === "WAVES") {
      fetchWaves(true);
    }
  }, [profile?.id, activeTab]);

  useEffect(() => {
    const target = observerTarget.current;
    if (!target) return;

    const observer = new IntersectionObserver(
      (entries) => {
        if (entries[0].isIntersecting) {
          fetchWaves();
        }
      },
      { threshold: 1.0 }
    );

    observer.observe(target);
    return () => observer.disconnect();
  }, [fetchWaves, observerTarget]);

  const handleFollow = async () => {
    if (!profile) return;
    try {
      if (isFollowing) {
        await unfollowUser(profile.id);
        setIsFollowing(false);
      } else {
        await followUser(profile.id);
        setIsFollowing(true);
      }
      // Refresh profile to get updated follower count
      fetchProfile();
    } catch (err) {
      console.error("Follow error:", err);
    }
  };

  const isOwnProfile = currentUser?.username === username;

  if (loading && !profile) {
    return (
      <ShellLayout>
        <div className="flex-1 flex items-center justify-center">
          <div className="w-10 h-10 border-2 border-teal/30 border-t-teal rounded-full animate-spin"></div>
        </div>
      </ShellLayout>
    );
  }

  if (!profile) {
    return (
      <ShellLayout>
        <div className="flex-1 flex flex-col items-center justify-center gap-4 p-8 text-center">
          <div className="w-20 h-20 rounded-full bg-slate-900 flex items-center justify-center">
             <svg className="w-10 h-10 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
             </svg>
          </div>
          <h2 className="text-xl font-black text-white">User Not Found</h2>
          <p className="text-slate-500">The profile you are looking for doesn't exist or has been moved.</p>
          <button 
            onClick={() => router.push("/feed")}
            className="mt-2 px-6 py-2 bg-teal rounded-full text-white font-bold hover:bg-teal-dark transition-all"
          >
            Back to Feed
          </button>
        </div>
      </ShellLayout>
    );
  }

  return (
    <ShellLayout>
      {/* Gift Sheet Modal */}
      {showGiftSheet && (
        <GiftSheet 
          userId={profile.id}
          onClose={() => setShowGiftSheet(false)} 
        />
      )}

      {/* Sticky Header */}
      <header className="sticky top-0 z-40 bg-slate-950/70 backdrop-blur-xl border-b border-white/5 flex items-center gap-6 px-6 py-4">
        <button 
          onClick={() => router.back()}
          className="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center hover:bg-white/10 transition-all text-white"
        >
          <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M15 19l-7-7 7-7" />
          </svg>
        </button>
        <div className="min-w-0">
          <h1 className="text-[17px] font-black text-white truncate leading-tight">{profile.name}</h1>
          <p className="text-[12px] text-slate-500 font-bold uppercase tracking-wider">
            {waves.length > 0 ? `${waves.length} Waves` : "Profile"}
          </p>
        </div>
      </header>

      <div className="flex-1 overflow-y-auto no-scrollbar">
        {/* Banner */}
        <div className="relative h-48 sm:h-64 bg-slate-900 overflow-hidden">
          {profile.banner_url ? (
            <img src={profile.banner_url} alt="banner" className="w-full h-full object-cover" />
          ) : (
            <div className="w-full h-full bg-gradient-to-br from-slate-900 via-slate-800 to-teal/10" />
          )}
          <div className="absolute inset-0 bg-gradient-to-b from-black/20 to-black/40" />
        </div>

        {/* Profile Info Section */}
        <div className="px-6 relative">
          {/* Avatar overlap */}
          <div className="absolute -top-16 left-6">
            <div className="p-1.5 rounded-full bg-slate-950">
              <Avatar 
                src={profile.avatar_url} 
                name={profile.name} 
                size="xl" 
                role={profile.role}
                className="w-28 h-28 sm:w-32 sm:h-32 border-[4px] border-teal shadow-2xl" 
              />
            </div>
          </div>

          {/* Action Buttons */}
          <div className="flex justify-end gap-3 py-5">
            {isOwnProfile ? (
              <button className="px-6 py-2.5 rounded-full bg-white/5 border border-white/10 text-white font-bold hover:bg-white/10 transition-all text-[14px]">
                Edit Profile
              </button>
            ) : (
              <>
                <button 
                  onClick={() => setShowGiftSheet(true)}
                  className="w-11 h-11 rounded-full bg-white/5 border border-white/10 flex items-center justify-center hover:bg-white/10 transition-all text-white"
                  title="Send Drops"
                >
                  <svg className="w-5 h-5 text-teal" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                  </svg>
                </button>
                <button className="w-11 h-11 rounded-full bg-white/5 border border-white/10 flex items-center justify-center hover:bg-white/10 transition-all text-white">
                  <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                  </svg>
                </button>
                <button 
                  onClick={handleFollow}
                  className={`px-8 py-2.5 rounded-full font-black text-[14px] transition-all ${
                    isFollowing 
                    ? "bg-white/5 border border-white/10 text-white hover:bg-red-500/10 hover:text-red-500 hover:border-red-500/50 group" 
                    : "bg-teal text-white hover:bg-teal-dark shadow-lg shadow-teal/20"
                  }`}
                >
                  <span className={isFollowing ? "group-hover:hidden" : ""}>
                    {isFollowing ? "Following" : "Follow"}
                  </span>
                  {isFollowing && <span className="hidden group-hover:inline">Unfollow</span>}
                </button>
              </>
            )}
          </div>

          {/* Info */}
          <div className="mt-2">
            <div className="flex items-center gap-2">
              <h2 className="text-2xl font-black text-white">{profile.name}</h2>
              {profile.role === 'admin' && (
                 <div className="w-5 h-5 rounded-full bg-teal flex items-center justify-center">
                    <svg className="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.64.304 1.25.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
                    </svg>
                 </div>
              )}
            </div>
            <p className="text-teal font-bold text-[15px]">@{profile.username}</p>
            
            <p className="mt-4 text-[15px] text-slate-300 leading-relaxed font-medium">
              {profile.bio || "No bio yet. Flowing through life..."}
            </p>

            {/* Stats Row */}
            <div className="flex gap-6 mt-6 pb-6 border-b border-white/5">
              <div className="flex items-baseline gap-1.5 hover:opacity-80 cursor-pointer">
                <span className="text-[16px] font-black text-white">{formatCount(profile.following_count)}</span>
                <span className="text-[13px] font-bold text-slate-500 uppercase tracking-widest">Following</span>
              </div>
              <div className="flex items-baseline gap-1.5 hover:opacity-80 cursor-pointer">
                <span className="text-[16px] font-black text-white">{formatCount(profile.followers_count)}</span>
                <span className="text-[13px] font-bold text-slate-500 uppercase tracking-widest">Followers</span>
              </div>
              <div className="ml-auto flex items-center gap-2 bg-violet/10 border border-violet/20 px-3 py-1 rounded-r12 group cursor-pointer">
                 <div className="w-5 h-5 rounded-full bg-violet flex items-center justify-center shadow-[0_0_10px_rgba(108,99,255,0.4)]">
                    <svg className="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                 </div>
                 <div className="flex flex-col">
                    <span className="text-[13px] font-black text-white leading-none">
                      {profile.flow_score_summary?.score ?? profile.flow_score}
                    </span>
                    <span className="text-[10px] font-black text-violet-300 uppercase tracking-tighter mt-0.5">
                      {profile.flow_score_summary?.tier_label ?? 'Rising'}
                    </span>
                 </div>
              </div>
            </div>
          </div>
        </div>

        {/* Tabs sticky bar */}
        <div className="sticky top-[73px] z-30 bg-slate-950/80 backdrop-blur-xl border-b border-white/5 px-6 flex gap-8">
          {(["WAVES", "CIRCLES", "SKILL DROPS"] as ProfileTab[]).map((tab) => (
            <button
              key={tab}
              onClick={() => setActiveTab(tab)}
              className={`py-4 text-[13px] font-black tracking-[0.1em] transition-all relative ${
                activeTab === tab ? "text-white" : "text-slate-600 hover:text-slate-400"
              }`}
            >
              {tab}
              {activeTab === tab && (
                <div className="absolute bottom-0 left-0 right-0 h-1 bg-teal rounded-t-full shadow-[0_-2px_10px_rgba(26,158,117,0.5)]" />
              )}
            </button>
          ))}
        </div>

        {/* Content Area */}
        <div className="p-1">
          {activeTab === "WAVES" && (
            <>
              {loading ? (
                <div className="py-20 flex justify-center">
                  <div className="w-8 h-8 border-2 border-teal/20 border-t-teal rounded-full animate-spin"></div>
                </div>
              ) : waves.length === 0 ? (
                <div className="py-24 flex flex-col items-center gap-4 opacity-30">
                  <svg className="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                  </svg>
                  <p className="text-sm font-bold uppercase tracking-widest">No Waves shared yet</p>
                </div>
              ) : (
                <div className="grid grid-cols-3 gap-1">
                  {waves.map((wave) => (
                    <div 
                      key={wave.id} 
                      className="aspect-[3/4] bg-slate-900 relative group cursor-pointer overflow-hidden"
                      onClick={() => router.push(`/feed?wave=${wave.id}`)}
                    >
                      {wave.thumbnail_url ? (
                        <img src={wave.thumbnail_url} alt="" className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                      ) : (
                        <div className="w-full h-full bg-slate-800 flex items-center justify-center">
                           <svg className="w-8 h-8 text-slate-700" fill="currentColor" viewBox="0 0 24 24">
                              <path d="M8 5v14l11-7z" />
                           </svg>
                        </div>
                      )}
                      <div className="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity" />
                      <div className="absolute bottom-2 left-2 flex items-center gap-1.5 text-white text-[11px] font-black opacity-0 group-hover:opacity-100 transition-all translate-y-2 group-hover:translate-y-0">
                         <svg className="w-3 h-3" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z" />
                         </svg>
                         {formatCount(wave.likes_count || 0)}
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </>
          )}

          {activeTab === "CIRCLES" && (
            <div className="py-24 flex flex-col items-center gap-4 opacity-30">
               <svg className="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
               </svg>
               <p className="text-sm font-bold uppercase tracking-widest">No Circles yet</p>
            </div>
          )}

          {activeTab === "SKILL DROPS" && (
            <div className="py-24 flex flex-col items-center gap-4 opacity-30">
               <svg className="w-16 h-16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
               </svg>
               <p className="text-sm font-bold uppercase tracking-widest">No Skill Drops shared</p>
            </div>
          )}
        </div>

        {/* Infinite Scroll sentinel for Waves */}
        {activeTab === "WAVES" && waves.length > 0 && (
          <div ref={observerTarget} className="h-20 flex items-center justify-center">
            {loadingMore && <div className="w-6 h-6 border-2 border-teal/20 border-t-teal rounded-full animate-spin" />}
          </div>
        )}
      </div>
    </ShellLayout>
  );
}
