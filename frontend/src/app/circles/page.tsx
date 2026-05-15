"use client";

import { useEffect, useMemo, useState } from "react";
import Link from "next/link";
import { ShellLayout } from "@/components/layout/ShellLayout";
import { getCircles, getGatedRooms, type Circle, type GatedRoom } from "@/lib/api";
import { useAuth } from "@/context/AuthContext";
import { Avatar } from "@/components/ui/Avatar";

function formatCompact(value: number) {
  if (value >= 1000000) return `${(value / 1000000).toFixed(1)}M`;
  if (value >= 1000) return `${(value / 1000).toFixed(1)}K`;
  return value.toString();
}

function formatCircleType(type: string) {
  return type.replaceAll("_", " ");
}

export default function CirclesPage() {
  const { token } = useAuth();
  const [circles, setCircles] = useState<Circle[]>([]);
  const [rooms, setRooms] = useState<GatedRoom[]>([]);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    let cancelled = false;

    async function fetchData() {
      setLoading(true);

      try {
        const [circlesRes, roomsRes] = await Promise.all([getCircles(), getGatedRooms()]);

        if (cancelled) return;

        setCircles(circlesRes.data || []);
        setRooms(roomsRes.data || []);
      } catch (error) {
        console.error("Circles fetch error:", error);
      } finally {
        if (!cancelled) {
          setLoading(false);
        }
      }
    }

    fetchData();

    return () => {
      cancelled = true;
    };
  }, [token]);

  const activeRooms = useMemo(() => rooms.filter((room) => room.status === "live"), [rooms]);
  const premiumCircles = useMemo(
    () => circles.filter((circle) => circle.type.toLowerCase().includes("premium")),
    [circles]
  );
  const publicCircles = useMemo(
    () => circles.filter((circle) => !circle.type.toLowerCase().includes("premium")),
    [circles]
  );

  const stats = [
    { label: "Joined circles", value: circles.length, accent: "text-white" },
    { label: "Live rooms", value: activeRooms.length, accent: "text-rose-300" },
    { label: "Premium spaces", value: premiumCircles.length, accent: "text-violet-300" },
  ];

  return (
    <ShellLayout>
      <div className="flex-1 px-5 py-6 sm:px-8 sm:py-8">
        <section className="overflow-hidden rounded-[32px] border border-white/8 bg-[radial-gradient(circle_at_top_left,_rgba(26,158,117,0.2),_transparent_36%),linear-gradient(135deg,rgba(12,17,24,0.98),rgba(15,23,42,0.9))] p-6 sm:p-8">
          <div className="grid gap-6 lg:grid-cols-[minmax(0,1.2fr)_minmax(280px,0.8fr)] lg:items-end">
            <div>
              <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-teal/20 bg-teal/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.22em] text-teal">
                Circles
                <span className="h-1.5 w-1.5 rounded-full bg-emerald-300" />
                Community layer
              </div>
              <h1 className="font-head text-4xl font-black tracking-tight text-white sm:text-5xl">
                Stay close to your communities and the rooms happening inside them.
              </h1>
              <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-300 sm:text-[15px]">
                Browse your creator-owned spaces, jump into live rooms, and discover where your next deeper connection on Zumi is forming.
              </p>
              <div className="mt-6 flex flex-wrap gap-3">
                <Link
                  href="/discover"
                  className="rounded-full bg-teal px-5 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-slate-950 transition-transform hover:scale-[1.01]"
                >
                  Discover circles
                </Link>
                <button className="rounded-full border border-white/10 bg-white/5 px-5 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-white transition-colors hover:bg-white/10">
                  Create circle
                </button>
              </div>
            </div>

            <div className="grid grid-cols-3 gap-3">
              {stats.map((stat) => (
                <div key={stat.label} className="rounded-[22px] border border-white/8 bg-white/5 px-4 py-4">
                  <div className={`text-2xl font-black ${stat.accent}`}>{stat.value}</div>
                  <div className="mt-1 text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">
                    {stat.label}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </section>

        {loading ? (
          <div className="flex flex-col items-center gap-4 py-20">
            <div className="h-10 w-10 animate-spin rounded-full border-2 border-teal/20 border-t-teal" />
            <p className="text-sm font-bold uppercase tracking-[0.18em] text-slate-500">
              Gathering your circles...
            </p>
          </div>
        ) : (
          <div className="mt-8 space-y-8 pb-14">
            <section className="glass rounded-[28px] p-6">
              <div className="mb-6 flex items-center justify-between">
                <div className="flex items-center gap-3">
                  <h2 className="text-2xl font-black text-white">Live Gated Rooms</h2>
                  {activeRooms.length > 0 && (
                    <div className="h-2.5 w-2.5 rounded-full bg-red-500 shadow-[0_0_10px_rgba(239,68,68,0.55)]" />
                  )}
                </div>
                <Link href="/discover" className="text-[11px] font-black uppercase tracking-[0.18em] text-teal">
                  Explore more
                </Link>
              </div>

              {activeRooms.length > 0 ? (
                <div className="grid gap-4 lg:grid-cols-2">
                  {activeRooms.map((room) => (
                    <Link
                      key={room.id}
                      href="/discover"
                      className="group overflow-hidden rounded-[24px] border border-teal/15 bg-[linear-gradient(135deg,rgba(26,158,117,0.12),rgba(15,23,42,0.92))] p-5 transition-all hover:border-teal/35 hover:-translate-y-1"
                    >
                      <div className="mb-5 flex items-start justify-between gap-4">
                        <div>
                          <div className="mb-2 inline-flex items-center gap-2 rounded-full bg-red-500/12 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-rose-200">
                            <span className="h-1.5 w-1.5 rounded-full bg-red-400" />
                            Live now
                          </div>
                          <h3 className="text-[18px] font-black text-white transition-colors group-hover:text-teal">
                            {room.title}
                          </h3>
                        </div>
                        <div className="rounded-full bg-black/20 px-3 py-1 text-[11px] font-black uppercase tracking-[0.18em] text-teal">
                          {room.entry_fee_drops} ◆
                        </div>
                      </div>

                      <div className="flex items-center gap-3">
                        <Avatar src={room.host?.avatar_url} name={room.host?.name} size="md" />
                        <div className="min-w-0">
                          <div className="truncate text-sm font-bold text-white">{room.host?.name}</div>
                          <div className="truncate text-[11px] font-semibold uppercase tracking-[0.14em] text-slate-500">
                            {formatCompact(room.participants_count)} participants listening
                          </div>
                        </div>
                      </div>
                    </Link>
                  ))}
                </div>
              ) : (
                <div className="rounded-[24px] border border-dashed border-white/10 bg-slate-950/30 px-6 py-14 text-center">
                  <div className="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-white/5 text-slate-600">
                    <svg className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path
                        strokeLinecap="round"
                        strokeLinejoin="round"
                        strokeWidth={1.8}
                        d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"
                      />
                    </svg>
                  </div>
                  <div className="text-sm font-black uppercase tracking-[0.18em] text-slate-500">
                    No live rooms in your circles yet
                  </div>
                  <div className="mt-2 text-sm text-slate-600">
                    The moment a creator goes live, this area becomes your quickest way in.
                  </div>
                </div>
              )}
            </section>

            <section className="grid gap-8 xl:grid-cols-[minmax(0,1fr)_320px]">
              <div className="space-y-5">
                <div className="flex items-center justify-between">
                  <div>
                    <div className="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">
                      Your communities
                    </div>
                    <h2 className="mt-1 text-2xl font-black text-white">Joined circles</h2>
                  </div>
                  <div className="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">
                    {circles.length} total
                  </div>
                </div>

                {circles.length > 0 ? (
                  circles.map((circle) => (
                    <Link
                      key={circle.id}
                      href={`/circles/${circle.slug}`}
                      className="group grid gap-5 rounded-[28px] border border-white/8 bg-[linear-gradient(135deg,rgba(255,255,255,0.05),rgba(15,23,42,0.82))] p-6 transition-all hover:border-teal/25 lg:grid-cols-[minmax(0,1fr)_auto]"
                    >
                      <div>
                        <div className="mb-4 flex flex-wrap items-center gap-3">
                          <div className="rounded-full bg-teal/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-teal">
                            {formatCircleType(circle.type)}
                          </div>
                          <div className="rounded-full bg-white/5 px-3 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                            {circle.status}
                          </div>
                        </div>
                        <h3 className="text-2xl font-black text-white transition-colors group-hover:text-teal">
                          {circle.name}
                        </h3>
                        <p className="mt-3 text-sm leading-6 text-slate-300">
                          {circle.description || "A focused creator-led space for exclusive drops, room sessions, and stronger audience belonging."}
                        </p>
                        <div className="mt-5 flex flex-wrap items-center gap-5 text-[11px] font-black uppercase tracking-[0.16em] text-slate-500">
                          <span>{formatCompact(circle.members_count)} members</span>
                          <span>Hosted by {circle.owner.name}</span>
                          <span>{circle.is_member ? "You are in" : "Available to join"}</span>
                        </div>
                      </div>

                      <div className="flex items-center gap-4 lg:flex-col lg:items-end lg:justify-between">
                        <Avatar src={circle.owner.avatar_url} name={circle.owner.name} size="md" role={circle.owner.role} />
                        <div className="rounded-full bg-white px-5 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-slate-950 transition-transform group-hover:scale-[1.02]">
                          Open circle
                        </div>
                      </div>
                    </Link>
                  ))
                ) : (
                  <div className="rounded-[28px] border border-dashed border-white/10 px-6 py-16 text-center">
                    <div className="text-sm font-black uppercase tracking-[0.18em] text-slate-500">
                      You haven&apos;t joined any circles yet
                    </div>
                    <Link
                      href="/discover"
                      className="mt-5 inline-flex rounded-full bg-teal px-5 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-slate-950"
                    >
                      Discover communities
                    </Link>
                  </div>
                )}
              </div>

              <aside className="space-y-5">
                <div className="glass rounded-[28px] p-5">
                  <div className="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">
                    Premium circles
                  </div>
                  <div className="mt-4 space-y-3">
                    {premiumCircles.slice(0, 4).map((circle) => (
                      <Link
                        key={circle.id}
                        href={`/circles/${circle.slug}`}
                        className="block rounded-[18px] border border-white/8 bg-slate-950/60 p-4 transition-all hover:border-violet/25"
                      >
                        <div className="truncate text-sm font-black text-white">{circle.name}</div>
                        <div className="mt-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                          {formatCompact(circle.members_count)} members
                        </div>
                      </Link>
                    ))}
                    {premiumCircles.length === 0 && (
                      <div className="rounded-[18px] border border-dashed border-white/10 px-4 py-6 text-sm text-slate-500">
                        No premium circles joined yet.
                      </div>
                    )}
                  </div>
                </div>

                <div className="glass rounded-[28px] p-5">
                  <div className="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">
                    Public momentum
                  </div>
                  <div className="mt-4 space-y-3">
                    {publicCircles.slice(0, 4).map((circle) => (
                      <Link
                        key={circle.id}
                        href={`/circles/${circle.slug}`}
                        className="block rounded-[18px] border border-white/8 bg-slate-950/60 p-4 transition-all hover:border-teal/25"
                      >
                        <div className="truncate text-sm font-black text-white">{circle.name}</div>
                        <div className="mt-1 text-[11px] font-semibold uppercase tracking-[0.16em] text-slate-500">
                          {formatCircleType(circle.type)}
                        </div>
                      </Link>
                    ))}
                    {publicCircles.length === 0 && (
                      <div className="rounded-[18px] border border-dashed border-white/10 px-4 py-6 text-sm text-slate-500">
                        Public circles will appear here once your memberships grow.
                      </div>
                    )}
                  </div>
                </div>
              </aside>
            </section>
          </div>
        )}
      </div>
    </ShellLayout>
  );
}
