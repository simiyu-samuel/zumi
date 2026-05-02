<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class CommentService
{
    /**
     * Add a comment to a model.
     */
    public function addComment(User $user, Model $commentable, string $content, ?string $parentId = null): Comment
    {
        return $commentable->comments()->create([
            'user_id' => $user->id,
            'content' => $content,
            'parent_id' => $parentId,
        ]);
    }

    /**
     * Get comments for a model.
     */
    public function getComments(Model $commentable)
    {
        return $commentable->comments()
            ->with(['user', 'replies.user'])
            ->whereNull('parent_id')
            ->latest()
            ->paginate(20);
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(Comment $comment): bool
    {
        return $comment->delete();
    }
}
