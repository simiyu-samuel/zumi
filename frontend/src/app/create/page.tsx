"use client";

import Link from "next/link";
import { ShellLayout } from "@/components/layout/ShellLayout";
import { useAuth } from "@/context/AuthContext";
import {
  CREATE_OPTIONS,
  getAccessibleCreateOptions,
  getLockedCreateOptions,
  getRoleLabel,
} from "@/lib/create";

function accentClasses(accent: string) {
  switch (accent) {
    case "violet":
      return "from-violet-500/20 to-violet-500/5 border-violet-400/20 text-violet-200";
    case "rose":
      return "from-rose-500/20 to-rose-500/5 border-rose-400/20 text-rose-200";
    case "amber":
      return "from-amber-500/20 to-amber-500/5 border-amber-400/20 text-amber-200";
    case "emerald":
      return "from-emerald-500/20 to-emerald-500/5 border-emerald-400/20 text-emerald-200";
    default:
      return "from-teal/20 to-teal/5 border-teal/20 text-teal-light";
  }
}

export default function CreateHubPage() {
  const { user } = useAuth();
  const available = getAccessibleCreateOptions(user?.role);
  const locked = getLockedCreateOptions(user?.role);

  return (
    <ShellLayout>
      <div className="flex-1 px-5 py-6 sm:px-8 sm:py-8">
        <section className="rounded-[30px] border border-white/8 bg-[radial-gradient(circle_at_top_left,_rgba(26,158,117,0.22),_transparent_35%),linear-gradient(135deg,rgba(12,17,24,0.98),rgba(15,23,42,0.92))] p-6 sm:p-7">
          <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div className="max-w-2xl">
              <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-teal/20 bg-teal/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.24em] text-teal">
                Create
                <span className="h-1.5 w-1.5 rounded-full bg-emerald-300" />
                Publish with intent
              </div>
              <h1 className="font-head text-[2.5rem] font-black leading-[0.95] tracking-tight text-white sm:text-[3rem]">
                Pick the format, then move straight into publishing.
              </h1>
              <p className="mt-3 max-w-xl text-sm leading-6 text-slate-300">
                No modal detours. Each flow has its own workspace, clearer inputs, and capability checks based on your current plan.
              </p>
            </div>

            <div className="rounded-[24px] border border-white/8 bg-white/[0.04] p-5 lg:w-[280px]">
              <div className="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">
                Creator access
              </div>
              <div className="mt-2 text-3xl font-black text-white">{getRoleLabel(user?.role)}</div>
              <div className="mt-3 text-sm text-slate-400">
                {available.length} of {CREATE_OPTIONS.length} creation lanes currently available to this account.
              </div>
            </div>
          </div>
        </section>

        <section className="mt-6">
          <div className="mb-4 flex items-center justify-between">
            <div>
              <div className="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">
                Available now
              </div>
              <h2 className="mt-1 text-2xl font-black text-white">Start creating</h2>
            </div>
          </div>

          <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            {available.map((option) => (
              <Link
                key={option.id}
                href={option.href}
                className={`group rounded-[28px] border bg-gradient-to-br p-5 transition-all hover:-translate-y-1 hover:border-white/18 ${accentClasses(option.accent)}`}
              >
                <div className="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">
                  {option.eyebrow}
                </div>
                <div className="mt-3 text-[1.6rem] font-black leading-tight text-white transition-colors group-hover:text-teal-light">
                  {option.title}
                </div>
                <p className="mt-3 min-h-[72px] text-sm leading-6 text-slate-300">
                  {option.description}
                </p>
                <div className="mt-6 inline-flex items-center gap-2 text-[11px] font-black uppercase tracking-[0.18em] text-white">
                  Open workspace
                  <span className="transition-transform group-hover:translate-x-1">→</span>
                </div>
              </Link>
            ))}
          </div>
        </section>

        {locked.length > 0 && (
          <section className="mt-8">
            <div className="mb-4">
              <div className="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">
                Plan-gated
              </div>
              <h2 className="mt-1 text-xl font-black text-white">Unlocked on higher plans</h2>
            </div>

            <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
              {locked.map((option) => (
                <div
                  key={option.id}
                  className="rounded-[24px] border border-white/8 bg-white/[0.03] p-5 opacity-70"
                >
                  <div className="flex items-center justify-between gap-3">
                    <div className="text-[10px] font-black uppercase tracking-[0.18em] text-slate-500">
                      {option.eyebrow}
                    </div>
                    <div className="rounded-full border border-white/10 bg-white/5 px-2.5 py-1 text-[10px] font-black uppercase tracking-[0.18em] text-slate-400">
                      {option.minRole}+ only
                    </div>
                  </div>
                  <div className="mt-3 text-[1.25rem] font-black text-white">{option.title}</div>
                  <p className="mt-2 text-sm leading-6 text-slate-400">{option.description}</p>
                </div>
              ))}
            </div>
          </section>
        )}
      </div>
    </ShellLayout>
  );
}
