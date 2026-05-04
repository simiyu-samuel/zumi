<?php

namespace App\Repositories\Interfaces;

use App\Models\User;

interface SubscriptionRepositoryInterface
{
    /**
     * Get the active subscription for a user.
     */
    public function getActiveForUser(User $user);

    /**
     * Get available plans.
     */
    public function getAvailablePlans(): array;
}
