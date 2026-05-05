<?php

namespace App\Repositories\Eloquent;

use App\Models\Comment;
use App\Repositories\Interfaces\CommentRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentCommentRepository implements CommentRepositoryInterface
{
    public function create(array $data, Model $commentable): Comment
    {
        return $commentable->comments()->create($data);
    }

    public function getForModel(Model $commentable, int $perPage = 20): LengthAwarePaginator
    {
        return $commentable->comments()
            ->with(Comment::RECURSIVE_EAGER_LOAD)
            ->whereNull('parent_id')
            ->latest()
            ->paginate($perPage);
    }

    public function delete(Comment $comment): bool
    {
        return $comment->delete();
    }

    public function findById(string $id): ?Comment
    {
        return Comment::with(Comment::DEFAULT_EAGER_LOAD)->find($id);
    }

    public function findLike(Comment $comment, string $userId): ?\App\Models\CommentLike
    {
        return $comment->likes()->where('user_id', $userId)->first();
    }

    public function addLike(Comment $comment, string $userId): void
    {
        $comment->likes()->create(['user_id' => $userId]);
        $comment->increment('likes_count');
    }

    public function removeLike(Comment $comment, \App\Models\CommentLike $like): void
    {
        $like->delete();
        $comment->decrement('likes_count');
    }
}
