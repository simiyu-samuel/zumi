<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Interfaces\FollowRepositoryInterface;
use App\Services\FlowScoreService;

class FollowService
{
    public function __construct(
        protected FollowRepositoryInterface $followRepository,
        protected FlowScoreService $flowScoreService,
    ) {}

    /**
     * Follow a user.
     */
    public function follow(User $follower, User $following): bool
    {
        if ($follower->id === $following->id) {
            return false;
        }

        if ($this->followRepository->isFollowing($follower, $following)) {
            return false;
        }

        $this->followRepository->follow($follower, $following);

        // Award Flow Score to the user being followed
        $this->flowScoreService->award($following, 'follower_gained');

        $following->notify(new \App\Notifications\NewFollowerNotification($follower));

        return true;
    }

    /**
     * Unfollow a user.
     */
    public function unfollow(User $follower, User $following): bool
    {
        $this->followRepository->unfollow($follower, $following);
        return true;
    }

    /**
     * Check if a user is following another user.
     */
    public function isFollowing(User $follower, User $following): bool
    {
        return $this->followRepository->isFollowing($follower, $following);
    }
}
