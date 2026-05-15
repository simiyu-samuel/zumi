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

function fmt(v: number) {
  if (v >= 1_000_000) return `${(v / 1_000_000).toFixed(1)}M`;
  if (v >= 1_000) return `${(v / 1_000).toFixed(1)}K`;
  return v.toString();
}

const TAB_META: Record<DiscoverTab, { icon: string; label: string }> = {
  trending: { icon: "🔥", label: "Trending" },
  creators: { icon: "✦", label: "Creators" },
  "skill-drops": { icon: "⚡", label: "Skill Drops" },
  circles: { icon: "◉", label: "Circles" },
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
      <div className="flex-1 px-4 py-5 sm:px-6 sm:py-6">
        {/* ── Hero Search Section ───────────────────────────── */}
        <section className="relative overflow-hidden rounded-3xl border border-white/[0.06] bg-gradient-to-br from-slate-900/95 via-slate-950 to-[#0a1628] p-6 sm:p-8 mb-6">
          <div className="absolute -top-24 -right-24 w-72 h-72 rounded-full bg-teal/10 blur-[100px] pointer-events-none" />
          <div className="absolute -bottom-16 -left-16 w-56 h-56 rounded-full bg-violet/8 blur-[80px] pointer-events-none" />
          <div className="relative z-10">
            <div className="flex items-center gap-2 mb-4">
              <span className="inline-flex items-center gap-1.5 rounded-full bg-teal/10 border border-teal/20 px-3 py-1 text-[10px] font-black uppercase tracking-[0.2em] text-teal">
                <span className="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse" /> Explore
              </span>
              <span className="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">{fmt(liveRooms.length)} live now</span>
            </div>
            <h1 className="font-head text-3xl sm:text-4xl font-black leading-[1.05] tracking-tight text-white max-w-lg">
              Discover what&apos;s <span className="text-teal">building momentum</span> on Zumi
            </h1>
            <p className="mt-3 text-sm leading-relaxed text-slate-400 max-w-md">Search creators, waves, circles and skill drops — then jump straight in.</p>

            {/* Search bar */}
            <div className="relative mt-5 max-w-lg">
              <div className="pointer-events-none absolute inset-y-0 left-4 flex items-center text-slate-500">
                <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" /></svg>
              </div>
              <input value={query} onChange={(e) => { setQuery(e.target.value); if (e.target.value.trim().length < 2) setSearchResults(null); }}
                type="text" placeholder="Search creators, waves, circles..."
                className="w-full rounded-2xl border border-white/10 bg-white/[0.04] backdrop-blur-md py-3 pl-11 pr-4 text-sm font-medium text-white outline-none transition-all placeholder:text-slate-600 focus:border-teal/40 focus:bg-white/[0.06] focus:ring-1 focus:ring-teal/20"
              />
            </div>
          </div>
        </section>

        {/* ── Tabs ──────────────────────────────────────────── */}
        <nav className="flex gap-2 mb-6 overflow-x-auto no-scrollbar pb-1">
          {(Object.keys(TAB_META) as DiscoverTab[]).map((key) => (
            <button key={key} onClick={() => setTab(key)}
              className={`flex items-center gap-1.5 whitespace-nowrap rounded-xl px-4 py-2.5 text-[11px] font-black uppercase tracking-[0.18em] transition-all ${
                tab === key
                  ? "bg-teal/15 border border-teal/30 text-teal shadow-[0_0_20px_rgba(26,158,117,0.1)]"
                  : "bg-white/[0.03] border border-white/[0.06] text-slate-500 hover:text-white hover:border-white/15"
              }`}>
              <span className="text-sm">{TAB_META[key].icon}</span> {TAB_META[key].label}
            </button>
          ))}
        </nav>

        {/* ── Search Results Overlay ───────────────────────── */}
        {sr && (
          <section className="mb-6 rounded-2xl border border-teal/15 bg-slate-950/80 backdrop-blur-xl p-5 animate-fade-in">
            <div className="flex items-center justify-between mb-4">
              <div>
                <div className="text-[10px] font-black uppercase tracking-[0.2em] text-teal">Search results</div>
                <div className="mt-0.5 text-xs text-slate-500">Matches for &ldquo;{query.trim()}&rdquo;</div>
              </div>
              <button onClick={() => setQuery("")} className="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500 hover:text-white transition-colors">Clear</button>
            </div>
            <div className="grid gap-3 sm:grid-cols-3">
              {[
                { title: "Creators", items: (sr.users || []).slice(0, 3), render: (u: WaveUser) => (
                  <Link key={u.id} href={`/profile/${u.username}`} className="flex items-center gap-2.5 rounded-xl p-2 hover:bg-white/5 transition-colors">
                    <Avatar src={u.avatar_url} name={u.name} size="sm" role={u.role} />
                    <div className="min-w-0"><div className="truncate text-sm font-bold text-white">{u.name}</div><div className="truncate text-[11px] text-slate-500">@{u.username}</div></div>
                  </Link>
                )},
                { title: "Waves", items: (sr.waves || []).slice(0, 3), render: (w: Wave) => (
                  <Link key={w.id} href={`/feed?wave=${w.id}`} className="block rounded-xl border border-white/5 bg-white/[0.02] p-2.5 hover:border-teal/20 transition-all">
                    <div className="truncate text-sm font-bold text-white">{w.title}</div>
                    <div className="truncate text-[11px] text-slate-500 mt-0.5">@{w.user.username}</div>
                  </Link>
                )},
                { title: "Circles", items: (sr.circles || []).slice(0, 3), render: (c: Circle) => (
                  <Link key={c.id} href={`/circles/${c.slug}`} className="block rounded-xl border border-white/5 bg-white/[0.02] p-2.5 hover:border-teal/20 transition-all">
                    <div className="truncate text-sm font-bold text-white">{c.name}</div>
                    <div className="text-[11px] text-slate-500 mt-0.5">{fmt(c.members_count)} members</div>
                  </Link>
                )},
              ].map((col) => (
                <div key={col.title} className="rounded-xl border border-white/[0.06] bg-white/[0.02] p-3">
                  <div className="text-[10px] font-black uppercase tracking-[0.16em] text-slate-500 mb-2">{col.title}</div>
                  <div className="space-y-2">{col.items.length ? col.items.map((item: any) => col.render(item)) : <div className="text-xs text-slate-600">No matches</div>}</div>
                </div>
              ))}
            </div>
          </section>
        )}

        {/* ── Content ──────────────────────────────────────── */}
        {loading ? (
          <div className="flex flex-col items-center gap-4 py-24">
            <div className="h-10 w-10 animate-spin rounded-full border-2 border-teal/20 border-t-teal" />
            <p className="text-xs font-black uppercase tracking-[0.2em] text-slate-500">Mapping the flow...</p>
          </div>
        ) : (
          <div className="space-y-6 pb-16">
            {/* ── TRENDING TAB ─────────────────────────────── */}
            {tab === "trending" && (
              <>
                {/* Live Rooms Strip */}
                {liveRooms.length > 0 && (
                  <section>
                    <SectionHeader title="Live Now" badge={`${liveRooms.length} rooms`} badgeColor="rose" href="/circles" />
                    <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                      {liveRooms.map((room) => (
                        <Link key={room.id} href="/circles" className="group rounded-2xl border border-white/[0.06] bg-white/[0.03] p-4 transition-all hover:border-rose-400/25 hover:-translate-y-0.5">
                          <div className="flex items-center justify-between mb-3">
                            <span className="inline-flex items-center gap-1.5 rounded-full bg-rose-500/10 px-2 py-0.5 text-[10px] font-black uppercase tracking-[0.16em] text-rose-300">
                              <span className="w-1.5 h-1.5 rounded-full bg-rose-400 animate-pulse" /> Live
                            </span>
                            <span className="text-[10px] font-black text-teal">{room.entry_fee_drops} ◆</span>
                          </div>
                          <div className="text-sm font-bold text-white leading-snug mb-3 line-clamp-2 group-hover:text-teal transition-colors">{room.title}</div>
                          <div className="flex items-center gap-2">
                            <Avatar src={room.host?.avatar_url} name={room.host?.name} size="sm" />
                            <div className="min-w-0">
                              <div className="truncate text-xs font-bold text-white">{room.host?.name}</div>
                              <div className="truncate text-[10px] text-slate-500">{fmt(room.participants_count)} listening</div>
                            </div>
                          </div>
                        </Link>
                      ))}
                    </div>
                  </section>
                )}

                {/* Hashtags */}
                <section>
                  <SectionHeader title="Hashtags heating up" href="/feed" linkText="Open feed" />
                  <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                    {(trending?.hashtags || []).map((h, i) => (
                      <Link key={h.tag} href={`/discover?tag=${encodeURIComponent(h.tag)}`}
                        className="group rounded-2xl border border-white/[0.06] bg-white/[0.03] p-4 transition-all hover:border-teal/25 hover:-translate-y-0.5">
                        <div className="flex items-center justify-between mb-2">
                          <span className="text-[10px] font-black uppercase tracking-[0.16em] text-slate-600">#{i + 1}</span>
                          <span className="rounded-full bg-teal/10 px-2.5 py-0.5 text-[10px] font-black text-teal">{fmt(h.count)} posts</span>
                        </div>
                        <div className="text-lg font-black text-white group-hover:text-teal transition-colors">{h.tag}</div>
                        <div className="mt-1 text-xs text-slate-400 leading-relaxed">{h.label}</div>
                      </Link>
                    ))}
                  </div>
                </section>

                {/* Top Waves */}
                <section>
                  <SectionHeader title="Top waves" />
                  <div className="space-y-2">
                    {(trending?.top_waves || []).map((w, i) => (
                      <Link key={w.id} href={`/feed?wave=${w.id}`}
                        className="group flex items-center gap-3 rounded-2xl border border-white/[0.06] bg-white/[0.03] p-3.5 transition-all hover:border-teal/20">
                        <div className="flex items-center justify-center w-8 h-8 rounded-xl bg-white/5 text-sm font-black text-teal shrink-0">{i + 1}</div>
                        <div className="min-w-0 flex-1">
                          <div className="truncate text-sm font-bold text-white group-hover:text-teal transition-colors">{w.title}</div>
                          <div className="truncate text-[11px] text-slate-500">@{w.user.username}</div>
                        </div>
                        <div className="flex gap-3 text-[10px] font-bold text-slate-500 shrink-0">
                          <span>{fmt(w.views_count)} views</span>
                          <span>{fmt(w.likes_count)} likes</span>
                        </div>
                      </Link>
                    ))}
                  </div>
                </section>

                {/* Challenge */}
                {trending?.active_challenge && (
                  <section className="overflow-hidden rounded-2xl border border-indigo-400/20 bg-gradient-to-br from-indigo-600/90 to-indigo-900/95 p-6">
                    <div className="flex flex-col sm:flex-row sm:items-end gap-5">
                      <div className="flex-1">
                        <span className="text-[10px] font-black uppercase tracking-[0.2em] text-indigo-200">Active challenge</span>
                        <h2 className="mt-2 text-2xl font-black text-white leading-tight">{trending.active_challenge.title}</h2>
                        <p className="mt-2 text-sm text-indigo-100/80 leading-relaxed max-w-lg">{trending.active_challenge.description}</p>
                      </div>
                      <div className="flex items-center gap-4 shrink-0">
                        <div>
                          <div className="text-[10px] font-black uppercase tracking-[0.16em] text-indigo-200">Prize</div>
                          <div className="text-xl font-black text-white">{trending.active_challenge.prize_pool.toLocaleString()} ◆</div>
                        </div>
                        <Link href="/feed" className="rounded-xl bg-white px-5 py-3 text-[11px] font-black uppercase tracking-[0.16em] text-indigo-900 hover:scale-[1.02] transition-transform">
                          Enter
                        </Link>
                      </div>
                    </div>
                  </section>
                )}
              </>
            )}

            {/* ── CREATORS TAB ─────────────────────────────── */}
            {tab === "creators" && (
              <section className="grid gap-4 sm:grid-cols-2">
                {topCreators.map((c, i) => (
                  <Link key={c.id} href={`/profile/${c.username}`}
                    className="group rounded-2xl border border-white/[0.06] bg-gradient-to-b from-white/[0.05] to-white/[0.02] p-5 transition-all hover:border-teal/25 hover:-translate-y-0.5">
                    <div className="flex items-start justify-between gap-3 mb-4">
                      <div className="flex items-center gap-3">
                        <Avatar src={c.avatar_url} name={c.name} size="lg" role={c.role} />
                        <div className="min-w-0">
                          <div className="truncate text-lg font-black text-white group-hover:text-teal transition-colors">{c.name}</div>
                          <div className="truncate text-sm text-slate-500">@{c.username}</div>
                        </div>
                      </div>
                      <span className="rounded-full bg-white/5 border border-white/10 px-2.5 py-1 text-[10px] font-black text-slate-400">#{i + 1}</span>
                    </div>
                    <p className="text-sm text-slate-300 leading-relaxed line-clamp-2 min-h-[40px]">
                      {c.bio || "Creator building high-signal community on Zumi."}
                    </p>
                    <div className="mt-4 flex items-center justify-between">
                      <span className="text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">{fmt(c.followers_count || 0)} followers</span>
                      <span className="rounded-full bg-teal/10 px-3 py-1.5 text-[10px] font-black uppercase tracking-[0.16em] text-teal">View</span>
                    </div>
                  </Link>
                ))}
                {topCreators.length === 0 && <EmptyState text="Creator recommendations will appear here." />}
              </section>
            )}

            {/* ── SKILL DROPS TAB ──────────────────────────── */}
            {tab === "skill-drops" && (
              <section className="grid gap-4 sm:grid-cols-2">
                {skillDrops.map((d) => (
                  <div key={d.id} className="group overflow-hidden rounded-2xl border border-white/[0.06] transition-all hover:border-violet/25 hover:-translate-y-0.5">
                    <div className="h-36 bg-gradient-to-br from-violet/30 via-slate-900 to-indigo-950 p-4 flex flex-col justify-between">
                      <div className="flex justify-between">
                        <span className="rounded-full bg-white/10 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-[0.16em] text-violet-100">Skill Drop</span>
                        <span className="rounded-full bg-black/25 px-2.5 py-0.5 text-[10px] font-black text-white">{d.price_drops} ◆</span>
                      </div>
                      <div className="text-xl font-black text-white leading-tight line-clamp-2">{d.title}</div>
                    </div>
                    <div className="p-4 bg-slate-950/60">
                      <div className="flex items-center gap-2.5 mb-3">
                        <Avatar src={d.user.avatar_url} name={d.user.name} size="sm" role={d.user.role} />
                        <div className="min-w-0">
                          <div className="truncate text-sm font-bold text-white">{d.user.name}</div>
                          <div className="truncate text-[11px] text-slate-500">@{d.user.username}</div>
                        </div>
                      </div>
                      <p className="text-xs text-slate-400 leading-relaxed line-clamp-2 mb-3">{d.description}</p>
                      <div className="flex justify-between text-[10px] font-bold text-slate-500">
                        <span>{fmt(d.sales_count)} sales</span>
                        <span>{d.rating_avg ? `${d.rating_avg.toFixed(1)} ★` : "New"}</span>
                      </div>
                    </div>
                  </div>
                ))}
                {skillDrops.length === 0 && <EmptyState text="No skill drops available yet." />}
              </section>
            )}

            {/* ── CIRCLES TAB ──────────────────────────────── */}
            {tab === "circles" && (
              <section className="space-y-3">
                {visibleCircles.map((c) => (
                  <Link key={c.id} href={`/circles/${c.slug}`}
                    className="group flex items-center gap-5 rounded-2xl border border-white/[0.06] bg-gradient-to-r from-white/[0.04] to-transparent p-5 transition-all hover:border-teal/25">
                    <div className="flex-1 min-w-0">
                      <div className="flex flex-wrap items-center gap-2 mb-2">
                        <span className="rounded-full bg-teal/10 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-[0.14em] text-teal">{c.type.replaceAll("_", " ")}</span>
                        <span className="rounded-full bg-white/5 px-2.5 py-0.5 text-[10px] font-black uppercase tracking-[0.14em] text-slate-400">{c.status}</span>
                      </div>
                      <h2 className="text-xl font-black text-white group-hover:text-teal transition-colors">{c.name}</h2>
                      <p className="mt-1.5 text-sm text-slate-400 leading-relaxed line-clamp-2 max-w-2xl">{c.description || "A creator-owned community for conversation, events, and exclusive drops."}</p>
                      <div className="mt-3 flex flex-wrap gap-4 text-[11px] font-bold text-slate-500">
                        <span>{fmt(c.members_count)} members</span>
                        <span>by {c.owner.name}</span>
                        <span>{c.is_member ? "Joined" : "Open"}</span>
                      </div>
                    </div>
                    <div className="flex flex-col items-center gap-3 shrink-0">
                      <Avatar src={c.owner.avatar_url} name={c.owner.name} size="md" role={c.owner.role} />
                      <span className="rounded-full bg-white px-4 py-2 text-[10px] font-black uppercase tracking-[0.14em] text-slate-950 group-hover:scale-[1.03] transition-transform">View</span>
                    </div>
                  </Link>
                ))}
                {visibleCircles.length === 0 && <EmptyState text="No circles found." />}
              </section>
            )}
          </div>
        )}
      </div>
    </ShellLayout>
  );
}

/* ── Shared small components ──────────────────────────── */

function SectionHeader({ title, badge, badgeColor = "teal", href, linkText = "View all" }: {
  title: string; badge?: string; badgeColor?: string; href?: string; linkText?: string;
}) {
  return (
    <div className="flex items-center justify-between mb-4">
      <div className="flex items-center gap-2.5">
        <h2 className="text-lg font-black text-white">{title}</h2>
        {badge && (
          <span className={`rounded-full px-2.5 py-0.5 text-[10px] font-black uppercase tracking-[0.14em] ${
            badgeColor === "rose" ? "bg-rose-500/10 text-rose-300" : "bg-teal/10 text-teal"
          }`}>{badge}</span>
        )}
      </div>
      {href && <Link href={href} className="text-[11px] font-black uppercase tracking-[0.16em] text-teal hover:text-teal-light transition-colors">{linkText}</Link>}
    </div>
  );
}

function EmptyState({ text }: { text: string }) {
  return (
    <div className="col-span-full rounded-2xl border border-dashed border-white/10 px-6 py-14 text-center text-sm text-slate-500">{text}</div>
  );
}
