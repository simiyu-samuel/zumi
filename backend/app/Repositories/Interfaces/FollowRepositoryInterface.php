<?php

namespace App\Repositories\Interfaces;

use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

interface FollowRepositoryInterface
{
    public function follow(User $follower, User $following): void;
    public function unfollow(User $follower, User $following): void;
    public function isFollowing(User $follower, User $following): bool;
    public function getFollowers(User $user, int $perPage = 20): LengthAwarePaginator;
    public function getFollowing(User $user, int $perPage = 20): LengthAwarePaginator;
}
