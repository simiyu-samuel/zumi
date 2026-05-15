"use client";

import { FormEvent, useMemo, useState } from "react";
import Link from "next/link";
import { useParams, useRouter } from "next/navigation";
import { ShellLayout } from "@/components/layout/ShellLayout";
import { useAuth } from "@/context/AuthContext";
import {
  createChallenge,
  createCircle,
  createGatedRoom,
  createSkillDrop,
  createWave,
  initializeWaveUpload,
} from "@/lib/api";
import { canAccessRole, getCreateOption, getRoleLabel, type CreateType } from "@/lib/create";

type SubmitState = {
  status: "idle" | "submitting" | "success" | "error";
  message?: string;
};

function getErrorMessage(error: unknown, fallback: string) {
  if (error instanceof Error && error.message) {
    return error.message;
  }

  return fallback;
}

function SectionTitle({ eyebrow, title, copy }: { eyebrow: string; title: string; copy: string }) {
  return (
    <div>
      <div className="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">{eyebrow}</div>
      <h2 className="mt-1 text-2xl font-black text-white">{title}</h2>
      <p className="mt-2 max-w-2xl text-sm leading-6 text-slate-400">{copy}</p>
    </div>
  );
}

function Field({
  label,
  hint,
  children,
}: {
  label: string;
  hint?: string;
  children: React.ReactNode;
}) {
  return (
    <label className="block">
      <div className="mb-2 flex items-center justify-between gap-3">
        <span className="text-[11px] font-black uppercase tracking-[0.18em] text-slate-400">{label}</span>
        {hint && <span className="text-[11px] text-slate-600">{hint}</span>}
      </div>
      {children}
    </label>
  );
}

function inputClassName() {
  return "w-full rounded-[18px] border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-white outline-none transition-all placeholder:text-slate-600 focus:border-teal/50 focus:ring-2 focus:ring-teal/20";
}

function textareaClassName() {
  return "w-full rounded-[18px] border border-white/10 bg-slate-950/70 px-4 py-3 text-sm text-white outline-none transition-all placeholder:text-slate-600 focus:border-teal/50 focus:ring-2 focus:ring-teal/20 min-h-[140px] resize-y";
}

export default function CreateTypePage() {
  const params = useParams<{ type: string }>();
  const router = useRouter();
  const { user } = useAuth();
  const type = params.type as CreateType;
  const option = getCreateOption(type);
  const userRole = user?.role || "user";

  const [submitState, setSubmitState] = useState<SubmitState>({ status: "idle" });

  const [waveTitle, setWaveTitle] = useState("");
  const [waveDescription, setWaveDescription] = useState("");
  const [waveVisibility, setWaveVisibility] = useState("public");
  const [waveGatedDrops, setWaveGatedDrops] = useState("0");
  const [waveFile, setWaveFile] = useState<File | null>(null);
  const [uploadedStreamId, setUploadedStreamId] = useState("");
  const [uploadProgress, setUploadProgress] = useState<"idle" | "uploading" | "done">("idle");

  const [dropTitle, setDropTitle] = useState("");
  const [dropDescription, setDropDescription] = useState("");
  const [dropPrice, setDropPrice] = useState("250");
  const [dropPreviewUrl, setDropPreviewUrl] = useState("");
  const [dropContentUrl, setDropContentUrl] = useState("");

  const [challengeTitle, setChallengeTitle] = useState("");
  const [challengeDescription, setChallengeDescription] = useState("");
  const [challengeType, setChallengeType] = useState("open");
  const [challengePrizePool, setChallengePrizePool] = useState("1000");
  const [challengeEndsAt, setChallengeEndsAt] = useState("");

  const [circleName, setCircleName] = useState("");
  const [circleDescription, setCircleDescription] = useState("");
  const [circleType, setCircleType] = useState("public");
  const [circleMonthlyPrice, setCircleMonthlyPrice] = useState("500");

  const [roomTitle, setRoomTitle] = useState("");
  const [roomDescription, setRoomDescription] = useState("");
  const [roomFee, setRoomFee] = useState("250");
  const [roomScheduledAt, setRoomScheduledAt] = useState("");

  const isAllowed = option ? canAccessRole(userRole, option.minRole) : false;

  const sourceDetails = useMemo(() => {
    switch (type) {
      case "wave":
        return {
          title: "Wave publishing",
          copy: "This flow uploads your video, receives a stream id, then creates the wave record in the backend.",
          source: "Uses `POST /waves/initialize-upload` then `POST /waves`.",
        };
      case "skill-drop":
        return {
          title: "Skill Drop publishing",
          copy: "This flow creates a monetized learning asset with a preview URL and content delivery URL.",
          source: "Uses `POST /skill-drops`.",
        };
      case "challenge":
        return {
          title: "Challenge publishing",
          copy: "This flow opens a new wave challenge with a prize pool and deadline.",
          source: "Uses `POST /challenges`.",
        };
      case "circle":
        return {
          title: "Circle setup",
          copy: "This flow provisions a public or gated community space tied to your account.",
          source: "Uses `POST /circles`.",
        };
      case "room":
        return {
          title: "Room scheduling",
          copy: "This flow creates a gated room you can start later or let people join when scheduled.",
          source: "Uses `POST /rooms`.",
        };
      default:
        return null;
    }
  }, [type]);

  if (!option) {
    return (
      <ShellLayout>
        <div className="px-8 py-14">
          <div className="rounded-[28px] border border-dashed border-white/10 p-10 text-center">
            <h1 className="text-2xl font-black text-white">Unknown creation flow</h1>
            <Link href="/create" className="mt-4 inline-flex text-teal">
              Back to create hub
            </Link>
          </div>
        </div>
      </ShellLayout>
    );
  }

  async function handleWaveSubmit(event: FormEvent) {
    event.preventDefault();
    if (!waveFile) {
      setSubmitState({ status: "error", message: "Choose a video file first." });
      return;
    }

    setSubmitState({ status: "submitting", message: "Uploading your wave..." });

    try {
      setUploadProgress("uploading");
      const uploadData = await initializeWaveUpload(waveTitle, waveFile.size);
      await fetch(uploadData.upload_url, {
        method: "PUT",
        body: waveFile,
      });

      setUploadedStreamId(uploadData.stream_id);

      await createWave({
        title: waveTitle,
        description: waveDescription || undefined,
        stream_id: uploadData.stream_id,
        visibility: waveVisibility,
        gated_drops: Number(waveGatedDrops || 0),
      });

      setUploadProgress("done");
      setSubmitState({ status: "success", message: "Wave published successfully." });
      router.push("/waves");
    } catch (error: unknown) {
      console.error(error);
      setUploadProgress("idle");
      setSubmitState({ status: "error", message: getErrorMessage(error, "Wave creation failed.") });
    }
  }

  async function handleSkillDropSubmit(event: FormEvent) {
    event.preventDefault();
    setSubmitState({ status: "submitting", message: "Publishing your skill drop..." });

    try {
      await createSkillDrop({
        title: dropTitle,
        description: dropDescription,
        price_drops: Number(dropPrice),
        preview_url: dropPreviewUrl || undefined,
        content_url: dropContentUrl || undefined,
      });
      setSubmitState({ status: "success", message: "Skill Drop created successfully." });
      router.push("/studio");
    } catch (error: unknown) {
      console.error(error);
      setSubmitState({ status: "error", message: getErrorMessage(error, "Skill Drop creation failed.") });
    }
  }

  async function handleChallengeSubmit(event: FormEvent) {
    event.preventDefault();
    setSubmitState({ status: "submitting", message: "Launching your challenge..." });

    try {
      await createChallenge({
        title: challengeTitle,
        description: challengeDescription,
        type: challengeType,
        prize_pool: Number(challengePrizePool),
        ends_at: challengeEndsAt,
      });
      setSubmitState({ status: "success", message: "Challenge created successfully." });
      router.push("/feed");
    } catch (error: unknown) {
      console.error(error);
      setSubmitState({ status: "error", message: getErrorMessage(error, "Challenge creation failed.") });
    }
  }

  async function handleCircleSubmit(event: FormEvent) {
    event.preventDefault();
    setSubmitState({ status: "submitting", message: "Creating your circle..." });

    try {
      await createCircle({
        name: circleName,
        description: circleDescription,
        type: circleType,
        monthly_drops_price: circleType === "premium" ? Number(circleMonthlyPrice) : undefined,
      });
      setSubmitState({ status: "success", message: "Circle created successfully." });
      router.push("/circles");
    } catch (error: unknown) {
      console.error(error);
      setSubmitState({ status: "error", message: getErrorMessage(error, "Circle creation failed.") });
    }
  }

  async function handleRoomSubmit(event: FormEvent) {
    event.preventDefault();
    setSubmitState({ status: "submitting", message: "Scheduling your room..." });

    try {
      await createGatedRoom({
        title: roomTitle,
        description: roomDescription,
        entry_fee_drops: Number(roomFee),
        scheduled_at: roomScheduledAt || undefined,
      });
      setSubmitState({ status: "success", message: "Gated room created successfully." });
      router.push("/circles");
    } catch (error: unknown) {
      console.error(error);
      setSubmitState({ status: "error", message: getErrorMessage(error, "Room creation failed.") });
    }
  }

  return (
    <ShellLayout>
      <div className="flex-1 px-5 py-6 sm:px-8 sm:py-8">
        <section className="rounded-[30px] border border-white/8 bg-[radial-gradient(circle_at_top_left,_rgba(26,158,117,0.22),_transparent_35%),linear-gradient(135deg,rgba(12,17,24,0.98),rgba(15,23,42,0.92))] p-6 sm:p-7">
          <div className="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div className="max-w-2xl">
              <div className="mb-3 inline-flex items-center gap-2 rounded-full border border-teal/20 bg-teal/10 px-3 py-1 text-[10px] font-black uppercase tracking-[0.24em] text-teal">
                {option.eyebrow}
                <span className="h-1.5 w-1.5 rounded-full bg-emerald-300" />
                {option.shortTitle}
              </div>
              <h1 className="font-head text-[2.4rem] font-black leading-[0.95] tracking-tight text-white sm:text-[3rem]">
                {option.title}
              </h1>
              <p className="mt-3 max-w-xl text-sm leading-6 text-slate-300">{option.description}</p>
            </div>

            <div className="rounded-[24px] border border-white/8 bg-white/[0.04] p-5 lg:w-[280px]">
              <div className="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">
                Access check
              </div>
              <div className="mt-2 text-2xl font-black text-white">{getRoleLabel(userRole)}</div>
              <div className="mt-2 text-sm text-slate-400">
                Requires {option.minRole} or higher.
              </div>
            </div>
          </div>
        </section>

        {!isAllowed ? (
          <section className="mt-6 rounded-[28px] border border-dashed border-white/10 bg-white/[0.03] p-8">
            <h2 className="text-2xl font-black text-white">This creation lane is locked</h2>
            <p className="mt-3 max-w-2xl text-sm leading-6 text-slate-400">
              Your current plan is <span className="text-white">{getRoleLabel(userRole)}</span>. This workflow requires
              <span className="text-white"> {option.minRole}</span> or higher because it uses premium creator capabilities.
            </p>
            <Link
              href="/create"
              className="mt-5 inline-flex rounded-full bg-teal px-5 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-slate-950"
            >
              Back to create hub
            </Link>
          </section>
        ) : (
          <div className="mt-6 grid gap-6 xl:grid-cols-[minmax(0,1fr)_290px]">
            <section className="rounded-[28px] border border-white/8 bg-white/[0.03] p-6">
              {type === "wave" && (
                <form className="space-y-5" onSubmit={handleWaveSubmit}>
                  <SectionTitle
                    eyebrow="Step-by-step"
                    title="Upload and publish a wave"
                    copy="Pick the video, add the feed metadata, then publish. This flow uploads first, then creates the wave record."
                  />
                  <div className="grid gap-5 md:grid-cols-2">
                    <Field label="Wave title">
                      <input className={inputClassName()} value={waveTitle} onChange={(e) => setWaveTitle(e.target.value)} required />
                    </Field>
                    <Field label="Visibility">
                      <select className={inputClassName()} value={waveVisibility} onChange={(e) => setWaveVisibility(e.target.value)}>
                        <option value="public">Public</option>
                        <option value="followers">Followers</option>
                        <option value="circle">Circle</option>
                        <option value="gated">Gated</option>
                      </select>
                    </Field>
                  </div>
                  <Field label="Description" hint="Optional">
                    <textarea className={textareaClassName()} value={waveDescription} onChange={(e) => setWaveDescription(e.target.value)} />
                  </Field>
                  <div className="grid gap-5 md:grid-cols-2">
                    <Field label="Video file">
                      <input
                        type="file"
                        accept="video/*"
                        className={inputClassName()}
                        onChange={(e) => setWaveFile(e.target.files?.[0] || null)}
                        required
                      />
                    </Field>
                    <Field label="Gated drops" hint="0 for free">
                      <input
                        type="number"
                        min="0"
                        className={inputClassName()}
                        value={waveGatedDrops}
                        onChange={(e) => setWaveGatedDrops(e.target.value)}
                      />
                    </Field>
                  </div>
                  {uploadedStreamId && (
                    <div className="rounded-[18px] border border-teal/20 bg-teal/5 px-4 py-3 text-sm text-teal-light">
                      Uploaded stream id: {uploadedStreamId}
                    </div>
                  )}
                  {uploadProgress === "uploading" && (
                    <div className="rounded-[18px] border border-white/10 bg-white/[0.03] px-4 py-3 text-sm text-slate-300">
                      Uploading your file to the video pipeline...
                    </div>
                  )}
                  <button
                    type="submit"
                    disabled={submitState.status === "submitting"}
                    className="rounded-full bg-teal px-6 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-slate-950 disabled:opacity-60"
                  >
                    {submitState.status === "submitting" ? "Publishing..." : "Publish wave"}
                  </button>
                </form>
              )}

              {type === "skill-drop" && (
                <form className="space-y-5" onSubmit={handleSkillDropSubmit}>
                  <SectionTitle
                    eyebrow="Monetized learning"
                    title="Package your knowledge"
                    copy="Give the drop a clear promise, set the price in Drops, and attach preview/content links."
                  />
                  <Field label="Title">
                    <input className={inputClassName()} value={dropTitle} onChange={(e) => setDropTitle(e.target.value)} required />
                  </Field>
                  <Field label="Description">
                    <textarea className={textareaClassName()} value={dropDescription} onChange={(e) => setDropDescription(e.target.value)} />
                  </Field>
                  <div className="grid gap-5 md:grid-cols-2">
                    <Field label="Price in drops">
                      <input type="number" min="0" className={inputClassName()} value={dropPrice} onChange={(e) => setDropPrice(e.target.value)} required />
                    </Field>
                    <Field label="Preview URL" hint="Optional">
                      <input type="url" className={inputClassName()} value={dropPreviewUrl} onChange={(e) => setDropPreviewUrl(e.target.value)} />
                    </Field>
                  </div>
                  <Field label="Content URL" hint="Optional but recommended">
                    <input type="url" className={inputClassName()} value={dropContentUrl} onChange={(e) => setDropContentUrl(e.target.value)} />
                  </Field>
                  <button type="submit" disabled={submitState.status === "submitting"} className="rounded-full bg-teal px-6 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-slate-950 disabled:opacity-60">
                    {submitState.status === "submitting" ? "Publishing..." : "Publish skill drop"}
                  </button>
                </form>
              )}

              {type === "challenge" && (
                <form className="space-y-5" onSubmit={handleChallengeSubmit}>
                  <SectionTitle
                    eyebrow="Community activation"
                    title="Launch a challenge"
                    copy="Set the creative brief, choose the participation type, and define the prize pool and close date."
                  />
                  <Field label="Challenge title">
                    <input className={inputClassName()} value={challengeTitle} onChange={(e) => setChallengeTitle(e.target.value)} required />
                  </Field>
                  <Field label="Description">
                    <textarea className={textareaClassName()} value={challengeDescription} onChange={(e) => setChallengeDescription(e.target.value)} />
                  </Field>
                  <div className="grid gap-5 md:grid-cols-3">
                    <Field label="Type">
                      <select className={inputClassName()} value={challengeType} onChange={(e) => setChallengeType(e.target.value)}>
                        <option value="open">Open</option>
                        <option value="direct">Direct</option>
                      </select>
                    </Field>
                    <Field label="Prize pool">
                      <input type="number" min="0" className={inputClassName()} value={challengePrizePool} onChange={(e) => setChallengePrizePool(e.target.value)} required />
                    </Field>
                    <Field label="Ends at">
                      <input type="datetime-local" className={inputClassName()} value={challengeEndsAt} onChange={(e) => setChallengeEndsAt(e.target.value)} required />
                    </Field>
                  </div>
                  <button type="submit" disabled={submitState.status === "submitting"} className="rounded-full bg-teal px-6 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-slate-950 disabled:opacity-60">
                    {submitState.status === "submitting" ? "Launching..." : "Launch challenge"}
                  </button>
                </form>
              )}

              {type === "circle" && (
                <form className="space-y-5" onSubmit={handleCircleSubmit}>
                  <SectionTitle
                    eyebrow="Community setup"
                    title="Open your circle"
                    copy="Define the name, purpose, and access level. Premium-style communities can also have a monthly drops price."
                  />
                  <Field label="Circle name">
                    <input className={inputClassName()} value={circleName} onChange={(e) => setCircleName(e.target.value)} required />
                  </Field>
                  <Field label="Description">
                    <textarea className={textareaClassName()} value={circleDescription} onChange={(e) => setCircleDescription(e.target.value)} />
                  </Field>
                  <div className="grid gap-5 md:grid-cols-2">
                    <Field label="Circle type">
                      <select className={inputClassName()} value={circleType} onChange={(e) => setCircleType(e.target.value)}>
                        <option value="public">Public</option>
                        <option value="private">Private</option>
                        <option value="premium">Premium</option>
                      </select>
                    </Field>
                    <Field label="Monthly drops price" hint="For paid circles">
                      <input type="number" min="1" className={inputClassName()} value={circleMonthlyPrice} onChange={(e) => setCircleMonthlyPrice(e.target.value)} />
                    </Field>
                  </div>
                  <button type="submit" disabled={submitState.status === "submitting"} className="rounded-full bg-teal px-6 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-slate-950 disabled:opacity-60">
                    {submitState.status === "submitting" ? "Creating..." : "Create circle"}
                  </button>
                </form>
              )}

              {type === "room" && (
                <form className="space-y-5" onSubmit={handleRoomSubmit}>
                  <SectionTitle
                    eyebrow="Live room"
                    title="Schedule or open a room"
                    copy="Set the room title, the entry fee in Drops, and optionally a scheduled start time."
                  />
                  <Field label="Room title">
                    <input className={inputClassName()} value={roomTitle} onChange={(e) => setRoomTitle(e.target.value)} required />
                  </Field>
                  <Field label="Description">
                    <textarea className={textareaClassName()} value={roomDescription} onChange={(e) => setRoomDescription(e.target.value)} />
                  </Field>
                  <div className="grid gap-5 md:grid-cols-2">
                    <Field label="Entry fee in drops">
                      <input type="number" min="0" className={inputClassName()} value={roomFee} onChange={(e) => setRoomFee(e.target.value)} required />
                    </Field>
                    <Field label="Scheduled time" hint="Optional">
                      <input type="datetime-local" className={inputClassName()} value={roomScheduledAt} onChange={(e) => setRoomScheduledAt(e.target.value)} />
                    </Field>
                  </div>
                  <button type="submit" disabled={submitState.status === "submitting"} className="rounded-full bg-teal px-6 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-slate-950 disabled:opacity-60">
                    {submitState.status === "submitting" ? "Creating..." : "Create gated room"}
                  </button>
                </form>
              )}

              {submitState.message && (
                <div
                  className={`mt-5 rounded-[18px] px-4 py-3 text-sm ${
                    submitState.status === "error"
                      ? "border border-red-500/20 bg-red-500/10 text-red-200"
                      : "border border-teal/20 bg-teal/10 text-teal-light"
                  }`}
                >
                  {submitState.message}
                </div>
              )}
            </section>

            <aside className="space-y-5">
              <div className="rounded-[24px] border border-white/8 bg-white/[0.03] p-5">
                <SectionTitle
                  eyebrow="Data source"
                  title={sourceDetails?.title || "This flow"}
                  copy={sourceDetails?.copy || "This creation flow is powered by the backend API."}
                />
                <div className="mt-4 rounded-[18px] border border-white/8 bg-slate-950/60 px-4 py-3 text-sm text-slate-300">
                  {sourceDetails?.source}
                </div>
              </div>

              <div className="rounded-[24px] border border-white/8 bg-white/[0.03] p-5">
                <div className="text-[11px] font-black uppercase tracking-[0.18em] text-slate-500">Publishing notes</div>
                <div className="mt-3 space-y-3 text-sm leading-6 text-slate-400">
                  <p>Only the flows your current role can access are enabled in the hub and navigation.</p>
                  <p>Waves require a successful upload first because the backend expects a `stream_id`.</p>
                  <p>Skill Drops, Rooms, and Challenges rely on premium-capable creator permissions in the backend policies.</p>
                </div>
              </div>

              <Link
                href="/create"
                className="inline-flex w-full items-center justify-center rounded-full border border-white/10 bg-white/5 px-5 py-3 text-[11px] font-black uppercase tracking-[0.18em] text-white transition-colors hover:bg-white/10"
              >
                Back to create hub
              </Link>
            </aside>
          </div>
        )}
      </div>
    </ShellLayout>
  );
}
