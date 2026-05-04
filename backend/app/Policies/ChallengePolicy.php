<?php

namespace App\Policies;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChallengePolicy
{
    /**
     * Determine whether the user can create challenges.
     */
    public function create(User $user): bool
    {
        return $user->can('host wave challenges') || $user->isPremium();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Challenge $challenge): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Challenge $challenge): bool
    {
        return $challenge->user_id === $user->id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Challenge $challenge): bool
    {
        return $challenge->user_id === $user->id;
    }

    /**
     * Determine whether the user can join the challenge.
     */
    public function join(User $user, Challenge $challenge): bool
    {
        return $challenge->status === \App\Enums\ChallengeStatus::Active &&
               !$challenge->participations()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can vote in the challenge.
     */
    public function vote(User $user, Challenge $challenge): bool
    {
        return $challenge->status === \App\Enums\ChallengeStatus::Active;
    }

    /**
     * Determine whether the user can close the challenge.
     */
    public function close(User $user, Challenge $challenge): bool
    {
        return $challenge->user_id === $user->id;
    }
}
