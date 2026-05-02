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
            ->with(['user', 'replies.user'])
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
        return Comment::find($id);
    }
}
