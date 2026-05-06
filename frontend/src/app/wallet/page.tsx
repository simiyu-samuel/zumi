"use client";

import React, { useState, useEffect, useCallback } from "react";
import { ShellLayout } from "@/components/layout/ShellLayout";
import { useAuth } from "@/context/AuthContext";
import { getWalletData, getGiftHistory, type GiftTransaction } from "@/lib/api";

type WalletTab = "all" | "gifts";

export default function WalletPage() {
  const { token, user, updateUser } = useAuth();
  const [activeTab, setActiveTab] = useState<WalletTab>("all");
  const [balance, setBalance] = useState<number>(0);
  const [transactions, setTransactions] = useState<GiftTransaction[]>([]);
  const [gifts, setGifts] = useState<GiftTransaction[]>([]);
  const [loading, setLoading] = useState(true);
  const [page, setPage] = useState(1);
  const [hasMore, setHasMore] = useState(true);
  const [loadingMore, setLoadingMore] = useState(false);

  const fetchData = useCallback(async (pageNum: number, append = false) => {
    if (!token) return;
    if (pageNum === 1) setLoading(true);
    else setLoadingMore(true);

    try {
      if (activeTab === "all") {
        const res = await getWalletData();
        setBalance(res.balance);
        updateUser({ drops_balance: res.balance });
        const items = res.transactions.data || [];
        setTransactions(append ? [...transactions, ...items] : items);
        setHasMore(false);
      } else {
        const res = await getGiftHistory(pageNum, 15);
        const items = res.data || [];
        setGifts(append ? [...gifts, ...items] : items);
        setHasMore(items.length >= 15);
      }
    } catch (err) {
      console.error("Wallet fetch error:", err);
    } finally {
      setLoading(false);
      setLoadingMore(false);
    }
  }, [token, activeTab]);

  useEffect(() => {
    setPage(1);
    fetchData(1, false);
  }, [fetchData]);

  const handleLoadMore = () => {
    const nextPage = page + 1;
    setPage(nextPage);
    fetchData(nextPage, true);
  };

  const activeItems = activeTab === "all" ? transactions : gifts;

  return (
    <ShellLayout>
      {/* Wallet Header */}
      <header className="px-8 py-10 bg-gradient-to-br from-slate-900 to-black border-b border-white/5">
        <div className="flex flex-col items-center text-center">
          <div className="w-16 h-16 rounded-full bg-teal/10 flex items-center justify-center text-teal mb-6 ring-8 ring-teal/5">
            <svg className="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
            </svg>
          </div>
          <p className="text-[12px] font-black text-slate-500 uppercase tracking-widest mb-2">Available Balance</p>
          <div className="flex items-center gap-3">
             <span className="text-5xl font-black text-white tracking-tighter">{balance.toLocaleString()}</span>
             <span className="text-teal font-black text-xl self-end mb-1.5 uppercase tracking-tight">Drops</span>
          </div>
          
          <div className="mt-8 flex gap-3">
             <button className="px-8 py-3 rounded-full bg-teal text-white font-black text-[14px] uppercase tracking-wider hover:bg-teal-dark transition-all shadow-lg shadow-teal/20">
                Buy Drops
             </button>
             <button className="px-8 py-3 rounded-full bg-white/5 border border-white/10 text-white font-black text-[14px] uppercase tracking-wider hover:bg-white/10 transition-all">
                Withdraw
             </button>
          </div>
        </div>
      </header>

      {/* Tabs */}
      <div className="flex px-8 border-b border-white/5 sticky top-0 bg-black/80 backdrop-blur-xl z-20">
         <button 
           onClick={() => setActiveTab("all")}
           className={`px-6 py-5 text-[13px] font-black tracking-widest uppercase transition-all ${activeTab === "all" ? "text-teal border-b-2 border-teal" : "text-slate-500 hover:text-white"}`}
         >
           History
         </button>
         <button 
           onClick={() => setActiveTab("gifts")}
           className={`px-6 py-5 text-[13px] font-black tracking-widest uppercase transition-all ${activeTab === "gifts" ? "text-teal border-b-2 border-teal" : "text-slate-500 hover:text-white"}`}
         >
           Gifts
         </button>
      </div>

      {/* Transaction List */}
      <div className="flex-1 overflow-y-auto no-scrollbar">
        {loading ? (
          <div className="flex flex-col items-center py-20 gap-4">
             <div className="w-10 h-10 border-2 border-teal/20 border-t-teal rounded-full animate-spin" />
             <p className="text-sm text-slate-500 font-bold uppercase tracking-widest">Updating Ledger...</p>
          </div>
        ) : activeItems.length === 0 ? (
          <div className="flex flex-col items-center py-24 gap-4 opacity-50">
             <svg className="w-16 h-16 text-slate-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
               <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
             </svg>
             <p className="text-slate-500 font-bold uppercase tracking-widest">No transactions found</p>
          </div>
        ) : (
          <div className="divide-y divide-white/5">
            {activeItems.map((tx) => {
              const description = tx.description || tx.type || "Transaction";
              const isGift = description.toLowerCase().includes("gift") || tx.type === "gift";
              const isReceived = tx.direction === 'credit';
              
              return (
                <div key={tx.id} className="px-8 py-6 flex items-center gap-5 hover:bg-white/5 transition-colors group">
                  <div className={`w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 ${isReceived ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'}`}>
                    {isGift ? (
                       <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V6a2 2 0 10-2 2h2zm0 0h4l1 3H7l1-3h4z" />
                      </svg>
                    ) : (
                      <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M13 10V3L4 14h7v7l9-11h-7z" />
                      </svg>
                    )}
                  </div>
                  <div className="flex-1 min-w-0">
                    <div className="flex justify-between items-start mb-1">
                       <h4 className="font-bold text-white text-[15px] truncate max-w-[70%]">{description || "Transaction"}</h4>
                       <span className={`font-black text-[15px] ${isReceived ? 'text-emerald-400' : 'text-white'}`}>
                          {isReceived ? '+' : '-'}{tx.amount}
                       </span>
                    </div>
                    <div className="flex justify-between items-center">
                       <p className="text-[11px] text-slate-500 font-bold uppercase tracking-widest">
                         {new Date(tx.created_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' })}
                       </p>
                       <span className="text-[10px] text-slate-700 font-black uppercase tracking-tighter">SUCCESS</span>
                    </div>
                  </div>
                </div>
              );
            })}

            {hasMore && (
              <div className="p-8 flex justify-center">
                <button 
                  onClick={handleLoadMore}
                  disabled={loadingMore}
                  className="px-8 py-3 rounded-full bg-white/5 border border-white/10 text-[13px] font-bold text-slate-400 hover:text-white hover:bg-white/10 transition-all disabled:opacity-50"
                >
                   {loadingMore ? "Loading..." : "Load More Activity"}
                </button>
              </div>
            )}
          </div>
        )}
      </div>
    </ShellLayout>
  );
}
