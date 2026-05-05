"use client";

import React, { useState } from "react";

interface ShareSheetProps {
  waveId: string;
  onClose: () => void;
}

export function ShareSheet({ waveId, onClose }: ShareSheetProps) {
  const [copied, setCopied] = useState(false);
  const shareUrl = `${window.location.origin}/waves/${waveId}`;

  const handleCopy = async () => {
    try {
      await navigator.clipboard.writeText(shareUrl);
      setCopied(true);
      setTimeout(() => setCopied(false), 2000);
    } catch (err) {
      console.error("Failed to copy", err);
    }
  };

  const shareOptions = [
    { 
      name: "WhatsApp", 
      icon: "https://cdn-icons-png.flaticon.com/512/3670/3670051.png", 
      url: `https://wa.me/?text=${encodeURIComponent(shareUrl)}`,
      color: "bg-[#25D366]"
    },
    { 
      name: "X / Twitter", 
      icon: "https://abs.twimg.com/responsive-web/client-web-legacy/icon-ios.b1fd64a5.png", 
      url: `https://twitter.com/intent/tweet?url=${encodeURIComponent(shareUrl)}`,
      color: "bg-black"
    },
    { 
      name: "Facebook", 
      icon: "https://cdn-icons-png.flaticon.com/512/124/124010.png", 
      url: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(shareUrl)}`,
      color: "bg-[#1877F2]"
    },
    { 
      name: "Telegram", 
      icon: "https://cdn-icons-png.flaticon.com/512/2111/2111646.png", 
      url: `https://t.me/share/url?url=${encodeURIComponent(shareUrl)}`,
      color: "bg-[#0088cc]"
    }
  ];

  const handleNativeShare = async () => {
    if (navigator.share) {
      try {
        await navigator.share({
          title: 'Check out this Wave on Zumi!',
          url: shareUrl,
        });
      } catch (err) {
        console.error("Native share failed", err);
      }
    }
  };

  return (
    <div className="fixed inset-0 z-[100] flex items-end justify-center animate-fade-in bg-black/60 backdrop-blur-sm">
      <div className="absolute inset-0" onClick={onClose} />
      
      <div className="w-full max-w-[500px] glass-dark rounded-t-[32px] border-t border-white/10 p-8 relative z-10 animate-slide-up">
        {/* Drag Handle */}
        <div className="w-12 h-1.5 bg-white/20 rounded-full mx-auto mb-8" />

        <h3 className="text-xl font-black text-white mb-6 tracking-tight">Share Wave</h3>

        {/* Share Grid */}
        <div className="grid grid-cols-4 gap-6 mb-8">
          {shareOptions.map((opt) => (
            <a 
              key={opt.name}
              href={opt.url}
              target="_blank"
              rel="noopener noreferrer"
              className="flex flex-col items-center gap-2 group"
            >
              <div className={`w-14 h-14 rounded-2xl ${opt.color} flex items-center justify-center shadow-lg group-hover:scale-110 transition-all active:scale-95`}>
                <img src={opt.icon} alt={opt.name} className="w-8 h-8 object-contain brightness-0 invert" />
              </div>
              <span className="text-[11px] font-bold text-slate-500 uppercase tracking-widest">{opt.name}</span>
            </a>
          ))}
        </div>

        <div className="space-y-4">
          {/* Copy Link */}
          <div className="flex items-center gap-3 bg-white/5 border border-white/10 rounded-2xl p-4 group hover:border-teal/30 transition-all">
            <div className="flex-1 overflow-hidden">
              <p className="text-sm text-slate-400 truncate font-medium">{shareUrl}</p>
            </div>
            <button 
              onClick={handleCopy}
              className={`px-6 py-2 rounded-xl text-xs font-black uppercase tracking-widest transition-all ${copied ? 'bg-teal text-white' : 'bg-white/10 text-white hover:bg-white/20'}`}
            >
              {copied ? 'Copied!' : 'Copy'}
            </button>
          </div>

          {/* More Options */}
          {typeof navigator !== 'undefined' && (navigator as any).share && (
            <button 
              onClick={handleNativeShare}
              className="w-full flex items-center justify-center gap-3 bg-white text-black rounded-2xl py-4 font-black text-sm uppercase tracking-widest hover:bg-slate-200 transition-all active:scale-[0.98]"
            >
              <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2.5} d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z" />
              </svg>
              More Share Options
            </button>
          )}
        </div>
      </div>
    </div>
  );
}
