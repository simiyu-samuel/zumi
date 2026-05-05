"use client";

import React, { useState, useEffect, useRef } from "react";
import { getComments, postComment, likeComment, type Comment } from "@/lib/api";
import { useAuth } from "@/context/AuthContext";
import echo from "@/lib/echo";

interface CommentSheetProps {
  waveId: string;
  onClose: () => void;
}

export function CommentSheet({ waveId, onClose }: CommentSheetProps) {
  const { user } = useAuth();
  const [comments, setComments] = useState<Comment[]>([]);
  const [loading, setLoading] = useState(true);
  const [newComment, setNewComment] = useState("");
  const [replyTo, setReplyTo] = useState<Comment | null>(null);
  const scrollRef = useRef<HTMLDivElement>(null);

  useEffect(() => {
    async function load() {
      try {
        const res = await getComments(waveId);
        setComments(res.data);
      } catch (err) {
        console.error("Failed to load comments", err);
      } finally {
        setLoading(false);
      }
    }
    load();

    // Listen for real-time updates
    const channel = echo.channel(`waves.${waveId}.comments`);
    
    channel.listen('.comment.posted', (data: { comment: Comment }) => {
      setComments((prev) => {
        // Prevent duplicate if current user was the sender (optimistic update already handled it)
        if (prev.some(c => c.id === data.comment.id)) return prev;

        if (data.comment.parent_id) {
          const updateReplies = (items: Comment[]): Comment[] => {
            return items.map(c => {
              if (c.id === data.comment.parent_id) {
                return { ...c, replies: [...(c.replies || []), data.comment] };
              }
              if (c.replies) {
                return { ...c, replies: updateReplies(c.replies) };
              }
              return c;
            });
          };
          return updateReplies(prev);
        }
        return [data.comment, ...prev];
      });
    });

    channel.listen('.comment.liked', (data: { id: string, likes_count: number }) => {
      setComments((prev) => {
        const updateLikes = (items: Comment[]): Comment[] => {
          return items.map(c => {
            if (c.id === data.id) {
              return { ...c, likes_count: data.likes_count };
            }
            if (c.replies) {
              return { ...c, replies: updateLikes(c.replies) };
            }
            return c;
          });
        };
        return updateLikes(prev);
      });
    });

    return () => {
      echo.leaveChannel(`waves.${waveId}.comments`);
    };
  }, [waveId]);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    if (!newComment.trim()) return;

    try {
      const comment = await postComment(waveId, newComment, replyTo?.id);
      if (replyTo) {
        setComments((prev) =>
          prev.map((c) =>
            c.id === replyTo.id
              ? { ...c, replies: [...(c.replies || []), comment] }
              : c
          )
        );
      } else {
        setComments((prev) => [comment, ...prev]);
      }
      setNewComment("");
      setReplyTo(null);
    } catch (err) {
      console.error("Failed to post comment", err);
    }
  };

  const handleLikeComment = async (commentId: string) => {
    try {
      const res = await likeComment(commentId);
      
      const updateCommentLike = (comments: Comment[]): Comment[] => {
        return comments.map((c) => {
          if (c.id === commentId) {
            return { ...c, is_liked: res.liked, likes_count: res.likes_count };
          }
          if (c.replies) {
            return { ...c, replies: updateCommentLike(c.replies) };
          }
          return c;
        });
      };

      setComments((prev) => updateCommentLike(prev));
    } catch (err) {
      console.error("Like comment error:", err);
    }
  };

  return (
    <div className="fixed inset-0 z-[100] flex items-end justify-center animate-fade-in bg-black/60 backdrop-blur-sm">
      <div 
        className="absolute inset-0" 
        onClick={onClose}
      />
      
      <div className="w-full max-w-[600px] h-[85vh] glass-dark rounded-t-[32px] border-t border-white/10 flex flex-col relative z-10 animate-slide-up shadow-[0_-20px_60px_rgba(0,0,0,0.8)]">
        {/* Drag Handle */}
        <div className="w-12 h-1.5 bg-white/20 rounded-full mx-auto mt-4 mb-2 shrink-0" />

        {/* Header */}
        <div className="px-6 py-4 flex justify-between items-center border-b border-white/5 shrink-0">
          <div>
            <h3 className="text-lg font-black text-white tracking-tight">Comments</h3>
            <p className="text-[12px] text-slate-500 font-bold uppercase tracking-wider">
              {comments.length} total
            </p>
          </div>
          <button 
            onClick={onClose}
            className="w-10 h-10 rounded-full bg-white/5 flex items-center justify-center text-white hover:bg-white/10 transition-colors"
          >
            <svg className="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        {/* Comment List */}
        <div ref={scrollRef} className="flex-1 overflow-y-auto px-6 py-6 space-y-8 no-scrollbar">
          {loading ? (
            <div className="flex flex-col items-center py-20 gap-4">
              <div className="w-8 h-8 border-2 border-teal/20 border-t-teal rounded-full animate-spin" />
              <p className="text-sm text-slate-500 font-bold">Loading comments...</p>
            </div>
          ) : comments.length === 0 ? (
            <div className="flex flex-col items-center py-20 gap-4 opacity-50">
              <svg className="w-12 h-12 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
              </svg>
              <p className="text-sm text-slate-500 font-bold">No comments yet. Be the first!</p>
            </div>
          ) : (
            comments.map((comment) => (
              <CommentItem 
                key={comment.id} 
                comment={comment} 
                onReply={() => setReplyTo(comment)}
                onLike={handleLikeComment}
              />
            ))
          )}
        </div>

        {/* Reply Indicator */}
        {replyTo && (
          <div className="px-6 py-2 bg-teal/10 border-t border-teal/20 flex justify-between items-center animate-in slide-in-from-bottom-2">
            <span className="text-[12px] text-teal font-bold">
              Replying to <span className="text-white">@{replyTo.user.username}</span>
            </span>
            <button 
              onClick={() => setReplyTo(null)}
              className="text-teal hover:text-white transition-colors"
            >
              <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
        )}

        {/* Input Footer */}
        <div className="p-6 pt-2 border-t border-white/5 bg-slate-900/50 backdrop-blur-md shrink-0">
          <form onSubmit={handleSubmit} className="flex items-center gap-4">
            <div className="w-10 h-10 rounded-full bg-teal/20 border border-teal/30 flex items-center justify-center font-bold text-teal shrink-0">
              {user?.name?.charAt(0)}
            </div>
            <div className="flex-1 relative">
              <input 
                type="text"
                value={newComment}
                onChange={(e) => setNewComment(e.target.value)}
                placeholder={replyTo ? "Write a reply..." : "Add a comment..."}
                className="w-full bg-white/5 border border-white/10 rounded-full py-3 px-5 text-[15px] text-white placeholder:text-slate-600 focus:outline-none focus:border-teal/50 focus:bg-white/10 transition-all"
              />
            </div>
            <button 
              type="submit"
              disabled={!newComment.trim()}
              className="w-10 h-10 rounded-full bg-teal text-white flex items-center justify-center hover:bg-teal-dark disabled:opacity-30 disabled:cursor-not-allowed transition-all shadow-lg shadow-teal/20"
            >
              <svg className="w-5 h-5 rotate-90" fill="currentColor" viewBox="0 0 24 24">
                <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z" />
              </svg>
            </button>
          </form>
        </div>
      </div>
    </div>
  );
}

function CommentItem({ 
  comment, 
  onReply, 
  onLike,
  isReply = false 
}: { 
  comment: Comment; 
  onReply: () => void;
  onLike: (id: string) => void;
  isReply?: boolean;
}) {
  const [showReplies, setShowReplies] = useState(false);
  const [repliesLimit, setRepliesLimit] = useState(5);

  if (!comment || !comment.user) return null;

  const hasReplies = comment.replies && comment.replies.length > 0;
  const visibleReplies = comment.replies?.slice(0, repliesLimit) || [];
  const remainingRepliesCount = (comment.replies?.length || 0) - repliesLimit;

  return (
    <div className={`flex gap-4 ${isReply ? 'mt-4 ml-10 scale-95' : ''}`}>
      <div className={`${isReply ? 'w-8 h-8' : 'w-10 h-10'} rounded-full bg-slate-800 border border-white/5 flex items-center justify-center font-bold text-teal shrink-0 overflow-hidden`}>
        {comment.user.avatar_url ? (
          <img src={comment.user.avatar_url} alt={comment.user.name} className="w-full h-full object-cover" />
        ) : (
          <span className="text-teal-light">{comment.user.name.charAt(0)}</span>
        )}
      </div>
      <div className="flex-1 min-w-0">
        <div className="flex items-center gap-2 mb-1">
          <span className="text-[14px] font-black text-white hover:text-teal transition-colors cursor-pointer">
            @{comment.user.username}
          </span>
          <span className="text-[11px] text-slate-600 font-bold uppercase">
            {new Date(comment.created_at).toLocaleDateString(undefined, { month: 'short', day: 'numeric' })}
          </span>
        </div>
        <p className="text-[15px] text-slate-200 leading-relaxed break-words font-medium">
          {comment.content}
        </p>
        <div className="flex items-center gap-6 mt-3">
          <button 
            onClick={() => onLike(comment.id)}
            className={`flex items-center gap-1.5 text-[12px] font-black transition-all hover:scale-110 active:scale-95 ${comment.is_liked ? 'text-teal' : 'text-slate-500 hover:text-white'}`}
          >
            <svg className="w-4 h-4" fill={comment.is_liked ? "currentColor" : "none"} viewBox="0 0 24 24" stroke="currentColor">
               <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
            </svg>
            {comment.likes_count > 0 && <span>{comment.likes_count}</span>}
          </button>
          {!isReply && (
            <button 
              onClick={onReply}
              className="text-[12px] font-black text-slate-500 hover:text-teal transition-colors uppercase tracking-wider"
            >
              Reply
            </button>
          )}
        </div>

        {/* Toggle Replies */}
        {hasReplies && !isReply && (
          <div className="mt-4">
            <button 
              onClick={() => setShowReplies(!showReplies)}
              className="flex items-center gap-2 text-[12px] font-black text-teal hover:text-teal-light transition-colors group"
            >
              <div className="w-8 h-px bg-teal/30 group-hover:bg-teal/50 transition-all" />
              {showReplies ? 'Hide Replies' : `View ${comment.replies?.length} Replies`}
              <svg className={`w-3 h-3 transition-transform ${showReplies ? 'rotate-180' : ''}`} fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={3} d="M19 9l-7 7-7-7" />
              </svg>
            </button>

            {showReplies && (
              <div className="relative animate-in fade-in slide-in-from-top-2 duration-300">
                <div className="absolute left-[-24px] top-4 bottom-4 w-px bg-white/5" />
                <div className="space-y-4">
                  {visibleReplies.map((reply) => (
                    <CommentItem 
                      key={reply.id} 
                      comment={reply} 
                      onReply={onReply} 
                      onLike={onLike}
                      isReply={true} 
                    />
                  ))}
                </div>

                {remainingRepliesCount > 0 && (
                  <button 
                    onClick={() => setRepliesLimit(prev => prev + 10)}
                    className="ml-10 mt-4 text-[12px] font-black text-slate-500 hover:text-teal transition-colors flex items-center gap-2"
                  >
                    Show more replies ({remainingRepliesCount})
                  </button>
                )}
              </div>
            )}
          </div>
        )}
      </div>
    </div>
  );
}
