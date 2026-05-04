<?php

namespace App\Services;

use App\Models\Comment;
use App\Models\User;
use App\Repositories\Interfaces\CommentRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class CommentService
{
    public function __construct(
        protected CommentRepositoryInterface $commentRepository,
        protected FlowScoreService $flowScoreService,
    ) {}

    /**
     * Add a comment to a model.
     */
    public function addComment(User $user, Model $commentable, string $content, ?string $parentId = null): Comment
    {
        $comment = $this->commentRepository->create([
            'user_id' => $user->id,
            'content' => $content,
            'parent_id' => $parentId,
        ], $commentable);

        // Award Flow Score to the owner of the commentable model (e.g. Wave owner)
        // Only if the owner is not the commenter themselves.
        if (isset($commentable->user_id) && $commentable->user_id !== $user->id) {
            $this->flowScoreService->award($commentable->user, 'comment_received');
        }

        return $comment;
    }

    /**
     * Get comments for a model.
     */
    public function getComments(Model $commentable, int $perPage = 15)
    {
        return $this->commentRepository->getForModel($commentable, $perPage);
    }

    /**
     * Delete a comment.
     */
    public function deleteComment(Comment $comment): bool
    {
        return $this->commentRepository->delete($comment);
    }
}
