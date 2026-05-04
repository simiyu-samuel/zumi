<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Interfaces\FollowRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class EloquentFollowRepository implements FollowRepositoryInterface
{
    public function follow(User $follower, User $following): void
    {
        $follower->following()->attach($following);
    }

    public function unfollow(User $follower, User $following): void
    {
        $follower->following()->detach($following);
    }

    public function isFollowing(User $follower, User $following): bool
    {
        return $follower->following()->where('following_id', $following->id)->exists();
    }

    public function getFollowers(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $user->followers()->paginate($perPage);
    }

    public function getFollowing(User $user, int $perPage = 20): LengthAwarePaginator
    {
        return $user->following()->paginate($perPage);
    }
}
