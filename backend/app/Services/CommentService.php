<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\User;
use App\Repositories\Interfaces\CommentRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class CommentService
{
    public function __construct(
        protected CommentRepositoryInterface $commentRepository
    ) {}

    /**
     * Add a comment to a model.
     */
    public function addComment(User $user, Model $commentable, string $content, ?string $parentId = null): Comment
    {
        return $this->commentRepository->create([
            'user_id' => $user->id,
            'content' => $content,
            'parent_id' => $parentId,
        ], $commentable);
    }

    /**
     * Get comments for a model.
     */
    public function getComments(Model $commentable)
    {
        return $this->commentRepository->getForModel($commentable);
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(Comment $comment): bool
    {
        return $this->commentRepository->delete($comment);
    }
}
