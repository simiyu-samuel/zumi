"use client";

import React, { useEffect, useMemo, useState } from "react";
import Link from "next/link";
import { ShellLayout } from "@/components/layout/ShellLayout";
import { Avatar } from "@/components/ui/Avatar";
import {
  getCircles, getGatedRooms, getSkillDrops, getSuggestedUsers, getTrending, globalSearch,
  type Circle, type GatedRoom, type SkillDrop, type SuggestedUser, type TrendingData, type Wave, type WaveUser,
} from "@/lib/api";

type DiscoverTab = "trending" | "creators" | "skill-drops" | "circles";
type SearchResults = { users: WaveUser[]; waves: Wave[]; circles: Circle[] } | null;

const Icons = {
  Trending: () => (
    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
    </svg>
  ),
  Creators: () => (
    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
    </svg>
  ),
  SkillDrops: () => (
    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M13 10V3L4 14h7v7l9-11h-7z" />
    </svg>
  ),
  Circles: () => (
    <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
      <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
    </svg>
  ),
};

function fmt(v: number) {
  if (v >= 1_000_000) return `${(v / 1_000_000).toFixed(1)}M`;
  if (v >= 1_000) return `${(v / 1_000).toFixed(1)}K`;
  return v.toString();
}

const TAB_META: Record<DiscoverTab, { icon: React.ReactNode; label: string }> = {
  trending: { icon: <Icons.Trending />, label: "Trending" },
  creators: { icon: <Icons.Creators />, label: "Creators" },
  "skill-drops": { icon: <Icons.SkillDrops />, label: "Skill Drops" },
  circles: { icon: <Icons.Circles />, label: "Circles" },
};

export default function DiscoverPage() {
  const [tab, setTab] = useState<DiscoverTab>("trending");
  const [query, setQuery] = useState("");
  const [loading, setLoading] = useState(true);
  const [trending, setTrending] = useState<TrendingData | null>(null);
  const [creators, setCreators] = useState<SuggestedUser[]>([]);
  const [skillDrops, setSkillDrops] = useState<SkillDrop[]>([]);
  const [circles, setCircles] = useState<Circle[]>([]);
  const [rooms, setRooms] = useState<GatedRoom[]>([]);
  const [searchResults, setSearchResults] = useState<SearchResults>(null);

  useEffect(() => {
    let cancelled = false;
    (async () => {
      setLoading(true);
      try {
        const [t, c, s, ci, r] = await Promise.all([
          getTrending(), getSuggestedUsers(8), getSkillDrops(8), getCircles(10), getGatedRooms(),
        ]);
        if (cancelled) return;
        setTrending(t); setCreators(c.data || []); setSkillDrops(s.data || []);
        setCircles(ci.data || []); setRooms(r.data || []);
      } catch (e) { console.error("Discover fetch error:", e); }
      finally { if (!cancelled) setLoading(false); }
    })();
    return () => { cancelled = true; };
  }, []);

  useEffect(() => {
    const q = query.trim();
    if (q.length < 2) return;
    let cancelled = false;
    const t = window.setTimeout(async () => {
      try {
        const r = await globalSearch(q, 8);
        if (!cancelled) setSearchResults({ users: r.users || [], waves: r.waves || [], circles: r.circles || [] });
      } catch { if (!cancelled) setSearchResults({ users: [], waves: [], circles: [] }); }
    }, 250);
    return () => { cancelled = true; window.clearTimeout(t); };
  }, [query]);

  const sr = query.trim().length >= 2 ? searchResults : null;
  const liveRooms = useMemo(() => rooms.filter((r) => r.status === "live").slice(0, 4), [rooms]);
  const topCreators = useMemo(() => {
    if (sr?.users?.length) return sr.users.map((u) => ({ id: u.id, name: u.name, username: u.username, bio: u.bio, avatar_url: u.avatar_url, followers_count: u.followers_count, role: u.role }));
    return creators;
  }, [sr, creators]);
  const visibleCircles = sr?.circles?.length ? sr.circles : circles;

  return (
    <ShellLayout fullWidth>
      <div className="flex-1 px-4 py-5 sm:px-6 sm:py-6 relative overflow-hidden">
        {/* Animated Background Blobs */}
        <div className="absolute top-0 right-0 w-[500px] h-[500px] bg-teal/5 rounded-full blur-[120px] animate-pulse pointer-events-none" />
        <div className="absolute bottom-0 left-0 w-[400px] h-[400px] bg-violet/5 rounded-full blur-[100px] animate-pulse pointer-events-none" style={{ animationDelay: "2s" }} />

        {/* ── Hero Search Section ───────────────────────────── */}
        <section className="relative overflow-hidden rounded-[32px] border border-white/[0.08] bg-gradient-to-br from-slate-900/40 via-slate-950/60 to-[#0a1628]/40 backdrop-blur-sm p-6 sm:p-10 mb-8 group">
          <div className="absolute inset-0 bg-gradient-to-br from-teal/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-700" />
          <div className="relative z-10">
            <div className="flex flex-wrap items-center gap-3 mb-6">
              <span className="inline-flex items-center gap-2 rounded-full bg-teal/10 border border-teal/20 px-4 py-1.5 text-[10px] font-black uppercase tracking-[0.25em] text-teal shadow-[0_0_15px_rgba(26,158,117,0.1)]">
                <span className="relative flex h-2 w-2">
                  <span className="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal opacity-75"></span>
                  <span className="relative inline-flex rounded-full h-2 w-2 bg-teal"></span>
                </span>
                EXPLORE MODE
              </span>
              <div className="h-1 w-1 rounded-full bg-slate-800" />
              <span className="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">{fmt(liveRooms.length)} Rooms Active</span>
            </div>
            
            <h1 className="font-head text-4xl sm:text-5xl font-black leading-[1.0] tracking-tight text-white max-w-2xl mb-4">
              The <span className="text-teal">Zeitgeist</span> of Zumi.
            </h1>
            <p className="text-base leading-relaxed text-slate-400 max-w-lg mb-8">
              Momentum is building across the flow. Find the creators, communities, and drops that matter right now.
            </p>

            {/* Search bar with UX enhancements */}
            <div className="relative max-w-xl group/search">
              <div className="pointer-events-none absolute inset-y-0 left-5 flex items-center text-slate-500 group-focus-within/search:text-teal transition-colors">
                <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
              </div>
              <input value={query} onChange={(e) => { setQuery(e.target.value); if (e.target.value.trim().length < 2) setSearchResults(null); }}
                type="text" placeholder="Search creators, waves, circles..."
                className="w-full rounded-[24px] border border-white/10 bg-slate-950/80 backdrop-blur-xl py-4.5 pl-14 pr-6 text-[15px] font-bold text-white outline-none transition-all placeholder:text-slate-600 focus:border-teal/40 focus:ring-4 focus:ring-teal/10 shadow-2xl"
              />
              <div className="absolute right-5 inset-y-0 flex items-center pointer-events-none">
                <span className="px-2 py-1 rounded-md bg-white/5 border border-white/10 text-[10px] font-black text-slate-500">⌘K</span>
              </div>
            </div>

            {/* Top Searches Pills */}
            <div className="mt-5 flex flex-wrap items-center gap-3">
              <span className="text-[10px] font-black uppercase tracking-widest text-slate-600">Trending Search:</span>
              {["#AI", "#Design", "#Crypto", "#ZumiFlow"].map(s => (
                <button key={s} onClick={() => setQuery(s)} className="text-[11px] font-bold text-slate-400 hover:text-teal transition-colors">{s}</button>
              ))}
            </div>
          </div>
        </section>

        {/* ── Tabs ──────────────────────────────────────────── */}
        <nav className="flex gap-3 mb-8 overflow-x-auto no-scrollbar pb-2">
          {(Object.keys(TAB_META) as DiscoverTab[]).map((key) => (
            <button key={key} onClick={() => setTab(key)}
              className={`flex items-center gap-2.5 whitespace-nowrap rounded-2xl px-6 py-3.5 text-[12px] font-black uppercase tracking-[0.2em] transition-all duration-300 relative group/tab ${
                tab === key
                  ? "bg-teal/15 border border-teal/40 text-teal shadow-[0_10px_30px_rgba(26,158,117,0.15)]"
                  : "bg-white/[0.03] border border-white/[0.08] text-slate-500 hover:text-white hover:border-white/20"
              }`}>
              <div className={`transition-transform duration-300 ${tab === key ? "scale-110" : "group-hover/tab:scale-110"}`}>
                {TAB_META[key].icon}
              </div>
              {TAB_META[key].label}
              {tab === key && (
                <div className="absolute -bottom-1 left-1/2 -translate-x-1/2 w-1.5 h-1.5 rounded-full bg-teal shadow-[0_0_10px_#1a9e75]" />
              )}
            </button>
          ))}
        </nav>

        {/* ── Search Results Overlay ───────────────────────── */}
        {sr && (
          <section className="mb-10 rounded-3xl border border-teal/20 bg-slate-950/90 backdrop-blur-2xl p-6 shadow-3xl animate-in slide-in-from-top-4 duration-500">
            <div className="flex items-center justify-between mb-6">
              <div>
                <div className="text-[11px] font-black uppercase tracking-[0.2em] text-teal">Instant Results</div>
                <div className="mt-1 text-sm text-slate-400 font-medium">Found in &ldquo;{query.trim()}&rdquo;</div>
              </div>
              <button onClick={() => setQuery("")} className="flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em] text-slate-500 hover:text-white transition-colors">
                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M6 18L18 6M6 6l12 12" /></svg>
                ESC
              </button>
            </div>
            <div className="grid gap-6 md:grid-cols-3">
              {[
                { title: "Creators", items: (sr.users || []).slice(0, 3), render: (u: WaveUser) => (
                  <Link key={u.id} href={`/profile/${u.username}`} className="flex items-center gap-3.5 rounded-2xl p-3 hover:bg-white/5 transition-all group/res">
                    <Avatar src={u.avatar_url} name={u.name} size="md" role={u.role} />
                    <div className="min-w-0">
                      <div className="truncate text-sm font-black text-white group-hover/res:text-teal transition-colors">{u.name}</div>
                      <div className="truncate text-[11px] font-bold text-slate-500">@{u.username}</div>
                    </div>
                  </Link>
                )},
                { title: "Waves", items: (sr.waves || []).slice(0, 3), render: (w: Wave) => (
                  <Link key={w.id} href={`/feed?wave=${w.id}`} className="block rounded-2xl border border-white/5 bg-white/[0.03] p-4 hover:border-teal/30 hover:bg-white/5 transition-all group/res">
                    <div className="truncate text-[13px] font-black text-white group-hover/res:text-teal transition-colors">{w.title}</div>
                    <div className="flex items-center justify-between mt-2">
                      <div className="truncate text-[11px] font-bold text-slate-500">@{w.user.username}</div>
                      <div className="text-[10px] font-black text-teal">FLOWING</div>
                    </div>
                  </Link>
                )},
                { title: "Circles", items: (sr.circles || []).slice(0, 3), render: (c: Circle) => (
                  <Link key={c.id} href={`/circles/${c.slug}`} className="block rounded-2xl border border-white/5 bg-white/[0.03] p-4 hover:border-teal/30 hover:bg-white/5 transition-all group/res">
                    <div className="truncate text-[13px] font-black text-white group-hover/res:text-teal transition-colors">{c.name}</div>
                    <div className="flex items-center justify-between mt-2">
                      <div className="text-[11px] font-bold text-slate-500 uppercase tracking-widest">{c.type.replace('_', ' ')}</div>
                      <div className="text-[10px] font-black text-slate-400">{fmt(c.members_count)} MBRS</div>
                    </div>
                  </Link>
                )},
              ].map((col) => (
                <div key={col.title} className="rounded-2xl border border-white/[0.06] bg-white/[0.02] p-4">
                  <div className="text-[11px] font-black uppercase tracking-[0.2em] text-slate-600 mb-4 border-b border-white/5 pb-2">{col.title}</div>
                  <div className="space-y-3">{col.items.length ? col.items.map((item: any) => col.render(item)) : <div className="py-4 text-center text-xs font-bold text-slate-600">No signals found</div>}</div>
                </div>
              ))}
            </div>
          </section>
        )}

        {/* ── Content ──────────────────────────────────────── */}
        {loading ? (
          <div className="flex flex-col items-center gap-6 py-32">
            <div className="relative flex h-14 w-14">
              <div className="animate-ping absolute inline-flex h-full w-full rounded-full bg-teal opacity-20"></div>
              <div className="relative inline-flex rounded-full h-14 w-14 border-4 border-teal/20 border-t-teal animate-spin"></div>
            </div>
            <div className="flex flex-col items-center">
              <p className="text-xs font-black uppercase tracking-[0.3em] text-white">Synthesizing Feed</p>
              <p className="mt-1 text-[10px] font-bold text-slate-500 uppercase tracking-widest animate-pulse">Syncing with the flow</p>
            </div>
          </div>
        ) : (
          <div className="space-y-10 pb-24">
            {/* ── TRENDING TAB ─────────────────────────────── */}
            {tab === "trending" && (
              <>
                {/* Live Rooms Strip */}
                {liveRooms.length > 0 && (
                  <section>
                    <SectionHeader title="Live Momentum" badge={`${liveRooms.length} ON AIR`} badgeColor="rose" href="/circles" />
                    <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                      {liveRooms.map((room) => (
                        <Link key={room.id} href="/circles" className="group relative rounded-3xl border border-white/[0.06] bg-slate-950/40 backdrop-blur-sm p-5 transition-all duration-500 hover:border-rose-500/40 hover:-translate-y-1 hover:shadow-[0_20px_40px_rgba(244,63,94,0.1)]">
                          <div className="absolute top-0 right-0 p-4 opacity-0 group-hover:opacity-100 transition-opacity">
                             <div className="h-2 w-2 rounded-full bg-rose-500 animate-ping" />
                          </div>
                          <div className="flex items-center justify-between mb-4">
                            <span className="inline-flex items-center gap-2 rounded-full bg-rose-500/10 border border-rose-500/20 px-3 py-1 text-[10px] font-black uppercase tracking-[0.16em] text-rose-300">
                              <span className="w-1.5 h-1.5 rounded-full bg-rose-500" /> LIVE ROOM
                            </span>
                            <span className="text-[11px] font-black text-teal">{room.entry_fee_drops} ◆</span>
                          </div>
                          <div className="text-[15px] font-black text-white leading-relaxed mb-5 line-clamp-2 group-hover:text-teal transition-colors min-h-[48px]">{room.title}</div>
                          <div className="flex items-center gap-3 pt-4 border-t border-white/5">
                            <Avatar src={room.host?.avatar_url} name={room.host?.name} size="sm" />
                            <div className="min-w-0">
                              <div className="truncate text-xs font-black text-white">{room.host?.name}</div>
                              <div className="truncate text-[10px] font-bold text-slate-500">{fmt(room.participants_count)} listening</div>
                            </div>
                          </div>
                        </Link>
                      ))}
                    </div>
                  </section>
                )}

                {/* Hashtags heating up */}
                <section>
                  <SectionHeader title="Signals Heatmap" badge="TRENDING" href="/feed" linkText="Open main feed" />
                  <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {(trending?.hashtags || []).map((h, i) => (
                      <Link key={h.tag} href={`/discover?tag=${encodeURIComponent(h.tag)}`}
                        className="group relative overflow-hidden rounded-3xl border border-white/[0.08] bg-slate-950/40 p-5 transition-all duration-500 hover:border-teal/40 hover:-translate-y-1">
                        <div className="absolute -right-4 -bottom-4 w-24 h-24 bg-teal/5 rounded-full blur-2xl group-hover:bg-teal/10 transition-colors" />
                        <div className="flex items-center justify-between mb-3">
                          <div className="flex items-center gap-2">
                            <span className="text-[10px] font-black uppercase tracking-[0.16em] text-slate-600">Rank #{i + 1}</span>
                            <span className="h-1 w-1 rounded-full bg-slate-800" />
                            <span className="text-[10px] font-black text-emerald-400">+{Math.floor(Math.random() * 40) + 10}%</span>
                          </div>
                          <span className="rounded-full bg-white/5 border border-white/10 px-3 py-1 text-[10px] font-black text-slate-400">{fmt(h.count)} Signals</span>
                        </div>
                        <div className="text-2xl font-black text-white group-hover:text-teal transition-colors tracking-tight">{h.tag}</div>
                        <div className="mt-2 text-xs font-bold text-slate-500 leading-relaxed uppercase tracking-wider">{h.label}</div>
                      </Link>
                    ))}
                  </div>
                </section>

                {/* Top Waves */}
                <section>
                  <SectionHeader title="High Performance Waves" />
                  <div className="grid gap-3 lg:grid-cols-2">
                    {(trending?.top_waves || []).map((w, i) => (
                      <Link key={w.id} href={`/feed?wave=${w.id}`}
                        className="group flex items-center gap-5 rounded-[24px] border border-white/[0.06] bg-slate-950/40 p-4 transition-all duration-300 hover:border-teal/30 hover:bg-slate-950/60">
                        <div className="flex items-center justify-center w-10 h-10 rounded-2xl bg-teal/10 border border-teal/20 text-sm font-black text-teal shrink-0 shadow-lg">{i + 1}</div>
                        <div className="min-w-0 flex-1">
                          <div className="truncate text-[15px] font-black text-white group-hover:text-teal transition-colors mb-0.5">{w.title}</div>
                          <div className="truncate text-[12px] font-bold text-slate-500">@{w.user.username}</div>
                        </div>
                        <div className="flex gap-4 items-center shrink-0">
                          <div className="text-right">
                            <div className="text-[11px] font-black text-white">{fmt(w.views_count)}</div>
                            <div className="text-[9px] font-black text-slate-500 uppercase tracking-tighter">VIEWS</div>
                          </div>
                          <div className="text-right">
                            <div className="text-[11px] font-black text-white">{fmt(w.likes_count)}</div>
                            <div className="text-[9px] font-black text-slate-500 uppercase tracking-tighter">LIKES</div>
                          </div>
                        </div>
                      </Link>
                    ))}
                  </div>
                </section>

                {/* Challenge - Now with more WOW */}
                {trending?.active_challenge && (
                  <section className="relative overflow-hidden rounded-[40px] border border-white/10 bg-slate-950 group/challenge">
                    <div className="absolute inset-0 bg-gradient-to-br from-indigo-600/20 to-violet-600/20 animate-pulse" />
                    <div className="absolute top-0 right-0 w-full h-full bg-[radial-gradient(circle_at_top_right,_rgba(99,102,241,0.15),_transparent_60%)]" />
                    
                    <div className="relative p-8 sm:p-12 flex flex-col md:flex-row items-center gap-10">
                      <div className="flex-1 text-center md:text-left">
                        <div className="inline-flex items-center gap-2 rounded-full bg-white/10 border border-white/20 px-4 py-1.5 text-[10px] font-black uppercase tracking-[0.25em] text-white mb-6">
                           WAVE CHALLENGE ACTIVE
                        </div>
                        <h2 className="text-4xl sm:text-5xl font-black text-white leading-tight mb-6 tracking-tight">{trending.active_challenge.title}</h2>
                        <p className="text-lg text-slate-300 leading-relaxed max-w-xl mb-0">{trending.active_challenge.description}</p>
                      </div>
                      
                      <div className="w-full md:w-[320px] shrink-0">
                        <div className="glass-dark border border-white/10 rounded-[32px] p-6 text-center shadow-2xl">
                          <div className="text-[11px] font-black uppercase tracking-[0.2em] text-indigo-300 mb-2">PRIZE POOL TOTAL</div>
                          <div className="text-4xl font-black text-white mb-8 flex items-center justify-center gap-2">
                             {trending.active_challenge.prize_pool.toLocaleString()} <span className="text-teal text-3xl">◆</span>
                          </div>
                          <Link href="/feed" className="block w-full rounded-2xl bg-white py-4 text-[13px] font-black uppercase tracking-[0.2em] text-indigo-950 hover:bg-teal hover:text-white transition-all duration-300 shadow-xl active:scale-95">
                            ENTER CHALLENGE
                          </Link>
                        </div>
                      </div>
                    </div>
                  </section>
                )}
              </>
            )}

            {/* ── CREATORS TAB ─────────────────────────────── */}
            {tab === "creators" && (
              <section className="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
                {topCreators.map((c, i) => (
                  <Link key={c.id} href={`/profile/${c.username}`}
                    className="group relative rounded-[32px] border border-white/[0.08] bg-slate-950/40 p-6 transition-all duration-500 hover:border-teal/30 hover:-translate-y-1">
                    <div className="flex items-start justify-between gap-4 mb-6">
                      <div className="flex items-center gap-4">
                        <div className="relative">
                           <Avatar src={c.avatar_url} name={c.name} size="xl" role={c.role} />
                           {i < 3 && (
                             <div className="absolute -top-1 -right-1 w-6 h-6 rounded-full bg-teal flex items-center justify-center text-[10px] font-black text-white border-2 border-slate-950">#{i+1}</div>
                           )}
                        </div>
                        <div className="min-w-0">
                          <div className="truncate text-xl font-black text-white group-hover:text-teal transition-colors tracking-tight">{c.name}</div>
                          <div className="truncate text-sm font-bold text-slate-500">@{c.username}</div>
                        </div>
                      </div>
                    </div>
                    <p className="text-sm font-medium text-slate-400 leading-relaxed line-clamp-2 min-h-[48px] mb-6">
                      {c.bio || "High-signal creator building on Zumi."}
                    </p>
                    <div className="flex items-center justify-between pt-5 border-t border-white/5">
                      <div className="flex flex-col">
                        <span className="text-[14px] font-black text-white leading-none">{fmt(c.followers_count || 0)}</span>
                        <span className="text-[9px] font-black text-slate-600 uppercase tracking-widest mt-1">FOLLOWERS</span>
                      </div>
                      <span className="rounded-full bg-teal text-white px-5 py-2.5 text-[11px] font-black uppercase tracking-[0.16em] transition-all group-hover:scale-105">View Profile</span>
                    </div>
                  </Link>
                ))}
                {topCreators.length === 0 && <EmptyState text="Mapping signals for new creator recommendations." />}
              </section>
            )}

            {/* ── SKILL DROPS TAB ──────────────────────────── */}
            {tab === "skill-drops" && (
              <section className="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                {skillDrops.map((d) => (
                  <div key={d.id} className="group relative overflow-hidden rounded-[32px] border border-white/[0.08] bg-slate-950/40 transition-all duration-500 hover:border-violet-500/40 hover:-translate-y-1">
                    <div className="h-44 bg-gradient-to-br from-violet-600/20 via-slate-900 to-indigo-950 p-6 flex flex-col justify-between group-hover:scale-[1.02] transition-transform duration-700">
                      <div className="flex justify-between items-start">
                        <span className="rounded-full bg-white/10 backdrop-blur-md border border-white/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.16em] text-violet-100">SKILL DROP</span>
                        <div className="flex flex-col items-end">
                           <span className="text-2xl font-black text-white">{d.price_drops} ◆</span>
                           <span className="text-[9px] font-black text-violet-300 uppercase tracking-widest">ONE TIME</span>
                        </div>
                      </div>
                      <div className="text-2xl font-black text-white leading-tight line-clamp-2 tracking-tight group-hover:text-violet-300 transition-colors">{d.title}</div>
                    </div>
                    <div className="p-6">
                      <div className="flex items-center gap-3 mb-4">
                        <Avatar src={d.user.avatar_url} name={d.user.name} size="sm" role={d.user.role} />
                        <div className="min-w-0 flex-1">
                          <div className="truncate text-sm font-black text-white">{d.user.name}</div>
                          <div className="truncate text-[11px] font-bold text-slate-500">@{d.user.username}</div>
                        </div>
                      </div>
                      <p className="text-sm font-medium text-slate-400 leading-relaxed line-clamp-2 mb-6 h-10">{d.description}</p>
                      <div className="flex items-center justify-between pt-5 border-t border-white/5">
                        <div className="flex gap-4">
                           <div className="flex flex-col">
                              <span className="text-[12px] font-black text-white">{fmt(d.sales_count)}</span>
                              <span className="text-[9px] font-black text-slate-600 uppercase tracking-tighter">SALES</span>
                           </div>
                           <div className="flex flex-col">
                              <span className="text-[12px] font-black text-white">{d.rating_avg ? `${d.rating_avg.toFixed(1)} ★` : "NEW"}</span>
                              <span className="text-[9px] font-black text-slate-600 uppercase tracking-tighter">RATING</span>
                           </div>
                        </div>
                        <button className="rounded-2xl bg-white/5 border border-white/10 px-6 py-2.5 text-[11px] font-black uppercase tracking-[0.16em] text-white hover:bg-violet-600 hover:text-white transition-all duration-300">DETAILS</button>
                      </div>
                    </div>
                  </div>
                ))}
                {skillDrops.length === 0 && <EmptyState text="High-signal skill drops are currently syncing." />}
              </section>
            )}

            {/* ── CIRCLES TAB ──────────────────────────────── */}
            {tab === "circles" && (
              <section className="grid gap-6">
                {visibleCircles.map((c) => (
                  <Link key={c.id} href={`/circles/${c.slug}`}
                    className="group flex flex-col md:flex-row items-center gap-8 rounded-[40px] border border-white/[0.08] bg-slate-950/40 p-8 transition-all duration-500 hover:border-teal/30 hover:bg-slate-950/60">
                    <div className="flex-1 min-w-0">
                      <div className="flex flex-wrap items-center gap-3 mb-4">
                        <span className="rounded-full bg-teal/10 border border-teal/20 px-4 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-teal">{c.type.replaceAll("_", " ")}</span>
                        <div className="h-1 w-1 rounded-full bg-slate-800" />
                        <span className="text-[10px] font-black uppercase tracking-[0.2em] text-slate-500">{c.status}</span>
                      </div>
                      <h2 className="text-3xl font-black text-white group-hover:text-teal transition-colors tracking-tight mb-4">{c.name}</h2>
                      <p className="text-base font-medium text-slate-400 leading-relaxed line-clamp-2 max-w-3xl mb-8">{c.description || "A signal-rich community for curated conversation, drops, and flow."}</p>
                      
                      <div className="flex flex-wrap gap-8">
                         <div className="flex flex-col">
                            <span className="text-lg font-black text-white">{fmt(c.members_count)}</span>
                            <span className="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">MEMBERS</span>
                         </div>
                         <div className="flex flex-col">
                            <span className="text-lg font-black text-white">{c.owner.name}</span>
                            <span className="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">CURATOR</span>
                         </div>
                         <div className="flex flex-col">
                            <span className="text-lg font-black text-teal">{c.is_member ? "JOINED" : "OPEN"}</span>
                            <span className="text-[10px] font-black text-slate-600 uppercase tracking-[0.2em]">STATUS</span>
                         </div>
                      </div>
                    </div>
                    
                    <div className="flex flex-col items-center gap-5 shrink-0 bg-white/5 p-8 rounded-[32px] border border-white/5 group-hover:border-teal/20 transition-all">
                      <Avatar src={c.owner.avatar_url} name={c.owner.name} size="xl" role={c.owner.role} />
                      <button className="w-full min-w-[140px] rounded-2xl bg-white py-3.5 text-[11px] font-black uppercase tracking-[0.2em] text-slate-950 transition-all hover:bg-teal hover:text-white hover:scale-105 active:scale-95 shadow-xl">VIEW CIRCLE</button>
                    </div>
                  </Link>
                ))}
                {visibleCircles.length === 0 && <EmptyState text="Searching for signals in the circle directory." />}
              </section>
            )}
          </div>
        )}
      </div>
    </ShellLayout>
  );
}

/* ── Shared small components ──────────────────────────── */

function SectionHeader({ title, badge, badgeColor = "teal", href, linkText = "Explore all" }: {
  title: string; badge?: string; badgeColor?: string; href?: string; linkText?: string;
}) {
  return (
    <div className="flex flex-wrap items-end justify-between gap-4 mb-8">
      <div className="flex items-center gap-4">
        <h2 className="text-2xl font-black text-white tracking-tight">{title}</h2>
        {badge && (
          <span className={`rounded-full px-3 py-1 text-[9px] font-black uppercase tracking-[0.2em] ${
            badgeColor === "rose" ? "bg-rose-500/10 border border-rose-500/20 text-rose-300 shadow-[0_0_15px_rgba(244,63,94,0.1)]" : "bg-teal/10 border border-teal/20 text-teal"
          }`}>{badge}</span>
        )}
      </div>
      {href && (
        <Link href={href} className="group flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.16em] text-slate-500 hover:text-teal transition-all">
          {linkText}
          <svg className="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M13 7l5 5m0 0l-5 5m5-5H6" />
          </svg>
        </Link>
      )}
    </div>
  );
}

function EmptyState({ text }: { text: string }) {
  return (
    <div className="col-span-full rounded-[32px] border-2 border-dashed border-white/10 px-6 py-24 flex flex-col items-center justify-center">
       <div className="w-16 h-16 rounded-full bg-white/5 flex items-center justify-center mb-6">
          <svg className="w-8 h-8 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
       </div>
       <p className="text-sm font-black uppercase tracking-[0.2em] text-slate-500">{text}</p>
    </div>
  );
}
