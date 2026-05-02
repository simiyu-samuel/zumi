<?php

namespace App\Repositories\Interfaces;

use App\Models\Comment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

interface CommentRepositoryInterface
{
    public function create(array $data, Model $commentable): Comment;
    public function getForModel(Model $commentable, int $perPage = 20): LengthAwarePaginator;
    public function delete(Comment $comment): bool;
    public function findById(string $id): ?Comment;
}
