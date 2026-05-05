"use client";

import React, { useState, useEffect } from "react";
import { useRouter } from "next/navigation";
import { DeviceFrame, StatusBar } from "@/components/DeviceFrame";
import { apiFetch } from "@/lib/api";
import { useAuth } from "@/context/AuthContext";

const INTERESTS = [
  { id: "tech", label: "Tech", icon: "💻" },
  { id: "music", label: "Music", icon: "🎵" },
  { id: "dance", label: "Dance", icon: "💃" },
  { id: "art", label: "Art", icon: "🎨" },
  { id: "gaming", label: "Gaming", icon: "🎮" },
  { id: "sports", label: "Sports", icon: "⚽" },
  { id: "crypto", label: "Crypto", icon: "₿" },
  { id: "fashion", label: "Fashion", icon: "✨" },
  { id: "food", label: "Food", icon: "🍔" },
];

const PILLS = ["AI", "Web3", "Startups", "Vlog", "ASMR", "Fitness", "Travel", "Comedy", "Education", "News", "Business", "Movies", "Anime", "Photography"];

export default function OnboardingPage() {
  const [step, setStep] = useState(1);
  const [selectedInterests, setSelectedInterests] = useState<string[]>([]);
  const [selectedFollows, setSelectedFollows] = useState<string[]>([]);
  const [suggestedUsers, setSuggestedUsers] = useState<any[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [completedSteps, setCompletedSteps] = useState<number[]>([]);

  const router = useRouter();
  const { user, token, updateUser, isLoading: authLoading } = useAuth();

  useEffect(() => {
    if (!authLoading && !token) {
      router.push("/auth/login");
    }
  }, [token, authLoading, router]);

  useEffect(() => {
    // Fetch suggested users when moving to step 2
    if (step === 2) {
      fetchSuggestedUsers();
    }
  }, [step]);

  const fetchSuggestedUsers = async () => {
    try {
      const data = await apiFetch("/search/users?limit=8");
      setSuggestedUsers(data.data || []);
    } catch (err) {
      console.error("Failed to fetch suggested users", err);
    }
  };

  const toggleInterest = (id: string) => {
    if (selectedInterests.includes(id)) {
      setSelectedInterests(selectedInterests.filter((i) => i !== id));
    } else {
      setSelectedInterests([...selectedInterests, id]);
    }
  };

  const toggleFollow = (id: string) => {
    if (selectedFollows.includes(id)) {
      setSelectedFollows(selectedFollows.filter((i) => i !== id));
    } else {
      setSelectedFollows([...selectedFollows, id]);
    }
  };

  const handleComplete = async () => {
    setStep(3);
    setIsLoading(true);

    // Simulate progress reveal as per design
    setTimeout(async () => {
      setCompletedSteps([1]);
      setTimeout(() => {
        setCompletedSteps([1, 2]);
        setTimeout(async () => {
          try {
            await apiFetch("/user/onboarding", {
              method: "POST",
              body: JSON.stringify({
                interests: selectedInterests,
                follows: selectedFollows,
              }),
            });
            setCompletedSteps([1, 2, 3]);
            updateUser({ onboarding_completed: true });
            setTimeout(() => {
              router.push("/feed");
            }, 1000);
          } catch (err) {
            console.error("Onboarding failed", err);
            setStep(2);
            setIsLoading(false);
          }
        }, 1200);
      }, 1000);
    }, 800);
  };

  return (
    <DeviceFrame>
      <div className="screen bg-white h-full flex flex-col overflow-hidden">
        {/* Step 1: Interests */}
        {step === 1 && (
          <div className="flex flex-col h-full animate-in fade-in slide-in-from-right duration-500">
            <div className="px-6 pt-6 pb-4">
              <div className="ob-progress flex gap-1.5">
                <div className="ob-step h-[3px] flex-1 rounded-[2px] bg-teal"></div>
                <div className="ob-step h-[3px] flex-1 rounded-[2px] bg-slate-100"></div>
                <div className="ob-step h-[3px] flex-1 rounded-[2px] bg-slate-100"></div>
              </div>
            </div>

            <div className="ob-header px-6 pb-5">
              <div className="ob-step-label text-[11px] font-bold text-teal tracking-[0.08em] uppercase mb-1">Step 1 of 3 — Discover</div>
              <h2 className="ob-title font-head text-[24px] font-extrabold text-dark tracking-tight leading-[1.15] mb-1.5">What&apos;s your world<br/>made of?</h2>
              <p className="ob-sub text-sm text-slate-600 leading-relaxed">Pick your interests so we can build a feed worth opening. Select at least 3.</p>
            </div>

            <div className="flex-1 overflow-y-auto">
              <div className="interests-grid grid grid-cols-3 gap-2.5 px-6">
                {INTERESTS.map((item) => (
                  <div 
                    key={item.id}
                    onClick={() => toggleInterest(item.id)}
                    className={`interest-chip relative flex flex-col items-center justify-center gap-1.5 p-[14px_8px] border-2 rounded-r16 cursor-pointer transition-all ${selectedInterests.includes(item.id) ? 'border-teal bg-teal-light' : 'border-slate-100 bg-white hover:border-teal/30 hover:bg-teal-light'}`}
                  >
                    <div className={`chip-icon-wrap w-10 h-10 rounded-r12 flex items-center justify-center transition-colors text-xl ${selectedInterests.includes(item.id) ? 'bg-teal' : 'bg-slate-100'}`}>
                      {item.icon}
                    </div>
                    <span className="chip-label text-[12px] font-semibold text-dark">{item.label}</span>
                    {selectedInterests.includes(item.id) && (
                      <div className="chip-check absolute top-1.5 right-1.5 w-4 h-4 rounded-full bg-teal flex items-center justify-center">
                        <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="4" strokeLinecap="round" strokeLinejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                      </div>
                    )}
                  </div>
                ))}
              </div>

              <div className="px-6 pt-4 pb-2 text-[11px] font-bold text-slate-400 tracking-[0.05em] uppercase">More</div>
              <div className="more-interests flex flex-wrap gap-2 px-6 pb-8">
                {PILLS.map((pill) => (
                  <button 
                    key={pill}
                    onClick={() => toggleInterest(pill.toLowerCase())}
                    className={`more-chip p-[8px_14px] rounded-rpill border-[1.5px] text-[13px] font-medium transition-all ${selectedInterests.includes(pill.toLowerCase()) ? 'border-teal bg-teal-light text-teal-dark font-bold' : 'border-slate-100 bg-white text-slate-600 hover:border-teal/40 hover:text-teal'}`}
                  >
                    {pill}
                  </button>
                ))}
              </div>
            </div>

            <div className="ob-bottom p-[16px_24px_28px] mt-auto border-t border-slate-50">
              <div className="ob-selected-count text-[13px] text-slate-400 text-center mb-3.5">
                {selectedInterests.length < 3 ? (
                  <>Select at least <span className="text-teal font-semibold">{3 - selectedInterests.length}</span> more</>
                ) : (
                  <><span className="text-teal font-semibold">{selectedInterests.length}</span> selected</>
                )}
              </div>
              <button 
                onClick={() => setStep(2)}
                disabled={selectedInterests.length < 3}
                className={`btn btn-primary w-full p-[15px] rounded-r12 bg-teal text-white font-semibold text-[15px] transition-all flex items-center justify-center gap-2 ${selectedInterests.length < 3 ? 'opacity-40 pointer-events-none' : ''}`}
              >
                Continue
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
              </button>
            </div>
          </div>
        )}

        {/* Step 2: Follows */}
        {step === 2 && (
          <div className="flex flex-col h-full animate-in fade-in slide-in-from-right duration-500">
            <div className="px-6 pt-6 pb-4">
              <div className="ob-progress flex gap-1.5">
                <div className="ob-step h-[3px] flex-1 rounded-[2px] bg-teal/40"></div>
                <div className="ob-step h-[3px] flex-1 rounded-[2px] bg-teal"></div>
                <div className="ob-step h-[3px] flex-1 rounded-[2px] bg-slate-100"></div>
              </div>
            </div>

            <div className="ob-header px-6 pb-5">
              <div className="ob-step-label text-[11px] font-bold text-teal tracking-[0.08em] uppercase mb-1">Step 2 of 3 — Community</div>
              <h2 className="ob-title font-head text-[24px] font-extrabold text-dark tracking-tight leading-[1.15] mb-1.5">Find your people</h2>
              <p className="ob-sub text-sm text-slate-600 leading-relaxed">Follow at least 3 creators to seed your Zumi feed with content you&apos;ll love.</p>
            </div>

            <div className="follows-list flex-1 overflow-y-auto px-6">
              {suggestedUsers.map((creator) => (
                <div key={creator.id} className="follow-card flex items-center gap-3 py-3 border-b border-slate-100 last:border-0">
                  <div className="follow-avatar w-12 h-12 rounded-full flex-shrink-0 bg-teal relative overflow-hidden">
                    {creator.avatar_url ? (
                      <img src={creator.avatar_url} alt={creator.name} className="w-full h-full object-cover" />
                    ) : (
                      <div className="w-full h-full flex items-center justify-center text-white font-head font-extrabold text-lg uppercase">
                        {creator.name.charAt(0)}
                      </div>
                    )}
                    <div className="avatar-verified absolute bottom-[-1px] right-[-1px] w-4 h-4 rounded-full bg-teal border-2 border-white flex items-center justify-center">
                      <svg width="8" height="8" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="4" strokeLinecap="round" strokeLinejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    </div>
                  </div>
                  <div className="follow-info flex-1 min-w-0">
                    <div className="follow-name text-sm font-semibold text-dark truncate">{creator.name}</div>
                    <div className="follow-handle text-xs text-slate-400 mt-0.5">@{creator.username}</div>
                    <div className="follow-meta flex items-center gap-1.5 mt-1">
                      <span className="follow-tag text-[10px] font-bold px-1.5 py-0.5 rounded-rpill bg-teal-light text-teal-dark">CREATOR</span>
                      <span className="follow-followers text-[11px] text-slate-400">
                        {creator.followers_count >= 1000 
                          ? `${(creator.followers_count / 1000).toFixed(1)}k` 
                          : creator.followers_count} followers
                      </span>
                    </div>
                  </div>
                  <button 
                    onClick={() => toggleFollow(creator.id)}
                    className={`follow-btn p-[8px_16px] rounded-rpill border-[1.5px] font-bold text-[13px] transition-all whitespace-nowrap ${selectedFollows.includes(creator.id) ? 'bg-teal border-teal text-white' : 'border-teal bg-transparent text-teal hover:bg-teal hover:text-white'}`}
                  >
                    {selectedFollows.includes(creator.id) ? "Following" : "Follow"}
                  </button>
                </div>
              ))}
            </div>

            <div className="ob-bottom p-[16px_24px_28px] mt-auto border-t border-slate-50">
              <div className="ob-selected-count text-[13px] text-slate-400 text-center mb-3.5">
                {selectedFollows.length < 3 ? (
                  <>Follow at least <span className="text-teal font-semibold">{3 - selectedFollows.length}</span> more</>
                ) : (
                  <><span className="text-teal font-semibold">{selectedFollows.length}</span> followed</>
                )}
              </div>
              <button 
                onClick={handleComplete}
                disabled={selectedFollows.length < 3}
                className={`btn btn-primary w-full p-[15px] rounded-r12 bg-teal text-white font-semibold text-[15px] transition-all flex items-center justify-center gap-2 ${selectedFollows.length < 3 ? 'opacity-40 pointer-events-none' : ''}`}
              >
                Complete Setup
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="M20 6 9 17 4 12"/></svg>
              </button>
            </div>
          </div>
        )}

        {/* Step 3: Success / Loading */}
        {step === 3 && (
          <div className="screen relative bg-dark flex flex-col items-center justify-center text-center animate-in fade-in duration-700">
            <div className="loading-bg absolute inset-0 z-0"></div>
            <div className="loading-content relative z-1 p-6">
              <div className="loading-logo font-head text-[42px] font-extrabold text-white tracking-[-1.5px] mb-10">
                zu<span className="text-teal">mi</span>
              </div>
              
              <div className="loading-ring w-20 h-20 mx-auto mb-8 relative">
                <svg viewBox="0 0 80 80" className="animate-spin duration-[1.4s]">
                  <circle className="fill-none stroke-white/10 stroke-[4px]" cx="40" cy="40" r="34"/>
                  <circle className="fill-none stroke-teal stroke-[4px] stroke-linecap-round animate-[ring-dash_1.4s_ease-in-out_infinite]" cx="40" cy="40" r="34" strokeDasharray="180" strokeDashoffset="60"/>
                </svg>
              </div>

              <div className="loading-steps flex flex-col gap-3.5 mb-10 text-left max-w-[240px] mx-auto">
                {[
                  { id: 1, label: "Saving your interests" },
                  { id: 2, label: "Following your creators" },
                  { id: 3, label: "Building your feed" },
                ].map((item) => (
                  <div key={item.id} className={`loading-step flex items-center gap-3 transition-all duration-400 ${completedSteps.includes(item.id) ? 'opacity-100 translate-x-0' : 'opacity-35 -translate-x-1.5'}`}>
                    <div className={`step-dot w-7 h-7 rounded-full border-2 flex items-center justify-center transition-all ${completedSteps.includes(item.id) ? 'bg-teal/20 border-teal' : 'border-white/15'}`}>
                      <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke={completedSteps.includes(item.id) ? "var(--color-teal)" : "rgba(255,255,255,0.4)"} strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                      </svg>
                    </div>
                    <span className={`step-label text-sm transition-colors ${completedSteps.includes(item.id) ? 'text-white font-medium' : 'text-white/70'}`}>
                      {item.label}
                    </span>
                  </div>
                ))}
              </div>

              <p className="text-[13px] text-white/35 leading-relaxed max-w-[260px] mx-auto">
                Completing setup & preparing your personal experience...
              </p>
            </div>
          </div>
        )}
      </div>
    </DeviceFrame>
  );
}
