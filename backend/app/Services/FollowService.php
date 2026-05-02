<?php

namespace App\Services;

use App\Models\User;

class FollowService
{
    /**
     * Follow a user.
     */
    public function follow(User $follower, User $following): bool
    {
        if ($follower->id === $following->id) {
            return false;
        }

        if ($follower->following()->where('following_id', $following->id)->exists()) {
            return false;
        }

        $follower->following()->attach($following->id, ['id' => \Illuminate\Support\Str::uuid()]);
        return true;
    }

    /**
     * Unfollow a user.
     */
    public function unfollow(User $follower, User $following): bool
    {
        return (bool) $follower->following()->detach($following->id);
    }

    /**
     * Check if a user is following another user.
     */
    public function isFollowing(User $follower, User $following): bool
    {
        return $follower->following()->where('following_id', $following->id)->exists();
    }
}
