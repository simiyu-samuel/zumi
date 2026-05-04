<?php

namespace App\Policies;

use App\Models\Circle;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CirclePolicy
{
    /**
     * Determine whether the user can create circles.
     */
    public function create(User $user): bool
    {
        if ($user->isPremium()) {
            return true;
        }

        return $user->ownedCircles()->count() < 1;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Circle $circle): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Circle $circle): bool
    {
        return $circle->owner_id === $user->id || 
               $circle->members()->where('user_id', $user->id)->where('role', \App\Enums\CircleMemberRole::Moderator)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Circle $circle): bool
    {
        return $circle->owner_id === $user->id;
    }

    /**
     * Determine whether the user can join the circle.
     */
    public function join(User $user, Circle $circle): bool
    {
        return !$circle->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can leave the circle.
     */
    public function leave(User $user, Circle $circle): bool
    {
        return $circle->owner_id !== $user->id && 
               $circle->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can view circle insights.
     */
    public function viewInsights(User $user, Circle $circle): bool
    {
        return $circle->owner_id === $user->id;
    }
}
