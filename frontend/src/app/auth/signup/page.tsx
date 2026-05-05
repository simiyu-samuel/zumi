"use client";

import React, { useState, useEffect } from "react";
import Link from "next/link";
import { useRouter } from "next/navigation";
import { DeviceFrame, StatusBar } from "@/components/DeviceFrame";
import { apiFetch } from "@/lib/api";
import { useAuth } from "@/context/AuthContext";

export default function SignupPage() {
  const [formData, setFormData] = useState({
    name: "",
    username: "",
    email: "",
    password: "",
  });
  const [strength, setStrength] = useState(0);
  const [isAgreed, setIsAgreed] = useState(false);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [usernameStatus, setUsernameStatus] = useState<"idle" | "checking" | "available" | "taken">("idle");

  const router = useRouter();
  const { login } = useAuth();

  useEffect(() => {
    // Basic password strength logic
    let s = 0;
    if (formData.password.length > 5) s++;
    if (formData.password.length > 8) s++;
    if (/[A-Z]/.test(formData.password)) s++;
    if (/[0-9]/.test(formData.password)) s++;
    setStrength(s);
  }, [formData.password]);

  const handleSignup = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!isAgreed) return;

    setIsLoading(true);
    setError(null);

    try {
      const data = await apiFetch("/auth/register", {
        method: "POST",
        body: JSON.stringify({
          ...formData,
          password_confirmation: formData.password,
        }),
      });

      login(data.access_token, data.user);
      router.push("/onboarding");
    } catch (err: any) {
      setError(err.message || "Something went wrong during signup.");
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <DeviceFrame>
      <div className="screen bg-white h-full flex flex-col overflow-hidden">
        <div className="auth-header flex items-center gap-3 px-5 pt-6 shrink-0">
          <button onClick={() => router.back()} className="back-btn w-9 h-9 rounded-full bg-slate-100 flex items-center justify-center transition-colors hover:bg-slate-200">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2.5" strokeLinecap="round" strokeLinejoin="round"><path d="m15 18-6-6 6-6"/></svg>
          </button>
          <div className="auth-logo-sm font-head font-extrabold text-[18px] text-dark tracking-tight ml-auto">
            zu<span className="text-teal">mi</span>
          </div>
        </div>

        <div className="auth-body px-6 py-5 flex-1">
          <div className="auth-eyebrow text-[11px] font-bold text-teal tracking-[0.06em] uppercase mb-1.5">New account</div>
          <h1 className="auth-heading font-head text-[26px] font-extrabold text-dark tracking-tight leading-[1.15] mb-1">Join Zumi</h1>
          <p className="auth-sub text-sm text-slate-600 mb-6 leading-relaxed">Start building your community and earning from it.</p>

          <form onSubmit={handleSignup} className="space-y-3.5">
            <div className="form-group">
              <label className="form-label block text-[12px] font-semibold text-slate-600 tracking-[0.04em] uppercase mb-1.5">Full Name</label>
              <input 
                className="form-input w-full p-[13px_14px] border-[1.5px] border-slate-100 rounded-r12 bg-slate-50 text-[15px] outline-none transition-all focus:border-teal focus:bg-white focus:shadow-[0_0_0_3px_rgba(26,158,117,0.1)]" 
                type="text" 
                placeholder="Jane Doe"
                required
                value={formData.name}
                onChange={(e) => setFormData({ ...formData, name: e.target.value })}
              />
            </div>

            <div className="form-group">
              <label className="form-label block text-[12px] font-semibold text-slate-600 tracking-[0.04em] uppercase mb-1.5">Username</label>
              <div className="input-icon-wrap relative">
                <svg className="input-icon absolute left-[13px] top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                <input 
                  className="form-input w-full p-[13px_14px_13px_42px] border-[1.5px] border-slate-100 rounded-r12 bg-slate-50 text-[15px] outline-none transition-all focus:border-teal focus:bg-white focus:shadow-[0_0_0_3px_rgba(26,158,117,0.1)]" 
                  type="text" 
                  placeholder="janedoe"
                  required
                  value={formData.username}
                  onChange={(e) => setFormData({ ...formData, username: e.target.value.toLowerCase() })}
                />
              </div>
              <div className="form-hint text-[12px] text-slate-400 mt-1.5">Letters, numbers, underscores only</div>
            </div>

            <div className="form-group">
              <label className="form-label block text-[12px] font-semibold text-slate-600 tracking-[0.04em] uppercase mb-1.5">Email address</label>
              <div className="input-icon-wrap relative">
                <svg className="input-icon absolute left-[13px] top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                <input 
                  className="form-input w-full p-[13px_14px_13px_42px] border-[1.5px] border-slate-100 rounded-r12 bg-slate-50 text-[15px] outline-none transition-all focus:border-teal focus:bg-white focus:shadow-[0_0_0_3px_rgba(26,158,117,0.1)]" 
                  type="email" 
                  placeholder="jane@example.com"
                  required
                  value={formData.email}
                  onChange={(e) => setFormData({ ...formData, email: e.target.value })}
                />
              </div>
            </div>

            <div className="form-group">
              <label className="form-label block text-[12px] font-semibold text-slate-600 tracking-[0.04em] uppercase mb-1.5">Password</label>
              <div className="input-icon-wrap relative">
                <svg className="input-icon absolute left-[13px] top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                <input 
                  className="form-input w-full p-[13px_14px_13px_42px] border-[1.5px] border-slate-100 rounded-r12 bg-slate-50 text-[15px] outline-none transition-all focus:border-teal focus:bg-white focus:shadow-[0_0_0_3px_rgba(26,158,117,0.1)]" 
                  type="password" 
                  placeholder="Min. 8 characters"
                  required
                  value={formData.password}
                  onChange={(e) => setFormData({ ...formData, password: e.target.value })}
                />
              </div>
              <div className="password-strength flex gap-1 mt-1.5">
                {[1, 2, 3, 4].map((i) => (
                  <div 
                    key={i} 
                    className={`strength-bar h-[3px] flex-1 rounded-[2px] transition-colors ${i <= strength ? (strength <= 2 ? 'bg-danger' : strength === 3 ? 'bg-warning' : 'bg-teal') : 'bg-slate-100'}`}
                  ></div>
                ))}
              </div>
            </div>

            <div className="terms-row flex items-start gap-2.5 my-4">
              <div 
                onClick={() => setIsAgreed(!isAgreed)}
                className={`checkbox-custom w-[18px] h-[18px] rounded-[5px] border-2 shrink-0 cursor-pointer mt-0.5 flex items-center justify-center transition-all ${isAgreed ? 'bg-teal border-teal' : 'bg-slate-50 border-slate-100'}`}
              >
                {isAgreed && <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="white" strokeWidth="3" strokeLinecap="round" strokeLinejoin="round"><polyline points="20 6 9 17 4 12"/></svg>}
              </div>
              <div className="terms-text text-[13px] text-slate-600 leading-relaxed">
                I agree to the <a href="#" className="text-teal font-medium">Terms of Service</a> and <a href="#" className="text-teal font-medium">Privacy Policy</a>
              </div>
            </div>

            {error && <div className="text-danger text-xs font-medium text-center">{error}</div>}

            <button 
              type="submit"
              disabled={!isAgreed || isLoading}
              className={`btn btn-primary w-full p-[15px] rounded-r12 bg-teal text-white font-semibold text-[15px] transition-all flex items-center justify-center gap-2 ${(!isAgreed || isLoading) ? 'opacity-50 cursor-not-allowed' : 'hover:bg-teal-dark active:translate-y-0'}`}
            >
              {isLoading ? <div className="spinner w-4.5 h-4.5 border-[2.5px] border-white/30 border-t-white rounded-full animate-spin"></div> : "Create account"}
            </button>
          </form>

          <div className="divider-row flex items-center gap-3 my-4">
            <div className="flex-1 h-[1px] bg-slate-100"></div>
            <span className="text-[12px] text-slate-400 font-medium">or continue with</span>
            <div className="flex-1 h-[1px] bg-slate-100"></div>
          </div>

          <button className="social-btn w-full p-[13px] rounded-r12 border-[1.5px] border-slate-100 bg-white font-medium text-sm text-dark flex items-center justify-center gap-2.5 transition-all hover:bg-slate-50">
            <svg width="18" height="18" viewBox="0 0 24 24"><path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/><path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/><path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/><path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/></svg>
            Continue with Google
          </button>

          <div className="switch-row text-center text-sm text-slate-600 pt-3 pb-1">
            Already have an account? <Link href="/auth/login" className="text-teal font-bold hover:underline">Sign in</Link>
          </div>
        </div>
      </div>
    </DeviceFrame>
  );
}
