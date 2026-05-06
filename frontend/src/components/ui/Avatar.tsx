"use client";

import React, { useState } from "react";

interface AvatarProps {
  src?: string | null;
  name?: string;
  size?: "sm" | "md" | "lg" | "xl";
  className?: string;
  role?: string;
}

export function Avatar({ src, name, size = "md", className = "", role }: AvatarProps) {
  const [error, setError] = useState(false);

  const sizeClasses = {
    sm: "w-8 h-8 text-[12px]",
    md: "w-10 h-10 text-[14px]",
    lg: "w-12 h-12 text-[18px]",
    xl: "w-16 h-16 text-[24px]",
  };

  const initials = name
    ? name
        .split(" ")
        .map((n) => n[0])
        .join("")
        .toUpperCase()
        .substring(0, 2)
    : "U";

  const showImage = src && !error;

  return (
    <div className="relative flex shrink-0">
      <div
        className={`rounded-full bg-teal/10 border border-teal/20 flex items-center justify-center font-black text-teal uppercase overflow-hidden ${sizeClasses[size]} ${className}`}
      >
        {showImage ? (
          <img
            src={src}
            alt={name}
            className="w-full h-full object-cover"
            onError={() => setError(true)}
          />
        ) : (
          <span>{initials}</span>
        )}
      </div>
      
      {/* Role Badge */}
      {(role === 'pro' || role === 'studio' || role === 'admin') && (
        <div className={`absolute -bottom-1 -right-1 rounded-full bg-slate-950 p-0.5 z-10 ${size === 'sm' ? 'scale-75' : ''}`}>
           <div className={`rounded-full flex items-center justify-center ${role === 'admin' ? 'bg-teal shadow-[0_0_8px_#1a9e75]' : 'bg-violet shadow-[0_0_8px_#6c63ff]'}`}>
              <svg 
                className={`${size === 'xl' ? 'w-4 h-4' : 'w-2.5 h-2.5'} text-white`} 
                fill="currentColor" 
                viewBox="0 0 20 20"
              >
                <path d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.64.304 1.25.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" />
              </svg>
           </div>
        </div>
      )}
    </div>
  );
}
