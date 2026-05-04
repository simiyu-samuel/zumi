<?php

namespace App\Policies;

use App\Models\User;

class SubscriptionPolicy
{
    /**
     * Determine if the user can subscribe to a plan.
     */
    public function subscribe(User $user): bool
    {
        // Can subscribe if not already subscribed to the 'default' plan
        return !$user->subscribed('default');
    }

    /**
     * Determine if the user can change their plan.
     */
    public function change(User $user): bool
    {
        return $user->subscribed('default');
    }

    /**
     * Determine if the user can cancel their subscription.
     */
    public function cancel(User $user): bool
    {
        return $user->subscribed('default') && !$user->subscription('default')->onGracePeriod();
    }

    /**
     * Determine if the user can resume their subscription.
     */
    public function resume(User $user): bool
    {
        return $user->subscription('default')?->onGracePeriod() ?? false;
    }
}
