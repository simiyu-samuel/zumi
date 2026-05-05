"use client";

import React, { useState } from "react";
import { giftWave } from "@/lib/api";
import { useAuth } from "@/context/AuthContext";

interface GiftSheetProps {
  waveId: string;
  onClose: () => void;
  onSuccess?: (amount: number) => void;
}

export function GiftSheet({ waveId, onClose, onSuccess }: GiftSheetProps) {
  const { user } = useAuth();
  const [amount, setAmount] = useState<number | "">("");
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [success, setSuccess] = useState(false);

  const presets = [50, 100, 500, 1000, 5000, 10000];

  const handleGift = async () => {
    if (!amount || amount <= 0) return;
    
    setLoading(true);
    setError(null);
    try {
      await giftWave(waveId, Number(amount));
      setSuccess(true);
      if (onSuccess) onSuccess(Number(amount));
      setTimeout(onClose, 2000);
    } catch (err: any) {
      setError(err.message || "Failed to send gift");
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 z-[100] flex items-end justify-center animate-fade-in bg-black/60 backdrop-blur-sm">
      <div className="absolute inset-0" onClick={onClose} />
      
      <div className="w-full max-w-[500px] glass-dark rounded-t-[32px] border-t border-white/10 p-8 relative z-10 animate-slide-up shadow-[0_-20px_60px_rgba(26,158,117,0.2)]">
        {/* Drag Handle */}
        <div className="w-12 h-1.5 bg-white/20 rounded-full mx-auto mb-8" />

        {success ? (
          <div className="py-10 text-center animate-in zoom-in-95 duration-500">
            <div className="w-24 h-24 bg-teal rounded-full flex items-center justify-center mx-auto mb-6 shadow-[0_0_40px_rgba(26,158,117,0.4)]">
              <svg className="w-12 h-12 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M5 13l4 4L19 7" />
              </svg>
            </div>
            <h3 className="text-2xl font-black text-white mb-2">Gift Sent!</h3>
            <p className="text-slate-400 font-bold uppercase tracking-widest text-xs">
              You've gifted {amount} Drops
            </p>
          </div>
        ) : (
          <>
            <div className="flex justify-between items-center mb-6">
              <h3 className="text-xl font-black text-white tracking-tight">Gift Drops</h3>
              <div className="flex items-center gap-2 bg-teal/10 border border-teal/20 px-3 py-1.5 rounded-full">
                <span className="text-[10px] font-black text-teal uppercase tracking-widest">Balance</span>
                <span className="text-sm font-black text-white">{user?.drops_balance || 0}</span>
              </div>
            </div>

            {/* Presets */}
            <div className="grid grid-cols-3 gap-3 mb-8">
              {presets.map((p) => (
                <button 
                  key={p}
                  onClick={() => setAmount(p)}
                  className={`py-3 rounded-2xl border font-black transition-all active:scale-95 ${amount === p ? 'bg-teal border-teal text-white shadow-lg shadow-teal/20' : 'bg-white/5 border-white/10 text-slate-400 hover:border-white/20 hover:text-white'}`}
                >
                  {p}
                </button>
              ))}
            </div>

            {/* Custom Input */}
            <div className="mb-8">
              <div className="relative group">
                <input 
                  type="number"
                  value={amount}
                  onChange={(e) => setAmount(e.target.value === "" ? "" : Number(e.target.value))}
                  placeholder="Custom amount"
                  className="w-full bg-white/5 border border-white/10 rounded-2xl py-4 px-6 text-lg font-black text-white placeholder:text-slate-600 focus:outline-none focus:border-teal/50 transition-all text-center"
                />
                <div className="absolute right-6 top-1/2 -translate-y-1/2 text-slate-600 font-black uppercase text-[10px] tracking-widest">Drops</div>
              </div>
              {error && <p className="mt-4 text-center text-red-400 text-xs font-bold uppercase tracking-widest">{error}</p>}
            </div>

            <button 
              onClick={handleGift}
              disabled={loading || !amount || amount <= 0}
              className="w-full bg-teal text-white rounded-2xl py-5 font-black text-sm uppercase tracking-[0.2em] hover:bg-teal-dark disabled:opacity-30 disabled:cursor-not-allowed transition-all shadow-xl shadow-teal/20 active:scale-[0.98]"
            >
              {loading ? (
                <div className="w-6 h-6 border-2 border-white/30 border-t-white rounded-full animate-spin mx-auto" />
              ) : (
                'Confirm Gift'
              )}
            </button>

            <button 
              onClick={onClose}
              className="w-full mt-4 text-slate-600 font-bold uppercase text-[10px] tracking-[0.3em] hover:text-slate-400 transition-colors"
            >
              Cancel
            </button>
          </>
        )}
      </div>
    </div>
  );
}
